<?php
/*
 *  定义权限
 */
return [
    [
        'title' => 'auth_site_management_help',
        'url' => '',
        'description' => '帮助中心', # 权限描述
        'parent' => 'auth_site_management', # 父权限 
        'child' => [
            [
                'title' => 'auth_site_management_help_view',
                'url' => 'index',
                'auth_rule' => [
                    'addon\idcsmart_help\controller\AdminIndexController::idcsmartHelpList',
                ],
                'description' => '查看页面',
            ],
            [
                'title' => 'auth_site_management_help_create_help',
                'url' => 'help_create',
                'auth_rule' => [
                    'addon\idcsmart_help\controller\AdminIndexController::idcsmartHelpTypeList',
                    'addon\idcsmart_help\controller\AdminIndexController::createIdcsmartHelp',
                ],
                'description' => '新增文档',
            ],
            [
                'title' => 'auth_site_management_help_index',
                'url' => 'help_index',
                'auth_rule' => [
                    'addon\idcsmart_help\controller\AdminIndexController::indexIdcsmartHelp',
                    'addon\idcsmart_help\controller\AdminIndexController::indexIdcsmartHelpSave',
                    'addon\idcsmart_help\controller\AdminIndexController::idcsmartHelpTypeList',
                ],
                'description' => '首页管理',
            ],
            [
                'title' => 'auth_site_management_help_type',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_help\controller\AdminIndexController::idcsmartHelpTypeList',
                    'addon\idcsmart_help\controller\AdminIndexController::createIdcsmartHelpType',
                    'addon\idcsmart_help\controller\AdminIndexController::updateIdcsmartHelpType',
                    'addon\idcsmart_help\controller\AdminIndexController::deleteIdcsmartHelpType',
                ],
                'description' => '帮助分类管理',
            ],
            [
                'title' => 'auth_site_management_help_show_hide',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_help\controller\AdminIndexController::hiddenIdcsmartHelp',
                ],
                'description' => '显隐开关',
            ],
            [
                'title' => 'auth_site_management_help_update_help',
                'url' => 'help_create',
                'auth_rule' => [
                    'addon\idcsmart_help\controller\AdminIndexController::idcsmartHelpDetail',
                    'addon\idcsmart_help\controller\AdminIndexController::idcsmartHelpTypeList',
                    'addon\idcsmart_help\controller\AdminIndexController::updateIdcsmartHelp',
                ],
                'description' => '编辑文档',
            ],
            [
                'title' => 'auth_site_management_help_delete_help',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_help\controller\AdminIndexController::deleteIdcsmartHelp',
                ],
                'description' => '删除文档',
            ],
        ]
    ],
];