<?php
namespace addon\idcsmart_refund\controller;

use addon\idcsmart_refund\model\IdcsmartRefundProductModel;
use addon\idcsmart_refund\validate\IdcsmartRefundProductValidate;
use app\event\controller\PluginAdminBaseController;

/**
 * @title 退款商品管理(后台)
 * @desc 退款商品管理(后台)
 * @use addon\idcsmart_refund\controller\RefundProductController
 */
class RefundProductController extends PluginAdminBaseController
{
    private $validate=null;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartRefundProductValidate();
    }

    /**
     * 时间 2022-07-06
     * @title 退款商品列表
     * @desc 退款商品列表
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/product
     * @method  GET
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序:id
     * @param string param.sort - 升/降序:asc,desc
     * @param string param.keywords - 关键字搜索:商品名称
     * @return array list - 退款商品列表
     * @return int list[].id - ID
     * @return string list[].product_name - 商品名称
     * @return array list[].config_option - 商品配置
     * @return string list[].config_option[].name - 配置名称
     * @return string list[].config_option[].field - 配置字段
     * @return string list[].config_option[].type - 配置类型
     * @return array list[].config_option[].option - 配置选项{"name":1,"value":1}
     * @return string list[].config_option[].option[].name - 选项名称
     * @return string list[].config_option[].option[].value - 选项值
     * @return string list[].admin_name - 提交人
     * @return string list[].create_time - 提交时间
     * @return string list[].type - 退款类型
     */
    public function refundProductList()
    {
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $IdcsmartRefundProductModel = new IdcsmartRefundProductModel();

        $result = $IdcsmartRefundProductModel->refundProductList($param);

        return json($result);
    }

    /**
     * 时间 2022-07-07
     * @title 新增退款商品
     * @desc 新增退款商品
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/product
     * @method  POST
     * @param int product_id - 商品ID required
     * @param string type - 退款类型:Artificial人工，Auto自动 required
     * @param string require - 退款要求:First首次订购,Same同类商品首次订购
     * @param int range_control - 购买后X天内控制:0否默认,1是(当range不传值或值为null时,传此字段为0;否则传1) required
     * @param int range - 购买后X天内
     * @param string rule - 退款规则:Day按天退款,Month按月退款,Ratio按比例退款 required
     * @param float ratio_value 0.00 比例,当rule=Ratio时,需要传此值,默认为0
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartRefundProductModel = new IdcsmartRefundProductModel();

        $result = $IdcsmartRefundProductModel->createRefundProduct($param);

        return json($result);
    }

    /**
     * 时间 2022-07-07
     * @title 编辑退款商品
     * @desc 编辑退款商品
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/product/:id
     * @method  PUT
     * @param int id - 退款商品ID required
     * @param int product_id - 商品ID required
     * @param string type - 退款类型:Artificial人工，Auto自动 required
     * @param string require - 退款要求:First首次订购,Same同类商品首次订购
     * @param int range_control - 购买后X天内控制:0否默认,1是(当range不传值或值为null时,传此字段为0;否则传1) required
     * @param int range - 购买后X天内
     * @param string rule - 退款规则:Day按天退款,Month按月退款,Ratio按比例退款 required
     * @param float ratio_value 0.00 比例,当rule=Ratio时,需要传此值,默认为0
     */
    public function update()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartRefundProductModel = new IdcsmartRefundProductModel();

        $result = $IdcsmartRefundProductModel->updateRefundProduct($param);

        return json($result);
    }

    /**
     * 时间 2022-07-07
     * @title 删除退款商品
     * @desc 删除退款商品
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/product/:id
     * @method  DELETE
     * @param int id - 退款商品ID required
     */
    public function delete()
    {
        $param = $this->request->param();

        $IdcsmartRefundProductModel = new IdcsmartRefundProductModel();

        $result = $IdcsmartRefundProductModel->deleteRefundProduct(intval($param['id']));

        return json($result);
    }

    /**
     * 时间 2022-07-07
     * @title 获取退款商品详情
     * @desc 获取退款商品详情
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/product/:id
     * @method  GET
     * @param int id - 退款商品ID required
     * @return int id - ID
     * @return int product_id - 商品ID
     * @return array config_option - 商品配置
     * @return string config_option[].name - 配置名称
     * @return string config_option[].field - 配置字段
     * @return string config_option[].type - 配置类型
     * @return array config_option[].option - 配置选项{"name":1,"value":1}
     * @return string config_option[].option[].name - 选项名称
     * @return string config_option[].option[].value - 选项值
     * @return string type - 退款类型
     * @return string require - 退款要求
     * @return int range_control - 购买后X天内控制:0否默认,1是
     * @return int range - 购买天数(若range_control==0,range默认填充空,即使返回0也填充为空)
     * @return string rule - 退款规则
     * @return float ratio_value - 比例
     */
    public function index()
    {
        $param = $this->request->param();

        $IdcsmartRefundProductModel = new IdcsmartRefundProductModel();

        $result = $IdcsmartRefundProductModel->indexRefundProduct(intval($param['id']));

        return json($result);
    }
    
}