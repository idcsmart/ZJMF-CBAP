<?php
namespace addon\idcsmart_certification\model;

use think\Model;
use think\Db;

/**
 * @title 企业认证模型
 * @desc 企业认证模型
 * @use addon\idcsmart_certification\model\CertificationCompanyModel
 */
class CertificationCompanyModel extends Model
{
	protected $name = 'addon_idcsmart_certification_company';

	// 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'client_id'     => 'int',
        'card_name'     => 'string',
        'card_type'     => 'string',
        'card_number'   => 'string',
        'phone'         => 'string',
        'status'        => 'int',
        'company'  => 'string',
        'company_organ_code' => 'string',
        'img_one'       => 'string',
        'img_two'       => 'string',
        'img_three'     => 'string',
        'certify_id'    => 'string',
        'auth_fail'     => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
        'custom_fields1'=> 'string',
        'custom_fields2'=> 'string',
        'custom_fields3'=> 'string',
        'custom_fields4'=> 'string',
        'custom_fields5'=> 'string',
        'custom_fields6'=> 'string',
        'custom_fields7'=> 'string',
        'custom_fields8'=> 'string',
        'custom_fields9'=> 'string',
        'custom_fields10'    => 'string',
    ];

}
