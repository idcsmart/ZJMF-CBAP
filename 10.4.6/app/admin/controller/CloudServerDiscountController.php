<?php
namespace app\admin\controller;

use app\common\model\CloudServerDiscountModel;
use app\admin\validate\CloudServerDiscountValidate;

/**
 * @title 模板控制器-云服务器优惠
 * @desc 模板控制器-云服务器优惠
 * @use app\admin\controller\CloudServerDiscountController
 */
class CloudServerDiscountController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new CloudServerDiscountValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 云服务器优惠列表
     * @desc 云服务器优惠列表
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_discount
     * @method  GET
     * @return array list -  优惠
     * @return int list[].id - 优惠ID
     * @return string list[].title - 标题
     * @return string list[].description - 描述
     * @return string list[].url - 跳转链接
     */
    public function list()
    {
        // 实例化模型类
        $CloudServerDiscountModel = new CloudServerDiscountModel();

        // 云服务器优惠列表
        $data = $CloudServerDiscountModel->discountList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建云服务器优惠
     * @desc 创建云服务器优惠
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_discount
     * @method  POST
     * @param string title - 标题 required
     * @param string description - 描述 required
     * @param string url - 跳转链接 required
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $CloudServerDiscountModel = new CloudServerDiscountModel();
        
        // 创建云服务器优惠
        $result = $CloudServerDiscountModel->createDiscount($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑云服务器优惠
     * @desc 编辑云服务器优惠
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_discount/:id
     * @method  PUT
     * @param int id - 优惠ID required
     * @param string title - 标题 required
     * @param string description - 描述 required
     * @param string url - 跳转链接 required
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $CloudServerDiscountModel = new CloudServerDiscountModel();
        
        // 编辑云服务器优惠
        $result = $CloudServerDiscountModel->updateDiscount($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除云服务器优惠
     * @desc 删除云服务器优惠
     * @author theworld
     * @version v1
     * @url /admin/v1/cloud_server_discount/:id
     * @method  DELETE
     * @param int id - 优惠ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $CloudServerDiscountModel = new CloudServerDiscountModel();
        
        // 删除云服务器优惠
        $result = $CloudServerDiscountModel->deleteDiscount($param['id']);

        return json($result);
    }
}