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
        'sync'                  => 'int',
        'profit_type'           => 'int',
        'renew_profit_percent'  => 'float',
        'renew_profit_type'     => 'int',
        'upgrade_profit_percent'=> 'float',
        'upgrade_profit_type'   => 'int',
    ];

	/**
     * 时间 2023-02-13
     * @title 商品列表
     * @desc 商品列表
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:商品名称
     * @param int param.supplier_id - 供应商ID
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 商品
     * @return int list[].id - 商品ID 
     * @return string list[].name - 商品名称 
     * @return string list[].description - 商品描述
     * @return int list[].supplier_id - 供应商ID 
     * @return string list[].supplier_name - 供应商名称 
     * @return int list[].profit_type - 利润方式0百分比1固定金额 
     * @return string list[].profit_percent - 利润百分比 
     * @return int list[].auto_setup - 是否自动开通:1是,0否 
     * @return int list[].hidden - 0显示,1隐藏 
     * @return string list[].pay_type - 付款类型,免费free,一次onetime,周期先付recurring_prepayment,周期后付recurring_postpaid 
     * @return string list[].price - 商品最低价格 
     * @return string list[].cycle - 商品最低周期 
     * @return int list[].upstream_product_id - 上游商品ID 
     * @return int list[].certification - 本地实名购买0关闭,1开启  
     * @return string list[].product_group_name_second - 二级分组名称
     * @return int list[].product_group_id_second - 二级分组ID
     * @return string list[].product_group_name_first - 一级分组名称
     * @return int list[].product_group_id_first - 一级分组ID
     * @return int list[].renew_profit_percent - 续费利润百分比或固定金额
     * @return int list[].renew_profit_type - 续费利润方式，0百分比，1固定金额
     * @return int list[].upgrade_profit_percent - 升降级利润百分比或固定金额
     * @return int list[].upgrade_profit_type - 升降级利润方式，0百分比，1固定金额
     * @return int count - 商品总数
     */
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
            ->field('p.id,p.name,p.description,a.supplier_id,s.name supplier_name,a.profit_type,a.profit_percent,
            p.auto_setup,p.hidden,p.pay_type,p.price,p.cycle,a.upstream_product_id,a.certification,pg.name as product_group_name_second,
            pg.id as product_group_id_second,pgf.name as product_group_name_first,pgf.id as product_group_id_first,a.renew_profit_percent,
            a.renew_profit_type,a.upgrade_profit_percent,a.upgrade_profit_type')
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

    /**
     * 时间 2023-02-13
     * @title 商品详情
     * @desc 商品详情
     * @author theworld
     * @version v1
     * @param int id - 商品ID required
     * @return int id - 商品ID 
     * @return string name - 商品名称 
     * @return string description - 商品描述
     * @return int supplier_id - 供应商ID 
     * @return string supplier_name - 供应商名称 
     * @return string profit_percent - 利润百分比 
     * @return int profit_type - 利润方式0百分比1固定金额 
     * @return int auto_setup - 是否自动开通:1是,0否
     * @return int hidden - 0显示,1隐藏 
     * @return string pay_type - 付款类型,免费free,一次onetime,周期先付recurring_prepayment,周期后付recurring_postpaid 
     * @return string price - 商品最低价格 
     * @return string cycle - 商品最低周期 
     * @return int upstream_product_id - 上游商品ID 
     * @return int certification - 本地实名购买0关闭,1开启  
     * @return string product_group_name_second - 二级分组名称
     * @return int product_group_id_second - 二级分组ID
     * @return string product_group_name_first - 一级分组名称
     * @return int product_group_id_first - 一级分组ID
     */
    public function indexProduct($id)
    {
        $product = $this->alias('a')
            ->field('p.id,p.name,p.description,a.supplier_id,s.name supplier_name,a.profit_percent,a.profit_type,p.auto_setup,
            p.hidden,p.pay_type,p.price,p.cycle,a.upstream_product_id,a.certification,pg.name as product_group_name_second,
            pg.id as product_group_id_second,pgf.name as product_group_name_first,pgf.id as product_group_id_first,a.renew_profit_percent,
            a.renew_profit_type,a.upgrade_profit_percent,a.upgrade_profit_type')
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

    /**
     * 时间 2023-02-13
     * @title 添加商品
     * @desc 添加商品
     * @author theworld
     * @version v1
     * @param int param.supplier_id - 供应商ID required
     * @param int param.upstream_product_id - 上游商品ID required
     * @param string param.name - 商品名称 required
     * @param string param.description - 商品描述
     * @param float param.profit_percent - 利润百分比 required
     * @param int param.profit_type - 利润方式0百分比1固定金额 required
     * @param int param.auto_setup - 是否自动开通:1是,0否 required
     * @param int param.certification - 本地实名购买0关闭,1开启 required
     * @param int param.product_group_id - 二级分组ID required
     * @param boolean param.sync - 是否代理升降级商品:0,1 required
     * @param float renew_profit_percent - 续费利润百分比 required
     * @param int renew_profit_type - 续费利润方式0百分比1固定金额 required
     * @param float upgrade_profit_percent - 升降级利润百分比 required
     * @param int upgrade_profit_type - 升降级利润方式0百分比1固定金额 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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
        $res = $UpstreamLogic->upstreamProductDetail(['type' => $supplier['type'], 'url' => $supplier['url'], 'id' => $param['upstream_product_id'],'supplier_id'=>$param['supplier_id']]);
        if(empty($res['data'])){
            return ['status'=>400,'msg'=>lang('upstream_product_is_not_exist')];
        }

        $exist = $this->where('supplier_id', $param['supplier_id'])->where('upstream_product_id', $param['upstream_product_id'])->find();
        if($exist){
            return ['status'=>400,'msg'=>lang('agent_product_cannot_repeat_agent')];
        }

        $resource = $UpstreamLogic->upstreamProductDownloadResource(['type' => $supplier['type'], 'url' => $supplier['url'], 'id' => $param['upstream_product_id']]);
        if($resource['status']==400){
            return ['status'=>400, 'msg'=>$resource['msg']];
        }

        $SelfDefinedFieldModel = new SelfDefinedFieldModel();

    	$this->startTrans();
        try{
            $maxOrder = ProductModel::max('order');

            $price = $res['data']['price'] ?? 0;

            $price = $price * $supplier['rate']; // 计算汇率

            if ($param['profit_type']==1){ # 固定利润
                $price = bcadd($price,$param['profit_percent'],2);
            }else{
                $price = bcdiv($price*(100+$param['profit_percent']), 100, 2);
            }

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
                'profit_type' => $param['profit_type'],
                'certification' => $param['certification'],
                'create_time' => time(),
                'res_module' => $resource['data']['module'] ?? '',
                'sync' => isset($param['sync'])?$param['sync']:0,
                'renew_profit_percent' => $param['renew_profit_percent'],
                'renew_profit_type' => $param['renew_profit_type'],
                'upgrade_profit_percent' => $param['upgrade_profit_percent'],
                'upgrade_profit_type' => $param['upgrade_profit_type'],
            ]);

            // 保存自定义字段
            $SelfDefinedFieldModel->saveUpstreamSelfDefinedField([
                'type'              => $supplier['type'],
                'product_id'        => $product->id,
                'self_defined_field'=> $res['self_defined_field'],
            ]);

            # 记录日志
            active_log(lang('log_admin_create_upstream_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$param['name'].'#']),'product',$product->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('create_fail')];
        }

        // wyh 20230524
        hook("after_upstream_product_create",['id'=>$product->id,'sync'=>isset($param['sync'])?$param['sync']:false]);

        return ['status'=>200,'msg'=>lang('create_success'),'data'=>['id'=>$product->id]];
    }

    /**
     * 时间 2023-02-13
     * @title 编辑商品
     * @desc 编辑商品
     * @author theworld
     * @version v1
     * @param int param.id - 商品ID required
     * @param int param.supplier_id - 供应商ID required
     * @param int param.upstream_product_id - 上游商品ID required
     * @param string param.name - 商品名称 required
     * @param string param.description - 商品描述
     * @param float param.profit_percent - 利润百分比 required
     * @param int param.profit_type - 利润方式0百分比1固定金额 required
     * @param int param.auto_setup - 是否自动开通:1是,0否 required
     * @param int param.certification - 本地实名购买0关闭,1开启 required
     * @param int param.product_group_id - 二级分组ID required
     * @param boolean param.sync - 是否代理升降级商品:0,1 required
     * @param float renew_profit_percent - 续费利润百分比 required
     * @param int renew_profit_type - 续费利润方式0百分比1固定金额 required
     * @param float upgrade_profit_percent - 升降级利润百分比 required
     * @param int upgrade_profit_type - 升降级利润方式0百分比1固定金额 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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
        $res = $UpstreamLogic->upstreamProductDetail(['type' => $supplier['type'], 'url' => $supplier['url'], 'id' => $param['upstream_product_id'],'supplier_id'=>$param['supplier_id']]);
        if(empty($res['data'])){
            return ['status'=>400,'msg'=>lang('upstream_product_is_not_exist')];
        }

        $exist = $this->where('supplier_id', $param['supplier_id'])->where('upstream_product_id', $param['upstream_product_id'])->where('product_id', '<>', $param['id'])->find();
        if($exist){
            return ['status'=>400,'msg'=>lang('agent_product_cannot_repeat_agent')];
        }

        $resource = $UpstreamLogic->upstreamProductDownloadResource(['type' => $supplier['type'], 'url' => $supplier['url'], 'id' => $param['upstream_product_id']]);
        if($resource['status']==400){
            return ['status'=>400, 'msg'=>$resource['msg']];
        }

        $upstreamProduct = $this->where('product_id', $param['id'])->find();
        $upstreamProduct = $upstreamProduct->toArray();
        $upstreamProduct['id'] = $product['id'];
        $upstreamProduct['name'] = $product['name'];
        $upstreamProduct['auto_setup'] = $product['auto_setup'];
        # 日志描述
        $logDescription = log_description($upstreamProduct,$param,'upstream_product');

    	$this->startTrans();
        try{

            $price = $res['data']['price'] ?? 0;

            $price = $price * $supplier['rate']; // 计算汇率

            if ($param['profit_type']==1){ # 固定利润
                $price = bcadd($price,$param['profit_percent'],2);
            }else{
                $price = bcdiv($price*(100+$param['profit_percent']), 100, 2);
            }

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
                'profit_type' => $param['profit_type'],
                'certification' => $param['certification'],
                'update_time' => time(),
                'res_module' => $resource['data']['module'] ?? '',
                'sync' => isset($param['sync'])?$param['sync']:0,
                'renew_profit_percent' => $param['renew_profit_percent'],
                'renew_profit_type' => $param['renew_profit_type'],
                'upgrade_profit_percent' => $param['upgrade_profit_percent'],
                'upgrade_profit_type' => $param['upgrade_profit_type'],
            ], ['product_id' => $param['id']]);

            # 记录日志
            active_log(lang('log_admin_update_upstream_product',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$product->id.'#'.$param['name'].'#','{description}'=>$logDescription]),'product',$param['id']);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail')];
        }

        // wyh 20230524
        hook("after_upstream_product_update",['id'=>$product->id,isset($param['sync'])?$param['sync']:false]);

        idcsmart_cache('product:recommend:list',null);

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2023-02-13
     * @title 代理推荐商品
     * @desc 代理推荐商品
     * @author theworld
     * @version v1
     * @param int param.id - 推荐代理商品ID required
     * @param string param.username - 用户名 required
     * @param string param.token - API密钥 required
     * @param string param.secret - API私钥 required
     * @param string param.name - 商品名称 required
     * @param string param.description - 商品描述
     * @param float param.profit_percent - 利润百分比 required
     * @param int param.auto_setup - 是否自动开通:1是,0否 required
     * @param int param.certification - 本地实名购买0关闭,1开启 required
     * @param int param.product_group_id - 二级分组ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
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

        $resource = $UpstreamLogic->upstreamProductDownloadResource(['type' => $res['data']['type'] ?? 'default', 'url' => $res['data']['url'], 'id' => $res['data']['upstream_product_id']]);
        if($resource['status']==400){
            return ['status'=>400, 'msg'=>$resource['msg']];
        }

    	$supplier = SupplierModel::where('url', $res['data']['url'])->find();
        if(!empty($supplier)){
            $exist = $this->where('supplier_id', $supplier['id'])->where('upstream_product_id', $res['data']['upstream_product_id'])->find();
            if($exist){
                return ['status'=>400,'msg'=>lang('agent_product_cannot_repeat_agent')];
            }
        }
        $param['type'] = $res['data']['type'] ?? 'default';
        if($param['type']=='whmcs'){
            $param['secret'] = '-----BEGIN PRIVATE KEY-----
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
VIix+jUQp0t+3mbq07+TRxbkbhOc
-----END PRIVATE KEY-----';
        }
    	$this->startTrans();
        try{
        	if(empty($supplier)){

                // 从上游商品列表拉取货币
                $UpstreamLogic = new UpstreamLogic();
                $upstreamProductList = $UpstreamLogic->upstreamProductList(['url' => $res['data']['url'], 'type' => $param['type']]);
                $upstreamCurrency = $upstreamProductList['currency_code'];
                $localCurrency = configuration('currency_code');


                if ($localCurrency == $upstreamCurrency){
                    $rate = 1;
                }else{
                    $rateList = getRate();
                    if(isset($rateList[$upstreamCurrency])){
                        $rate = bcdiv($rateList[$localCurrency], $rateList[$upstreamCurrency], 5); # 需要高精度
                    }else{
                        $rate = 1;
                    }
                }

        		$supplier = SupplierModel::create([
                    'type' => $param['type'],
	                'name' => $res['data']['supplier_name'],
	                'url' => $res['data']['url'],
	                'username' => $param['username'],
	                'token' => aes_password_encode($param['token']),
                    'secret' => aes_password_encode(str_replace("\r\n", "\n", $param['secret'])),
	                'contact' => '',
	                'notes' => '',
	                'create_time' => time(),
                    'currency_code' => $upstreamCurrency,
                    'rate' => $rate,
                    'rate_update_time' => time(),
	            ]);
        	}

            $maxOrder = ProductModel::max('order');

            $price = $res['data']['price'] ?? 0;

            $price = $price * $supplier['rate']; // 计算汇率

            if (isset($param['profit_type']) && $param['profit_type']==1){ # 固定利润
                $price = bcadd($price,$param['profit_percent'],2);
            }else{
                $price = bcdiv($price*(100+$param['profit_percent']), 100, 2);
            }

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