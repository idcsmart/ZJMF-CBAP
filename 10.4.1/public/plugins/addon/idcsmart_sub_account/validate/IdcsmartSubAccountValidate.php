<?php
namespace addon\idcsmart_sub_account\validate;

use think\Validate;

/**
 * 子账户管理验证
 */
class IdcsmartSubAccountValidate extends Validate
{
    protected $regex = ['password' => '/^[^\x{4e00}-\x{9fa5}\x{9fa6}-\x{9fef}\x{3400}-\x{4db5}\x{20000}-\x{2ebe0}\s]{6,32}$/u'];

    protected $rule = [
        'id'                => 'require|integer|gt:0',
        'username'          => 'require|max:50',
        'email'             => 'requireWithout:phone|email|unique:client',
        'phone_code'        => 'requireWithout:email',
        'phone'             => 'requireWithout:email|max:11|number|unique:client,phone_code^phone',
        'password'          => 'require|regex:password',
        'project_id'        => 'array', 
        'auth'              => 'array',
        'notice'            => 'checkNotice:thinkphp',
        'visible_product'   => 'requireWithout:project_id|in:module,host',
        'module'            => 'requireIf:visible_product,module|array',
        'host_id'           => 'requireIf:visible_product,host|array',
        'status'            => 'require|in:0,1'
    ];

    protected $message = [
        'id.require'                => 'id_error',
        'id.integer'                => 'id_error',
        'id.gt'                     => 'id_error',
        'username.require'          => 'addon_idcsmart_sub_account_name_require',
        'username.max'              => 'addon_idcsmart_sub_account_name_max',
        'email.requireWithout'      => 'addon_idcsmart_sub_account_please_enter_vaild_email',
        'email.email'               => 'addon_idcsmart_sub_account_please_enter_vaild_email', 
        'email.unique'              => 'addon_idcsmart_sub_account_email_has_been_registered',   
        'phone_code.requireWithout' => 'addon_idcsmart_sub_account_please_select_phone_code', 
        'phone.requireWithout'      => 'addon_idcsmart_sub_account_please_enter_vaild_phone', 
        'phone.max'                 => 'addon_idcsmart_sub_account_please_enter_vaild_phone', 
        'phone.number'              => 'addon_idcsmart_sub_account_please_enter_vaild_phone',
        'phone.unique'              => 'addon_idcsmart_sub_account_phone_has_been_registered',  
        'password.require'          => 'addon_idcsmart_sub_account_please_enter_password', 
        'password.regex'            => 'addon_idcsmart_sub_account_password_formatted_incorrectly', 
        'project_id.require'        => 'addon_idcsmart_sub_account_project_id_require', 
        'project_id.array'          => 'param_error', 
        'auth.array'                => 'param_error', 
        'notice.checkNotice'        => 'param_error',
        'visible_product.requireWithout' => 'param_error',
        'visible_product.in'        => 'param_error',
        'module.requireIf'          => 'addon_idcsmart_sub_account_module_require',
        'module.array'              => 'param_error',
        'host_id.requireIf'         => 'addon_idcsmart_sub_account_host_id_require',
        'host_id.array'             => 'param_error',
        'status.require'            => 'param_error',
        'status.in'                 => 'param_error',
    ];

    protected $scene = [
        'create' => ['username', 'email', 'phone_code', 'phone', 'password', 'project_id', 'auth', 'notice', 'visible_product', 'module', 'host_id'],
        'status' => ['id', 'status'],
    ];

    # 修改验证
    public function sceneUpdate()
    {
        return $this->only(['id', 'email', 'phone_code', 'phone', 'password', 'project_id', 'auth', 'notice', 'visible_product', 'module', 'host_id'])
            ->remove('password', 'require');
    }

    public function checkNotice($value){
        if(!is_array($value)){
            return false;
        }
        if(count(array_filter(array_unique($value)))!=count($value)){
            return false;
        }
        if(count(array_diff($value, ['product','marketing','ticket','cost','recommend','system']))>0){
            return false;
        }
        return true;
    }
}