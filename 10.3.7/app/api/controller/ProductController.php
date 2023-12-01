<?php
namespace app\api\controller;

use app\common\logic\ModuleLogic;
use app\common\model\ProductGroupModel;
use app\common\model\ProductModel;
use app\common\model\ServerGroupModel;
use app\common\model\ServerModel;

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
            $group['products'] = $ProductModel->alias('p')
                ->field("p.id,p.name,p.description,s.module,sgs.module as sgs_module")
                ->withAttr("module",function ($value,$data){
                    return $value??$data['sgs_module'];
                })
                ->whereIn('s.module|sgs.module',['mf_dcim','mf_cloud'])
                ->where('p.product_group_id',$group['id'])
                ->where('p.hidden',0)
                ->where('p.agentable',1)
                ->leftJoin('server s','p.type=\'server\' and s.id=p.rel_id')
                ->leftJoin('server_group sg','p.type=\'server_group\' and sg.id=p.rel_id')
                ->leftJoin('server sgs','sgs.server_group_id=sg.id')
                ->select()
                ->toArray();
        }

        $groupsFilter = [];
        foreach ($groups as $item){
            if (!empty($item['products'])){
                $groupsFilter[] = $item;
            }
        }

        return json([
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'products' => $groupsFilter,
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
        $product = $ProductModel->field('id,name,pay_type,price,cycle,auto_setup,description')
            ->where('hidden', 0)
            ->where('agentable', 1)
            ->where('id', $id)
            ->find();
        if(empty($product)){
            $product = (object)[]; 
        }
        if (!empty($product)){
            $product['cancel_control'] = 0;
            if (class_exists("addon\idcsmart_refund\IdcsmartRefund")){
                $product['cancel_control'] = 1;
            }
        }

        $ModuleLogic = new ModuleLogic();
        $res = $ModuleLogic->getPriceCycle($id);
        $hookDiscountResultsOrgins = hook("client_discount_by_amount",['client_id'=>get_client_id(),'product_id'=>$product['id'],'amount'=>$res['price']]);
        foreach ($hookDiscountResultsOrgins as $hookDiscountResultsOrgin){
            if ($hookDiscountResultsOrgin['status']==200){
                $res['price'] = bcadd($res['price'], $hookDiscountResultsOrgin['data']['discount']??0, 2);
            }
        }
        $product['price'] = $res['price'];
        $product['cycle'] = $res['cycle']??$product['cycle'];

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