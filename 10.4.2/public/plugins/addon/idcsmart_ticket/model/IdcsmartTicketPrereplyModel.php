<?php
namespace addon\idcsmart_ticket\model;

use think\Model;

/*
 * @author wyh
 * @time 2022-06-20
 */
class IdcsmartTicketPrereplyModel extends Model
{
    protected $name = 'addon_idcsmart_ticket_prereply';

    # 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'content'                          => 'string',
    ];

    /**
     * 时间 2022-10-21
     * @title 工单预设回复列表
     * @desc 工单预设回复列表
     * @author wyh
     * @version v1
     * @return array list - 工单预设回复列表
     * @return int list[].id - ID
     * @return string list[].content - 内容
     */
    public function ticketPrereplyList()
    {
        $list = $this->select()
            ->toArray();

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'list' => $list
            ]
        ];
    }

    /**
     * 时间 2022-10-21
     * @title 工单预设回复详情
     * @desc 工单预设回复详情
     * @author wyh
     * @version v1
     * @param int id - 工单预设回复ID
     * @return int id - ID
     * @return string content - 内容
     */
    public function ticketPrereplyIndex($param)
    {
        $ticketPrereply = $this->find($param['id']);

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'ticket_prereply' => $ticketPrereply?:(object)[]
            ]
        ];
    }

    /**
     * 时间 2022-10-21
     * @title 创建工单预设回复
     * @desc 创建工单预设回复
     * @author wyh
     * @version v1
     * @param string content - 内容
     */
    public function ticketPrereplyCreate($param)
    {
        $this->startTrans();

        try{
            $this->insert([
                'content' => $param['content']??''
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('error_message')];
        }
        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-10-21
     * @title 编辑工单预设回复
     * @desc 编辑工单预设回复
     * @author wyh
     * @version v1
     * @param int id - 工单预设回复ID
     * @param string content - 内容
     */
    public function ticketPrereplyUpdate($param)
    {
        $this->startTrans();

        try{
            $ticketPrereply = $this->find($param['id']);

            if (empty($ticketPrereply)){
                throw new \Exception(lang_plugins('ticket_ticket_prereply_is_not_exist'));
            }
            $ticketPrereply->save([
                'content' => $param['content']??''
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('error_message')];
        }
        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-10-21
     * @title 删除工单预设回复
     * @desc 删除工单预设回复
     * @author wyh
     * @version v1
     * @param int id - 工单预设回复ID
     */
    public function ticketPrereplyDelete($param)
    {
        $this->startTrans();

        try{
            $ticketPrereply = $this->find($param['id']);

            if (empty($ticketPrereply)){
                throw new \Exception(lang_plugins('ticket_ticket_prereply_is_not_exist'));
            }
            $ticketPrereply->delete();

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('error_message')];
        }
        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }


}
