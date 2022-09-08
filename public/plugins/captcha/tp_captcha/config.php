<?php
/**
 * @desc 插件后台配置
 * @author wyh
 * @version 1.0
 * @time 2022-05-27
 */
return [
    'module_name'            => [    # 在后台插件配置表单中的键名(统一规范:小写+下划线),会是config[module_name]
        'title' => '名称',            # 表单的label标题
        'type'  => 'text',           # 表单的类型：text文本,password密码,checkbox复选框,select下拉,radio单选,textarea文本区域,tip提示
        'value' => 'thinkphp图形验证',     # 表单的默认值
        'tip'   => 'friendly name',  # 表单的帮助提示
        'size'  => 200,               # 输入框长度(当type类型为text,password,textarea,tip时,可传入此键)
    ],
    'captcha_width'                 => [
        'title' => '验证码宽度',
        'type'  => 'text',
        'value' => 250,
        'tip'   => '',
        'size'  => 200,
    ],
    'captcha_height'                 => [
        'title' => '验证码高度',
        'type'  => 'text',
        'value' => 61,
        'tip'   => '',
        'size'  => 200,
    ],
    'captcha_length'                 => [
        'title' => '验证码长度',
        'type'  => 'text',
        'value' => 4,
        'tip'   => '',
        'size'  => 200,
    ],
    /*'code_set'   => [
        'title' => '验证码字符集',
        'type'  => 'text',
        'value' => '1234567890',
        'tip'   => '',
        'size'  => 200,
    ],*/

];
