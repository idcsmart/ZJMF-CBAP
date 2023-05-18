<?php
namespace server\idcsmart_common\controller\home;

use app\event\controller\BaseController;
use server\idcsmart_common\model\IdcsmartCommonProductModel;

/**
 * @title 商品配置信息(前台)
 * @desc 商品配置信息(前台)
 * @use server\idcsmart_common\controller\home\IdcsmartCommonProductController
 */
class IdcsmartCommonProductController extends BaseController
{
    /**
     * 时间 2022-09-28
     * @title 产品列表
     * @desc 产品列表
     * @author wyh
     * @version v1
     * @url /console/v1/idcsmart_common/host
     * @method  GET
     * @param string keywords - 关键字,搜索范围:产品ID,商品名称,标识
     * @param string status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,active_time,due_time
     * @param string sort - 升/降序 asc,desc
     * @return array list - 产品
     * @return int list[].id - 产品ID
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
        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        // 获取产品列表
        $data = $IdcsmartCommonProductModel->hostList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-09-26
     * @title 前台商品配置信息
     * @desc 前台商品配置信息
     * @url /console/v1/idcsmart_common/product/:product_id/configoption
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  object common_product - 商品基础信息
     * @return  string common_product.name - 商品名称
     * @return  string common_product.order_page_description - 订购页面html
     * @return  string common_product.allow_qty - 是否允许选择数量:1是，0否默认
     * @return  string common_product.pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return  object configoptions - 配置项信息
     * @return  int configoptions.id - 配置项ID
     * @return  int configoptions.option_name - 配置项名称
     * @return  int configoptions.option_type -  配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域
     * @return  int configoptions.qty_min - 数量时最小值
     * @return  int configoptions.qty_max - 数量时最大值
     * @return  int configoptions.unit - 单位
     * @return  int configoptions.allow_repeat - 数量类型时：是否允许重复:开启后,前台购买时，可通过点击添加按钮，自动创建一个新的配置项，取名如bw1
     * @return  int configoptions.max_repeat - 最大允许重复数量
     * @return  int configoptions.description - 说明
     * @return array configoptions.subs - 子项信息
     * @return  float configoptions.subs.id - 子项ID
     * @return  float configoptions.subs.option_name - 子项名称
     * @return  float configoptions.subs.qty_change - 数量变化值
     * @return  float configoptions.subs.qty_min - 子项最小值
     * @return  float configoptions.subs.qty_max - 子项最大值
     * @return object cycles - 周期({"onetime":1.00})
     * @return object custom_cycles - 自定义周期
     * @return int custom_cycles.id - 自定义周期ID
     * @return string custom_cycles.name - 自定义周期名称
     * @return int custom_cycles.cycle_time - 自定义周期时长
     * @return string custom_cycles.cycle_unit - 自定义周期单位
     * @return int custom_cycles.cycle_amount - 自定义周期金额
     */
    public function cartConfigoption()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $result = $IdcsmartCommonProductModel->cartConfigoption($param);

        return json($result);
	}

    /**
     * 时间 2022-09-26
     * @title 前台商品配置信息计算价格
     * @desc 前台商品配置信息计算价格
     * @url /console/v1/idcsmart_common/product/:product_id/configoption/calculate
     * @method  POST
     * @author wyh
     * @version v1
     * @param   object configoption - 配置信息{168:1,514:53} require
     * @return object cycles - 周期({"onetime":1.00})
     * @return object custom_cycles - 自定义周期
     * @return int custom_cycles.id - 自定义周期ID
     * @return string custom_cycles.name - 自定义周期名称
     * @return int custom_cycles.cycle_time - 自定义周期时长
     * @return string custom_cycles.cycle_unit - 自定义周期单位
     * @return int custom_cycles.cycle_amount - 自定义周期金额
     */
    public function cartConfigoptionCalculate()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $result = $IdcsmartCommonProductModel->cartConfigoptionCalculate($param);

        return json($result);
    }

    /**
     * 时间 2022-09-26
     * @title 前台产品内页
     * @desc 前台产品内页
     * @url /console/v1/idcsmart_common/host/:host_id/configoption
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int host_id - 产品ID require
     * @return  object host - 财务信息
     * @return  int host.create_time - 订购时间
     * @return  int host.due_time - 到期时间
     * @return  int host.billing_cycle - 计费方式:计费周期免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return  int host.billing_cycle_name - 模块计费周期名称
     * @return  int host.billing_cycle_time - 模块计费周期时间,秒
     * @return  int host.renew_amount - 续费金额
     * @return  int host.first_payment_amount - 首付金额
     * @return  object configoptions - 配置项信息
     * @return  int configoptions.id - 配置项ID
     * @return  int configoptions.option_name - 配置项名称
     * @return  int configoptions.option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域
     * @return  int configoptions.unit - 单位
     * @return  array configoptions.subs -
     * @return  string configoptions.subs.option_name - 子项名称
     * @return  int configoptions.qty - 数量(当类型为数量时,显示此值)
     */
    public function hostConfigotpion()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();

        $result = $IdcsmartCommonProductModel->hostConfigotpion($param);

        return json($result);
	}


}


