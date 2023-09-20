<?php
# 编辑商品后实现钩子
add_hook('after_product_edit',function ($param){
    $productId= $param['id']??0;
    $ProductModel = new \app\common\model\ProductModel();
    # 通用模块接口
    $product = $ProductModel->alias('p')
        ->field('p.id,s.module,ss.module as module2')
        ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id AND s.module=\'idcsmart_common\'')
        ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
        ->leftjoin('server ss','ss.server_group_id=sg.id AND ss.module=\'idcsmart_common\'')
        ->where('p.id',$productId)
        ->find();
    if (!empty($product) && ($product['module']=='idcsmart_common' || $product['module2']=='idcsmart_common')){
        $IdcsmartCommonProductModel = new \server\idcsmart_common\model\IdcsmartCommonProductModel();
        return $IdcsmartCommonProductModel->updateProductMinPrice($productId);
    }

    return true;
});

# 删除商品时实现钩子
add_hook('after_product_delete', function($param){
    $productId = $param['id']??0;
    $ProductModel = new \app\common\model\ProductModel();
    # 通用模块接口
    $product = $ProductModel->alias('p')
        ->field('p.id,s.module,ss.module as module2')
        ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id AND s.module=\'idcsmart_common\'')
        ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
        ->leftjoin('server ss','ss.server_group_id=sg.id AND ss.module=\'idcsmart_common\'')
        ->where('p.id',$productId)
        ->find();
    if (!empty($product) && ($product['module']=='idcsmart_common' || $product['module2']=='idcsmart_common')){
        $IdcsmartCommonProductModel = new \server\idcsmart_common\model\IdcsmartCommonProductModel();
        return $IdcsmartCommonProductModel->deleteProduct($param);
    }

    return true;
});

# 删除产品时实现钩子
add_hook('after_host_delete', function($param){
    $hostId = $param['id']??0;
    $HostModel = new \app\common\model\HostModel();
    $ProductModel = new \app\common\model\ProductModel();
    $host = $HostModel->find($hostId);
    $productId = $host['product_id'];
    # 通用模块接口
    $product = $ProductModel->alias('p')
        ->field('p.id,s.module,ss.module as module2')
        ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id AND s.module=\'idcsmart_common\'')
        ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
        ->leftjoin('server ss','ss.server_group_id=sg.id AND ss.module=\'idcsmart_common\'')
        ->where('p.id',$productId)
        ->find();
    if (!empty($product) && ($product['module']=='idcsmart_common' || $product['module2']=='idcsmart_common')){
        $IdcsmartCommonHostConfigoptionModel = new \server\idcsmart_common\model\IdcsmartCommonHostConfigoptionModel();
        return $IdcsmartCommonHostConfigoptionModel->deleteHost($param);
    }

    return true;
});

# 产品详情自定义字段
add_hook('product_detail_custom_fields',function ($param){
    $productId= $param['id']??0;

    $IdcsmartCommonProductConfigoptionModel = new \server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel();
    $ProductModel = new \app\common\model\ProductModel();
    $product = $ProductModel->alias('p')
        ->field('p.id,s.module,ss.module as module2')
        ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id AND s.module=\'idcsmart_common\'')
        ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
        ->leftjoin('server ss','ss.server_group_id=sg.id AND ss.module=\'idcsmart_common\'')
        ->where('p.id',$productId)
        ->find();
    if (!empty($product) && ($product['module']=='idcsmart_common' || $product['module2']=='idcsmart_common')){
        $sonCount = $IdcsmartCommonProductConfigoptionModel->where('product_id',$productId)
            ->where('son_product_id','>',0)
            ->count();
        if ($sonCount>0){
            $flag=true;
        }else{
            $flag=false;
        }

        return ['is_link'=>$flag];
    }
    return false;
});

