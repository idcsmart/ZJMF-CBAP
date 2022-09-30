<?php
namespace server\idcsmart_common\controller\admin;

use app\event\controller\BaseController;
use server\idcsmart_common\logic\IdcsmartCommonLogic;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel;
use server\idcsmart_common\validate\IdcsmartCommonProductConfigoptionValidate;

/**
 * @title 商品配置项管理
 * @desc 商品配置项管理
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
     * @return  int configoption.option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域
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
     * @return array configoption_sub - 子项信息
     * @return int configoption_sub.id -
     * @return  float configoption_sub.onetime - 一次性,价格(值为-1时显示空)
     * @return  float configoption_sub.monthly - 月，价格(值为-1时显示空)
     * @return  float configoption_sub.quarterly - 季，价格(值为-1时显示空)
     * @return  float configoption_sub.semaiannually - 半年，价格(值为-1时显示空)
     * @return  float configoption_sub.annually - 一年，价格(值为-1时显示空)
     * @return  float configoption_sub.biennially - 两年，价格(值为-1时显示空)
     * @return  float configoption_sub.triennianlly - 三年，价格(值为-1时显示空)
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


