<?php
namespace server\mf_cloud\validate;

use think\Validate;
use server\mf_cloud\model\OptionModel;

/**
 * @title 套餐验证
 * @use   server\mf_cloud\validate\RecommendConfigValidate
 */
class RecommendConfigValidate extends Validate
{
	protected $rule = [
		'id' 		        => 'require|integer',
        'name'              => 'require|length:1,50',
        'description'       => 'length:0,65535',
        'order'             => 'integer|between:0,999',
        'data_center_id'    => 'require|integer',
        'line_id'           => 'require|integer',
        'cpu'               => 'require|integer|gt:0',
        'memory'            => 'require|integer|between:1,512',
        'system_disk_size'  => 'require|integer|gt:0',
        'system_disk_type'  => 'length:0,50',
        'data_disk_size'    => 'integer|egt:0',
        'data_disk_type'    => 'length:0,50',
        'bw'                => 'integer|between:1,30000',
        'flow'              => 'integer|between:0,999999',
        'peak_defence'      => 'integer|between:0,999999',
        'ip_num'            => 'require|integer|between:0,2000',
        'price'             => 'checkPrice:thinkphp',
        'hidden'            => 'require|in:0,1',
        'gpu_num'           => 'integer|between:0,100',
    ];

    protected $message  =   [
    	'id.require'                            => 'id_error',
        'id.integer'                            => 'id_error',
        'name.require'                          => 'please_input_recommend_config_name',
        'name.length'                           => 'recommend_config_name_length_error',
        'description.length'                    => 'recommend_config_description_length_error',
        'order.integer'                         => 'order_id_format_error',
        'order.between'                         => 'order_id_format_error',
        'data_center_id.require'                => 'please_select_data_center',
        'data_center_id.integer'                => 'please_select_data_center',
        'line_id.require'                       => 'please_select_line',
        'line_id.integer'                       => 'please_select_line',
        'cpu.require'                           => 'please_input_recommend_config_cpu',
        'cpu.integer'                           => 'recommend_config_cpu_foramt_error',
        'cpu.gt'                                => 'recommend_config_cpu_foramt_error',
        'memory.require'                        => 'please_input_recommend_config_memory',
        'memory.integer'                        => 'memory_value_format_error',
        'memory.between'                        => 'memory_value_format_error',
        'system_disk_size.require'              => 'please_input_recommend_config_system_disk_size',
        'system_disk_size.integer'              => 'recommend_config_system_disk_size_format_error',
        'system_disk_size.gt'                   => 'recommend_config_system_disk_size_format_error',
        'system_disk_type.length'               => 'disk_type_format_error',
        'data_disk_size.integer'                => 'recommend_config_data_disk_size_format_error',
        'data_disk_size.gt'                     => 'recommend_config_data_disk_size_format_error',
        'data_disk_type.length'                 => 'disk_type_format_error',
        'bw.integer'                            => 'line_bw_format_error',
        'bw.between'                            => 'line_bw_format_error',
        'flow.integer'                          => 'line_flow_format_error',
        'flow.between'                          => 'line_flow_format_error',
        'peak_defence.integer'                  => 'recommend_config_peak_defence_format_error',
        'peak_defence.between'                  => 'recommend_config_peak_defence_format_error',
        'ip_num.require'                        => 'please_input_line_ip_num',
        'ip_num.integer'                        => 'mf_cloud_recommend_config_ip_num_format_error',
        'ip_num.between'                        => 'mf_cloud_recommend_config_ip_num_format_error',
        'price.checkPrice'                      => 'price_cannot_lt_zero',
        'gpu_num.integer'                       => 'mf_cloud_recommend_config_gpu_num_format_error',
        'gpu_num.between'                       => 'mf_cloud_recommend_config_gpu_num_format_error',
    ];

    protected $scene = [
        'create'  => ['name','description','order','data_center_id','line_id','cpu','memory','system_disk_size','data_disk_size','bw','flow','peak_defence','ip_num','price','gpu_num'],
        'update'  => ['id','name','description','order','data_center_id','line_id','cpu','memory','system_disk_size','data_disk_size','bw','flow','peak_defence','ip_num','price','gpu_num'],
        'update_hidden' => ['id','hidden'],
    ];

    public function checkPrice($value){
        if(!is_array($value)){
            return false;
        }
        foreach($value as $v){
            if(!is_numeric($v) || $v<0 || $v>999999){
                return 'price_must_between_0_999999';
            }
        }
        return true;
    }

}