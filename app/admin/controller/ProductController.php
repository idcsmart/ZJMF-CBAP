<?php
namespace app\admin\controller;

use app\admin\validate\ProductValidate;
use app\common\model\ProductModel;

/**
 * @title 商品管理
 * @desc 商品管理
 * @use app\admin\controller\ProductController
 */
class ProductController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ProductValidate();
    }

    /**
     * 时间 2022-5-17
     * @title 商品列表
     * @desc 商品列表
     * @url /admin/v1/product
     * @method  GET
     * @author wyh
     * @version v1
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,name,description
     * @param string sort - 升/降序 asc,desc
     * @return array list - 商品列表
     * @return int list[].id - ID
     * @return int list[].name - 商品名
     * @return int list[].description - 描述
     * @return int list[].stock_control - 是否开启库存控制:1开启,0关闭
     * @return int list[].qty - 库存
     * @return string list[].pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return int list[].hidden - 是否隐藏:1隐藏,0显示
     * @return int list[].product_group_name_second - 二级分组名称
     * @return int list[].product_group_id_second - 二级分组ID
     * @return int list[].product_group_name_first - 一级分组名称
     * @return int list[].product_group_id_first - 一级分组ID
     * @return int count - 商品总数
     */
    public function productList()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),[]);//['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new ProductModel())->productList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 商品详情
     * @desc 商品详情
     * @url /admin/v1/product/:id
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
     * @return int product.creating_notice_sms - 开通中短信通知是否开启:1开启默认,0关闭
     * @return int product.creating_notice_sms_api - 开通中短信通知接口,默认0
     * @return int product.creating_notice_sms_api_template - 开通中短信通知接口模板,默认0
     * @return int product.created_notice_sms - 已开通短信通知是否开启:1开启默认,0关闭
     * @return int product.created_notice_sms_api - 已开通短信通知接口,默认0
     * @return int product.created_notice_sms_api_template - 已开通短信通知接口模板,默认0
     * @return int product.creating_notice_mail - 开通中邮件通知是否开启:1开启默认,0关闭
     * @return int product.creating_notice_mail_template - 开通中邮件通知模板,默认0
     * @return int product.created_notice_mail_template - 已开通邮件通知模板,默认0
     * @return int product_id - 父商品ID
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
     * 时间 2022-5-17
     * @title 新建商品
     * @desc 新建商品
     * @url /admin/v1/product
     * @method  post
     * @author wyh
     * @version v1
     * @param string name 测试商品 商品名称 required
     * @param int product_group_id 1 分组ID(只传二级分组ID) required
     * @return int product_id - 商品ID
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductModel())->createProduct($param);

        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 编辑商品
     * @desc 编辑商品
     * @url /admin/v1/product/:id
     * @method  put
     * @author wyh
     * @version v1
     * @param string name 测试商品 商品名称 required
     * @param int product_group_id 1 分组ID(只传二级分组ID) required
     * @param string description 1 描述 required
     * @param int hidden 1 是否隐藏:1隐藏默认,0显示 required
     * @param int stock_control 1 库存控制(1:启用)默认0 required
     * @param int qty 1 库存数量(与stock_control有关) required
     * @param int creating_notice_sms 1 开通中短信通知是否开启:1开启默认,0关闭 required
     * @param int creating_notice_sms_api 1 开通中短信通知接口,默认0 required
     * @param int creating_notice_sms_api_template 1 开通中短信通知接口模板,默认0 required
     * @param int created_notice_sms 1 已开通短信通知是否开启:1开启默认,0关闭 required
     * @param int created_notice_sms_api 1 已开通短信通知接口,默认0 required
     * @param int created_notice_sms_api_template 1 已开通短信通知接口模板,默认0 required
     * @param int creating_notice_mail 1 开通中邮件通知是否开启:1开启默认,0关闭 required
     * @param int creating_notice_mail_api 1 开通中邮件通知接口 required
     * @param int creating_notice_mail_template 1 开通中邮件通知模板,默认0 required
     * @param int created_notice_mail 1 已开通邮件通知是否开启:1开启默认,0关闭 required
     * @param int created_notice_mail_api 1 已开通邮件通知接口 required
     * @param int created_notice_mail_template 1 已开通邮件通知模板,默认0 required
     * @param string pay_type recurring_prepayment 付款类型(免费free，一次onetime，周期先付recurring_prepayment(默认),周期后付recurring_postpaid required
     * @param int auto_setup 1 是否自动开通:1是默认,0否 required
     * @param string type server_group 关联类型:server,server_group required
     * @param int rel_id 1 关联ID required
     * @param array upgrade [1,3,4] 可升降级商品ID,数组
     * @param int product_id 1 父级商品ID
     */
    public function update()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('edit')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductModel())->updateProduct($param);

        return json($result);
    }

    /**
     * 时间 2022-6-10
     * @title 编辑商品接口
     * @desc 编辑商品接口
     * @url /admin/v1/product/:id/server
     * @method  put
     * @author wyh
     * @version v1
     * @param int auto_setup 1 是否自动开通:1是默认,0否 required
     * @param string type server_group 关联类型:server,server_group required
     * @param int rel_id 1 关联ID required
     */
    public function updateServer()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('edit_server')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductModel())->updateServer($param);

        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 删除商品
     * @desc 删除商品
     * @url /admin/v1/product/:id
     * @method  delete
     * @author wyh
     * @version v1
     * @param int id 1 商品ID required
     */
    public function delete()
    {
        $param = $this->request->param();

        $result = (new ProductModel())->deleteProduct(intval($param['id']));

        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 隐藏/显示商品
     * @desc 隐藏/显示商品
     * @url /admin/v1/product/:id/:hidden
     * @method  put
     * @author wyh
     * @version v1
     * @param int id 1 商品ID required
     * @param int hidden 1 商品ID required
     */
    public function hidden()
    {
        $param = $this->request->param();

        $result = (new ProductModel())->hiddenProduct($param);

        return json($result);
    }

    /**
     * 时间 2022-5-18
     * @title 商品拖动排序
     * @desc 商品拖动排序
     * @url /admin/v1/product/order/:id
     * @method  put
     * @author wyh
     * @version v1
     * @param int id 1 商品ID required
     * @param int pre_product_id 1 移动后前一个商品ID(没有则传0) required
     * @param int product_group_id 1 移动后的商品组ID required
     */
    public function order()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('order')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductModel())->orderProduct($param);

        return json($result);
    }

    /**
     * 时间 2022-5-31
     * @title 获取商品关联的升降级商品
     * @desc 获取商品关联的升降级商品
     * @url /admin/v1/product/:id/upgrade
     * @method  get
     * @author wyh
     * @version v1
     * @param int id 1 商品ID required
     * @return array list - 商品列表
     * @return int list[].id - ID
     * @return string list[].name - 商品名
     */
    public function upgrade()
    {
        $param = $this->request->param();

        $result = (new ProductModel())->upgradeProduct(intval($param['id']));

        return json($result);
    }
    
    /**
     * 时间 2022-05-30
     * @title 选择接口获取配置
     * @desc 选择接口获取配置
     * @url /admin/v1/product/:id/server/config_option
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 商品ID
     * @param   string type - 关联类型(server=接口,server_group=接口分组)
     * @param   int rel_id - 关联ID
     * @return  string data.content - 模块输出内容
     */
    public function moduleServerConfigOption(){
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('module_server_config_option')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
        
        $ProductModel = new ProductModel();
        $result = $ProductModel->moduleServerConfigOption($param);
        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 商品配置页面
     * @desc 商品配置页面
     * @url /admin/v1/product/:id/config_option
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 商品ID required
     * @param   string tag - 商品价格显示标识
     * @return  string data.content - 模块输出内容
     */
    public function moduleAdminConfigOption()
    {
        $param = $this->request->param();

        $ProductModel = new ProductModel();

        $result = $ProductModel->moduleAdminConfigOption($param);
        return json($result);
    }

    /**
     * 时间 2022-05-31
     * @title 修改配置计算价格
     * @desc 修改配置计算价格
     * @url /admin/v1/product/:id/config_option
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
     * 时间 2022-07-25
     * @title 获取商品所有配置项 
     * @desc 获取商品所有配置项
     * @url /admin/v1/product/:id/all_config_option
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 商品ID require
     * @return  string [].name - 配置名称
     * @return  string [].field - 配置标识
     * @return  string [].type - 配置形式(dropdown=下拉,目前只有这个)
     * @return  string [].option[].name - 选项名称
     * @return  mixed [].option[].value - 选项值
     */
    public function moduleAllConfigOption(){
        $param = $this->request->param();

        $ProductModel = new ProductModel();
        $result = $ProductModel->productAllConfigOption($param['id']);
        return json($result);
    }



}

