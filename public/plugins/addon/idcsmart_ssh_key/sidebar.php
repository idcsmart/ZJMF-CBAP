<?php
/*
 *  自定义菜单
 */
return [
    [
        'name' => '样式1', # 链接名称
        'url'  => 'DemoStyle://AdminIndex/addhelp', # 链接格式   插件名://控制器名/方法
        'custom' => 0, # 是否为自定义路由
        'lang' => [ # 菜单多语言
            'chinese' => '样式1', # 中文
            'chinese_tw' => '樣式1', # 台湾
            'english' => 'Demo style 1', # 英文
        ],
    ],
    [
        'name' => '样式2',
        'url'  => 'DemoStyle://AdminIndex/customerdetail1',
        'custom' => 0,
        'lang' => [ # 菜单多语言
            'chinese' => '样式2', # 中文
            'chinese_tw' => '樣式2', # 台湾
            'english' => 'Demo style 2', # 英文
        ],
    ],
    [
        'name' => '样式3',
        'url'  => 'DemoStyle://AdminIndex/customerdetail2',
        'custom' => 0,
        'lang' => [ # 菜单多语言
            'chinese' => '样式3', # 中文
            'chinese_tw' => '樣式3', # 台湾
            'english' => 'Demo style 3', # 英文
        ],
    ],
    [
        'name' => '样式4',
        'url'  => 'DemoStyle://AdminIndex/customerdetail3',
        'custom' => 0,
        'lang' => [ # 菜单多语言
            'chinese' => '样式4', # 中文
            'chinese_tw' => '樣式4', # 台湾
            'english' => 'Demo style 4', # 英文
        ],
    ],
    [
        'name' => '样式5',
        'url'  => 'DemoStyle://AdminIndex/customerdetail4',
        'custom' => 0,
        'lang' => [ # 菜单多语言
            'chinese' => '样式5', # 中文
            'chinese_tw' => '樣式5', # 台湾
            'english' => 'Demo style 5', # 英文
        ],
    ],
    [
        'name' => '样式6',
        'url'  => 'DemoStyle://AdminIndex/helplist',
        'custom' => 0,
        'lang' => [ # 菜单多语言
            'chinese' => '样式6', # 中文
            'chinese_tw' => '樣式6', # 台湾
            'english' => 'Demo style 6', # 英文
        ],
    ],
    [
        'name' => '样式7',
        'url'  => 'https://www.baidu.com',
        'custom' => 1, # 自定义路由,不对url做任何处理
        'lang' => [ # 菜单多语言
            'chinese' => '样式7', # 中文
            'chinese_tw' => '樣式7', # 台湾
            'english' => 'Demo style 7', # 英文
        ],
    ],
];