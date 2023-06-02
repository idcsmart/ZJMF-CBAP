<?php
namespace addon\idcsmart_sub_account;

use app\common\lib\Plugin;
use think\facade\Db;
use addon\idcsmart_sub_account\model\IdcsmartSubAccountModel;
use addon\idcsmart_sub_account\model\IdcsmartSubAccountHostModel;
use think\facade\Cache;
use app\home\model\ClientareaAuthModel;
use app\home\model\ClientareaAuthRuleModel;
use app\common\model\ClientModel;

require_once __DIR__ . '/common.php';
/*
 * 子账户管理
 * @author theworld
 * @time 2022-08-09
 * @copyright Copyright (c) 2013-2021 https://www.idcsmart.com All rights reserved.
 */
class IdcsmartSubAccount extends Plugin
{
    public $noNav;
    
    # 插件基本信息
    public $info = array(
        'name'        => 'IdcsmartSubAccount', //插件英文名,作为插件唯一标识,改成你的插件英文就行了
        'title'       => '子账户管理',
        'description' => '子账户管理',
        'author'      => '智简魔方',  //开发者
        'version'     => '1.0.0',      // 版本号
    );
    # 插件安装
    public function install()
    {
        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_sub_account`",
            "CREATE TABLE `idcsmart_addon_idcsmart_sub_account` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '子账户ID',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '主账户ID',
  `client_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联客户ID',
  `auth` text NOT NULL COMMENT '权限',
  `notice` text NOT NULL COMMENT '通知',
  `visible_product` varchar(20) NOT NULL DEFAULT '' COMMENT '可见产品类型:module模块host产品',
  `module` text NOT NULL COMMENT '模块',
  `host_id` text NOT NULL COMMENT '产品ID',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `downstream_client_id` int(11) NOT NULL DEFAULT '0' COMMENT '下游客户ID',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='子账户表'",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_sub_account_project`",
            "CREATE TABLE `idcsmart_addon_idcsmart_sub_account_project` (
  `addon_idcsmart_sub_account_id` int(11) NOT NULL DEFAULT '0' COMMENT '子账户ID',
  `addon_idcsmart_project_id` int(11) NOT NULL DEFAULT '0' COMMENT '项目ID',
  KEY `addon_idcsmart_sub_account_id` (`addon_idcsmart_sub_account_id`),
  KEY `addon_idcsmart_project_id` (`addon_idcsmart_project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='子账户项目关联表'",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_sub_account_host`",
            "CREATE TABLE `idcsmart_addon_idcsmart_sub_account_host` (
  `addon_idcsmart_sub_account_id` int(11) NOT NULL DEFAULT '0' COMMENT '子账户ID',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  KEY `addon_idcsmart_sub_account_id` (`addon_idcsmart_sub_account_id`),
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='子账户产品关联表'",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }
        # 安装成功返回true，失败false
        return true;
    }
    # 插件卸载
    public function uninstall()
    {
        $clientId = IdcsmartSubAccountModel::column('client_id');
        ClientModel::destroy($clientId);

        $sql = [
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_sub_account`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_sub_account_project`",
            "DROP TABLE IF EXISTS `idcsmart_addon_idcsmart_sub_account_host`",
        ];
        foreach ($sql as $v){
            Db::execute($v);
        }
        return true;
    }

    public function getClientParentId($param)
    {
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        return $IdcsmartSubAccountModel->getClientParentId($param['client_id']??0);
    }

    public function getClientHostId($param)
    {
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        return $IdcsmartSubAccountModel->getSubAccountHost($param['client_id']??0);
    }

    public function beforeTaskCreate($param)
    {
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        $result = $IdcsmartSubAccountModel->beforeTaskCreate($param);
        return true;   
    }

    public function homeCheckAccess($param)
    {
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        $param['rule'] = $param['rule'] ?? '';
        $param['client_id'] = $param['client_id'] ?? 0;

        $result = ['status' => 200];
        if(in_array($param['rule'], ['on', 'off', 'reboot', 'vnc', 'reinstall', 'rescue', 'reset_password', 'upgrade', 'renew', 'refund', 'delete'])){
            $subAccount =  $IdcsmartSubAccountModel->where('client_id', $param['client_id'])->find();
            if(!empty($subAccount)){
                $auth = json_decode($subAccount['auth'], true);
                if(in_array($param['rule'], ['on', 'off', 'reboot'])){
                    $authId = ClientareaAuthModel::where('title', 'clientarea_auth_on_off_restart')->value('id');
                }else if(in_array($param['rule'], ['upgrade', 'renew', "addon\\idcsmart_renew\\controller\\clientarea\\IndexController::renew", 'refund', "addon\\idcsmart_refund\\controller\\clientarea\\RefundController::refund"])){
                    $authId = ClientareaAuthModel::where('title', 'clientarea_auth_refund_renew_upgrade')->value('id');
                }else if(in_array($param['rule'], ['vnc', 'reinstall', 'rescue', 'reset_password'])){
                    $authId = ClientareaAuthModel::where('title', 'clientarea_auth_control_reinstall_rescue_reset_set_mount')->value('id');
                }else if(in_array($param['rule'], ['delete'])){
                    $authId = ClientareaAuthModel::where('title', 'clientarea_auth_delete')->value('id');
                }

                if(!empty($authId) && !in_array($authId, $auth)){
                    $result = ['status' => 400];
                }
            }
        }else{
            $auth = $IdcsmartSubAccountModel->getSubAccountAuthRule($param['client_id']??0);

            if(!empty($auth)){
                Cache::set('home_auth_rule_'.$param['client_id'], json_encode($auth),7200);
                if(!in_array($param['rule'], $auth)){
                    $result = ['status' => 400];
                }
            } 
        }
        return $result;
        
    }

    public function afterClientDelete($param)
    {
        $id = $param['id'] ?? 0;

        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        $subAccount = $IdcsmartSubAccountModel->where('client_id', $id)->find();
        $subAccountId = $subAccount['id'] ?? 0;
        $IdcsmartSubAccountModel->where('client_id', $id)->delete();

        $IdcsmartSubAccountHostModel = new IdcsmartSubAccountHostModel();

        $IdcsmartSubAccountHostModel->where('addon_idcsmart_sub_account_id', $subAccountId)->delete();

        return true;
    }

    public function afterClientLogin($param)
    {
        $id = $param['id'] ?? 0;

        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        $auth = $IdcsmartSubAccountModel->getSubAccountAuthRule($id);

        if(!empty($auth)){
            Cache::set('home_auth_rule_'.$id, json_encode($auth),7200);
        } 

        return true;
    }

    public function afterHostCreate($param)
    {
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        $auth = $IdcsmartSubAccountModel->afterHostCreate($param);
        return true;
    }

    public function afterHostDelete($param)
    {
        $id = $param['id'] ?? 0;

        $IdcsmartSubAccountHostModel = new IdcsmartSubAccountHostModel();

        $IdcsmartSubAccountHostModel->where('host_id', $id)->delete();

        return true;
    }
}