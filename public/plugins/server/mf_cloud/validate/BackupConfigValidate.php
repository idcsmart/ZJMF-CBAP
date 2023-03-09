<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 备份设置验证
 * @use  server\mf_cloud\validate\BackupConfigValidate
 */
class BackupConfigValidate extends Validate{

	protected $rule = [
        'id'            => 'require|integer',
        'product_id'    => 'require|integer',
        'num'           => 'require|number|between:1,999',
        'price'         => 'require|float|between:0,999999',
        'type'          => 'require|in:snap,backup',
    ];

    protected $message = [
    	'id.require'     		=> 'id_error',
    	'id.integer'     		=> 'id_error',
        'product_id.require'    => 'product_id_error',
        'product_id.integer'    => 'product_id_error',
        'num.require'           => 'please_input_backup_config_num',
        'num.number'            => 'num_must_between_1_999',
        'num.between'           => 'num_must_between_1_999',
        'price.require'         => 'please_input_price',
        'price.float'           => 'price_must_be_number',
        'price.between'         => 'price_cannot_lt_zero',
        'type.require'          => 'backup_config_type_error',
        'type.in'               => 'backup_config_type_error',
    ];

    protected $scene = [
        'create' => ['product_id','num','price','type'],
        'buy'    => ['type','num'],
        'save'   => ['num', 'price'],
    ];

}