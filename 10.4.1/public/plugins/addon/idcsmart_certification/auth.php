<?php
/*
 *  定义权限
 */
return [
    [
        'title' => 'auth_user_certification',
        'url' => '',
        'description' => '实名认证', # 权限描述
        'parent' => 'auth_user', # 父权限 
        'child' => [
            [
                'title' => 'auth_user_certification_approval',
                'url' => '',
                'description' => '实名审批',
                'child' => [
                    [
                        'title' => 'auth_user_certification_approval_view',
                        'url' => 'index',
                        'auth_rule' => [
                            'addon\idcsmart_certification\controller\CertificationController::certificationList',
                        ],
                        'description' => '查看页面',
                    ],
                    [
                        'title' => 'auth_user_certification_approval_pass_approval',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_certification\controller\CertificationController::approve',
                        ],
                        'description' => '通过审批',
                    ],
                    [
                        'title' => 'auth_user_certification_approval_deny_approval',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_certification\controller\CertificationController::reject',
                        ],
                        'description' => '拒绝审批',
                    ],
                    [
                        'title' => 'auth_user_certification_approval_certification_detail',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_certification\controller\CertificationController::index',
                        ],
                        'description' => '查看实名详情',
                    ],
                ]
            ],
            [
                'title' => 'auth_user_certification_configuration',
                'url' => '',
                'description' => '实名设置',
                'child' => [
                    [
                        'title' => 'auth_user_certification_configuration_view',
                        'url' => 'real_name_setting',
                        'auth_rule' => [
                            'addon\idcsmart_certification\controller\CertificationController::getConfig',
                        ],
                        'description' => '查看页面',
                    ],
                    [
                        'title' => 'auth_user_certification_configuration_save_configuration',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_certification\controller\CertificationController::setConfig',
                        ],
                        'description' => '保存设置',
                    ],
                ]
            ],
            [
                'title' => 'auth_user_certification_interface',
                'url' => '',
                'description' => '接口管理',
                'child' => [
                    [
                        'title' => 'auth_user_certification_interface_view',
                        'url' => 'real_name_interface',
                        'auth_rule' => [
                            'app\admin\controller\PluginController::certificationPluginList',
                        ],
                        'description' => '查看页面',
                    ],
                    [
                        'title' => 'auth_user_certification_interface_jump_app_store',
                        'url' => '',
                        'auth_rule' => [],
                        'description' => '跳转应用商店',
                    ],
                    [
                        'title' => 'auth_user_certification_interface_configure_interface',
                        'url' => '',
                        'auth_rule' => [
                            'app\admin\controller\PluginController::certificationSetting',
                            'app\admin\controller\PluginController::certificationSettingPost',
                        ],
                        'description' => '配置接口',
                    ],
                    [
                        'title' => 'auth_user_certification_interface_deactivate_enable_interface',
                        'url' => '',
                        'auth_rule' => [
                            'app\admin\controller\PluginController::certificationStatus',
                        ],
                        'description' => '停/启用接口',
                    ],
                    [
                        'title' => 'auth_user_certification_interface_install_uninstall_interface',
                        'url' => '',
                        'auth_rule' => [
                            'app\admin\controller\PluginController::certificationInstall',
                            'app\admin\controller\PluginController::certificationUninstall',
                        ],
                        'description' => '安装/卸载接口',
                    ],
                ]
            ],
        ]
    ],
];