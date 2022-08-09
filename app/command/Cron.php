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
		$this->minuteCron();// 每分钟执行一次hook需要
		$config = $this->cronConfig();	
		$this->configurationUpdate('cron_lock_start_time',time());
		//锁
		if(!empty($config['cron_lock'])){
			return false;
		}
		//最后执行时间判断
		if(((time() - $config["cron_lock_last_time"]) < 5*60)){
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
		if(date('Y-m-d',$config['cron_lock_day_last_time'])==date('Y-m-d')){
			return false;
		}
		$this->hostDue($config);//主机续费提示
		$output->writeln('续费提醒结束:'.date('Y-m-d H:i:s'));
		hook('daily_cron');// 每日执行一次定时任务钩子
		$this->configurationUpdate('cron_lock_day_last_time',time());
	}
	// 每分钟执行一次
	public function minuteCron(){
		hook('minute_cron');// 每分钟执行一次定时任务钩子
	}
	// 每五分钟执行一次
	public function fiveMinuteCron($config,$output){
		if((time()-$config['cron_lock_five_minute_last_time'])<5*60){
			return false;
		}
		$this->hostModule($config);// 主机暂停、删除
		$output->writeln('自动暂停、删除结束:'.date('Y-m-d H:i:s'));
		$this->configurationUpdate('cron_lock_five_minute_last_time',time());
	}
	//主机续费提醒	
	public function hostDue($config){
		$renewal_first_swhitch=$config['cron_due_renewal_first_swhitch'];
		$renewal_first_day=$config['cron_due_renewal_first_day'];
		$renewal_second_swhitch=$config['cron_due_renewal_second_swhitch'];
		$renewal_second_day=$config['cron_due_renewal_second_day'];
        $time=time();
		$host=Db::name('host')->whereIn('status','Active,Suspended')->where('due_time','>',0);
		foreach($host as $h){
			
			//第一次提醒
			$end_time1 = $time+$renewal_first_day*24*3600;
			if($renewal_first_swhitch==1 && date('Y-m-d',$h['due_time'])==date('Y-m-d',$end_time1)){
				add_task([
					'type' => 'email',
					'description' => '第一次客户续费提醒,发送邮件',
					'task_data' => [
						'name'=>'host_renewal_one',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);
				add_task([
					'type' => 'sms',
					'description' => '第一次客户续费提醒,发送短信',
					'task_data' => [
						'name'=>'host_renewal_one',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);				
			}
			//第二次提醒
			$end_time2 = $time+$renewal_second_day*24*3600;
			if($renewal_second_swhitch==1 && date('Y-m-d',$h['due_time'])==date('Y-m-d',$end_time2)){
				add_task([
					'type' => 'email',
					'description' => '第二次客户续费提醒,发送邮件',
					'task_data' => [
						'name'=>'host_renewal_two',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);
				add_task([
					'type' => 'sms',
					'description' => '第二次客户续费提醒,发送短信',
					'task_data' => [
						'name'=>'host_renewal_two',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);					
			}
		}
	}	
	//主机逾期提醒	
	public function hostOverdue($config){
		$overdue_first_swhitch=$config['cron_overdue_first_swhitch'];
		$overdue_first_day=$config['cron_overdue_first_day'];
		$overdue_second_swhitch=$config['cron_overdue_second_swhitch'];
		$overdue_second_day=$config['cron_overdue_second_day'];
		$overdue_third_swhitch=$config['cron_overdue_third_swhitch'];
		$overdue_third_day=$config['cron_overdue_third_day'];
        $time=time();
		$host=Db::name('host')
		->whereIn('status','Active,Suspended')
		->where('due_time','>',0)
		->where('due_time','<=',$time)
		->where('billing_cycle', '<>', 'free')
        ->where('billing_cycle', '<>', 'onetime')
		->select()->toArray();
		foreach($host as $h){
			
			//第一次提醒
			$end_time1 = $time-$overdue_first_day*24*3600;
			if($overdue_first_swhitch==1 && date('Y-m-d',$h['due_time'])==date('Y-m-d',$end_time1)){
				add_task([
					'type' => 'email',
					'description' => '第一次客户续费提醒,发送邮件',
					'task_data' => [
						'name'=>'host_renewal_one',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);
				add_task([
					'type' => 'sms',
					'description' => '第一次客户续费提醒,发送短信',
					'task_data' => [
						'name'=>'host_renewal_one',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);				
			}
			//第二次提醒
			$end_time2 = $time-$overdue_second_day*24*3600;
			if($overdue_second_swhitch==1 && date('Y-m-d',$h['due_time'])==date('Y-m-d',$end_time2)){
				add_task([
					'type' => 'email',
					'description' => '第二次客户续费提醒,发送邮件',
					'task_data' => [
						'name'=>'host_renewal_two',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);
				add_task([
					'type' => 'sms',
					'description' => '第二次客户续费提醒,发送短信',
					'task_data' => [
						'name'=>'host_renewal_two',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);					
			}
			//第三次提醒
			$end_time3 = $time-$overdue_third_day*24*3600;
			if($overdue_third_swhitch==1 && date('Y-m-d',$h['due_time'])==date('Y-m-d',$end_time3)){
				add_task([
					'type' => 'email',
					'description' => '第三次客户续费提醒,发送邮件',
					'task_data' => [
						'name'=>'host_renewal_two',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);
				add_task([
					'type' => 'sms',
					'description' => '第三次客户续费提醒,发送短信',
					'task_data' => [
						'name'=>'host_renewal_two',//发送动作名称
						'host_id'=>$h['id'],//主机ID
					],		
				]);					
			}
		}
	}
	//主机暂停、删除
	public function hostModule($config){
		$suspend_switch=$config['cron_due_suspend_swhitch'];
		$suspend_day=$config['cron_due_suspend_day'];
		$terminate_switch=$config['cron_due_terminate_swhitch'];
		$terminate_day=$config['cron_due_terminate_day'];
        $time=time();
		$host=Db::name('host')->where('status','Active')->where('due_time','>',0);
		foreach($host as $h){
			$end_time_suspend = $time-$suspend_day*24*3600;
			if($suspend_switch==1 && date('Y-m-d',$h['due_time'])==date('Y-m-d',$end_time_suspend)){
				add_task([
					'type' => 'host_suspend',
					'description' => '主机暂停',
					'task_data' => [
						'host_id'=>$h['id'],//主机ID
					],		
				]);				
			}
			$end_time_terminate = $time-$terminate_day*24*3600;
			if($terminate_switch==1 && date('Y-m-d',$h['due_time'])==date('Y-m-d',$end_time_terminate)){
				add_task([
					'type' => 'host_terminate',
					'description' => '主机删除',
					'task_data' => [
						'host_id'=>$h['id'],//主机ID
					],		
				]);					
			}
		
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
}
