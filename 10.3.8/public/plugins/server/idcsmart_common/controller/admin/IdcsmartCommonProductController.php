<?php
namespace server\idcsmart_common\controller\admin;

use app\common\validate\ProductDurationRatioValidate;
use app\event\controller\BaseController;
use server\idcsmart_common\logic\IdcsmartCommonLogic;
use server\idcsmart_common\logic\ProvisionLogic;
use server\idcsmart_common\model\IdcsmartCommonProductModel;
use server\idcsmart_common\validate\IdcsmartCommonCustomCycleValidate;
use server\idcsmart_common\validate\IdcsmartCommonProductValidate;

/**
 * @title 商品管理
 * @desc 商品管理
 * @use server\idcsmart_common\controller\admin\IdcsmartCommonProductController
 */
class IdcsmartCommonProductController extends BaseController
{
    public $validate;
    # 初始验证
    public function initialize()
    {
        parent::initialize();

        $this->validate = new IdcsmartCommonProductValidate();

        $param = $this->request->param();

        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        $IdcsmartCommonLogic->validate($param);
    }

    /**
     * 时间 2022-09-26
     * @title 商品基础信息
     * @desc 商品基础信息,插入默认价格信息
     * @url /admin/v1/idcsmart_common/product/:product_id
     * @method  get
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return array
     * @return string pay_type - 付款类型：付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return object common_product - 商品信息
     * @return int common_product - 商品信息
     * @return int common_product.product_id - 商品ID
     * @return string common_product.order_page_description - 订购页面html
     * @return int common_product.allow_qty - 是否允许选择数量:1是，0否
     * @return int common_product.auto_support - 是否自动化支持:1是，0否
     * @return  object pricing - 周期信息
     * @return  float pricing.onetime - 一次性,价格(当pay_type=='onetime'时,只显示此价格)
     * @return object custom_cycle - 自定义周期
     * @return int custom_cycle.id - 自定义周期ID
     * @return string custom_cycle.name - 名称
     * @return int custom_cycle.cycle_time - 时长
     * @return string custom_cycle.cycle_unit - 时长单位
     * @return float custom_cycle.amount - 金额
     */
	public function index()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $result = $IdcsmartCommonProductModel->indexProduct($param);

