<?php
namespace addon\idcsmart_refund\validate;

use app\common\model\ProductModel;
use think\Validate;

/**
 * 退款商品验证
 */
class IdcsmartRefundReasonValidate extends Validate
{
	protected $rule = [
		'id' 		            => 'require|integer',
		'content' 		        => 'require|max:500',
		'reason_custom' 		=> 'require|in:0,1',
    ];

    protected $message  =   [
    	'id.require'     			        => 'id_error',
    	'id.integer'     			        => 'id_error',
    	'content.require'     			    => 'refund_reason_content_require',
    	'content.max'     			        => 'refund_reason_content_max',
    	'reason_custom.require'     		=> 'refund_reason_custom_require',
    	'reason_custom.in'     			    => 'refund_reason_custom_in',
    ];

    protected $scene = [
        'create' => ['content'],
        'update' => ['id','content'],
        'custom' => ['reason_custom'],
    ];
}