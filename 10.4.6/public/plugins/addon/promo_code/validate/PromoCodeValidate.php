<?php
namespace addon\promo_code\validate;

use think\Validate;

/**
 * 优惠码验证
 */
class PromoCodeValidate extends Validate
{
	protected $rule = [
		'id'                  => 'require|integer|gt:0',
		'code'                => 'require|checkCode:thinkphp|unique:addon_promo_code',
		'type'                => 'require|in:percent,fixed_amount,replace_price,free',
		'client_type' 		  => 'require|in:all,new,old',
		'start_time'          => 'require|checkDate:thinkphp',
		'end_time'            => 'checkDate:thinkphp|gt:start_time',
        'products'            => 'checkProducts:thinkphp',
        'need_products'       => 'checkNeed:thinkphp',
		'max_times'           => 'require|integer|egt:0',
		'single_user_once'    => 'require|in:0,1',
		'upgrade'             => 'require|in:0,1',
        'host_upgrade'        => 'require|in:0,1',
		'renew'               => 'require|in:0,1',
		'loop'                => 'require|in:0,1',
		'cycle_limit'         => 'require|in:0,1',
		'cycle'               => 'requireIf:cycle_limit,1|checkCycle:thinkphp',
		'notes'               => 'max:1000',
        'status'              => 'require|in:0,1',
        'scene'               => 'require|in:new,upgrade,renew',
        //'promo_code'          => 'requireIf:scene,new|checkCode:thinkphp',
        'promo_code'          => 'requireIf:scene,new', // wyh 20240612 兼容老财务优惠码(不限长度)
        'host_id'             => 'requireWithout:promo_code|integer|gt:0',
        'product_id'          => 'require|integer|gt:0',
        'qty'                 => 'requireIf:scene,new|integer|gt:0',
        'amount'              => 'require|float|egt:0',
        'billing_cycle_time'  => 'require|integer|egt:0',
    ];

    protected $message  =   [
    	'id.require'                    => 'id_error',
    	'id.integer'     			    => 'id_error',
        'id.gt'                         => 'id_error',
    	'code.require'                  => 'promo_code_require',
    	'code.checkCode'                => 'promo_code_error',
        'code.unique'                   => 'promo_code_unique',
    	'type.require'                  => 'param_error',
    	'type.in'                       => 'param_error',
        'client_type.require'           => 'param_error',
        'client_type.in'                => 'param_error',
        'start_time.require'            => 'promo_code_start_time_require',
        'start_time.checkDate'          => 'promo_code_start_time_date',
        'end_time.checkDate'            => 'promo_code_end_time_date',
        'end_time.gt'                   => 'promo_code_end_time_gt',
        'products.checkProducts'        => 'param_error',
        'need_products.checkNeed'       => 'param_error',
        'max_times.require'             => 'promo_code_max_times_require',
        'max_times.integer'             => 'promo_code_max_times_error',
        'max_times.egt'                 => 'promo_code_max_times_error',
        'single_user_once.require'      => 'param_error',
        'single_user_once.in'           => 'param_error',
        'upgrade.require'               => 'param_error',
        'upgrade.in'                    => 'param_error',
        'host_upgrade.require'          => 'param_error',
        'host_upgrade.in'               => 'param_error',
        'renew.require'                 => 'param_error',
        'renew.in'                      => 'param_error',
        'loop.require'                  => 'param_error',
        'loop.in'                       => 'param_error',
        'cycle_limit.require'           => 'param_error',
        'cycle_limit.in'                => 'param_error',
        'cycle.require'                 => 'param_error',
        'cycle.checkCycle'              => 'param_error',
        'notes.max'                     => 'promo_code_notes_max',
        'status.require'                => 'param_error',
        'status.in'                     => 'param_error',
        'scene.require'                 => 'param_error',
        'scene.in'                      => 'param_error',
        'promo_code.requireIf'          => 'promo_code_require',
        'promo_code.checkCode'          => 'promo_code_error',
        'host_id.requireWithout'        => 'param_error',
        'host_id.integer'               => 'param_error',
        'host_id.gt'                    => 'param_error',
        'product_id.require'            => 'param_error',
        'product_id.integer'            => 'param_error',
        'product_id.gt'                 => 'param_error',
        'qty.requireIf'                 => 'param_error',
        'qty.integer'                   => 'param_error',
        'qty.gt'                        => 'param_error',
        'amount.require'                => 'param_error',
        'amount.float'                  => 'param_error',
        'amount.egt'                    => 'param_error',
        'billing_cycle_time.require'    => 'param_error',
        'billing_cycle_time.integer'    => 'param_error',
        'billing_cycle_time.egt'        => 'param_error',
    ];

    protected $scene = [
        'create' => ['code','type','client_type','start_time','end_time','products','need_products','max_times','single_user_once','upgrade','host_upgrade','renew','loop','cycle_limit','cycle','notes'],
        'update' => ['id','client_type','start_time','end_time','products','need_products','max_times','single_user_once','upgrade','host_upgrade','renew','loop','cycle_limit','cycle','notes'],
        'status' => ['status'],
        'apply' => ['scene','promo_code','host_id','product_id','qty','amount','billing_cycle_time'],
    ];

    public function checkDate($value)
    {
        if(!is_integer($value)){
            return false;
        }
        if($value<0){
            return false;
        }
        if(strtotime(date("Y-m-d H:i:s", $value))!=$value){
            return false;
        }
        return true;
    }

    public function checkCode($value)
    {
        if(strlen($value)!=9 || preg_match('/[^0-9A-Za-z]/', $value)){
            return false;
        }
        $match = 0;
        if(preg_match('/[0-9]/',$value)){
            $match += 1;
        }
        if(preg_match('/[a-z]/',$value)){
            $match += 1;
        }
        if(preg_match('/[A-Z]/',$value)){
            $match += 1;
        }
        if($match<3){
            return false;
        }
        return true;
    }

    public function checkProducts($value)
    {
        if(!is_array($value)){
            return false;
        }
        if(count($value)!=count(array_filter(array_unique($value)))){
            return false;
        }
        foreach ($value as $k=> $v) {
            if(!is_integer($v)){
                return false;
            }
            if($v<=0){
                return false;
            }
        }
        return true;
    }

    public function checkNeed($value)
    {
        if(!is_array($value)){
            return false;
        }
        if(count($value)!=count(array_filter(array_unique($value)))){
            return false;
        }
        foreach ($value as $k=> $v) {
            if(!is_integer($v)){
                return false;
            }
            if($v<=0){
                return false;
            }
        }
        return true;
    }

    public function checkCycle($value)
    {
        if(!is_array($value)){
            return false;
        }
        if(count($value)!=count(array_filter(array_unique($value)))){
            return false;
        }
        foreach ($value as $k=> $v) {
            if(!in_array($v, ['monthly','quarterly','semiannually','annually','biennially','triennially'])){
                return false;
            }
        }
        return true;
    }
}