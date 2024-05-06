<?php
namespace app\admin\validate;

use think\Validate;

/**
 * @title 官网导航验证
 * @use   app\admin\validate\WebNavValidate
 */
class WebNavValidate extends Validate
{
	protected $rule = [
		'id'            => 'require|integer|gt:0',
        'name'          => 'require|max:10',
        'web_nav_id'    => 'require|integer',
        'url'           => 'require|checkUrl:thinkphp',
        'status'        => 'require|in:0,1',
        'prev_id'       => 'require|integer',
    ];

    protected $message = [
    	'id.require'            => 'id_error',
        'id.integer'            => 'id_error',
        'id.gt'                 => 'id_error',
        'name.require'          => 'web_nav_name_require',
        'name.max'              => 'web_nav_name_max',
        'web_nav_id.require'    => 'web_nav_web_nav_id_require',
        'web_nav_id.integer'    => 'web_nav_web_nav_id_integer',
        'url.require'           => 'web_nav_url_require',
        'url.checkUrl'          => 'web_nav_url_url',
        'status.require'        => 'web_nav_status_require',
        'status.in'             => 'web_nav_status_in',
        'prev_id.require'       => 'web_nav_prev_id_require',
        'prev_id.integer'       => 'web_nav_prev_id_integer',
    ];

    protected $scene = [
        'create'        => ['name','web_nav_id','url','status'],
        'update'        => ['id','name','web_nav_id','url','status'],
        'update_status' => ['id','status'],
        'drag_to_sort'  => ['id','prev_id','web_nav_id'],
    ];

    /**
     * 时间 2024-03-01
     * @title 验证跳转地址
     * @desc  验证跳转地址,以http://|https://开头
     * @author hh
     * @version v1
     * @param   string value - 跳转地址 require
     * @return  bool
     */
    protected function checkUrl($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) && preg_match('#^(http|https)://#', $value);
    }

}