<?php
/*
 *  自定义前台导航菜单(仅支持一二级)
 */
return [
    [
        'name' => 'nav_plugin_addon_idcsmart_announcement', # 链接名称,同时需要在lang/目录下定义语言
        'url'  => 'announcement', # 链接格式,会自动加上.html
        'icon' => '', # 图标
        'child' => [ # 二级菜单
        ]
    ],
    [
    	'name' => 'nav_plugin_addon_idcsmart_announcement_source', # 链接名称,同时需要在lang/目录下定义语言
        'url'  => 'source', # 链接格式,会自动加上.html
        'icon' => '', # 图标
        'child' => [ # 二级菜单
        ]
    ],
];