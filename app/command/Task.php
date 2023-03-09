<?php
declare (strict_types = 1);

namespace app\command;
use think\facade\Db;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\common\logic\SmsLogic;
use app\common\logic\EmailLogic;
use app\common\model\HostModel;
class Task extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('task')
            ->setDescription('the task command');
    }

    protected function execute(Input $input, Output $output)
    {
		ignore_user_abort(true); 
		set_time_limit(0);
		$task_time = Db::name('configuration')->where('setting','task_time')->value('value');
		if(empty($task_time)){
			Db::name('configuration')->where('setting','task_time')->data(['value'=>time()])->update();
			$task_time = time();
		}
		$programEnd=true;
		do{
			
			if((time()-$task_time)>=2*60){
				$programEnd=false;
				Db::name('configuration')->where('setting','task_time')->data(['value'=>0])->update();
			}
			$this->taskWait();
			//sleep(1); 
		}while($programEnd); 
		
		
		
    }
	//队列
	public function taskWait(){
		$task_lock = file_exists(__DIR__.'/task.lock') ? file_get_contents(__DIR__.'/task.lock') : 0; 
			
		if(empty($task_lock) || time()>($task_lock+2*60)){
			file_put_contents(__DIR__.'/task.lock', time());

			Db::startTrans();

			try{
				$task_wait = Db::name('task_wait')->limit(10)
	                //->lock(true) # 加悲观锁,不允许其它进程访问(supervisor开启5个进程)
	                ->whereIn('status',['Wait','Failed'])
	                ->where('retry','<=',3) # 重试次数小于等于3
	                ->select()->toArray();//取10条数据

	            Db::name('task_wait')->where('retry','>',3)
	                ->whereOr('status','Finish')
	                ->delete(); # 删除重试次数大于3或者状态已完成的任务

	            Db::commit();
			}catch(\think\db\exception\PDOException $e){
				// file_put_contents(__DIR__.'/task.lock', 0);
                Db::rollback();
				return ;
			}catch(\Exception $e){
				// file_put_contents(__DIR__.'/task.lock', 0);
                Db::rollback();
				return ;
			}

			if($task_wait){
				foreach($task_wait as $v){
					$start = Db::name('task_wait')->where('id', $v['id'])->whereIn('status',['Wait','Failed'])->update(['status'=>'Exec']);
					if(empty($start)){
						continue;
					}
					$task_data = json_decode($v['task_data'],true);
					if(strpos($v['type'],'host_')===0){
						$result = $this->host(str_replace('host_','',$v['type']),$task_data);
					}else if ($v['type']=='email'){						
						$result = $this->email($task_data);						
					}else if ($v['type']=='sms'){
						$result = $this->sms($task_data);						
					}else{
						$result = $this->hook($v);
					}
					if($v['task_id']>0){ 
						$task_update = [
							'status' => $result['status'],
							'finish_time' => time(),
							'fail_reason' => empty($result['msg'])?'':$result['msg'],
						];
						Db::name('task')->where('id',$v['task_id'])->data($task_update)->update();

						Db::name('task_wait')->where('task_id',$v['task_id'])->data([
                            'status' => $result['status'],
                            'finish_time' => time(),
                            'retry' => $v['retry']+1
                        ])->update();
					}else{
						Db::name('task_wait')->where('id', $v['id'])->update(['status'=>$result['status'] ]);
					}

				}
                // Db::commit(); # 当前进程的任务执行完毕,释放锁
			}else{
                // Db::commit();
				sleep(3);
			}

			file_put_contents(__DIR__.'/task.lock', 0);
		}
		

	}

	//主机
	public function host($action,$task_data){
		try {	
			$action=strtolower($action).'Account';
			$HostModel = new HostModel();
			$HostModelAction = get_class_methods($HostModel);
			if(in_array($action,$HostModelAction)){
				if($action=='suspendAccount'){
					$send_result = $HostModel->$action(['suspend_reason'=>'产品到期暂停','id'=>$task_data['host_id']]);
				}else if($action=='upgradeAccount'){
					$send_result = $HostModel->$action($task_data['upgrade_id']);
				}else{
					$send_result = $HostModel->$action($task_data['host_id']);
				}
				if($send_result['status']==200){
					$result['status'] = 'Finish';
				}else{
					$result['status'] = 'Failed';
					$result['msg'] = $send_result['msg'];
				}		
			}else{
				$result['status'] = 'Failed';
				$result['msg'] = 'Executive function not found';
			}	
			return $result;
		} catch (\Exception $e) {
			$result['status'] = 'Failed';
			$result['msg'] = $e->getMessage();			
			return $result;
		}
	}		
	//邮件
	/*
	数据格式：
	[
	'name'=>'client_register',//发送动作名称
	'email'=>'111@qq.com',
	'client_id'=>33,//客户ID，要发送客户相关的需要这个参数
	'order_id'=>33,//订单ID，要发送订单表相关的需要这个参数
	'host_id'=>33,//主机ID，要发送主机表相关的需要这个参数
	'data'=>['code'=>'44gf'],//其它参数
	]
	*/
	public function email($task_data){
		try {
			$send_result = (new EmailLogic)->send($task_data);
			if($send_result['status']==200){
				$result['status'] = 'Finish';
			}else{
				$result['status'] = 'Failed';
				$result['msg'] = $send_result['msg'];
			}
			return $result;
		} catch (\Exception $e) {
			$result['status'] = 'Failed';
			$result['msg'] = $e->getMessage();				
			return $result;
		}
	}	
	//短信
	/*
	数据格式：
	[
	'name'=>'client_register',//发送动作名称
	'phone_code'=>'86',
	'phone'=>'17646046961',
	'client_id'=>33,//客户ID，要发送客户相关的需要这个参数
	'order_id'=>33,//订单ID，要发送订单表相关的需要这个参数
	'host_id'=>33,//主机ID，要发送主机表相关的需要这个参数
	'data'=>['code'=>44gf],//其它参数
	]
	*/
	public function sms($task_data){
		try {
			$send_result = (new SmsLogic)->send($task_data);	
			if($send_result['status']==200){
				$result['status'] = 'Finish';
			}else{
				$result['status'] = 'Failed';
				$result['msg'] = $send_result['msg'];
			}
			return $result;
		} catch (\Exception $e) {
			$result['status'] = 'Failed';
			$result['msg'] = $e->getMessage();				
			return $result;
		}
	}	
	//hook
	public function hook($data){
		try {
			$result_hook = hook('task_run',$data);
			$result_hook = array_values(array_filter($result_hook ?? []));
			if($result_hook[0]){
				$result['status']=$result_hook[0];
			}else{
				$result['status'] = 'Failed';
			}
			return $result;
		} catch (\Exception $e) {
			$result['status'] = 'Failed';
			$result['msg'] = $e->getMessage();				
			return $result;
		}
	}
}
