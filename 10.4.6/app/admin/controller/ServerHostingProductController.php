<?php
namespace app\admin\controller;

use app\common\model\ServerHostingProductModel;
use app\admin\validate\ServerHostingProductValidate;

/**
 * @title 模板控制器-服务器托管商品
 * @desc 模板控制器-服务器托管商品
 * @use app\admin\controller\ServerHostingProductController
 */
class ServerHostingProductController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ServerHostingProductValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 服务器托管商品列表
     * @desc 服务器托管商品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/server_hosting_product
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list -  商品
     * @return int list[].id - 商品ID
     * @return int list[].area_id - 区域ID
     * @return string list[].first_area - 所属区域
     * @return string list[].title - 标题
     * @return string list[].region - 地域
     * @return string list[].ip_num - IP数量
     * @return string list[].bandwidth - 带宽
     * @return string list[].defense - 防御
     * @return string list[].bandwidth_price - 带宽价格
     * @return string list[].bandwidth_price_unit - 带宽价格单位,month/M/月year/M/年
     * @return string list[].selling_price - 售价
     * @return string list[].selling_price_unit - 售价单位,month月year年
     * @return int list[].product_id - 关联商品ID
     * @return int count - 商品数量
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $ServerHostingProductModel = new ServerHostingProductModel();

        // 服务器托管商品列表
        $data = $ServerHostingProductModel->productList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建服务器托管商品
     * @desc 创建服务器托管商品
     * @author theworld
     * @version v1
     * @url /admin/v1/server_hosting_product
     * @method  POST
     * @param int area_id - 区域ID required
     * @param string title - 标题 required
     * @param string region - 地域 required
     * @param string ip_num - IP数量 required
     * @param string bandwidth - 带宽 required
     * @param string defense - 防御 required
     * @param float bandwidth_price - 带宽价格 required
     * @param string bandwidth_price_unit - 带宽价格单位,month/M/月year/M/年 required
     * @param float selling_price - 售价 required
     * @param string selling_price_unit - 售价单位,month月year年 required
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
        $ServerHostingProductModel = new ServerHostingProductModel();
        
        // 创建服务器托管商品
        $result = $ServerHostingProductModel->createProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑服务器托管商品
     * @desc 编辑服务器托管商品
     * @author theworld
     * @version v1
     * @url /admin/v1/server_hosting_product/:id
     * @method  PUT
     * @param int id - 商品ID required
     * @param int area_id - 区域ID required
     * @param string title - 标题 required
     * @param string region - 地域 required
     * @param string ip_num - IP数量 required
     * @param string bandwidth - 带宽 required
     * @param string defense - 防御 required
     * @param float bandwidth_price - 带宽价格 required
     * @param string bandwidth_price_unit - 带宽价格单位,month/M/月year/M/年 required
     * @param float selling_price - 售价 required
     * @param string selling_price_unit - 售价单位,month月year年 required
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
        $ServerHostingProductModel = new ServerHostingProductModel();
        
        // 编辑服务器托管商品
        $result = $ServerHostingProductModel->updateProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除服务器托管商品
     * @desc 删除服务器托管商品
     * @author theworld
     * @version v1
     * @url /admin/v1/server_hosting_product/:id
     * @method  DELETE
     * @param int id - 商品ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $ServerHostingProductModel = new ServerHostingProductModel();
        
        // 删除服务器托管商品
        $result = $ServerHostingProductModel->deleteProduct($param['id']);

        return json($result);
    }
}