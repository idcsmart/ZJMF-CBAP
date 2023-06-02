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
        'widget.require'        => '请选择挂件',
        'widget.length'         => '挂件标识错误',
        'widget_arr.require'    => '挂件标识错误',
        'widget_arr.array'      => '挂件标识错误',
        'status.require'        => '显示状态参数错误',
    	'status.in'             => '显示状态参数错误',
    ];

    protected $scene = [
        'save_order'    => ['widget_arr'],
        'toggle_status' => ['widget','status'],        
    ];
}