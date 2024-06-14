<?php
namespace server\idcsmart_common\logic;

use server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel;
use server\idcsmart_common\model\IdcsmartCommonServerModel;
use think\facade\View;

/*
 * 模块逻辑类
 */
class ProvisionLogic
{

    public $modules = [];

    public $is_admin = false;

    protected $hostid = 0;
    protected $params = [];
    protected $support = [
        'CreateAccount'=>[
            'type'=>'button',
            'auth'=>'admin',
            'func'=>'create',
            'des'=>'开通'
        ],
        'SuspendAccount'=>[
            'type'=>'button',
            'auth'=>'admin',
            'func'=>'suspend',
            'des'=>'暂停'
        ],
        'UnsuspendAccount'=>[
            'type'=>'button',
            'auth'=>'admin',
            'func'=>'unsuspend',
            'des'=>'解除暂停'
        ],
        'TerminateAccount'=>[
            'type'=>'button',
            'auth'=>'admin',
            'func'=>'terminate',
            'des'=>'删除'
        ],
        'Renew'=>[
            'auth'=>'both',
            'des'=>'续费'
        ],
        'ChangePackage'=>[
            'des'=>'升降级'
        ],
        'On'=>[
            'type'=>'button',
            'auth'=>'both',
            'place'=>'control',
            'func'=>'on',
            'des'=>'开机'
        ],
        'Off'=>[
            'type'=>'button',
            'auth'=>'both',
            'place'=>'control',
            'func'=>'off',
            'des'=>'关机'
        ],
        'Reboot'=>[
            'type'=>'button',
            'auth'=>'both',
            'place'=>'control',
            'func'=>'reboot',
            'des'=>'重启'
        ],
        'HardOff'=>[
            'type'=>'button',
            'auth'=>'both',
            'place'=>'control',
            'func'=>'hard_off',
            'des'=>'硬关机'
        ],
        'HardReboot'=>[
            'type'=>'button',
            'auth'=>'both',
            'place'=>'control',
            'func'=>'hard_reboot',
            'des'=>'硬重启'
        ],
        'Reinstall'=>[
            'type'=>'button',
            'auth'=>'both',
            'place'=>'console',
            'func'=>'reinstall',
            'des'=>'重装系统'
        ],
        'CrackPassword'=>[
            'type'=>'button',
            'auth'=>'both',
            'place'=>'console',
            'func'=>'crack_pass',
            'des'=>'重置密码'
        ],
        'RescueSystem'=>[
            'type'=>'button',
            'auth'=>'both',
            'place'=>'control', // 改成控制下拉，不清楚需要哪些参数
            'func'=>'rescue_system',
            'des'=>'救援系统'
        ],
        'Vnc'=>[
            'type'=>'button',
            'auth'=>'both',
            'place'=>'console',
            'func'=>'vnc',
            'des'=>'VNC'
        ],
        /*
        'Ikvm'=>[
            'type'=>'button',
            'auth'=>'both',
            'place'=>'console',
            'func'=>'ikvm',
            'des'=>'IKVM'
        ],
        'Kvm'=>[
            'type'=>'button',
            'auth'=>'both',
            'place'=>'console',
            'func'=>'kvm',
            'des'=>'KVM'
        ],
        */
        'Sync'=>[
            'type'=>'button',
            'auth'=>'admin',
            'place'=>'control',
            'func'=>'sync',
            'des'=>'拉取信息'
        ],
        'Status'=>[
            'auth'=>'both',
            'func'=>'status',
            'des'=>'获取状态'
        ],
        'ManagePanel'=>[
            'type'=>'button',
            'auth'=>'admin',
            'func'=>'panel',
            'des'=>'管理面板',
        ],
    ];
    protected $options = [  //标准配置
        'text'=>[
            'name',
            'placeholder',
            'description',
            'default',
            'type',
            'key',
        ],
        'password'=>[
            'name',
            'placeholder',
            'description',
            'default',
            'type',
            'key',
        ],
        'yesno'=>[  //值 on|off
            'name',
            'description',
            'default',
            'type',
            'key',
        ],
        'radio'=>[
            'name',
            'description',
            'options',
            'default',
            'type',
            'key',
        ],
        'dropdown'=>[
            'name',
            'description',
            'options',
            'default',
            'type',
            'key',
        ],
        'textarea'=>[
            'name',
            'placeholder',
            'description',
            'default',
            'rows',
            'cols',
            'type',
            'key',
        ]
    ];

    protected $other_params = [];

    protected $max_option = 23;

    private $dir = WEB_ROOT.'plugins/server/idcsmart_common/module/';

