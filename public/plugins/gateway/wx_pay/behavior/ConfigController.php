<?php

namespace gateway\wx_pay\behavior;


#use cmf\controller\RestBaseController;
use app\admin\controller\BaseController;
use gateway\wx_pay\WxPay;
use think\facade\Db;
class ConfigController extends BaseController
{

    public $info = [];

    /**
     * 获取插件名
     * @return string
     */
    final public function getName()
    {
        $get_name = new WxPay();
        $name = $get_name->info['name'];
        return $name;

    }

    /**
     * 获取插件的配置数组
     * @return array
     */
    final public function getConfig()
    {
        static $_config = [];
        $name = $this->getName();
        if (isset($_config[$name])) {
            return $_config[$name];
        }

        $config = Db::name('plugin')->where('name', $name)->value('config');
        if (!empty($config) && $config != "null") {
            $config = json_decode($config, true);
        } else {
            $this->error('请先将微信相关信息配置收入');

        }
        $_config[$name] = $config;
        return $config;
    }


}