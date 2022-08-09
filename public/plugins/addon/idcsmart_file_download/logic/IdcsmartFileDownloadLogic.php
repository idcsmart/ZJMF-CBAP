<?php
namespace addon\idcsmart_file_download\logic;

use addon\idcsmart_file_download\IdcsmartFileDownload;

class IdcsmartFileDownloadLogic
{
    # 默认配置
    public static function getDefaultConfig($name = '')
    {
        $fileConfig = require_once dirname(__DIR__) . '/config/config.php';

        $dbConfig = (new IdcsmartFileDownload())->getConfig();

        $config = array_merge($fileConfig,$dbConfig);

        return isset($config[$name])?$config[$name]:$config;
    }
}