<?php
namespace addon\idcsmart_certification\logic;

use addon\idcsmart_certification\IdcsmartCertification;
use app\admin\model\PluginModel;

class IdcsmartCertificationLogic
{
    # 默认配置
    public static function getDefaultConfig($name = '')
    {
        $fileConfig = include dirname(__DIR__) . '/config/config.php';

        $dbConfig = (new IdcsmartCertification())->getConfig();

        $config = array_merge($fileConfig?:[],$dbConfig?:[]);

        return isset($config[$name])?$config[$name]:$config;
    }

    public function getConfig()
    {
        $PluginModel = new PluginModel();
        $plugin = $PluginModel->where('name','IdcsmartCertification')
            ->where('module','addon')
            ->find();
        return json_decode($plugin['config']??(object)[],true);
    }

    public function setConfig($param)
    {
        $PluginModel = new PluginModel();
        $PluginModel->where('name','IdcsmartCertification')
            ->where('module','addon')
            ->update([
                'config' => json_encode($param),
                'update_time' => time()
            ]);
        return true;
    }
}