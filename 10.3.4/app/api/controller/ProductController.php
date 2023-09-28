<?php
namespace app\api\controller;

use app\common\model\ProductGroupModel;
use app\common\model\ProductModel;

class ProductController
{
    /**
     *
     */
    public function groupProduct()
    {
        $ProductGroupModel = new ProductGroupModel();
        $groups = $ProductGroupModel->field('id,name')
            ->where('hidden',0)
            ->where('parent_id','>',0)
            ->order('order','asc')
            ->select()
            ->toArray();
        $ProductModel = new ProductModel();
        foreach ($groups as &$group){
            $group['products'] = $ProductModel->field("id,name,description")
                ->where('product_group_id',$group['id'])
                ->where('hidden',0)
                ->where('agentable',1)
                ->order('id','asc')
                ->select()
                ->toArray();
        }
        return json([
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'products' => $groups,
                'currency' => configuration("currency_code")
            ]
        ]);
    }

    public function product()
    {
        if ($list = idcsmart_cache('product:list')){
            $list = json_decode($list,true);
        }else{
            $ProductModel = new ProductModel();
            $list = $ProductModel->field('id,name,description,pay_type,price,cycle')
                ->where('hidden',0)
                ->where('agentable',1)
                ->order('id','desc')
                ->order('order','asc')
                ->select()
                ->toArray();
            idcsmart_cache('product:list',json_encode($list),30*24*3600);
        }

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'list' => $list
            ]
        ];

        return json($result);
    }

    public function index()
    {
        $param = request()->param();
        $id = intval($param['id'] ?? 0);
        $ProductModel = new ProductModel();
        $product = $ProductModel->field('id,name,pay_type,price,cycle')
            ->where('hidden', 0)
            ->where('agentable', 1)
            ->where('id', $id)
            ->find();
        if(empty($product)){
            $product = (object)[]; 
        }
        
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'product' => $product
            ]
        ];

        return json($result);
    }

    /*public function allConfigoption()
    {
        $param = request()->param();

        $ProductModel = new ProductModel();

        $result = $ProductModel->productAllConfigOption($param['id']);

        return json($result);
    }*/

    /**
     * 时间 2023-02-16
     * @title 获取上游商品模块和资源
     * @desc 获取上游商品模块和资源
     * @url 
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 商品ID require
     * @return  string module - resmodule名称
     * @return  string url - zip包完整下载路径
     */
    public function downloadResource(){
        $param = request()->param();

        $ProductModel = new ProductModel();
        $result = $ProductModel->downloadResource($param);
        return json($result);
    }


}