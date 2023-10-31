<?php
namespace server\mf_dcim\validate;

use think\Validate;
use server\mf_dcim\logic\ToolLogic;
use server\mf_dcim\model\DataCenterModel;
use server\mf_dcim\model\ConfigLimitModel;

/**
 * @title 下单参数验证
 * @use  server\mf_dcim\validate\CartValidate
 */
class CartValidate extends Validate
{
	protected $rule = [
        'data_center_id'        => 'require|integer',
        'model_config_id'       => 'require|integer',
        'image_id'              => 'require|integer',   // 镜像ID,暂时必须
        'duration_id'           => 'require|integer',
        'notes'                 => 'length:0,1000',
        // 'bw'                    => '',
        'ip_num'                => 'require',
        'line_id'               => 'require|integer',
    ];

    protected $message  =   [
    	'data_center_id.require'     	=> 'data_center_id_error',
        'data_center_id.integer'        => 'data_center_id_error',
        'model_config_id.require'       => 'please_select_model_config',
        'model_config_id.integer'       => 'please_select_model_config',
        'image_id.require'              => 'mf_dcim_please_select_image',
        'image_id.integer'              => 'mf_dcim_please_select_image',
        'duration_id.require'           => 'mf_dcim_please_select_pay_duration',
        'duration_id.integer'           => 'mf_dcim_please_select_pay_duration',
        'notes.length'                  => 'mf_dcim_notes_length_error',
        'bw.integer'                    => 'mf_dcim_bw_error',
        'ip_num.require'                => 'mf_dcim_please_select_ip_num',
        'ip_num.integer'                => 'mf_dcim_please_select_ip_num',
    	'ip_num.gt'     	            => 'mf_dcim_please_select_ip_num',
        'line_id.require'               => 'mf_dcim_please_select_line',
        'line_id.integer'               => 'mf_dcim_please_select_line',
    ];

    protected $scene = [
        // 下单验证
        'cal' => ['data_center_id','model_config_id','image_id','duration_id','notes','ip_num','line_id'],
    ];

    public function sceneCalPrice(){
        return $this->only(['data_center_id','image_id','duration_id']);
    }

    // 验证配置限制,下单时验证
    public function checkConfigLimit($value, $type, $param){
        $dataCenter = DataCenterModel::find($param['data_center_id']);
        if(empty($dataCenter)){
            return 'mf_dcim_data_center_not_found';
        }
        $ConfigLimitModel = new ConfigLimitModel();
        $checkConfigLimit  = $ConfigLimitModel->checkConfigLimit($dataCenter['product_id'], $param);
        if($checkConfigLimit['status'] == 400){
            return $checkConfigLimit['msg'];
        }
        return true;
    }

}