<?php 
namespace server\idcsmart_common\model;

use think\Model;

class IdcsmartCommonProductCustomFieldModel extends Model
{
    protected $name = 'module_idcsmart_common_product_custom_field';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'product_id'             => 'int',
        'name'                   => 'string',
        'type'                   => 'string',
        'description'            => 'string',
        'options'                => 'string',
        'regexpr'                => 'string',
        'require'                => 'int',
        'admin_only'             => 'int',
        'param'                  => 'string',
        'create_time'            => 'int',
        'update_time'            => 'int',
    ];

    public function listCustomField($param)
    {
        $list = $this->field('id,name,type,admin_only')
            ->where('product_id',$param['product_id'])
            ->select()
            ->toArray();

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'list' => $list?:[]
            ]
        ];
    }

    public function indexCustomField($param)
    {
        
    }

    public function createCustomField($param)
    {
        
    }

    public function updateCustomField($param)
    {
        
    }

    public function deleteCustomField($param)
    {
        
    }

    public function statusCustomField($param)
    {
        
    }

}