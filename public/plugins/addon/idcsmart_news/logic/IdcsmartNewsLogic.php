<?php
namespace addon\idcsmart_news\logic;

use addon\idcsmart_news\IdcsmartNews;

class IdcsmartNewsLogic
{
    # 默认配置
    public static function getDefaultConfig($name = '')
    {
        $fileConfig = require_once dirname(__DIR__) . '/config/config.php';

        $dbConfig = (new IdcsmartNews())->getConfig();

        $config = array_merge($fileConfig,$dbConfig);

        return isset($config[$name])?$config[$name]:$config;
    }
}