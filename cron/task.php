<?php
namespace think;

// 命令行入口文件
// 加载基础文件
require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';
define('IDCSMART_ROOT',dirname(__DIR__ ). '/'); # 网站根目录
define('WEB_ROOT',dirname(__DIR__ ). '/public/'); # 网站入口目录
// 应用初始化
$output=(new App())->console->call('task');
echo $output->fetch();