<?php
namespace app\home\controller;

use app\home\model\ClientareaAuthRuleModel;
use think\facade\Cache;

/**
 * idcsmart控制器基础类
 */
class HomeBaseController extends BaseController
{
    public function initialize()
    {
        if(!empty(configuration('clientarea_url'))){
            if(request()->domain()!=configuration('clientarea_url')){
                header("location:".configuration('clientarea_url'));die;
            }
        }

        //维护模式
        if (configuration('maintenance_mode')==1){
            $msg = configuration('maintenance_mode_message');
            $url = request()->domain() . '/503.html?msg='.$msg;
            header("location:{$url}");die;
            echo json_encode(['status'=>503, 'msg'=>configuration('maintenance_mode_message')??'维护中……']);die;
        }

        if(!$this->checkAccess()){
            $module     = app('http')->getName();
            $controller = $this->request->controller();
            $action     = $this->request->action();
            $rule = 'app\\'.$module .'\\controller\\'. $controller .'Controller::'. $action;

            // 查找权限,未找到设置了则放行
            $ClientareaAuthRuleModel = new ClientareaAuthRuleModel();
    		$name = $ClientareaAuthRuleModel->getAuthName($rule);
            if(!empty($name)){
                echo json_encode(['status'=>404, 'msg'=>lang('permission_denied')]);die;
            }
            
    	}
    }

    # 检查用户权限
    private function checkAccess()
    {
    	$clientId = get_client_id(false);
    	if(empty($clientId)){
            return true;
        }

        $module     = app('http')->getName();
        $controller = $this->request->controller();
        $action     = $this->request->action();
        $rule = 'app\\'.$module .'\\controller\\'. $controller .'Controller::'. $action;

        // 先获取缓存的权限
        if(Cache::has('home_auth_rule_'.$clientId)){
            $auth = json_decode(Cache::get('home_auth_rule_'.$clientId), true);
            if(!in_array($rule, $auth)){
                return false;
            }else{
                return true;
            }
        }

       	$result = hook('home_check_access', ['rule' => $rule, 'client_id' => $clientId]);
       	$result = array_values(array_filter($result ?? []));
       	foreach ($result as $key => $value) {
       		if(isset($value['status'])){
       			if($value['status']==200){
       				return true;
       			}else{
       				return false;
       			}
       		}
       	}
    	return true;
    }
}

