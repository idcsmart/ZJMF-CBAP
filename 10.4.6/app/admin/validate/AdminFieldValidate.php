<?php
namespace app\admin\validate;

use think\Validate;

/**
 * @title 管理员字段设置验证
 * @use   app\admin\validate\AdminFieldValidate
 */
class AdminFieldValidate extends Validate
{
	protected $rule = [
		'view'            => 'require|in:client,order,host,transaction',
        'select_field'    => 'require|array|checkSelectField:thinkphp',
    ];

    protected $message = [
    	'view.require'          => 'admin_field_validate_view_require',
        'view.in'               => 'admin_field_validate_view_error',
        'select_field.require'  => 'admin_field_validate_select_field_require',
        'select_field.array'    => 'admin_field_validate_select_field_require',
    ];

    protected $scene = [
        'save'  => ['view','select_field'],
    ];

    // 验证选中字段
    public function checkSelectField($value)
    {
        // 当前id必须选中
        if(!in_array('id', $value)){
            return 'admin_field_validate_id_cannot_cancel';
        }
        return true;
    }


}