<?php
namespace app\admin\controller;

use app\admin\model\AdminModel;
use app\admin\validate\AdminValidate;

/**
 * @title 管理员
 * @desc 管理员管理
 * @use app\admin\controller\AdminController
 */
class AdminController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new AdminValidate();
    }

    /**
     * 时间 2022-5-10
     * @title 管理员列表
     * @desc 管理员列表
     * @url /admin/v1/admin
     * @method  GET
     * @author wyh
     * @version v1
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,nickname,name,email
     * @param string sort - 升/降序 asc,desc
     * @return array list - 管理员列表
     * @return int list[].id - ID
     * @return int list[].nickname - 名称
     * @return int list[].name - 用户名
     * @return int list[].email - 邮箱
     * @return int list[].roles - 分组名称
     * @return int list[].status - 状态;0:禁用,1:正常
     * @return int count - 管理员总数
     */
    public function adminList()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new AdminModel())->adminList($param)
        ];
       return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 获取单个管理员
     * @desc 获取单个管理员
     * @url /admin/v1/admin/:id
     * @method  GET
     * @author wyh
     * @version v1
     * @param int id - 管理员分组ID required
     * @return object admin - 管理员
     * @return int admin.id - ID
     * @return string admin.nickname - 名称
     * @return string admin.name - 用户名
     * @return string admin.email - 邮箱
     * @return string admin.role_id - 分组ID
     * @return string admin.roles - 所属分组,逗号分隔
     * @return string admin.status - 状态;0:禁用;1:正常
     */
    public function index()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>[
                'admin' => (new AdminModel())->indexAdmin(intval($param['id']))
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 添加管理员
     * @desc 添加管理员
     * @url /admin/v1/admin
     * @method  post
     * @author wyh
     * @version v1
     * @param string name 测试员 用户名 required
     * @param string password 123456 密码 required
     * @param string repassword 123456 重复密码 required
     * @param string email 123@qq.com 邮箱 required
     * @param string nickname 小华 名称 required
     * @param string role_id 1 分组ID required
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new AdminModel())->createAdmin($param);

        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 修改管理员
     * @desc 修改管理员
     * @url /admin/v1/admin/:id
     * @method  put
     * @author wyh
     * @version v1
     * @param string id 1 管理员ID required
     * @param string name 测试员 用户名 required
     * @param string password 123456 密码 required
     * @param string repassword 123456 重复密码 required
     * @param string email 123@qq.com 邮箱 required
     * @param string nickname 小华 名称 required
     * @param string role_id 1 分组ID required
     */
    public function update()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
		if(!empty($param['password']) || !empty($param['repassword'])){
			//密码验证
			if (!$this->validate->scene('password')->check($param)){
				return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
			}
		}
        $result = (new AdminModel())->updateAdmin($param);

        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 删除管理员
     * @desc 删除管理员
     * @url /admin/v1/admin/:id
     * @method  delete
     * @author wyh
     * @version v1
     * @param int id 1 管理员ID required
     */
    public function delete()
    {
        $param = $this->request->param();

        $result = (new AdminModel())->deleteAdmin($param);

        return json($result);
    }

    /**
     * 时间 2022-5-10
     * @title 管理员状态切换
     * @desc 管理员状态切换
     * @url /admin/v1/admin/:id/status
     * @method  put
     * @author wyh
     * @version v1
     * @param int id 1 管理员ID required
     * @param int status 1 状态:0禁用,1启用 required
     */
    public function status()
    {
        $param = $this->request->param();

        $result = (new AdminModel())->status($param);

        return json($result);
    }

    /**
     * 时间 2022-5-13
     * @title 注销
     * @desc 注销
     * @url /admin/v1/logout
     * @method  post
     * @author wyh
     * @version v1
     */
    public function logout()
    {
        $param = $this->request->param();

        $result = (new AdminModel())->logout($param);

        return json($result);
    }

}

