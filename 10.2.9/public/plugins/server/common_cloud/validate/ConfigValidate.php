<?php
namespace server\common_cloud\validate;

use think\Validate;

/**
 * 设置参数验证
 */
class ConfigValidate extends Validate{

	protected $rule = [
        'product_id'        => 'require|integer',
        'product_type'      => 'in:0,1',
        'support_ssh_key'   => 'in:0,1',
        'buy_data_disk'     => 'in:0,1',
        'price'             => 'requireIf:buy_data_disk,1|float|between:0,999999',
        'disk_min_size'     => 'requireIf:buy_data_disk,1|number|between:10,1000000|checkDiskSize:thinkphp',
        'disk_max_size'     => 'requireIf:buy_data_disk,1|number|between:10,1000000|checkDiskSize:thinkphp|gt:disk_min_size',
        'disk_max_num'      => 'requireIf:buy_data_disk,1|number|between:1,5',
        'backup_enable'     => 'in:0,1',
        'snap_enable'       => 'in:0,1',
    ];

    protected $message = [
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'product_type.in'               => 'product_type_param_error',
        'support_ssh_key.in'            => 'support_ssh_key_param_error',
        'buy_data_disk.in'              => 'buy_data_disk_param_error',
        'price.requireIf'               => 'please_input_per_ten_price',
        'price.float'                   => 'price_must_between_0_999999',
        'price.between'                 => 'price_must_between_0_999999',
        'disk_min_size.requireIf'       => 'please_input_disk_min_size',
        'disk_min_size.number'          => 'disk_min_size_must_between_10_1000000',
        'disk_min_size.between'         => 'disk_min_size_must_between_10_1000000',
        'disk_min_size.checkDiskSize'   => 'disk_min_size_must_be_multiple_of_10',
        'disk_max_size.requireIf'       => 'please_input_disk_max_size',
        'disk_max_size.number'          => 'disk_max_size_must_between_10_1000000',
        'disk_max_size.between'         => 'disk_max_size_must_between_10_1000000',
        'disk_max_size.checkDiskSize'   => 'disk_max_size_must_be_multiple_of_10',
        'disk_max_num.requireIf'        => 'please_input_add_data_disk_max_num',
        'disk_max_num.number'           => 'add_data_disk_max_num_must_between_1_5',
        'disk_max_num.between'          => 'add_data_disk_max_num_must_between_1_5',
        'disk_max_size.gt'              => 'min_size_cannot_gt_max_size',
        'backup_enable.in'              => 'backup_enable_param_error',
        'snap_enable.in'                => 'snap_enable_param_error',
    ];

    protected $scene = [
        'edit'   => ['product_id','product_type','support_ssh_key','buy_data_disk','price','disk_min_size','disk_max_size','disk_max_num','disk_store_id','backup_enable','snap_enable'],
    ];


    public function checkDiskSize($value){
        if($value % 10 != 0){
            return false;
        }
        return true;
    }
    
    

}