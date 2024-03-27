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

    /**
     * 时间 2023-02-28
     * @title 获取意见反馈类型
     * @desc 获取意见反馈类型
     * @author theworld
     * @version v1
     * @return array list - 意见反馈类型
     * @return int list[].id - 意见反馈类型ID 
     * @return string list[].name - 名称 
     * @return string list[].description - 描述
     */
    public function feedbackTypeList()
    {
        $list = $this->field('id,name,description')
            ->select()
            ->toArray();

        return ['list' => $list];
    }

    /**
     * 时间 2023-02-28
     * @title 添加意见反馈类型
     * @desc 添加意见反馈类型
     * @author theworld
     * @version v1
     * @param string name - 名称 required
     * @param string description - 描述 required
     */
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

    /**
     * 时间 2023-02-28
     * @title 编辑意见反馈类型
     * @desc 编辑意见反馈类型
     * @author theworld
     * @version v1
     * @param int id - 意见反馈类型ID required
     * @param string name - 名称 required
     * @param string description - 描述 required
     */
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

    /**
     * 时间 2023-02-28
     * @title 删除意见反馈类型
     * @desc 删除意见反馈类型
     * @author theworld
     * @version v1
     * @param int id - 意见反馈类型ID required
     */
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