<?php 
namespace server\idcsmart_common\model;

use app\common\model\HostModel;
use app\common\model\ProductModel;
use server\idcsmart_common\logic\IdcsmartCommonLogic;
use think\db\Query;
use think\Model;

class IdcsmartCommonProductConfigoptionModel extends Model
{
    protected $name = 'module_idcsmart_common_product_configoption';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'product_id'             => 'int',
        'option_name'            => 'string',
        'option_type'            => 'string',
        'option_param'           => 'string',
        'qty_min'                => 'int',
        'qty_max'                => 'int',
        'order'                  => 'int',
        'hidden'                 => 'int',
        'unit'                   => 'string',
        'allow_repeat'           => 'int',
        'max_repeat'             => 'int',
        'fee_type'               => 'string',
        'description'            => 'string',
        'configoption_id'        => 'int',
        'son_product_id'         => 'int',
        'free'                   => 'int',
    ];

    /**
     * 时间 2022-09-26
     * @title 配置项列表
     * @desc 配置项列表
     * @url /admin/v1/idcsmart_common/product/:product_id/configoption
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  array configoption - 列表数据
     * @return  int configoption.id -
     * @return  int configoption.product_id - 商品ID
     * @return  string configoption.option_name - 配置项名称
     * @return  int configoption.option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域
     * @return  int configoption.hidden - 是否隐藏:1是，0否
     */
    public function configoptionList($param)
    {
        $productId = $param['product_id']??0;

        $configoptions = $this->field('id,product_id,option_name,option_type,hidden')
            ->where('product_id',$productId)
            ->select()
            ->toArray();

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'configoption' => $configoptions
            ]
        ];
    }

    public function quantityConfigoption($param)
    {
        $where = function (Query $query) use ($param){
            $query->where('product_id',$param['product_id'])
                ->whereIn('option_type',['quantity','quantity_range']);

            if (isset($param['configoption_id']) && $param['configoption_id']){
                $query->where('id','<>',$param['configoption_id']);
            }
        };

        $list = $this->field('id,option_name')
            ->where($where)
            ->select()
            ->toArray();

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'list' => $list
            ]
        ];
    }

    /**
     * 时间 2022-09-26
     * @title 配置项详情
     * @desc 配置项详情
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  array configoption - 列表数据
     * @return  int configoption.id -
     * @return  int configoption.product_id - 商品ID
     * @return  string configoption.option_name - 配置项名称
     * @return  int configoption.option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域
     * @return  int configoption.hidden - 是否隐藏:1是，0否
     * @return  string configoption.unit - 单位
     * @return  int configoption.allow_repeat - 是否允许重复:开启后,前台购买时，可通过点击添加按钮，自动创建一个新的配置项，取名如bw1
     * @return  int configoption.max_repeat - 最大允许重复数量
     * @return  string configoption.fee_type - 数量的类型的计费方式：stage阶梯计费，qty数量计费(当前区间价格*数量)
     * @return  string configoption.description - 说明
     * @return  int configoption.configoption_id - 当前商品其他类型为数量拖动/数量输入的配置项ID
     * @return array configoption_sub - 子项信息
     * @return int configoption_sub.id -
     * @return  float configoption_sub.onetime - 一次性,价格
     * @return array configoption_sub.custom_cycle - 自定义周期
     * @return array configoption_sub.custom_cycle.id - 自定义周期ID
     * @return array configoption_sub.custom_cycle.name - 名称
     * @return array configoption_sub.custom_cycle.amount - 金额
     */
    public function indexConfigoption($param)
    {
        $productId = $param['product_id']??0;

        $id = $param['id']??0;

        $configoption = $this->where('product_id',$productId)
            ->where('id',$id)
            ->find();
        if (empty($configoption)){
            return ['status'=>400,'msg'=>lang_plugins('idcsmart_common_configoption_not_exist')];
        }

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

        $configoptionSubs = $IdcsmartCommonProductConfigoptionSubModel->alias('cs')
            ->field('cs.id,cs.option_name,p.onetime,cs.qty_min,cs.qty_max')
            ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=cs.id AND p.type=\'configoption\'')
            ->where('cs.product_configoption_id',$id)
            ->select()
            ->toArray();
        # 获取自定义周期
        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        $customCycles = $IdcsmartCommonCustomCycleModel->where('product_id',$productId)
            ->field('id,name')
            ->select()
            ->toArray();
        $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
        foreach ($configoptionSubs as &$configoptionSub){
            # 配置子项的自定义周期及价格
            if (!empty($customCycles)){
                foreach ($customCycles as $key=>$customCycle){
                    $amount = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$customCycle['id'])
                        ->where('rel_id',$configoptionSub['id'])
                        ->where('type','configoption')
                        ->value('amount');
                    $customCycles[$key]['amount'] = $amount??bcsub(0,1,2);
                }
            }
            $configoptionSub['custom_cycle'] = $customCycles??[];
        }

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'configoption' => $configoption,
                'configoption_sub' => $configoptionSubs
            ]
        ];
    }

    /**
     * 时间 2022-09-26
     * @title 添加配置项
     * @desc 添加配置项
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 配置项ID require
     * @param   string option_name - 配置项名称
     * @param   string option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域 require
     * @param   string option_param - 参数:请求接口
     * @param   string description - 说明
     * @param   string unit - 单位
     * @param   int allow_repeat - 是否允许重复:开启后,前台购买时，可通过点击添加按钮，自动创建一个新的配置项，取名如bw1
     * @param   int max_repeat - 最大允许重复数量
     * @param   string fee_type - 数量的类型的计费方式：stage阶梯计费，qty数量计费(当前区间价格*数量)
     * @param   int hidden - 是否隐藏:1是，0否
     * @param   int configoption_id - 当前商品其他类型为数量拖动/数量输入的配置项ID
     * @param   int set_son_product - 是否设为子商品:1是,0否(选择是时,才传下面pay_type,free两个字段)
     * @param   string pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @param   int free - 关联商品首周期是否免费:1是,0否
     */
    public function createConfigoption($param)
    {
        $this->startTrans();

        try{
            $productId = $param['product_id']??0;

            $maxOrder = $this->max('order');

            $id = $this->insertGetId([
                'product_id' => $productId,
                'option_name' => $param['option_name']??'',
                'option_type' => $param['option_type']??'select',
                'option_param' => $param['option_param']??'',
                'description' => $param['description']??'',
                'unit' => $param['unit']??'',
                'allow_repeat' => $param['allow_repeat']??0,
                'max_repeat' => $param['max_repeat']??5,
                'fee_type' => $param['fee_type']??'',
                'qty_min' => $param['qty_min']??0,
                'qty_max' => $param['qty_max']??0,
                'order' => $maxOrder+1,
                'hidden' => $param['hidden']??0,
            ]);

            if ($param['option_type'] == 'yes_no'){
                $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
                $IdcsmartCommonProductConfigoptionSubModel->insertYesNo($id);
            }

            # 更新商品最低价格
            $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();
            $IdcsmartCommonProductModel->updateProductMinPrice($productId);

            # 关联数量类型同步子项
            if (isset($param['configoption_id']) && $param['configoption_id']){
                $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
                $subs = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$param['configoption_id'])
                    ->select()
                    ->toArray();
                foreach ($subs as $sub){
                    $result = $IdcsmartCommonProductConfigoptionSubModel->createConfigoptionSub([
                        'configoption_id' => $id,
                        'option_name' => $sub['option_name']??'',
                        'option_param' => $sub['option_param']??'',
                        'qty_min' => $sub['qty_min']??0,
                        'qty_max' => $sub['qty_max']??0,
                        'country' => $sub['country']??'',
                        'hidden' => $sub['hidden']??0,
                    ]);
                    if ($result['status']!=200){
                        throw new \Exception($result['msg']);
                    }
                }
            }

            # 创建子商品
            if (isset($param['set_son_product']) && $param['set_son_product']){
                $ProductModel = new ProductModel();
                $product = $ProductModel->where('product_id',$productId)->find();
                $result2 = $ProductModel->createProduct([
                    'name' => $param['option_name']??'',
                    'product_group_id' => $product['product_group_id']??0,
                ]);
                if ($result2['status']!=200){
                    throw new \Exception($result2['msg']);
                }

                $ProductModel->update([
                    'pay_type' => $param['pay_type'],
                    'product_id' => $productId,
                    'update_time' => time()
                ],['id'=>$result2['data']['product_id']??0]);

                $this->update([
                    'son_product_id' => $result2['data']['product_id']??0,
                    'free' => $param['free']
                ],['id'=>$id]);

                $maxOrder = $this->max('order');

                $sonId = $this->insertGetId([
                    'product_id' => $result2['data']['product_id']??0,
                    'option_name' => $param['option_name']??'',
                    'option_type' => $param['option_type']??'select',
                    'option_param' => $param['option_param']??'',
                    'description' => $param['description']??'',
                    'unit' => $param['unit']??'',
                    'allow_repeat' => $param['allow_repeat']??0,
                    'max_repeat' => $param['max_repeat']??5,
                    'fee_type' => $param['fee_type']??'',
                    'qty_min' => $param['qty_min']??0,
                    'qty_max' => $param['qty_max']??0,
                    'order' => $maxOrder+1,
                    'hidden' => $param['hidden']??0,
                ]);
            }

            $this->commit();
        }catch (\Exception $e){

            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['id'=>$id]];
    }

    /**
     * 时间 2022-09-26
     * @title 更新配置项
     * @desc 更新配置项
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 配置项ID require
     * @param   string option_name - 配置项名称
     * @param   string option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域 require
     * @param   string option_param - 参数:请求接口
     * @param   string description - 说明
     * @param   string unit - 单位
     * @param   int allow_repeat - 是否允许重复:开启后,前台购买时，可通过点击添加按钮，自动创建一个新的配置项，取名如bw1
     * @param   int max_repeat - 最大允许重复数量
     * @param   string fee_type - 数量的类型的计费方式：stage阶梯计费，qty数量计费(当前区间价格*数量)
     * @param   int hidden - 是否隐藏:1是，0否
     */
    public function updateConfigoption($param)
    {
        $this->startTrans();

        try{
            $productId = $param['product_id']??0;

            $id = $param['id']??0;

            $configoption = $this->where('product_id',$productId)->where('id',$id)->find();
            if (empty($configoption)){
                throw new \Exception(lang_plugins('idcsmart_common_configoption_not_exist'));
            }

            $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();
            $hostCount = $IdcsmartCommonHostConfigoptionModel->where('configoption_id',$id)->count();
            if ($hostCount>0 && $configoption['option_type'] != $param['option_type']){
                throw new \Exception(lang_plugins('idcsmart_common_configoption_cannot_update'));
            }

            # 切换为不同类型,删除子项
            $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
            if ($configoption['option_type'] != $param['option_type']){

                $configoptionSubs = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$id)
                    ->select();
                $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
                $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
                foreach ($configoptionSubs as $configoptionSub){
                    # 删除子项一次性价格
                    $IdcsmartCommonPricingModel->where('rel_id',$configoptionSub['id'])
                        ->where('type','configoption')
                        ->delete();

                    # 删除子项的自定义周期价格
                    $IdcsmartCommonCustomCyclePricingModel->where('rel_id',$configoptionSub['id'])
                        ->where('type','configoption')
                        ->delete();

                    # 删除子项
                    $configoptionSub->delete();
                }

                if ($param['option_type']=='yes_no'){
                    $IdcsmartCommonProductConfigoptionSubModel->insertYesNo($id);
                }
            }

            $configoption->save([
                'option_name' => $param['option_name']??'',
                'option_type' => $param['option_type']??'select',
                'option_param' => $param['option_param']??'',
                'description' => $param['description']??'',
                'unit' => $param['unit']??'',
                'allow_repeat' => $param['allow_repeat']??0,
                'max_repeat' => $param['max_repeat']??5,
                'fee_type' => $param['fee_type']??'',
            ]);

            # 更新商品最低价格
            $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();
            $IdcsmartCommonProductModel->updateProductMinPrice($productId);

            $this->commit();
        }catch (\Exception $e){

            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-26
     * @title 删除配置项
     * @desc 删除配置项
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 配置项ID require
     */
    public function deleteConfigoption($param)
    {
        $this->startTrans();

        try{
            $productId = $param['product_id']??0;

            $id = $param['id']??0;

            $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();
            $hostCount = $IdcsmartCommonHostConfigoptionModel->where('configoption_id',$id)->count();
            if ($hostCount>0){
                throw new \Exception(lang_plugins('idcsmart_common_configoption_cannot_delete'));
            }

            $configoption = $this->where('product_id',$productId)->where('id',$id)->find();
            if (empty($configoption)){
                throw new \Exception(lang_plugins('idcsmart_common_configoption_not_exist'));
            }

            $configoption->delete();

            # 删除子项
            $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
            $configoptionSubs = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$id)
                ->select();
            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
            $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
            foreach ($configoptionSubs as $configoptionSub){
                # 删除子项价格
                $IdcsmartCommonPricingModel->where('rel_id',$configoptionSub['id'])
                    ->where('type','configoption')
                    ->delete();

                # 删除子项的自定义周期价格
                $IdcsmartCommonCustomCyclePricingModel->where('rel_id',$configoptionSub['id'])
                    ->where('type','configoption')
                    ->delete();
                $configoptionSub->delete();
            }

            # 更新商品最低价格
            $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();
            $IdcsmartCommonProductModel->updateProductMinPrice($productId);

            $this->commit();
        }catch (\Exception $e){

            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-26
     * @title 配置项开启/隐藏
     * @desc 配置项开启/隐藏
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 配置项ID require
     */
    public function hiddenConfigoption($param)
    {
        $this->startTrans();

        try{
            $productId = $param['product_id']??0;

            $id = $param['id']??0;

            if (!isset($param['hidden']) || !in_array($param['hidden'],[0,1])){
                throw new \Exception(lang_plugins('param_error'));
            }

            $configoption = $this->where('product_id',$productId)->where('id',$id)->find();
            if (empty($configoption)){
                throw new \Exception(lang_plugins('idcsmart_common_configoption_not_exist'));
            }

            if ($configoption['hidden'] == $param['hidden']){
                throw new \Exception(lang_plugins('cannot_repeat_operate'));
            }

            $configoption->save([
                'hidden' => intval($param['hidden'])
            ]);

            # 更新商品最低价格
            $IdcsmartCommonProductModel = new IdcsmartCommonProductModel();
            $IdcsmartCommonProductModel->updateProductMinPrice($productId);

            $this->commit();
        }catch (\Exception $e){

            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    # 修改配置项数量数据：qty_min,qty_max
    public function updateConfigoptionQuantity($configoption_id)
    {
        $configoption = $this->find($configoption_id);

        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        if ($IdcsmartCommonLogic->checkQuantity($configoption['option_type'])){
            $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
            $min = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$configoption_id)->min('qty_min');
            $max = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$configoption_id)->max('qty_max');

            $this->update([
                'qty_min' => $min,
                'qty_max' => $max
            ],['id'=>$configoption_id]);
        }

        return true;
    }

    # 过滤配置子项(隐藏的配置项不计算价格,但是要保存)
    public function filterConfigoption($product_id,$configoptions,$hidden=0)
    {
        $where = function (Query $query) use ($product_id,$hidden){
            if ($hidden==0){
                $query->where('hidden',0);
            }
            $query->where('product_id',$product_id);
        };

        $allConfigoptions = $this->where($where)
            ->order('order','asc')
            ->select()
            ->toArray();


        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

        $configoptionsFilter = [];

        foreach ($allConfigoptions as $k1=>$v1){
            $qtyMin = $v1['qty_min'];
            $qtyMax = $v1['qty_max'];
            $optionType = $v1['option_type'];
            $configoptionId = $v1['id'];
            if ($IdcsmartCommonLogic->checkQuantity($optionType)){
                if (isset($configoptions[$configoptionId]) && is_array($configoptions[$configoptionId])){
                    $qtyArr = [];
                    foreach ($configoptions[$configoptionId] as $k2=>$v2){
                        $qty = $v2 < $qtyMin ? $qtyMin : $v2;
                        $qty = $qty > $qtyMax ? $qtyMax : $v2;
                        $qtyArr[] = $qty;
                    }
                    $configoptionsFilter[$configoptionId] = $qtyArr;
                }else{
                    # 不传或参数错误,默认取排序第一的子项的最小值
                    $qtySub = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$configoptionId)
                        ->order('order','asc')
                        ->order('id','asc')
                        ->find();
                    if (!empty($qtySub)){
                        $qtyMin = $qtySub['qty_min'];
                    }

                    $configoptionsFilter[$configoptionId] = [$qtyMin];
                }
            }elseif ($IdcsmartCommonLogic->checkMultiSelect($optionType)){
                if (isset($configoptions[$configoptionId]) && is_array($configoptions[$configoptionId])){
                    $multiArr = [];
                    foreach ($configoptions[$configoptionId] as $k2=>$v2){
                        $multiSub = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$configoptionId)
                            ->where('hidden',0)
                            ->where('id',$v2)
                            ->find();
                        if (!empty($multiSub)){ # 过滤掉不存在的子项
                            $multiArr[] = $v2;
                        }
                    }

                    $configoptionsFilter[$configoptionId] = $multiArr;
                }else{
                    $configoptionsFilter[$configoptionId] = [];
                }
            }else{
                if (isset($configoptions[$configoptionId]) && $configoptions[$configoptionId]){
                    $otherSub = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$configoptionId)
                        ->where('hidden',0)
                        ->where('id',$configoptions[$configoptionId])
                        ->find();
                }else{
                    $otherSub = null;
                }

                if (!empty($otherSub)){
                    $configoptionsFilter[$configoptionId] = $otherSub['id'];
                }else{
                    $sub = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$configoptionId)
                        ->where('hidden',0)
                        ->order('order','asc')
                        ->order('id','asc')
                        ->find();
                    if (!empty($sub)){
                        $configoptionsFilter[$configoptionId] = $sub['id'];
                    }
                }
            }
        }

        return $configoptionsFilter;
    }

}