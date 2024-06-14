<?php
namespace addon\idcsmart_renew\logic;

use addon\idcsmart_renew\IdcsmartRenew;

class IdcsmartRenewLogic
{
    # 默认配置
    public static function getDefaultConfig($name = '')
    {
        $fileConfig = include dirname(__DIR__) . '/config/config.php';

        $dbConfig = (new IdcsmartRenew())->getConfig();
        
        $config = array_merge($fileConfig?:[],$dbConfig?:[]);

        return isset($config[$name])?$config[$name]:$config;
    }
}