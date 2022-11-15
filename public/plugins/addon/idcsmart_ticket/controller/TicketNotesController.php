<?php
namespace addon\idcsmart_ticket\controller;

use addon\idcsmart_ticket\model\IdcsmartTicketNotesModel;
use app\event\controller\PluginAdminBaseController;

/**
 * @title 工单备注(后台)
 * @desc 工单备注(后台)
 * @use addon\idcsmart_ticket\controller\TicketNotesController
 */
class TicketNotesController extends PluginAdminBaseController
{

    /**
     * 时间 2022-10-21
     * @title 工单备注列表
     * @desc 工单备注列表
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/notes
     * @method  GET
     * @param int ticket_id - 工单ID
     * @return array list - 工单备注列表
     * @return int list[].id - ID
     * @return string list[].content - 工单备注
     * @return int list[].create_time - 创建时间
     * @return int list[].update_time - 更新时间
     * @return string list[].name - 管理员名称
     */
    public function ticketNotesList()
    {
        $param = $this->request->param();

        $IdcsmartTicketNotesModel = new IdcsmartTicketNotesModel();

        $result = $IdcsmartTicketNotesModel->ticketNotesList($param);

        return json($result);
    }

    /**
     * 时间 2022-10-21
     * @title 工单备注详情
     * @desc 工单备注详情
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/:ticket_id/notes/:id
     * @method  GET
     * @param int id - 工单备注ID
     * @return int id - ID
     * @return string content - 工单备注
     * @return int create_time - 创建时间
     * @return int update_time - 更新时间
     * @return string name - 管理员名称
     */
    public function index()
    {
        $param = $this->request->param();

        $IdcsmartTicketNotesModel = new IdcsmartTicketNotesModel();

        $result = $IdcsmartTicketNotesModel->ticketNotesIndex($param);

        return json($result);
    }

    /**
     * 时间 2022-10-21
     * @title 创建工单备注
     * @desc 创建工单备注
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/:ticket_id/notes
     * @method  POST
     * @param string content - 工单备注
     */
    public function create()
    {
        $param = $this->request->param();

        $IdcsmartTicketNotesModel = new IdcsmartTicketNotesModel();

        $result = $IdcsmartTicketNotesModel->ticketNotesCreate($param);

        return json($result);
    }

    /**
     * 时间 2022-10-21
     * @title 编辑工单备注
     * @desc 编辑工单备注
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/:ticket_id/notes/:id
     * @method  PUT
     * @param int id - 工单备注ID
     * @param string content - 工单备注
     */
    public function update()
    {
        $param = $this->request->param();

        $IdcsmartTicketNotesModel = new IdcsmartTicketNotesModel();

        $result = $IdcsmartTicketNotesModel->ticketNotesUpdate($param);

        return json($result);
    }

    /**
     * 时间 2022-10-21
     * @title 删除工单备注
     * @desc 删除工单备注
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/:ticket_id/notes/:id
     * @method  DELETE
     * @param int id - 工单备注ID
     */
    public function delete()
    {
        $param = $this->request->param();

        $IdcsmartTicketNotesModel = new IdcsmartTicketNotesModel();

        $result = $IdcsmartTicketNotesModel->ticketNotesDelete($param);

        return json($result);
    }


}