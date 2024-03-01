<?php
namespace app\api\controller;

use app\common\logic\ModuleLogic;
use app\common\model\ProductGroupModel;
use app\common\model\ProductModel;
use app\common\model\ServerGroupModel;
use app\common\model\ServerModel;
use app\common\model\SelfDefinedFieldModel;
use app\common\model\UpstreamProductModel;

/**
 * @title 商品管理
 * @desc 商品管理
 * @use app\api\controller\ProductController
 */
class ProductController
{
    /**
     * @title 所有商品列表(含分组)
     * @desc 所有商品列表(含分组)
     * @author wyh
     * @version v1
     * @url /api/v1/group/product
     * @method  GET
     * @return array products - 商品列表，包括商品分组
     * @return int products[].id - 商品分组ID
     * @return string products[].name - 商品分组名称
     * @return array products[].products -
     * @return int products[].products[].id - 商品ID
     * @return string products[].products[].name - 名称
     * @return string products[].products[].description - 描述
     * @return string products[].products[].module - 服务器关联模块
     * @return string products[].products[].sgs_module - 服务器分组关联模块
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

    /**
     * @title 所有商品列表
     * @desc 所有商品列表
     * @author wyh
     * @version v1
     * @url /api/v1/product
     * @method  GET
     * @return array list - 商品列表
     * @return int list[].id - 商品ID
     * @return string list[].name - 名称
     * @return string list[].description - 描述
     * @return string list[].pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return float list[].price - 价格
     * @return string list[].cycle - 周期
     */
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

    /**
     * @title 商品详情
     * @desc 商品详情
     * @author wyh
     * @version v1
     * @url /api/v1/product/:id
     * @method  GET
     * @return int id - 商品ID
     * @return string name - 名称
     * @return string pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return float price - 价格
     * @return string cycle - 周期
     * @return int auto_setup - 是否自动开通，1是，0否
     * @return string description - 描述
     * @return int cancel_control - 取消控制
     * @return array self_defined_field - 自定义字段
     * @return int self_defined_field[].id - ID
     * @return string self_defined_field[].field_name - 字段名称
     * @return string self_defined_field[].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区)
     * @return string self_defined_field[].description - 字段描述
     * @return string self_defined_field[].regexpr - 验证规则
     * @return string self_defined_field[].field_option - 下拉选项
     * @return string self_defined_field[].is_required -是否必填(0=否,1=是)
     * @return string self_defined_field[].show_client_host_list - 前台列表可见(0=否,1=是)
     * @return string self_defined_field[].upstream_id - 上下游ID（需要的need_upstream_id才返回）
     */
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

        $UpstreamProductModel = new UpstreamProductModel();
        $upstreamProduct = $UpstreamProductModel->where('product_id',$id)->find();
        if (empty($upstreamProduct)){
            $ModuleLogic = new ModuleLogic();
            $res = $ModuleLogic->getPriceCycle($id);
        }else{
            // 若是上游商品，则使用商品price（处理多级代理）
            $res['price'] = $product['price'];
        }

        $hookDiscountResultsOrgins = hook("client_discount_by_amount",['client_id'=>get_client_id(),'product_id'=>$product['id'],'amount'=>$res['price']]);
        foreach ($hookDiscountResultsOrgins as $hookDiscountResultsOrgin){
            if ($hookDiscountResultsOrgin['status']==200){
                $res['price'] = bcsub($res['price'], $hookDiscountResultsOrgin['data']['discount']??0, 2);
                $res['price'] = $res['price']>0 ? $res['price'] : 0;
            }
        }
        $product['price'] = $res['price'];
        $product['cycle'] = $res['cycle']??$product['cycle'];

        // 自定义字段
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        $selfDefinedField = $SelfDefinedFieldModel->showOrderPageField([
            'id' => $id,
        ]);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'product'           => $product,
                'self_defined_field'=> $selfDefinedField['data'],
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2023-02-16
     * @title 获取上游商品模块和资源
     * @desc 获取上游商品模块和资源
     * @url /api/v1/product/:id/resource
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 商品ID require
     * @return  string module - resmodule名称
     * @return  string url - zip包完整下载路径
     * @return  string version - 版本号
     */
    public function downloadResource()
    {
        $param = request()->param();

        $ProductModel = new ProductModel();
        $result = $ProductModel->downloadResource($param);
        return json($result);
    }


}