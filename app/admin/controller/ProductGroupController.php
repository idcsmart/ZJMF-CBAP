<?php
namespace app\admin\controller;

use app\admin\validate\ProductGroupValidate;
use app\common\model\ProductGroupModel;

/**
 * @title 商品组管理
 * @desc 商品组管理
 * @use app\admin\controller\ProductGroupController
 */
class ProductGroupController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ProductGroupValidate();
    }

    /**
     * 时间 2022-5-17
     * @title 获取商品一级分组
     * @desc 获取商品一级分组
     * @url /admin/v1/product/group/first
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 商品一级分组
     * @return int list[].id - 商品一级分组ID
     * @return int list[].name - 商品一级分组名称
     * @return int count - 商品一级分组总数
     */
    public function productGroupFirstList()
    {
        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new ProductGroupModel())->productGroupFirstList()
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 获取商品二级分组
     * @desc 获取商品二级分组
     * @url /admin/v1/product/group/second
     * @method  GET
     * @author wyh
     * @version v1
     * @param int id - 一级分组ID
     * @return array list - 商品二级分组
     * @return int list[].id - 商品二级分组ID
     * @return int list[].name - 商品二级分组名称
     * @return int count - 商品二级分组总数
     */
    public function productGroupSecondList()
    {
        $param = $this->request->param();

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new ProductGroupModel())->productGroupSecondList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 新建商品分组
     * @desc 新建商品分组
     * @url /admin/v1/product/group
     * @method  POST
     * @author wyh
     * @version v1
     * @param string name 电脑 分组名称 required
     * @param int id 1(传0表示创建一级分组) 分组ID required
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductGroupModel())->createProductGroup($param);

        return json($result);
    }

    /**
     * 时间 2022-5-31
     * @title 编辑商品分组
     * @desc 编辑商品分组
     * @url /admin/v1/product/group/:id
     * @method  PUT
     * @author wyh
     * @version v1
     * @param int id 1 分组ID required
     * @param string name 电脑 分组名称 required
     */
    public function update()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('edit')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductGroupModel())->updateProductGroup($param);

        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 删除商品分组
     * @desc 删除商品分组
     * @url /admin/v1/product/group/:id
     * @method  DELETE
     * @author wyh
     * @version v1
     * @param int id 1 分组ID required
     */
    public function delete()
    {
        $param = $this->request->param();

        $result = (new ProductGroupModel())->deleteProductGroup(intval($param['id']));

        return json($result);
    }

    /**
     * 时间 2022-5-17
     * @title 移动商品至其他商品组
     * @desc 移动商品至其他商品组
     * @url /admin/v1/product/group/:id/product
     * @method  PUT
     * @author wyh
     * @version v1
     * @param int id 1 二级分组ID required
     * @param int target_product_group_id 1 移动后二级分组ID required
     */
    public function moveProduct()
    {
        $param = $this->request->param();

        $result = (new ProductGroupModel())->moveProduct($param);

        return json($result);
    }

    /**
     * 时间 2022-07-11
     * @title 商品分组拖动排序
     * @desc 商品分组拖动排序
     * @url /admin/v1/product/group/order/:id
     * @method  put
     * @author wyh
     * @version v1
     * @param int id 1 分组ID required
     * @param int first_product_group_id 1 一级分组ID required
     * @param int pre_product_group_id 1 移动后前一个分组ID(没有则传0) required
     * @param int pre_first_product_group_id 1 移动后的一级分组ID required
     * @param int backward 1 是否向后移动:1是,0否 required
     */
    public function order()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('order')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductGroupModel())->orderProductGroup($param);

        return json($result);
    }

    /**
     * 时间 2022-07-11
     * @title 一级商品分组拖动排序
     * @desc 一级商品分组拖动排序
     * @url /admin/v1/product/group/first/order/:id
     * @method  put
     * @author wyh
     * @version v1
     * @param int id 1 一级分组ID required
     * @param int pre_first_product_group_id 1 移动后前一个一级分组ID(没有则传0) required
     * @param int backward 1 是否向后移动:1是,0否 required
     */
    public function orderFirst()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('order_first')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ProductGroupModel())->orderFristProductGroup($param);

        return json($result);
    }

}

