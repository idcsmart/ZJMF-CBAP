<?php

$domain = request()->domain();
return [
    # 文件保存地址
    'file_upload' => WEB_ROOT . 'plugins/addon/idcsmart_help/upload/',
    # 文件访问地址
    'get_file_upload' => $domain . '/plugins/addon/idcsmart_help/upload/',

];