<?php
namespace server\idcsmart_cloud_disk\validate;

use think\Validate;

/**
 * 周期价格验证
 */
class DurationPriceValidate extends Validate{

	protected $rule = [
		'id' 		        => 'require|integer',
        // 'duration'       => 'require|number|between:1,100000',
        'disk_ratio'        => 'require|between:1,100000',
    ];

    protected $message  =   [
    	'id.require'    	    => 'id_error',
    	'id.integer'    	    => 'id_error',
        'disk_ratio.require'    => 'please_enter_disk_ratio',
        'disk_ratio.between'    => 'disk_ratio_can_only_be_between_1_100000',
    ];

    protected $scene = [
        'save' => ['id', 'disk_ratio'],
    ];


}

