<?php
declare (strict_types = 1);

namespace app\command;
use think\facade\Db;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\common\model\ConfigurationModel;
use app\common\model\SmsTemplateModel;
use app\common\model\TransactionModel;
use app\common\model\SupplierModel;
use app\common\model\UpstreamProductModel;
use app\common\logic\UpstreamLogic;
use app\common\model\ProductModel;
use app\common\model\OrderModel;
use app\common\model\SelfDefinedFieldModel;

class Cron extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('cron')
            ->setDescription('the cron command');
    }

    protected function execute(Input $input, Output $output)
    {
        // 每天几点开始执行
        if (date('G')<($config['cron_day_start_time']??1)){
            return false;
        }
		$this->minuteCron();// 每分钟执行一次hook需要
		$config = $this->cronConfig();

		$this->configurationUpdate('cron_lock_start_time',time());
		
		//最后执行时间判断
		if(((time() - $config["cron_lock_last_time"]) < 5*60)){
            return false;
        }
		//最后执行时间判断
		if(((time() - $config["cron_lock_last_time"]) > 5*60)){
            $this->configurationUpdate('cron_lock',0);
        }
		//锁
		if(!empty($config['cron_lock'])){
			return false;
		}
		$output->writeln('自动任务开始:'.date('Y-m-d H:i:s'));
		$this->configurationUpdate('cron_lock',1);
        // 指令输出
		$this->fiveMinuteCron($config,$output);// 5分钟执行一次
		$this->dayCron($config,$output);// 每天执行一次
		$this->configurationUpdate('cron_lock',0);
		$this->configurationUpdate('cron_lock_last_time',time());
        $output->writeln('自动任务结束:'.date('Y-m-d H:i:s'));
    }
	// 每天执行一次
	public function dayCron($config,$output){
        # 如果已经过去24小时,并且时间超过了设置时间
        $this_time = time();
        if( (($this_time - $config["cron_lock_day_last_time"]??0) < 60*60*24) || date('G') < ($config["cron_day_start_time"]??0)){
            return false;
        }
        # 今日执行 15分钟限制
        /*$time_day = strtotime(date('Y-m-d'))+intval($config["cron_day_start_time"]??0)*60*60;
        if ($time_day > time() || time() > $time_day+60*15){
            return false;
        }*/
        # 今天执行了 锁 ;
        if (date('Y-m-d',$config['cron_lock_day_last_time']??0) == date('Y-m-d')){
            return false;
        }
        # 执行自动任务
        $this->configurationUpdate('cron_lock_day_last_time',time());

		$this->hostDue($config);//主机续费提示
		$this->hostOverdue($config);//主机逾期提示
		$this->orderOverdue($config);//订单未付款
		$this->downstreamSyncProduct();//订单未付款
		$this->orderUnpaidDelete($config);//订单未付款自动删除
		$output->writeln('续费提醒结束:'.date('Y-m-d H:i:s'));

		hook('daily_cron');// 每日执行一次定时任务钩子
		$this->configurationUpdate('cron_lock_day_last_time',time());
	}
	// 每分钟执行一次
	public function minuteCron(){
		$this->deleteRecycleOrder(); // 删除到期回收站订单
		hook('minute_cron');// 每分钟执行一次定时任务钩子
	}
	// 每五分钟执行一次
	public function fiveMinuteCron($config,$output){
		if((time()-$config['cron_lock_five_minute_last_time'])<5*60){
			return false;
		}
		//更新短信模板状态
		$sms_template=Db::name('sms_template')->field('sms_name')->whereIn('status','1')->group('sms_name')->select()->toArray();
		if(!empty($sms_template)){
			foreach($sms_template as $v){
				(new SmsTemplateModel())->statusSmsTemplate(['name'=>$v['sms_name']]);
			}
		}
		
		$this->hostModule($config);// 主机暂停、删除
		$output->writeln('自动暂停、删除结束:'.date('Y-m-d H:i:s'));

		// TODO 删除，测试！
        $this->downstreamSyncProduct();//订单未付款
		hook('five_minute_cron');// 每五分钟执行一次定时任务钩子
		$this->configurationUpdate('cron_lock_five_minute_last_time',time());
	}
	//主机续费提醒	
	public function hostDue($config){
        $time=time();
        //第一次提醒
        $renewal_first_swhitch=$config['cron_due_renewal_first_swhitch'];
		$renewal_first_day=$config['cron_due_renewal_first_day'];
        if($renewal_first_swhitch==1){
	        $time_renewal_first = $time+$renewal_first_day*24*3600;     
	        $time_renewal_first_start = strtotime(date('Y-m-d 00:00:00',$time_renewal_first));
	        $time_renewal_first_end = strtotime(date('Y-m-d 23:59:59',$time_renewal_first));
			$renewal_first_host=Db::name('host')
			->field('id,client_id')
			->whereIn('status','Active')
			->where('due_time','>=',$time_renewal_first_start)
			->where('due_time','<=',$time_renewal_first_end)
			->where('billing_cycle', '<>', 'free')
			->where('billing_cycle', '<>', 'onetime')
			->select()->toArray();
			foreach($renewal_first_host as $h){
				try {
					hook('before_host_renewal_first',$h);
				} catch (\Exception $e) {
					$result['status'] = 'Failed';
					$result['msg'] = $e->getMessage();				
					//continue;
				}	
				$host = Db::name('host')->where('id', $h['id'])->find();
				if($host['due_time']<=$time_renewal_first_start || $host['due_time']>=$time_renewal_first_end){
					continue;
				}

				add_task([
					'type' => 'email',
					'description' => '#host#'.$h['id'].'#'.lang('host_renewal_first_send_mail'),
					'task_data' => [
						'name'=>'host_renewal_first',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);
				add_task([
					'type' => 'sms',
					'description' => '#host#'.$h['id'].'#'.lang('host_renewal_first_send_sms'),
					'task_data' => [
						'name'=>'host_renewal_first',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);							
			}
			unset($renewal_first_host);
		}
		//第二次提醒
		$renewal_second_swhitch=$config['cron_due_renewal_second_swhitch'];
		$renewal_second_day=$config['cron_due_renewal_second_day'];
		if($renewal_second_swhitch==1){
			$time_renewal_second = $time+$renewal_second_day*24*3600;     
	        $time_renewal_second_start = strtotime(date('Y-m-d 00:00:00',$time_renewal_second));
	        $time_renewal_second_end = strtotime(date('Y-m-d 23:59:59',$time_renewal_second));
	        $renewal_second_host=Db::name('host')
	        ->field('id,client_id')
			->whereIn('status','Active')
			->where('due_time','>=',$time_renewal_second_start)
			->where('due_time','<=',$time_renewal_second_end)
			->where('billing_cycle', '<>', 'free')
			->where('billing_cycle', '<>', 'onetime')
			->select()->toArray();
			foreach($renewal_second_host as $h){				
				add_task([
					'type' => 'email',
					'description' => '#host#'.$h['id'].'#'.lang('host_renewal_second_send_mail'),
					'task_data' => [
						'name'=>'host_renewal_second',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);
				add_task([
					'type' => 'sms',
					'description' => '#host#'.$h['id'].'#'.lang('host_renewal_second_send_sms'),
					'task_data' => [
						'name'=>'host_renewal_second',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);									
			}
			unset($renewal_second_host);
		}
	}	
	//主机逾期提醒	
	public function hostOverdue($config){
		$time=time();
		//第一次提醒
        $overdue_first_swhitch=$config['cron_overdue_first_swhitch'];
		$overdue_first_day=$config['cron_overdue_first_day'];
        if($overdue_first_swhitch==1){
	        $time_overdue_first = $time-$overdue_first_day*24*3600;     
	        $time_overdue_first_start = strtotime(date('Y-m-d 00:00:00',$time_overdue_first));
	        $time_overdue_first_end = strtotime(date('Y-m-d 23:59:59',$time_overdue_first));
			$overdue_first_host=Db::name('host')
			->field('id,client_id')
			->whereIn('status','Active,Suspended')
			->where('due_time','>=',$time_overdue_first_start)
			->where('due_time','<=',$time_overdue_first_end)
			->where('billing_cycle', '<>', 'free')
			->where('billing_cycle', '<>', 'onetime')
			->select()->toArray();
			foreach($overdue_first_host as $h){			
				add_task([
					'type' => 'email',
					'description' => '#host#'.$h['id'].'#'.lang('host_overdue_first_send_mail'),
					'task_data' => [
						'name'=>'host_overdue_first',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);
				add_task([
					'type' => 'sms',
					'description' => '#host#'.$h['id'].'#'.lang('host_overdue_first_send_sms'),
					'task_data' => [
						'name'=>'host_overdue_first',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);							
			}
			unset($overdue_first_host);
		}
		//第二次提醒
		$overdue_second_swhitch=$config['cron_overdue_second_swhitch'];
		$overdue_second_day=$config['cron_overdue_second_day'];
        if($overdue_second_swhitch==1){
	        $time_overdue_second = $time-$overdue_second_day*24*3600;     
	        $time_overdue_second_start = strtotime(date('Y-m-d 00:00:00',$time_overdue_second));
	        $time_overdue_second_end = strtotime(date('Y-m-d 23:59:59',$time_overdue_second));
			$overdue_second_host=Db::name('host')
			->field('id,client_id')
			->whereIn('status','Active,Suspended')
			->where('due_time','>=',$time_overdue_second_start)
			->where('due_time','<=',$time_overdue_second_end)
			->where('billing_cycle', '<>', 'free')
			->where('billing_cycle', '<>', 'onetime')
			->select()->toArray();
			foreach($overdue_second_host as $h){			
				add_task([
					'type' => 'email',
					'description' => '#host#'.$h['id'].'#'.lang('host_overdue_second_send_mail'),
					'task_data' => [
						'name'=>'host_overdue_second',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);
				add_task([
					'type' => 'sms',
					'description' => '#host#'.$h['id'].'#'.lang('host_overdue_second_send_sms'),
					'task_data' => [
						'name'=>'host_overdue_second',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);							
			}
			unset($overdue_second_host);
		}
		//第三次提醒
		$overdue_third_swhitch=$config['cron_overdue_third_swhitch'];
		$overdue_third_day=$config['cron_overdue_third_day'];
		if($overdue_third_swhitch==1){
	        $time_overdue_third = $time-$overdue_third_day*24*3600;     
	        $time_overdue_third_start = strtotime(date('Y-m-d 00:00:00',$time_overdue_third));
	        $time_overdue_third_end = strtotime(date('Y-m-d 23:59:59',$time_overdue_third));
			$overdue_third_host=Db::name('host')
			->field('id,client_id')
			->whereIn('status','Active,Suspended')
			->where('due_time','>=',$time_overdue_third_start)
			->where('due_time','<=',$time_overdue_third_end)
			->where('billing_cycle', '<>', 'free')
			->where('billing_cycle', '<>', 'onetime')
			->select()->toArray();
			foreach($overdue_third_host as $h){			
				add_task([
					'type' => 'email',
					'description' => '#host#'.$h['id'].'#'.lang('host_overdue_third_send_mail'),
					'task_data' => [
						'name'=>'host_overdue_third',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);
				add_task([
					'type' => 'sms',
					'description' => '#host#'.$h['id'].'#'.lang('host_overdue_third_send_sms'),
					'task_data' => [
						'name'=>'host_overdue_third',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);						
			}
			unset($overdue_third_host);
		}

	}
	//订单未付款通知
	public function orderOverdue($config){
		$time=time();
		$order_overdue_swhitch=$config['cron_order_overdue_swhitch'];
		$order_overdue=$config['cron_order_overdue_day'];      
        if($order_overdue_swhitch==1){
        	$time_order_overdue = $time-$order_overdue*24*3600;     
	        $time_order_overdue_start = strtotime(date('Y-m-d 00:00:00',$time_order_overdue));
	        $time_order_overdue_end = strtotime(date('Y-m-d 23:59:59',$time_order_overdue));

	        $end_time = $time-$order_overdue*24*3600;
			$order=Db::name('order')
			->where('status','Unpaid')
			->where('create_time','>=',$time_order_overdue_start)
			->where('create_time','<=',$time_order_overdue_end)
			->where('is_recycle', 0)
			->select()->toArray();
			foreach($order as $o){
				add_task([
					'type' => 'email',
					'description' => '#order'.$o['id'].lang('order_overdue_send_mail'),
					'task_data' => [
						'name'=>'order_overdue',//发送动作名称
						'order_id'=>$o['id'],//订单ID
					],		
				]);
				add_task([
					'type' => 'sms',
					'description' => '#order'.$o['id'].lang('order_overdue_send_sms'),
					'task_data' => [
						'name'=>'order_overdue',//发送动作名称
						'order_id'=>$o['id'],//订单ID
					],		
				]);					
			}
			unset($order);
		}
	}
	//订单未付款自动删除
	public function orderUnpaidDelete($config){
		$time=time();
		$order_unpaid_delete_swhitch=$config['cron_order_unpaid_delete_swhitch'];
		$order_unpaid_delete=$config['cron_order_unpaid_delete_day'];  
 
        if($order_unpaid_delete_swhitch==1){
        	$time_order_overdue = $time-$order_unpaid_delete*24*3600;     
	        $time_order_overdue_end = strtotime(date('Y-m-d 23:59:59',$time_order_overdue));

			$order=Db::name('order')
			->where('type','<>','artificial')
			->where('status','Unpaid')
			->where('create_time','<=',$time_order_overdue_end)
			->where('is_recycle', 0)
			->select()->toArray();

			$OrderModel = new OrderModel();
			foreach($order as $o){
				$OrderModel->deleteOrder(['id' => $o['id'], 'delete_host' => 1]);				
			}
			unset($order);
		}
	}
	//主机暂停、删除
	public function hostModule($config){
		$time=time();
		//暂停
		$suspend_switch=$config['cron_due_suspend_swhitch'];
		$suspend_day=$config['cron_due_suspend_day'];
        if($suspend_switch==1){
	        $time_suspend = $time-$suspend_day*24*3600;     
	        $time_suspend_start = strtotime(date('Y-m-d 00:00:00',$time_suspend));
	        $time_suspend_end = $time_suspend;       
			$suspend_host=Db::name('host')->whereIn('status','Active')
			->field('id,client_id')
			->where('due_time','>=',$time_suspend_start)
			->where('due_time','<=',$time_suspend_end)
			->where('billing_cycle', '<>', 'free')
			->where('billing_cycle', '<>', 'onetime')
			->select()->toArray();
			foreach($suspend_host as $h){			
				add_task([
					'type' => 'host_suspend',
					'description' => '#host#'.$h['id'].'#'.lang('host_suspend'),
					'task_data' => [
						'host_id'=>$h['id'],//主机ID
					],		
				]);				
			}
			unset($suspend_host);
		}
		//删除
		$terminate_switch=$config['cron_due_terminate_swhitch'];
		$terminate_day=$config['cron_due_terminate_day'];
		if($terminate_switch==1){
			$time_terminate = $time-$terminate_day*24*3600;
			$time_terminate_start = strtotime(date('Y-m-d 00:00:00',$time_terminate));
	        $time_terminate_end = $time_terminate;
			$terminate_host=Db::name('host')->whereIn('status','Active,Suspended')
			->field('id,client_id')
			->where('due_time','>=',$time_terminate_start)
			->where('due_time','<=',$time_terminate_end)
			->where('billing_cycle', '<>', 'free')
			->where('billing_cycle', '<>', 'onetime')
			->select()->toArray();
			foreach($terminate_host as $h){
				add_task([
					'type' => 'host_terminate',
					'description' => '#host#'.$h['id'].'#'.lang('host_delete'),
					'task_data' => [
						'host_id'=>$h['id'],//主机ID
					],		
				]);						
			}
			unset($terminate_host);
		}
	}
	
	private function cronConfig(){
		$configurations = (new ConfigurationModel)->index();
		$array = [];
		$time = time();
		foreach ($configurations as $v){
			if (strpos($v['setting'],'cron_')===0){
				if($v['setting']=='cron_lock_start_time' && $v['value']<=0){
					$this->configurationUpdate('cron_lock_start_time',$time-15*60);
					$array[$v['setting']] = $time-15*60;
				}
				if($v['setting']=='cron_lock_last_time' && $v['value']<=0){
					$this->configurationUpdate('cron_lock_last_time',$time-10*60);
					$array[$v['setting']] = $time-10*60;
				}
				if($v['setting']=='cron_lock_day_last_time' && $v['value']<=0){
					$this->configurationUpdate('cron_lock_day_last_time',strtotime('-1 day'));
					$array[$v['setting']] = strtotime('-1 day');
				}
				if($v['setting']=='cron_lock_five_minute_last_time' && $v['value']<=0){
					$this->configurationUpdate('cron_lock_five_minute_last_time',$time-10*60);
					$array[$v['setting']] = $time-10*60;
				}
				$array[$v['setting']] = (int)$v['value'];
			}		
		}
		return $array;
	}
	
	//修改设置
	private function configurationUpdate($name,$value){
		Db::name('configuration')->where('setting',$name)->data(['value'=>$value])->update();
	}

    /**
     * 时间 2022-5-25
     * @title 下游同步商品信息
     * @desc 下游同步商品信息
     * @author theworld
     * @version v1
     */
    public function downstreamSyncProduct()
    {
    	$SupplierModel = new SupplierModel();
    	$supplier = $SupplierModel->select()->toArray();
    	$supplierArr = [];
    	foreach ($supplier as $key => $value) {
    		$supplierArr[$value['id']] = $value;
    	}

    	$UpstreamProductModel = new UpstreamProductModel();
    	$UpstreamLogic = new UpstreamLogic();
    	$SelfDefinedFieldModel = new SelfDefinedFieldModel();
    	$product = $UpstreamProductModel->select()->toArray();
    	/*$productArr = [];
    	foreach ($product as $key => $value) {
    		$productArr[$value['supplier_id']][$value['upstream_product_id']] = ['id' => $value['product_id'], 'profit_percent' => $value['profit_percent'],'profit_type'=>$value['profit_type']];
    	}*/
    	foreach ($product as $key => $value) {
    		$type = $supplierArr[$value['supplier_id']]['type'] ?? '';
    		$res = $UpstreamLogic->upstreamProductDetail(['type' => $type, 'url' => $supplierArr[$value['supplier_id']]['url'] ?? '', 'id' => $value['upstream_product_id'],'supplier_id'=>$value['supplier_id']]);
    		if(isset($res['data']) && !empty($res['data'])){
    			$profit_percent = $value['profit_percent'];
    			$profit_type = $value['profit_type'];
    			if ($profit_type==1){
                    $price = $res['data']['price'] ?? 0;
                    $price = bcadd((string)$price,$profit_percent,2);
                }else{
                    $price = $res['data']['price'] ?? 0;
                    $price = $price*(100+$profit_percent);
                    $price = bcdiv((string)$price, '100', 2);
                }

        		// 魔方财务特殊处理
        		if ($type=='finance'){
                    $res['data']['pay_type'] = $res['data']['pay_type']=='recurring'?'recurring_prepayment':$res['data']['pay_type'];
                }
    			ProductModel::update([
    				'pay_type' => $res['data']['pay_type'] ?? 'recurring_prepayment',
	                'price' => $price,
	                'cycle' => $res['data']['cycle'] ?? '',
    			], ['id' => $value['id']]);
    		}
    		$SelfDefinedFieldModel->saveUpstreamSelfDefinedField([
    			'type'				=> $type,
    			'product_id'		=> $value['product_id'],
    			'self_defined_field'=> $res['self_defined_field'],
    		]);
    	}
    	foreach ($supplier as $key => $value) {
    		// 从上游商品拉取
        	$res = $UpstreamLogic->upstreamProductList(['type' => $value['type'], 'url' => $value['url']]);
        	/*foreach ($res['list'] as $k => $v) {
        		if(isset($productArr[$value['id']][$v['id']])){
        			$id = $productArr[$value['id']][$v['id']]['id'];
        			$profit_percent = $productArr[$value['id']][$v['id']]['profit_percent'];
        			$profit_type = $productArr[$value['id']][$v['id']]['profit_type'];
        			if ($profit_type==1){
                        $price = $v['price'] ?? 0;
                        $price = bcadd($price,$profit_percent,2);
                    }else{
                        $price = $v['price'] ?? 0;
                        $price = $price*(100+$profit_percent);
                        $price = bcdiv((string)$price, '100', 2);
                    }

            		// 魔方财务特殊处理
            		if ($value['type']=='finance'){
                        $v['pay_type'] = $v['pay_type']=='recurring'?'recurring_prepayment':$v['pay_type'];
                    }
        			ProductModel::update([
        				'pay_type' => $v['pay_type'] ?? 'recurring_prepayment',
		                'price' => $price,
		                'cycle' => $v['cycle'] ?? '',
        			], ['id' => $id]);
        		}
        	}*/
        	if(isset($res['list'][0]['id'])){
        		$UpstreamLogic->upstreamProductDownloadResource(['type' => $value['type'], 'url' => $value['url'], 'id' => $res['list'][0]['id']]);
        	}
        	
    	}
    	
    }

    /**
     * 时间 2024-03-19
     * @title 删除到期回收站订单
     * @desc  删除到期回收站订单
     * @author hh
     * @version v1
     */
    public function deleteRecycleOrder()
    {
    	$time = time();

    	$OrderModel = new OrderModel();
    	$id = $OrderModel->where('is_recycle', 1)->where('will_delete_time', '>', 0)->where('will_delete_time', '<=', $time)->limit(1000)->column('id');
    	if(!empty($id)){
    		$OrderModel->batchDeleteOrder(['id'=>$id, 'delete_host'=>1], 'recycle_bin');
    	}
    }


}
