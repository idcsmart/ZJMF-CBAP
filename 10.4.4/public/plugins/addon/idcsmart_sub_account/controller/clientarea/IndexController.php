<?php
namespace addon\idcsmart_sub_account\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_sub_account\model\IdcsmartSubAccountModel;
use addon\idcsmart_sub_account\validate\IdcsmartSubAccountValidate;

/**
 * @title 子账户管理
 * @desc 子账户管理
 * @use addon\idcsmart_sub_account\controller\clientarea\IndexController
 */
class IndexController extends PluginBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartSubAccountValidate();
    }

    /**
     * 时间 2022-08-09
     * @title 子账户列表
     * @desc 子账户列表
     * @author theworld
     * @version v1
     * @url /console/v1/sub_account
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 子账户
     * @return int list[].id - 子账户ID
     * @return int list[].status - 状态0禁用1启用
     * @return string list[].username - 账户名
     * @return string list[].last_action_time - 上次使用时间
     * @return int count - 子账户总数
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        // 获取子账户列表
        $data = $IdcsmartSubAccountModel->idcsmartSubAccountList($param, 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-08-09
     * @title 子账户详情
     * @desc 子账户详情
     * @author theworld
     * @version v1
     * @url /console/v1/sub_account/:id
     * @method  GET
     * @return object account - 子账户
     * @return int account.id - 子账户ID
     * @return string account.username - 账户名
     * @return string account.email - 邮箱
     * @return int account.phone_code - 国际电话区号
     * @return string account.phone - 手机号
     * @return array account.auth - 权限
     * @return array account.notice - 通知product产品marketing营销ticket工单cost费用recommend推介system系统
     * @return array account.project_id - 项目ID
     * @return string account.visible_product - 可见产品:module模块host具体产品
     * @return array account.module - 模块
     * @return array account.host_id - 产品ID
     */
    public function index()
    {
        // 合并分页参数
        $param = $this->request->param();

        // 实例化模型类
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        // 获取子账户详情
        $account = $IdcsmartSubAccountModel->idcsmartSubAccountDetail($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'account' => $account
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-08-09
     * @title 创建子账户
     * @desc 创建子账户
     * @author theworld
     * @version v1
     * @url /console/v1/sub_account
     * @method  POST
     * @param string username - 账户名 required
     * @param string email - 邮箱 邮箱手机号两者至少输入一个
     * @param int phone_code - 国际电话区号 输入手机号时必须传此参数
     * @param string phone - 手机号 邮箱手机号两者至少输入一个
     * @param string password - 密码 required
     * @param array project_id - 项目ID
     * @param string visible_product - 可见产品:module模块host具体产品
     * @param array module - 模块
     * @param array host_id - 产品ID
     * @param array auth - 权限 required
     * @param array notice - 通知product产品marketing营销ticket工单cost费用recommend推介system系统 required
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        // 创建子账户
        $result = $IdcsmartSubAccountModel->createIdcsmartSubAccount($param);

        return json($result);
    }

    /**
     * 时间 2022-08-09
     * @title 编辑子账户
     * @desc 编辑子账户
     * @author theworld
     * @version v1
     * @url /console/v1/sub_account/:id
     * @method  PUT
     * @param int id - 子账户ID required
     * @param string email - 邮箱 邮箱手机号两者至少输入一个
     * @param int phone_code - 国际电话区号 输入手机号时必须传此参数
     * @param string phone - 手机号 邮箱手机号两者至少输入一个
     * @param string password - 密码 required
     * @param array project_id - 项目ID
     * @param string visible_product - 可见产品:module模块host具体产品
     * @param array module - 模块
     * @param array host_id - 产品ID
     * @param array auth - 权限 required
     * @param array notice - 通知product产品marketing营销ticket工单cost费用recommend推介system系统 required
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        // 编辑子账户
        $result = $IdcsmartSubAccountModel->updateIdcsmartSubAccount($param);

        return json($result);
    }

    /**
     * 时间 2022-08-09
     * @title 删除子账户
     * @desc 删除子账户
     * @author theworld
     * @version v1
     * @url /console/v1/sub_account/:id
     * @method  DELETE
     * @param int id - 子账户ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        // 删除子账户
        $result = $IdcsmartSubAccountModel->deleteIdcsmartSubAccount($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-08-09
     * @title 子账户状态切换
     * @desc 子账户状态切换
     * @author theworld
     * @version v1
     * @url /console/v1/sub_account/:id/status
     * @method  PUT
     * @param int id - 子账户ID required
     * @param int status - 状态0禁用1启用
     */
    public function status()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('status')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        // 子账户状态切换
        $result = $IdcsmartSubAccountModel->updateIdcsmartSubAccountStatus($param);

        return json($result);
    }

    /**
     * 时间 2022-5-27
     * @title 当前子账户权限列表
     * @desc 当前子账户权限列表
     * @author theworld
     * @version v1
     * @url /console/v1/sub_account/:id/auth
     * @method  GET
     * @return array list - 权限列表
     * @return int list[].id - 权限ID
     * @return string list[].title - 权限标题
     * @return string list[].url - 地址
     * @return int list[].order - 排序
     * @return int list[].parent_id - 父级ID
     * @return array list[].child - 权限子集
     * @return int list[].child[].id - 权限ID
     * @return string list[].child[].title - 权限标题
     * @return string list[].child[].url - 地址
     * @return int list[].child[].order - 排序
     * @return int list[].child[].parent_id - 父级ID
     * @return array list[].child[].child - 权限子集
     * @return int list[].child[].child[].id - 权限ID
     * @return string list[].child[].child[].title - 权限标题
     * @return string list[].child[].child[].url - 地址
     * @return int list[].child[].child[].order - 排序
     * @return int list[].child[].child[].parent_id - 父级ID
     * @return array rules - 权限规则
     */
    public function subAccountAuthList()
    {
        // 接收参数
        $param = $this->request->param();
        
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new IdcsmartSubAccountModel())->authList($param['id'])
        ];
        return json($result);
    }
}