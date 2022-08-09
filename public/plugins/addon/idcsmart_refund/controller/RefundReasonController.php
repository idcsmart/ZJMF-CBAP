<?php
namespace addon\idcsmart_refund\controller;

use addon\idcsmart_refund\model\IdcsmartRefundReasonModel;
use addon\idcsmart_refund\validate\IdcsmartRefundReasonValidate;
use app\event\controller\PluginBaseController;

/**
 * @title 停用原因管理(后台)
 * @desc 停用原因管理(后台)
 * @use addon\idcsmart_refund\controller\RefundReasonController
 */
class RefundReasonController extends PluginBaseController
{
    private $validate=null;

    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartRefundReasonValidate();
    }

    /**
     * 时间 2022-07-07
     * @title 停用原因列表
     * @desc 停用原因列表
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/reason
     * @method  GET
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序:id
     * @param string param.sort - 升/降序:asc,desc
     * @param string param.keywords - 关键字搜索:内容
     * @return array list - 停用原因列表
     * @return int list[].id - ID
     * @return string list[].content - 内容
     * @return string list[].admin_name - 提交人
     * @return string list[].create_time - 提交时间
     */
    public function refundReasonList()
    {
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $IdcsmartRefundReasonModel = new IdcsmartRefundReasonModel();

        $result = $IdcsmartRefundReasonModel->refundReasonList($param);

        return json($result);
    }

    /**
     * 时间 2022-07-07
     * @title 新增停用原因
     * @desc 新增停用原因
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/reason
     * @method  POST
     * @param string content - 内容 required
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartRefundReasonModel = new IdcsmartRefundReasonModel();

        $result = $IdcsmartRefundReasonModel->createRefundReason($param);

        return json($result);
    }

    /**
     * 时间 2022-07-07
     * @title 编辑停用原因
     * @desc 编辑停用原因
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/reason/:id
     * @method  PUT
     * @param int id - 停用原因ID required
     * @param string content - 内容 required
     */
    public function update()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartRefundReasonModel = new IdcsmartRefundReasonModel();

        $result = $IdcsmartRefundReasonModel->updateRefundReason($param);

        return json($result);
    }

    /**
     * 时间 2022-07-07
     * @title 删除停用原因
     * @desc 删除停用原因
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/reason/:id
     * @method  DELETE
     * @param int id - 停用原因ID required
     */
    public function delete()
    {
        $param = $this->request->param();

        $IdcsmartRefundReasonModel = new IdcsmartRefundReasonModel();

        $result = $IdcsmartRefundReasonModel->deleteRefundReason(intval($param['id']));

        return json($result);
    }

    /**
     * 时间 2022-07-07
     * @title 获取停用原因详情
     * @desc 获取停用原因详情
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/reason/:id
     * @method  GET
     * @param int id - 退款商品ID required
     * @return int id - ID
     * @return string content - 内容
     */
    public function index()
    {
        $param = $this->request->param();

        $IdcsmartRefundReasonModel = new IdcsmartRefundReasonModel();

        $result = $IdcsmartRefundReasonModel->indexRefundReason(intval($param['id']));

        return json($result);
    }

    /**
     * 时间 2022-07-07
     * @title 获取停用原因自定义设置
     * @desc 获取停用原因自定义设置
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/reason/custom
     * @method  GET
     * @return int reason_custom - 停用原因是否自定义:1是,0否默认
     */
    public function custom()
    {
        $IdcsmartRefundReasonModel = new IdcsmartRefundReasonModel();

        $result = $IdcsmartRefundReasonModel->indexCustomRefundReason();

        return json($result);
    }

    /**
     * 时间 2022-07-07
     * @title 停用原因自定义
     * @desc 停用原因自定义
     * @author wyh
     * @version v1
     * @url /admin/v1/refund/reason/custom
     * @method  POST
     * @param int reason_custom - 停用原因是否自定义:1是,0否默认 required
     */
    public function customSet()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('custom')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $IdcsmartRefundReasonModel = new IdcsmartRefundReasonModel();

        $result = $IdcsmartRefundReasonModel->customRefundReason($param);

        return json($result);
    }
    
}