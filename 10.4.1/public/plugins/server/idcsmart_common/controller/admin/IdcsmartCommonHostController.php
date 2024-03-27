<?php
namespace server\idcsmart_common\controller\admin;

use app\event\controller\BaseController;
use server\idcsmart_common\model\IdcsmartCommonHostConfigoptionModel;

/**
 * @title 通用商品-产品管理
 * @desc 通用商品-产品管理
 * @use server\idcsmart_common\controller\admin\IdcsmartCommonHostController
 */
class IdcsmartCommonHostController extends BaseController
{
    public $validate;
    # 初始验证
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 时间 2022-09-26
     * @title 产品配置信息
     * @desc 产品配置信息
     * @url /admin/v1/idcsmart_common/host/:id
     * @method  get
     * @author wyh
     * @version v1
     * @param   int id - 产品ID require
     * @return   array config_option - 配置项 
     * @return   int config_option.id - 配置项ID
     * @return   string config_option.option_name - 名称
     * @return   string config_option.option_type - 类型select单选radio单选quantity数量quantity_range数量拖动
     * @return   int config_option.qty_min - 数量最小值
     * @return   int config_option.qty_max - 数量最大值
     * @return   string config_option.value - 值
     * @return   array config_option.sub - 选项
     * @return   string config_option.sub.name - 选项名称
     * @return   int config_option.sub.value - 选项值
     * @return   array upgrade_configoptions - 可升降级配置项
     * @return   int upgrade_configoptions[].id - 配置项ID
     * @return   int upgrade_configoptions[].product_id - 商品ID
     * @return   string upgrade_configoptions[].option_name - 配置项名称
     * @return   string upgrade_configoptions[].option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域
     * @return   string upgrade_configoptions[].option_param - 参数:请求接口
     * @return   int upgrade_configoptions[].qty_min - 最小值
     * @return   int upgrade_configoptions[].qty_max - 最大值
     * @return   int upgrade_configoptions[].order - 排序
     * @return   int upgrade_configoptions[].hidden - 是否隐藏:1是，0否
     * @return   string upgrade_configoptions[].unit - 单位
     * @return   int upgrade_configoptions[].allow_repeat - 是否允许重复:开启后,前台购买时，可通过点击添加按钮，自动创建一个新的配置项，取名如bw1
     * @return   int upgrade_configoptions[].max_repeat - 最大允许重复数量
     * @return   string upgrade_configoptions[].fee_type - 数量的类型的计费方式：stage阶梯计费，qty数量计费(当前区间价格*数量
     * @return   string upgrade_configoptions[].description - 说明
     * @return   int upgrade_configoptions[].configoption_id - 当前商品其他类型为数量拖动/数量输入的配置项ID
     * @return   int upgrade_configoptions[].son_product_id - 子商品ID
     * @return   int upgrade_configoptions[].free - 关联商品首周期是否免费:1是，0否
     * @return   array upgrade_configoptions[].subs - 子配置
     * @return   int upgrade_configoptions[].subs[].id - 子配置项ID
     * @return   int upgrade_configoptions[].subs[].product_configoption_id - 配置项ID
     * @return   string upgrade_configoptions[].subs[].option_name - 名称
     * @return   string upgrade_configoptions[].subs[].option_param - 参数
     * @return   int upgrade_configoptions[].subs[].qty_min - 最小值
     * @return   int upgrade_configoptions[].subs[].qty_max - 最大值
     * @return   int upgrade_configoptions[].subs[].order - 排序
     * @return   int upgrade_configoptions[].subs[].hidden - 是否隐藏:1是，0否默认
     * @return   string upgrade_configoptions[].subs[].country - 国家:类型为区域时选择
     * @return   int upgrade_configoptions[].subs[].qty_change - 数量变化最小值
     */
    public function index()
    {
        $param = $this->request->param();

        $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();

        $data = $IdcsmartCommonHostConfigoptionModel->indexHost($param);

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' => $data
        ];

        return json($result);
    }

    /**
     * 时间 2022-09-26
     * @title 保存产品配置信息
     * @desc 保存产品配置信息
     * @url /admin/v1/idcsmart_common/host/:id
     * @method  put
     * @author wyh
     * @version v1
     * @param int id - 产品ID require
     * @param   array config_option - 配置项 require
     * @param   int config_option.id - 配置项ID require
     * @param   string config_option.value - 值 require
     */
    public function update()
    {
        $param = $this->request->param();
        
        $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();

        $result = $IdcsmartCommonHostConfigoptionModel->updateHost($param);

        return json($result);

    }
}


