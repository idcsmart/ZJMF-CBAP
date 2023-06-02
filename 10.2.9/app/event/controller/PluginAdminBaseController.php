<?php
namespace app\event\controller;

use app\admin\model\AuthRuleModel;
use think\facade\Cache;
use app\common\logic\UpgradePluginsLogic;

class PluginAdminBaseController extends PluginBaseController
{
    // 初始化
    protected function initialize()
    {
        parent::initialize();

        if(!$this->checkAccess()){
            $param = $this->request->param();
            $module     = 'addon';
            $plugin     = $param['_plugin']??'';
            $controller = (isset($param['_controller']) && !empty($param['_controller']))?ucfirst(parse_name($param['_controller'],1)):'';
            $action     = (isset($param['_action']) && !empty($param['_action']))?lcfirst(parse_name($param['_action'],1)):'';

            $rule = $module.'\\'.$plugin .'\\controller\\'. $controller .'Controller::'. $action;

            // 查找权限,未找到设置了则放行
            $AuthRuleModel = new AuthRuleModel();
            $name = $AuthRuleModel->getAuthName($rule);
            if(!empty($name)){
                echo json_encode(['status'=>404, 'msg'=>lang('permission_denied', ['{name}'=>lang_plugins($name)])]);die;
            }

        }

    }

    private function checkAccess()
    {
        $adminId = get_admin_id();
        if($adminId==1 || empty($adminId)){
            return true;
        }
        $param = $this->request->param();
        $module     = 'addon';
        $plugin     = $param['_plugin']??'';
        $controller = (isset($param['_controller']) && !empty($param['_controller']))?ucfirst(parse_name($param['_controller'],1)):'';
        $action     = (isset($param['_action']) && !empty($param['_action']))?lcfirst(parse_name($param['_action'],1)):'';

        $rule = $module.'\\'.$plugin .'\\controller\\'. $controller .'Controller::'. $action;

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

    /*private function upgrade()
    {


        $UpgradePluginsLogic = new UpgradePluginsLogic();

        return $UpgradePluginsLogic->upgrade();
    }*/
}