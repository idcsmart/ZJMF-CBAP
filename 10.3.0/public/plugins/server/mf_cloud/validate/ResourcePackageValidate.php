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
        'rid.require'         => '请输入资源包ID',
        'rid.integer'         => '资源包ID格式错误',
        'rid.between'         => '资源包ID格式错误',
        'name.require'        => '请输入资源包名称',
        'name.length'         => '资源包名称长度不能超过255',
    ];

    protected $scene = [
        'save'   => ['rid', 'name'],
    ];

}