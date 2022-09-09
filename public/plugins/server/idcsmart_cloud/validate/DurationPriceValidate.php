<?php
namespace server\idcsmart_cloud\validate;

use think\Validate;

/**
 * 周期价格验证
 */
class DurationPriceValidate extends Validate{

	protected $rule = [
		'id' 		    => 'require|integer',
        'display_name'  => 'length:1,10',
        'cal_ratio'     => 'between:1,100000',
        'bw_ratio'      => 'between:1,100000',
    ];

    protected $message  =   [
    	'id.require'    	  => 'id_error',
    	'id.integer'    	  => 'id_error',
        'display_name.length' => '显示值长度不能超过10个字'  ,
        // 'cal_ratio.require' => 'please_input_cal_ratio',
        'cal_ratio.between'   => 'cal_ratio_format_error',
        // 'bw_ratio.require'  => 'please_input_bw_ratio',
        'bw_ratio.between'    => 'bw_ratio_format_error',
    ];

    protected $scene = [
        'save' => ['id','cal_ratio','bw_ratio'],
    ];


}

