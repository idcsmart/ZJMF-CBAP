<?php
namespace addon\idcsmart_help\logic;

use addon\idcsmart_help\IdcsmartHelp;

class IdcsmartHelpLogic
{
    # 默认配置
    public static function getDefaultConfig($name = '')
    {
        $fileConfig = require_once dirname(__DIR__) . '/config/config.php';

        $dbConfig = (new IdcsmartHelp())->getConfig();

        $config = array_merge($fileConfig,$dbConfig);

        return isset($config[$name])?$config[$name]:$config;
    }
}