		return json($result);
	}

    /**
     * 时间 2022-09-26
     * @title 保存商品基础信息
     * @desc 保存商品基础信息
     * @url /admin/v1/idcsmart_common/product/:product_id
     * @method  post
     * @author wyh
     * @version v1
     * @param int product_id - 商品ID require
     * @param string order_page_description - 订购页描述
     * @param int allow_qty - 是否允许选择数量:1是，0否默认
     * @param int auto_support - 自动化支持:开启后所有配置选项都可输入参数
     * @param object pricing - 周期价格,格式:{"onetime":0.1,"monthly":0,"quarterly":1.0}
     * @param float pricing.onetime - 一次性价格
     * @param array configoption - 自定义配置值数组
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $result = $IdcsmartCommonProductModel->createProduct($param);

        return json($result);

	}

    /**
     * 时间 2022-09-26
     * @title 获取自定义周期详情
     * @desc 获取自定义周期详情
     * @url /admin/v1/idcsmart_common/product/:product_id/custom_cycle/:id
     * @method  get
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 自定义字段ID require
     * @return object custom_cycle
     * @return string custom_cycle.name - 名称
     * @return string custom_cycle.cycle_time - 周期时长
     * @return string custom_cycle.cycle_unit - 周期单位:hour小时,day天,month月
     * @return string custom_cycle.amout - 金额
     */
    public function customCycle()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $result = $IdcsmartCommonProductModel->customCycle($param);

        return json($result);
	}

    /**
     * 时间 2022-09-26
     * @title 添加自定义周期
     * @desc 添加自定义周期
     * @url /admin/v1/idcsmart_common/product/:product_id/custom_cycle
     * @method  post
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   string name - 名称 require
     * @param   int cycle_time - 周期时长(infinite时,传0) require
     * @param   string cycle_unit - 周期单位:hour小时,day天,month月,infinite无限 require
     * @param   float amout - 金额 require
     */
    public function createCustomCycle()
    {
        $param = $this->request->param();

        $validate = new IdcsmartCommonCustomCycleValidate();
        if (!$validate->check($param)){
            return json(['status'=>400,'msg'=>$validate->getError()]);
        }

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $result = $IdcsmartCommonProductModel->createCustomCycle($param);

        return json($result);
	}

    /**
     * 时间 2022-09-26
     * @title 修改自定义周期
     * @desc 修改自定义周期
     * @url /admin/v1/idcsmart_common/product/:product_id/custom_cycle/:id
     * @method  get
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 自定义字段ID require
     * @param   int product_id - 商品ID require
     * @param   string name - 名称 require
     * @param   int cycle_time - 周期时长 require
     * @param   string cycle_unit - 周期单位:hour小时,day天,month月,infinite无限 require
     * @param   float amout - 金额 require
     */
	public function updateCustomCycle()
    {
        $param = $this->request->param();

        $validate = new IdcsmartCommonCustomCycleValidate();
        if (!$validate->check($param)){
            return json(['status'=>400,'msg'=>$validate->getError()]);
        }

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $result = $IdcsmartCommonProductModel->updateCustomCycle($param);

        return json($result);
    }

    /**
     * 时间 2022-09-26
     * @title 删除自定义字段
     * @desc 删除自定义字段
     * @url /admin/v1/idcsmart_common/product/:product_id/custom_cycle/:id
     * @method  get
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 自定义字段ID require
     */
    public function deleteCustomCycle()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $result = $IdcsmartCommonProductModel->deleteCustomCycle($param);

        return json($result);
    }

    /**
     * 时间 2023-06-01
     * @title 获取模块列表
     * @desc 获取模块列表
     * @url /admin/v1/idcsmart_common/product/:product_id/module
     * @method  get
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return array list -
     * @return string list[].name - 名称
     * @return string list[].value - 值
     */
    public function getModules(){
        $param = $this->request->param();

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $result = $IdcsmartCommonProductModel->getModules($param);

        return json($result);
    }

    /**
     * 时间 2023-06-01
     * @title 获取模块自定义参数
     * @desc 获取模块自定义参数
     * @url /admin/v1/idcsmart_common/product/:product_id/module/:server_id
     * @method  get
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int server_id - 服务器ID require
     * @return array configoption -
     * @return string configoption[].name - 名称
     * @return string configoption[].placeholder - 填充
     * @return string configoption[].description - 描述
     * @return string configoption[].default - 默认值
     * @return string configoption[].type - 类型text,password,yesno(值 on|off),radio,dropdown,textarea,
     * @return string configoption[].options - 选项,单选和下拉才有
     * @return string configoption[].rows - 文本域属性rows
     * @return string configoption[].cols - 文本域属性cols
     * @return object module_meta -
     * @return string module_meta.APIVersion - 版本
     * @return string module_meta.HelpDoc - 帮助文档地址
     */
    public function getModuleConfig(){
        $param = $this->request->param();

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $result = $IdcsmartCommonProductModel->getModuleConfig($param);

        return json($result);
    }

    /**
     * 时间 2023-12-18
     * @title 获取周期比例
     * @desc 获取周期比例
     * @url /admin/v1/idcsmart_common/duration_ratio
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  int list[].id - 周期ID
     * @return  string list[].name - 周期名称
     * @return  int list[].num - 周期时长
     * @return  string list[].unit - 单位(hour=小时,day=天,month=月)
     * @return  string list[].ratio - 比例
     */
    public function indexDurationRatio(){

        $param = request()->param();

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $data = $IdcsmartCommonProductModel->indexRatio($param['product_id'] ?? 0);

        $result = [
            'status' => 200,
            'msg'	 => lang_plugins('success_message'),
            'data'	 => [
                'list' => $data,
            ],
        ];

        return json($result);
    }

    /**
     * 时间 2023-12-18
     * @title 保存周期比例
     * @desc 保存周期比例
     * @url /admin/v1/idcsmart_common/duration_ratio
     * @method  PUT
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   object ratio - 比例(如{"2":"1.5"},键是周期ID,值是比例) require
     */
    public function saveDurationRatio(){
        $param = request()->param();

        $ProductDurationRatioValidate = new ProductDurationRatioValidate();
        if (!$ProductDurationRatioValidate->scene('save')->check($param)){
            return json(['status' => 400 , 'msg' => lang($ProductDurationRatioValidate->getError())]);
        }

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $result = $IdcsmartCommonProductModel->saveRatio($param);

        return json($result);
    }

    /**
     * 时间 2023-12-18
     * @title 周期比例填充
     * @desc 周期比例填充
     * @url /admin/v1/idcsmart_common/duration_ratio/fill
     * @method  POST
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   object price - 价格(如{"2":"1.5"},键是周期ID,值是价格) require
     * @return  object list - 周期价格(如{"2":"1.5"},键是周期ID,值是价格)
     */
    public function fillDurationRatio(){
        $param = request()->param();

        $ProductDurationRatioValidate = new ProductDurationRatioValidate();
        if (!$ProductDurationRatioValidate->scene('fill')->check($param)){
            return json(['status' => 400 , 'msg' => lang($ProductDurationRatioValidate->getError())]);
        }

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $result = $IdcsmartCommonProductModel->autoFill($param);

        return json($result);
    }

}


