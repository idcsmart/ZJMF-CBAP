<?php
# 编辑商品后实现钩子
add_hook('after_product_edit',function ($param){
    $productId= $param['id']??0;
    $ProductModel = new \app\common\model\ProductModel();
    # 通用模块接口
    $product = $ProductModel->alias('p')
        ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id AND s.module=\'idcsmart_common\'')
        ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
        ->leftjoin('server ss','ss.server_group_id=sg.id AND ss.module=\'idcsmart_common\'')
        ->where('p.id',$productId)
        ->find();
    if (!empty($product)){
        $IdcsmartCommonProductModel = new \server\idcsmart_common\model\IdcsmartCommonProductModel();
        return $IdcsmartCommonProductModel->updateProductMinPrice($productId);
    }

    return true;
});

# 删除商品时实现钩子
add_hook('after_product_delete', function($param){
    $IdcsmartCommonProductModel = new \server\idcsmart_common\model\IdcsmartCommonProductModel();
    return $IdcsmartCommonProductModel->deleteProduct($param);
});

# 删除产品时实现钩子
add_hook('after_host_delete', function($param){
    $IdcsmartCommonHostConfigoptionModel = new \server\idcsmart_common\model\IdcsmartCommonHostConfigoptionModel();
    return $IdcsmartCommonHostConfigoptionModel->deleteHost($param);
});