<?php
namespace addon\idcsmart_sub_account\controller;

use app\event\controller\PluginAdminBaseController;
use addon\idcsmart_sub_account\model\IdcsmartSubAccountModel;
use addon\idcsmart_sub_account\validate\IdcsmartSubAccountValidate;

/**
 * @title 子账户管理
 * @desc 子账户管理
 * @use addon\idcsmart_sub_account\controller\AdminIndexController
 */
class AdminIndexController extends PluginAdminBaseController
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
     * @url /admin/v1/sub_account
     * @method  GET
     * @param int id - 用户ID
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 子账户
     * @return int list[].id - 子账户ID
     * @return int list[].status - 状态0禁用1启用
     * @return string list[].username - 账户名
     * @return string list[].email - 邮箱
     * @return string list[].phone_code - 国际电话区号
     * @return string list[].phone - 手机号
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
        $data = $IdcsmartSubAccountModel->IdcsmartSubAccountList($param, 'admin');

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
     * @url /admin/v1/sub_account/:id
     * @method  GET
     * @param int id - 子账户ID
     * @return object account - 子账户
     * @return int account.id - 子账户ID
     * @return string account.username - 账户名
     * @return string account.email - 邮箱
     * @return int account.phone_code - 国际电话区号
     * @return string account.phone - 手机号
     * @return array account.auth - 权限
     * @return array account.notice - 通知product产品marketing营销ticket工单cost费用recommend推介system系统
     * @return array account.project_id - 项目ID
     * @return string visible_product - 可见产品:module模块host具体产品
     * @return array module - 模块
     * @return array host_id - 产品ID
     */
    public function index()
    {
        // 合并分页参数
        $param = $this->request->param();

        // 实例化模型类
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        // 获取子账户详情
        $account = $IdcsmartSubAccountModel->idcsmartSubAccountDetail($param['id'], 'admin');

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
     * @title 编辑子账户
     * @desc 编辑子账户
     * @author theworld
     * @version v1
     * @url /admin/v1/sub_account/:id
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
        $result = $IdcsmartSubAccountModel->updateIdcsmartSubAccount($param, 'admin');

        return json($result);
    }

    /**
     * 时间 2022-08-09
     * @title 获取子账户对应主账户
     * @desc 获取子账户对应主账户
     * @author theworld
     * @version v1
     * @url /admin/v1/sub_account/parent
     * @method  GET
     * @param string id - 用户ID,用,分隔
     * @return array list - 子账户
     * @return int list[].id - 子账户ID
     * @return int list[].parent_id - 主账户ID
     * @return string list[].username - 主账户名
     */
    public function parentList()
    {
        // 合并分页参数
        $param = $this->request->param();

        // 实例化模型类
        $IdcsmartSubAccountModel = new IdcsmartSubAccountModel();

        // 获取子账户对应主账户
        $data = $IdcsmartSubAccountModel->idcsmartSubAccountParentList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }
}