<?php
namespace addon\idcsmart_ticket\controller;

use addon\idcsmart_ticket\model\IdcsmartTicketModel;
use addon\idcsmart_ticket\validate\TicketValidate;
use app\event\controller\PluginBaseController;

/**
 * @title 工单(后台)
 * @desc 工单(后台)
 * @use addon\idcsmart_ticket\controller\TicketController
 */
class TicketController extends PluginBaseController
{
    private $validate=null;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new TicketValidate();
    }

    /**
     * 时间 2022-06-22
     * @title 工单列表
     * @desc 工单列表
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket
     * @method  GET
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 工单列表
     * @return int list[].id - ID
     * @return string list[].ticket_num - 工单号
     * @return string list[].title - 标题
     * @return string list[].name - 类型
     * @return int list[].post_time - 提交时间
     * @return string list[].username - 客户名称
     * @return array list[].hosts - 关联产品,数组
     * @return int list[].last_reply_time - 最近回复时间
     * @return string list[].status - 状态
     * @return string list[].internal_status - 内部工单状态:Pending待接受,Handling处理中,Reply待回复,Replied已回复,Resolved已解决,Closed已关闭
     * @return int count - 工单总数
     */
    public function ticketList()
    {
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $IdcsmartTicketModel->isAdmin = true;

        $result = $IdcsmartTicketModel->ticketList($param);

        return json($result);
    }

    /**
     * 时间 2022-06-22
     * @title 接收工单
     * @desc 接收工单
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/:id/receive
     * @method  PUT
     * @param int id - 工单ID required
     */
    public function receive()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->receiveTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-06-22
     * @title 已解决工单
     * @desc 已解决工单
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/:id/resolved
     * @method  PUT
     * @param int id - 工单ID required
     */
    public function resolved()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->resolvedTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-06-22
     * @title 工单详情
     * @desc 工单详情
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/:id
     * @method  GET
     * @param int id - 工单ID required
     * @return object ticket - 工单详情
     * @return int ticket.id - 工单ID
     * @return int ticket.client_id - 用户ID
     * @return string ticket.title - 工单标题
     * @return string ticket.content - 内容
     * @return int ticket.ticket_type_id - 类型ID
     * @return string ticket.status - 状态:Pending待接受,Handling处理中,Reply待回复,Replied已回复,Resolved已解决,Closed已关闭
     * @return int ticket.create_time - 创建时间
     * @return array ticket.attachment - 工单附件,数组,返回所有附件
     * @return int ticket.last_reply_time - 工单最后回复时间
     * @return string ticket.username - 用户名
     * @return array ticket.host_ids - 关联产品ID,数组
     * @return array ticket.replies - 沟通记录,数组
     * @return string ticket.replies[].content - 内容
     * @return array ticket.replies[].attachment - 附件访问地址,数组
     * @return int ticket.replies[].create_time - 时间
     * @return string ticket.replies[].type - 类型:Client用户回复,Admin管理员回复
     * @return string ticket.replies[].client_name - 用户名,type==Client时用此值
     * @return string ticket.replies[].admin_name - 管理员名,type==Admin时用此值
     */
    public function index()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->indexTicket(intval($param['id']));

        return json($result);
    }

    /**
     * 时间 2022-06-22
     * @title 回复工单
     * @desc 回复工单
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/:id/reply
     * @method  POST
     * @param int id - 工单ID required
     * @param string content - 回复内容,不超过3000个字符 required
     * @param array attachment - 附件,数组(后台调admin/v1/upload(前台调console/v1/upload)上传文件,取返回值save_name)
     * @return int ticket_reply_id - 回复ID
     */
    public function reply()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('reply')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $IdcsmartTicketModel->isAdmin = true;

        $result = $IdcsmartTicketModel->replyTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-07-22
     * @title 工单附件下载
     * @desc 工单附件下载
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/download
     * @method  POST
     * @param string name - 附件名称 required
     */
    public function download()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        return $IdcsmartTicketModel->download($param);
    }
    
}