<?php

namespace addon\idcsmart_ticket\validate;

use addon\idcsmart_ticket\model\IdcsmartTicketTypeModel;
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
        'title'                    => 'require|max:150',
        'ticket_type_id'           => 'require|checkTicketType:thinkphp',
        'content'                  => 'max:3000',
        'host_ids'                 => 'array|checkHostIds:thinkphp',
        'attachment'               => 'array',
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

    protected function checkHostIds($value)
    {
        $HostModel = new HostModel();

        foreach ($value as $item){
            $exist = $HostModel->where('client_id',get_client_id())
                ->where('id',$item)
                ->find();
            if (empty($exist)){
                return lang_plugins('ticket_host_is_not_exist');
            }
        }

        return true;
    }
}