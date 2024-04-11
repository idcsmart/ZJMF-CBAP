<?php
/*
 *  定义权限
 */
return [
    [
        'title' => 'auth_user_refund',
        'url' => '',
        'description' => '退款管理', # 权限描述
        'parent' => 'auth_user', # 父权限 
        'child' => [
            [
                'title' => 'auth_user_refund_apply_list',
                'url' => '',
                'description' => '申请列表',
                'child' => [
                    [
                        'title' => 'auth_user_refund_apply_list_view',
                        'url' => 'index',
                        'auth_rule' => [
                            'addon\idcsmart_refund\controller\RefundController::refundList',
                        ],
                        'description' => '查看页面',
                    ],
                    [
                        'title' => 'auth_user_refund_apply_list_approve',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_refund\controller\RefundController::pending',
                        ],
                        'description' => '通过审核',
                    ],
                    [
                        'title' => 'auth_user_refund_apply_list_reject',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_refund\controller\RefundController::reject',
                        ],
                        'description' => '审核驳回',
                    ],
                    [
                        'title' => 'auth_user_refund_apply_list_cancel_apply',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_refund\controller\RefundController::cancel',
                        ],
                        'description' => '取消申请',
                    ],
                ]
            ],
            [
                'title' => 'auth_user_refund_product',
                'url' => '',
                'description' => '商品管理',
                'child' => [
                    [
                        'title' => 'auth_user_refund_product_view',
                        'url' => 'refund',
                        'auth_rule' => [
                            'addon\idcsmart_refund\controller\RefundProductController::refundProductList',
                        ],
                        'description' => '查看页面',
                    ],
                    [
                        'title' => 'auth_user_refund_product_create_product',
                        'url' => 'add_refund_product',
                        'auth_rule' => [
                            'addon\idcsmart_refund\controller\RefundProductController::create',
                            'app\admin\controller\ProductController::productList',
                            'app\admin\controller\ProductGroupController::productGroupFirstList',
                            'app\admin\controller\ProductGroupController::productGroupSecondList',
                        ],
                        'description' => '新增可退款商品',
                    ],
                    [
                        'title' => 'auth_user_refund_product_suspend_reason',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_refund\controller\RefundReasonController::refundReasonList',
                            'addon\idcsmart_refund\controller\RefundReasonController::create',
                            'addon\idcsmart_refund\controller\RefundReasonController::update',
                            'addon\idcsmart_refund\controller\RefundReasonController::delete',
                            'addon\idcsmart_refund\controller\RefundReasonController::index',
                            'addon\idcsmart_refund\controller\RefundReasonController::custom',
                            'addon\idcsmart_refund\controller\RefundReasonController::customSet',
                        ],
                        'description' => '停用原因管理',
                    ],
                    [
                        'title' => 'auth_user_refund_product_update_product',
                        'url' => 'add_refund_product',
                        'auth_rule' => [
                            'addon\idcsmart_refund\controller\RefundProductController::index',
                            'addon\idcsmart_refund\controller\RefundProductController::update',
                            'app\admin\controller\ProductController::productList',
                            'app\admin\controller\ProductGroupController::productGroupFirstList',
                            'app\admin\controller\ProductGroupController::productGroupSecondList',
                        ],
                        'description' => '编辑退款商品',
                    ],
                    [
                        'title' => 'auth_user_refund_product_delete_product',
                        'url' => '',
                        'auth_rule' => [
                            'addon\idcsmart_refund\controller\RefundProductController::delete',
                        ],
                        'description' => '删除退款商品',
                    ],
                ]
            ],
        ]
    ],
];