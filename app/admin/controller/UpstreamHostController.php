<?php
namespace app\admin\controller;

use app\common\model\UpstreamHostModel;

/**
 * @title 上下游产品(后台)
 * @desc 上下游产品(后台)
 * @use app\admin\controller\UpstreamHostModel
 */
class UpstreamHostController extends AdminBaseController
{   
    /**
     * 时间 2023-02-13
     * @title 产品列表
     * @desc 产品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/host
     * @method  GET
     * @param string keywords - 关键字,搜索范围:ID,用户名称,邮箱,手机号,商品名称,产品标识
     * @param int supplier_id - 供应商ID
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 产品
     * @return int list[].id - 产品ID 
     * @return string list[].name - 产品标识 
     * @return string list[].product_name - 商品名称 
     * @return string list[].status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败 
     * @return string list[].first_payment_amount - 金额 
     * @return string list[].renew_amount - 续费金额 
     * @return string list[].billing_cycle - 周期
     * @return string list[].due_time - 到期时间 
     * @return string list[].client_id - 用户ID 
     * @return string list[].username - 用户名 
     * @return string list[].company - 公司 
     * @return string list[].email - 邮箱  
     * @return string list[].phone_code - 国际电话区号 
     * @return string list[].phone - 手机号 
     * @return int count - 产品总数
     */
    public function list()
    {
    	// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $UpstreamHostModel = new UpstreamHostModel();

        // 获取上游产品列表
        $data = $UpstreamHostModel->hostList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-13
     * @title 产品详情
     * @desc 产品详情
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/host/:id
     * @method  GET
     * @param int id - 产品ID required
     * @return object host - 产品
     * @return int host.id - 产品ID 
     * @return int host.upstream_host_id - 上游产品ID 
     * @return string host.first_payment_amount - 订购金额
     * @return string host.renew_amount - 续费金额
     * @return string host.billing_cycle - 计费周期
     * @return string host.billing_cycle_name - 模块计费周期名称
     * @return string host.billing_cycle_time - 模块计费周期时间
     * @return int host.active_time - 开通时间 
     * @return int host.due_time - 到期时间
     * @return string host.status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     */
    public function index()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $UpstreamHostModel = new UpstreamHostModel();

        // 获取产品
        $host = $UpstreamHostModel->indexHost($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'host' => $host,
            ]
        ];
        return json($result);
    }
}