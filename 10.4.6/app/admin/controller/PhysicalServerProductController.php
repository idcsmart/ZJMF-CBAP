<?php
namespace app\admin\controller;

use app\common\model\PhysicalServerProductModel;
use app\admin\validate\PhysicalServerProductValidate;

/**
 * @title 模板控制器-物理服务器商品
 * @desc 模板控制器-物理服务器商品
 * @use app\admin\controller\PhysicalServerProductController
 */
class PhysicalServerProductController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new PhysicalServerProductValidate();
    }

    /**
     * 时间 2024-04-02
     * @title 物理服务器商品列表
     * @desc 物理服务器商品列表
     * @author theworld
     * @version v1
     * @url /admin/v1/physical_server_product
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list -  商品
     * @return int list[].id - 商品ID
     * @return int list[].area_id - 区域ID
     * @return string list[].first_area - 一级区域
     * @return string list[].second_area - 二级区域
     * @return string list[].title - 标题
     * @return string list[].description - 描述
     * @return string list[].cpu - 处理器
     * @return string list[].memory - 内存
     * @return string list[].disk - 硬盘
     * @return string list[].ip_num - IP数量
     * @return string list[].bandwidth - 带宽
     * @return string list[].duration - 时长
     * @return string list[].tag - 标签
     * @return string list[].original_price - 原价
     * @return string list[].original_price_unit - 原价单位,month月year年
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
        $PhysicalServerProductModel = new PhysicalServerProductModel();

        // 物理服务器商品列表
        $data = $PhysicalServerProductModel->productList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 创建物理服务器商品
     * @desc 创建物理服务器商品
     * @author theworld
     * @version v1
     * @url /admin/v1/physical_server_product
     * @method  POST
     * @param int area_id - 区域ID required
     * @param string title - 标题 required
     * @param string description - 描述 required
     * @param string cpu - 处理器 required
     * @param string memory - 内存 required
     * @param string disk - 硬盘 required
     * @param string ip_num - IP数量 required
     * @param string bandwidth - 带宽 required
     * @param string duration - 时长 required
     * @param string tag - 标签 required
     * @param float original_price - 原价 required
     * @param string original_price_unit - 原价单位,month月year年 required
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
        $PhysicalServerProductModel = new PhysicalServerProductModel();
        
        // 创建物理服务器商品
        $result = $PhysicalServerProductModel->createProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 编辑物理服务器商品
     * @desc 编辑物理服务器商品
     * @author theworld
     * @version v1
     * @url /admin/v1/physical_server_product/:id
     * @method  PUT
     * @param int id - 商品ID required
     * @param int area_id - 区域ID required
     * @param string title - 标题 required
     * @param string description - 描述 required
     * @param string cpu - 处理器 required
     * @param string memory - 内存 required
     * @param string disk - 硬盘 required
     * @param string ip_num - IP数量 required
     * @param string bandwidth - 带宽 required
     * @param string duration - 时长 required
     * @param string tag - 标签 required
     * @param float original_price - 原价 required
     * @param string original_price_unit - 原价单位,month月year年 required
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
        $PhysicalServerProductModel = new PhysicalServerProductModel();
        
        // 编辑物理服务器商品
        $result = $PhysicalServerProductModel->updateProduct($param);

        return json($result);
    }

    /**
     * 时间 2024-04-02
     * @title 删除物理服务器商品
     * @desc 删除物理服务器商品
     * @author theworld
     * @version v1
     * @url /admin/v1/physical_server_product/:id
     * @method  DELETE
     * @param int id - 商品ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $PhysicalServerProductModel = new PhysicalServerProductModel();
        
        // 删除物理服务器商品
        $result = $PhysicalServerProductModel->deleteProduct($param['id']);

        return json($result);
    }
}