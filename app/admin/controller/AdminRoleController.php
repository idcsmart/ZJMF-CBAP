<?php
namespace app\admin\controller;

use app\admin\model\AdminRoleModel;
use app\admin\validate\AdminRoleValidate;

/**
 * @title 管理员分组
 * @desc 管理员分组管理
 * @use app\admin\controller\AdminRoleController
 */
class AdminRoleController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new AdminRoleValidate();
    }

    /**
     * 时间 2022-5-10
     * @title 管理员分组列表
     * @desc 管理员分组列表
     * @url /admin/v1/admin/role
     * @method  GET
     * @author wyh
     * @version v1
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,name,description
     * @param string sort - 升/降序 asc,desc
     * @return array list - 管理员分组列表
     * @return int list[].id - ID
     * @return string list[].name - 分组名称
     * @return string list[].description - 分组说明
     * @return string list[].admins - 分组下管理员,逗号分隔
     * @return int count - 管理员分组总数
     */
    public function adminRoleList()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new AdminRoleModel())->adminRoleList($param)
        ];
       return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 获取单个管理员分组
     * @desc 获取单个管理员分组
     * @url /admin/v1/admin/role/:id
     * @method  GET
     * @author wyh
     * @version v1
     * @param int id - 管理员分组ID required
     * @return object admin_role - 管理员分组
     * @return int admin_role.id - ID
     * @return string admin_role.name - 分组名称
     * @return string admin_role.description - 分组描述
     * @return string admin_role.admins - 分组下管理员,逗号分隔
     * @return array admin_role.auth - 权限ID数组
     */
    public function index()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>[
                'admin_role' => (new AdminRoleModel())->indexAdminRole(intval($param['id']))
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 添加管理员分组
     * @desc 添加管理员分组
     * @url /admin/v1/admin/role
     * @method  post
     * @author wyh
     * @version v1
     * @param string name 超级管理员 分组名称 required
     * @param string description 拥有所有权限 分组说明 required
     * @param array auth - 权限ID数组
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new AdminRoleModel())->createAdminRole($param);

        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 修改管理员分组
     * @desc 修改管理员分组
     * @url /admin/v1/admin/role/:id
     * @method  put
     * @author wyh
     * @version v1
     * @param string id 1 分组ID required
     * @param string name 超级管理员 分组名称 required
     * @param string description 拥有所有权限 分组说明 required
     * @param array auth - 权限ID数组
     */
    public function update()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new AdminRoleModel())->updateAdminRole($param);

        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 删除管理员分组
     * @desc 删除管理员分组
     * @url /admin/v1/admin/role/:id
     * @method  delete
     * @author wyh
     * @version v1
     * @param int id 1 管理员分组ID required
     */
    public function delete()
    {
        $param = $this->request->param();

        $result = (new AdminRoleModel())->deleteAdminRole(intval($param['id']));

        return json($result);
    }
}

