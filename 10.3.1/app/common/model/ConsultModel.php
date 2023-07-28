<?php
namespace app\common\model;

use think\Model;

/**
 * @title 方案咨询模型
 * @desc 方案咨询模型
 * @use app\common\model\ConsultModel
 */
class ConsultModel extends Model
{
    protected $name = 'consult';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'client_id'     => 'int',
        'matter'        => 'string',
        'contact'       => 'string',
        'company'       => 'string',
        'phone'         => 'string',
        'email'         => 'string',
        'create_time'   => 'int',
    ];

    public function consultList($param)
    {
        
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'a.'.$param['orderby'] : 'a.id';

        $count = $this->alias('a')
            ->field('a.id')
            ->count();

        $list = $this->alias('a')
            ->field('a.id,a.matter,a.contact,a.company,a.phone,a.email,a.client_id,c.username,a.create_time')
            ->leftjoin('client c', 'c.id=a.client_id')
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        return ['list' => $list, 'count' => $count];
    }

    public function createConsult($param)
    {
        $clientId = get_client_id();

        $this->startTrans();
        try{
            $this->create([
                'client_id' => $clientId,
                'matter' => $param['matter'],
                'contact' => $param['contact'],
                'company' => $param['company'] ?? '',
                'phone' => $param['phone'] ?? '',
                'email' => $param['email'] ?? '',
                'create_time' => time(),
            ]);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('create_fail')];
        }

        return ['status'=>200, 'msg'=>lang('create_success')];
    }


}