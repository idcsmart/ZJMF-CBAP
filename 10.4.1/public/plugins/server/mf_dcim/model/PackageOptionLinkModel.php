<?php 
namespace server\mf_dcim\model;

use think\Model;

/**
 * @title 灵活机型可选配配置模型
 * @use server\mf_dcim\model\PackageOptionLinkModel
 */
class PackageOptionLinkModel extends Model
{
	protected $name = 'module_mf_dcim_package_option_link';

    // 设置字段信息
    protected $schema = [
        'package_id'        => 'int',
        'option_id'         => 'int',
        'option_rel_type'   => 'int',
    ];


}