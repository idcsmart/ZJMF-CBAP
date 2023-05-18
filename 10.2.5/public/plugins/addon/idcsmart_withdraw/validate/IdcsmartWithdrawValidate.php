<?php
namespace addon\idcsmart_withdraw\validate;

use think\Validate;

/**
 * 提现验证
 */
class IdcsmartWithdrawValidate extends Validate
{
    protected $rule = [
        'id'                    => 'require|integer|gt:0',
        'status'                => 'require|in:1,2',
        'reason'                => 'requireIf:status,2|max:1000',
        'source'                => 'require|unique:addon_idcsmart_withdraw_rule',
        'method'                => 'require|checkMethod:thinkphp',
        'process'               => 'require|in:artificial,auto',
        'min'                   => 'float|egt:0',
        'max'                   => 'float|egt:min',
        'cycle'                 => 'require|in:day,week,month',
        'cycle_limit'           => 'integer|egt:0',
        'withdraw_fee_type'     => 'require|in:fixed,percent',
        'withdraw_fee'          => 'float|egt:0',
        'percent'               => 'float|egt:0|elt:100',
        'percent_min'           => 'float|egt:0',
        'amount'                => 'require|float|gt:0',
        'card_number'           => 'max:100',
        'name'                  => 'max:20',
        'account'               => 'max:100',
        'transaction_number'    => 'require|alphaNum',
        'method_id'             => 'require|integer|gt:0',
        'notes'                 => 'max:1000',
        'fee'                   => 'float|egt:0|elt:amount',    
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'id.gt'                         => 'id_error',
        'status.require'                => 'param_error',
        'status.in'                     => 'param_error',
        'reason.requireIf'              => 'addon_idcsmart_withdraw_reason_require',
        'reason.max'                    => 'addon_idcsmart_withdraw_reason_max',
        'source.require'                => 'addon_idcsmart_withdraw_source_require',
        'source.unique'                 => 'addon_idcsmart_withdraw_source_unique',
        'method.require'                => 'addon_idcsmart_withdraw_method_require',
        'method.checkMethod'            => 'param_error',
        'process.require'               => 'addon_idcsmart_withdraw_process_require',
        'process.in'                    => 'param_error',
        'min.float'                     => 'addon_idcsmart_withdraw_min_error',
        'min.egt'                       => 'addon_idcsmart_withdraw_min_error',
        'max.float'                     => 'addon_idcsmart_withdraw_max_error',
        'max.egt'                       => 'addon_idcsmart_withdraw_max_error',
        'cycle.require'                 => 'addon_idcsmart_withdraw_cycle_require',
        'cycle.in'                      => 'param_error',
        'cycle_limit.integer'           => 'addon_idcsmart_withdraw_cycle_limit_error',
        'cycle_limit.egt'               => 'addon_idcsmart_withdraw_cycle_limit_error',
        'withdraw_fee_type.require'     => 'addon_idcsmart_withdraw_withdraw_fee_type_require',
        'withdraw_fee_type.in'          => 'param_error',
        'withdraw_fee.float'            => 'addon_idcsmart_withdraw_withdraw_fee_error',
        'withdraw_fee.egt'              => 'addon_idcsmart_withdraw_withdraw_fee_error',
        'percent.float'                 => 'addon_idcsmart_withdraw_percent_error',
        'percent.egt'                   => 'addon_idcsmart_withdraw_percent_error',
        'percent.elt'                   => 'addon_idcsmart_withdraw_percent_error',
        'percent_min.float'             => 'addon_idcsmart_withdraw_percent_min_error',
        'percent_min.egt'               => 'addon_idcsmart_withdraw_percent_min_error',
        'amount.require'                => 'addon_idcsmart_withdraw_amount_require',
        'amount.float'                  => 'addon_idcsmart_withdraw_amount_error',
        'amount.gt'                     => 'addon_idcsmart_withdraw_amount_error',
        'card_number.max'               => 'addon_idcsmart_withdraw_card_number_max',
        'name.max'                      => 'addon_idcsmart_withdraw_name_max',
        'account.max'                   => 'addon_idcsmart_withdraw_account_max',
        'method.in'                     => 'param_error',
        'transaction_number.require'    => 'addon_idcsmart_withdraw_transaction_number_require', 
        'transaction_number.alphaNum'   => 'transaction_number_formatted_incorrectly', 
        'method_id.require'             => 'param_error',
        'method_id.integer'             => 'param_error',
        'method_id.gt'                  => 'param_error',
        'notes.max'                     => 'addon_idcsmart_withdraw_notes_max',
        'fee.float'                     => 'addon_idcsmart_withdraw_fee_error',
        'fee.egt'                       => 'addon_idcsmart_withdraw_fee_error',
        'fee.elt'                       => 'addon_idcsmart_withdraw_fee_error',
    ];

    protected $scene = [
        'audit' => ['id', 'status', 'reason'],
        'save' => ['method', 'process', 'min', 'max', 'cycle', 'cycle_limit', 'withdraw_fee_type', 'withdraw_fee', 'percent', 'percent_min'],
        'confirm_remit' => ['id', 'transaction_number'],
        'update_transaction' => ['id', 'transaction_number'],
    ];

    # 申请提现验证
    public function sceneWithdraw()
    {
        return $this->only(['source', 'amount', 'method_id', 'card_number', 'name', 'account', 'notes', 'fee'])
            ->remove('source', 'unique');
    }

    # 修改提现状态验证
    public function sceneUpdate()
    {
        return $this->only(['id','status'])
            ->remove('status', 'in')
            ->append('status', 'in:0,1');
    }

    # 验证提现方式
    public function checkMethod($value)
    {
        if(!is_array($value)){
            return false;
        }
        if(count(array_unique(array_filter($value)))!=count($value)){
            return false;
        }
        foreach ($value as $k => $v) {
            if(!is_integer($v)){
                return false;
            }
            if($v<0){
                return false;
            }
        }
        return true;
    }
}