<?php
namespace addon\idcsmart_ticket\model;

use think\Model;

/*
 * @author wyh
 * @time 2022-06-20
 */
class IdcsmartTicketStatusModel extends Model
{
    protected $name = 'addon_idcsmart_ticket_status';

    # 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'name'                             => 'string',
        'color'                            => 'string',
        'status'                           => 'int',
        'default'                          => 'int',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
    ];

    /**
     * 时间 2022-10-21
     * @title 工单状态列表
     * @desc 工单状态列表
     * @author wyh
     * @version v1
     * @return array list - 工单状态列表
     * @return int list[].id - ID
     * @return string list[].name - 工单状态
     * @return string list[].color - 状态颜色
     * @return int list[].status - 完结状态:1完结,0未完结
     * @return int list[].default - 是否默认状态:0否,1是,默认状态无法修改删除
     */
    public function ticketStatusList()
    {
        $list = $this->field('id,name,color,status,default')
            ->select()
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
     * @title 工单状态详情
     * @desc 工单状态详情
     * @author wyh
     * @version v1
     * @param int id - 工单状态ID
     * @return int id - ID
     * @return string name - 工单状态
     * @return string color - 状态颜色
     * @return int status - 完结状态:1完结,0未完结
     */
    public function ticketStatusIndex($param)
    {
        $ticketStatus = $this->field('id,name,color,status')
            ->where('id',intval($param['id']))
            ->find();

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'ticket_status' => $ticketStatus?:(object)[]
            ],
        ];
    }

    /**
     * 时间 2022-10-21
     * @title 创建工单状态
     * @desc 创建工单状态
     * @author wyh
     * @version v1
     * @param string name - 工单状态ID
     * @param string color - 状态颜色
     * @param int status - 完结状态:1完结,0未完结
     */
    public function ticketStatusCreate($param)
    {
        $this->startTrans();

        try{
            $this->insert([
                'name' => $param['name'],
                'color' => $param['color'],
                'status' => $param['status']??0,
                'create_time' =>time()
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
        ];
    }

    /**
     * 时间 2022-10-21
     * @title 编辑工单状态
     * @desc 编辑工单状态
     * @author wyh
     * @version v1
     * @param int id - 工单状态ID
     * @param string name - 工单状态ID
     * @param string color - 状态颜色
     * @param int status - 完结状态:1完结,0未完结
     */
    public function ticketStatusUpdate($param)
    {
        $this->startTrans();

        try{
            $ticketStatus = $this->find($param['id']);

            if (empty($ticketStatus)){
                throw new \Exception(lang_plugins('ticket_ticket_status_is_not_exist'));
            }

            if ($ticketStatus['default']==1){
                throw new \Exception(lang_plugins('ticket_ticket_status_cannot_update'));
            }

            $ticketStatus->save([
                'name' => $param['name'],
                'color' => $param['color'],
                'status' => $param['status']??0,
                'update_time' =>time()
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
        ];
    }

    /**
     * 时间 2022-10-21
     * @title 删除工单状态
     * @desc 删除工单状态
     * @author wyh
     * @version v1
     * @param int id - 工单状态ID
     */
    public function ticketStatusDelete($param)
    {
        $this->startTrans();

        try{
            $ticketStatus = $this->find($param['id']);

            if (empty($ticketStatus)){
                throw new \Exception(lang_plugins('ticket_ticket_status_is_not_exist'));
            }

            if ($ticketStatus['default']==1){
                throw new \Exception(lang_plugins('ticket_ticket_status_cannot_delete'));
            }

            $ticketStatus->delete();

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
        ];
    }

}
