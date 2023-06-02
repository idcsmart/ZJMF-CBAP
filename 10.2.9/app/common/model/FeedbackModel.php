<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 意见反馈模型
 * @desc 意见反馈模型
 * @use app\common\model\FeedbackModel
 */
class FeedbackModel extends Model
{
    protected $name = 'feedback';

    // 设置字段信息
    protected $schema = [
        'id'      		    => 'int',
        'client_id'         => 'int',
        'feedback_type_id'  => 'int',
        'title'    		    => 'string',
        'description'       => 'string',
        'contact'    		=> 'string',
        'attachment'        => 'string',
        'create_time'       => 'int',
    ];

    public function feedbackList($param)
    {
        
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'a.'.$param['orderby'] : 'a.id';

        $count = $this->alias('a')
            ->field('a.id')
            ->count();
        $url = request()->domain() . '/upload/common/default/';
        $list = $this->alias('a')
            ->field('a.id,a.title,b.name type,a.description,a.client_id,c.username,a.contact,a.attachment,a.create_time')
            ->leftjoin('feedback_type b', 'b.id=a.feedback_type_id')
            ->leftjoin('client c', 'c.id=a.client_id')
            ->withAttr('attachment',function ($value) use ($url){
                $attachment = !empty($value) ? explode(',',$value) : [];
                foreach ($attachment as &$vv){
                    $vv = $url . $vv;
                }
                return $attachment;
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        return ['list' => $list, 'count' => $count];
    }

    public function createFeedback($param)
    {
        $clientId = get_client_id();

        $type = FeedbackTypeModel::find($param['type']);
        if(empty($type)){
            return ['status' => 400, 'msg' => lang('feedback_type_is_not_exist')];
        }
        $param['attachment'] = $param['attachment'] ?? [];
        foreach ($param['attachment'] as $key => $value) {
            if(!file_exists(UPLOAD_DEFAULT.$value)){
                return ['status' => 400, 'msg' => lang('upload_file_is_not_exist')];
            }
        }

        $this->startTrans();
        try{
            $this->create([
                'client_id' => $clientId,
                'feedback_type_id' => $param['type'],
                'title' => $param['title'],
                'description' => $param['description'],
                'contact' => $param['contact'] ?? '',
                'attachment' => implode(',', $param['attachment']),
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