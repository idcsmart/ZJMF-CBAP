<?php
namespace server\mf_cloud\validate;

use think\Validate;
use server\mf_cloud\model\OptionModel;

/**
 * @title 推荐配置验证
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
        'memory'            => 'require|integer|gt:0',
        'system_disk_size'  => 'require|integer|gt:0',
        'system_disk_type'  => 'checkSystemDiskType:thinkphp',
        'data_disk_size'    => 'integer|egt:0',
        'data_disk_type'    => 'checkDataDiskType:thinkphp',
        'network_type'      => 'require|in:normal,vpc',
        'bw'                => 'integer|between:1,30000',
        'flow'              => 'integer|between:0,999999',
        'peak_defence'      => 'integer|between:0,999999',
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
        'memory.integer'                        => 'recommend_config_memory_format_error',
        'memory.gt'                             => 'recommend_config_memory_format_error',
        'system_disk_size.require'              => 'please_input_recommend_config_system_disk_size',
        'system_disk_size.integer'              => 'recommend_config_system_disk_size_format_error',
        'system_disk_size.gt'                   => 'recommend_config_system_disk_size_format_error',
        'system_disk_type.checkSystemDiskType'  => 'system_disk_type_not_found',
        'data_disk_size.integer'                => 'recommend_config_data_disk_size_format_error',
        'data_disk_size.gt'                     => 'recommend_config_data_disk_size_format_error',
        'data_disk_type.checkDataDiskType'      => 'data_disk_type_not_found',
        'network_type.require'                  => 'please_select_network_type',
        'network_type.in'                       => 'please_select_network_type',
        'bw.integer'                            => 'line_bw_format_error',
        'bw.between'                            => 'line_bw_format_error',
        'flow.integer'                          => 'line_flow_format_error',
        'flow.between'                          => 'line_flow_format_error',
        'peak_defence.integer'                  => 'recommend_config_peak_defence_format_error',
        'peak_defence.between'                  => 'recommend_config_peak_defence_format_error',
    ];

    protected $scene = [
        'create'  => ['name','description','order','data_center_id','line_id','cpu','memory','system_disk_size','data_disk_size','network_type','bw','flow','peak_defence'],
        'update'  => ['id','name','description','order','data_center_id','line_id','cpu','memory','system_disk_size','data_disk_size','network_type','bw','flow','peak_defence'],
    ];

    public function checkSystemDiskType($val){
        $where = [];
        $where[] = ['rel_type', '=', OptionModel::SYSTEM_DISK];
        $where[] = ['other_config', '=', json_encode(['disk_type'=>$val])];
        $exist = OptionModel::where($where)->find();
        return !empty($exist);
    }

    public function checkDataDiskType($val){
        $where = [];
        $where[] = ['rel_type', '=', OptionModel::DATA_DISK];
        $where[] = ['other_config', '=', json_encode(['disk_type'=>$val])];
        $exist = OptionModel::where($where)->find();
        return !empty($exist);
    }

}