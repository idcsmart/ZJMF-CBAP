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


