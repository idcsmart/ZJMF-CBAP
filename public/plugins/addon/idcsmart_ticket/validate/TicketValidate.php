<?php

namespace addon\idcsmart_ticket\validate;

use addon\idcsmart_ticket\model\IdcsmartTicketModel;
use addon\idcsmart_ticket\model\IdcsmartTicketTypeModel;
use app\admin\model\AdminModel;
use app\admin\model\AdminRoleModel;
use app\common\model\ClientModel;
use app\common\model\HostModel;
use think\Validate;

/**
 * @title 工单验证
 * @description 接口说明:工单验证
 */
class TicketValidate extends Validate
{
    protected $rule = [
        'id'                       => 'require',
        'client_id'                => 'require|checkClient:thinkphp',
        'title'                    => 'require|max:150',
        'ticket_type_id'           => 'require|checkTicketType:thinkphp',
        'content'                  => 'max:3000',
        'host_ids'                 => 'array|checkHostIds:thinkphp',
        'attachment'               => 'array',
        'admin_id'                 => 'require|integer',
    ];

    protected $message = [
        'title.require'            => 'ticket_title_require',
        'title.max'                => 'ticket_title_max',
        'ticket_type_id.require'   => 'ticket_type_id_require',
        'content.max'              => 'ticket_content_max',
        'host_ids.array'           => 'param_error',
        'attachment.array'         => 'param_error',
    ];

    protected $scene = [
        'create'               => ['title','ticket_type_id','content','host_ids','attachment'],
        'create_admin'         => ['client_id','title','ticket_type_id','content','host_ids','attachment'],
        'update_status'         => ['ticket_type_id','host_ids'],
        'forward'         => ['admin_id','ticket_type_id'],
    ];

    # 工单回复验证
    public function sceneReply()
    {
        return $this->only(['id','content','attachment'])
            ->append('content','require');
    }

    protected function checkTicketType($value)
    {
        $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();

        $exist = $IdcsmartTicketTypeModel->find($value);

        if (empty($exist)){
            return lang_plugins('ticket_type_id_error');
        }else {
            return true;
        }
    }

    protected function checkHostIds($value,$rule,$data)
    {
        $clientId = $data['client_id']??get_client_id();
        $id = $data['id']??0;
        $IdcsmartTicketModel = new IdcsmartTicketModel();
        $ticket = $IdcsmartTicketModel->find($id);
        if (!empty($ticket)){
            $clientId = $ticket['client_id'];
        }

        $HostModel = new HostModel();

        foreach ($value as $item){
            $exist = $HostModel->where('client_id',$clientId)
                ->where('id',$item)
                ->find();
            if (empty($exist)){
                return lang_plugins('ticket_host_is_not_exist');
            }
        }

        return true;
    }

    protected function checkClient($value)
    {
        $ClientModel = new ClientModel();

        $client = $ClientModel->find($value);

        if (empty($client)){
            return lang_plugins('client_is_not_exist');
        }

        return true;
    }

    // protected function checkAdmin($value,$rule,$data)
    // {
    //     $AdminModel = new AdminModel();

    //     $admin = $AdminModel->alias('a')
    //         ->leftJoin('admin_role_link arl','arl.admin_id=a.id')
    //         ->where('a.id',$value)
    //         ->where('arl.admin_role_id',$data['admin_role_id'])
    //         ->find();

    //     if (empty($admin)){
    //         return lang_plugins('admin_is_not_exist');
    //     }

    //     return true;
    // }
}