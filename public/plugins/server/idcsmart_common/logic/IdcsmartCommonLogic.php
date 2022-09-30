<?php 
namespace server\idcsmart_common\logic;

# 逻辑类
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\ServerModel;
use server\idcsmart_common\model\IdcsmartCommonCustomCycleModel;
use server\idcsmart_common\model\IdcsmartCommonCustomCyclePricingModel;
use server\idcsmart_common\model\IdcsmartCommonHostConfigoptionModel;
use server\idcsmart_common\model\IdcsmartCommonPricingModel;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionSubModel;

class IdcsmartCommonLogic
{
    public $systemCycles = [
        'onetime' => '一次性',
        'monthly' => '月',
        'quarterly' => '季',
        'semaiannually' => '半年',
        'annually' => '一年',
        'biennially' => '两年',
        'triennianlly' => '三年'
    ];

    # 初始化验证
    public function validate($param)
    {
        $productId = $param['product_id']??0;

        $ProductModel = new ProductModel();

        $product = $ProductModel->find($productId);

        if (empty($product)){
            echo json_encode(['status'=>400,'msg'=>lang_plugins('product_not_found')]);die;
        }
        $ServerModel = new ServerModel();

        if ($product['type'] == 'server'){
            $server = $ServerModel->where('id',$product['rel_id'])
                ->where('module','idcsmart_common')
                ->find();
        }else{
            $server = $ServerModel->where('server_group_id',$product['rel_id'])
                ->where('module','idcsmart_common')
                ->find();
        }
        if (empty($server)){
            echo json_encode(['status'=>400,'msg'=>lang_plugins('product_not_link_idcsmart_common_module')]);die;
        }
    }

    # 配置子项初始化验证
    public function validateConfigoption($param)
    {
        $configoptionId = $param['configoption_id']??0;

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $productConfigoption = $IdcsmartCommonProductConfigoptionModel->find($configoptionId);

        if (empty($productConfigoption)){
            echo json_encode(['status'=>400,'msg'=>lang_plugins('idcsmart_common_configoption_not_exist')]);die;
        }
    }

    public function checkQuantity($option_type)
    {
        if (in_array($option_type,['quantity','quantity_range'])){
            return true;
        }

        return false;
    }

    public function checkMultiSelect($option_type)
    {
        if ($option_type == 'multi_select'){
            return true;
        }

        return false;
    }

    public function checkYesNo($option_type)
    {
        if ($option_type == 'yes_no'){
            return true;
        }

        return false;
    }

    # 自定义周期时长s
    public function customCycleTime($cycle_time,$cycle_unit='hour')
    {
        if ($cycle_unit == 'hour'){
            $time = $cycle_time * 3600;
        }elseif ($cycle_unit == 'day'){
            $time = $cycle_time * 3600 * 24;
        }else{
            $time = $cycle_time * 3600 * 24 * 30;
        }

        return $time;
    }

    # 系统周期时长s
    public function systemCycleTime($cycle)
    {
        if ($cycle == 'onetime'){
            $time = 0;
        }elseif ($cycle == 'monthly'){
            $time = 3600 * 24 * 30;
        }elseif ($cycle == 'quarterly'){
            $time = 3600 * 24 * 30 * 3;
        }elseif ($cycle == 'semaiannually'){
            $time = 3600 * 24 * 30 * 6;
        }elseif ($cycle == 'annually'){
            $time = 3600 * 24 * 30 * 12;
        }elseif ($cycle == 'biennially'){
            $time = 3600 * 24 * 30 * 24;
        }elseif ($cycle == 'triennianlly'){
            $time = 3600 * 24 * 30 * 36;
        }else{
            $time = 0;
        }

        return $time;
    }

