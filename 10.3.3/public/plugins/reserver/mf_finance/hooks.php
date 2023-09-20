<?php
use reserver\mf_finance\logic\RouteLogic;

function afterUpstreamProductEditcommon($param){
    if (isset($param['sync']) && $param['sync']==false){
        return false;
    }
    $productId= $param['id']??0;
    $RouteLogic = new RouteLogic();
    $RouteLogic->routeByProduct($productId);

    $res = $RouteLogic->curl( "api/product/upgrade_product", ['id' => $RouteLogic->upstream_product_id],'GET');
    if ($res['status']!=200){
        return false;
    }

    $ProductModel = new \app\common\model\ProductModel();
    $product = $ProductModel->find($productId);

    if (!empty($product)){
        $UpstreamProductModel = new \app\common\model\UpstreamProductModel();
        $ProductUpgradeProductModel = new \app\common\model\ProductUpgradeProductModel();
        $upgradeProducts = $res['data']['list']??[];
        $links = [];
        foreach ($upgradeProducts as $upgradeProduct){
            // 对于已经导入至本地的商品，不需要导入，给出关联
            $exist =$UpstreamProductModel->where('supplier_id',$RouteLogic->supplier_id)
                ->where('upstream_product_id',$upgradeProduct['id'])
                ->find();
            if (!empty($exist)){ // 本地已存在
                // 看是否关联上
                $existlink = $ProductUpgradeProductModel->where('product_id',$productId)
                    ->where('upgrade_product_id',$exist['product_id'])
                    ->find();
                if (empty($existlink)){ // 未关联上
                    $links[] = [
                        'product_id' => $productId,
                        'upgrade_product_id' => $exist['product_id'],
                    ];
                }

            }else{
                // 套娃模式(会导入第一个商品的可升降级商品和 可升降级商品的可升降级商品 和……)
                $result = $UpstreamProductModel->createProduct([
                    'product_group_id' => $product['product_group_id'],
                    'supplier_id' => $RouteLogic->supplier_id,
                    'upstream_product_id' => $upgradeProduct['id'],
                    'name' => $upgradeProduct['name'],
                    // 以下为默认值
                    'description' => '',
                    'auto_setup' => 0,
                    'profit_percent' => $RouteLogic->getProfitPercent(),
                    'certification' => 0,
                ]);
                if ($result['status']==200){
                    $links[] = [
                        'product_id' => $productId,
                        'upgrade_product_id' => $result['data']['id']
                    ];
                }
            }
        }

        // 插入关联数据
        if (!empty($links[0])){
            $ProductUpgradeProductModel->insertAll($links);
        }

    }

    return true;
}

# 添加商品后实现钩子
add_hook('after_upstream_product_create',function ($param){
    return afterUpstreamProductEditcommon($param);
});
# 编辑商品后实现钩子
add_hook('after_upstream_product_update',function ($param){
    return afterUpstreamProductEditcommon($param);
});