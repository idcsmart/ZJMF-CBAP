<?php

$domain = request()->domain();
return [
    # 实名附件保存地址
    'certification_upload_url' => WEB_ROOT . 'plugins/addon/idcsmart_certification/upload/',
    # 实名附件访问地址
    'get_certification_upload_url' => $domain . '/plugins/addon/idcsmart_certification/upload/',

];