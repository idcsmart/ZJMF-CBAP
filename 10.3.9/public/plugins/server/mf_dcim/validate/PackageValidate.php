<?php
namespace server\mf_dcim\validate;

use think\Validate;

/**
 * @title 灵活机型验证
 * @use  server\mf_dcim\validate\PackageValidate
 */
class PackageValidate extends Validate{

	protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'name'              => 'require|length:1,30',
        'group_id'          => 'require|integer|gt:0',
        'cpu_option_id'     => 'require|integer|gt:0',
        'cpu_num'           => 'require|integer|between:1,10000',
        'mem_option_id'     => 'require|integer|gt:0',
        'mem_num'           => 'require|integer|between:1,256',
        'disk_option_id'    => 'require|integer|gt:0',
        'disk_num'          => 'require|integer|between:1,256',
        'bw'                => 'require|integer|between:1,30000',
        'ip_num'            => 'require|integer|between:1,30000',
        'description'       => 'length:0,30',
        'optional_memory_id'=> 'array',
        'mem_max'           => 'integer|between:0,99999999',
        'mem_max_num'       => 'integer|between:0,10000',
        'optional_disk_id'  => 'array',
        'disk_max_num'      => 'integer|between:0,10000',
        'price'             => 'checkPrice:thinkphp',
        'order'             => 'integer|between:0,999',
        'hidden'            => 'require|in:0,1',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'name.require'                  => 'mf_dcim_package_name_require',
        'name.length'                   => 'mf_dcim_package_name_length_error',
        'group_id.require'              => 'mf_dcim_package_group_id_require',
        'group_id.integer'              => 'mf_dcim_package_group_id_format_error',
        'group_id.gt'                   => 'mf_dcim_package_group_id_format_error',
        'cpu_option_id.require'         => 'mf_dcim_package_cpu_option_id_require',
        'cpu_option_id.integer'         => 'param_error',
        'cpu_option_id.gt'              => 'param_error',
        'cpu_num.require'               => 'mf_dcim_package_cpu_num_require',
        'cpu_num.integer'               => 'mf_dcim_package_cpu_num_format_error',
        'cpu_num.between'               => 'mf_dcim_package_cpu_num_format_error',
        'mem_option_id.require'         => 'mf_dcim_package_mem_option_id_require',
        'mem_option_id.integer'         => 'param_error',
        'mem_option_id.gt'              => 'param_error',
        'mem_num.require'               => 'mf_dcim_package_mem_num_require',
        'mem_num.integer'               => 'mf_dcim_package_mem_num_format_error',
        'mem_num.between'               => 'mf_dcim_package_mem_num_format_error',
        'disk_option_id.require'        => 'mf_dcim_package_disk_option_id_require',
        'disk_option_id.integer'        => 'param_error',
        'disk_option_id.gt'             => 'param_error',
        'disk_num.require'              => 'mf_dcim_package_disk_num_require',
        'disk_num.integer'              => 'mf_dcim_package_disk_num_format_error',
        'disk_num.between'              => 'mf_dcim_package_disk_num_format_error',
        'bw.require'                    => 'mf_dcim_package_bw_require',
        'bw.integer'                    => 'mf_dcim_package_bw_format_error',
        'bw.between'                    => 'mf_dcim_package_bw_format_error',
        'ip_num.require'                => 'mf_dcim_package_ip_num_require',
        'ip_num.integer'                => 'mf_dcim_package_ip_num_format_error',
        'ip_num.between'                => 'mf_dcim_package_ip_num_format_error',
        'description.length'            => 'mf_dcim_package_description_length_error',
        'optional_memory_id.array'      => 'param_error',
        'mem_max.integer'               => 'mf_dcim_package_mem_max_format_error',
        'mem_max.between'               => 'mf_dcim_package_mem_max_format_error',
        'mem_max_num.integer'           => 'mf_dcim_package_mem_max_num_format_error',
        'mem_max_num.between'           => 'mf_dcim_package_mem_max_num_format_error',
        'optional_disk_id.array'        => 'param_error',
        'disk_max_num.integer'          => 'mf_dcim_package_disk_max_num_format_error',
        'disk_max_num.between'          => 'mf_dcim_package_disk_max_num_format_error',
        'price.checkPrice'              => 'mf_dcim_price_cannot_lt_zero',
        'order.integer'                 => 'mf_dcim_order_format_error',
        'order.between'                 => 'mf_dcim_order_format_error',
    ];

    protected $scene = [
        'create' => ['product_id','name','group_id','cpu_option_id','cpu_num','mem_option_id','mem_num','disk_option_id','disk_num','bw','ip_num','description','optional_memory_id','mem_max','mem_max_num','optional_disk_id','disk_max_num','price','order'],
        'update' => ['id','name','group_id','cpu_option_id','cpu_num','mem_option_id','mem_num','disk_option_id','disk_num','bw','ip_num','description','optional_memory_id','mem_max','mem_max_num','optional_disk_id','disk_max_num','price','order'],
        'delete' => ['id'],
        'update_hidden' => ['id','hidden'],
    ];

    public function checkPrice($value){
        if(!is_array($value)){
            return false;
        }
        foreach($value as $v){
            if(!is_numeric($v) || $v<0 || $v>999999){
                return 'mf_dcim_price_must_between_0_999999';
            }
        }
        return true;
    }


}