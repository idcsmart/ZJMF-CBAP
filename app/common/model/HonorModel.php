<?php
namespace app\common\model;

use think\Model;

/**
 * @title 荣誉资质模型
 * @desc 荣誉资质模型
 * @use app\common\model\HonorModel
 */
class HonorModel extends Model
{
    protected $name = 'honor';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'name'          => 'string',
        'img'           => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    public function honorList()
    {
        $list = $this->field('id,name,img')
            ->select()
            ->toArray();

        return ['list' => $list];
    }

    public function createHonor($param)
    {
        $this->startTrans();
        try{
            $this->create([
                'name' => $param['name'],
                'img' => $param['img'],
                'create_time' => time(),
            ]);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('create_fail')];
        }

        return ['status'=>200, 'msg'=>lang('create_success')];
    }

    public function updateHonor($param)
    {
        $honor = $this->find($param['id']);
        if(empty($honor)){
            return ['status'=>400, 'msg'=>lang('honor_is_not_exist')];
        }

        $this->startTrans();
        try{
            $this->update([
                'name' => $param['name'],
                'img' => $param['img'],
                'update_time' => time(),
            ], ['id' => $param['id']]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail')];
        }

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    public function deleteHonor($id)
    {
        $honor = $this->find($id);
        if(empty($honor)){
            return ['status'=>400, 'msg'=>lang('honor_is_not_exist')];
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