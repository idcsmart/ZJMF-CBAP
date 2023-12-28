<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 资源包验证
 * @use  server\mf_cloud\validate\ResourcePackageValidate
 */
class ResourcePackageValidate extends Validate{

	protected $rule = [
        'rid'           => 'require|integer|between:1,99999999',
        'name'         	=> 'require|length:1,255',
    ];

    protected $message = [
        'rid.require'         => 'mf_cloud_rid_require',
        'rid.integer'         => 'mf_cloud_rid_format_error',
        'rid.between'         => 'mf_cloud_rid_format_error',
        'name.require'        => 'mf_cloud_resource_package_name_require',
        'name.length'         => 'mf_cloud_resource_package_name_length_error',
    ];

    protected $scene = [
        'save'   => ['rid', 'name'],
    ];

}