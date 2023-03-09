<?php
namespace app\common\model;

use think\Model;

/**
 * @title 合作伙伴模型
 * @desc 合作伙伴模型
 * @use app\common\model\PartnerModel
 */
class PartnerModel extends Model
{
    protected $name = 'partner';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'name'          => 'string',
        'img'           => 'string',
        'description'   => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    public function partnerList()
    {
        $list = $this->field('id,name,img,description')
            ->select()
            ->toArray();

        return ['list' => $list];
    }

    public function createPartner($param)
    {
        $this->startTrans();
        try{
            $this->create([
                'name' => $param['name'],
                'img' => $param['img'],
                'description' => $param['description'],
                'create_time' => time(),
            ]);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('create_fail')];
        }

        return ['status'=>200, 'msg'=>lang('create_success')];
    }

    public function updatePartner($param)
    {
        $partner = $this->find($param['id']);
        if(empty($partner)){
            return ['status'=>400, 'msg'=>lang('partner_is_not_exist')];
        }

        $this->startTrans();
        try{
            $this->update([
                'name' => $param['name'],
                'img' => $param['img'],
                'description' => $param['description'],
                'update_time' => time(),
            ], ['id' => $param['id']]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail')];
        }

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    public function deletePartner($id)
    {
        $partner = $this->find($id);
        if(empty($partner)){
            return ['status'=>400, 'msg'=>lang('partner_is_not_exist')];
        }

        $this->startTrans();
        try{
            $this->destroy($id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('delete_fail')];
        }

        return ['status'=>200,'msg'=>lang('delete_success')];
    }
}