    public function __construct(){
        $this->getOriginModules();
    }

    public function __call($name, $arguments = []){
        if(isset($this->support[ucfirst($name)])){
            if($name == 'reinstall' || $name == 'Reinstall'){
                $this->other_params['os'] = $arguments[1];
                $this->other_params['os_name'] = $arguments[2];
                $this->other_params['sub_id'] = $arguments[3];
                $this->other_params['option_id'] = $arguments[4];
            }else if($name == 'changePackage' || $name == 'ChangePackage'){
                $this->other_params['old_config'] = $arguments[1];
            }else if($name == 'crackPassword' || $name == 'CrackPassword'){
                $this->other_params['new_pass'] = $arguments[1];
            }
            return $this->execSupportFunc($name, $arguments[0]);
        }
        return ['status'=>'error', 'msg'=>lang_plugins('NO_SUPPORT_FUNCTION'),'no_support_function'=>true];
    }

    // 5分钟定时任务
    public function fiveMinuteCron(){
        $modules = $this->getModules();
        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();
        foreach ($modules as $v){
            $module = $v['value'];
            // 判断是否存在接口，需要有表存在，否则会报错
            $exist = $IdcsmartCommonServerModel->where('type',$module)->find();
            if ($exist && $this->checkAndRequire($module)){
                if(function_exists($module.'_FiveMinuteCron')){
                    call_user_func($module.'_FiveMinuteCron');
                }
            }
        }
    }

    // 每日定时任务
    public function dailyCron(){
        $modules = $this->getModules();
        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();
        foreach ($modules as $v){
            $module = $v['value'];
            $exist = $IdcsmartCommonServerModel->where('type',$module)->find();
            if ($exist && $this->checkAndRequire($module)){
                if(function_exists($module.'_DailyCron')){
                    call_user_func($module.'_DailyCron');
                }
            }
        }
    }

    /**
     * 作者: huanghao
     * 时间: 2019-11-21
     * 获取所有可用模块
     * @return [type]         [description]
     */
    public function getModules(){
        $data = [];
        foreach($this->modules as $k=>$v){
            $data[] = [
                'value'=>$k,
                'name'=>$v
            ];
        }
        return $data;
    }

    /**
     * 作者: huanghao
     * 时间: 2019-11-21
     * 获取模块产品配置
     * @param  string $module  模块名称
     * @return [type]         [description]
     */
    public function getModuleConfigOptions($module = '', $hidden_key = true){
        $result = [];
        if($this->checkAndRequire($module)){
            if(function_exists($module.'_ConfigOptions')){
                $res = call_user_func($module.'_ConfigOptions');
                if(is_array($res)){
                    $res = array_slice($res, 0, $this->max_option); //模块24个配置项(对应product表的config_option1)
                    //去掉不合法的类型
                    foreach($res as $k=>$v){
                        $type = $v['type'];
                        $one = [];
                        foreach($this->options[$type] as $kk=>$vv){
                            if($hidden_key && $vv == 'key'){
                                continue;
                            }
                            if(isset($v[$vv])){
                                if($vv == 'options'){
                                    if(is_string($v[$vv])){
                                        $arr = explode(',', $v[$vv]);
                                        foreach($arr as $vvv){
                                            $one[$vv][] = [
                                                'value'=>$vvv,
                                                'name'=>$vvv
                                            ];
                                        }
                                    }else if(is_array($v[$vv])){
                                        foreach($v[$vv] as $kkk=>$vvv){
                                            $one[$vv][] = [
                                                'value'=>$kkk,
                                                'name'=>$vvv
                                            ];
                                        }
                                    }
                                    continue;
                                }else{
                                    $one[$vv] = $v[$vv];
                                }
                            }else{
                                $one[$vv] = '';
                            }
                        }
                        $result[] = $one;
                    }
                }else{
                    $result = [];
                }
            }
        }
        return $result;
    }

    /**
     * 作者: huanghao
     * 时间: 2019-11-20
     * 获取模块metadata
     * @param  string $module 模块名称
     * @return [type]         [description]
     */
    public function getModuleMetaData($module = ''){
        $res = [
            'APIVersion'=>'',    // API版本
            'HelpDoc'=>'',      // 帮助文档地址
        ];
        if($this->checkAndRequire($module)){
            if(function_exists($module.'_MetaData')){
                $data = call_user_func($module.'_MetaData');
                foreach($res as $k=>$v){
                    if(isset($data[$k])){
                        $res[$k] = $data[$k];
                    }
                }
            }
        }
        return $res;
    }

