<?php
namespace app\home\controller;

use app\common\model\ProductModel;
use app\common\model\ProductGroupModel;
use app\home\validate\ProductValidate;

/**
 * @title 商品管理
 * @desc 商品管理
 * @use app\home\controller\ProductController
 */
class ProductController extends HomeBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ProductValidate();
    }

	/**
     * 时间 2022-5-30
     * @title 获取商品一级分组
     * @desc 获取商品一级分组
     * @author theworld
     * @version v1
     * @url /console/v1/product/group/first
     * @method  GET
     * @return array list - 商品一级分组
     * @return int list[].id - 商品一级分组ID
     * @return int list[].name - 商品一级分组名称
     * @return int count - 商品一级分组总数
     */
    public function productGroupFirstList()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new ProductGroupModel())->productGroupFirstList()
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-30
     * @title 获取商品二级分组
     * @desc 获取商品二级分组
     * @author theworld
     * @version v1
     * @url /console/v1/product/group/second
     * @method  GET
     * @param int id - 一级分组ID
     * @return array list - 商品二级分组
     * @return int list[].id - 商品二级分组ID
     * @return int list[].name - 商品二级分组名称
     * @return int count - 商品二级分组总数
     */
    public function productGroupSecondList()
    {
        $param = $this->request->param();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new ProductGroupModel())->productGroupSecondList($param)
        ];
        return json($result);
    }

     /**
     * 时间 2022-5-30
     * @title 商品列表
     * @desc 商品列表
     * @author theworld
     * @version v1
     * @url /console/v1/product
     * @method  GET
     * @param string keywords - 关键字,搜索范围:商品ID,商品名,描述
     * @param int id - 二级分组ID
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 商品列表
     * @return int list[].id - ID
     * @return string list[].name - 商品名
     * @return string list[].description - 描述
     * @return string list[].price - 商品最低价格
     * @return string list[].cycle - 商品最低周期
     * @return int count - 商品总数
     */
    public function list()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new ProductModel())->productList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 商品详情
     * @desc 商品详情
     * @url /console/v1/product/:id
     * @method  GET
     * @author wyh
     * @version v1
     * @param int id - 商品ID required
     * @return object product - 商品
     * @return int product.id - ID
     * @return string product.name - 商品名称
     */
    public function index()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>[
                'product' => (new ProductModel())->indexProduct(intval($param['id']))
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-05-31
     * @title 结算商品
     * @desc 结算商品
     * @author theworld
     * @version v1
     * @url /console/v1/product/settle
     * @method  POST
     * @param  int product_id - 商品ID required
     * @param  object config_options - 自定义配置 required
     * @param  object customfield - 自定义参数,比如优惠码参数传:{"promo_code":["pr8nRQOGbmv5"]}
     * @param  int qty - 数量 required
     * @return int order_id - 订单ID
     */
    public function settle()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('settle')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ProductModel = new ProductModel();
        
        // 结算商品
        $result = $ProductModel->settle($param);

        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 商品配置页面
     * @desc 商品配置页面
     * @url /console/v1/product/:id/config_option
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 商品ID required
     * @param   string tag - 商品价格显示标识
     * @return  string data.content - 模块输出内容
     */
    public function moduleClientConfigOption()
    {
        $param = $this->request->param();

        $ProductModel = new ProductModel();

        $result = $ProductModel->moduleClientConfigOption($param);
        return json($result);
    }

    /**
     * 时间 2022-05-31
     * @title 修改配置计算价格
     * @desc 修改配置计算价格
     * @url /console/v1/product/:id/config_option
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 商品ID required
     * @param   mixed config_options - 自定义配置,配置为商品存在的配置
     * @return  float price - 配置项金额
     * @return  string billing_cycle - 周期名称
     * @return  int duration - 周期时长(秒)
     * @return  string description - 子项描述
     * @return  string content - 配置选择预览输出
     */
    public function moduleCalculatePrice()
    {
        $param = $this->request->param();
        $param['product_id'] = $param['id'] ?? 0;

        $ProductModel = new ProductModel();

        $result = $ProductModel->productCalculatePrice($param);
        return json($result);
    }

    /**
     * 时间 2022-10-11
     * @title 获取商品库存
     * @desc 获取商品库存
     * @author theworld
     * @version v1
     * @url /console/v1/product/:id/stock
     * @method  GET
     * @param int id - 商品ID
     * @return object product - 商品
     * @return int product.id - ID
     * @return int product.stock_control - 库存控制0:关闭1:启用
     * @return int product.qty - 库存数量
     */
    public function productStock()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' =>[
                'product' => (new ProductModel())->productStock($param['id'])
            ] 
        ];
        return json($result);
    }

}