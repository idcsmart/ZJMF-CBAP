<?php
namespace server\idcsmart_common\model;

use app\common\model\HostModel;
use app\common\model\ProductModel;
use server\idcsmart_common\logic\IdcsmartCommonLogic;
use think\facade\Db;
use think\db\Query;
use think\Model;

class IdcsmartCommonProductModel extends Model
{
    protected $name = 'module_idcsmart_common_product';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'product_id'             => 'int',
        'order_page_description' => 'string',
        'allow_qty'              => 'int',
        'auto_support'           => 'int',
        'create_time'            => 'int',
        'update_time'            => 'int',
        'type'                   => 'string',
        'rel_id'                 => 'int',
    ];

    /**
     * 时间 2022-09-26
     * @title 商品基础信息
     * @desc 商品基础信息,插入默认价格信息
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return array
     * @return string pay_type - 付款类型：付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return object common_product - 商品信息
     * @return int common_product - 商品信息
     * @return int common_product.product_id - 商品ID
     * @return string common_product.order_page_description - 订购页面html
     * @return int common_product.allow_qty - 是否允许选择数量:1是，0否
     * @return int common_product.auto_support - 是否自动化支持:1是，0否
     * @return  object pricing - 周期信息(注意显示)
     * @return  float pricing.onetime - 一次性,价格(当pay_type=='onetime'时,只显示此价格)
     * @return  float pricing.monthly - 月，价格(当pay_type!=onetime时,显示)
     * @return  float pricing.quarterly - 季，价格(当pay_type!=onetime时,显示)
     * @return  float pricing.semaiannually - 半年，价格(当pay_type!=onetime时,显示)
     * @return  float pricing.annually - 一年，价格(当pay_type!=onetime时,显示)
     * @return  float pricing.biennially - 两年，价格(当pay_type!=onetime时,显示)
     * @return  float pricing.triennianlly - 三年，价格(当pay_type!=onetime时,显示)
     * @return object custom_cycle - 自定义周期
     * @return int custom_cycle.id - 自定义周期ID
     * @return string custom_cycle.name - 名称
     * @return int custom_cycle.cycle_time - 时长
     * @return string custom_cycle.cycle_unit - 时长单位
     * @return float custom_cycle.amount - 金额,-1不显示出，留空
     */
    public function indexProduct($param)
    {
        $productId = $param['product_id']??0;

        $ProductModel = new ProductModel();

        $product = $ProductModel->find($productId); # pay_type

        $commonProduct = $this->field('product_id,order_page_description,allow_qty,auto_support')
            ->where('product_id',$productId)
            ->find();

        if (!empty($commonProduct)){
            $commonProduct['order_page_description'] = htmlspecialchars_decode($commonProduct['order_page_description']);
        }

        # 是否有默认价格数据
        $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
        $pricing = $IdcsmartCommonPricingModel->where('type','product')
            ->where('rel_id',$productId)
            ->find();
        if (empty($pricing)){
            $IdcsmartCommonPricingModel->insert([
                'type' => 'product',
                'rel_id' => $productId
            ]);
        }

        $pricing = $IdcsmartCommonPricingModel
            ->withoutField('id,type,rel_id')
            ->where('type','product')
            ->where('rel_id',$productId)
            ->find();

        # 自定义周期及价格
        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
        $customCycle = $IdcsmartCommonCustomCycleModel->alias('cc')
            ->field('cc.id,cc.name,cc.cycle_time,cc.cycle_unit,ccp.amount')
            ->leftJoin('module_idcsmart_common_custom_cycle_pricing ccp','ccp.custom_cycle_id=cc.id AND ccp.type=\'product\'')
            ->where('ccp.rel_id',$productId)
            ->select()
            ->toArray();

        $data = [
            'pay_type' => $product['pay_type'],
            'common_product' => $commonProduct??['product_d'=>$productId,'order_page_description'=>'','allow_qty'=>0,'auto_support'=>0],
            'pricing' => $pricing??[],
            'custom_cycle' => $customCycle??[]
        ];

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];

        return $result;
    }

    /**
     * 时间 2022-09-26
     * @title 保存商品基础信息
     * @desc 保存商品基础信息
     * @author wyh
     * @version v1
     * @param int product_id - 商品ID require
     * @param string order_page_description - 订购页描述
     * @param int allow_qty - 是否允许选择数量:1是，0否默认
     * @param int auto_support - 自动化支持:开启后所有配置选项都可输入参数
     * @param object pricing - 周期价格,格式:{"onetime":0.1,"monthly":-1,"quarterly":1.0}
     * @param float pricing.onetime - 一次性价格:删除时，传此周期价格为-1
     * @param float pricing.monthly - 月:删除时，传此周期价格为-1
     * @param float pricing.quarterly - 季:删除时，传此周期价格为-1
     * @param float pricing.semaiannually - 半年:删除时，传此周期价格为-1
     * @param float pricing.annually - 一年:删除时，传此周期价格为-1
     * @param float pricing.biennially - 两年:删除时，传此周期价格为-1
     * @param float pricing.triennianlly - 三年:删除时，传此周期价格为-1
     */
    public function createProduct($param)
    {
        $this->startTrans();

        try{
            $productId = $param['product_id']??0;

            $commonProduct = $this->where('product_id',$productId)->find();

            if (!empty($commonProduct)){
                $commonProduct->save([
                    'order_page_description' => htmlspecialchars($param['order_page_description']),
                    'allow_qty' => intval($param['allow_qty']),
                    'auto_support' => intval($param['auto_support']),
                    'update_time' => time()
                ]);
            }else{
                $this->insert([
                    'product_id' => $productId,
                    'order_page_description' => htmlspecialchars($param['order_page_description']),
                    'allow_qty' => intval($param['allow_qty']),
                    'auto_support' => intval($param['auto_support']),
                    'create_time' => time()
                ]);
            }

            $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();

            $IdcsmartCommonPricingModel->commonInsert($param['pricing']??[],$productId);


            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-26
     * @title 获取自定义周期详情
     * @desc 获取自定义周期详情
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 自定义字段ID require
     * @return object custom_cycle
     * @return string custom_cycle.name - 名称
     * @return string custom_cycle.cycle_time - 周期时长
     * @return string custom_cycle.cycle_unit - 周期单位:day天,month月
     * @return string custom_cycle.amout - 金额
     */
    public function customCycle($param)
    {
        $productId = $param['product_id']??0;

        $id = $param['id']??0;

        $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();

        $customCycle = $IdcsmartCommonCustomCycleModel->where('product_id',$productId)->where('id',$id)->find();
        if (empty($customCycle)){
            return ['status'=>400,'msg'=>lang_plugins('idcsmart_common_custom_cycle_not_exist')];
        }

        $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
        $customCyclePricing = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$id)
            ->where('type','product')
            ->where('rel_id',$productId)
            ->find();

        $customCycle['amount'] = $customCyclePricing['amount']??-1;

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['custom_cycle'=>$customCycle??(object)[]]];
    }

    /**
     * 时间 2022-09-26
     * @title 添加自定义周期
     * @desc 添加自定义周期
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int name - 名称 require
     * @param   int cycle_time - 周期时长 require
     * @param   int cycle_unit - 周期单位:day天,month月 require
     */
    public function createCustomCycle($param)
    {
        $this->startTrans();

        try{
            $productId = $param['product_id']??0;

            $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();
            $customCycleId = $IdcsmartCommonCustomCycleModel->insertGetId([
                'product_id' => $productId,
                'name' => $param['name']??'',
                'cycle_time' => $param['cycle_time']??0,
                'cycle_unit' => $param['cycle_unit']??'',
                'create_time' => time(),
            ]);

            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
            $IdcsmartCommonCustomCyclePricingModel->insert([
                'custom_cycle_id' => $customCycleId,
                'rel_id' => $productId,
                'type' => 'product',
                'amount' => $param['amount']??-1,
            ]);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-26
     * @title 修改自定义周期
     * @desc 修改自定义周期
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 自定义周期ID require
     * @param   int product_id - 商品ID require
     * @param   string name - 名称 require
     * @param   int cycle_time - 周期时长 require
     * @param   string cycle_unit - 周期单位:day天,month月 require
     * @param   float amout - 金额 require
     */
    public function updateCustomCycle($param)
    {
        $this->startTrans();

        try{
            $productId = $param['product_id']??0;

            $id = $param['id']??0;

            $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();

            $customCycle = $IdcsmartCommonCustomCycleModel->where('product_id',$productId)->where('id',$id)->find();
            if (empty($customCycle)){
                throw new \Exception(lang_plugins('idcsmart_common_custom_cycle_not_exist'));
            }

            $customCycle->save([
                'product_id' => $productId,
                'name' => $param['name']??'',
                'cycle_time' => $param['cycle_time']??0,
                'cycle_unit' => $param['cycle_unit']??'',
                'update_time' => time(),
            ]);

            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
            $customCyclePricing = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$id)
                ->where('type','product')
                ->where('rel_id',$productId)
                ->find();
            if (!empty($customCyclePricing)){
                $customCyclePricing->save([
                    'amount' => $param['amount']??-1,
                ]);
            }else{
                $IdcsmartCommonCustomCyclePricingModel->insert([
                    'custom_cycle_id' => $id,
                    'rel_id' => $productId,
                    'type' => 'product',
                    'amount' => $param['amount']??-1,
                ]);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-26
     * @title 删除自定义周期
     * @desc 删除自定义周期
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int id - 自定义周期ID require
     */
    public function deleteCustomCycle($param)
    {
        $this->startTrans();

        try{
            $productId = $param['product_id']??0;

            $id = $param['id']??0;

            $IdcsmartCommonCustomCycleModel = new IdcsmartCommonCustomCycleModel();

            $customCycle = $IdcsmartCommonCustomCycleModel->where('product_id',$productId)->where('id',$id)->find();
            if (empty($customCycle)){
                throw new \Exception(lang_plugins('idcsmart_common_custom_cycle_not_exist'));
            }

            $customCycle->delete();

            $IdcsmartCommonCustomCyclePricingModel = new IdcsmartCommonCustomCyclePricingModel();
            $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$id)
                ->where('type','product')
                ->where('rel_id',$productId)
                ->delete();

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-09-26
     * @title 前台商品配置信息
     * @desc 前台商品配置信息
     * @url /console/v1/idcsmart_common/product/:product_id/configoption
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  object common_product - 商品基础信息
     * @return  string common_product.order_page_description - 订购页面html
     * @return  string common_product.allow_qty - 是否允许选择数量:1是，0否默认
     * @return  string common_product.pay_type - 付款类型(免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return  object configoptions - 配置项信息
     * @return  int configoptions.id - 配置项ID
     * @return  int configoptions.option_name - 配置项名称
     * @return  int configoptions.option_type -  配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域
     * @return  int configoptions.qty_min - 数量时最小值
     * @return  int configoptions.qty_max - 数量时最大值
     * @return  int configoptions.unit - 单位
     * @return  int configoptions.allow_repeat - 数量类型时：是否允许重复:开启后,前台购买时，可通过点击添加按钮，自动创建一个新的配置项，取名如bw1
     * @return  int configoptions.max_repeat - 最大允许重复数量
     * @return  int configoptions.description - 说明
     * @return array configoptions.subs - 子项信息
     * @return  float configoptions.subs.id - 子项ID
     * @return  float configoptions.subs.option_name - 子项名称
     * @return object cycles - 周期({"onetime":1.00})
     * @return object custom_cycles - 自定义周期
     * @return int custom_cycles.id - 自定义周期ID
     * @return string custom_cycles.name - 自定义周期名称
     * @return int custom_cycles.cycle_time - 自定义周期时长
     * @return string custom_cycles.cycle_unit - 自定义周期单位
     * @return int custom_cycles.cycle_amount - 自定义周期金额
     */
    public function cartConfigoption($param)
    {
        $productId = $param['product_id']??0;

        $commonProduct = $this->alias('cp')
            ->field('cp.order_page_description,cp.allow_qty,p.pay_type')
            ->leftJoin('product p','p.id=cp.product_id')
            ->where('cp.product_id',$productId)
            ->find();

        $IdcsmartCommonPricingModel = new IdcsmartCommonPricingModel();
        $pricing = $IdcsmartCommonPricingModel->where('type','product')
            ->where('rel_id',$productId)
            ->find();

        $systemCycles = [
            'onetime',
            'monthly',
            'quarterly',
            'semaiannually',
            'annually',
            'biennially',
            'triennianlly'
        ];

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        $configoptions = $IdcsmartCommonProductConfigoptionModel->field('id,product_id,option_name,option_type,qty_min,qty_max,unit,allow_repeat,max_repeat,description')
            ->where('product_id',$productId)
            ->where('hidden',0)
            ->select()
            ->toArray();
        # TODO 最低配置子项价格(取第一个)
        $minSubPricings = [];
        foreach ($configoptions as &$configoption){
            $subs = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                ->field('pcs.id,pcs.option_name,pcs.qty_min,pcs.qty_max,pcs.country')/*,p.onetime,p.monthly,p.quarterly,p.semaiannually,p.annually,p.biennially,p.triennianlly*/
                ->leftJoin('module_idcsmart_common_pricing p','p.rel_id=pcs.id AND p.type=\'configoption\'')
                ->where('pcs.product_configoption_id',$configoption['id'])
                ->where('pcs.hidden',0)
                ->select()
                ->toArray();
            $configoption['subs'] = $subs??[];

            if (!empty($subs[0])){
                $minSubPricings[] = $subs[0];
            }
        }

        $cycles = [];
        foreach ($systemCycles as $systemCycle){
            if ($pricing[$systemCycle]==-1){
                unset($pricing[$systemCycle]);
            }else{
                $cycleFee = $pricing[$systemCycle]??0;

                foreach ($minSubPricings as $minSubPricing){
                    $cycleFee = bcadd($cycleFee,$minSubPricing[$systemCycle]??0,2);
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
            foreach ($minSubPricings as $minSubPricing){
                $amount = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$customCycle['id'])
                    ->where('rel_id',$minSubPricing['id'])
                    ->where('type','configoption')
                    ->value('amount');
                $customCycleAmount = bcadd($customCycleAmount,$amount??0);
            }
            $customCycle['cycle_amount'] = $customCycleAmount;
        }

        if (empty($commonProduct) || (!empty($commonProduct) && $commonProduct['pay_type'] == 'free')){
            $cycles = [];
            $cycles['free'] = 0;
        }

        $data = [
            'common_product' => $commonProduct??(object)[],
            'configoptions' => $configoptions??(object)[],
            'cycles' => $cycles??(object)[],
            'custom_cycles' => $customCycles??(object)[]
        ];

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
    }

    /**
     * 时间 2022-09-26
     * @title 前台产品内页
     * @desc 前台产品内页
     * @author wyh
     * @version v1
     * @param   int host_id - 产品ID require
     * @return  object host - 财务信息
     * @return  int host.create_time - 订购时间
     * @return  int host.due_time - 到期时间
     * @return  int host.billing_cycle - 计费方式:计费周期免费free，一次onetime，周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return  int host.billing_cycle_name - 模块计费周期名称
     * @return  int host.billing_cycle_time - 模块计费周期时间,秒
     * @return  int host.renew_amount - 续费金额
     * @return  int host.first_payment_amount - 首付金额
     * @return  int host.name - 商品名称
     * @return  int host.status - 产品状态
     * @return  object configoptions - 配置项信息
     * @return  int configoptions.id - 配置项ID
     * @return  int configoptions.option_name - 配置项名称
     * @return  int configoptions.option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域
     * @return  int configoptions.unit - 单位
     * @return  array configoptions.subs - 子项名称,数组
     * @return  int configoptions.qty - 数量(当类型为数量时,显示此值)
     */
    public function hostConfigotpion($param)
    {
        $hostId = $param['host_id']??0;

        $HostModel = new HostModel();

        $host = $HostModel->alias('h')
            ->field('h.create_time,h.due_time,h.billing_cycle,h.billing_cycle_name,h.billing_cycle_time,h.renew_amount,h.first_payment_amount,p.name,h.status')
            ->leftJoin('product p','p.id=h.product_id')
            ->where('h.id',$hostId)
            ->find();
        if (empty($host)){
            return ['status'=>400,'msg'=>lang_plugins('host_is_not_exist')];
        }

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
        $configoptions = $IdcsmartCommonProductConfigoptionModel->alias('pc')
            ->field('pc.id,pc.option_name,pc.option_type,pc.unit,hc.qty,hc.repeat')
            ->leftJoin('module_idcsmart_common_host_configoption hc','hc.configoption_id=pc.id ')
            ->where('hc.host_id',$hostId)
            ->withAttr('option_name',function ($value,$data){
                if ($data['repeat']>0){
                    return $value.$data['repeat'];
                }
                return $value;
            })
            ->select()
            ->toArray();
        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();

        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        $configoptionMultiSelect = $configoptionOther = [];

        foreach ($configoptions as $key=>$configoption){
            $subs = $IdcsmartCommonProductConfigoptionSubModel->alias('pcs')
                ->field('pcs.option_name,pcs.country')
                ->leftJoin('module_idcsmart_common_host_configoption hc','hc.configoption_sub_id=pcs.id')
                ->where('hc.host_id',$hostId)
                ->where('hc.configoption_id',$configoption['id'])
                ->select()
                ->toArray();
            $configoption['subs'] = $subs??[];

            if ($IdcsmartCommonLogic->checkMultiSelect($configoption['option_type'])){
                $configoptionMultiSelect[$configoption['id']] = $configoption;
            }else{
                $configoptionOther[] = $configoption;
            }

        }

        $configoptionFilter = array_merge($configoptionOther,array_values($configoptionMultiSelect));

        $data = [
            'host' => $host,
            'configoptions' => $configoptionFilter??[]
        ];

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];

        return $result;
    }

    /**
     * 时间 2022-09-28
     * @title 产品列表
     * @desc 产品列表
     * @author wyh
     * @version v1
     * @param string keywords - 关键字,搜索范围:产品ID,商品名称,标识
     * @param string status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,active_time,due_time
     * @param string sort - 升/降序 asc,desc
     * @return array list - 产品
     * @return int list[].id - 产品ID
     * @return int list[].product_id - 商品ID
     * @return string list[].product_name - 商品名称
     * @return string list[].name - 标识
     * @return int list[].active_time - 开通时间
     * @return int list[].due_time - 到期时间
     * @return string list[].first_payment_amount - 金额
     * @return string list[].billing_cycle - 周期
     * @return string list[].status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @return int count - 产品总数
     */
    public function hostList($param)
    {
        $param['client_id'] = get_client_id();
        $param['keywords'] = $param['keywords'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id', 'client_id', 'product_name', 'name', 'active_time', 'due_time', 'first_payment_amount', 'status']) ? $param['orderby'] : 'id';
        if($param['orderby']=='product_name'){
            $param['orderby'] = 'p.name';
        }else{
            $param['orderby'] = 'h.'.$param['orderby'];
        }

        $where = function (Query $query) use($param) {
            if(!empty($param['client_id'])){
                $query->where('h.client_id', $param['client_id']);
            }
            if(!empty($param['keywords'])){
                $query->where('h.id|p.name|h.name|c.username|c.email|c.phone', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['status'])){
                $query->where('h.status', $param['status']);
            }
            $query->where('s.module|ss.module','idcsmart_common');
        };
        $HostModel = new HostModel();
        $count = $HostModel->alias('h')
            ->field('h.id')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id AND s.module=\'idcsmart_common\'')
            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
            ->leftjoin('server ss','ss.server_group_id=sg.id AND ss.module=\'idcsmart_common\'')
            ->leftjoin('client c', 'c.id=h.client_id')
            ->where($where)
            ->count();
        $hosts = $HostModel->alias('h')
            ->field('h.id,h.client_id,c.username client_name,c.email,c.phone_code,c.phone,c.company,h.product_id,p.name product_name,h.name,h.create_time,h.active_time,h.due_time,h.first_payment_amount,h.billing_cycle,h.billing_cycle_name,h.status,o.pay_time')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id AND s.module=\'idcsmart_common\'')
            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
            ->leftjoin('server ss','ss.server_group_id=sg.id AND ss.module=\'idcsmart_common\'')
            ->leftjoin('client c', 'c.id=h.client_id')
            ->leftjoin('order o', 'o.id=h.order_id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        foreach ($hosts as $key => $host) {
            $hosts[$key]['first_payment_amount'] = amount_format($host['first_payment_amount']); // 处理金额格式
            $hosts[$key]['billing_cycle'] = $host['billing_cycle']!='onetime' ? $host['billing_cycle_name'] : '';

            unset($hosts[$key]['client_id'], $hosts[$key]['client_name'], $hosts[$key]['email'], $hosts[$key]['phone_code'], $hosts[$key]['phone'], $hosts[$key]['company']);

            unset($hosts[$key]['billing_cycle_name'], $hosts[$key]['create_time'], $hosts[$key]['pay_time']);
        }

        return ['list' => $hosts, 'count' => $count];
    }
}