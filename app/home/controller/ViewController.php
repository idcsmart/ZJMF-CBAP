<?php
namespace app\home\controller;

use think\facade\Db;
use think\Request;
use think\facade\View;
use app\common\model\TaskWaitModel;
use app\common\logic\SmsLogic;
use app\common\logic\EmailLogic;
use app\admin\model\PluginModel;
class ViewController extends HomeBaseController
{
	public function index()
    {
    	$param = $this->request->param();
		$data = [
			'title'=>'首页-智简魔方',		
		];
		$data['template_catalog'] = DIR_ADMIN;
		$data['themes'] = 'default';
		$tplName = $param['view_html'];
		View::config(['view_path' => '../public/clientarea/template/default/']);
		return View::fetch("/".$tplName,$data);
    }	
	
	public function plugin()
    {
    	$param = $this->request->param();
		$data['themes'] = 'default';
		$plugin_id = $param['plugin_id'];
		$tplName = $param['view_html']; 
		$addon = (new PluginModel())->plugins('addon')['list'];
	    $addon = array_column($addon,'name','id');
		$name=parse_name($addon[$plugin_id]);
		if(empty($name)){
		    exit('not found template1');
		}
		$tpl = '../public/plugins/addon/'.$name.'/template/clientarea/';
		
		if(file_exists($tpl.$tplName.".php")){
			$content=$this->view('header',$data);
			$content.=$this->pluginView($tplName,$data,$name);
			$content.=$this->view('footer',$data);
			return $content;
		}else{
			exit('not found template');
		}
		
    }
	
	public function view($tplName, $data){
        View::config(['view_path' => '../public/clientarea/template/default/']);
		return View::fetch('/'.$tplName,$data);
    }
	
	public function pluginView($tplName, $data, $name){
        View::config(['view_path' => '../public/plugins/addon/'.$name.'/template/clientarea/']);
		return View::fetch('/'.$tplName,$data);
    }
	public function task(){
		exit('no');
	    /*
	    $task_update = [
							'status' => 'Exec',
							'finish_time' => time(),
							'fail_reason' => '',
						];
						Db::name('task')->where('id',54)->data($task_update)->update();
	    
	    exit('zzzz');*/
	    $task=
	    [
    	    [
    	        "name"=>"client_register",
    	        "email"=>"3004755363@qq.com",
    	        "client_id"=>"135"
    	    ],
    	    [
    	        "name"=>"order_pay",
    	        "email"=>"3004755363@qq.com",
    	        "client_id"=>"135"
    	    ],
	    ];
	    for($i=0;$i<100;$i++){
	        $time = time();
    	    foreach($task as $v){
    	        
    	        add_task([
                	'type' => 'email',
                	'description' => '批量测试',
                	'task_data' => $v,		
                ]);
    	        
    	    }
	    }
	    exit('success');
	    $task = Db::name('task')->find(54);
	    $task_data = json_decode($task['task_data'],true);
	    $time = time();
	    $wait=[
    		'type' => $task['type'],   
    		'rel_id' => 0, 
    		'status' => 'Wait',
    		'retry' => 0,
    		'description' => $task['description'],
    		'task_data' => json_encode($task_data),
    		'start_time' => $time, 
    		'finish_time' => 0,  
            'create_time' => $time,  
            'update_time' => $time,   
            'task_id'=>$task['id'],
    	];
	    $re=Db::name('task_wait')->insert($wait);
	    dump($wait);
	    dump($re);
	    exit; 
	    
	    $data = [
			'name'=>$task_data['name'],
			'phone_code'=>empty($task_data['phone_code'])?'86':$task_data['phone_code'],
			'phone'=>$task_data['phone'],
			'client_id'=>empty($task_data['client_id'])?'':$task_data['client_id'],
			'order_id'=>empty($task_data['order_id'])?'':$task_data['order_id'],
			'host_id'=>empty($task_data['host_id'])?'':$task_data['host_id'],
			'template_param'=>empty($task_data['data'])?[]:$task_data['data'],
		];
	
		$send_result = (new SmsLogic)->send($data);	
		    
	    
	    
	    
	    
		$noticeActionCreate = [
			'name'=>'add_ticket_notice',
			'name_lang'=>'工单通知',
			'sms_name'=>'Submail',
			'sms_template'=>[
				'title'=>'工单通知',
				'content'=>'@var(username),您的工单：【@var(subject)】正在处理中，请耐心等待',
			],
			'sms_global_name'=>'Submail',
			'sms_global_template'=>[
				'title'=>'工单通知',
				'content'=>'@var(username),您的工单：【@var(subject)】正在处理中，请耐心等待',
			],
			'email_name'=>'工单通知',
			'email_template'=>[
				'title'=>'工单通知',
				'content'=>'有新的工单',
			]
		];
	//	$result=notice_action_create($noticeActionCreate);
		$result=notice_action_delete('add_ticket_notice');
		var_dump($result);exit; 
		 
		
		$task_data = [
			'name'=>'client_register',//发送动作名称
			'email'=>'111@qq.com',
			'client_id'=>33,//客户ID，要发送客户相关的需要这个参数
			'order_id'=>33,//订单ID，要发送订单表相关的需要这个参数
			'host_id'=>33,//主机ID，要发送主机表相关的需要这个参数
			'data'=>['code'=>'44gf'],//其它参数
		];
		
		$param = [
			'type' => 'host',
			'rel_id' => 0,
			'description' => 'hook测试',
			'task_data' => $task_data,		
		];
		add_task($param);
		
	}
}
