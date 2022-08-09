<?php 
namespace app\common\model;

use think\Model;
use think\Db;
/**
 * @title 用户模型
 * @desc 用户模型
 * @use app\common\model\ConfigurationModel
 */
class ConfigurationModel extends Model
{
	
	protected $name = 'configuration';
	protected $pk = 'setting';
	private $config=[
		'system'=>[
			'lang_admin',
			'lang_home',
			'lang_home_open',
			'maintenance_mode',
			'maintenance_mode_message',
			'website_name',
			'website_url',
			'terms_service_url',
		],
		'login'=>[
			'login_phone_verify',
			'register_email',
			'register_phone',
		],
		'security'=>[
			'captcha_client_register',
			'captcha_client_login',
			'captcha_admin_login',
			'captcha_client_login_error',
			'captcha_width',
			'captcha_height',
			'captcha_length',
		],
		'currency'=>[
			'currency_code',
			'currency_prefix',
			'currency_suffix',
			'recharge_open',
			'recharge_min',
		],
		'cron'=>[
			'cron_due_suspend_swhitch',
			'cron_due_suspend_day',
			'cron_due_unsuspend_swhitch',
			'cron_due_terminate_swhitch',
			'cron_due_terminate_day',
			'cron_due_renewal_first_swhitch',
			'cron_due_renewal_second_swhitch',
			'cron_due_renewal_first_day',
			'cron_due_renewal_second_day',
			'cron_overdue_first_swhitch',
			'cron_overdue_second_swhitch',
			'cron_overdue_third_swhitch',
			'cron_overdue_first_day',
			'cron_overdue_second_day',
			'cron_overdue_third_day',
			'cron_ticket_swhitch',
			'cron_ticket_close_day',
			'cron_aff_swhitch',
		],
		'send'=>[
			'send_sms',
			'send_sms_global',
			'send_email',
		],
	];
	/**
	* 时间 2022-5-10
	* @title 获取所有配置项数据
	* @desc 获取所有配置项数据
	* @author xiong
	* @version v1
    * @return string [].setting - 配置项名称 
    * @return string [].value - 配置项值 
	*/	
	public function index()
	{
		return $this->field('setting,value')->select()->toArray();
	}	
	/**
	* 时间 2022-5-10
	* @title 获取系统设置
	* @desc 获取系统设置
	* @author xiong
	* @version v1
	* @return  string lang_admin - 后台默认语言
	* @return  int lang_home_open - 前台多语言开关:1开启0关闭
	* @return  string lang_home - 前台默认语言
	* @return  int maintenance_mode - 维护模式开关:1开启0关闭
	* @return  string maintenance_mode_message - 维护模式内容
	* @return  string website_name - 网站名称
	* @return  string website_url - 网站域名地址
	* @return  string terms_service_url - 服务条款地址
	*/
	public function systemList()
	{
		$configuration = $this->index();
		foreach($configuration as $v){
			if(in_array($v['setting'], $this->config['system'])){
				if($v['setting'] == 'lang_home_open' || $v['setting'] == 'maintenance_mode'){
					$data[$v['setting']] = (int)$v['value'];
				}else{	
					$data[$v['setting']] = (string)$v['value'];
				}
			}
		}
		return $data;
	}
    /**
     * 时间 2022-05-10
     * @title 保存系统设置
     * @desc 保存系统设置
     * @author xiong
     * @version v1
     * @param  string param.lang_admin - 后台默认语言
     * @param  int param.lang_home_open - 前台多语言开关:1开启0关闭
     * @param  string param.lang_home - 前台默认语言
     * @param  int param.maintenance_mode - 维护模式开关:1开启0关闭
     * @param  string param.maintenance_mode_message - 维护模式内容
     * @param  string param.website_name - 网站名称
     * @param  string param.website_url - 网站域名地址
     * @param  string param.terms_service_url - 服务条款地址
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息	 
     */
	public function systemUpdate($param)
	{
		$admin = array_column(lang_list('admin'),'display_lang','display_lang');
		$home =  array_column(lang_list('home'),'display_lang','display_lang');
		if(empty($admin[$param['lang_admin']])){
			return ['status' => 400, 'msg' => lang('configuration_admin_default_language_error')];
		}
		if(empty($admin[$param['lang_home']])){
			return ['status' => 400, 'msg' => lang('configuration_home_default_language_error')];
		}
		$param['lang_home_open'] = intval($param['lang_home_open']);
		$param['maintenance_mode'] = intval($param['maintenance_mode']);
		$this->startTrans();
		try {
			foreach($this->config['system'] as $v){
				$list[]=[
					'setting'=>$v,
					'value'=>$param[$v],
				];
			}			
	    	$this->saveAll($list);
	        $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('update_fail')];
		}
		return ['status' => 200, 'msg' => lang('update_success')];
	}
	/**
	* 时间 2022-5-10
	* @title 获取登录设置
	* @desc 获取登录设置
	* @author xiong
	* @version v1
     * @return  int register_email - 邮箱注册开关:1开启0关闭
     * @return  int register_phone - 手机号注册开关:1开启0关闭
     * @return  int login_phone_verify - 手机号登录短信验证开关:1开启0关闭
	*/
	public function loginList()
	{
		
		$configuration = $this->index();
		foreach($configuration as $v){
			if(in_array($v['setting'], $this->config['login'])){
				$data[$v['setting']] = (int)$v['value'];
			}
		}
		return $data;
	}
    /**
     * 时间 2022-05-10
     * @title 保存登录设置
     * @desc 保存登录设置
     * @author xiong
     * @version v1
     * @param  int param.register_email - 邮箱注册开关:1开启0关闭
     * @param  int param.register_phone - 手机号注册开关:1开启0关闭
     * @param  int param.login_phone_verify - 手机号登录短信验证开关:1开启0关闭
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
	public function loginUpdate($param)
	{
		$this->startTrans();
		try {
			foreach($this->config['login'] as $v){
				$list[]=[
					'setting'=>$v,
					'value'=>intval($param[$v]),
				];
			}			
	    	$this->saveAll($list);
	        $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('update_fail')];
		}
		return ['status' => 200, 'msg' => lang('update_success')];
	}
	/**
	* 时间 2022-5-10
	* @title 获取安全设置
	* @desc 获取安全设置
	* @author xiong
	* @version v1
     * @return  int captcha_client_register - 客户注册图形验证码开关:1开启0关闭
     * @return  int captcha_client_login - 客户登录图形验证码开关:1开启0关闭
     * @return  int captcha_client_login_error - 客户登录失败图形验证码开关:1开启0关闭
     * @return  int captcha_admin_login - 管理员登录图形验证码开关:1开启0关闭
     * @return  int captcha_width - 图形验证码宽度
     * @return  int captcha_height - 图形验证码高度
     * @return  int captcha_length - 图形验证码字符长度
	*/
	public function securityList()
	{
		
		$configuration = $this->index();
		foreach($configuration as $v){
			if(in_array($v['setting'], $this->config['security'])){
				if($v=="captcha_width" || $v=="captcha_height"){
					$data[$v['setting']] = (float)$v['value'];
				} else{
					$data[$v['setting']] = (int)$v['value'];
				}
			}
		}
		return $data;
	}
    /**
     * 时间 2022-05-10
     * @title 保存安全设置
     * @desc 保存安全设置
     * @author xiong
     * @version v1
     * @param  int param.captcha_client_register - 客户注册图形验证码开关:1开启0关闭
     * @param  int param.captcha_client_login - 客户登录图形验证码开关:1开启0关闭
     * @param  int param.captcha_client_login_error - 客户登录失败图形验证码开关:1开启0关闭
     * @param  int param.captcha_admin_login - 管理员登录图形验证码开关:1开启0关闭
     * @param  int param.captcha_width - 图形验证码宽度
     * @param  int param.captcha_height - 图形验证码高度
     * @param  int param.captcha_length - 图形验证码字符长度
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
	public function securityUpdate($param)
	{
		$this->startTrans();
		try {
			foreach($this->config['security'] as $v){
				if($v=="captcha_width" || $v=="captcha_height") $param[$v]=round($param[$v],2);
				else $param[$v] = intval($param[$v]);
				$list[]=[
					'setting'=>$v,
					'value'=>$param[$v],
				];
			}			
	    	$this->saveAll($list);
	        $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('update_fail')];
		}
		return ['status' => 200, 'msg' => lang('update_success')];
	}
	/**
	* 时间 2022-5-10
	* @title 获取货币设置
	* @desc 获取货币设置
	* @author xiong
	* @version v1
	* @return  string currency_code - 货币代码
	* @return  string currency_prefix - 货币符号
	* @return  string currency_suffix - 货币后缀
	* @return  int recharge_open - 启用充值:1开启0关闭
	* @return  int recharge_min - 单笔最小金额
	*/
	public function currencyList()
	{
		
		$configuration = $this->index();
		foreach($configuration as $v){
			if(in_array($v['setting'], $this->config['currency'])){
				if($v['setting'] == 'recharge_open' || $v['setting'] == 'recharge_min'){
					$data[$v['setting']] = (float)$v['value'];
				}else{	
					$data[$v['setting']] = (string)$v['value'];
				}
			}
		}
		return $data;
	}
    /**
     * 时间 2022-05-10
     * @title 保存货币设置
     * @desc 保存货币设置
     * @author xiong
     * @version v1
     * @param  string param.currency_code - 货币代码
     * @param  string param.currency_prefix - 货币符号
     * @param  string param.currency_suffix - 货币后缀
     * @param  int param.recharge_open - 启用充值:1开启0关闭
     * @param  int param.recharge_min - 单笔最小金额
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息	 
     */
	public function currencyUpdate($param)
	{
		$this->startTrans();
		try {
			foreach($this->config['currency'] as $v){
				if($v == 'recharge_min'){
					$param[$v] = round($param[$v],2);
				}else if($v == 'recharge_open'){
					$param['recharge_open'] = intval($param['recharge_open']);
				}
				
				$list[] = [
					'setting'=>$v,
					'value'=>$param[$v],
				];
			}			
	    	$this->saveAll($list);
	        $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('update_fail')];
		}
		return ['status' => 200, 'msg' => lang('update_success')];
	}
	/**
	 * 时间 2022-5-10
	 * @title 获取自动化设置
	 * @desc 获取自动化设置
	 * @author xiong
	 * @version v1
     * @return int cron_shell - 自动化脚本
     * @return int cron_status - 自动化状态,正常返回success,不正常返回error
     * @return int cron_due_suspend_swhitch - 产品到期暂停开关 1开启，0关闭
     * @return int cron_due_suspend_day - 产品到期暂停X天后暂停
     * @return int cron_due_unsuspend_swhitch - 财务原因产品暂停后付款自动解封开关 1开启，0关闭
     * @return int cron_due_terminate_swhitch - 产品到期删除开关 1开启，0关闭
     * @return int cron_due_terminate_day - 产品到期X天后删除
     * @return int cron_due_renewal_first_swhitch - 续费第一次提醒开关 1开启，0关闭
     * @return int cron_due_renewal_first_day - 续费X天后到期第一次提醒
     * @return int cron_due_renewal_second_swhitch - 续费第二次提醒开关 1开启，0关闭
     * @return int cron_due_renewal_second_day - 续费X天后到期第二次提醒
     * @return int cron_overdue_first_swhitch - 产品逾期第一次提醒开关 1开启，0关闭
     * @return int cron_overdue_first_day - 产品逾期X天后第一次提醒
     * @return int cron_overdue_second_swhitch - 产品逾期第二次提醒开关 1开启，0关闭
     * @return int cron_overdue_second_day - 产品逾期X天后第二次提醒
     * @return int cron_overdue_third_swhitch - 产品逾期第三次提醒开关 1开启，0关闭
     * @return int cron_overdue_third_day - 产品逾期X天后第三次提醒
     * @return int cron_ticket_swhitch - 自动关闭工单开关 1开启，0关闭
     * @return int cron_ticket_close_day - 已回复状态的工单超过x小时后关闭
     * @return int cron_aff_swhitch - 推介月报开关 1开启，0关闭
	*/
	public function cronList()
	{
		
		$configuration = $this->index();
		foreach($configuration as $v){
			if(in_array($v['setting'], $this->config['cron'])){
				$data[$v['setting']] = (int)$v['value'];
			}
		}
		$data['cron_shell'] = 'php '. root_path() .'cron/cron.php';
		$data['cron_status'] = 'success';
		return $data;
	}
    /**
     * 时间 2022-05-10
     * @title 保存自动化设置
     * @desc 保存自动化设置
     * @author xiong
     * @version v1
     * @return int param.cron_due_suspend_swhitch - 产品到期暂停开关1开启，0关闭 required
     * @return int param.cron_due_suspend_day - 产品到期暂停X天后暂停 required
     * @return int param.cron_due_unsuspend_swhitch - 财务原因产品暂停后付款自动解封开关1开启，0关闭 required
     * @return int param.cron_due_terminate_swhitch - 产品到期删除开关1开启，0关闭 required
     * @return int param.cron_due_terminate_day - 产品到期X天后删除 required
     * @return int param.cron_due_renewal_first_swhitch - 续费第一次提醒开关1开启，0关闭 required
     * @return int param.cron_due_renewal_first_day - 续费X天后到期第一次提醒 required
     * @return int param.cron_due_renewal_second_swhitch - 续费第二次提醒开关1开启，0关闭 required
     * @return int param.cron_due_renewal_second_day - 续费X天后到期第二次提醒 required
     * @return int param.cron_overdue_first_swhitch - 产品逾期第一次提醒开关1开启，0关闭 required
     * @return int param.cron_overdue_first_day - 产品逾期X天后第一次提醒 required
     * @return int param.cron_overdue_second_swhitch - 产品逾期第二次提醒开关1开启，0关闭 required
     * @return int param.cron_overdue_second_day - 产品逾期X天后第二次提醒 required
     * @return int param.cron_overdue_third_swhitch - 产品逾期第三次提醒开关1开启，0关闭 required
     * @return int param.cron_overdue_third_day - 产品逾期X天后第三次提醒 required
     * @return int param.cron_ticket_swhitch - 自动关闭工单开关 1开启，0关闭 required
     * @return int param.cron_ticket_close_day - 已回复状态的工单超过x小时后关闭 required
     * @return int param.cron_aff_swhitch - 推介月报开关 1开启，0关闭 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息	 
     */
	public function cronUpdate($param)
	{
		//暂停和删除
		if($param['cron_due_suspend_day']>$param['cron_due_terminate_day'] && $param['cron_due_suspend_swhitch']==1 && $param['cron_due_terminate_swhitch']==1){
			return ['status' => 400, 'msg' => lang('configuration_cron_suspend_day_less_terminate_day')];//产品到期暂停天数应小于产品到期删除天数
		}
		//续费提醒
		if($param['cron_due_renewal_first_day']<$param['cron_due_renewal_second_day'] && $param['cron_due_renewal_first_swhitch']==1 && $param['cron_due_renewal_second_swhitch']==1){
			return ['status' => 400, 'msg' => lang('configuration_cron_renewal_first_day_less_renewal_second_day')];//第一次续费提醒天数应大于第二次续费提醒天数
		}
		//逾期天数
		$overdueday = [];
		if($param['cron_overdue_first_swhitch']==1){
			$overdueday[count($overdueday)] = $param['cron_overdue_first_day'];			
		}
		if($param['cron_overdue_second_swhitch']==1){
			$overdueday[count($overdueday)] = $param['cron_overdue_second_day'];			
		}
		if($param['cron_overdue_third_swhitch']==1){
			$overdueday[count($overdueday)] = $param['cron_overdue_third_day'];			
		}
		if($param['cron_due_terminate_swhitch']==1){
			$overdueday[count($overdueday)] = $param['cron_due_terminate_day'];			
		}
		$overdueday_sort = $overdueday;
		sort($overdueday_sort);
		$overdueday_array_diff=array_diff_assoc($overdueday,$overdueday_sort);
		if(!empty($overdueday_array_diff)){
			return ['status' => 400, 'msg' => lang('configuration_cron_overdue_day_less_terminate_day')];//第一次逾期提醒天数应小于第二次逾期提醒天数小于第三次逾期提醒天数小于产品到期删除天数
		}
		$this->startTrans();
		try {
			
			foreach($this->config['cron'] as $v){
				$list[] = [
					'setting'=>$v,
					'value'=>$param[$v],
				];
			}			
	    	$this->saveAll($list);
	        $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('update_fail')];
		}
		return ['status' => 200, 'msg' => lang('update_success')];
	}
	/**
	* 时间 2022-5-10
	* @title 默认发送设置
	* @desc 默认发送设置
	* @author xiong
	* @version v1
	* @return  string send_sms - 默认短信发送国内接口
	* @return  string send_sms_global - 默认短信发送国际接口
	* @return  string send_email - 默认邮件信发送接口
	*/
	public function sendList()
	{		
		$configuration = $this->index();
		foreach($configuration as $v){
			if(in_array($v['setting'], $this->config['send'])){				
				$data[$v['setting']] = (string)$v['value'];				
			}
		}
		return $data;
	}
    /**
     * 时间 2022-05-10
     * @title 默认发送设置
     * @desc 默认发送设置
     * @author xiong
     * @version v1
	 * @param  string send_sms - 默认短信发送国内接口
	 * @param  string send_sms_global - 默认短信发送国际接口
	 * @param  string send_email - 默认邮件信发送接口
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息	 
     */
	public function sendUpdate($param)
	{
		$this->startTrans();
		try {
			foreach($this->config['send'] as $v){
				$list[] = [
					'setting'=>$v,
					'value'=>$param[$v],
				];
			}			
	    	$this->saveAll($list);
	        $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('update_fail')];
		}
		return ['status' => 200, 'msg' => lang('update_success')];
	}
}