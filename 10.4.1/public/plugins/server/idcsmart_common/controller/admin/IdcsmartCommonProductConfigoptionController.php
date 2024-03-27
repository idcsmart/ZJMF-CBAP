<?php
namespace server\idcsmart_common\controller\admin;

use app\event\controller\BaseController;
use server\idcsmart_common\logic\IdcsmartCommonLogic;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel;
use server\idcsmart_common\validate\IdcsmartCommonProductConfigoptionValidate;

/**
 * @title 通用商品-商品配置项管理
 * @desc 通用商品-商品配置项管理
 * @use server\idcsmart_common\controller\admin\IdcsmartCommonProductConfigoptionController
 */
class IdcsmartCommonProductConfigoptionController extends BaseController
{
    public $validate;
    # 初始验证
    public function initialize()
    {
        parent::initialize();

        $this->validate = new IdcsmartCommonProductConfigoptionValidate();

        $param = $this->request->param();

        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        $IdcsmartCommonLogic->validate($param);
    }

    /**
     * 时间 2022-09-26
     * @title 配置项列表
     * @desc 配置项列表
     * @url /admin/v1/idcsmart_common/product/:product_id/configoption
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  array configoption - 列表数据
     * @return  int configoption.id -
     * @return  int configoption.product_id - 商品ID
     * @return  string configoption.option_name - 配置项名称
     * @return  string configoption.option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域
     * @return  int configoption.hidden - 是否隐藏:1是，0否
     */
    public function configoptionList()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $result = $IdcsmartCommonProductConfigoptionModel->configoptionList($param);

        return json($result);
    }

    /**
     * 时间 2024-03-20
     * @title 配置项拖动排序
     * @desc 配置项拖动排序
     * @url /admin/v1/idcsmart_common/product/:product_id/configoption/order
     * @method  POST
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 配置项ID require
     * @param   int prev_id - 拖动后前一个配置项ID，没有传0 require
     */
    public function configoptionOrder()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $result = $IdcsmartCommonProductConfigoptionModel->configoptionOrder($param);

        return json($result);
    }

    /**
     * 时间 2022-12-12
     * @title 数量配置项列表(新增接口)
     * @desc 数量配置项列表
     * @url /admin/v1/idcsmart_common/product/:product_id/configoption/quantity
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int configoption_id - 编辑时,传当前配置项ID
     * @return  array configoption - 列表数据
     * @return  int configoption.id -
     * @return  string configoption.option_name - 配置项名称
     */
    public function quantityConfigoption()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $result = $IdcsmartCommonProductConfigoptionModel->quantityConfigoption($param);

        return json($result);
    }

    /**
     * 时间 2022-09-26
     * @title 配置项详情
     * @desc 配置项详情
     * @url /admin/v1/idcsmart_common/product/:product_id/configoption/:id
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  array configoption - 列表数据
     * @return  int configoption.id -
     * @return  int configoption.product_id - 商品ID
     * @return  string configoption.option_name - 配置项名称
     * @return  int configoption.option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域
     * @return  int configoption.hidden - 是否隐藏:1是，0否
     * @return  string configoption.unit - 单位
     * @return  int configoption.allow_repeat - 是否允许重复:开启后,前台购买时，可通过点击添加按钮，自动创建一个新的配置项，取名如bw1
     * @return  int configoption.max_repeat - 最大允许重复数量
     * @return  string configoption.fee_type - 数量的类型的计费方式：stage阶梯计费，qty数量计费(当前区间价格*数量)
     * @return  string configoption.description - 说明
     * @return  int configoption.configoption_id - 当前商品其他类型为数量拖动/数量输入的配置项ID
     * @return array configoption_sub - 子项信息
     * @return int configoption_sub.id -
     * @return  float configoption_sub.onetime - 一次性,价格
     * @return  float configoption_sub.qty_min - 最小值
     * @return  float configoption_sub.qty_max - 最大值
     * @return array configoption_sub.custom_cycle - 自定义周期
     * @return array configoption_sub.custom_cycle.id - 自定义周期ID
     * @return array configoption_sub.custom_cycle.name - 名称
     * @return array configoption_sub.custom_cycle.amount - 金额
     */
    public function index()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $result = $IdcsmartCommonProductConfigoptionModel->indexConfigoption($param);

        return json($result);
    }

    /**
     * 时间 2022-09-26
     * @title 添加配置项
     * @desc 添加配置项
     * @url /admin/v1/idcsmart_common/product/:product_id/configoption
     * @method  POST
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int option_name - 配置项名称
     * @param   int option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域 require
     * @param   int option_param - 参数:请求接口
     * @param   int description - 说明
     * @param   int unit - 单位
     * @param   int allow_repeat - 是否允许重复:开启后,前台购买时，可通过点击添加按钮，自动创建一个新的配置项，取名如bw1
     * @param   int max_repeat - 最大允许重复数量
     * @param   int fee_type - 数量的类型的计费方式：stage阶梯计费，qty数量计费(当前区间价格*数量)
     * @param   int hidden - 是否隐藏:1是，0否
     * @param   int configoption_id - 当前商品其他类型为数量拖动/数量输入的配置项ID
     * @param   int set_son_product - 是否设为子商品:1是,0否(选择是时,才传下面pay_type,free两个字段)
     * @param   string pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @param   int free - 关联商品首周期是否免费:1是,0否
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $result = $IdcsmartCommonProductConfigoptionModel->createConfigoption($param);

        return json($result);
    }

    /**
     * 时间 2022-09-26
     * @title 更新配置项
     * @desc 更新配置项
     * @url /admin/v1/idcsmart_common/product/:product_id/configoption/:id
     * @method  put
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 配置项ID require
     * @param   string option_name - 配置项名称
     * @param   string option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域 require
     * @param   string option_param - 参数:请求接口
     * @param   string description - 说明
     * @param   string unit - 单位
     * @param   int allow_repeat - 是否允许重复:开启后,前台购买时，可通过点击添加按钮，自动创建一个新的配置项，取名如bw1
     * @param   int max_repeat - 最大允许重复数量
     * @param   string fee_type - 数量的类型的计费方式：stage阶梯计费，qty数量计费(当前区间价格*数量)
     * @param   int hidden - 是否隐藏:1是，0否
     */
    public function update()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $result = $IdcsmartCommonProductConfigoptionModel->updateConfigoption($param);

        return json($result);
    }

    /**
     * 时间 2022-09-26
     * @title 删除配置项
     * @desc 删除配置项
     * @url /admin/v1/idcsmart_common/product/:product_id/configoption/:id
     * @method  put
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 配置项ID require
     */
    public function delete()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $result = $IdcsmartCommonProductConfigoptionModel->deleteConfigoption($param);

        return json($result);
    }

    /**
     * 时间 2022-09-26
     * @title 配置项开启/隐藏
     * @desc 配置项开启/隐藏
     * @url /admin/v1/idcsmart_common/product/:product_id/configoption/:id/hidden
     * @method  put
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 配置项ID require
     * @param   int hidden - 是否隐藏:1是，0否 require
     */
    public function hidden()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $result = $IdcsmartCommonProductConfigoptionModel->hiddenConfigoption($param);

        return json($result);
    }

}


