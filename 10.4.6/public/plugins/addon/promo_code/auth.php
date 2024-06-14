<?php
/*
 *  定义权限
 */
return [
    [
        'title' => 'auth_product_promo_code',
        'url' => '',
        'description' => '优惠码', # 权限描述
        'parent' => 'auth_product', # 父权限 
        'child' => [
            [
                'title' => 'auth_product_promo_code_view',
                'url' => 'index',
                'auth_rule' => [
                    'addon\promo_code\controller\AdminIndexController::promoCodeList',
                ],
                'description' => '查看页面',
            ],
            [
                'title' => 'auth_product_promo_code_create_promo_code',
                'url' => 'create_promo_code',
                'auth_rule' => [
                    'addon\promo_code\controller\AdminIndexController::create',
                    'app\admin\controller\ProductController::productList',
                    'app\admin\controller\ProductGroupController::productGroupFirstList',
                    'app\admin\controller\ProductGroupController::productGroupSecondList',
                ],
                'description' => '新增优惠码',
            ],
            [
                'title' => 'auth_product_promo_code_delete_promo_code',
                'url' => '',
                'auth_rule' => [
                    'addon\promo_code\controller\AdminIndexController::delete',
                ],
                'description' => '删除优惠码',
            ],
            [
                'title' => 'auth_product_promo_code_deactivate_enable_promo_code',
                'url' => '',
                'auth_rule' => [
                    'addon\promo_code\controller\AdminIndexController::status',
                ],
                'description' => '停/启用优惠码',
            ],
            [
                'title' => 'auth_product_promo_code_update_promo_code',
                'url' => 'create_promo_code',
                'auth_rule' => [
                    'addon\promo_code\controller\AdminIndexController::index',
                    'addon\promo_code\controller\AdminIndexController::update',
                    'app\admin\controller\ProductController::productList',
                    'app\admin\controller\ProductGroupController::productGroupFirstList',
                    'app\admin\controller\ProductGroupController::productGroupSecondList',
                ],
                'description' => '编辑优惠码',
            ],
        ]
    ],
];