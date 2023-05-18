<?php
namespace addon\idcsmart_announcement\logic;

use addon\idcsmart_announcement\IdcsmartAnnouncement;

class IdcsmartAnnouncementLogic
{
    # 默认配置
    public static function getDefaultConfig($name = '')
    {
        $fileConfig = require_once dirname(__DIR__) . '/config/config.php';

        $dbConfig = (new IdcsmartAnnouncement())->getConfig();

        $config = array_merge($fileConfig,$dbConfig);

        return isset($config[$name])?$config[$name]:$config;
    }
}