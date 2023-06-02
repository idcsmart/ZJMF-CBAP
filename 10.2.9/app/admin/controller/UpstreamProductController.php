<?php
namespace app\admin\controller;

use app\common\model\UpstreamProductModel;
use app\admin\validate\UpstreamProductValidate;
use app\common\logic\UpstreamLogic;

/**
 * @title 上下游商品(后台)
 * @desc 上下游商品(后台)
 * @use app\admin\controller\UpstreamProductController
 */
class UpstreamProductController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new UpstreamProductValidate();
    }

    /**
     * 时间 2023-02-13
     * @title 商品列表
     * @desc 商品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/product
     * @method  GET
     * @param string keywords - 关键字,搜索范围:商品名称
     * @param int supplier_id - 供应商ID
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 商品
     * @return int list[].id - 商品ID 
     * @return string list[].name - 商品名称 
     * @return string list[].description - 商品描述
     * @return int list[].supplier_id - 供应商ID 
     * @return string list[].supplier_name - 供应商名称 
     * @return string list[].profit_percent - 利润百分比 
     * @return int list[].auto_setup - 是否自动开通:1是,0否 
     * @return int list[].hidden - 0显示,1隐藏 
     * @return string list[].pay_type - 付款类型,免费free,一次onetime,周期先付recurring_prepayment,周期后付recurring_postpaid 
     * @return string list[].price - 商品最低价格 
     * @return string list[].cycle - 商品最低周期 
     * @return int list[].upstream_product_id - 上游商品ID 
     * @return int list[].certification - 本地实名购买0关闭,1开启  
     * @return string list[].product_group_name_second - 二级分组名称
     * @return int list[].product_group_id_second - 二级分组ID
     * @return string list[].product_group_name_first - 一级分组名称
     * @return int list[].product_group_id_first - 一级分组ID
     * @return int count - 商品总数
     */
    public function list()
    {
    	// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $UpstreamProductModel = new UpstreamProductModel();

        // 获取上游商品列表
        $data = $UpstreamProductModel->productList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-13
     * @title 商品详情
     * @desc 商品详情
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/product/:id
     * @method  GET
     * @param int id - 商品ID required
     * @return object product - 商品
     * @return int product.id - 商品ID 
     * @return string product.name - 商品名称 
     * @return string product.description - 商品描述
     * @return int product.supplier_id - 供应商ID 
     * @return string product.supplier_name - 供应商名称 
     * @return string product.profit_percent - 利润百分比 
     * @return int product.auto_setup - 是否自动开通:1是,0否 
     * @return int product.hidden - 0显示,1隐藏 
     * @return string product.pay_type - 付款类型,免费free,一次onetime,周期先付recurring_prepayment,周期后付recurring_postpaid 
     * @return string product.price - 商品最低价格 
     * @return string product.cycle - 商品最低周期 
     * @return int product.upstream_product_id - 上游商品ID 
     * @return int product.certification - 本地实名购买0关闭,1开启  
     * @return string product.product_group_name_second - 二级分组名称
     * @return int product.product_group_id_second - 二级分组ID
     * @return string product.product_group_name_first - 一级分组名称
     * @return int product.product_group_id_first - 一级分组ID
     */
    public function index()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $UpstreamProductModel = new UpstreamProductModel();

        // 获取商品
        $product = $UpstreamProductModel->indexProduct($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'product' => $product,
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-13
     * @title 添加商品
     * @desc 添加商品
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/product
     * @method  POST
     * @param int supplier_id - 供应商ID required
     * @param int upstream_product_id - 上游商品ID required
     * @param string name - 商品名称 required
     * @param string description - 商品描述
     * @param float profit_percent - 利润百分比 required
     * @param int auto_setup - 是否自动开通:1是,0否 required
     * @param int certification - 本地实名购买0关闭,1开启 required
     * @param int product_group_id - 二级分组ID required
     * @param boolean sync - 是否代理升降级商品:0,1 required
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $UpstreamProductModel = new UpstreamProductModel();
        
        // 新建商品
        $result = $UpstreamProductModel->createProduct($param);

        return json($result);
    }

    /**
     * 时间 2023-02-13
     * @title 编辑商品
     * @desc 编辑商品
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/product/:id
     * @method  PUT
     * @param int id - 商品ID required
     * @param int supplier_id - 供应商ID required
     * @param int upstream_product_id - 上游商品ID required
     * @param string name - 商品名称 required
     * @param string description - 商品描述
     * @param float profit_percent - 利润百分比 required
     * @param int auto_setup - 是否自动开通:1是,0否 required
     * @param int certification - 本地实名购买0关闭,1开启 required
     * @param int product_group_id - 二级分组ID required
     * @param boolean sync - 是否代理升降级商品:0,1 required
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
        $UpstreamProductModel = new UpstreamProductModel();
        
        // 修改商品
        $result = $UpstreamProductModel->updateProduct($param);

        return json($result);
    }

    /**
     * 时间 2023-02-13
     * @title 推荐代理商品列表
     * @desc 推荐代理商品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/recommend/product
     * @method  GET
     * @param string keywords - 关键字,搜索范围:商品名称
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list -  推荐商品
     * @return int list[].id - 推荐商品ID
     * @return int list[].upstream_product_id - 上游商品ID
     * @return string list[].name - 商品名称
     * @return string list[].supplier_name - 供应商名称
     * @return string list[].login_url - 前台网站地址
     * @return string list[].url - 接口地址
     * @return string list[].price - 商品最低价格
     * @return string list[].cycle - 商品最低周期
     * @return int list[].cpu_min - CPU(核)最小值
     * @return int list[].cpu_max - CPU(核)最大值
     * @return int list[].memory_min - 内存(GB)最小值
     * @return int list[].memory_max - 内存(GB)最大值
     * @return int list[].disk_min - 硬盘(GB)最小值
     * @return int list[].disk_max - 硬盘(GB)最大值
     * @return int list[].bandwidth_min - 带宽(Mbps)最小值
     * @return int list[].bandwidth_max - 带宽(Mbps)最大值
     * @return int list[].flow_min - 流量(G)最小值 
     * @return int list[].flow_max - 流量(G)最大值 
     * @return string list[].description - 简介
     * @return int list[].agent - 是否已代理0否1是
     * @return object list[].supplier - 供应商,已添加时有数据
     * @return object list[].supplier.id - 供应商ID
     * @return object list[].supplier.username - 上游账户名
     * @return object list[].supplier.token - API密钥
     * @return object list[].supplier.secret - API私钥
     * @return int count -  推荐商品总数
     */
    public function recommendProductList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $UpstreamLogic = new UpstreamLogic();

        // 获取推荐代理商品列表
        $data = $UpstreamLogic->recommendProductList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-13
     * @title 代理推荐商品
     * @desc 代理推荐商品
     * @author theworld
     * @version v1
     * @url /admin/v1/upstream/recommend/product
     * @method  POST
     * @param int id - 推荐代理商品ID required
     * @param string username - 用户名 required
     * @param string token - API密钥 required
     * @param string secret - API私钥 required
     * @param string name - 商品名称 required
     * @param string description - 商品描述
     * @param float profit_percent - 利润百分比 required
     * @param int auto_setup - 是否自动开通:1是,0否 required
     * @param int certification - 本地实名购买0关闭,1开启 required
     * @param int product_group_id - 二级分组ID required
     */
    public function agentRecommendProduct()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('agent')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $UpstreamProductModel = new UpstreamProductModel();
        
        // 代理推荐商品
        $result = $UpstreamProductModel->agentRecommendProduct($param);

        return json($result);
    }
}