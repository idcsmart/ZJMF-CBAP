<?php
/*
 *  自定义后台导航菜单(仅支持一二级),注意系统会在插件导航下默认创建url为"plugin/插件名称/index.html"的导航,因此需要在template/admin/目录下创建index.php文件作为插件入口
 */
/*return [
    [ # 一级导航
        'name' => 'nav_plugin_addon_ticket', # 导航名称,不要与系统冲突(参考idcsmart_nav表中name字段),同时需要在lang/目录下定义语言
        'url'  => '', # 为空表示一级导航,不需要链接
        'icon' => 'tools', # 图标,获取图标:https://tdesign.tencent.com/vue/components/icon
        'in' => '', # 一级导航,此值为空
        'child' => [ # 二级导航
            [
                'name' => 'nav_plugin_addon_ticket_list', # 导航名称
                'url' => 'ticket', # 链接格式,会自动加上.html
                'in' => 'nav_user_management', # 可定义导航在某个一级导航之下,默认会放置在此一级导航最后的位置(获取方式:idcsmart_nav表中的parent_id==0的name字段)
                'icon' => '', # 图标,获取图标:https://tdesign.tencent.com/vue/components/icon
            ],
        ]
    ],
];*/