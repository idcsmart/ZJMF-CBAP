<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 上下游商品验证
 */
class UpstreamProductValidate extends Validate
{
    protected $rule = [
        'id'                    => 'require|integer|gt:0',
        'supplier_id'           => 'require|integer|gt:0',
        'upstream_product_id'   => 'require|integer|gt:0',
        'name'                  => 'require|max:50',
        'profit_type'           => 'require|in:0,1',
        'profit_percent'        => 'require|float',
        'auto_setup'            => 'require|in:0,1',
        'certification'         => 'require|in:0,1',
        'product_group_id'      => 'require|integer|gt:0',
        'username'              => 'require|max:100',
        'token'                 => 'require|max:200',
        'secret'                => 'require',
        'renew_profit_type'     => 'require|in:0,1',
        'renew_profit_percent'  => 'require|float',
        'upgrade_profit_type'   => 'require|in:0,1',
        'upgrade_profit_percent'=> 'require|float',
    ];

    protected $message = [
        'id.require'                        => 'id_error',
        'id.integer'                        => 'id_error',
        'id.gt'                             => 'id_error',
        'supplier_id.require'               => 'supplier_id_error',
        'supplier_id.integer'               => 'supplier_id_error',
        'supplier_id.gt'                    => 'supplier_id_error',
        'upstream_product_id.require'       => 'upstream_product_id_error',
        'upstream_product_id.integer'       => 'upstream_product_id_error',
        'upstream_product_id.gt'            => 'upstream_product_id_error',
        'name.require'                      => 'please_enter_upstream_product_name',
        'name.max'                          => 'upstream_product_name_cannot_exceed_50_chars',
        'profit_percent.require'            => 'please_enter_upstream_product_profit_percent',
        'profit_percent.float'              => 'upstream_product_profit_percent_error',
        'profit_percent.gt'                 => 'upstream_product_profit_percent_error',
        'auto_setup.require'                => 'param_error',
        'auto_setup.in'                     => 'param_error',
        'certification.require'             => 'param_error',
        'certification.in'                  => 'param_error',
        'product_group_id.require'          => 'product_group_id_error',
        'product_group_id.integer'          => 'product_group_id_error',
        'product_group_id.gt'               => 'product_group_id_error',
        'username.require'                  => 'please_enter_supplier_username',
        'username.max'                      => 'supplier_username_cannot_exceed_100_chars',
        'token.require'                     => 'please_enter_supplier_token',
        'token.max'                         => 'supplier_token_cannot_exceed_200_chars',
        'secret.require'                    => 'please_enter_supplier_secret',
        'renew_profit_percent.require'      => 'please_enter_upstream_product_renew_profit_percent',
        'renew_profit_percent.float'        => 'upstream_product_renew_profit_percent_error',
        'upgrade_profit_percent.require'    => 'please_enter_upstream_product_upgrade_profit_percent',
        'upgrade_profit_percent.float'      => 'upstream_product_upgrade_profit_percent_error',
    ];

    protected $scene = [
        'create' => ['supplier_id', 'upstream_product_id', 'name', 'profit_percent', 'auto_setup', 'certification', 'product_group_id','profit_type','renew_profit_type','renew_profit_percent','upgrade_profit_type','upgrade_profit_percent'],
        'update' => ['id', 'supplier_id', 'upstream_product_id', 'name', 'profit_percent', 'auto_setup', 'certification', 'product_group_id','profit_type','renew_profit_type','renew_profit_percent','upgrade_profit_type','upgrade_profit_percent'],
        'agent' => ['id', 'username', 'token', 'secret', 'name', 'profit_percent', 'auto_setup', 'certification', 'product_group_id'],
    ];
}