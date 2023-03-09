<?php
namespace app\admin\controller;

use app\common\model\HostModel;
use app\admin\validate\HostValidate;

/**
 * @title 产品管理
 * @desc 产品管理
 * @use app\admin\controller\HostController
 */
class HostController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new HostValidate();
    }

    /**
     * 时间 2022-05-13
     * @title 产品列表
     * @desc 产品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/host
     * @method  GET
     * @param string keywords - 关键字,搜索范围:产品ID,商品名称,标识,用户名,邮箱,手机号
     * @param int client_id - 用户ID
     * @param string status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,active_time,due_time
     * @param string sort - 升/降序 asc,desc
     * @return array list - 产品
     * @return int list[].id - 产品ID 
     * @return int list[].client_id - 用户ID 
     * @return int list[].client_name - 用户名 
     * @return string list[].email - 邮箱 
     * @return string list[].phone_code - 国际电话区号 
     * @return string list[].phone - 手机号 
     * @return string list[].company - 公司 
     * @return int list[].product_id - 商品ID 
     * @return string list[].product_name - 商品名称 
     * @return string list[].name - 标识 
     * @return int list[].active_time - 开通时间 
     * @return int list[].due_time - 到期时间
     * @return string list[].first_payment_amount - 金额
     * @return string list[].billing_cycle - 周期
     * @return string list[].status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @return int count - 产品总数
     */
	public function hostList()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $HostModel = new HostModel();

        // 获取产品列表
        $data = $HostModel->hostList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

	/**
     * 时间 2022-05-13
     * @title 产品详情
     * @desc 产品详情
     * @author theworld
     * @version v1
     * @url /admin/v1/host/:id
     * @method  GET
     * @param int id - 产品ID required
     * @return object host - 产品
     * @return int host.id - 产品ID 
     * @return int host.product_id - 商品ID 
     * @return int host.server_id - 接口ID 
     * @return string host.name - 标识 
     * @return string host.notes - 备注 
     * @return string host.first_payment_amount - 订购金额
     * @return string host.renew_amount - 续费金额
     * @return string host.billing_cycle - 计费周期
     * @return string host.billing_cycle_name - 模块计费周期名称
     * @return string host.billing_cycle_time - 模块计费周期时间
     * @return int host.active_time - 开通时间 
     * @return int host.due_time - 到期时间
     * @return string host.status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @return string host.suspend_type - 暂停类型,overdue到期暂停,overtraffic超流暂停,certification_not_complete实名未完成,other其他
     * @return string host.suspend_reason - 暂停原因
     * @return array status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @return string host.product_name - 商品名称
     * @return string host.agent - 代理产品0否1是
     */
	public function index()
    {
		// 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $HostModel = new HostModel();

        // 获取产品
        $host = $HostModel->indexHost($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'host' => $host,
                'status' => config('idcsmart.host_status')
            ]
        ];
        return json($result);
	}	

	/**
     * 时间 2022-05-13
     * @title 修改产品
     * @desc 修改产品
     * @author theworld
     * @version v1
     * @url /admin/v1/host/:id
     * @method  put
     * @param int id - 产品ID required
     * @param int product_id - 商品ID required
     * @param int server_id - 接口
     * @param string name - 标识
     * @param string notes - 备注
     * @param float first_payment_amount - 订购金额 required
     * @param float renew_amount - 续费金额 required
     * @param string billing_cycle - 计费周期 required
     * @param string active_time - 开通时间
     * @param string due_time - 到期时间
     * @param string status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     */
	public function update()
    {
		// 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $HostModel = new HostModel();
        
        // 修改产品
        $result = $HostModel->updateHost($param);

        return json($result);
	}

	/**
     * 时间 2022-05-13
     * @title 删除产品
     * @desc 删除产品
     * @author theworld
     * @version v1
     * @url /admin/v1/host/:id
     * @method  DELETE
     * @param int id - 产品ID required
     */
	public function delete()
    {
		// 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        // 删除产品
        $result = $HostModel->deleteHost($param);

        return json($result);
	}

    /**
     * 时间 2022-05-13
     * @title 批量删除产品
     * @desc 批量删除产品
     * @author theworld
     * @version v1
     * @url /admin/v1/host
     * @method  DELETE
     * @param array id - 产品ID required
     */
    public function batchDelete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        // 批量删除产品
        $result = $HostModel->batchDeleteHost($param);

        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 模块开通 
     * @desc 模块开通
     * @url /admin/v1/host/:id/module/create
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID required
     */
    public function createAccount()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        $result = $HostModel->createAccount($param['id']);
        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 模块暂停
     * @desc 模块暂停
     * @url /admin/v1/host/:id/module/suspend
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID required
     * @param   string suspend_type - 暂停类型(overdue=到期暂停,overtraffic=超流暂停,certification_not_complete=实名未完成,other=其他) required
     * @param   string suspend_reason - 暂停原因
     */
    public function suspendAccount()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('suspend')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $HostModel = new HostModel();
        
        $result = $HostModel->suspendAccount($param);
        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 模块解除暂停
     * @desc 模块解除暂停
     * @url /admin/v1/host/:id/module/unsuspend
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID required
     */
    public function unsuspendAccount()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        $result = $HostModel->unsuspendAccount($param['id']);
        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 模块删除
     * @desc 模块删除
     * @url /admin/v1/host/:id/module/terminate
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID required
     */
    public function terminateAccount()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        $result = $HostModel->terminateAccount($param['id']);
        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 产品内页模块
     * @desc 产品内页模块
     * @url /admin/v1/host/:id/module
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 产品ID required
     * @return  string content - 模块输出内容
     */
    public function adminArea()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        $result = $HostModel->adminArea($param['id']);
        return json($result);
    }


     /**
     * 时间 2022-05-31
     * @title 产品升降级配置
     * @desc 产品升降级配置
     * @url /admin/v1/host/:id/upgrade/config_option
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 产品ID required
     */
    public function changeConfigOption()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        $result = $HostModel->adminChangeConfigOption($param['id']);
        return json($result);
    }

    /**
     * 时间 2022-05-31
     * @title 产品升降级配置计算价格
     * @desc 产品升降级配置计算价格
     * @url /admin/v1/host/:id/upgrade/config_option
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID required
     * @param   mixed config_options - 自定义配置项
     */
    public function changeConfigOptionCalculatePrice(){
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();
        
        $result = $HostModel->changeConfigOptionCalculatePrice($param);
        return json($result);
    }


    /**
     * 时间 2022-10-26
     * @title 获取用户所有产品
     * @desc 获取用户所有产品
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id/host/all
     * @method  GET
     * @param int id - 用户ID required
     * @return array list - 产品
     * @return int list[].id - 产品ID 
     * @return int list[].product_id - 商品ID 
     * @return string list[].product_name - 商品名称 
     * @return string list[].name - 标识 
     * @return int count - 产品总数
     */
    public function clientHost()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();

        // 获取用户产品
        $data = $HostModel->clientHost($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-01-31
     * @title 模块按钮输出
     * @desc 模块按钮输出
     * @url /admin/v1/host/:id/module/button
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @return  string button[].type - 按钮类型(暂时都是default)
     * @return  string button[].func - 按钮功能(create=开通,suspend=暂停,unsuspend=解除暂停,terminate=删除,renew=续费)
     * @return  string button[].name - 名称
     */
    public function moduleButton()
    {
        $param = $this->request->param();

        // 实例化模型类
        $HostModel = new HostModel();

        $result = $HostModel->moduleAdminButton($param);
        return json($result);
    }



}