    // 模块是否可用
    public function checkAndRequire($module = ''){
        if(!empty($module) && isset($this->modules[$module])){
            if(file_exists($this->dir.$module.'/'.$module.'.php')){
                require_once $this->dir.$module.'/'.$module.'.php';
                return true;
            }
        }
        return false;
    }

    /**
     * 作者: huanghao
     * 时间: 2019-11-20
     * 模块前台输出(控制栏)
     * @return [type] [description]
     */
    public function clientArea($hostid = 0){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        $html = [];
        if($this->checkAndRequire($module)){
            $function = $module.'_ClientArea';
            if(function_exists($function)){
                $data = call_user_func($function, $params);

                if(is_array($data)){
                    foreach($data as $k=>$v){
                        if(is_array($v)){
                            $template = $v['template']??"";
                            if(file_exists($this->dir.$module.'/'.$template)){
                                $html[] = [
                                    'key'=>$k,
                                    'name'=>$v['name'] ?? $k
                                ];
                            }else if(is_string($v['html'])){
                                $html[] = [
                                    'key'=>$k,
                                    'name'=>$v['name'] ?? $k,
                                ];
                            }
                        }else{
                            $html[] = [
                                'key'=>$k,
                                'name'=>$k,
                            ];
                        }
                    }
                }
            }
        }
        return $html;
    }

    // 获取指定的控制栏html
    public function clientAreaDetail($hostid, $key, $api_url = ''){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        $html = '';
        if($this->checkAndRequire($module)){
            $function = $module.'_ClientArea';
            if(function_exists($function)){
                $data = call_user_func($function, $params);

                $detail_func = $module.'_ClientAreaOutput';
                if(isset($data[$key]) && function_exists($detail_func)){

                    $res = call_user_func($detail_func, $params, $key);
                    if(is_array($res)){
                        if(file_exists($this->dir.$module.'/'.$res['template'])){
                            foreach($res['vars'] as $k=>$v){
                                View::assign($k, $v);
                            }
                            // 调用方法变量
                            if(!empty($api_url)){
                                View::assign('MODULE_CUSTOM_API', $api_url);
                            }else{
                                View::assign('MODULE_CUSTOM_API', request()->domain().request()->rootUrl()."/console/v1/idcsmart_common/host/{$hostid}/custom/provision");
                            }
                            $html = View::fetch($this->dir.$module.'/'.$res['template']);
                        }
                    }else if(is_string($res)){
                        $html = $res;
                    }else{
                        $html = (string)$res;
                    }
                }else{
                    $html = '';
                }
            }else{
                $html = '';
            }
        }else{
            $html = '';
        }
        return $html;
    }

    /**
     * 作者: huanghao
     * 时间: 2019-11-20
     * 模块后台输出
     * @return [type] [description]
     */
    public function adminArea($hostid = 0){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        $html = [];
        if($this->checkAndRequire($module)){
            $function = $module.'_AdminArea';
            if(function_exists($function)){
                $arr = call_user_func($function, $params);
                foreach($arr as $k=>$v){
                    $html[] = [
                        'name'=>$k,
                        'content'=>(string)$v
                    ];
                }
            }
        }
        return $html;
    }

    /**
     * 作者: huanghao
     * 时间: 2019-11-20
     * 获取前台自定义的标准按钮
     * @return [type] [description]
     */
    public function clientButton($hostid = 0){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        $button = [];
        if($this->checkAndRequire($module)){
            $function = $module.'_ClientButton';
            if(function_exists($function)){
                $buttons = call_user_func($function, $params);
                if(!is_array($buttons)){
                    $button = [];
                }else{  //去掉格式错误的
                    foreach($buttons as $k=>$v){
                        if(!is_array($v)){
                            unset($button[$k]);
                        }else{
                            $button[] = [
                                'type'=>'custom',
                                'func'=>$k,
                                'name'=>$v['name'] ?? $k,
                                'place'=>in_array($v['place'], ['control','console']) ? $v['place'] : 'control',
                                'desc'=>$v['desc'] ?: ''
                            ];
                        }
                    }
                }
            }
        }
        return $button;
    }

    // 获取默认图表定义
    public function chart($hostid){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        $button = [];
        if($this->checkAndRequire($module)){
            $function = $module.'_Chart';
            if(function_exists($function)){
                $buttons = call_user_func($function, $params);
                if(!is_array($buttons)){
                    $button = [];
                }else{  //去掉格式错误的
                    foreach($buttons as $k=>$v){
                        if(!is_array($v)){
                            unset($button[$k]);
                        }else{
                            $button[] = [
                                'type'=>$k,
                                'title'=>$v['title'] ?: $k,
                                'select'=>$v['select'] ?? [],
                                // 'place'=>'chart'
                            ];
                        }
                    }
                }
            }
        }
        return $button;
    }

