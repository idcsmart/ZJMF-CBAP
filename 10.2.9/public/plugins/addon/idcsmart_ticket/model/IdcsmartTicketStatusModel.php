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

    # 工单状态列表
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
