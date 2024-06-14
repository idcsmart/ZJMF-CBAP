<?php
namespace app\admin\controller;

use app\common\model\SmsServiceProductModel;
use app\admin\validate\SmsServiceProductValidate;

/**
 * @title 模板控制器-短信服务商品
 * @desc 模板控制器-短信服务商品
 * @use app\admin\controller\SmsServiceProductController
 */
class SmsServiceProductController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new SmsServiceProductValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 短信服务商品列表
     * @desc 短信服务商品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/sms_service_product
     * @method  GET
     * @return array list -  商品
     * @return int list[].id - 商品ID
     * @return string list[].title - 标题
     * @return string list[].description - 描述
     * @return string list[].price - 价格
     * @return string list[].price_unit - 价格单位,month月year年
     * @return int list[].product_id - 关联商品ID
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $SmsServiceProductModel = new SmsServiceProductModel();

        // 短信服务商品列表
        $data = $SmsServiceProductModel->productList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建短信服务商品
     * @desc 创建短信服务商品
     * @author theworld
     * @version v1
     * @url /admin/v1/sms_service_product
     * @method  POST
     * @param string title - 标题 required
     * @param string description - 描述 required
     * @param float price - 价格 required
     * @param string price_unit - 价格单位,month月year年 required
     * @param int product_id - 关联商品ID required
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
        $SmsServiceProductModel = new SmsServiceProductModel();
        
        // 创建短信服务商品
        $result = $SmsServiceProductModel->createProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑短信服务商品
     * @desc 编辑短信服务商品
     * @author theworld
     * @version v1
     * @url /admin/v1/sms_service_product/:id
     * @method  PUT
     * @param int id - 商品ID required
     * @param string title - 标题 required
     * @param string description - 描述 required
     * @param float price - 价格 required
     * @param string price_unit - 价格单位,month月year年 required
     * @param int product_id - 关联商品ID required
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
        $SmsServiceProductModel = new SmsServiceProductModel();
        
        // 编辑短信服务商品
        $result = $SmsServiceProductModel->updateProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除短信服务商品
     * @desc 删除短信服务商品
     * @author theworld
     * @version v1
     * @url /admin/v1/sms_service_product/:id
     * @method  DELETE
     * @param int id - 商品ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $SmsServiceProductModel = new SmsServiceProductModel();
        
        // 删除短信服务商品
        $result = $SmsServiceProductModel->deleteProduct($param['id']);

        return json($result);
    }
}