    # 阶梯计费
    public function quantityStagePrice($configoptionId,$quantity,$cycle,$last_price=0,$is_custom=false)
    {
        if ($quantity == 0){
            return 0;
        }

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        $subs = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
            ->field('pcs.qty_min,pcs.qty_max')
            ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
            ->where('pcs.product_configoption_id',$configoptionId)
            ->where('pcs.hidden',0)
            ->select()
            ->toArray();

        array_multisort($subs,array_column($subs,'qty_max'));

        foreach ($subs as $k=>$v){
            if ($v['qty_max']>=$quantity && $quantity>=$v['qty_min']){
                $min = $k;
                break;
            }
        }
        if ($is_custom){
            $pricing = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                ->where('pcs.product_configoption_id',$configoptionId)
                ->where('pcs.hidden',0)
                ->where('pcs.qty_min','<=',$quantity)
                ->where('pcs.qty_max','>=',$quantity)
                ->order('pcs.id','acs')
                ->where('ccp.custom_cycle_id',$cycle)
                ->find();

            $amount = $pricing['amount']??0;
        }else{
            $pricing = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                ->where('pcs.product_configoption_id',$configoptionId)
                ->where('pcs.hidden',0)
                ->where('pcs.qty_min','<=',$quantity)
                ->where('pcs.qty_max','>=',$quantity)
                ->order('pcs.id','acs')
                ->find();
            $amount = $pricing[$cycle];
        }

        if ($pricing['qty_min'] != 0){
            $quantity = $quantity-$pricing['qty_min']+1;
        }

        if (!empty($pricing)){
            $price = $amount * $quantity;
        }else{
            $price = $last_price * $quantity;
        }
        if ($quantity > 0 && $min!=0){
            if ($pricing['qty_min']>1){
                $sum = $this->quantityStagePrice($configoptionId,intval($subs[$min-1]['qty_max']),$cycle,floatval($amount),$is_custom);
            }else{
                $sum = $this->quantityStagePrice($configoptionId,intval($subs[$min-1]['qty_max']),$cycle,0,$is_custom);
            }
            $price = $sum + $price;
        }
        return $price;
    }

    /*
     * 购物车计算价格{"configoption":{"1"：2,"2":3,"4":[1,2,3]},"cycle":"monthly","product_id":104},配置类型为数量时,值取数量;为其他类型时,值取子项ID
     * 参数传递规则:
     * config_options:{
   "configoption":{"1":2,"2":3,"4":[1,2,3]},   这里是：配置项ID=>子项ID ，当配置项类型为数量时传 数量数组，为多选时，传子项ID的数组。，，其他的话就传子项ID，，
   "cycle": "monthly"或者 “自定义周期ID”,   这里 系统默认周期 就这样传，，，如果是 自定义周期，，，传自定义周期ID
}
     *
     * */
    public function cartCalculatePrice($param)
    {
        $configoptions = $param['configoption']??[];

        $productId = $param['product_id'];

        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        $customCycle = $IdcsmartCommonCustomCycleModel->alias('cc')
            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.custom_cycle_id=cc.id AND ccp.type=\'product\'')
            ->where('cc.product_id',$productId)
            ->where('ccp.rel_id',$productId)
            ->where('ccp.amount','>=',0)
            ->where('cc.id',intval($param['cycle']))
            ->find();

        # 总价
        $price = 0;

        $description = [];

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        # 过滤配置项
        $configoptions = $IdcsmartCommonProductConfigoptionModel->filterConfigoption($productId,$configoptions);

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

