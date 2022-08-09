<?php

namespace addon\idcsmart_ticket\validate;

use addon\idcsmart_ticket\model\IdcsmartTicketModel;
use addon\idcsmart_ticket\model\IdcsmartTicketTypeModel;
use app\admin\model\AdminRoleLinkModel;
use app\admin\model\AdminRoleModel;
use app\common\model\ClientModel;
use app\common\model\HostModel;
use think\Validate;

/**
 * @title 内部工单验证
 * @description 接口说明:内部工单验证
 */
class TicketInternalValidate extends Validate
{
    protected $rule = [
        'id'                       => 'require',
        'ticket_id'                => 'checkTicketId:thinkphp',
        'title'                    => 'require|max:150',
        'ticket_type_id'           => 'require|checkTicketType:thinkphp',
        'priority'                 => 'require|in:medium,high',
        'admin_role_id'            => 'require|checkAdminRole:thinkphp',
        'admin_id'                 => 'checkAdmin:thinkphp',
        'client_id'                => 'checkClient:thinkphp',
        'content'                  => 'max:3000',
        'host_ids'                 => 'array|checkHostIds:thinkphp',
        'attachment'               => 'array',
    ];

    protected $message = [
        'title.require'            => 'ticket_title_require',
        'title.max'                => 'ticket_title_max',
        'ticket_type_id.require'   => 'ticket_type_id_require',
        'priority.require'         => 'ticket_priority_require',
        'priority.in'              => 'ticket_priority_in',
        'admin_role_id.require'    => 'ticket_admin_role_id_require',
        'content.max'              => 'ticket_content_max',
        'host_ids.array'           => 'param_error',
        'attachment.array'         => 'param_error',
    ];

    protected $scene = [
        'create'               => ['ticket_id','title','ticket_type_id','priority','admin_role_id','admin_id','client_id','content','host_ids','attachment'],
        'reply'                => ['id','content','attachment'],
        'forward'              => ['id','admin_role_id','admin_id'],
    ];

    # 工单回复验证
    public function sceneReply()
    {
        return $this->only(['id','content','attachment'])
            ->append('content','require');
    }

    protected function checkTicketId($value)
    {
        $IdcsmartTicketModel = new IdcsmartTicketModel();
        $exist = $IdcsmartTicketModel->find($value);
        if (empty($exist)){
            return lang_plugins('ticket_is_not_exist');
        }

        return true;
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
        $HostModel = new HostModel();

        foreach ($value as $item){
            $exist = $HostModel->where('client_id',$data['client_id'])
                ->where('id',$item)
                ->find();
            if (empty($exist)){
                return lang_plugins('ticket_host_is_not_exist');
            }
        }

        return true;
    }

    protected function checkAdminRole($value)
    {
        $AdminRoleModel = new AdminRoleModel();
        $exist = $AdminRoleModel->find($value);
        if (!empty($exist)){
            return true;
        }

        return lang_plugins('admin_role_is_not_exist');
    }

    protected function checkAdmin($value,$rule,$data)
    {
        $AdminRoleLinkModel = new AdminRoleLinkModel();
        $exist = $AdminRoleLinkModel->where('admin_role_id',$data['admin_role_id'])
            ->where('admin_id',$value)
            ->find();
        if (empty($exist)){
            return lang_plugins('admin_is_not_exist');
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
}