<?php
/*
 *  定义权限
 */
return [
    [
        'title' => 'auth_site_management_news',
        'url' => '',
        'description' => '新闻中心', # 权限描述
        'parent' => 'auth_site_management', # 父权限 
        'child' => [
            [
                'title' => 'auth_site_management_news_view',
                'url' => 'index',
                'auth_rule' => [
                    'addon\idcsmart_news\controller\AdminIndexController::idcsmartNewsList',
                ],
                'description' => '查看页面',
            ],
            [
                'title' => 'auth_site_management_news_create_news',
                'url' => 'news_create',
                'auth_rule' => [
                    'addon\idcsmart_news\controller\AdminIndexController::idcsmartNewsTypeList',
                    'addon\idcsmart_news\controller\AdminIndexController::createIdcsmartNews',
                ],
                'description' => '新增新闻',
            ],
            [
                'title' => 'auth_site_management_news_type',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_news\controller\AdminIndexController::idcsmartNewsTypeList',
                    'addon\idcsmart_news\controller\AdminIndexController::createIdcsmartNewsType',
                    'addon\idcsmart_news\controller\AdminIndexController::updateIdcsmartNewsType',
                    'addon\idcsmart_news\controller\AdminIndexController::deleteIdcsmartNewsType',
                ],
                'description' => '新闻分类管理',
            ],
            [
                'title' => 'auth_site_management_news_show_hide',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_news\controller\AdminIndexController::hiddenIdcsmartNews',
                ],
                'description' => '显隐开关',
            ],
            [
                'title' => 'auth_site_management_news_update_news',
                'url' => 'news_create',
                'auth_rule' => [
                    'addon\idcsmart_news\controller\AdminIndexController::idcsmartNewsDetail',
                    'addon\idcsmart_news\controller\AdminIndexController::idcsmartNewsTypeList',
                    'addon\idcsmart_news\controller\AdminIndexController::updateIdcsmartNews',
                ],
                'description' => '编辑新闻',
            ],
            [
                'title' => 'auth_site_management_news_delete_news',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_news\controller\AdminIndexController::deleteIdcsmartNews',
                ],
                'description' => '删除新闻',
            ],
        ]
    ],
];