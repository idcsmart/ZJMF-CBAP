<?php
namespace app\home\controller;

use app\common\model\ProductModel;
use app\common\model\ProductGroupModel;
use app\home\validate\ProductValidate;
use app\common\model\SelfDefinedFieldModel;

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
     * @return int list[].type - 分组类型：type=domain表示域名
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
     * @return int list[].parent_id - 商品一级分组ID
     * @return int list[].type - 分组类型：type=domain表示域名
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
     * @return string list[].pay_type - 付款类型免费free,一次onetime,周期先付recurring_prepayment,周期后付recurring_postpaid
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
     * @return int product.product_group_id - 所属商品组ID
     * @return string product.description - 商品描述
     * @return int product.hidden - 0显示默认，1隐藏
     * @return int product.stock_control - 库存控制(1:启用)默认0
     * @return int product.qty - 库存数量(与stock_control有关)
     * @return int product.pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return int product.auto_setup - 是否自动开通:1是默认,0否
     * @return int product.type - 关联类型:server,server_group
     * @return int product.rel_id - 关联ID
     * @return int product.creating_notice_sms - 开通中短信通知是否开启:1开启默认,0关闭
     * @return int product.creating_notice_sms_api - 开通中短信通知接口,默认0
     * @return int product.creating_notice_sms_api_template - 开通中短信通知接口模板,默认0
     * @return int product.created_notice_sms - 已开通短信通知是否开启:1开启默认,0关闭
     * @return int product.created_notice_sms_api - 已开通短信通知接口,默认0
     * @return int product.created_notice_sms_api_template - 已开通短信通知接口模板,默认0
     * @return int product.creating_notice_mail - 开通中邮件通知是否开启:1开启默认,0关闭
     * @return int product.creating_notice_mail_api - 开通中邮件通知接口
     * @return int product.creating_notice_mail_template - 开通中邮件通知模板,默认0
     * @return int product.created_notice_mail - 已开通邮件通知模板,默认0
     * @return int product.created_notice_mail_api - 已开通邮件通知接口
     * @return int product.created_notice_mail_template - 已开通邮件通知模板,默认0
     * @return array upgrade - 可升降级商品ID,数组
     * @return int product_id - 父商品ID
     * @return array plugin_custom_fields - 自定义字段{is_link:是否已有子商品,是,置灰}
     * @return int show - 是否将商品展示在会员中心对应模块的列表中:0否1是
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
     * @param  object self_defined_field - 自定义字段({"5":"123"},5是自定义字段ID,123是填写的内容)
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
     * @param   int id - 商品ID require
     * @param   bool flag false 是否获取隐藏隐藏商品的模块内容(true=是,false=否)
     * @return  string product_name - 商品名称
     * @return  string content - 模块输出内容
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
     * @param   int qty - 数量 required
     * @param   array config_options - 模块自定义配置参数,格式{"configoption":{1:1,2:[2]},"cycle":2,"promo_code":"Af13S1ACj","event_promotion":12,"qty":1}
     * @return  string price - 价格
     * @return  string renew_price - 续费价格
     * @return  string billing_cycle - 周期名称d
     * @return  int duration - 周期时长(秒)
     * @return  string description - 订单子项描述
     * @return  string base_price - 基础价格
     * @return  float price_total - 折扣后金额（各种优惠折扣处理后的金额，没有就是price价格）
     * @return  float price_promo_code_discount - 优惠码折扣金额（当使用优惠码，且有效时，才返回此字段）
     * @return  float price_client_level_discount - 客户等级折扣金额（当客户等级有效时，才返回此字段）
     * @return  float price_event_promotion_discount - 活动促销折扣金额（当活动促销有效时，才返回此字段）
     */
    public function moduleCalculatePrice()
    {
        $param = $this->request->param();
        $param['product_id'] = $param['id'] ?? 0;

        // 参数验证
        if (!$this->validate->scene('settle')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
        
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
        $param = $this->request->param();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' =>[
                'product' => (new ProductModel())->productStock($param['id'])
            ] 
        ];
        return json($result);
    }

    /**
     * 时间 2024-01-02
     * @title 商品订单页自定义字段
     * @desc  商品订单页自定义字段
     * @url /console/v1/product/:id/self_defined_field/order_page
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 商品ID require
     * @return  int data[].id - 自定义字段ID
     * @return  string data[].field_name - 字段名称
     * @return  string data[].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,checkbox=勾选框,textarea=文本区)
     * @return  string data[].description - 字段描述
     * @return  string data[].regexpr - 验证规则
     * @return  string data[].field_option - 下拉选项
     * @return  int data[].is_required - 是否必填(0=否,1=是)
     * @return  int data[].show_client_host_list - 会员中心列表显示(0=否,1=是)
     */
    public function orderPageSelfDefinedField()
    {
        $param = $this->request->param();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new SelfDefinedFieldModel())->showOrderPageField($param),
        ];
        return json($result);
    }


}