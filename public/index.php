<?php

// [ 应用入口文件 ]
namespace think;

if (!file_exists(__DIR__ . '/../config.php')){
    header("location:/install.html");die;
}

require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';

define('IDCSMART_ROOT',dirname(__DIR__ ). '/'); # 网站根目录
define('WEB_ROOT',__DIR__ . '/'); # 网站入口目录
define('UPLOAD_DEFAULT',__DIR__ . '/upload/common/default/'); # 文件保存默认路径

// 执行HTTP应用并响应
$App=new App();
$App->debug(APP_DEBUG);
$http = $App->http;

$response = $http->run();

$response->send();

$http->end($response);
