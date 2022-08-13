<?php
/*
 *  自定义前台导航菜单(仅支持一二级)
 */
return [
    [
        'name' => 'nav_plugin_addon_ticket', # 链接名称,同时需要在lang/目录下定义语言
        'url'  => '', # 链接格式,会自动加上.html
        'icon' => '', # 图标
        'child' => [ # 二级菜单
            [
                'name' => 'nav_plugin_addon_ticket_list',
                'url' => 'ticket',
                'icon' => '', # 图标
            ],
        ]
    ],
];