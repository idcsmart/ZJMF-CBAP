<?php
namespace addon\idcsmart_certification\validate;

use app\admin\model\PluginModel;
use think\Validate;

/**
 * 实名认证验证器
 */
class CertificationValidate extends Validate
{
    protected $rule = [
        'plugin_name'       => 'require|checkPlugin:thinkphp',
        'card_name'         => 'require|max:255',
        'card_type'         => 'require|in:1,2,3,4,5,6,7,8',
        'card_number'       => 'require|max:255',
        'company'           => 'require|max:255',
        'company_organ_code'=> 'require|max:255',
        'certification_open'=> 'require|in:0,1',
        'certification_approval'=> 'require|in:0,1',
        'certification_notice'=> 'require|in:0,1',
        'certification_update_client_name'=> 'require|in:0,1',
        'certification_upload'=> 'require|in:0,1',
        'certification_update_client_phone'=> 'require|in:0,1',
        'certification_uncertified_cannot_buy_product'=> 'require|in:0,1',
    ];

    protected $message  =   [
        'plugin_name.require'        => 'certification_plugin_name_require',
        'card_name.require'          => 'certification_card_name_require',
        'card_name.max'              => 'certification_card_name_max',
        'card_type.require'          => 'certification_card_type_require',
        'card_type.in'               => 'certification_card_type_in',
        'card_number.require'        => 'certification_card_number_require',
        'card_number.max'            => 'certification_card_number_max',
        'company.require'            => 'certification_company_require',
        'company.max'                => 'certification_company_max',
        'company_organ_code.require' => 'certification_company_organ_code_require',
        'company_organ_code.max'     => 'certification_company_organ_code_max',
    ];

    protected $scene = [
        'create_person' => ['plugin_name', 'card_name', 'card_type', 'card_number'],
        'create_company' => ['plugin_name', 'company', 'company_organ_code'],
        'set_config' => ['certification_open', 'certification_approval', 'certification_notice' ,'certification_update_client_name',
            'certification_upload', 'certification_update_client_phone', 'certification_uncertified_cannot_buy_product'],
    ];

    protected function checkPlugin($value)
    {
        $PluginModel = new PluginModel();

        $plugin = $PluginModel->where('name',$value)
            ->where('status',1)
            ->where('module','certification')
            ->find();

        if (empty($plugin)){
            return lang_plugins('plugin_is_not_exist');
        }

        return true;
    }
}