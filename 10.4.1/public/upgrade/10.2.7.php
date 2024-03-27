<?php 

require dirname(dirname(__DIR__ )) . '/config.php';
require dirname(dirname(__DIR__ )) .'/vendor/autoload.php';

define('IDCSMART_ROOT',dirname(dirname(__DIR__ )). '/'); # 网站根目录
define('WEB_ROOT',dirname(__DIR__ ) . '/'); # 网站入口目录



set_time_limit(0);
ini_set('max_execution_time', 3600);

if(file_exists(WEB_ROOT.'/'.DIR_ADMIN.'/index.html'))unlink(WEB_ROOT.'/'.DIR_ADMIN.'/index.html');
