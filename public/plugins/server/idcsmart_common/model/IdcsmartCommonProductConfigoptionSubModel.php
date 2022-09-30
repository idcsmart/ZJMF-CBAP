<?php 
namespace server\idcsmart_common\model;

use server\idcsmart_common\logic\IdcsmartCommonLogic;
use think\Model;

class IdcsmartCommonProductConfigoptionSubModel extends Model
{
    protected $name = 'module_idcsmart_common_product_configoption_sub';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'product_configoption_id'=> 'int',
        'option_name'            => 'string',
        'option_param'           => 'string',
        'qty_min'                => 'int',
        'qty_max'                => 'int',
        'order'                  => 'int',
        'hidden'                 => 'int',
        'country'                => 'string',
    ];

    /**
     * 时间 2022-09-26
     * @title 配置子项详情
     * @desc 配置子项详情
     * @url /admin/v1/idcsmart_common/configoption/:configoption_id/sub/:id
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 配置子项ID require
     * @return object configoption_sub - 子项信息
     * @return int configoption_sub.id -
     * @return  float configoption_sub.onetime - 一次性,价格(值为-1时显示空)
     * @return  float configoption_sub.monthly - 月，价格(值为-1时显示空)
     * @return  float configoption_sub.quarterly - 季，价格(值为-1时显示空)
     * @return  float configoption_sub.semaiannually - 半年，价格(值为-1时显示空)
     * @return  float configoption_sub.annually - 一年，价格(值为-1时显示空)
     * @return  float configoption_sub.biennially - 两年，价格(值为-1时显示空)
     * @return  float configoption_sub.triennianlly - 三年，价格(值为-1时显示空)
     * @return array configoption_sub.custom_cycle - 自定义周期
     * @return array configoption_sub.custom_cycle.id - 自定义周期ID
     * @return array configoption_sub.custom_cycle.name - 名称
     * @return array configoption_sub.custom_cycle.amount - 金额
     */
    public function indexConfigoptionSub($param)
    {
        $configoptionId = $param['configoption_id']??0;

        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        $configoptionSub = $IdcsmartCommonProductConfigoptionSubModel->alias('cs')
            ->field('cs.id,cs.option_name,cs.option_param,cs.country,cs.qty_min,cs.qty_max,p.onetime,p.monthly,p.quarterly,p.semaiannually,p.annually,p.biennially,p.triennianlly')
            ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=cs.id AND p.type=\'configoption\'')
            ->where('cs.product_configoption_id',$configoptionId)
            ->where('cs.id',$param['id'])
            ->find();
        if (empty($configoptionSub)){
            return ['status'=>400,'msg'=>lang_plugins('idcsmart_common_configoption_sub_not_exist')];
        }

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
        $productId = $IdcsmartCommonProductConfigoptionModel->where('id',$configoptionId)->value('product_id');

        # 获取自定义周期
        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        $customCycles = $IdcsmartCommonCustomCycleModel->where('product_id',$productId)
            ->field('id,name')
            ->select()
            ->toArray();
        # 配置子项的自定义周期及价格
        $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
        foreach ($customCycles as &$customCycle){
            $amount = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$customCycle['id'])
                ->where('rel_id',$configoptionSub['id'])
                ->where('type','configoption')
                ->value('amount');
            $customCycle['amount'] = $amount??bcsub(0,0,2);
        }

        $configoptionSub['custom_cycle'] = $customCycles;

        return [
            'status' =>200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'configoption_sub' => $configoptionSub
            ],
        ];
    }

    /**
     * 时间 2022-09-26
     * @title 添加配置子项
     * @desc 添加配置子项
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   string option_name - 配置项名称
     * @param   string option_param - 参数:请求接口
     * @param   int qty_min - 最小值：类型为数量的时候quantity,quantity_range选择
     * @param   int qty_max - 最大值：类型为数量的时候quantity,quantity_range选择
     * @param   string country - 国家:类型为区域时选择
     * @param   string country - 国家:类型为区域时选择
     * @param   float onetime - 一次性价格：不填时传-1,
     * @param   float monthly - 月：不填时传-1,
     * @param   float quarterly - 季：不填时传-1,
     * @param   float semaiannually - 半年：不填时传-1,
     * @param   float annually - 一年：不填时传-1,
     * @param   float biennially - 两年：不填时传-1,
     * @param   float triennianlly - 三年：不填时传-1,
     * @param   object custom_cycle - 自定义周期及价格格式：{"{自定义周期ID}":"{金额}"}
     * @param   float custom_cycle.1 - 自定义周期及价格：留空传-1(所有价格都是如此，留空传-1)
     */
    public function createConfigoptionSub($param)
    {
        $this->startTrans();

        try{
            $configoptionId = $param['configoption_id']??0;

            $maxOrder = $this->max('order');

            $subId = $this->insertGetId([
                'product_configoption_id' => $configoptionId,
                'option_name' => $param['option_name']??'',
                'option_param' => $param['option_param']??'',
                'qty_min' => $param['qty_min']??0,
                'qty_max' => $param['qty_max']??0,
                'country' => $param['country']??'',
                'order' => $maxOrder,
                'hidden' => $param['hidden']??0
            ]);

            # 插入价格
            $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
            $param['sub_id'] = $subId;
            $IdcsmartCommonPricingModel->commonInsert($param,$subId,'configoption');

            # 获取自定义周期
            $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
            $productId = $IdcsmartCommonProductConfigoptionModel->where('id',$configoptionId)->value('product_id');
            $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
            $customCycles = $IdcsmartCommonCustomCycleModel->where('product_id',$productId)
                ->select()
                ->toArray();
            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
            foreach ($customCycles as $customCycle){
                # 插入自定义周期价格
                $IdcsmartCommonCustomCyclePricingModel->insert([
                    'custom_cycle_id' => $customCycle['id'],
                    'rel_id' => $subId,
                    'type' => 'configoption',
                    'amount' => $param['custom_cycle'][$customCycle['id']]??-1
                ]);
            }

            $IdcsmartCommonProductConfigoptionModel->updateConfigoptionQuantity($configoptionId);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-26
     * @title 修改配置子项
     * @desc 修改配置子项
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 配置子项ID require
     * @param   string option_name - 配置项名称
     * @param   string option_param - 参数:请求接口
     * @param   int qty_min - 最小值：类型为数量的时候quantity,quantity_range选择
     * @param   int qty_max - 最大值：类型为数量的时候quantity,quantity_range选择
     * @param   string country - 国家:类型为区域时选择
     * @param   string country - 国家:类型为区域时选择
     * @param   float onetime - 一次性价格：不填时传-1,
     * @param   float monthly - 月：不填时传-1,
     * @param   float quarterly - 季：不填时传-1,
     * @param   float semaiannually - 半年：不填时传-1,
     * @param   float annually - 一年：不填时传-1,
     * @param   float biennially - 两年：不填时传-1,
     * @param   float triennianlly - 三年：不填时传-1,
     * @param   object custom_cycle - 自定义周期及价格格式：{"{自定义周期ID}":"{金额}"}
     * @param   float custom_cycle.1 - 自定义周期及价格：留空传-1(所有价格都是如此，留空传-1)
     */
    public function updateConfigoptionSub($param)
    {
        $this->startTrans();

        try{
            $configoptionId = $param['configoption_id']??0;

            $subId = $param['id']??0;

            $configoptionSub = $this->find($subId);
            if (empty($configoptionSub)){
                throw new \Exception(lang_plugins('idcsmart_common_configoption_sub_not_exist'));
            }

            $configoptionSub->save([
                'product_configoption_id' => $configoptionId,
                'option_name' => $param['option_name']??'',
                'option_param' => $param['option_param']??'',
                'qty_min' => $param['qty_min']??0,
                'qty_max' => $param['qty_max']??0,
                'country' => $param['country']??'',
            ]);

            # 插入价格
            $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
            $param['sub_id'] = $subId;
            $IdcsmartCommonPricingModel->commonInsert($param,$subId,'configoption');

            # 获取自定义周期
            $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
            $productId = $IdcsmartCommonProductConfigoptionModel->where('id',$configoptionId)->value('product_id');
            $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
            $customCycles = $IdcsmartCommonCustomCycleModel->where('product_id',$productId)
                ->select()
                ->toArray();
            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
            foreach ($customCycles as $customCycle){
                # 插入自定义周期价格
                $customCyclePricing = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$customCycle['id'])
                    ->where('rel_id',$subId)
                    ->where('type','configoption')
                    ->find();
                if (!empty($customCyclePricing)){
                    $customCyclePricing->save([
                        'custom_cycle_id' => $customCycle['id'],
                        'rel_id' => $subId,
                        'type' => 'configoption',
                        'amount' => $param['custom_cycle'][$customCycle['id']]??-1
                    ]);
                }else{
                    $IdcsmartCommonCustomCyclePricingModel->insert([
                        'custom_cycle_id' => $customCycle['id'],
                        'rel_id' => $subId,
                        'type' => 'configoption',
                        'amount' => $param['custom_cycle'][$customCycle['id']]??-1
                    ]);
                }
            }

            $IdcsmartCommonProductConfigoptionModel->updateConfigoptionQuantity($configoptionId);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-26
     * @title 删除配置子项
     * @desc 删除配置子项
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 配置子项ID require
     */
    public function deleteConfigoptionSub($param)
    {
        $this->startTrans();

        try{
            $configoptionId = $param['configoption_id']??0;

            $subId = $param['id']??0;

            $configoptionSub = $this->find($subId);
            if (empty($configoptionSub)){
                throw new \Exception(lang_plugins('idcsmart_common_configoption_sub_not_exist'));
            }

            $configoptionSub->delete();

            # 删除价格
            $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
            $IdcsmartCommonPricingModel->where('type','configoption')
                ->where('rel_id',$subId)
                ->delete();

            # 获取自定义周期
            $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
            $productId = $IdcsmartCommonProductConfigoptionModel->where('id',$configoptionId)->value('product_id');
            $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
            $customCycles = $IdcsmartCommonCustomCycleModel->where('product_id',$productId)
                ->select()
                ->toArray();

            # 删除自定义价格
            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
            foreach ($customCycles as $customCycle){
                $IdcsmartCommonCustomCyclePricingModel->where('rel_id',$subId)
                    ->where('type','configoption')
                    ->where('custom_cycle_id',$customCycle['id'])
                    ->delete();
            }

            $IdcsmartCommonProductConfigoptionModel->updateConfigoptionQuantity($configoptionId);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

}