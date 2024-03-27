<?php
namespace addon\idcsmart_ticket\controller;

use addon\idcsmart_ticket\model\IdcsmartTicketStatusModel;
use addon\idcsmart_ticket\validate\TicketStatusValidate;
use app\event\controller\PluginAdminBaseController;

/**
 * @title 工单状态(后台)
 * @desc 工单状态(后台)
 * @use addon\idcsmart_ticket\controller\TicketStatusController
 */
class TicketStatusController extends PluginAdminBaseController
{
    private $validate=null;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new TicketStatusValidate();
    }

    /**
     * 时间 2022-10-21
     * @title 工单状态列表
     * @desc 工单状态列表
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/status
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
     * 时间 2022-10-21
     * @title 工单状态详情
     * @desc 工单状态详情
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/status/:id
     * @method  GET
     * @param int id - 工单状态ID
     * @return int id - ID
     * @return string name - 工单状态
     * @return string color - 状态颜色
     * @return int status - 完结状态:1完结,0未完结
     */
    public function index()
    {
        $param = $this->request->param();

        $IdcsmartTicketStatusModel = new IdcsmartTicketStatusModel();

        $result = $IdcsmartTicketStatusModel->ticketStatusIndex($param);

        return json($result);
    }

    /**
     * 时间 2022-10-21
     * @title 创建工单状态
     * @desc 创建工单状态
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/status
     * @method  POST
     * @param string name - 工单状态ID
     * @param string color - 状态颜色
     * @param int status - 完结状态:1完结,0未完结
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketStatusModel = new IdcsmartTicketStatusModel();

        $result = $IdcsmartTicketStatusModel->ticketStatusCreate($param);

        return json($result);
    }

    /**
     * 时间 2022-10-21
     * @title 编辑工单状态
     * @desc 编辑工单状态
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/status/:id
     * @method  PUT
     * @param int id - 工单状态ID
     * @param string name - 工单状态ID
     * @param string color - 状态颜色
     * @param int status - 完结状态:1完结,0未完结
     */
    public function update()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketStatusModel = new IdcsmartTicketStatusModel();

        $result = $IdcsmartTicketStatusModel->ticketStatusUpdate($param);

        return json($result);
    }

    /**
     * 时间 2022-10-21
     * @title 删除工单状态
     * @desc 删除工单状态
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/status/:id
     * @method  DELETE
     * @param int id - 工单状态ID
     */
    public function delete()
    {
        $param = $this->request->param();

        $IdcsmartTicketStatusModel = new IdcsmartTicketStatusModel();

        $result = $IdcsmartTicketStatusModel->ticketStatusDelete($param);

        return json($result);
    }


}