    // 前台标准按钮输出
    public function clientButtonOutput($hostid = 0){
        $default_button = $this->defaultButton($hostid, false);
        $custom_button = $this->clientButton($hostid);
        $button = $default_button;
        foreach($custom_button as $k=>$v){
            $button[$v['place']][] = [
                'type'=>$v['type'],
                'func'=>$v['func'],
                'name'=>$v['name'],
                'desc'=>$v['desc'] ?: ''
            ];
        }
        if(!isset($button['control'])){
            $button['control'] = [];
        }
        if(!isset($button['console'])){
            $button['console'] = [];
        }
        //$button = array_merge($default_button, $button);
        return $button;
    }

    /**
     * 执行前台标准输出按钮方法
     * @author 	huanghao
     * @time    2019-11-21
     * @param   int    $hostid  hostid
     * @param   string $func  	执行的方法
     * @return  [type]     [description]
     */
    public function execClientButton($hostid = 0, $func = ''){
        $params = $this->getParams($hostid);
        if(empty($params)){
            $result['status'] = 'error';
            $result['msg'] = 'ID错误';
            return $result;
        }
        if($params['domainstatus'] != 'Active' || request()->uid != $params['uid']){
            $result['status'] = 'error';
            $result['msg'] = '不能执行该操作';
            return $result;
        }
        $module = $params['module_type'];
        $button = $this->clientButton($hostid);
        if(in_array($func, array_filter(array_column($button, 'func')))){  //支持的方法
            $function = $module.'_'.$func;
            if(function_exists($function)){
                $res = call_user_func($function, $params);
                if(is_array($res)){
                    return $res;
                }else{
                    if($res === null || $res == 'success' || $res == 'ok'){
                        return ['status'=>'success'];
                    }else{
                        return ['status'=>'error', 'msg'=>(string)$res];
                    }
                }
            }
            return ['status'=>'success'];
        }
        $result = ['status'=>'error', 'msg'=>'错误的方法'];
        return $result;
    }

    /**
     * 作者: huanghao
     * 时间: 2019-11-20
     * 获取后台自定义按钮
     * @param  int   $hostid hostid
     * @return [type]         [description]
     */
    public function adminButton($hostid = 0){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        $button = [];
        if($this->checkAndRequire($module)){
            $function = $module.'_AdminButton';
            if(function_exists($function)){
                $buttons = call_user_func($function, $params);
                if(!is_array($buttons)){
                    $button = [];
                }else{  //去掉格式错误的
                    $hide = $this->adminButtonHide($module, $params);
                    foreach($buttons as $k=>$v){
                        if(!is_string($v) || $hide[$k] === true){
                            unset($button[$k]);
                        }else{
                            $button[] = [
                                'type'=>'custom',
                                'func'=>$k,
                                'name'=>$v
                            ];
                        }
                    }
                }
            }
        }
        return $button;
    }

    /**
     * 作者: huanghao
     * 时间: 2019-12-12
     * 获取模块默认定义的按钮
     * @param  int 	   $hostid 		hostid
     * @param  bool    $is_admin    true后台,false前台
     * @return [type]          [description]
     */
    public function defaultButton($hostid = 0, $is_admin = true){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];

