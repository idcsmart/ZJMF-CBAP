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
			$task_time = Db::name('configuration')->where('setting','task_time')->value('value');
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
		Db::startTrans();
		$task_lock = Db::name('configuration')->where('setting','task')->lock(true)->value('value');
			
		if(empty($task_lock)){
			Db::name('configuration')->where('setting','task')->data(['value'=>1])->update();
			$task_wait = Db::name('task_wait')->limit(10)->select()->toArray();//取10条数据				
			if($task_wait) Db::name('task_wait')->whereIn('id',array_column($task_wait,'id'))->delete();
			Db::commit();
			Db::name('configuration')->where('setting','task')->data(['value'=>0])->update();
			if($task_wait){
				foreach($task_wait as $v){
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
					}
				}
			}
		}else{
			Db::commit();
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
