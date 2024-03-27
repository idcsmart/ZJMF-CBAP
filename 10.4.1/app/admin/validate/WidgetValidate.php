<?php
namespace app\admin\validate;

use think\Validate;

/**
 * @title 挂件验证
 * @use   app\admin\validate\WidgetValidate
 */
class WidgetValidate extends Validate
{
	protected $rule = [
        'widget'                => 'require|length:1,255',
        'widget_arr'            => 'require|array',
		'status' 		        => 'require|in:0,1',
    ];

    protected $message  =   [
        'widget.require'        => 'widget_validate_widget_require',
        'widget.length'         => 'widget_validate_widget_error',
        'widget_arr.require'    => 'widget_validate_widget_error',
        'widget_arr.array'      => 'widget_validate_widget_error',
        'status.require'        => 'widget_validate_status_param_error',
    	'status.in'             => 'widget_validate_status_param_error',
    ];

    protected $scene = [
        'save_order'    => ['widget_arr'],
        'toggle_status' => ['widget','status'],        
    ];
}