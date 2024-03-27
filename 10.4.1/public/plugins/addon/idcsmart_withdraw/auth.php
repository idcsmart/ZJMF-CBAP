<?php
/*
 *  定义权限
 */
return [
    [
        'title' => 'auth_business_withdraw',
        'url' => '',
        'description' => '提现管理', # 权限描述
        'parent' => 'auth_business', # 父权限 
        'child' => [
            [
                'title' => 'auth_business_withdraw_apply_list',
                'url' => '',
                'description' => '申请列表',
                'child' => [
                    [
                        'title' => 'auth_business_withdraw_apply_list_view',
                        'url' => 'index',
                        'auth_rule' => [
                            'addon\idcsmart_withdraw\controller\AdminIndexController::idcsmartWithdrawList',
                            'addon\idcsmart_withdraw\controller\AdminIndexController::idcsmartWithdrawRejectReasonList',
                        ],
                        'description' => '查看页面',
                    ],
                    [
                        'title' => 'auth_business_withdraw_apply_list_approve_reject',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_withdraw\controller\AdminIndexController::idcsmartWithdrawAudit',
                        ],
                        'description' => '通过/驳回审核',
                    ],
                    [
                        'title' => 'auth_business_withdraw_apply_list_reject_status_edit',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_withdraw\controller\AdminIndexController::updateIdcsmartWithdrawStatus',
                        ],
                        'description' => '驳回状态编辑',
                    ],
                    [
                        'title' => 'auth_business_withdraw_apply_list_approve_status_edit',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_withdraw\controller\AdminIndexController::confirmRemit',
                        ],
                        'description' => '通过状态编辑',
                    ],
                    [
                        'title' => 'auth_business_withdraw_apply_list_confirm_status_edit',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_withdraw\controller\AdminIndexController::updateIdcsmartWithdrawTransaction',
                        ],
                        'description' => '确认状态编辑',
                    ],
                ]
            ],
            [
                'title' => 'auth_business_withdraw_credit_withdraw_configuration',
                'url' => '',
                'description' => '余额提现设置',
                'child' => [
                    [
                        'title' => 'auth_business_withdraw_credit_withdraw_configuration_view',
                        'url' => 'balance_withdrawal_settings',
                        'auth_rule' => [
                            'addon\idcsmart_withdraw\controller\AdminIndexController::idcsmartWithdrawRuleCredit',
                            'addon\idcsmart_withdraw\controller\AdminIndexController::idcsmartWithdrawMethodList',
                        ],
                        'description' => '查看页面',
                    ],
                    [
                        'title' => 'auth_business_withdraw_credit_withdraw_configuration_save_configuration',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_withdraw\controller\AdminIndexController::saveIdcsmartWithdrawRuleCredit',
                        ],
                        'description' => '保存设置',
                    ],
                ]
            ],
            [
                'title' => 'auth_business_withdraw_configuration',
                'url' => '',
                'description' => '提现设置',
                'child' => [
                    [
                        'title' => 'auth_business_withdraw_configuration_view',
                        'url' => 'withdrawal_setting',
                        'auth_rule' => [
                            'addon\idcsmart_withdraw\controller\AdminIndexController::idcsmartWithdrawRejectReasonList',
                            'addon\idcsmart_withdraw\controller\AdminIndexController::idcsmartWithdrawMethodList',
                        ],
                        'description' => '查看页面',
                    ],
                    [
                        'title' => 'auth_business_withdraw_configuration_withdraw_method',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_withdraw\controller\AdminIndexController::createIdcsmartWithdrawMethod',
                            'addon\idcsmart_withdraw\controller\AdminIndexController::updateIdcsmartWithdrawMethod',
                            'addon\idcsmart_withdraw\controller\AdminIndexController::deleteIdcsmartWithdrawMethod',
                        ],
                        'description' => '提现方式',
                    ],
                    [
                        'title' => 'auth_business_withdraw_configuration_reject_reason',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_withdraw\controller\AdminIndexController::createIdcsmartWithdrawRejectReason',
                            'addon\idcsmart_withdraw\controller\AdminIndexController::updateIdcsmartWithdrawRejectReason',
                            'addon\idcsmart_withdraw\controller\AdminIndexController::deleteIdcsmartWithdrawRejectReason',
                        ],
                        'description' => '驳回原因',
                    ],
                ]
            ],
        ]
    ],
];