<?php
namespace app\common\model;

use app\admin\model\AdminModel;
use app\admin\model\PluginModel;
use think\Exception;
use think\Model;
use think\facade\Db;
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
            'terms_privacy_url',
            'system_logo',
            'client_start_id_value',
            'order_start_id_value',
            'clientarea_url',
            'tab_logo',
        ],
        'login'=>[
            'login_phone_verify',
            'register_email',
            'register_phone',
            'home_login_check_ip',
            'admin_login_check_ip',
            'code_client_email_register',
            'code_client_phone_register',
        ],
        'security'=>[
            'captcha_client_register',
            'captcha_client_login',
            'captcha_admin_login',
            'captcha_client_login_error',
            'captcha_plugin',
            'code_client_email_register',
        ],
        'currency'=>[
            'currency_code',
            'currency_prefix',
            'currency_suffix',
            'recharge_open',
            'recharge_min',
            'recharge_max',
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
            'cron_ticket_close_swhitch',
            'cron_ticket_close_day',
            'cron_aff_swhitch',
            'cron_order_overdue_swhitch',
            'cron_order_overdue_day',
            'cron_task_shell',
            'cron_task_status',
            'cron_day_start_time',
            'cron_order_unpaid_delete_swhitch',
            'cron_order_unpaid_delete_day',
        ],
        'send'=>[
            'send_sms',
            'send_sms_global',
            'send_email',
        ],
        'theme' => [
            'admin_theme',
            'clientarea_theme',
            'web_switch',
            'web_theme',
        ],
        'certification' => [
            'certification_open',
            'certification_approval',
            'certification_notice',
            'certification_update_client_name',
            'certification_upload',
            'certification_update_client_phone',
            'certification_uncertified_suspended_host',
        ],
        'info' => [
            'enterprise_name',
            'enterprise_telephone',
            'enterprise_mailbox',
            'enterprise_qrcode',
            'online_customer_service_link',
            'icp_info',
            'icp_info_link',
            'public_security_network_preparation',
            'public_security_network_preparation_link',
            'telecom_appreciation',
            'copyright_info',
            'official_website_logo',
            'cloud_product_link',
            'dcim_product_link',
        ],
        'debug' => [
            'debug_model_auth',
            'debug_model_expire_time'
        ],
        'oss' => [
            'oss_method',
            'oss_sms_plugin',
            'oss_sms_plugin_template',
            'oss_sms_plugin_admin',
            'oss_mail_plugin',
            'oss_mail_plugin_template',
            'oss_mail_plugin_admin',
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
     * @title 保存配置项数据
     * @desc 保存配置项数据
     * @author xiong
     * @version v1
     * @return string setting - 配置项名称
     * @return string value - 配置项值
     */
    public function saveConfiguration($param)
    {
        $setting = $param['setting'] ?? '';
        $value = $param['value'] ?? '';
        if(!empty($setting)){
            $configuration = $this->index();
            $this->startTrans();
            try {
                if(!in_array($setting, array_column($configuration, 'setting'))){
                    $this->create([
                        'setting' => $setting,
                        'value' => $value,
                        'description' => $setting,
                        'create_time' => time()
                    ]);
                }else{
                    $this->update([
                        'value' => $value,
                        'update_time' => time()
                    ], ['setting' => $setting]);
                }
                $this->commit();
            } catch (\Exception $e) {
                // 回滚事务
                $this->rollback();
                return ['status' => 400, 'msg' => lang('fail_message')];
            }
        }else{
            return ['status' => 400, 'msg' => lang('param_error')];
        }
        
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
                if($v['setting'] == 'lang_home_open' || $v['setting'] == 'maintenance_mode' || $v['setting'] == 'client_start_id_value' || $v['setting'] == 'order_start_id_value'){
                    $data[$v['setting']] = (int)$v['value'];
                }elseif ($v['setting']=='system_logo' || $v['setting'] == 'tab_logo'){
                    $data[$v['setting']] = config('idcsmart.system_logo_url') . $v['value'];
                }
                else{
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
        $param['system_logo'] = explode('/',$param['system_logo'])[count(explode('/',$param['system_logo']))-1];
        if(isset($param['tab_logo']) && !empty($param['tab_logo'])){
            $param['tab_logo'] = explode('/',$param['tab_logo'])[count(explode('/',$param['tab_logo']))-1];
        }
        

        # 日志
        $description = [];
        $systemList = $this->systemList();
        $desc = array_diff_assoc($param,$systemList);
        foreach($desc as $k=>$v){
            $lang = '"'.lang("configuration_log_{$k}").'"';
            if($k=='lang_home_open'){
                $lang_old = lang("configuration_log_home_open_{$systemList[$k]}");
                $lang_new = lang("configuration_log_home_open_{$v}");
            }else if($k=='maintenance_mode'){
                $lang_old = lang("configuration_log_switch_{$systemList[$k]}");
                $lang_new = lang("configuration_log_switch_{$v}");
            }else{
                $lang_old = $systemList[$k];
                $lang_new = $v;
            }
            $description[] = lang('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
        }
        $description = implode(',', $description);


        $this->startTrans();
        try {
            foreach($this->config['system'] as $v){
                $list[]=[
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            if($param['client_start_id_value']!=$systemList['client_start_id_value']){
                $res = Db::query("SELECT AUTO_INCREMENT FROM information_schema.tables WHERE table_name='".config('database.connections.mysql.prefix')."client'");
                $AUTO_INCREMENT = $res[0]['AUTO_INCREMENT'] ?? 1; 
                if($AUTO_INCREMENT<$param['client_start_id_value']){
                    $AUTO_INCREMENT = $param['client_start_id_value'];
                    Db::execute("ALTER TABLE ".config('database.connections.mysql.prefix')."client AUTO_INCREMENT={$AUTO_INCREMENT};");
                }
            }
            if($param['order_start_id_value']!=$systemList['order_start_id_value']){
                $res = Db::query("SELECT AUTO_INCREMENT FROM information_schema.tables WHERE table_name='".config('database.connections.mysql.prefix')."order'");
                $AUTO_INCREMENT = $res[0]['AUTO_INCREMENT'] ?? 1; 
                if($AUTO_INCREMENT<$param['order_start_id_value']){
                    $AUTO_INCREMENT = $param['order_start_id_value'];
                    Db::execute("ALTER TABLE ".config('database.connections.mysql.prefix')."order AUTO_INCREMENT={$AUTO_INCREMENT};");
                }
            }


            $this->saveAll($list);
            # 记录日志
            if($description)
                active_log(lang('admin_configuration_system', ['{admin}'=>request()->admin_name, '{description}'=>$description]), 'admin', request()->admin_id);

            if(isset($param['tab_logo']) && !empty($param['tab_logo']) && file_exists(UPLOAD_DEFAULT.$param['tab_logo'])){
                copy(UPLOAD_DEFAULT.$param['tab_logo'], WEB_ROOT.'favicon.ico'); 
            }
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
     * @return  int home_login_check_ip - 前台登录检查IP:1开启0关闭
     * @return  int admin_login_check_ip - 后台登录检查IP:1开启0关闭
     * @return  int code_client_email_register - 邮箱注册是否需要验证码:1开启0关闭
     * @return  int code_client_phone_register - 手机注册是否需要验证码:1开启0关闭
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
     * @param  int home_login_check_ip - 前台登录检查IP:1开启0关闭
     * @param  int admin_login_check_ip - 后台登录检查IP:1开启0关闭
     * @return  int code_client_email_register - 邮箱注册是否需要验证码:1开启0关闭
     * @return  int code_client_phone_register - 手机注册是否需要验证码:1开启0关闭
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function loginUpdate($param)
    {
        foreach($param as $k=>$v){
            $param[$k] = intval($v);
        }
        # 日志
        $description = [];
        $loginList = $this->loginList();
        $desc = array_diff_assoc($param,$loginList);
        foreach($desc as $k=>$v){
            $lang = '"'.lang("configuration_log_{$k}").'"';
            $lang_old = lang("configuration_log_{$k}_{$loginList[$k]}");
            $lang_new = lang("configuration_log_{$k}_{$v}");
            $description[] = lang('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
        }
        $description = implode(',', $description);
        $this->startTrans();
        try {
            foreach($this->config['login'] as $v){
                $list[]=[
                    'setting'=>$v,
                    'value'=>intval($param[$v]),
                ];
            }
            $this->saveAll($list);
            # 记录日志
            if($description)
                active_log(lang('admin_configuration_login', ['{admin}'=>request()->admin_name, '{description}'=>$description]), 'admin', request()->admin_id);
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
     * @title 获取验证码设置
     * @desc 获取验证码设置
     * @author xiong
     * @version v1
     * @return  int captcha_client_register - 客户注册图形验证码开关:1开启0关闭
     * @return  int captcha_client_login - 客户登录图形验证码开关:1开启0关闭
     * @return  int captcha_client_login_error - 客户登录失败图形验证码开关:1开启0关闭
     * @return  int captcha_admin_login - 管理员登录图形验证码开关:1开启0关闭
     * @return  int captcha_width - 图形验证码宽度
     * @return  int captcha_height - 图形验证码高度
     * @return  int captcha_length - 图形验证码字符长度
     * @return  int code_client_email_register - 邮箱注册数字验证码开关:1开启0关闭
     */
    public function securityList()
    {

        $configuration = $this->index();
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['security'])){
                if($v=="captcha_width" || $v=="captcha_height"){
                    $data[$v['setting']] = (float)$v['value'];
                } else{
                    $data[$v['setting']] = $v['value'];
                }
            }
        }
        return $data;
    }
    /**
     * 时间 2022-05-10
     * @title 保存验证码设置
     * @desc 保存验证码设置
     * @author xiong
     * @version v1
     * @param  int param.captcha_client_register - 客户注册图形验证码开关:1开启0关闭
     * @param  int param.captcha_client_login - 客户登录图形验证码开关:1开启0关闭
     * @param  int param.captcha_client_login_error - 客户登录失败图形验证码开关:1开启0关闭
     * @param  int param.captcha_admin_login - 管理员登录图形验证码开关:1开启0关闭
     * @param  string captcha_plugin - 验证码插件(从/admin/v1/captcha_list接口获取)
     * @param  int param.code_client_email_register - 邮箱注册数字验证码开关:1开启0关闭
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function securityUpdate($param)
    {
        if (!empty($param['captcha_plugin'])){
            $PluginModel = new PluginModel();
            $captchaPlugin = $PluginModel->where('name',$param['captcha_plugin'])->where('module','captcha')->where('status',1)->find();
            if (empty($captchaPlugin)){
                return ['status'=>400,'msg'=>lang('plugin_is_not_exist')];
            }
        }

        # 日志
        $description = [];
        $systemList = $this->securityList();
        $desc = array_diff_assoc($param,$systemList);
        foreach($desc as $k=>$v){
            $lang = '"'.lang("configuration_log_{$k}").'"';
            if($k=='captcha_width' || $k=='captcha_height' || $k=='captcha_length'){
                $lang_old = $systemList[$k];
                $lang_new = $v;
            }else if($k=='captcha_client_login_error'){
                $lang_old = lang("configuration_log_captcha_client_login_error_{$systemList[$k]}");
                $lang_new = lang("configuration_log_captcha_client_login_error_{$v}");
            }else{
                $lang_old = lang("configuration_log_switch_{$systemList[$k]}");
                $lang_new = lang("configuration_log_switch_{$v}");
            }
            $description[] = lang('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
        }
        $description = implode(',', $description);
        $this->startTrans();
        try {
            foreach($this->config['security'] as $v){
                $param[$v] = $v=='captcha_plugin'?$param[$v]:intval($param[$v]??0);
                $list[]=[
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);
            # 记录日志
            if($description)
                active_log(lang('admin_configuration_security', ['{admin}'=>request()->admin_name, '{description}'=>$description]), 'admin', request()->admin_id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail') . ':' . $e->getMessage()];
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
                if($v['setting'] == 'recharge_open' || $v['setting'] == 'recharge_min' || $v['setting'] == 'recharge_max'){
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
     * @param  int param.recharge_max - 单笔最大金额
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function currencyUpdate($param)
    {
        # 日志
        $description = [];
        $systemList = $this->currencyList();
        $desc = array_diff_assoc($param,$systemList);
        foreach($desc as $k=>$v){
            $lang = '"'.lang("configuration_log_{$k}").'"';
            if($k=='recharge_open'){
                $lang_old = lang("configuration_log_switch_{$systemList[$k]}");
                $lang_new = lang("configuration_log_switch_{$v}");
            }else{
                $lang_old = $systemList[$k];
                $lang_new = $v;
            }
            $description[] = lang('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
        }
        $description = implode(',', $description);
        $this->startTrans();
        try {
            foreach($this->config['currency'] as $v){
                if($v == 'recharge_min'){
                    $param[$v] = round($param[$v],2);
                }else if($v == 'recharge_max'){
                    $param[$v] = round($param[$v],2);
                }
                else if($v == 'recharge_open'){
                    $param['recharge_open'] = intval($param['recharge_open']);
                }

                $list[] = [
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);
            # 记录日志
            if($description)
                active_log(lang('admin_configuration_currency', ['{admin}'=>request()->admin_name, '{description}'=>$description]), 'admin', request()->admin_id);
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
     * @return int cron_ticket_close_swhitch - 自动关闭工单开关 1开启，0关闭
     * @return int cron_ticket_close_day - 已回复状态的工单超过x小时后关闭
     * @return int cron_aff_swhitch - 推介月报开关 1开启，0关闭
     * @return int cron_order_overdue_swhitch - 订单未付款通知开关 1开启，0关闭 required
     * @return int cron_order_overdue_day - 订单未付款X天后通知 required
     * @return int cron_task_shell - 任务队列命令 required
     * @return int cron_task_status - 任务队列最新状态 required
     * @return int cron_order_unpaid_delete_swhitch - 订单自动删除开关 1开启，0关闭 required
     * @return int cron_order_unpaid_delete_day - 订单未付款X天后自动删除 required
     * @return int cron_day_start_time - 定时任务开始时间 required
     */
    public function cronList()
    {

        $configuration = $this->index();
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['cron'])){
                $data[$v['setting']] = (int)$v['value'];
            }
        }
        //最后执行时间判断
        if((time() - configuration("cron_lock_last_time") > 10*60)){
            $data['cron_status'] = 'error';
        }else{
            $data['cron_status'] = 'success';
        }
        $data['cron_shell'] = 'php '. root_path() .'cron/cron.php';

        // 任务队列命令及状态
        $task_time = $this->where('setting','task_time')->value('value');
        if((time()-$task_time)>=2*60 || empty($task_time)){
            $data['cron_task_status'] = 'error';
        }else{
            $data['cron_task_status'] = 'success';
        }

        $data['cron_task_shell'] = 'php '. root_path() .'cron/task.php';

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
     * @return int param.cron_ticket_close_swhitch - 自动关闭工单开关 1开启，0关闭 required
     * @return int param.cron_ticket_close_day - 已回复状态的工单超过x小时后关闭 required
     * @return int param.cron_aff_swhitch - 推介月报开关 1开启，0关闭 required
     * @return int param.cron_order_overdue_swhitch - 订单未付款通知开关 1开启，0关闭 required
     * @return int param.cron_order_overdue_day - 订单未付款X天后通知 required
     * @return int param.cron_day_start_time - 定时任务开始时间 required
     * @return int param.cron_order_unpaid_delete_swhitch - 订单自动删除开关 1开启，0关闭 required
     * @return int param.cron_order_unpaid_delete_day - 订单未付款X天后自动删除 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function cronUpdate($param)
    {
        $day=[
            'cron_due_suspend_day',
            'cron_due_terminate_day',
            'cron_due_renewal_first_day',
            'cron_due_renewal_second_day',
            'cron_overdue_first_day',
            'cron_overdue_second_day',
            'cron_overdue_third_day',
            'cron_ticket_close_day',
            'cron_order_overdue_day',
            'cron_day_start_time',
            'cron_order_unpaid_delete_day',
        ];
        foreach($day as $v){
            if(!isset($param[$v]) || empty($param[$v])){
                $param[$v]=0;
            }
        }

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
        # 日志
        $description = [];
        $systemList = $this->cronList();
        $desc = array_diff_assoc($param,$systemList);
        foreach($desc as $k=>$v){
            $lang = '"'.lang("configuration_log_".str_replace('day','swhitch',$k)).'"';
            $unit = '';
            if($k=='cron_ticket_close_day'){
                $unit = lang("configuration_log_cron_due_hour");
            }else{
                $unit = lang("configuration_log_cron_due_day");
            }

            if(strpos($k,'swhitch')>0){
                $lang_old = lang("configuration_log_switch_{$systemList[$k]}");
                $lang_new = lang("configuration_log_switch_{$v}");
            }else{
                $lang_old = $systemList[$k].$unit;
                $lang_new = $v.$unit;
            }
            $description[] = lang('admin_old_to_new',['{field}'=>$lang, '{old}'=>'"'.$lang_old.'"', '{new}'=>'"'.$lang_new.'"']);
        }
        $description = implode(',', $description);
        $this->startTrans();
        try {

            foreach($this->config['cron'] as $v){
                $list[] = [
                    'setting'=>$v,
                    'value'=>$param[$v],
                ];
            }
            $this->saveAll($list);
            # 记录日志
            if($description)
                active_log(lang('admin_configuration_cron', ['{admin}'=>request()->admin_name, '{description}'=>$description]), 'admin', request()->admin_id);
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

    /**
     * 时间 2022-08-12
     * @title 获取主题设置
     * @desc 获取主题设置
     * @author theworld
     * @version v1
     * @return string admin_theme - 后台主题
     * @return string clientarea_theme - 会员中心主题
     * @return int web_switch - 官网开关0关闭1开启
     * @return string web_theme - 官网主题
     * @return array admin_theme_list - 后台主题列表
     * @return string admin_theme_list[].name - 名称
     * @return string admin_theme_list[].img - 图片
     * @return array clientarea_theme_list - 会员中心主题列表
     * @return string clientarea_theme_list[].name - 名称
     * @return string clientarea_theme_list[].img - 图片
     * @return array web_theme_list - 官网主题列表
     * @return string web_theme_list[].name - 名称
     * @return string web_theme_list[].img - 图片
     */
    public function themeList()
    {
        $configuration = $this->index();
        $data = [
            'admin_theme' => '',
            'clientarea_theme' => '',
            'web_theme' => '',
            'admin_theme_list' => [],
            'clientarea_theme_list' => [],
            'web_theme_list' => [],
        ];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['theme'])){
                $data[$v['setting']] = (string)$v['value'];
            }
        }
        $domain = request()->domain();
        $adminThemeList = get_files(IDCSMART_ROOT . 'public/'. DIR_ADMIN .'/template');
        foreach ($adminThemeList as $key => $value) {
            $data['admin_theme_list'][] = ['name' => $value, 'img' => $domain . '/'. DIR_ADMIN .'/template/'.$value.'/theme.jpg'];
        }
        $clientareaThemeList = get_files(IDCSMART_ROOT . 'public/clientarea/template');
        foreach ($clientareaThemeList as $key => $value) {
            $data['clientarea_theme_list'][] = ['name' => $value, 'img' => $domain . '/clientarea/template/'.$value.'/theme.jpg'];
        }
        $webThemeList = get_files(IDCSMART_ROOT . 'public/web');
        foreach ($webThemeList as $key => $value) {
            $data['web_theme_list'][] = ['name' => $value, 'img' => $domain . '/web/'.$value.'/theme.jpg'];
        }
        return $data;
    }

    /**
     * 时间 2022-08-12
     * @title 保存主题设置
     * @desc 保存主题设置
     * @author theworld
     * @version v1
     * @param string param.admin_theme - 后台主题 required
     * @param string param.clientarea_theme - 会员中心主题 required
     * @param int param.web_switch - 官网开关0关闭1开启 required
     * @param string param.web_theme - 官网主题 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function themeUpdate($param)
    {
        $adminThemeList = get_files(IDCSMART_ROOT . 'public/'.DIR_ADMIN.'/template');
        $clientareaThemeList = get_files(IDCSMART_ROOT . 'public/clientarea/template');
        $webThemeList = get_files(IDCSMART_ROOT . 'public/web');

        if(!in_array($param['admin_theme'], $adminThemeList)){
            return ['status' => 400, 'msg' => lang('configuration_theme_admin_theme_cannot_error')];
        }
        if(!in_array($param['clientarea_theme'], $clientareaThemeList)){
            return ['status' => 400, 'msg' => lang('configuration_theme_clientarea_theme_cannot_error')];
        }
        if(!in_array($param['web_theme'], $webThemeList)){
            return ['status' => 400, 'msg' => lang('configuration_theme_web_theme_cannot_error')];
        }
        $this->startTrans();
        try {
            foreach($this->config['theme'] as $v){
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
     * 时间 2022-09-23
     * @title 获取实名设置
     * @desc 获取实名设置
     * @author wyh
     * @version v1
     * @return int certification_open - 实名认证是否开启:1开启默认,0关
     * @return int certification_approval - 是否人工复审:1开启默认，0关
     * @return int certification_notice - 审批通过后,是否通知客户:1通知默认,0否
     * @return int certification_update_client_name - 是否自动更新姓名:1是,0否默认
     * @return int certification_upload - 是否需要上传证件照:1是,0否默认
     * @return int certification_update_client_phone - 手机一致性:1是,0否默认
     * @return int certification_uncertified_suspended_host - 未认证暂停产品:1是,0否默认
     */
    public function certificationList()
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['certification'])){
                $data[$v['setting']] = (string)$v['value'];
            }
        }
        return $data;
    }

    /**
     * 时间 2022-08-12
     * @title 保存实名设置
     * @desc 保存实名设置
     * @author theworld
     * @version v1
     * @param int certification_open - 实名认证是否开启:1开启默认,0关 required
     * @param int certification_approval - 是否人工复审:1开启默认，0关 required
     * @param int certification_notice - 审批通过后,是否通知客户:1通知默认,0否 required
     * @param int certification_update_client_name - 是否自动更新姓名:1是,0否默认 required
     * @param int certification_upload - 是否需要上传证件照:1是,0否默认 required
     * @param int certification_update_client_phone - 手机一致性:1是,0否默认 required
     * @param int certification_uncertified_suspended_host - 未认证暂停产品:1是,0否默认 required
     */
    public function certificationUpdate($param)
    {
        $this->startTrans();
        try {
            foreach($this->config['certification'] as $v){
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
     * 时间 2023-02-28
     * @title 获取信息配置
     * @desc 获取信息配置
     * @author theworld
     * @version v1
     * @return string enterprise_name - 企业名称
     * @return string enterprise_telephone - 企业电话
     * @return string enterprise_mailbox - 企业邮箱
     * @return string enterprise_qrcode - 企业二维码
     * @return string online_customer_service_link - 在线客服链接
     * @return string icp_info - ICP信息
     * @return string icp_info_link - ICP信息信息链接
     * @return string public_security_network_preparation - 公安网备
     * @return string public_security_network_preparation_link - 公安网备链接
     * @return string telecom_appreciation - 电信增值
     * @return string copyright_info - 版权信息
     * @return string official_website_logo - 官网LOGO
     * @return string cloud_product_link - 云产品跳转链接
     * @return string dcim_product_link - DCIM产品跳转链接
     */
    public function infoList()
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['info'])){
                $data[$v['setting']] = (string)$v['value'];
            }
        }
        return $data;
    }

    /**
     * 时间 2023-02-28
     * @title 保存信息配置
     * @desc 保存信息配置
     * @author theworld
     * @version v1
     * @param string enterprise_name - 企业名称 required
     * @param string enterprise_telephone - 企业电话 required
     * @param string enterprise_mailbox - 企业邮箱 required
     * @param string enterprise_qrcode - 企业二维码 required
     * @param string online_customer_service_link - 在线客服链接 required
     * @param string icp_info - ICP信息 required
     * @param string icp_info_link - ICP信息信息链接 required
     * @param string public_security_network_preparation - 公安网备 required
     * @param string public_security_network_preparation_link - 公安网备链接 required
     * @param string telecom_appreciation - 电信增值 required
     * @param string copyright_info - 版权信息 required
     * @param string official_website_logo - 官网LOGO required
     * @param string cloud_product_link - 云产品跳转链接 required
     * @param string dcim_product_link - DCIM产品跳转链接 required
     */
    public function infoUpdate($param)
    {
        $this->startTrans();
        try {
            foreach($this->config['info'] as $v){
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
     * 时间 2023-09-07
     * @title debug页面
     * @desc debug页面
     * @author wyh
     * @version v1
     * @return string debug_model - 1开启debug模式
     * @return string debug_model_auth - debug模式授权码
     * @return string debug_model_expire_time - 到期时间
     */
    public function debugInfo()
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['debug'])){
                $data[$v['setting']] = (string)($v['value']??"");
            }
        }
        $data['debug_model'] = intval(cache('debug_model'));
        return $data;
    }

    /**
     * 时间 2023-09-07
     * @title 保存debug页面
     * @desc 保存debug页面
     * @author wyh
     * @version v1
     * @param string debug_model - 1开启debug模式 required
     */
    public function debug($param)
    {
        if ($param['debug_model']==1){
            $private_key = '-----BEGIN RSA PRIVATE KEY-----
MIIEogIBAAKCAQEAnDPK9GhJh/beaBTstVoL0j1C2KbC2Nr2J9eVeFPqlYZKfsrEbdezbpztqzCjXQWVBfFbQmp6sCeuL1GWGFC3qTKOYxKAwWPgtBtPNEQIw7Ym9KX5suS3SYxi04bVhsof8fHaR4pSl88cG6Q7+FaJqLibqwIpmwAx3ZKrThUVqmNwKkHLC4W6mkQo6wE7u4Laiyd+LJxthW0BItKXw6G7Ns39gAYulBE0Nz1SGA+VvutzZzzwz2aE6YMjpFX2cP+qGC56HPs0e38v1eV5oE6R/U7Kif7KPKlWePmuS8lW8EelV1OwfsTwFc+EM9OEtORNlDKmdctns9/IcxdajjKmHwIDAQABAoIBAHvXtHnClUnvOLZcoK/IDMdLOsx6qtE0CSXdjuwv3DVgm3+bU9GiyuhQEz8++Mavvk9P5ILr2QoA6+EoVlBA7tx+8NUrvlmVznn9jPZrWmeQ66HcVfS30XnGjDQZGwIbDujMT7uYt5MU6bwgoktqkQnsE7+pn0L9DIwX1Sm7HcpQf23HaCFb3+ok+FrrDQUgzMDqMYUIQWrfmXo1+FKu1LPGF85QsxIwNxtedlUHlAHFfuF/Zq9dZVF38FTtRU7Z8rX7ewpdx9kMfAKWu0fMdKDXgWixIHGmq4KV3ZpCN9DYQ2Ft7/0RenIGgIuf2WUsNFV+EyrTC7qaqcFr6F4a5sECgYEAyMABYW7ii5ouu3njvRW9OLefabbpytuy99LIoebPyjjUgYzeDrQ8HiL1ZhdtMYhtvy7crhW871tOgl0aVSxK420U2WTToGIO78+twVBhhD3yzlMhBVbPy6I0N1v+BZ71sH1e72PfmAFbLb/HtGbWAE1+Jd4TVIQDd6yD6DfmFCMCgYEAxzElJdB83bJznew7DmSXJxRp6l5Q5N1a8jTiflpwjNNum5QLx0FePmwmGHIAglvPQBHCAj+dGyNnlaqSBDgOwK15Un3G7BRLDpAoCxc/pUWWEl1SoPonH/qXvgpmcdHkKkAS3D9ExR+u2zE8YzgS/BzLjoqGGpvJX/hAE0IkV9UCgYAWp7SALmdaodfMSIEvAZkNIYvX/lB8GDcmSJ9jxgyFIcy5ohAdULHIJOHU16f3AxJ/lOZKryFXUdKWW7NxEUKST+keb4aCfw54edN+EXgv2F3icvczBw0EShXieXs9XycS99MS6Q5+tQh5LT94WHKmLhiiZWGBFDTf+JQaTNSmSQKBgHpcBBfAhJOjBUajUHu86uUEszNXEJYmK7HRLrizUaQQVUeYn8ucqgnqYVRu40UwpJUU03qSHS4Ih572ko+o59cQORClVsa6iIi/oPl/JIefwVoynYlpYRNR2ljRBrEwX9pcVbmZ2+LDXaQkEJZaYb8g6SH8kfhSbldXpfSukqipAoGAGYEQFcaZ+wEhIsFUBsgHSiVHKD904HIZHoAJn1HBF2UtOH2j/znhjnYY3Xh9yBJ1uoht7u7VHQPDsTys9/IJF2lUjUCqt2PsJDpEBtbyKd8+tU1mvZ9eEOxiL5Ihzy2DhiUW1YZT8PzCkUCZT5Lyo3dLeoR3CK896Fsk3Bi+VKQ=
-----END RSA PRIVATE KEY-----';

            $domain = request()->domain();

            $url = $domain . '/' . DIR_ADMIN;

            $password = rand_str(32);

            cache('debug_model_password',$password,24*3600);

            $debug_msg = ['url'=>$url, 'username'=>'debuguser', 'password'=>$password];

            $debug_msg['node'] = [
                'name' => configuration('enterprise_name')?:"",
                'ip' => '',
                'type' => '',
                'port' => '',
                'ssh_pass' => ''
            ];

            $debug_html = zjmf_private_encrypt(json_encode($debug_msg), $private_key);

            cache('debug_model',intval($param['debug_model']),24*3600);

            $this->startTrans();
            try {
                foreach($this->config['debug'] as $v){
                    if ($v=='debug_model_auth'){
                        $list[] = [
                            'setting'=>$v,
                            'value'=>$debug_html,
                        ];
                    }elseif ($v=="debug_model_expire_time"){
                        $list[] = [
                            'setting'=>$v,
                            'value'=>time()+24*3600,
                        ];
                    }else{
                        $list[] = [
                            'setting'=>$v,
                            'value'=>$param[$v],
                        ];
                    }
                }
                $this->saveAll($list);

                $this->commit();
            } catch (\Exception $e) {
                // 回滚事务
                $this->rollback();
                return ['status' => 400, 'msg' => lang('update_fail')];
            }
            return ['status' => 200, 'msg' => lang('update_success')];
        }else{
            cache('debug_model_password',null);
            $this->startTrans();
            try {
                foreach($this->config['debug'] as $v){
                    if ($v=='debug_model_auth'){
                        $list[] = [
                            'setting'=>$v,
                            'value'=>"",
                        ];
                    }elseif ($v=="debug_model_expire_time"){
                        $list[] = [
                            'setting'=>$v,
                            'value'=>time(),
                        ];
                    }else{
                        $list[] = [
                            'setting'=>$v,
                            'value'=>$param[$v],
                        ];
                    }
                }
                $this->saveAll($list);

                $this->commit();
            } catch (\Exception $e) {
                // 回滚事务
                $this->rollback();
                return ['status' => 400, 'msg' => lang('update_fail')];
            }

            cache('debug_model',intval($param['debug_model']),24*3600);

            return ['status' => 200, 'msg' => lang('update_success')];
        }
    }

    public function getOssConfig()
    {
        $configuration = $this->index();
        $data = [];
        foreach($configuration as $v){
            if(in_array($v['setting'], $this->config['oss'])){
                if ($v['setting']=='oss_sms_plugin_admin' || $v['setting']=="oss_mail_plugin_admin"){
                    $data[$v['setting']] = explode(",",$v['value'])??[];
                }else{
                    $data[$v['setting']] = (string)($v['value']??"");
                }
            }
        }
        return $data;
    }

    public function ossConfig($param)
    {
        $this->startTrans();
        try {
            foreach($this->config['oss'] as $v){
                if ($v=='oss_sms_plugin_admin' || $v=='oss_mail_plugin_admin'){
                    $param[$v] = implode(',',$param[$v]);
                }
                // 切换存储方式需要验证
                if ($v=='oss_method' && !empty($param[$v]) && $param[$v]!=configuration("oss_method")){
                    $AdminModel = new AdminModel();
                    $admin = $AdminModel->find(get_admin_id());
                    if (empty($admin)){
                        throw new \Exception(lang("admin_is_not_exist"));
                    }
                    if (!isset($param['password']) || !idcsmart_password_compare($param['password'],$admin['password'])){
                        throw new \Exception(lang("admin_name_or_password_error"));
                    }
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
            return ['status' => 400, 'msg' => lang('update_fail') . ":" . $e->getMessage()];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }
}