<?php
namespace app\admin\controller;

use app\common\model\TrademarkServiceProductModel;
use app\admin\validate\TrademarkServiceProductValidate;

/**
 * @title 模板控制器-商标延伸服务商品
 * @desc 模板控制器-商标延伸服务商品
 * @use app\admin\controller\TrademarkServiceProductController
 */
class TrademarkServiceProductController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new TrademarkServiceProductValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 商标延伸服务商品列表
     * @desc 商标延伸服务商品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/trademark_service_product
     * @method  GET
     * @return array list -  商品
     * @return int list[].id - 商品ID
     * @return string list[].title - 标题
     * @return string list[].description - 描述
     * @return string list[].price - 价格
     * @return int list[].product_id - 关联商品ID
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $TrademarkServiceProductModel = new TrademarkServiceProductModel();

        // 商标延伸服务商品列表
        $data = $TrademarkServiceProductModel->productList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建商标延伸服务商品
     * @desc 创建商标延伸服务商品
     * @author theworld
     * @version v1
     * @url /admin/v1/trademark_service_product
     * @method  POST
     * @param string title - 标题 required
     * @param string description - 描述 required
     * @param float price - 价格 required
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
        $TrademarkServiceProductModel = new TrademarkServiceProductModel();
        
        // 创建商标延伸服务商品
        $result = $TrademarkServiceProductModel->createProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑商标延伸服务商品
     * @desc 编辑商标延伸服务商品
     * @author theworld
     * @version v1
     * @url /admin/v1/trademark_service_product/:id
     * @method  PUT
     * @param int id - 商品ID required
     * @param string title - 标题 required
     * @param string description - 描述 required
     * @param float price - 价格 required
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
        $TrademarkServiceProductModel = new TrademarkServiceProductModel();
        
        // 编辑商标延伸服务商品
        $result = $TrademarkServiceProductModel->updateProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除商标延伸服务商品
     * @desc 删除商标延伸服务商品
     * @author theworld
     * @version v1
     * @url /admin/v1/trademark_service_product/:id
     * @method  DELETE
     * @param int id - 商品ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $TrademarkServiceProductModel = new TrademarkServiceProductModel();
        
        // 删除商标延伸服务商品
        $result = $TrademarkServiceProductModel->deleteProduct($param['id']);

        return json($result);
    }
}