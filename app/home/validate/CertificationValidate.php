<?php
namespace app\home\validate;

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
        'card_type'         => 'require|in:0,1',
        'card_number'       => 'require|max:255',
        'company'           => 'require|max:255',
        'company_organ_code'=> 'require|max:255',
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
        'create_company' => ['plugin_name', 'card_name', 'card_type', 'card_number', 'company', 'company_organ_code'],
    ];

    protected function checkPlugin($value)
    {
        $PluginModel = new PluginModel();

        $plugin = $PluginModel->where('name',$value)
            ->where('status',1)
            ->where('module','certification')
            ->find();

        if (empty($plugin)){
            return lang('plugin_is_not_exist');
        }

        return true;
    }
}