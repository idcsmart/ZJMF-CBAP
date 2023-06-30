<?php
namespace server\idcsmart_common\controller\admin;

use app\event\controller\BaseController;
use server\idcsmart_common\logic\IdcsmartCommonLogic;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionSubModel;
use server\idcsmart_common\validate\IdcsmartCommonProductConfigoptionSubValidate;

/**
 * @title 商品配置项子项管理
 * @desc 商品配置项子项管理
 * @use server\idcsmart_common\controller\admin\IdcsmartCommonProductConfigoptionSubController
 */
class IdcsmartCommonProductConfigoptionSubController extends BaseController
{
    public $validate;
    # 初始验证
    public function initialize()
    {
        parent::initialize();

        $this->validate = new IdcsmartCommonProductConfigoptionSubValidate();

        $param = $this->request->param();

        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        $IdcsmartCommonLogic->validateConfigoption($param);
    }

    /**
     * 时间 2022-09-26
     * @title 配置子项详情
     * @desc 配置子项详情
     * @url /admin/v1/idcsmart_common/configoption/:configoption_id/sub/:id
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int configoption_id - 配置项ID require
     * @param   int id - 配置子项ID require
     * @return object configoption_sub - 子项信息
     * @return int configoption_sub.id -
     * @return  float configoption_sub.onetime - 一次性,价格
     * @return array configoption_sub.custom_cycle - 自定义周期
     * @return array configoption_sub.custom_cycle.id - 自定义周期ID
     * @return array configoption_sub.custom_cycle.name - 名称
     * @return array configoption_sub.custom_cycle.amount - 金额
     */
    public function index()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

        $result = $IdcsmartCommonProductConfigoptionSubModel->indexConfigoptionSub($param);

        return json($result);
    }

    /**
     * 时间 2022-09-26
     * @title 添加配置子项
     * @desc 添加配置子项
     * @url /admin/v1/idcsmart_common/configoption/:configoption_id/sub
     * @method  POST
     * @author wyh
     * @version v1
     * @param   int configoption_id - 配置项ID require
     * @param   string option_name - 配置项名称
     * @param   string option_param - 参数:请求接口
     * @param   int qty_min - 最小值：类型为数量的时候quantity,quantity_range选择
     * @param   int qty_max - 最大值：类型为数量的时候quantity,quantity_range选择
     * @param   string country - 国家:类型为区域时选择
     * @param   int qty_change - 数量变化最小值:类型为数量的时候quantity,quantity_range选择
     * @param   float onetime - 一次性价格
     * @param   object custom_cycle - 自定义周期及价格格式：{"{自定义周期ID}":"{金额}"}
     * @param   float custom_cycle.1 - 自定义周期及价格
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

        $result = $IdcsmartCommonProductConfigoptionSubModel->createConfigoptionSub($param);

        return json($result);
    }

    /**
     * 时间 2022-09-26
     * @title 修改配置子项
     * @desc 修改配置子项
     * @url /admin/v1/idcsmart_common/configoption/:configoption_id/sub/:id
     * @method  put
     * @author wyh
     * @version v1
     * @param   int configoption_id - 配置项ID require
     * @param   int id - 配置子项ID require
     * @param   string option_name - 配置项名称
     * @param   string option_param - 参数:请求接口
     * @param   int qty_min - 最小值：类型为数量的时候quantity,quantity_range选择
     * @param   int qty_max - 最大值：类型为数量的时候quantity,quantity_range选择
     * @param   string country - 国家:类型为区域时选择
     * @param   string country - 国家:类型为区域时选择
     * @param   float onetime - 一次性价格
     * @param   object custom_cycle - 自定义周期及价格格式：{"{自定义周期ID}":"{金额}"}
     * @param   float custom_cycle.1 - 自定义周期及价格
     */
    public function update()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

        $result = $IdcsmartCommonProductConfigoptionSubModel->updateConfigoptionSub($param);

        return json($result);
    }

    /**
     * 时间 2022-09-26
     * @title 删除配置子项
     * @desc 删除配置子项
     * @url /admin/v1/idcsmart_common/configoption/:configoption_id/sub/:id
     * @method  delete
     * @author wyh
     * @version v1
     * @param   int configoption_id - 配置项ID require
     * @param   int id - 配置子项ID require
     */
    public function delete()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

        $result = $IdcsmartCommonProductConfigoptionSubModel->deleteConfigoptionSub($param);

        return json($result);
    }
}


