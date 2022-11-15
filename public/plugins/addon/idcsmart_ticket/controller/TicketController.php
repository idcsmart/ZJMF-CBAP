<?php
namespace addon\idcsmart_ticket\controller;

use addon\idcsmart_ticket\IdcsmartTicket;
use addon\idcsmart_ticket\logic\IdcsmartTicketLogic;
use addon\idcsmart_ticket\model\IdcsmartTicketModel;
use addon\idcsmart_ticket\model\IdcsmartTicketReplyModel;
use addon\idcsmart_ticket\model\IdcsmartTicketTypeModel;
use addon\idcsmart_ticket\validate\TicketValidate;
use app\event\controller\PluginAdminBaseController;

/**
 * @title 工单(后台)
 * @desc 工单(后台)
 * @use addon\idcsmart_ticket\controller\TicketController
 */
class TicketController extends PluginAdminBaseController
{
    private $validate=null;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new TicketValidate();
    }

    /**
     * 时间 2022-06-22
     * @title 设置
     * @desc 设置
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/config
     * @method  get
     * @return int refresh_time - 刷新时间
     */
    public function getConfig()
    {
        return json([
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'refresh_time' => IdcsmartTicketLogic::getDefaultConfig('refresh_time')
            ]
        ]);
    }

    /**
     * 时间 2022-06-22
     * @title 设置
     * @desc 设置
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/config
     * @method  POST
     * @param int refresh_time - 刷新时间 required
     */
    public function setConfig()
    {
        $param = $this->request->param();

        $result = (new IdcsmartTicketLogic())->setConfig($param);

        return json($result);
    }

    /**
     * 时间 2022-10-24
     * @title 工单部门
     * @desc 工单部门
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/department
     * @method  GET
     * @return array list - 工单部门列表
     * @return int list[].admin_role_id - 工单部门ID
     * @return string list[].name - 工单部门名称
     */
    public function department()
    {
        $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();

        $result = $IdcsmartTicketTypeModel->typeDepartment();

        return json($result);
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
     * @param array status - 状态搜索,数组(/console/v1/ticket/status get获取状态列表)
     * @param int ticket_type_id - 工单类型搜索(/console/v1/ticket/type get获取类型列表)
     * @param int client_id - 用户搜索(客户列表接口获取)
     * @param int admin_id - 跟进人搜索(管理员列表获取)
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 工单列表
     * @return int list[].id - ID
     * @return string list[].ticket_num - 工单号
     * @return string list[].title - 标题
     * @return string list[].name - 类型
     * @return int list[].post_time - 提交时间
     * @return string list[].client_id - 用户ID
     * @return string list[].username - 客户名称
     * @return array list[].hosts - 关联产品,数组
     * @return array list[].host_ids - 关联产品ID,数组(作跳转使用)
     * @return int list[].last_reply_time - 最近回复时间
     * @return string list[].status - 状态
     * @return string list[].color - 状态颜色
     * @return string list[].admin_name - 跟进人,为null时显示-
     * @return string list[].ticket_internal - 是否有内部工单插件:1是(显示新建内部工单按钮),0否
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
     * @return string ticket.title - 工单标题
     * @return string ticket.priority - 内容
     * @return int ticket.ticket_type_id - 类型ID
     * @return string ticket.status - 状态:Pending待接受,Handling处理中,Reply待回复,Replied已回复,Resolved已解决,Closed已关闭
     * @return int ticket.create_time - 创建时间
     * @return array ticket.attachment - 工单附件,数组,返回所有附件
     * @return int ticket.last_reply_time - 工单最后回复时间
     * @return string ticket.username - 用户名
     * @return array ticket.host_ids - 关联产品ID,数组
     * @return array ticket.replies - 沟通记录,数组
     * @return int ticket.replies[].id - 回复ID,注意：有一个回复ID为0,是工单内容默认填充,这里不能修改
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

    /**
     * 时间 2022-09-21
     * @title 转内部工单
     * @desc 转内部工单
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/convert
     * @method  POST
     * @param int ticket_id - 工单ID(转内部工单时需要传此参数)
     * @param string title - 内部工单标题 required
     * @param int ticket_type_id - 内部工单类型ID(调admin/v1/ticket/internal/type获取列表) required
     * @param string priority - 紧急程度:medium一般,high紧急 required
     * @param int client_id - 关联用户
     * @param int admin_role_id - 指定部门 required
     * @param int admin_id - 管理员ID
     * @param array host_ids - 关联产品ID,数组(/admin/v1/host?client_id= 获取所选客户的产品列表,取产品ID)
     * @param string content - 问题描述
     * @param array attachment - 附件,数组(后台调admin/v1/upload(前台调console/v1/upload)上传文件,取返回值save_name)
     */
    public function convert()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->convert($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 内部工单类型列表
     * @desc 内部工单类型列表
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/internal/type
     * @method  GET
     * @return array list - 工单类型列表
     * @return int list[].id - 工单类型ID
     * @return int list[].name - 工单类型名称
     * @return int list[].role_name - 默认接受部门
     */
    public function ticketInternalType()
    {
        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result =  $IdcsmartTicketModel->ticketInternalType();

        return json($result);
    }

    /**
     * 时间 2022-09-23
     * @title 创建工单
     * @desc 创建工单
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket
     * @method  POST
     * @param int client_id - 用户ID required
     * @param string title - 工单标题 required
     * @param int ticket_type_id - 工单类型ID required
     * @param array host_ids - 关联产品ID,数组(id从)
     * @param string content - 问题描述
     * @param array attachment - 附件,数组(后台调admin/v1/upload(前台调console/v1/upload)上传文件,取返回值save_name)
     * @param string notes - 备注
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create_admin')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $IdcsmartTicketModel->isAdmin = true;

        $result = $IdcsmartTicketModel->createTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-09-23
     * @title 转交工单
     * @desc 转交工单
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/:id/forward
     * @method  POST
     * @param int admin_role_id - 部门ID required
     * @param int admin_id - 管理员ID required
     * @param int notes - 备注 required
     * @param int ticket_type_id - 类型ID required
     */
    public function forward()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('forward')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $IdcsmartTicketModel->isAdmin = true;

        $result = $IdcsmartTicketModel->forward($param);

        return json($result);
    }

    /**
     * 时间 2022-09-23
     * @title 修改工单状态
     * @desc 修改工单状态
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/:id/status
     * @method  PUT
     * @param int id - 工单ID required
     * @param int status - 状态ID required
     * @param int ticket_type_id - 工单类型ID
     * @param array host_ids - 产品ID,数组
     */
    public function status()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update_status')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $IdcsmartTicketModel->isAdmin = true;

        $result = $IdcsmartTicketModel->status($param);

        return json($result);
    }

    /**
     * 时间 2022-09-23
     * @title 修改工单回复
     * @desc 修改工单回复
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/reply/:id
     * @method  PUT
     * @param int id - 工单回复ID required
     * @param int content - 内容 required
     */
    public function ticketReplyUpdate()
    {
        $param = $this->request->param();

        $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();

        $result = $IdcsmartTicketReplyModel->ticketReplyUpdate($param);

        return json($result);
    }

    /**
     * 时间 2022-09-23
     * @title 删除工单回复
     * @desc 删除工单回复
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/reply/:id
     * @method  DELETE
     * @param int id - 工单回复ID required
     */
    public function ticketReplyDelete()
    {
        $param = $this->request->param();

        $IdcsmartTicketReplyModel = new IdcsmartTicketReplyModel();

        $result = $IdcsmartTicketReplyModel->ticketReplyDelete($param);

        return json($result);
    }

    /**
     * 时间 2022-09-23
     * @title 工单日志
     * @desc 工单日志
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/:id/log
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list -
     * @return int list[].id - ID
     * @return int list[].create_time - 记录时间
     * @return int list[].description - 描述
     */
    public function ticketLog()
    {
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $IdcsmartTicketModel->isAdmin = true;

        $result = $IdcsmartTicketModel->ticketLog($param);

        return json($result);
    }

    /**
     * 时间 2022-09-23
     * @title 修改工单内容
     * @desc 修改工单内容
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/:id/content
     * @method  PUT
     * @param int id - 工单ID required
     * @param int content - 内容 required
     */
    public function updateContent()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $IdcsmartTicketModel->isAdmin = true;

        $result = $IdcsmartTicketModel->updateContent($param);

        return json($result);
    }
}