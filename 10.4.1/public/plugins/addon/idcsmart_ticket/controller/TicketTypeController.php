<?php
namespace addon\idcsmart_ticket\controller;

use addon\idcsmart_ticket\model\IdcsmartTicketTypeModel;
use addon\idcsmart_ticket\validate\TicketTypeValidate;
use app\event\controller\PluginAdminBaseController;

/**
 * @title 工单类型(后台)
 * @desc 工单类型(后台)
 * @use addon\idcsmart_ticket\controller\TicketTypeController
 */
class TicketTypeController extends PluginAdminBaseController
{
    private $validate=null;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new TicketTypeValidate();
    }

    /**
     * 时间 2022-06-21
     * @title 工单部门列表
     * @desc 工单部门列表
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/type
     * @method  GET
     * @return array list - 工单部门列表
     * @return int list[].id - 工单部门ID
     * @return int list[].name - 工单部门名称
     * @return int list[].admin[].id - 管理员ID
     * @return string list[].admin[].name - 管理员名称
     */
    public function ticketTypeList()
    {
        $param = $this->request->param();

        $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();

        $IdcsmartTicketTypeModel->isAdmin = true;

        $result = $IdcsmartTicketTypeModel->typeTicket($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 工单部门详情
     * @desc 工单部门详情
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/type/:id
     * @method  GET
     * @param int id - 工单部门ID required
     * @return object ticket_type - 工单部门详情
     * @return int ticket_type.id - 工单部门ID
     * @return string ticket_type.name - 工单部门名称
     * @return int admin[].id - 管理员ID
     * @return string admin[].name - 管理员名称
     */
    public function index()
    {
        $param = $this->request->param();

        $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();

        $result = $IdcsmartTicketTypeModel->indexTicketType(intval($param['id']));

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 创建工单部门
     * @desc 创建工单部门
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/type
     * @method  POST
     * @param string name - 工单部门名称 required
     * @param array admin_id - 管理员ID required
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();

        $result = $IdcsmartTicketTypeModel->createTicketType($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 编辑工单部门
     * @desc 编辑工单部门
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/type/:id
     * @method  PUT
     * @param int id - 工单部门ID required
     * @param string name - 工单部门名称 required
     * @param array admin_id - 管理员ID required
     */
    public function update()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();

        $result = $IdcsmartTicketTypeModel->updateTicketType($param);

        return json($result);
    }

    /**
     * 时间 2022-06-21
     * @title 删除工单部门
     * @desc 删除工单部门
     * @author wyh
     * @version v1
     * @url /admin/v1/ticket/type/:id
     * @method  DELETE
     * @param int id - 工单部门ID required
     */
    public function delete()
    {
        $param = $this->request->param();

        $IdcsmartTicketTypeModel = new IdcsmartTicketTypeModel();

        $result = $IdcsmartTicketTypeModel->deleteTicketType(intval($param['id']));

        return json($result);
    }

}