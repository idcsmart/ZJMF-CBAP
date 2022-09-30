<?php
namespace server\common_cloud\validate;

use think\Validate;

/**
 * 套餐验证
 */
class PackageValidate extends Validate
{
	protected $rule = [
        'id'                            => 'require|integer',
		'product_id' 	                => 'require|integer',
        'name'                          => 'require|length:1,100',
        'description'                   => 'require|length:1,65535',
        'data_center_id'                => 'require|array',
        'cpu'                           => 'require|number|egt:1|elt:240',
        'memory'                        => 'require|number|egt:128|elt:524288',
        'system_disk_size'              => 'require|number|between:1,1048576',
        'system_disk_store'             => 'number',
        'free_data_disk_size'           => 'number|between:0,1048576',
        'data_disk_store'               => 'number',
        'in_bw'                         => 'require|number|between:0,30000',
        'out_bw'                        => 'require|number|between:0,30000',
        'ip_num'                        => 'require|number|between:0,9999',
        'ip_group'                      => 'number',
        'custom_param'                  => 'length:0,65535',
        'traffic_enable'                => 'require|in:0,1',
        'flow'                          => 'requireIf:traffic_enable,1|number|between:0,999999999',
        'traffic_bill_type'             => 'in:month,last_30days',
        'onetime_fee'                   => 'float|between:0,999999',
        'month_fee'                     => 'float|between:0,999999',
        'quarter_fee'                   => 'float|between:0,999999',
        'half_year_fee'                 => 'float|between:0,999999',
        'year_fee'                      => 'float|between:0,999999',
        'two_year'                      => 'float|between:0,999999',
        'three_year'                    => 'float|between:0,999999',
    ];

    protected $message  =   [
    	'id.require'     	                    => 'id_error',
        'id.integer'                            => 'id_error',
        'product_id.require'                    => 'product_id_error',
    	'product_id.integer'                    => 'product_id_error',
        'name.require'                          => 'please_input_package_name',
        'name.length'                           => 'package_name_length_foramt_error',
        'description.require'                   => 'please_input_description',
        'description.length'                    => 'description_length_cannot_over_65535',
        'data_center_id.require'                => 'please_select_data_center',
        'data_center_id.array'                  => 'please_select_data_center',
        'data_center_id.integer'                => 'please_select_data_center',
        'cpu.require'                           => 'please_input_cpu',
        'cpu.number'                            => 'cpu_format_error',
        'cpu.egt'                               => 'cpu_format_error',
        'cpu.elt'                               => 'cpu_format_error',
        'memory.require'                        => 'please_input_memory',
        'memory.number'                         => 'memory_format_error',
        'memory.egt'                            => 'memory_format_error',
        'memory.elt'                            => 'memory_format_error',
        'system_disk_size.require'              => 'please_input_system_disk_size',
        'system_disk_size.number'               => 'system_disk_size_format_error',
        'system_disk_size.between'              => 'system_disk_size_format_error',
        'system_disk_store.number'              => 'system_disk_store_must_bw_number',
        'free_data_disk_size.number'            => 'free_data_disk_size_format_error',
        'free_data_disk_size.between'           => 'free_data_disk_size_format_error',
        'data_disk_store.number'                => 'store_must_bw_number',
        'in_bw.require'                         => 'please_input_in_bw',
        'in_bw.number'                          => 'in_bw_format_error',
        'in_bw.between'                         => 'in_bw_format_error',
        'out_bw.require'                        => 'please_input_out_bw',
        'out_bw.number'                         => 'out_bw_format_error',
        'out_bw.between'                        => 'out_bw_format_error',
        'ip_num.require'                        => 'please_input_ip_num',
        'ip_num.number'                         => 'ip_num_format_error',
        'ip_num.between'                        => 'ip_num_format_error',
        'ip_group.number'                       => 'ip_group_format_error',
        'custom_param.length'                   => 'custom_param_format_error',
        'traffic_enable.require'                => 'traffic_enable_param_error',
        'traffic_enable.in'                     => 'traffic_enable_param_error',
        'flow.requireIf'                        => 'please_input_flow',
        'flow.number'                           => 'flow_format_error',
        'traffic_bill_type.in'                  => 'traffic_bill_type_param_error',
        'onetime_fee.float'                     => 'package_price_format_error',
        'onetime_fee.between'                   => 'package_price_format_error',
        'month_fee.float'                       => 'package_price_format_error',
        'month_fee.between'                     => 'package_price_format_error',
        'quarter_fee.float'                     => 'package_price_format_error',
        'quarter_fee.between'                   => 'package_price_format_error',
        'half_year_fee.float'                   => 'package_price_format_error',
        'half_year_fee.between'                 => 'package_price_format_error',
        'year_fee.float'                        => 'package_price_format_error',
        'year_fee.between'                      => 'package_price_format_error',
        'two_year.float'                        => 'package_price_format_error',
        'two_year.between'                      => 'package_price_format_error',
        'three_year.float'                      => 'package_price_format_error',
        'three_year.between'                    => 'package_price_format_error',
    ];

    protected $scene = [
        'create' => ['product_id','name','description','data_center_id','cpu','memory','system_disk_size','system_disk_store','free_data_disk_size','data_disk_store','in_bw','out_bw','ip_num','ip_group','custom_param','traffic_enable','flow','traffic_bill_type','onetime_fee','month_fee','quarter_fee','half_year_fee','year_fee','two_year','three_year'],
    ];

    public function sceneEdit(){
        return $this->only(['id','name','description','data_center_id','cpu','memory','system_disk_size','system_disk_store','free_data_disk_size','data_disk_store','in_bw','out_bw','ip_num','ip_group','custom_param','traffic_enable','flow','traffic_bill_type','onetime_fee','month_fee','quarter_fee','half_year_fee','year_fee','two_year','three_year'])
                    ->remove('data_center_id', 'array')
                    ->append('data_center_id', 'integer');

    }


}