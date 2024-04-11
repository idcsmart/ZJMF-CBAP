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

    /**
     * 时间 2023-02-28
     * @title 意见反馈列表
     * @desc 意见反馈列表
     * @author theworld
     * @version v1
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 意见反馈
     * @return int list[].id - 意见反馈ID 
     * @return string list[].title - 标题 
     * @return string list[].type - 类型 
     * @return string list[].description - 描述
     * @return int list[].client_id - 用户ID 
     * @return string list[].username - 用户名
     * @return string list[].contact - 联系方式
     * @return array list[].attachment - 附件
     * @return int list[].create_time - 反馈时间
     * @return int count - 意见反馈总数
     */
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

    /**
     * 时间 2023-02-28
     * @title 提交意见反馈
     * @desc 提交意见反馈
     * @author theworld
     * @version v1
     * @param int param.type - 类型 required
     * @param string param.title - 标题 required
     * @param string param.description - 描述 required
     * @param array param.attachment - 附件
     * @param string param.contact - 联系方式
     * @return  int status - 状态,200=成功,400=失败
     * @return  string msg - 信息
     */
    public function createFeedback($param)
    {
        $clientId = get_client_id();

        $type = FeedbackTypeModel::find($param['type']);
        if(empty($type)){
            return ['status' => 400, 'msg' => lang('feedback_type_is_not_exist')];
        }
        // $param['attachment'] = $param['attachment'] ?? [];
        // foreach ($param['attachment'] as $key => $value) {
        //     if(!file_exists(UPLOAD_DEFAULT.$value)){
        //         return ['status' => 400, 'msg' => lang('upload_file_is_not_exist')];
        //     }
        // }

        $this->startTrans();
        try{
            $this->create([
                'client_id' => $clientId,
                'feedback_type_id' => $param['type'],
                'title' => $param['title'],
                'description' => $param['description'],
                'contact' => $param['contact'] ?? '',
                'attachment' => '',//implode(',', $param['attachment']),
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