        if (!empty($customCycle)){ # 自定义周期
            $cycleName = $customCycle['name']??'';
            $cycleTime = $this->customCycleTime($customCycle['cycle_time'],$customCycle['cycle_unit']);

            # 配置项价格
            foreach ($configoptions as $key=>$value){
                $tmp = $IdcsmartCommonProductConfigoptionModel->field('option_name,option_type,fee_type,allow_repeat,max_repeat')->where('id',$key)->find();
                $optionType = $tmp['option_type']??'';
                $feeType = $tmp['fee_type']??'qty';

                if ($this->checkQuantity($optionType)){
                    if (!is_array($value)){
                        $value = [$value];
                    }
                    foreach ($value as $k=>$item){
                        $quantityType = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                            ->where('pcs.product_configoption_id',$key)
                            ->where('pcs.hidden',0)
                            ->where('pcs.qty_min','<=',$item)
                            ->where('pcs.qty_max','>=',$item)
                            ->order('pcs.id','acs')
                            ->where('ccp.custom_cycle_id',$param['cycle'])
                            ->find();

                        if (!empty($quantityType)){
                            if ($k>=1){
                                $description[] = $tmp['option_name'] . $k . '=>' . $item;
                            }else{
                                $description[] = $tmp['option_name'] . '=>' . $item;
                            }

                            # 阶梯计费
                            if ($feeType == 'stage'){
                                $price = bcadd($price,$this->quantityStagePrice($key,$item,$param['cycle'],0,true),2);
                            }else{ # 数量计费
                                $price = bcadd($price,$quantityType['amount'] * $item,2);
                            }
                        }
                    }

                }elseif($this->checkMultiSelect($optionType)){ # 多选
                    $configoptionPrices = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                        ->field('pc.option_name,pcs.option_name as sub_name,ccp.amount')
                        ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                        ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                        ->where('pc.hidden',0)
                        ->where('pcs.hidden',0)
                        ->where('pc.id',$key)
                        ->whereIn('pcs.id',$value??[])
                        ->where('ccp.custom_cycle_id',$param['cycle'])
                        ->select()
                        ->toArray();
                    foreach ($configoptionPrices as $configoptionPrice){
                        $description[] = $configoptionPrice['option_name'] . '=>' . $configoptionPrice['sub_name'];
                        $price = bcadd($price,isset($configoptionPrice['amount']) && $configoptionPrice['amount']>=0?$configoptionPrice['amount']:0,2);
                    }
                }else{
                    $configoptionPrice = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                        ->field('pc.option_name,pcs.option_name as sub_name,ccp.amount')
                        ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                        ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.rel_id=pcs.id AND ccp.type=\'configoption\'')
                        ->where('pc.hidden',0)
                        ->where('pcs.hidden',0)
                        ->where('pc.id',$key)
                        ->where('pcs.id',$value)
                        ->where('ccp.custom_cycle_id',$param['cycle'])
                        ->find();
                    if (!empty($configoptionPrice)){
                        $description[] = $configoptionPrice['option_name'] . '=>' . $configoptionPrice['sub_name'];
                    }
                    $price = bcadd($price,isset($configoptionPrice['amount']) && $configoptionPrice['amount']>=0?$configoptionPrice['amount']:0,2);

                }
            }


