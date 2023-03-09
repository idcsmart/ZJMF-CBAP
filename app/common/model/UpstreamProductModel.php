<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\logic\UpstreamLogic;

/**
 * @title 上游商品模型
 * @desc 上游商品模型
 * @use app\common\model\UpstreamProductModel
 */
class UpstreamProductModel extends Model
{
	protected $name = 'upstream_product';

    // 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'supplier_id'           => 'int',
        'product_id'            => 'int',
        'upstream_product_id'   => 'int',
        'profit_percent'        => 'float',
        'certification'         => 'int',
        'create_time'           => 'int',
        'update_time'           => 'int',
        'res_module'            => 'string',
    ];

	# 商品列表
    public function productList($param)
    {
    	if (!isset($param['orderby']) || !in_array($param['orderby'],['id'])){
            $param['orderby'] = 'p.id';
        }else{
            $param['orderby'] = 'p.'.$param['orderby'];
        }
        $param['keywords'] = $param['keywords'] ?? '';
        $param['supplier_id'] = intval($param['supplier_id'] ?? 0);

        $where = function (Query $query) use($param) {
        	$query->where('p.id', '>', 0);
            if(!empty($param['keywords'])){
                $query->where('p.id|p.name|p.description', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['supplier_id'])){
                $query->where('a.supplier_id', $param['supplier_id']);
            }
        };

        $count = $this->alias('a')
            ->field('p.id')
            ->leftJoin('product p','p.id=a.product_id')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->leftjoin('supplier s','a.supplier_id=s.id')
            ->where($where)
            ->count();

        $products = $this->alias('a')
            ->field('p.id,p.name,p.description,a.supplier_id,s.name supplier_name,a.profit_percent,p.auto_setup,p.hidden,p.pay_type,p.price,p.cycle,a.upstream_product_id,a.certification,pg.name as product_group_name_second,pg.id as product_group_id_second,pgf.name as product_group_name_first,pgf.id as product_group_id_first')
            ->leftJoin('product p','p.id=a.product_id')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->leftjoin('supplier s','a.supplier_id=s.id')
            ->where($where)
            ->limit($param['limit'])
    		->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        foreach ($products as $key => $value) {
            $products[$key]['price'] = amount_format($value['price']);
        }
        
        return ['list'=>$products,'count'=>$count];
        
    }

    public function indexProduct($id)
    {
        $product = $this->alias('a')
            ->field('p.id,p.name,p.description,a.supplier_id,s.name supplier_name,a.profit_percent,p.auto_setup,p.hidden,p.pay_type,p.price,p.cycle,a.upstream_product_id,a.certification,pg.name as product_group_name_second,pg.id as product_group_id_second,pgf.name as product_group_name_first,pgf.id as product_group_id_first')
            ->leftJoin('product p','p.id=a.product_id')
            ->leftjoin('product_group pg','p.product_group_id=pg.id')
            ->leftjoin('product_group pgf','pg.parent_id=pgf.id')
            ->leftjoin('supplier s','a.supplier_id=s.id')
            ->where('p.id', $id)
            ->find();
        if(empty($product)){
            $product = (object)[];
        }
        return $product;
    }

    # 添加商品
    public function createProduct($param)
    {
    	$productGroupId = intval($param['product_group_id']);

        $ProductGroupModel = new ProductGroupModel();
        $productGroup = $ProductGroupModel->where('id',$productGroupId)
            ->where('parent_id','>',0)
            ->find();
        if (empty($productGroup)){
            return ['status'=>400,'msg'=>lang('please_select_product_group_second')];
        }

        $supplier = SupplierModel::find($param['supplier_id']);
        if (empty($supplier)){
            return ['status'=>400,'msg'=>lang('supplier_is_not_exist')];
        }

        // 从上游商品详情拉取
        $UpstreamLogic = new UpstreamLogic();
        $res = $UpstreamLogic->upstreamProductDetail(['url' => $supplier['url'], 'id' => $param['upstream_product_id']]);
        if(empty($res['data'])){
            return ['status'=>400,'msg'=>lang('upstream_product_is_not_exist')];
        }

        $exist = $this->where('upstream_product_id', $param['upstream_product_id'])->find();
        if($exist){
            return ['status'=>400,'msg'=>lang('agent_product_cannot_repeat_agent')];
        }

        $resource = $UpstreamLogic->upstreamProductDownloadResource(['url' => $supplier['url'], 'id' => $param['upstream_product_id']]);
        if($resource['status']==400){
            return ['status'=>400, 'msg'=>$resource['msg']];
        }


    	$this->startTrans();
        try{
            $maxOrder = ProductModel::max('order');

            $price = $res['data']['price'] ?? 0;
            $price = bcdiv($price*(100+$param['profit_percent']), 100, 2); 

        	$product = ProductModel::create([
                'name' => $param['name'],
                'order' => $maxOrder+1,
                'product_group_id' => $productGroupId,
                'description' => $param['description'] ?? '',
                'pay_type' => $res['data']['pay_type'] ?? 'recurring_prepayment',
                'auto_setup' => $param['auto_setup'],
                'price' => $price,
                'cycle' => $res['data']['cycle'] ?? '',
                'create_time' => time()
            ]);

            $this->create([
            	'supplier_id' => $param['supplier_id'],
                'product_id' => $product->id,
                'upstream_product_id' => $param['upstream_product_id'],
                'profit_percent' => $param['profit_percent'],
                'certification' => $param['certification'],
                'create_time' => time(),
                'res_module' => $resource['data']['module'] ?? ''
            ]);

            # 记录日志
            active_log(lang('log_admin_create_upstream_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$param['name'].'#']),'product',$product->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('create_fail')];
        }

        return ['status'=>200,'msg'=>lang('create_success')];
    }

    # 编辑商品
    public function updateProduct($param)
    {
    	$product = ProductModel::find($param['id']);
        if (empty($product)){
            return ['status'=>400,'msg'=>lang('product_is_not_exist')];
        }

    	$productGroupId = intval($param['product_group_id']);

        $ProductGroupModel = new ProductGroupModel();
        $productGroup = $ProductGroupModel->where('id',$productGroupId)
            ->where('parent_id','>',0)
            ->find();
        if (empty($productGroup)){
            return ['status'=>400,'msg'=>lang('please_select_product_group_second')];
        }

        $supplier = SupplierModel::find($param['supplier_id']);
        if (empty($supplier)){
            return ['status'=>400,'msg'=>lang('supplier_is_not_exist')];
        }

        // 从上游商品详情拉取
        $UpstreamLogic = new UpstreamLogic();
        $res = $UpstreamLogic->upstreamProductDetail(['url' => $supplier['url'], 'id' => $param['upstream_product_id']]);
        if(empty($res['data'])){
            return ['status'=>400,'msg'=>lang('upstream_product_is_not_exist')];
        }

        $exist = $this->where('upstream_product_id', $param['upstream_product_id'])->where('product_id', '<>', $param['id'])->find();
        if($exist){
            return ['status'=>400,'msg'=>lang('agent_product_cannot_repeat_agent')];
        }

        $resource = $UpstreamLogic->upstreamProductDownloadResource(['url' => $supplier['url'], 'id' => $param['upstream_product_id']]);
        if($resource['status']==400){
            return ['status'=>400, 'msg'=>$resource['msg']];
        }

        $upstreamProduct = $this->where('product_id', $param['id'])->find();
        $upstreamProduct = $upstreamProduct->toArray();
        $upstreamProduct['name'] = $product['name'];
        $upstreamProduct['auto_setup'] = $product['auto_setup'];
        # 日志描述
        $logDescription = log_description($upstreamProduct,$param,'upstream_product');

    	$this->startTrans();
        try{

            $price = $res['data']['price'] ?? 0;
            $price = bcdiv($price*(100+$param['profit_percent']), 100, 2); 

        	ProductModel::update([
                'name' => $param['name'],
                'product_group_id' => $productGroupId,
                'description' => $param['description'] ?? '',
                'pay_type' => $res['data']['pay_type'] ?? 'recurring_prepayment',
                'auto_setup' => $param['auto_setup'],
                'price' => $price,
                'cycle' => $res['data']['cycle'] ?? '',
                'update_time' => time()
            ], ['id' => $param['id']]);

            $this->update([
            	'supplier_id' => $param['supplier_id'],
                'upstream_product_id' => $param['upstream_product_id'],
                'profit_percent' => $param['profit_percent'],
                'certification' => $param['certification'],
                'update_time' => time(),
                'res_module' => $resource['data']['module'] ?? ''
            ], ['product_id' => $param['id']]);

            # 记录日志
            active_log(lang('log_admin_update_upstream_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$param['name'].'#','{description}'=>$logDescription]),'product',$param['id']);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail')];
        }

        idcsmart_cache('product:recommend:list',null);

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    # 代理推荐商品
    public function agentRecommendProduct($param)
    {
        $productGroupId = intval($param['product_group_id']);

        $ProductGroupModel = new ProductGroupModel();
        $productGroup = $ProductGroupModel->where('id',$productGroupId)
            ->where('parent_id','>',0)
            ->find();
        if (empty($productGroup)){
            return ['status'=>400,'msg'=>lang('please_select_product_group_second')];
        }

        # 从推荐代理接口获取商品详情
        $UpstreamLogic = new UpstreamLogic();
        $res = $UpstreamLogic->recommendProductDetail(['id' => $param['id']]);
        if(empty($res['data'])){
            return ['status'=>400,'msg'=>lang('recommend_product_is_not_exist')];
        }

        $exist = $this->where('upstream_product_id', $res['data']['upstream_product_id'])->find();
        if($exist){
            return ['status'=>400,'msg'=>lang('agent_product_cannot_repeat_agent')];
        }

        $resource = $UpstreamLogic->upstreamProductDownloadResource(['url' => $res['data']['url'], 'id' => $res['data']['upstream_product_id']]);
        if($resource['status']==400){
            return ['status'=>400, 'msg'=>$resource['msg']];
        }

    	$supplier = SupplierModel::where('url', $res['data']['url'])->find();
    	$this->startTrans();
        try{
        	if(empty($supplier)){
        		$supplier = SupplierModel::create([
	                'name' => $res['data']['supplier_name'],
	                'url' => $res['data']['url'],
	                'username' => $param['username'],
	                'token' => aes_password_encode($param['token']),
                    'secret' => aes_password_encode(str_replace("\r\n", "\n", $param['secret'])),
	                'contact' => '',
	                'notes' => '',
	                'create_time' => time(),
	            ]);
        	}

            $maxOrder = ProductModel::max('order');

            $price = $res['data']['price'] ?? 0;
            $price = bcdiv($price*(100+$param['profit_percent']), 100, 2); 

        	$product = ProductModel::create([
                'name' => $param['name'],
                'order' => $maxOrder+1,
                'product_group_id' => $productGroupId,
                'description' => $param['description'] ?? '',
                'pay_type' => $res['data']['pay_type'] ?? 'recurring_prepayment',
                'auto_setup' => $param['auto_setup'],
                'price' => $price,
                'cycle' => $res['data']['cycle'] ?? '',
                'create_time' => time()
            ]);

            $this->create([
            	'supplier_id' => $supplier->id,
                'product_id' => $product->id,
                'upstream_product_id' => $res['data']['upstream_product_id'],
                'profit_percent' => $param['profit_percent'],
                'certification' => $param['certification'],
                'create_time' => time(),
                'res_module' => $resource['data']['module'] ?? ''
            ]);

            # 记录日志
            active_log(lang('log_admin_agent_upstream_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$param['name'].'#']),'product',$product->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('create_fail')];
        }

        return ['status'=>200,'msg'=>lang('create_success')];
    }

    # 删除商品
    public function afterProductDelete($id)
    {
        $this->where('product_id', $id)->delete();

        return true;
    }
}