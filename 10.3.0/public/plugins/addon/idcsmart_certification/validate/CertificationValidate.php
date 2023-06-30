<?php
namespace addon\idcsmart_certification\validate;

use addon\idcsmart_certification\logic\IdcsmartCertificationLogic;
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
        'card_number'       => 'require|max:255|checkAge:thinkphp',
        'company'           => 'require|max:255',
        'company_organ_code'=> 'require|max:255',
        'certification_open'=> 'require|in:0,1',
        'certification_approval'=> 'require|in:0,1',
        'certification_notice'=> 'require|in:0,1',
        'certification_update_client_name'=> 'require|in:0,1',
        'certification_upload'=> 'require|in:0,1',
        'certification_update_client_phone'=> 'require|in:0,1',
        'certification_uncertified_cannot_buy_product'=> 'require|in:0,1',
        'certification_age'=> 'require|egt:0',
        'certification_age_open'=> 'require|in:0,1',
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
            'certification_upload', 'certification_update_client_phone', 'certification_uncertified_cannot_buy_product','certification_age','certification_age_open'],
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

    protected function checkAge($value,$rule,$data){

        $IdcsmartCertificationLogic = new IdcsmartCertificationLogic();
        $config = $IdcsmartCertificationLogic->getConfig();
        $ageOpen = $config['certification_age_open']??0;
        $age = $config['certification_age']??18;

        // 当为身份证时才验证
        if ($data['card_type']==1 && $ageOpen){
            if (strlen($value)==18){
                $birthYear = intval(substr($value,6,4));
                $birthMonth = intval(substr($value,10,2));
                $birthDay = intval(substr($value,12,2));
                $birthMonthAndDay = substr($value,10,4);
            }elseif (strlen($value)==15){
                $birthYear = intval("19".substr($value,6,2));
                $birthMonth = intval(substr($value,8,2));
                $birthDay = intval(substr($value,10,2));
                $birthMonthAndDay = substr($value,8,4);
            }else{
                return "身份证格式错误";
            }
            $birthDate = $birthYear . '-' . $birthMonth . '-' . $birthDay . " 00:00:00";
            $diff = (date('Y')-$birthYear-1) + ((date('md')>=$birthMonthAndDay)?1:0);
            if ($diff<$age){
                return "由于您的年龄低于{$age}岁,暂时无法认证";
            }
            /*if ((time()-strtotime($birthDate)) <= $age*365*24*60*60){
                return "由于您的年龄低于{$age}岁,暂时无法认证";
            }*/
        }
        return true;
    }
}