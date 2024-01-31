<?php 
namespace server\mf_dcim\model;

use think\Model;

/**
 * @title 机型配置可选配配置模型
 * @use server\mf_dcim\model\ModelConfigOptionLinkModel
 */
class ModelConfigOptionLinkModel extends Model{

	protected $name = 'module_mf_dcim_model_config_option_link';

    // 设置字段信息
    protected $schema = [
        'model_config_id'   => 'int',
        'option_id'         => 'int',
        'option_rel_type'   => 'int',
    ];


}