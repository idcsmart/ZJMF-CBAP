<?php
namespace app\admin\controller;

use app\admin\model\AuthRuleModel;
use think\facade\Cache;

/**
 * idcsmart控制器基础类
 */
class AdminBaseController extends BaseController
{
	// 初始化
    protected function initialize()
    {
    	parent::initialize();

    	if(!$this->checkAccess()){
            $module     = app('http')->getName();
            $controller = $this->request->controller();
            $action     = $this->request->action();
            $rule = 'app\\'.$module .'\\controller\\'. $controller .'Controller::'. $action;

            // 查找权限,未找到设置了则放行
            $AuthRuleModel = new AuthRuleModel();
    		$name = $AuthRuleModel->getAuthName($rule);
            if(!empty($name)){
                echo json_encode(['status'=>404, 'msg'=>lang('permission_denied', ['{name}'=>lang($name)])]);die;
            }
            
    	}

        if(!Cache::has('get_idcsamrt_auth')){
            Cache::set('get_idcsamrt_auth', 1, 3600*24);
            get_idcsamrt_auth();
        }
    	
    }

    private function checkAccess()
    {
    	$adminId = get_admin_id();
        if($adminId==1 || empty($adminId)){
            return true;
        }
        $module     = app('http')->getName();
        $controller = $this->request->controller();
        $action     = $this->request->action();
        $rule = 'app\\'.$module .'\\controller\\'. $controller .'Controller::'. $action;

        // 先获取缓存的权限
    	if(Cache::has('admin_auth_rule_'.$adminId)){
    		$auth = json_decode(Cache::get('admin_auth_rule_'.$adminId), true);
    		if(!in_array($rule, $auth)){
	    		return false;
	    	}else{
	    		return true;
	    	}
    	}

        // 获取数据库的权限
    	$AuthRuleModel = new AuthRuleModel();
    	$auth = $AuthRuleModel->getAdminAuthRule($adminId);
    	Cache::set('admin_auth_rule_'.$adminId, json_encode($auth),7200);
    	if(!in_array($rule, $auth)){
    		return false;
    	}
    	return true;
    }
}