            # 商品价格
            $price = bcadd($price,$customCycle['amount'],2);


        }else{ # 系统周期
            $cycleName = $this->systemCycles[$param['cycle']]??'';
            $cycleTime = $this->systemCycleTime($param['cycle']);

            # 配置项价格
            foreach ($configoptions as $key=>$value){

                $tmp = $IdcsmartCommonProductConfigoptionModel->field('option_name,option_type,fee_type,allow_repeat,max_repeat')->where('id',$key)->find();
                $optionType = $tmp['option_type']??'';
                $feeType = $tmp['fee_type']??'qty';

                # 数量类型
                if ($this->checkQuantity($optionType)){
                    if (!is_array($value)){
                        $value = [$value];
                    }
                    foreach ($value as $k=>$item){
                        $quantityType = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                            ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                            ->where('pcs.product_configoption_id',$key)
                            ->where('pcs.hidden',0)
                            ->where('pcs.qty_min','<=',$item)
                            ->where('pcs.qty_max','>=',$item)
                            ->order('pcs.id','acs')
                            ->find();
                        if (!empty($quantityType)){
                            if ($k>=1){
                                $description[] = $tmp['option_name'] . $k . '=>' . $item;
                            }else{
                                $description[] = $tmp['option_name'] . '=>' . $item;
                            }
                            # 阶梯计费
                            if ($feeType == 'stage'){
                                $price = bcadd($price,$this->quantityStagePrice($key,$item,$param['cycle']),2);
                            }else{ # 数量计费
                                $price = bcadd($price,$quantityType[$param['cycle']] * $item,2);
                            }
                        }
                    }
                }elseif($this->checkMultiSelect($optionType)){ # 多选
                    $configoptionPrices = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                        ->field('pc.option_name,pcs.option_name as sub_name,p.onetime,p.monthly,p.quarterly,p.semaiannually,p.annually,p.biennially,p.triennianlly')
                        ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                        ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                        ->where('pc.hidden',0)
                        ->where('pcs.hidden',0)
                        ->where('pc.id',$key)
                        ->whereIn('pcs.id',$value??[])
                        ->select()
                        ->toArray();
                    foreach ($configoptionPrices as $configoptionPrice){
                        $description[] = $configoptionPrice['option_name'] . '=>' . $configoptionPrice['sub_name'];
                        $price = bcadd($price,isset($configoptionPrice[$param['cycle']]) && $configoptionPrice[$param['cycle']]>=0?$configoptionPrice[$param['cycle']]:0,2);
                    }
                }else{ # 非数量类型
                    $configoptionPrice = $IdcsmartCommonProductConfigoptionModel->alias('pc')
                        ->field('pc.option_name,pcs.option_name as sub_name,p.onetime,p.monthly,p.quarterly,p.semaiannually,p.annually,p.biennially,p.triennianlly')
                        ->leftJoin('module_idcsmart_common_product_configoption_sub pcs','pcs.product_configoption_id=pc.id')
                        ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                        ->where('pc.hidden',0)
                        ->where('pcs.hidden',0)
                        ->where('pc.id',$key)
                        ->where('pcs.id',$value)
                        ->find();
                    if (!empty($configoptionPrice)){
                        $description[] = $configoptionPrice['option_name'] . '=>' . $configoptionPrice['sub_name'];
                    }

                    $price = bcadd($price,isset($configoptionPrice[$param['cycle']]) && $configoptionPrice[$param['cycle']]>=0?$configoptionPrice[$param['cycle']]:0,2);
                }
            }

            # 商品价格
            $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
            $productPricing = $IdcsmartCommonPricingModel->where('type','product')
                ->where('rel_id',$productId)
                ->find();
            $price = bcadd($price,isset($productPricing[$param['cycle']]) && $productPricing[$param['cycle']]>0?$productPricing[$param['cycle']]:0,2);
        }

        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[
                'price'=>$param['cycle']=='free'?0:$price,
                'billing_cycle'=>$param['cycle']=='free'?lang_plugins('free'):$cycleName,
                'duration'=>$param['cycle']=='free'?0:$cycleTime,
                'description'=>implode("\n",$description),
                'content'=>implode("\n",$description)
            ]
        ];

        return $result;
    }

    # 结算后调用,保存下单的配置项{"custom":{"configoption":{"1"：2,"2":3}},"product":{},"host_id":1},配置类型为数量时,值取数量;为其他类型时,值取子项ID
    public function afterSettle($param)
    {
        $product = $param['product'];

        $productId = $product['id'];

        $hostId = $param['host_id'];

        $configoptions = $param['custom']['configoption']??[];

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();

        $configoptions = $IdcsmartCommonProductConfigoptionModel->filterConfigoption($productId,$configoptions,1);

        $insert = [];

        foreach ($configoptions as $key=>$value){
            $configoption = $IdcsmartCommonProductConfigoptionModel->where('id',$key)->find();
            $optionType = $configoption['option_type']??'';
            if ($this->checkQuantity($optionType)){
                if (!is_array($value)){
                    $value = [$value];
                }
                foreach ($value as $k=>$item){
                    $insert[] = [
                        'host_id' => $hostId,
                        'configoption_id' => $key,
                        'configoption_sub_id' => 0,
                        'qty' => $item,
                        'repeat' => $k
                    ];
                }

            }elseif ($this->checkMultiSelect($optionType)){
                if (!is_array($value)){
                    $value = [$value];
                }
                foreach ($value as $item){
                    $insert[] = [
                        'host_id' => $hostId,
                        'configoption_id' => $key,
                        'configoption_sub_id' => $item,
                        'qty' => 0,
                        'repeat' => 0
                    ];
                }
            }
            else{
                $insert[] = [
                    'host_id' => $hostId,
                    'configoption_id' => $key,
                    'configoption_sub_id' => $value,
                    'qty' => 0,
                    'repeat' => 0
                ];
            }
        }

        $IdcsmartCommonHostConfigoptionModel =new IdcsmartCommonHostConfigoptionModel();
        $IdcsmartCommonHostConfigoptionModel->insertAll($insert);

        return true;
    }

    # 获取可用续费周期
    public function currentDurationPrice($host_id)
    {
        $HostModel = new HostModel();
        $host = $HostModel->find($host_id);
        if (empty($host)){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }
        $productId = $host['product_id'];

        $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
        $pricing = $IdcsmartCommonPricingModel->where('type','product')
            ->where('rel_id',$productId)
            ->find();

        $systemCycles = array_keys($this->systemCycles);

        $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();
        $configoptions = $IdcsmartCommonHostConfigoptionModel->alias('hc')
            ->field('pc.id,pc.option_type,hc.configoption_sub_id,hc.qty,pc.fee_type')
            ->leftJoin('module_idcsmart_common_product_configoption pc','pc.id=hc.configoption_id')
            ->where('hc.host_id',$host_id)
            ->select()
            ->toArray();

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

        $cycles = [];
        foreach ($systemCycles as $systemCycle){
            if ($pricing[$systemCycle]==-1){
                unset($pricing[$systemCycle]);
            }else{
                $cycleFee = $pricing[$systemCycle]??0;

                foreach ($configoptions as $configoption){
                    if ($this->checkQuantity($configoption['option_type'])){
                        # 找子项
                        $qtySub = $IdcsmartCommonProductConfigoptionSubModel
                            ->where('product_configoption_id',$configoption['id'])
                            ->where('qty_min','<=',$configoption['qty'])
                            ->where('qty_max','>=',$configoption['qty'])
                            ->order('order','asc')
                            ->find();
                        if (!empty($qtySub)){
                            # 阶梯计费
                            if ($configoption['fee_type'] == 'stage'){
                                $cycleFee = bcadd($cycleFee,$this->quantityStagePrice($configoption['id'],$configoption['qty'],$systemCycle),2);
                            }else{ # 数量计费
                                $cycleFee = bcadd($cycleFee,$qtySub[$systemCycle] * $configoption['qty'],2);
                            }
                            
                        }

                    }else{
                        $sub = $IdcsmartCommonPricingModel->where('type','configoption')
                            ->where('rel_id',$configoption['configoption_sub_id'])
                            ->find();
                        if (!empty($sub)){
                            $cycleFee = bcadd($cycleFee,$sub[$systemCycle]>=0?$sub[$systemCycle]:0,2);
                        }
                    }
                }


                $cycles[$systemCycle] = $cycleFee;
            }
        }


        # 自定义周期及价格
        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        $customCycles = $IdcsmartCommonCustomCycleModel->alias('cc')
            ->field('cc.id,cc.name,cc.cycle_time,cc.cycle_unit,ccp.amount')
            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.custom_cycle_id=cc.id AND ccp.type=\'product\'')
            ->where('cc.product_id',$productId)
            ->where('ccp.rel_id',$productId)
            ->where('ccp.amount','>=',0) # 可显示出得周期
            ->select()
            ->toArray();
        $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
        foreach ($customCycles as &$customCycle){

            $customCycleAmount = $customCycle['amount']??0;

            # 配置子项的自定义价格
            foreach ($configoptions as $configoption){
                if ($this->checkQuantity($configoption['option_type'])){
                    # 找子项
                    $qtySub = $IdcsmartCommonProductConfigoptionSubModel
                        ->where('product_configoption_id',$configoption['id'])
                        ->where('qty_min','<=',$configoption['qty'])
                        ->where('qty_max','>=',$configoption['qty'])
                        ->order('order','asc')
                        ->find();
                    if (!empty($qtySub)){
                        # 阶梯计费
                        if ($configoption['fee_type'] == 'stage'){
                            $customCycleAmount = bcadd($customCycleAmount,$this->quantityStagePrice($configoption['id'],$configoption['qty'],$customCycle['id'],0,true),2);
                        }else{ # 数量计费
                            $customCycleAmount = bcadd($customCycleAmount,$qtySub[$systemCycle] * $configoption['qty'],2);
                        }

                    }
                }else{
                    $amount = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$customCycle['id'])
                        ->where('rel_id',$configoption['id'])
                        ->where('type','configoption')
                        ->value('amount');
                    $customCycleAmount = bcadd($customCycleAmount,!is_null($amount) && $amount>=0?$amount:0,2);
                }
            }
            $customCycle['cycle_amount'] = $customCycleAmount;
        }

        $duration = [];
        foreach ($cycles as $key=>$item){
            $duration[] = [
                'duration' => $this->systemCycleTime($key),
                'price' => $item,
                'billing_cycle' => $this->systemCycles[$key]
            ];
        }
        foreach ($customCycles as $item1){
            $duration[] = [
                'duration' => $this->customCycleTime($item1['cycle_time'],$item1['cycle_unit']),
                'price' => $item1['cycle_amount'],
                'billing_cycle' => $item1['name']
            ];
        }

        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>$duration
        ];

        return $result;
    }

    # 获取所有配置
    public function allConfigOption($product_id)
    {
        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
        $configoptions = $IdcsmartCommonProductConfigoptionModel->where('product_id',$product_id)
            ->order('order','asc')
            ->select()
            ->toArray();

        $data = [];

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        foreach ($configoptions as $configoption){
            $subArr = [];
            # TODO 排除数量和多选类型
            if (!$this->checkQuantity($configoption['option_type']) && !$this->checkMultiSelect($configoption['option_type'])){
                $subs = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$configoption['id'])
                    ->select()
                    ->toArray();

                foreach ($subs as $sub){
                    $subArr[] = [
                        'name' => $sub['option_name'],
                        'value' => $sub['id']
                    ];
                }
                $data[] = [
                    'name' => $configoption['option_name'],
                    'field' => "configoption[{$configoption['id']}]",
                    'type' => 'dropdown',
                    'option' => $subArr
                ];
            }
        }

        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>$data,
        ];

        return $result;
    }

    # TODO 当前所选配置项,排除数量类型和多选类型,未处理(优惠码使用)
    public function currentConfigOptioin($host_id)
    {
        $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();
        $configoptions = $IdcsmartCommonHostConfigoptionModel->alias('hc')
            ->field('hc.configoption_id,hc.configoption_sub_id')
            ->leftJoin('module_idcsmart_common_product_configoption pc','pc.id=hc.configoption_id AND pc.type not in (\'multi_select\',\'quantity\',\'quantity_range\')')
            ->where('hc.host_id',$host_id)
            ->select()
            ->toArray();

        $data = [];
        foreach ($configoptions as $configoption){
            $data[$configoption['configoption_id']] = $configoption['configoption_sub_id'];
        }

        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>$data,
        ];

        return $result;
    }
    
}