        $button = [];
        if($this->checkAndRequire($module)){
            $hide = $this->adminButtonHide($module, $params);
            foreach($this->support as $k=>$v){
                if(!function_exists($module.'_'.$k) || (isset($v['type']) && $v['type'] != 'button')){
                    continue;
                }
                if($is_admin){
                    if(isset($v['auth']) && ($v['auth'] == 'admin' || $v['auth'] == 'both') && $hide[$k] !== true){
                        if(($params['domainstatus'] == 'Active' || $params['domainstatus'] == 'Suspended') && $k == 'CreateAccount'){
                            continue;
                        }
                        $button[] = [
                            'type'=>'default',
                            'func'=>$v['func'],
                            'name'=>$v['des'],
                        ];
                    }
                }else{
                    if(isset($v['auth']) && ($v['auth'] == 'user' || $v['auth'] == 'both')){
                        if (isset($v['place'])){
                            $button[$v['place']][] = [
                                'type'=>'default',
                                'func'=>$v['func'],
                                'name'=>$v['des'],
                            ];
                        }
                    }
                }
            }
        }
        return $button;
    }

    /**
     * 时间 2020-09-14
     * @title 要隐藏的后台按钮方法
     * @author hh
     * @version v1
     * @param   [type] $module [description]
     * @param   [type] $params [description]
     */
    public function adminButtonHide($module, $params){
        $func = $module.'_AdminButtonHide';
        $result = [];
        if(function_exists($func)){
            $res = call_user_func($func, $params);
            if(is_array($res)){
                foreach($res as $v){
                    if(is_string($v)){
                        $result[$v] = true;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 作者: huanghao
     * 时间: 2019-11-20
     * 获取后台自定义按钮标准输出
     * @param  int   $hostid hostid
     * @return [type]         [description]
     */
    public function adminButtonOutput($hostid = 0){
        $button = $this->defaultButton($hostid);

        $params = $this->getParams($hostid);
        if($params['domainstatus'] == 'Pending'){
            foreach($button as $k=>$v){
                if($v['func'] != 'create'){
                    unset($button[$k]);
                }
            }
            $button = array_values($button);
        }
        if($params['domainstatus'] != 'Suspended'){
            foreach($button as $k=>$v){
                if($v['func'] == 'unsuspend'){
                    unset($button[$k]);
                }
            }
            $button = array_values($button);
        }
        if($params['domainstatus'] != 'Pending'){
            $custom_button = $this->adminButton($hostid);
            foreach($custom_button as $k=>$v){
                $button[] = [
                    'type'=>$v['type'],
                    'func'=>$v['func'],
                    'name'=>$v['name']
                ];
            }
        }
        // $button = array_merge($default_button, $custom_button);
        return $button;
    }

    /**
     * 时间 2020-09-18
     * @title 前台主要输出
     * @author hh
     * @version v1
     * @param   integer $hostid [description]
     * @return  [type]          [description]
     */
    public function clientAreaMainOutput($hostid = 0){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];

        if($this->checkAndRequire($module)){
            $func = $module.'_ClientAreaMainOutput';
            if(function_exists($func)){
                return call_user_func($func, $params) ?: [];
            }
        }
        return [];
    }

    /**
     * 时间 2020-09-18
     * @title 后台主要输出
     * @author hh
     * @version v1
     * @param   integer $hostid [description]
     * @return  [type]          [description]
     */
    public function adminAreaMainOutput($hostid = 0){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];

        if($this->checkAndRequire($module)){
            $func = $module.'_AdminAreaMainOutput';
            if(function_exists($func)){
                return call_user_func($func, $params) ?: [];
            }
        }
        return [];
    }

    /**
     * 作者: huanghao
     * 时间: 2019-11-20
     * 执行后台自定义按钮方法
     * @param  int    $hostid hostid
     * @param  string $func   插件方法
     * @return [type]         [description]
     */
    public function execAdminButton($hostid = 0, $func = ''){
        $params = $this->getParams($hostid);
        if(empty($params)){
            $result['status'] = 'error';
            $result['msg'] = 'ID错误';
            return $result;
        }
        $module = $params['module_type'];
        $button = $this->adminButton($hostid);
        if(in_array($func, array_filter(array_column($button, 'func')))){  //支持的方法
            $function = $module.'_'.$func;
            if(function_exists($function)){
                $res = call_user_func($function, $params);
                if(is_array($res)){
                    $result = $res;
                }else{
                    if($res === null || $res == 'success' || $res == 'ok'){
                        $result['status'] = 'success';
                    }else{
                        $result['status'] = 'error';
                        $result['msg'] = $module.'模块错误:'.(string)$res;
                    }
                }
            }else{
                $result['status'] = 'error';
                $result['msg'] = lang_plugins('NO_SUPPORT_FUNCTION');
            }
        }else{
            $result['status'] = 'error';
            $result['msg'] = lang_plugins('NO_SUPPORT_FUNCTION');
        }
        if(!empty($module) && !empty($params['hostid'])){
            if($result['status'] == 'success'){
                active_log("模块执行成功");
            }else{
                active_log("模块执行失败");
            }
        }
        return $result;
    }

    /**
     * 作者: huanghao
     * 时间: 2019-11-21
     * 后台保存服务时触发
     * @param  int    $hostid hostid
     * @return [type]         [description]
     */
    public function adminSave($hostid = 0){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        if($this->checkAndRequire($module)){
            $function = $module.'_AdminSave';
            if(function_exists($function)){
                return call_user_func($function, $params);
            }
        }
        return true;
    }

    /**
     * 作者: huanghao
     * 时间: 2019-11-21
     * 设置hostid
     * @param  int    $hostid hostid
     * @return [type]         [description]
     */
    public function setHost($hostid = 0){
        if(!empty($hostid)){
            $this->hostid = $hostid;
        }
        return $this;
    }

    /**
     * 作者: wyh
     * 时间: 2022-10-09
     * 获取所有可用模块
     * @return [type]         [description]
     */
    protected function getOriginModules(){
        if(empty($this->modules)){
            $modules = [];
            if(is_dir($this->dir)){
                if($handle = opendir($this->dir)){
                    while(($file = readdir($handle)) !== false){
                        if($file != '.' && $file != '..' && filetype($this->dir . $file) == 'dir' && preg_match('/^[a-z][a-z0-9]+$/', $file)){
                            if(file_exists($this->dir.$file.'/'.$file.'.php')){
                                require_once $this->dir.$file.'/'.$file.'.php';
                                if(function_exists($file.'_MetaData')){
                                    $data = call_user_func($file.'_MetaData');
                                    $modules[$file] = $data['DisplayName'] ?: ucfirst($file);
                                }else{
                                    $modules[$file] = ucfirst($file);
                                }
                            }
                        }
                    }
                    closedir($handle);
                }
            }

            ksort($modules);
            $this->modules = $modules;
        }
        return $this->modules;
    }

    /**
     * 作者: huanghao
     * 时间: 2019-11-21
     * 执行支持的方法
     * @param  string $name   方法名称
     * @param  int    $hostid hostid
     * @return [type]         [description]
     */
    protected function execSupportFunc($name, $hostid = 0){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        if (empty($module)){
            // 空子模块，直接返回开机状态
            if ($name=='status'){
                $result['status'] = 200;
                $result['data']['status'] = 'on';
                $result['data']['des'] = '开机';
                return $result;
            }
            return ['status'=>200];
        }
        $define = false;
        $name = ucfirst($name);
        if($this->checkAndRequire($module)){
            $function = $module.'_'.$name;
            // 获取隐藏的按钮
            // $hide = $this->adminButtonHide($module, $params);
            // if($hide[$name] === true){
            // 	$result['status'] = 'error';
            // 	$result['msg'] = lang_plugins('NO_SUPPORT_FUNCTION');
            // }else{
            if(function_exists($function)){
                if($name == 'Reinstall'){
                    $params['reinstall_os'] = $this->other_params['os'];
                    $params['reinstall_os_name'] = $this->other_params['os_name'];
                    $params['sub_id'] = $this->other_params['sub_id'];
                    $params['option_id'] = $this->other_params['option_id'];
                }else if($name == 'ChangePackage'){
                    $params['old_configoptions'] = $this->other_params['old_config'];
                }
                if($name == 'CrackPassword'){
                    $res = call_user_func($function, $params, $this->other_params['new_pass']);
                }else{
                    $res = call_user_func($function, $params);
                }
                if(is_array($res)){
                    $result = $res;
                }else{
                    if($res === null || $res == 'success' || $res == 'ok'){
                        $result['status'] = 'success';
                        $result['msg'] = '操作成功';
                    }else{
                        $result = ['status'=>'error', 'msg'=>(string)$res];
                    }
                }
                $define = true;
            }else{
                $result['status'] = 'error';
                $result['msg'] = lang_plugins('NO_SUPPORT_FUNCTION');
                $result['no_support_function'] = true;
            }
            // }
        }else{
            $result['status'] = 'error';
            $result['msg'] = lang_plugins('NO_SUPPORT_FUNCTION');
            $result['no_support_function'] = true;
            // $result['status'] = 'success';
        }
        if($this->is_admin && $result['status'] != 'success' && !empty($module) && !empty($params['hostid'])){
            $result['msg'] = $module.'模块错误:'.$result['msg'];
        }
        if(!empty($module) && !empty($params['hostid']) && $define && $name != 'Status'){
            if($result['status'] == 'success'){
                active_log("模块执行成功");
            }else{
                active_log("模块执行失败");
            }
        }

        if ($result['status']=='success'){
            $result['status'] = 200;
        }else{
            $result['status'] = 400;
        }

        return $result;
    }

    public function moduleFunctionExists($name, $hostid = 0){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];

        $name = ucfirst($name);
        if($this->checkAndRequire($module)){
            $function = $module.'_'.$name;

            if(function_exists($function)){
                return true;
            }

        }
        return false;
    }

    /**
     * 作者: huanghao
     * 时间: 2019-11-21
     * 执行自定义的方法
     * @param  string $name   方法名称
     * @param  int    $hostid hostid
     * @return [type]         [description]
     */
    public function execCustomFunc($name, $hostid = 0, $type = 'client', $customFields = []){
        $params = $this->getParams($hostid);
        $params['custom_fields'] = $customFields;
        $module = $params['module_type'];
        $define = false;
        if($this->checkAndRequire($module)){
            $allow_func = $module.'_AllowFunction';

            if(function_exists($allow_func)){
                $allow = call_user_func($allow_func);

                $default = array_keys($this->support);

                $allow = array_diff($allow[$type], $default);

                $function = $module.'_'.$name;
                if(is_array($allow) && in_array($name, $allow) && function_exists($function)){
                    $res = call_user_func($function, $params);
                    if(is_array($res)){
                        $result = $res;
                    }else{
                        if($res == 'success' || $res == 'ok'){
                            $result['status'] = 'success';
                        }else{
                            $result = ['status'=>'error', 'msg'=>(string)$res];
                        }
                    }
                    $define = true;
                }else{
                    $result['status'] = 'error';
                    $result['msg'] = lang_plugins('NO_SUPPORT_FUNCTION');
                }
            }else{
                $result['status'] = 'error';
                $result['msg'] = lang_plugins('NO_SUPPORT_FUNCTION');
            }
        }else{
            $result['status'] = 'error';
            $result['msg'] = lang_plugins('NO_SUPPORT_FUNCTION');
        }
        // if(!empty($module) && !empty($params['hostid']) && $define){
        // 	if($result['status'] == 'success'){
        // 		active_log(lang_plugins('MODULE_EXEC_SUCCESS', [$module, $name, $params['hostid']]));
        // 	}else{
        // 		active_log(lang_plugins('MODULE_EXEC_FAILED', [$module, $name, $params['hostid'], $result['msg']]));
        // 	}
        // }
        return $result;
    }

    // 获取图表数据
    public function getChartData($hostid = 0, $chart_data = []){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        if($this->checkAndRequire($module)){
            $function = $module.'_ChartData';
            if(function_exists($function)){
                $params['chart'] = $chart_data;
                $result = call_user_func($function, $params);
            }else{
                $result['status'] = 'error';
                $result['msg'] = lang_plugins('NO_SUPPORT_FUNCTION');
            }
        }else{
            $result['status'] = 'error';
            $result['msg'] = lang_plugins('NO_SUPPORT_FUNCTION');
        }
        return $result;
    }

    // 流量更新
    public function usageUpdate($module, $hostid){
        if($this->checkAndRequire($module)){
            $function = $module.'_UsageUpdate';
            if(function_exists($function)){
                call_user_func($function, $hostid);
            }
        }
        return $result;
    }

    // 支付通用流量包后调用的方法
    public function afterFlowPacketPaid($hostid, $packet){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        $define = false;
        if($this->checkAndRequire($module)){
            $function = $module.'_FlowPacketPaid';
            if(function_exists($function)){
                $params['flow_packet']['capacity'] = $packet['capacity'];
                call_user_func($function, $params);
                $define = true;
            }
        }
        // if(!empty($module) && !empty($params['hostid']) && $define && $name != 'status'){
        // 	if($result['status'] == 'success'){
        // 		active_log(lang_plugins('MODULE_EXEC_SUCCESS', [$module, $this->support[ucfirst($name)]['des'], $params['hostid']]));
        // 	}else{
        // 		active_log(lang_plugins('MODULE_EXEC_FAILED', [$module, $this->support[ucfirst($name)]['des'], $params['hostid'], $result['msg']]));
        // 	}
        // }
        return $result;
    }

    // 连接状态测试
    public function testLink($module, $data){
        if($this->checkAndRequire($module)){
            $function = $module.'_TestLink';
            if(function_exists($function)){
                $res = call_user_func($function, $data);
                // 控制返回值格式
                $result['status'] = 200;
                if(is_array($res)){
                    if($res['status'] == 200 || $res['status'] == 'success'){
                        $result['data']['server_status'] = (int)$res['data']['server_status'];
                        if(isset($res['data']['msg'])){
                            $result['data']['msg'] = $res['data']['msg'];
                        }
                    }else{
                        $result['status'] = 400;
                        $result['msg'] = $res['msg'] ?: '连接失败';
                    }
                }else{
                    if($res == 'ok' || $res == 'success'){
                        $result['data']['server_status'] = 1;
                    }else{
                        $result['status'] = 400;
                        $result['data']['server_status'] = 0;
                        $result['data']['msg'] = $res ?: '连接失败';
                    }
                }
            }else{
                $result['status'] = 200;
                $result['data']['server_status'] = 0;
                $result['data']['msg'] = '模块未定义测试连接方法';
            }
        }else{
            $result['status'] = 200;
            $result['data']['server_status'] = 0;
            $result['data']['msg'] = '服务器组未关联模块';
        }
        return $result;
    }

    // 用量
    public function trafficUsage($hostid, $start, $end){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        if($this->checkAndRequire($module)){
            $function = $module.'_TrafficUsage';
            if(function_exists($function)){
                $result = call_user_func($function, $params, $start, $end);
            }
        }else{
            $result['status'] = 400;
            $result['msg'] = '模块未定义该方法';
        }
        return $result;
    }

    // 验证是否定义用量方法
    public function checkDefineUsage($hostid){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        if($this->checkAndRequire($module)){
            $function = $module.'_TrafficUsage';
            if(function_exists($function)){
                $result = true;
            }else{
                $result = false;
            }
        }else{
            $result = false;
        }
        return $result;
    }

    // 验证host对应 模块是否定义了某个方法
    public function checkDefineFunc($hostid, $func = ''){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        if($this->checkAndRequire($module)){
            $function = $module.'_'.$func;
            if(function_exists($function)){
                // nokvm特殊处理
                if($module == 'nokvm'){
                    if(empty($params['customfields']['vserverid']) && $params['domainstatus'] == 'Pending'){
                        $result = false;
                    }else{
                        $result = true;
                    }
                }else{
                    $result = true;
                }
            }else{
                $result = false;
            }
        }else{
            $result = false;
        }
        return $result;
    }

    // 创建工单
    public function createTicket($hostid, $ticket_data){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        if($this->checkAndRequire($module)){
            $function = $module.'_CreateTicket';
            // 获取工单内容
            $params['ticket'] = $ticket_data;
            call_user_func($function, $params);
        }
    }

    // 回复工单
    public function replyTicket($hostid, $ticket){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        if($this->checkAndRequire($module)){
            $function = $module.'_ReplyTicket';
            $params['ticket'] = $ticket['ticket'];
            $params['ticket_reply'] = $ticket['ticket_reply'];
            call_user_func($function, $params);
        }
    }

    // 获取回调params
    public function getParams($hostid = 0){
        $IdcsmartCommonServerHostLinkModel = new IdcsmartCommonServerHostLinkModel();
        $this->setHost($hostid);
        if(!isset($this->params[$this->hostid])){
            $params = $IdcsmartCommonServerHostLinkModel->getProvisionParams($this->hostid);
            //$params['module_type'] = 'nokvm';
            // configoption 没有设置键就默认取模块设置中的键
            $module_config = $this->getModuleConfigOptions($params['module_type'], false);
            if(!empty($module_config)){
                $i = 0;
                foreach($module_config as $v){
                    $i++;
                    if(empty($v['key'])){
                        continue;
                    }
                    if(!isset($params['configoptions'][$v['key']])){
                        $params['configoptions'][$v['key']] = $params['config_option'.$i];
                    }
                }
            }
            $this->params[$this->hostid] = $params;
        }
        return $this->params[$this->hostid] ?: [];
    }

    public function moduleCustomButton($name, $req, $hostid = 0, $type = 'client'){

        $result['status'] = 'error';
        $result['msg'] = lang_plugins('NO_SUPPORT_FUNCTION');

        return $result;
    }

    public function sslCertCustomButton($name, $req, $hostid = 0, $type = 'client'){
        $params = $this->getParams($hostid);
        $module = $params['module_type'];
        $define = false;

        if($this->checkAndRequire($module)){
            $allow_func = $module.'_AllowFunction';

            if(function_exists($allow_func)){
                $allow = call_user_func($allow_func);

                $default = array_keys($this->support);
                $allow = array_diff($allow, $default);

                $function = $module.'_'.$name;

                if(is_array($allow[$type]) && in_array($name, $allow[$type]) && function_exists($function)){
                    $res = call_user_func($function, $params, $req);
                    if(is_array($res)){
                        $result = $res;
                    }else{
                        if($res == 'success' || $res == 'ok'){
                            $result['status'] = 'success';
                        }else{
                            $result = ['status'=>'error', 'msg'=>(string)$res];
                        }
                    }
                    $define = true;
                }else{
                    $result['status'] = 'error';
                    $result['msg'] = lang_plugins('NO_SUPPORT_FUNCTION');
                }
            }else{
                $result['status'] = 'error';
                $result['msg'] = lang_plugins('NO_SUPPORT_FUNCTION');
            }
        }else{
            $result['status'] = 'error';
            $result['msg'] = lang_plugins('NO_SUPPORT_FUNCTION');
        }

        return $result;
    }
}