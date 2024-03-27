<?php
namespace gateway\user_custom;

use app\admin\model\PluginModel;
use app\common\lib\Plugin;

class UserCustom extends Plugin
{

    public $info = array(
        'name'        => 'UserCustom',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '线下支付',
        'description' => '线下支付',
        'author'      => '智简魔方', // 必填 插件作者
        'version'     => '1.0.0',  // 必填 插件版本
        'help_url'    => '', // 选填 申请链接
        'author_url'  => '', // 选填 作者链接
        'url'         => '', // 选填 图标地址(可以自定义支付图片地址)
    );

    // 临时订单生成规则,1:毫秒时间戳+8位随机数(21-22位长度订单号,默认规则),2:时间戳+8位随机数(18位长度订单号),3:10位随机数(10位长度订单号)
    public $orderRule=1;

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
        $PluginModel = new PluginModel();
        $config = $PluginModel->where('name','UserCustom')->value('config');
        $config = json_decode($config,true);
        $message = $config['seller_id']??'';
        $reData = htmlspecialchars_decode($message, ENT_QUOTES);
        return $reData;
    }

}