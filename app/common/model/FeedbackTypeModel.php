<?php
namespace app\common\model;

use think\Model;

/**
 * @title 意见反馈类型模型
 * @desc 意见反馈类型模型
 * @use app\common\model\FeedbackTypeModel
 */
class FeedbackTypeModel extends Model
{
    protected $name = 'feedback_type';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'name'    		=> 'string',
        'description'   => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    public function feedbackTypeList()
    {
        $list = $this->field('id,name,description')
            ->select()
            ->toArray();

        return ['list' => $list];
    }

    public function createFeedbackType($param)
    {
        $this->startTrans();
        try{
            $this->create([
                'name' => $param['name'],
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

    public function updateFeedbackType($param)
    {
        $feedbackType = $this->find($param['id']);
        if(empty($feedbackType)){
            return ['status'=>400, 'msg'=>lang('feedback_type_is_not_exist')];
        }

        $this->startTrans();
        try{
            $this->update([
                'name' => $param['name'],
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

    public function deleteFeedbackType($id)
    {
        $feedbackType = $this->find($id);
        if(empty($feedbackType)){
            return ['status'=>400, 'msg'=>lang('feedback_type_is_not_exist')];
        }
        $count = FeedbackModel::where('feedback_type_id', $id)->count();
        if($count>0){
            return ['status' => 400, 'msg' => lang('cannot_delete_feedback_type')];
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