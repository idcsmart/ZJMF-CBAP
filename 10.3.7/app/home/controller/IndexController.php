<?php
namespace app\home\controller;

use app\common\model\ClientModel;
use app\common\model\ClientCreditModel;
use app\common\model\HostModel;

/**
 * @title 会员中心首页
 * @desc 会员中心首页
 * @use app\home\controller\AccountController
 */
class IndexController extends HomeBaseController
{
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 时间 2022-10-13
     * @title 会员中心首页
     * @desc 会员中心首页
     * @author theworld
     * @version v1
     * @url /console/v1/index
     * @method  GET
     * @return object account - 账户
     * @return string account.username - 姓名 
     * @return string account.email - 邮箱 
     * @return int account.phone_code - 国际电话区号 
     * @return string account.phone - 手机号 
     * @return string account.credit - 余额 
     * @return string account.host_num - 产品数量 
     * @return string account.host_active_num - 激活产品数量 
     * @return string account.unpaid_order - 未支付订单 
     * @return string account.consume - 总消费金额 
     * @return string account.this_month_consume - 本月消费
     * @return string account.this_month_consume_percent - 本月消费对比上月增长百分比 
     */
    public function index()
    {
        // 接收参数
        $param = $this->request->param();
        $id = get_client_id(false); // 获取用户ID
        
        // 实例化模型类
        $ClientModel = new ClientModel();

        // 获取用户
        $account = $ClientModel->indexClient2($id);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'account' => $account
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-10-13
     * @title 会员中心首页产品列表
     * @desc 会员中心首页产品列表
     * @author theworld
     * @version v1
     * @url /console/v1/index/host
     * @method  GET
     * @param int page - 页数
     * @return array list - 产品
     * @return int list[].id - 产品ID 
     * @return int list[].product_id - 商品ID 
     * @return string list[].product_name - 商品名称 
     * @return string list[].name - 标识 
     * @return int list[].due_time - 到期时间
     * @return string list[].status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除
     * @return string list[].type - 类型 
     * @return int count - 产品总数
     */
    public function hostList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $HostModel = new HostModel();

        // 获取产品列表
        $data = $HostModel->indexHostList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }
}