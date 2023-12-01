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

//商品复制后
add_hook('after_product_copy', function($param){
    try{
        $param['son_product_id'] = $param['son_product_id'] ?? [];

        $IdcsmartCommonProductModel = new \server\idcsmart_common\model\IdcsmartCommonProductModel();
        $idcsmartCommonProduct = $IdcsmartCommonProductModel->where('product_id', $param['product_id'])->select()->toArray();
        if(!empty($idcsmartCommonProduct)){
            $idcsmartCommonProductIdArr = [];
            foreach ($idcsmartCommonProduct as $key => $value) {
                $id = $value['id'];
                $idcsmartCommonProductIdArr[$id] = 0;
                unset($value['id']);
                $value['product_id'] = $param['id'];
                $r = $IdcsmartCommonProductModel->create($value);
                $idcsmartCommonProductIdArr[$id] = $r->id;
            }

            $IdcsmartCommonProductCustomFieldModel = new \server\idcsmart_common\model\IdcsmartCommonProductCustomFieldModel();
            $idcsmartCommonProductCustomField = $IdcsmartCommonProductCustomFieldModel->where('product_id', $param['product_id'])->select()->toArray();
            $idcsmartCommonProductCustomFieldIdArr = [];
            foreach ($idcsmartCommonProductCustomField as $key => $value) {
                $id = $value['id'];
                $idcsmartCommonProductCustomFieldIdArr[$id] = 0;
                unset($value['id']);
                $value['product_id'] = $param['id'];
                $r = $IdcsmartCommonProductCustomFieldModel->create($value);
                $idcsmartCommonProductCustomFieldIdArr[$id] = $r->id;
            }

            $IdcsmartCommonProductConfigoptionModel = new \server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel();
            $idcsmartCommonProductConfigoption = $IdcsmartCommonProductConfigoptionModel->where('product_id', $param['product_id'])->where('configoption_id', 0)->select()->toArray();
            $idcsmartCommonProductConfigoptionIdArr = [];
            foreach ($idcsmartCommonProductConfigoption as $key => $value) {
                $id = $value['id'];
                $idcsmartCommonProductConfigoptionIdArr[$id] = 0;
                unset($value['id']);
                $value['product_id'] = $param['id'];
                $r = $IdcsmartCommonProductConfigoptionModel->create($value);
                $idcsmartCommonProductConfigoptionIdArr[$id] = $r->id;
            }

            $IdcsmartCommonProductConfigoptionModel = new \server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel();
            $idcsmartCommonProductConfigoption = $IdcsmartCommonProductConfigoptionModel->where('product_id', $param['product_id'])->where('configoption_id', '>', 0)->select()->toArray();
            foreach ($idcsmartCommonProductConfigoption as $key => $value) {
                $id = $value['id'];
                $idcsmartCommonProductConfigoptionIdArr[$id] = 0;
                unset($value['id']);
                $value['product_id'] = $param['id'];
                $value['son_product_id'] = $param['son_product_id'][$value['son_product_id']] ?? 0;
                $value['configoption_id'] = $idcsmartCommonProductConfigoptionIdArr[$value['configoption_id']];
                $r = $IdcsmartCommonProductConfigoptionModel->create($value);
                $idcsmartCommonProductConfigoptionIdArr[$id] = $r->id;
            }

            $IdcsmartCommonProductConfigoptionSubModel = new \server\idcsmart_common\model\IdcsmartCommonProductConfigoptionSubModel();
            $idcsmartCommonProductConfigoptionSub = $IdcsmartCommonProductConfigoptionSubModel->whereIn('product_configoption_id', array_keys($idcsmartCommonProductConfigoptionIdArr))->select()->toArray();
            $idcsmartCommonProductConfigoptionSubIdArr = [];
            foreach ($idcsmartCommonProductConfigoptionSub as $key => $value) {
                $id = $value['id'];
                $idcsmartCommonProductConfigoptionSubIdArr[$id] = 0;
                unset($value['id']);
                $value['product_configoption_id'] = $idcsmartCommonProductConfigoptionIdArr[$value['product_configoption_id']] ?? 0;
                $r = $IdcsmartCommonProductConfigoptionSubModel->create($value);
                $idcsmartCommonProductConfigoptionSubIdArr[$id] = $r->id;
            }

            $IdcsmartCommonPricingModel = new \server\idcsmart_common\model\IdcsmartCommonPricingModel();
            $idcsmartCommonPricing = $IdcsmartCommonPricingModel->select()->toArray();
            $idcsmartCommonPricingIdArr = [];
            foreach ($idcsmartCommonPricing as $key => $value) {
                $id = $value['id'];
                if($value['type']=='product' && $value['rel_id']==$param['product_id']){
                    $idcsmartCommonPricingIdArr[$id] = 0;
                    unset($value['id']);
                    $value['rel_id'] = $param['id'];
                    $r = $IdcsmartCommonPricingModel->create($value);
                    $idcsmartCommonPricingIdArr[$id] = $r->id;
                }else if($value['type']=='configoption' && isset($idcsmartCommonProductConfigoptionSubIdArr[$value['rel_id']])){
                    $idcsmartCommonPricingIdArr[$id] = 0;
                    unset($value['id']);
                    $value['rel_id'] = $idcsmartCommonProductConfigoptionSubIdArr[$value['rel_id']] ?? 0;
                    $r = $IdcsmartCommonPricingModel->create($value);
                    $idcsmartCommonPricingIdArr[$id] = $r->id;
                }
            }

            $IdcsmartCommonCustomCycleModel = new \server\idcsmart_common\model\IdcsmartCommonCustomCycleModel();
            $idcsmartCommonCustomCycle = $IdcsmartCommonCustomCycleModel->where('product_id', $param['product_id'])->select()->toArray();
            $idcsmartCommonCustomCycleIdArr = [];
            foreach ($idcsmartCommonCustomCycle as $key => $value) {
                $id = $value['id'];
                $idcsmartCommonCustomCycleIdArr[$id] = 0;
                unset($value['id']);
                $value['product_id'] = $param['id'];
                $r = $IdcsmartCommonCustomCycleModel->create($value);
                $idcsmartCommonCustomCycleIdArr[$id] = $r->id;
            }

            $IdcsmartCommonCustomCyclePricingModel = new \server\idcsmart_common\model\IdcsmartCommonCustomCyclePricingModel();
            $idcsmartCommonCustomCyclePricing = $IdcsmartCommonCustomCyclePricingModel->whereIn('custom_cycle_id', array_keys($idcsmartCommonCustomCycleIdArr))->select()->toArray();
            $idcsmartCommonCustomCyclePricingIdArr = [];
            foreach ($idcsmartCommonCustomCyclePricing as $key => $value) {
                $id = $value['id'];
                if($value['type']=='product' && $value['rel_id']==$param['product_id']){
                    $idcsmartCommonCustomCyclePricingIdArr[$id] = 0;
                    unset($value['id']);
                    $value['custom_cycle_id'] = $idcsmartCommonCustomCycleIdArr[$value['custom_cycle_id']] ?? 0;
                    $value['rel_id'] = $param['id'];
                    $r = $IdcsmartCommonCustomCyclePricingModel->create($value);
                    $idcsmartCommonCustomCyclePricingIdArr[$id] = $r->id;
                }else if($value['type']=='configoption' && isset($idcsmartCommonProductConfigoptionSubIdArr[$value['rel_id']])){
                    $idcsmartCommonCustomCyclePricingIdArr[$id] = 0;
                    unset($value['id']);
                    $value['custom_cycle_id'] = $idcsmartCommonCustomCycleIdArr[$value['custom_cycle_id']] ?? 0;
                    $value['rel_id'] = $idcsmartCommonProductConfigoptionSubIdArr[$value['rel_id']] ?? 0;
                    $r = $IdcsmartCommonCustomCyclePricingModel->create($value);
                    $idcsmartCommonCustomCyclePricingIdArr[$id] = $r->id;
                }
            }
        }

        //子商品复制
        foreach ($param['son_product_id'] as $k => $v) {
            $IdcsmartCommonProductModel = new \server\idcsmart_common\model\IdcsmartCommonProductModel();
            $idcsmartCommonProduct = $IdcsmartCommonProductModel->where('product_id', $k)->select()->toArray();
            if(!empty($idcsmartCommonProduct)){
                $idcsmartCommonProductIdArr = [];
                foreach ($idcsmartCommonProduct as $key => $value) {
                    $id = $value['id'];
                    $idcsmartCommonProductIdArr[$id] = 0;
                    unset($value['id']);
                    $value['product_id'] = $v;
                    $r = $IdcsmartCommonProductModel->create($value);
                    $idcsmartCommonProductIdArr[$id] = $r->id;
                }

                $IdcsmartCommonProductCustomFieldModel = new \server\idcsmart_common\model\IdcsmartCommonProductCustomFieldModel();
                $idcsmartCommonProductCustomField = $IdcsmartCommonProductCustomFieldModel->where('product_id', $k)->select()->toArray();
                $idcsmartCommonProductCustomFieldIdArr = [];
                foreach ($idcsmartCommonProductCustomField as $key => $value) {
                    $id = $value['id'];
                    $idcsmartCommonProductCustomFieldIdArr[$id] = 0;
                    unset($value['id']);
                    $value['product_id'] = $v;
                    $r = $IdcsmartCommonProductCustomFieldModel->create($value);
                    $idcsmartCommonProductCustomFieldIdArr[$id] = $r->id;
                }

                $IdcsmartCommonProductConfigoptionModel = new \server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel();
                $idcsmartCommonProductConfigoption = $IdcsmartCommonProductConfigoptionModel->where('product_id', $k)->where('configoption_id', 0)->select()->toArray();
                $idcsmartCommonProductConfigoptionIdArr = [];
                foreach ($idcsmartCommonProductConfigoption as $key => $value) {
                    $id = $value['id'];
                    $idcsmartCommonProductConfigoptionIdArr[$id] = 0;
                    unset($value['id']);
                    $value['product_id'] = $v;
                    $r = $IdcsmartCommonProductConfigoptionModel->create($value);
                    $idcsmartCommonProductConfigoptionIdArr[$id] = $r->id;
                }

                $IdcsmartCommonProductConfigoptionModel = new \server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel();
                $idcsmartCommonProductConfigoption = $IdcsmartCommonProductConfigoptionModel->where('product_id', $k)->where('configoption_id', '>', 0)->select()->toArray();
                foreach ($idcsmartCommonProductConfigoption as $key => $value) {
                    $id = $value['id'];
                    $idcsmartCommonProductConfigoptionIdArr[$id] = 0;
                    unset($value['id']);
                    $value['product_id'] = $v;
                    $value['configoption_id'] = $idcsmartCommonProductConfigoptionIdArr[$value['configoption_id']];
                    $r = $IdcsmartCommonProductConfigoptionModel->create($value);
                    $idcsmartCommonProductConfigoptionIdArr[$id] = $r->id;
                }

                $IdcsmartCommonProductConfigoptionSubModel = new \server\idcsmart_common\model\IdcsmartCommonProductConfigoptionSubModel();
                $idcsmartCommonProductConfigoptionSub = $IdcsmartCommonProductConfigoptionSubModel->whereIn('product_configoption_id', array_keys($idcsmartCommonProductConfigoptionIdArr))->select()->toArray();
                $idcsmartCommonProductConfigoptionSubIdArr = [];
                foreach ($idcsmartCommonProductConfigoptionSub as $key => $value) {
                    $id = $value['id'];
                    $idcsmartCommonProductConfigoptionSubIdArr[$id] = 0;
                    unset($value['id']);
                    $value['product_id'] = $v;
                    $value['product_configoption_id'] = $idcsmartCommonProductConfigoptionIdArr[$value['product_configoption_id']] ?? 0;
                    $r = $IdcsmartCommonProductConfigoptionSubModel->create($value);
                    $idcsmartCommonProductConfigoptionSubIdArr[$id] = $r->id;
                }

                $IdcsmartCommonPricingModel = new \server\idcsmart_common\model\IdcsmartCommonPricingModel();
                $idcsmartCommonPricing = $IdcsmartCommonPricingModel->select()->toArray();
                $idcsmartCommonPricingIdArr = [];
                foreach ($idcsmartCommonPricing as $key => $value) {
                    $id = $value['id'];
                    if($value['type']=='product' && $value['rel_id']==$k){
                        $idcsmartCommonPricingIdArr[$id] = 0;
                        unset($value['id']);
                        $value['rel_id'] = $v;
                        $r = $IdcsmartCommonPricingModel->create($value);
                        $idcsmartCommonPricingIdArr[$id] = $r->id;
                    }else if($value['type']=='configoption' && isset($idcsmartCommonProductConfigoptionSubIdArr[$value['rel_id']])){
                        $idcsmartCommonPricingIdArr[$id] = 0;
                        unset($value['id']);
                        $value['rel_id'] = $idcsmartCommonProductConfigoptionSubIdArr[$value['rel_id']] ?? 0;
                        $r = $IdcsmartCommonPricingModel->create($value);
                        $idcsmartCommonPricingIdArr[$id] = $r->id;
                    }
                }

                $IdcsmartCommonCustomCycleModel = new \server\idcsmart_common\model\IdcsmartCommonCustomCycleModel();
                $idcsmartCommonCustomCycle = $IdcsmartCommonCustomCycleModel->where('product_id', $k)->select()->toArray();
                $idcsmartCommonCustomCycleIdArr = [];
                foreach ($idcsmartCommonCustomCycle as $key => $value) {
                    $id = $value['id'];
                    $idcsmartCommonCustomCycleIdArr[$id] = 0;
                    unset($value['id']);
                    $value['product_id'] = $v;
                    $r = $IdcsmartCommonCustomCycleModel->create($value);
                    $idcsmartCommonCustomCycleIdArr[$id] = $r->id;
                }

                $IdcsmartCommonCustomCyclePricingModel = new \server\idcsmart_common\model\IdcsmartCommonCustomCyclePricingModel();
                $idcsmartCommonCustomCyclePricing = $IdcsmartCommonCustomCyclePricingModel->whereIn('custom_cycle_id', array_keys($idcsmartCommonCustomCycleIdArr))->select()->toArray();
                $idcsmartCommonCustomCyclePricingIdArr = [];
                foreach ($idcsmartCommonCustomCyclePricing as $key => $value) {
                    $id = $value['id'];
                    if($value['type']=='product' && $value['rel_id']==$k){
                        $idcsmartCommonCustomCyclePricingIdArr[$id] = 0;
                        unset($value['id']);
                        $value['custom_cycle_id'] = $idcsmartCommonCustomCycleIdArr[$value['custom_cycle_id']] ?? 0;
                        $value['rel_id'] = $v;
                        $r = $IdcsmartCommonCustomCyclePricingModel->create($value);
                        $idcsmartCommonCustomCyclePricingIdArr[$id] = $r->id;
                    }else if($value['type']=='configoption' && isset($idcsmartCommonProductConfigoptionSubIdArr[$value['rel_id']])){
                        $idcsmartCommonCustomCyclePricingIdArr[$id] = 0;
                        unset($value['id']);
                        $value['custom_cycle_id'] = $idcsmartCommonCustomCycleIdArr[$value['custom_cycle_id']] ?? 0;
                        $value['rel_id'] = $idcsmartCommonProductConfigoptionSubIdArr[$value['rel_id']] ?? 0;
                        $r = $IdcsmartCommonCustomCyclePricingModel->create($value);
                        $idcsmartCommonCustomCyclePricingIdArr[$id] = $r->id;
                    }
                }
            }
        }

    }catch(\Exception $e){
        return $e->getMessage();
    }
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

                    if(!empty(get_client_id())){
                        $defaultLang = get_client_lang();
                    }else{
                        $defaultLang = get_system_lang(true);
                    }

                    if ($defaultLang=="zh-cn"){
                        $de = " 升降级配置：";
                    }elseif ($defaultLang="en-us"){
                        $de = " Upgrade config option:";
                    }elseif ($defaultLang="zh-hk"){
                        $de = " 升降级配置：";
                    }

                    $description = implode("\n",$description);
                    $configoptionOrderItem->save([
                        'description' => $product['name'] . $de.$description
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
                        if(!empty(get_client_id())){
                            $defaultLang = get_client_lang();
                        }else{
                            $defaultLang = get_system_lang(true);
                        }

                        if ($defaultLang=="zh-cn"){
                            $de = " 升降级产品：";
                        }elseif ($defaultLang="en-us"){
                            $de = " Upgrade product:";
                        }elseif ($defaultLang="zh-hk"){
                            $de = " 升降级产品：";
                        }
                        $description = $de . ($oldProduct['name']??'') . '=>' . ($newProduct['name']??'') . '(' . $subDescription . ')';
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

    }


    return true;
});

// 5分钟定时任务
add_hook("five_minute_cron",function ($param){
    $ProvisionLogic = new \server\idcsmart_common\logic\ProvisionLogic();
    $ProvisionLogic->fiveMinuteCron();
});

// 每日定时任务
add_hook("daily_cron",function ($param){
    $ProvisionLogic = new \server\idcsmart_common\logic\ProvisionLogic();
    $ProvisionLogic->dailyCron();
});