<?php
namespace server\idcsmart_cloud_ip\validate;

use think\Validate;

/**
 * 周期价格验证
 */
class DurationPriceValidate extends Validate{

	protected $rule = [
		'id' 		        => 'require|integer',
        // 'duration'       => 'require|number|between:1,100000',
        'ip_ratio'          => 'require|between:1,100000',
        'bw_ratio'          => 'require|between:1,100000',
    ];

    protected $message  =   [
    	'id.require'    	    => 'id_error',
    	'id.integer'    	    => 'id_error',
        'ip_ratio.require'      => 'please_enter_ip_ratio',
        'ip_ratio.between'      => 'ip_ratio_can_only_be_between_1_100000',
        'bw_ratio.require'      => 'please_enter_bw_ratio',
        'bw_ratio.between'      => 'bw_ratio_can_only_be_between_1_100000',
    ];

    protected $scene = [
        'save' => ['id', 'ip_ratio', 'bw_ratio'],
    ];


}