# 创建订单之后钩子
add_hook('after_order_create',function ($param){

    $orderId = $param['id'];
    $OrderItemModel = new \app\common\model\OrderItemModel();
    $HostModel = new \app\common\model\HostModel();
    $UpgradeModel = new \app\common\model\UpgradeModel();
    $ProductModel = new \app\common\model\ProductModel();
    $IdcsmartCommonHostConfigoptionModel = new \server\idcsmart_common\model\IdcsmartCommonHostConfigoptionModel();
    $IdcsmartCommonProductConfigoptionModel = new \server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel();
    $IdcsmartCommonProductConfigoptionSubModel = new \server\idcsmart_common\model\IdcsmartCommonProductConfigoptionSubModel();
    $IdcsmartCommonLogic = new \server\idcsmart_common\logic\IdcsmartCommonLogic();

    $upgrade = $UpgradeModel->where('order_id',$orderId)
        ->whereIn('status',['Unpaid','Pending'])
        ->order('id','desc')
        ->find();
    if(is_null($upgrade) || empty($upgrade)){
        return false;
    }
    $configoptionHost = $HostModel->find($upgrade['host_id']);
    if(is_null($configoptionHost) || empty($configoptionHost)){
        return false;
    }
    $product = $ProductModel->alias('p')
        ->field('p.id,p.name,s.module,ss.module as module2')
        ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id AND s.module=\'idcsmart_common\'')
        ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
        ->leftjoin('server ss','ss.server_group_id=sg.id AND ss.module=\'idcsmart_common\'')
        ->where('p.id',$configoptionHost['product_id'])
        ->find();
    if (!empty($product) && ($product['module']=='idcsmart_common' || $product['module2']=='idcsmart_common')){
        if (!empty($upgrade)){
            $new = json_decode($upgrade['data'],true);
            if ($upgrade['type']=='config_option'){ // 更改配置升降级订单子项描述
                $configoptionOrderItems = $OrderItemModel->where('order_id',$orderId)
                    ->where('type','upgrade')
                    ->select();
                foreach ($configoptionOrderItems as $configoptionOrderItem){
                    $olds = $IdcsmartCommonHostConfigoptionModel->where('host_id',$configoptionOrderItem['host_id'])
                        ->select()->toArray();
                    $description= [];
                    foreach ($olds as $key=>$old){ # 考虑到配置项只有两个，且只有数量
                        $configoption = $IdcsmartCommonProductConfigoptionModel->find($old['configoption_id']);
                        if ($IdcsmartCommonLogic->checkQuantity($configoption['option_type'])){
                            if (isset($new['configoption'][$old['configoption_id']]) && !empty($new['configoption'][$old['configoption_id']])){
                                foreach ($new['configoption'][$old['configoption_id']] as $temp){
                                    $description[] = $configoption['option_name'] . ':' . $old['qty'] . '=>' . $temp;
                                }
                            }
                            //$description[] = $configoption['option_name'] . ':' . $old['qty'] . '=>' . ($new['configoption'][$old['configoption_id']][0]??0);
                        }elseif($IdcsmartCommonLogic->checkMultiSelect($configoption['option_type'])){
                            $oldSub = $IdcsmartCommonProductConfigoptionSubModel->find($old['configoption_sub_id']);
                            $newSub = $IdcsmartCommonProductConfigoptionSubModel->find($new['configoption'][$old['configoption_id']][0]??0);
                            $description[] = $configoption['option_name'] . ':' . $oldSub['option_name'] . '=>' . ($newSub['option_name']??'');
                        }else{
                            $oldSub = $IdcsmartCommonProductConfigoptionSubModel->find($old['configoption_sub_id']);
                            $newSub = $IdcsmartCommonProductConfigoptionSubModel->find($new['configoption'][$old['configoption_id']]??0);
                            $description[] = $configoption['option_name'] . ':' . $oldSub['option_name'] . '=>' . ($newSub['option_name']??'');
                        }
                    }
                    $description = implode("\n",$description);
                    $configoptionOrderItem->save([
                        'description' => $product['name'] . '升降级配置：'.$description
                    ]);
                }

            }
            elseif($upgrade['type']=='product'){ // 产品升降级描述
                $productOrderItems = $OrderItemModel->where('order_id',$orderId)
                    ->where('type','upgrade')
                    ->select();
                if (!empty($productOrderItems)){
                    foreach ($productOrderItems as $productOrderItem){
                        $productUpgradeHost = $HostModel->find($productOrderItem['host_id']);
                        $oldProduct = $ProductModel->find($productUpgradeHost['product_id']);
                        $newProduct = $ProductModel->find($upgrade['rel_id']);
                        $subDescription = '';
                        if (!empty($new['configoption'])){
                            foreach ($new['configoption'] as $k=>$item){
                                $productUpgradeConfigoption = $IdcsmartCommonProductConfigoptionModel->find($k);
                                if ($IdcsmartCommonLogic->checkQuantity($productUpgradeConfigoption['option_type'])){
                                    $subDescription .= $productUpgradeConfigoption['option_name'] . ":" . $item[0] . ';';
                                }
                            }
                        }
                        $description = '升级/降级产品：' . ($oldProduct['name']??'') . '=>' . ($newProduct['name']??'') . '(' . $subDescription . ')';
                        $productOrderItem->save([
                            'description' => $description
                        ]);
                    }
                }

            }
        }

        // 原产品可能存在优惠码，更改upgrade表金额
        $hookResults = hook('apply_promo_code',['host_id'=>$upgrade['host_id'],'price'=>$upgrade['price'],'scene'=>'upgrade','duration'=>$upgrade['billing_cycle_time']]);
        foreach ($hookResults as $hookResult){
            if ($hookResult['status']==200){
                $upgradePrice = bcsub($upgrade['price'],$hookResult['data']['discount']??0,2);
                $upgrade->save([
                    'price' => $upgradePrice
                ]);
            }
        }

        $hookResults2 = hook('apply_promo_code',['host_id'=>$upgrade['host_id'],'price'=>$upgrade['renew_price'],'scene'=>'upgrade','duration'=>$upgrade['billing_cycle_time']]);
        foreach ($hookResults2 as $hookResult2){
            if ($hookResult2['status']==200){
                $upgradeRenewPrice = bcsub($upgrade['renew_price'],$hookResult2['data']['discount']??0,2);
                $upgrade->save([
                    'renew_price' => $upgradeRenewPrice
                ]);
            }
        }

        // 通用商品子模块
        $orderItems = $OrderItemModel->alias('oi')
            ->field('oi.host_id,cp.type,cp.rel_id')
            ->leftJoin('host h','h.id=oi.host_id')
            ->leftJoin('module_idcsmart_common_product cp','cp.product_id=h.product_id')
            ->where('oi.order_id',$orderId)
            ->where('oi.type','host')
            ->select()->toArray();
        $IdcsmartCommonServerHostLinkModel = new \server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel();
        $insert = [];
        foreach ($orderItems as $orderItem){
            if ($orderItem['type']=='server' && !empty($orderItem['rel_id'])){
                $temp = [
                    'host_id' => $orderItem['host_id'],
                    'server_id' => $orderItem['rel_id']
                ];
                $insert[] = $temp;
            }
        }
        $IdcsmartCommonServerHostLinkModel->insertAll($insert);

    }


    return true;
});
