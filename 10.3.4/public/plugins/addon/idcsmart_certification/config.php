<?php
/**
 * @desc 插件后台配置
 * @author wyh
 * @version 1.0
 * @time 2022-10-13
 */
return [
    'certification_open'            => [    # 在后台插件配置表单中的键名(统一规范:小写+下划线),会是config[module_name]
        'title' => '实名认证',            # 表单的label标题
        'type'  => 'text',           # 表单的类型：text文本,password密码,checkbox复选框,select下拉,radio单选,textarea文本区域,tip提示
        'value' => 1,     # 表单的默认值
        'tip'   => 'friendly name',  # 表单的帮助提示
        'size'  => 200,               # 输入框长度(当type类型为text,password,textarea,tip时,可传入此键)
    ],
    'certification_approval'                 => [
        'title' => '人工复审',
        'type'  => 'text',
        'value' => 0,
        'tip'   => '',
        'size'  => 200,
    ],
    'certification_notice'   => [
        'title' => '后台审批通过后，通知用户',
        'type'  => 'text',
        'value' => 0,
        'tip'   => '',
        'size'  => 200,
    ],
    'certification_update_client_name'      => [
        'title' => '自动更新姓名',
        'type'  => 'text',
        'value' => 0,
        'tip'   => '',
        'size'  => 200,
    ],
    'certification_upload'      => [
        'title' => '上传图片',
        'type'  => 'text',
        'value' => 0,
        'tip'   => '',
        'size'  => 200,
    ],
    'certification_update_client_phone'      => [
        'title' => '手机一致性',
        'type'  => 'text',
        'value' => 0,
        'tip'   => '',
        'size'  => 200,
    ],
    'certification_uncertified_cannot_buy_product'      => [
        'title' => '未认证无法购买产品',
        'type'  => 'text',
        'value' => 0,
        'tip'   => '',
        'size'  => 200,
    ],
    'certification_age_open'      => [
        'title' => '是否开启低于某岁数驳回',
        'type'  => 'text',
        'value' => 0,
        'tip'   => '',
        'size'  => 200,
    ],
    'certification_age'      => [
        'title' => '低于某岁数驳回',
        'type'  => 'text',
        'value' => 0,
        'tip'   => '',
        'size'  => 200,
    ],

];
