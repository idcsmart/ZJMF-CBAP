<?php
namespace gateway\user_custom;

use app\common\lib\Plugin;
use think\Db;

class UserCustomPlugin extends Plugin
{

    public $info = array(
        'name'        => 'UserCustom',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '线下支付',
        'description' => '线下支付',
        'status'      => 1,
        'author'      => '顺戴网络',
        'version'     => '2.0.0',
        'module'        => 'gateway',
    );

    public $hasAdmin = 0;//插件是否有后台管理界面

    // 插件安装
    public function install()
    {
        return true;//安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        // 在这里不要try catch数据库异常，直接抛出上层会处理异常后回滚的
        return true;//卸载成功返回true，失败false
    }

    public function UserCustomHandle($param)
    {
        $config = Db::name('plugin')->where('name','UserCustom')->value('config');
        $config = json_decode($config,true);
        $message = $config['seller_id']??'';
        return htmlspecialchars_decode($message, ENT_QUOTES);
    }

}