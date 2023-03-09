<?php
namespace app\common\model;

use think\Model;

/**
 * @title 友情链接模型
 * @desc 友情链接模型
 * @use app\common\model\FriendlyLinkModel
 */
class FriendlyLinkModel extends Model
{
    protected $name = 'friendly_link';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'name'          => 'string',
        'url'           => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    public function friendlyLinkList()
    {
        $list = $this->field('id,name,url')
            ->select()
            ->toArray();

        return ['list' => $list];
    }

    public function createFriendlyLink($param)
    {
        $this->startTrans();
        try{
            $this->create([
                'name' => $param['name'],
                'url' => $param['url'],
                'create_time' => time(),
            ]);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('create_fail')];
        }

        return ['status'=>200, 'msg'=>lang('create_success')];
    }

    public function updateFriendlyLink($param)
    {
        $friendlyLink = $this->find($param['id']);
        if(empty($friendlyLink)){
            return ['status'=>400, 'msg'=>lang('friendly_link_is_not_exist')];
        }

        $this->startTrans();
        try{
            $this->update([
                'name' => $param['name'],
                'url' => $param['url'],
                'update_time' => time(),
            ], ['id' => $param['id']]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail')];
        }

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    public function deleteFriendlyLink($id)
    {
        $friendlyLink = $this->find($id);
        if(empty($friendlyLink)){
            return ['status'=>400, 'msg'=>lang('friendly_link_is_not_exist')];
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