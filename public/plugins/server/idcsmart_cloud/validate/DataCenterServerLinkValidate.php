<?php
namespace server\idcsmart_cloud\validate;

use think\Validate;

/**
 * 数据中心接口关联验证
 */
class DataCenterServerLinkValidate extends Validate{

	protected $rule = [
		'server_id' => 'require|number',
    ];

    protected $message = [
    	'server_id.require'  => 'please_select_server',
    	'server_id.number'   => 'please_select_server',
    ];

}