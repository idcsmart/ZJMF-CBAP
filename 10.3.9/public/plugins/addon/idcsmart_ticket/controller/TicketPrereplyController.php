<?php
namespace addon\idcsmart_ticket\controller;

use addon\idcsmart_ticket\model\IdcsmartTicketPrereplyModel;
use app\event\controller\PluginAdminBaseController;

/**
 * @title 工单预设回复(后台)
 * @desc 工单预设回复(后台)
 * @use addon\idcsmart_ticket\controller\TicketPrereplyController
 */
class TicketPrereplyController extends PluginAdminBaseController
{   
    /**
     * 时间 2022-10-21
     * @title 工单预设回复列表
     * @desc 工单预设回复列表
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/prereply
     * @method  GET
     * @return array list - 工单预设回复列表
     * @return int list[].id - ID
     * @return string list[].content - 内容
     */
    public function ticketPrereplyList()
    {
        $IdcsmartTicketPrereplyModel = new IdcsmartTicketPrereplyModel();

        $result = $IdcsmartTicketPrereplyModel->ticketPrereplyList();

        return json($result);
    }

    /**
     * 时间 2022-10-21
     * @title 工单预设回复详情
     * @desc 工单预设回复详情
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/prereply/:id
     * @method  GET
     * @param int id - 工单预设回复ID
     * @return int id - ID
     * @return string content - 内容
     */
    public function index()
    {
        $param = $this->request->param();

        $IdcsmartTicketPrereplyModel = new IdcsmartTicketPrereplyModel();

        $result = $IdcsmartTicketPrereplyModel->ticketPrereplyIndex($param);

        return json($result);
    }

    /**
     * 时间 2022-10-21
     * @title 创建工单预设回复
     * @desc 创建工单预设回复
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/prereply
     * @method  POST
     * @param string content - 内容
     */
    public function create()
    {
        $param = $this->request->param();

        $IdcsmartTicketPrereplyModel = new IdcsmartTicketPrereplyModel();

        $result = $IdcsmartTicketPrereplyModel->ticketPrereplyCreate($param);

        return json($result);
    }

    /**
     * 时间 2022-10-21
     * @title 编辑工单预设回复
     * @desc 编辑工单预设回复
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/prereply/:id
     * @method  PUT
     * @param int id - 工单预设回复ID
     * @param string content - 内容
     */
    public function update()
    {
        $param = $this->request->param();

        $IdcsmartTicketPrereplyModel = new IdcsmartTicketPrereplyModel();

        $result = $IdcsmartTicketPrereplyModel->ticketPrereplyUpdate($param);

        return json($result);
    }

    /**
     * 时间 2022-10-21
     * @title 删除工单预设回复
     * @desc 删除工单预设回复
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/prereply/:id
     * @method  DELETE
     * @param int id - 工单预设回复ID
     */
    public function delete()
    {
        $param = $this->request->param();

        $IdcsmartTicketPrereplyModel = new IdcsmartTicketPrereplyModel();

        $result = $IdcsmartTicketPrereplyModel->ticketPrereplyDelete($param);

        return json($result);
    }


}