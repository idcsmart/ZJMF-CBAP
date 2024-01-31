<?php
namespace addon\idcsmart_ticket\controller\clientarea;

use addon\idcsmart_ticket\logic\IdcsmartTicketLogic;
use addon\idcsmart_ticket\model\IdcsmartTicketModel;
use addon\idcsmart_ticket\model\IdcsmartTicketStatusModel;
use addon\idcsmart_ticket\model\IdcsmartTicketTypeModel;
use addon\idcsmart_ticket\validate\TicketValidate;
use app\event\controller\PluginBaseController;

/**
 * @title 工单(会员中心)
 * @desc 工单(会员中心)
 * @use addon\idcsmart_ticket\controller\clientarea\TicketController
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
     * 时间 2022-10-21
     * @title 工单状态列表
     * @desc 工单状态列表
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/status
     * @method  GET
     * @return array list - 工单状态列表
     * @return int list[].id - ID
     * @return string list[].name - 工单状态
     * @return string list[].color - 状态颜色
     * @return int list[].status - 完结状态:1完结,0未完结
     * @return int list[].default - 是否默认状态:0否,1是,默认状态无法修改删除
     */
    public function ticketStatusList()
    {
        $IdcsmartTicketStatusModel = new IdcsmartTicketStatusModel();

        $result = $IdcsmartTicketStatusModel->ticketStatusList();

        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 工单列表
     * @desc 工单列表
     * @author wyh
     * @version v1
     * @url /console/v1/ticket
     * @method  GET
     * @param string keywords - 关键字
     * @param int status - 状态搜索(/console/v1/ticket/status get获取状态列表)
     * @param int ticket_type_id - 工单类型搜索(/console/v1/ticket/type get获取类型列表)
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 工单列表
     * @return int list[].id - ID
     * @return string list[].ticket_num - 工单号
     * @return string list[].title - 标题
     * @return string list[].name - 类型
     * @return int list[].post_time - 提交时间
     * @return int list[].last_reply_time - 最近回复时间
     * @return string list[].status - 状态
     * @return string list[].color - 状态颜色
     * @return string list[].last_urge_time - 上次催单时间戳(0代表未催单)
     * @return int count - 工单总数
     */
    public function ticketList()
    {
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->ticketList($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 工单统计
     * @desc 工单统计
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/statistic
     * @method  GET
     * @return int 1 - 待接单数量
     * @return int 2 - 待回复数量
     * @return int 3 - 已回复数量
     */
    public function statistic()
    {
        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->statisticTicket();

        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 查看工单
     * @desc 查看工单
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/:id
     * @method  GET
     * @param int id - 工单ID required
     * @return object ticket - 工单详情
     * @return int ticket.client_id - 用户ID
     * @return int ticket.id - 工单ID
     * @return string ticket.title - 工单标题
     * @return string ticket.content - 内容
     * @return int ticket.ticket_type_id - 类型ID
     * @return string ticket.status - 状态,直接显示,结合color
     * @return string ticket.color - 状态颜色
     * @return int ticket.create_time - 创建时间
     * @return array ticket.attachment - 工单附件,数组,返回所有附件(附件以^符号分割,取最后一个值获取文件原名)
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
     * 时间 2022-06-20
     * @title 创建工单
     * @desc 创建工单
     * @author wyh
     * @version v1
     * @url /console/v1/ticket
     * @method  POST
     * @param string title - 工单标题 required
     * @param int ticket_type_id - 工单类型ID,/console/v1/ticket/type接口获取 required
     * @param array host_ids - 关联产品ID,数组(id从产品列表接口获取)
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

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->createTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 回复工单
     * @desc 回复工单
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/:id/reply
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

        $result = $IdcsmartTicketModel->replyTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 催单
     * @desc 催单
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/:id/urge
     * @method  PUT
     * @param int id - 工单ID required
     */
    public function urge()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->urgeTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 关闭工单
     * @desc 关闭工单
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/:id/close
     * @method  PUT
     * @param int id - 工单ID required
     */
    public function close()
    {
        $param = $this->request->param();

        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->closeTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-10-24
     * @title 工单部门
     * @desc 工单部门
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/department
     * @method  GET
     * @return array list - 工单部门列表
     * @return int list[].id - 工单部门ID
     * @return string list[].name - 工单部门名称
     */
    public function department()
    {
        $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();

        $result = $IdcsmartTicketTypeModel->typeDepartment();

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 工单部门
     * @desc 工单部门
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/type
     * @method  GET
     * @return array list - 工单类型列表
     * @return int list[].id - 工单部门ID
     * @return string list[].name - 工单部门名称
     */
    public function type()
    {
        $param = $this->request->param();

        $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();

        $result = $IdcsmartTicketTypeModel->typeTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-07-22
     * @title 工单附件下载
     * @desc 工单附件下载
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/download
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
     * 时间 2024-01-22
     * @title 工单通知设置
     * @desc 工单通知设置
     * @author wyh
     * @version v1
     * @url /console/v1/ticket/config
     * @method  GET
     * @return int ticket_notice_open - 是否开启工单通知，1是默认，0否
     * @return string ticket_notice_description - 工单通知描述
     */
    public function ticketConfig()
    {
        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $result = $IdcsmartTicketModel->ticketConfig();

        return json($result);
    }
    
}