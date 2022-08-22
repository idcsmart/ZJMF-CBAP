<?php 
namespace app\common\logic;
use think\facade\Db;
use app\admin\model\PluginModel;
use app\common\model\NoticeSettingModel;
use app\common\model\SmsTemplateModel;
use app\common\model\CountryModel;
use app\admin\model\SmsLogModel;
use app\common\model\ConfigurationModel;
/**
 * @title 短信发送逻辑类
 * @desc 短信发送逻辑类
 * @use app\common\logic\SmsLogic
 */
class SmsLogic
{
    /**
     * 时间 2022-05-19
     * @title 基础发送
     * @desc 基础发送
     * @author xiong
     * @version v1
     * @param array
     * @param string param.sms_name - 短信插件标识名 
     * @param string param.phone_code - 手机区号 
     * @param string param.phone - 手机号
     * @param string param.content - 短信内容
     * @param array param.template_param - 模板要替换的参数
     */
    public function sendBase($param)
    {
        $param['phone_code'] = str_replace('+','',$param['phone_code']);
		if(!empty($param['phone_code'])){		
			$country = (new CountryModel())->checkPhoneCode($param['phone_code']);
			if(!$country){
				return ['status'=>400, 'msg'=>lang('send_sms_area_code_error')];//区号错误
			}
		}else{
			$param['phone_code'] = '';
		}
		$data=[
			'content' => $param['content'],
			'template_param' => $param['template_param'],
			'sms_name' => $param['sms_name'],
		];
		if(empty($param['phone'])){
			return ['status'=>400, 'msg'=>lang('sms_phone_number_cannot_be_empty')];//手机号不能为空
		}
		if($param['phone_code'] == '86' || empty($param['phone_code'])){	
			$data['mobile'] = $param['phone'];
			$sms_methods = $this->smsMethods('sendCnSms',$data);
		}else{
			$data['mobile'] = '+'.$param['phone_code'].$param['phone'];
			$sms_methods = $this->smsMethods('sendGlobalSms',$data);
		}

		if($sms_methods['status'] == 'success'){
			return ['status'=>200, 'msg'=>lang('send_sms_success'), 'data'=>$sms_methods];//短信发送成功
		}else{
			return ['status'=>400, 'msg'=>lang('send_sms_error').' : '.$sms_methods['msg'], 'data'=>$sms_methods];//短信发送失败
		}
    }
    /**
     * 时间 2022-05-19
     * @title 发送
     * @desc 发送
     * @author xiong
     * @version v1
     * @param string param.phone_code - 手机区号 
     * @param string param.phone - 手机号 
     * @param string param.name - 动作名称
     * @param int param.client_id - 客户id
     * @param int param.host_id - 产品id
     * @param int param.order_id - 订单id
     * @param array param.template_param - 参数
     */
    public function send($param)
    {
		//读取发送动作
		$index_setting = (new NoticeSettingModel())->indexSetting($param['name']);
		//产品开通中
		if($param['name']=='host_pending'){
			if(empty($param['host_id'])){
				return ['status'=>400, 'msg'=>lang('id_error')];
			}
			$index_host = Db::name('host')->field('id,product_id,server_id,name,notes,first_payment_amount,renew_amount,billing_cycle,billing_cycle_name,billing_cycle_time,active_time,due_time,status,client_id,suspend_reason')->find($param['host_id']);
			$index_product = Db::name('product')->find($index_host['product_id']);
			if($index_product['creating_notice_sms_api']>0 && $index_product['creating_notice_sms_api_template']>0){
				$plugin = Db::name('plugin')->field('id,name')->find($index_product['creating_notice_sms_api']);
				$index_setting['sms_enable'] = 1;
				$index_setting['sms_name'] = $plugin['name'];
				$index_setting['sms_template'] = $index_product['creating_notice_sms_api_template'];
			}

		}
		//产品开通成功
		if($param['name']=='host_active'){
			if(empty($param['host_id'])){
				return ['status'=>400, 'msg'=>lang('id_error')];
			}
			$index_host = Db::name('host')->field('id,product_id,server_id,name,notes,first_payment_amount,renew_amount,billing_cycle,billing_cycle_name,billing_cycle_time,active_time,due_time,status,client_id,suspend_reason')->find($param['host_id']);
			$index_product = Db::name('product')->find($index_host['product_id']);
			if($index_product['created_notice_sms_api']>0 && $index_product['created_notice_sms_api_template']>0){
				$plugin = Db::name('plugin')->field('id,name')->find($index_product['created_notice_sms_api']);
				$index_setting['sms_enable'] = 1;
				$index_setting['sms_name'] = $plugin['name'];
				$index_setting['sms_template'] = $index_product['created_notice_sms_api_template'];
			}
		}
		if(empty($index_setting['name'])){
			return ['status'=>400, 'msg'=>lang('send_wrong_action_name')];//动作名称错误
		}	
		
		$template_param = $client = $order = $host = [];
		$client_id = 0;
		//订单
        if(!empty($param['order_id'])){
			$index_order = Db::name('order')->field('id,type,amount,create_time,status,gateway_name gateway,credit,client_id')->find($param['order_id']);
			$order = [
				'order_id' => $index_order['id'],
				'order_create_time' => $index_order['create_time'],
				'order_amount' => $index_order['amount'],
			];	
			$client_id = $param['client_id'] = $index_order['client_id'];	
			
		}
		//产品
        if(!empty($param['host_id'])){	
			$index_host = Db::name('host')->field('id,product_id,server_id,name,notes,first_payment_amount,renew_amount,billing_cycle,billing_cycle_name,billing_cycle_time,active_time,due_time,status,client_id,suspend_reason')->find($param['host_id']);
			$index_product = Db::name('product')->field('id,name')->find($index_host['product_id']);
			//获取自动化设置
			$config=(new ConfigurationModel())->cronList();
			$host = [
				'product_name' => $index_product['name'] .'-'.$index_host['name'],
				'product_marker_name' => $index_host['name'],
				'product_first_payment_amount' => $index_host['first_payment_amount'],
				'product_renew_amount' => $index_host['renew_amount'],
				'product_binlly_cycle' => $index_host['billing_cycle'],
				'product_active_time' => $index_host['active_time'],
				'product_due_time' => $index_host['due_time'],
				'product_suspend_reason' => $index_host['suspend_reason'],
				'renewal_first' => $config['cron_due_renewal_first_day'],
				'renewal_second' => $config['cron_due_renewal_second_day'],
			];	
			$client_id = $param['client_id'] = $index_host['client_id'];		
		}
		//客户
        if(!empty($param['client_id'])){
			$index_client = Db::name('client')->field('id,username,email,phone_code,phone,company,country,address,language,notes,status,create_time register_time,last_login_time,last_login_ip,credit')->find($param['client_id']);
			if($index_client['username']){
				$account = $index_client['username'];
			}else if($index_client['phone']){
				$account = $index_client['phone_code'].$index_client['phone'];
			}else if($index_client['email']){
				$account = $index_client['email'];
			}	
			
			$client = [
				'client_register_time' => $index_client['register_time'],
				'client_username' => $index_client['username'],
				'client_email' => $index_client['email'],
				'client_phone' => $index_client['phone_code'].'-'.$index_client['phone'],
				'client_company' => $index_client['company'],
				'client_last_login_time' => $index_client['last_login_time'],
				'client_last_login_ip' => $index_client['last_login_ip'],
				'account' => $account,
			];
			$client_id = $param['client_id'];	
			$param['phone_code'] = $index_client['phone_code'];
			$param['phone'] = $index_client['phone'];
		}		
		
		if(!empty($param['phone_code'])){
			$param['phone_code'] = str_replace('+','',$param['phone_code']);
		}
		if($index_setting['sms_enable'] == 0){
			return ['status'=>400, 'msg'=>lang('send_sms_action_not_enabled')];//短信动作发送未开启
		}
		if($param['phone_code'] == '86' || empty($param['phone_code'])){
			if(empty($index_setting['sms_name'])){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_not_set_domestic')];//国内短信发送接口未设置
			}
			if($index_setting['sms_template'] == 0){
				return ['status'=>400, 'msg'=>lang('send_sms_template_not_set_domestic')];//国内短信发送模板未设置
			}			
			$index_sms_template = (new SmsTemplateModel())->indexSmsTemplate(['name'=>$index_setting['sms_name'],'id'=>$index_setting['sms_template']]);
			if($index_sms_template['type']!=0 || empty($index_sms_template)){
				return ['status'=>400, 'msg'=>lang('send_sms_template_is_not_exist_domestic')];//国内短信模板不存在
			}
			if ($index_sms_template['status'] != 2){
				return ['status'=>400, 'msg'=>lang('sms_template_review_before_sending')];
			}	
		}else{
			
			if(empty($index_setting['sms_global_name'])){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_not_set_global')];//国际短信发送接口未设置
			}
			if($index_setting['sms_global_template'] == 0){
				return ['status'=>400, 'msg'=>lang('send_sms_template_not_set_global')];//国际短信发送模板未设置
			}
			$index_sms_template = (new SmsTemplateModel())->indexSmsTemplate(['name'=>$index_setting['sms_global_name'],'id'=>$index_setting['sms_global_template']]);
			if($index_sms_template['type']!=1 || empty($index_sms_template)){
				return ['status'=>400, 'msg'=>lang('send_sms_template_is_not_exist_global')];//国际短信模板不存在
			}	
			if ($index_sms_template['status'] != 2){
				return ['status'=>400, 'msg'=>lang('sms_template_review_before_sending')];
			}
		}

		//全局参数
		$setting = ['website_name','website_url'];
		$configuration=configuration($setting);
		$system = [
			'system_website_name'=>$configuration['website_name'],
			'system_website_url'=>$configuration['website_url'],
		];

		if(!empty($param['template_param'])) $template_param = $param['template_param'];
		$template_param=array_merge($system,$client,$order,$host,$template_param);
		
		$data=[
			'phone_code' => $param['phone_code']?:'',
			'phone' => $param['phone'],
			'content' => $index_sms_template['content'],
			'template_param' => $template_param,
			'sms_name' => $index_setting['sms_name'],
		];
		$send_result = $this->sendBase($data);	
		$log = [       
            'phone_code' => $data['phone_code'],
            'phone' => $data['phone'],
            'template_code' => $index_sms_template['template_id'],
            'content' => $send_result['data']['content'],
            'status' => ($send_result['status'] == 200)?1:0,
			'fail_reason' => ($send_result['status'] == 200)?'':$send_result['msg'],			
            'rel_id' => $client_id,
            'type' => 'client',
			'ip' =>  empty($param['ip'])?'':$param['ip'],
			'port' =>  empty($param['port'])?'':$param['port'],
        ];
		(new SmsLogModel())->createSmsLog($log);
		unset($send_result['data']);	
		return $send_result;	
    }
	//短信接口调用
	private function smsMethods($cmd,$param)
	{
		//短信接口判断
		$sms = (new PluginModel())->pluginList(['module'=>'sms']);				
		$sms_type = array_column($sms['list'],"sms_type","name");	
		$sms_status = array_column($sms['list'],"status","name");
		if(strpos($cmd,"sendCn")!==false){
		    if(empty($sms_type[$param['sms_name']])){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_is_not_exist_domestic')];//国内短信接口不存在
			}
			if($sms_status[$param['sms_name']]==0){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_is_disabled_domestic')];//国内短信接口已禁用
			}else if($sms_status[$param['sms_name']]==3){
			    return ['status'=>400, 'msg'=>lang('send_sms_interface_not_installed_domestic')];//国内短信接口未安装
			}
			
		}else if(strpos($cmd,"sendGlobal")!==false){
		    if(empty($sms_type[$param['sms_name']])){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_is_not_exist_global')];//国际短信接口不存在
			}
		    if($sms_status[$param['sms_name']]==0){
				return ['status'=>400, 'msg'=>lang('send_sms_interface_is_disabled_global')];//国际短信接口已禁用
			}else if($sms_status[$param['sms_name']]==3){
			    return ['status'=>400, 'msg'=>lang('send_sms_interface_not_installed_global')];//国际短信接口未安装
			}			
		}
		//提交到接口
		
		$class = get_plugin_class($param['sms_name'],'sms');
		if (!class_exists($class)) {
			return ['status'=>400, 'msg'=>lang('send_sms_interface_is_not_exist')];//短信接口不存在
		}
		$methods = get_class_methods($class)?:[];
		if(!in_array($cmd,$methods)){
			return ['status'=>400, 'msg'=>lang('send_sms_interface_not_supported')];//短信接口不支持
		}
		$sms_class = new $class();
		$config = $sms_class->getConfig();
		//发送
		$data = [
			'mobile' => $param['mobile'],
			'content' => $param['content'],
			'templateParam' => $param['template_param'],
			'config' => $config?:[],
		];
		return $sms_class->$cmd($data);
		
		
	}
	
}