<?php
namespace addon\idcsmart_ticket\controller;

use addon\idcsmart_ticket\model\IdcsmartTicketInternalModel;
use addon\idcsmart_ticket\validate\TicketInternalValidate;
use app\event\controller\PluginAdminBaseController;

/**
 * @title 内部工单(后台)
 * @desc 内部工单(后台)
 * @use addon\idcsmart_ticket\controller\TicketInternalController
 */
class TicketInternalController extends PluginAdminBaseController
{
    private $validate=null;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new TicketInternalValidate();
    }

    /**
     * 时间 2022-06-22
     * @title 内部工单列表
     * @desc 内部工单列表
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/internal
     * @method  GET
     * @param string keywords - 关键字(标题,工单类型名字,工单内容)
     * @param string status - 状态搜索
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 内部工单列表
     * @return int list[].id - ID
     * @return string list[].ticket_num - 工单号
     * @return string list[].title - 标题
     * @return string list[].name - 类型
     * @return string list[].priority - 优先级:medium一般,high紧急
     * @return int list[].post_time - 提交时间
     * @return int list[].last_reply_time - 最近回复时间
     * @return string list[].status - 状态:Pending待接受,Handling处理中,Reply待回复,Replied已回复,Resolved已解决,Closed已关闭
     * @return string list[].ticket_status - 关联状态::Pending待接受,Handling处理中,Reply待回复,Replied已回复,Resolved已解决,Closed已关闭
     * @return int count - 工单总数
     */
    public function ticketInternalList()
    {
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $IdcsmartTicketInternalModel = new IdcsmartTicketInternalModel();

        $result = $IdcsmartTicketInternalModel->ticketInternalList($param);

        return json($result);
    }

    /**
     * 时间 2022-06-22
     * @title 内部工单详情
     * @desc 内部工单详情
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/internal/:id
     * @method  GET
     * @param int id - 内部工单ID required
     * @return object ticket_internal - 内部工单详情
     * @return int ticket_internal.client_id - 用户ID
     * @return int ticket_internal.id - 内部工单ID
     * @return string ticket_internal.title - 内部工单标题
     * @return string ticket_internal.content - 内容
     * @return string ticket_internal.priority - 优先级:medium一般,high紧急
     * @return int ticket_internal.ticket_type_id - 类型ID
     * @return string ticket_internal.status - 状态:Pending待接受,Handling处理中,Reply待回复,Replied已回复,Resolved已解决,Closed已关闭
     * @return int ticket_internal.create_time - 创建时间
     * @return array ticket_internal.attachment - 内部工单附件,数组,返回所有附件
     * @return int ticket_internal.last_reply_time - 工单最后回复时间
     * @return int ticket_internal.admin_id - 接单人ID(具体数据从管理员列表里获取)
     * @return int ticket_internal.post_admin_id - 提交人ID(具体数据从管理员列表里获取)
     * @return int ticket_internal.client_id - 关联用户ID(具体数据从客户列表里获取)
     * @return string ticket_internal.client_name - 用户名称
     * @return array ticket_internal.host_ids - 关联产品ID,数组(/admin/v1/host?client_id= 获取所选客户的产品)
     * @return array ticket_internal.replies - 沟通记录,数组
     * @return string ticket_internal.replies[].content - 内容
     * @return array ticket_internal.replies[].attachment - 附件访问地址,数组
     * @return int ticket_internal.replies[].create_time - 时间
     * @return string ticket_internal.replies[].admin_name - 管理员名称
     */
    public function index()
    {
        $param = $this->request->param();

        $IdcsmartTicketInternalModel = new IdcsmartTicketInternalModel();

        $result = $IdcsmartTicketInternalModel->indexTicketInternal(intval($param['id']));

        return json($result);
    }

    /**
     * 时间 2022-06-22
     * @title 创建内部工单
     * @desc 创建内部工单
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/internal
     * @method  POST
     * @param int ticket_id - 工单ID(转内部工单时需要传此参数)
     * @param string title - 内部工单标题 required
     * @param int ticket_type_id - 内部工单类型ID required
     * @param string priority - 紧急程度:medium一般,high紧急 required
     * @param int client_id - 关联用户
     * @param int admin_role_id - 指定部门 required
     * @param int admin_id - 管理员ID
     * @param array host_ids - 关联产品ID,数组(/admin/v1/host?client_id= 获取所选客户的产品列表,取产品ID)
     * @param string content - 问题描述
     * @param array attachment - 附件,数组(后台调admin/v1/upload(前台调console/v1/upload)上传文件,取返回值save_name)
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketInternalModel = new IdcsmartTicketInternalModel();

        $result = $IdcsmartTicketInternalModel->createTicketInternal($param);

        return json($result);
    }

    /**
     * 时间 2022-06-22
     * @title 接收内部工单
     * @desc 接收内部工单
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/internal/:id/receive
     * @method  PUT
     * @param int id - 工单ID required
     */
    public function receive()
    {
        $param = $this->request->param();

        $IdcsmartTicketInternalModel = new IdcsmartTicketInternalModel();

        $result = $IdcsmartTicketInternalModel->receiveTicketInternal($param);

        return json($result);
    }

    /**
     * 时间 2022-06-22
     * @title 已解决内部工单
     * @desc 已解决内部工单
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/internal/:id/resolved
     * @method  PUT
     * @param int id - 工单ID required
     */
    public function resolved()
    {
        $param = $this->request->param();

        $IdcsmartTicketInternalModel = new IdcsmartTicketInternalModel();

        $result = $IdcsmartTicketInternalModel->resolvedTicketInternal($param);

        return json($result);
    }

    /**
     * 时间 2022-06-22
     * @title 回复内部工单
     * @desc 回复内部工单
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/internal/:id/reply
     * @method  POST
     * @param int id - 工单ID required
     * @param string content - 回复内容,不超过3000个字符 required
     * @param array attachment - 附件,数组(后台调admin/v1/upload(前台调console/v1/upload)上传文件,取返回值save_name)
     * @return int ticket_internal_reply_id - 回复ID
     */
    public function reply()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('reply')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketInternalModel = new IdcsmartTicketInternalModel();

        $result = $IdcsmartTicketInternalModel->replyTicketInternal($param);

        return json($result);
    }

    /**
     * 时间 2022-06-22
     * @title 转发内部工单
     * @desc 转发内部工单
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/internal/:id/forward
     * @method  PUT
     * @param int id - 工单ID required
     * @param int admin_role_id - 部门ID(管理员分组ID) required
     * @param int admin_id - 指定人员ID(管理员ID)
     */
    public function forward()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('forward')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketInternalModel = new IdcsmartTicketInternalModel();

        $result = $IdcsmartTicketInternalModel->forwardTicketInternal($param);

        return json($result);
    }

}