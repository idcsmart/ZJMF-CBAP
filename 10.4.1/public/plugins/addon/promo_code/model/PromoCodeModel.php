<?php
namespace addon\promo_code\model;

use addon\idcsmart_renew\model\IdcsmartRenewModel;
use addon\promo_code\logic\PromoCodeLogic;
use app\common\logic\ModuleLogic;
use app\common\logic\ResModuleLogic;
use app\common\model\ClientModel;
use app\common\model\HostModel;
use app\common\model\OrderItemModel;
use app\common\model\OrderModel;
use app\common\model\ProductModel;
use app\common\model\UpgradeModel;
use app\common\model\UpstreamProductModel;
use think\db\Query;
use think\Model;

/**
 * @title 优惠码模型
 * @desc 优惠码模型
 * @use addon\promo_code\model\PromoCodeModel
 */
class PromoCodeModel extends Model
{
    protected $name = 'addon_promo_code';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'code'              => 'string',
        'type'              => 'string',
        'value'             => 'float',
        'status'            => 'int',
        'client_type'       => 'string',
        'start_time'        => 'int',
        'end_time'          => 'int',
        'max_times'         => 'int',
        'used'              => 'int',
        'single_user_once'  => 'int',
        'upgrade'           => 'int',
        'host_upgrade'      => 'int',
        'renew'             => 'int',
        'loop'              => 'int',
        'cycle_limit'       => 'int',
        'cycle'             => 'string',
        'notes'             => 'string',
        'delete_time'       => 'int',
        'create_time'       => 'int',
        'update_time'       => 'int',
    ];

    # 优惠码适用场景
    private $applyScene = [
        'New',
        'Upgrade',
        'Renew',
    ];

    # 适用场景时长
    private $applySceneTime = [
        'monthly'       => ['min' => 28*24*3600, 'max' => 31*24*3600],
        'quarterly'     => ['min' => 89*24*3600, 'max' => 92*24*3600],
        'semiannually'  => ['min' => 178*24*3600, 'max' => 185*24*3600],
        'annually'      => ['min' => 360*24*3600, 'max' => 366*24*3600],
        'biennially'    => ['min' => 2*360*24*3600, 'max' => 2*366*24*3600],
        'triennially'   => ['min' => 3*360*24*3600, 'max' => 3*366*24*3600],
    ];

    /**
     * 时间 2022-10-19
     * @title 优惠码列表
     * @desc 优惠码列表
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字搜索:优惠码
     * @param string param.type - 优惠码类型:percent百分比,fixed_amount固定金额减免,replace_price覆盖价格,free免费
     * @param string param.status - 状态:Suspended已停用,Active启用中,Expiration已失效,Pending待生效
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,code
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 优惠码列表
     * @return int list[].id - ID
     * @return string list[].code - 优惠码
     * @return string list[].type - 优惠码类型:percent百分比,fixed_amount固定金额减免,replace_price覆盖价格,free免费
     * @return float list[].value - 优惠码数值
     * @return int list[].max_times - 可用
     * @return int list[].used - 已用
     * @return int list[].start_time - 开始时间
     * @return int list[].end_time - 结束时间
     * @return int list[].status - 状态:Suspended已停用,Active启用中,Expiration已失效,Pending待生效
     * @return int count - 优惠码总数
     */
    public function promoCodeList($param)
    {
        if (!isset($param['orderby']) || !in_array($param['orderby'],['id','code'])){
            $param['orderby'] = 'id';
        }

        $where = function (Query $query) use ($param){
            if (isset($param['keywords']) && !empty($param['keywords'])){
                $query->where('code','like',"%{$param['keywords']}%");
            }
            if (isset($param['type']) && !empty($param['type'])){
                $query->where('type', $param['type']);
            }
            if (isset($param['status']) && !empty($param['status'])){
                $time = time();
                if($param['status']=='Pending'){
                    $query->whereRaw("start_time>{$time}");
                }else if($param['status']=='Active'){
                    $query->whereRaw("status=1 AND (end_time=0 OR end_time>={$time})");
                }else if($param['status']=='Suspended'){
                    $query->whereRaw("status=0 AND (end_time=0 OR end_time>={$time})");
                }else if($param['status']=='Expiration'){
                    $query->whereRaw("end_time>0 AND end_time<{$time}");
                }
            }

        };

        $promoCodes = $this->field('id,code,type,value,max_times,used,start_time,end_time,status')
            ->withAttr('status',function ($value,$data){
                $time = time();
                if ($data['start_time']>$time){
                    return 'Pending';
                }else if (!empty($data['end_time'])){ # 自定义失效时间
                    if ($data['end_time']<$time){
                        return 'Expiration';
                    } elseif ($time<=$data['end_time'] && $value==1){
                        return 'Active';
                    } elseif ($time<=$data['end_time'] && $value==0){
                        return 'Suspended';
                    }
                }else{
                    if ($value == 1){
                        return 'Active';
                    }else{
                        return 'Suspended';
                    }
                }
            })
            ->where('delete_time',0)
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->order('status','desc')
            ->order('end_time','asc')
            ->select()
            ->toArray();

        $count = $this->where('delete_time',0)->where($where)->count();

        return ['list'=>$promoCodes, 'count'=>$count];
    }

    /**
     * 时间 2022-10-19
     * @title 获取优惠码
     * @desc 获取优惠码
     * @author theworld
     * @version v1
     * @param int param.id - 优惠码ID required
     * @return int id - ID
     * @return string code - 优惠码
     * @return string type - 优惠码类型:percent百分比,fixed_amount固定金额减免,replace_price覆盖价格,free免费
     * @return float value - 优惠码数值
     * @return string client_type - 适用客户:all不限,new无产品用户,old用户必须存在激活中的产品
     * @return int start_time - 开始时间
     * @return int end_time - 结束时间,为0代表无限
     * @return int max_times - 最大使用次数:0不限
     * @return int single_user_once - 单用户一次:0关闭,1开启
     * @return int upgrade - 升降级:0关闭,1开启
     * @return int host_upgrade - 升降级商品配置:0关闭,1开启
     * @return int renew - 续费:0关闭,1开启
     * @return int loop - 循环优惠:0关闭,1开启
     * @return int cycle_limit - 周期限制:0关闭,1开启
     * @return array cycle - 周期:monthly月,quarterly季,semiannually半年,annually一年,biennially两年,triennially三年
     * @return string notes - 备注
     * @return array products - 可应用商品的ID
     * @return array need_products - 需求商品的ID
     */
    public function indexPromoCode($param)
    {
        $promoCode =  $this->field('id,code,type,value,client_type,start_time,end_time,max_times,single_user_once,upgrade,host_upgrade,renew,loop,cycle_limit,cycle,notes')
            ->where('delete_time',0)
            ->find($param['id']);
        if (empty($promoCode)){
            return (object)[];
        }

        $promoCode['cycle'] = !empty($promoCode['cycle']) ? explode(',',$promoCode['cycle']) : [];

        $promoCode['products'] = PromoCodeProductModel::where('addon_promo_code_id',$param['id'])->column('product_id');

        $promoCode['need_products'] = PromoCodeProductNeedModel::where('addon_promo_code_id',$param['id'])->column('product_id');

        return $promoCode;
    }

    /**
     * 时间 2022-10-19
     * @title 添加优惠码
     * @desc 添加优惠码
     * @author theworld
     * @version v1
     * @param string param.code - 优惠码 required
     * @param string param.type - 优惠码类型:percent百分比,fixed_amount固定金额减免,replace_price覆盖价格,free免费 required
     * @param float param.value - 优惠码数值 类型不为免费时必填
     * @param string param.client_type - 适用客户:all不限,new无产品用户,old用户必须存在激活中的产品 required
     * @param int param.start_time - 开始时间 required
     * @param int param.end_time - 结束时间  
     * @param int param.max_times - 最大使用次数:0不限 required
     * @param int param.single_user_once - 单用户一次:0关闭,1开启 required
     * @param int param.upgrade - 升降级:0关闭,1开启,仅百分比和免费支持开启 required
     * @param int param.host_upgrade - 升降级商品配置:0关闭,1开启,仅百分比支持开启 required
     * @param int param.renew - 续费:0关闭,1开启,仅百分比和免费支持开启 required
     * @param int param.loop - 循环优惠:0关闭,1开启,仅百分比支持开启 required
     * @param int param.cycle_limit - 周期限制:0关闭,1开启 required
     * @param array param.cycle - 周期:monthly月,quarterly季,semiannually半年,annually一年,biennially两年,triennially三年 周期限制开启时必填
     * @param string param.notes - 备注
     * @param array param.products - 可应用商品的ID
     * @param array param.need_products - 需求商品的ID
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createPromoCode($param)
    {
        # 判断value值
        if($param['type']=='percent'){
            if (isset($param['value'])){
                if (!is_numeric($param['value'])){
                    return ['status'=>400,'msg'=>lang_plugins('promo_code_type_percent_value_error')];
                }
                if ($param['value']<=0){
                    return ['status'=>400,'msg'=>lang_plugins('promo_code_type_percent_value_error')];
                }
                if ($param['value']>100){
                    return ['status'=>400,'msg'=>lang_plugins('promo_code_type_percent_value_error')];
                }
            }else{
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_percent_value_error')];
            }
        }else if($param['type']=='fixed_amount'){
            if($param['upgrade']==1 || $param['host_upgrade']==1 || $param['renew']==1 || $param['loop']==1){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_fixed_amount_not_support')];
            }
            if (isset($param['value'])){
                if (!is_numeric($param['value'])){
                    return ['status'=>400,'msg'=>lang_plugins('promo_code_type_fixed_amount_value_error')];
                }
                if ($param['value']<=0){
                    return ['status'=>400,'msg'=>lang_plugins('promo_code_type_fixed_amount_value_error')];
                }
            }else{
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_fixed_amount_value_error')];
            }
        }else if($param['type']=='replace_price'){
            if($param['upgrade']==1 || $param['host_upgrade']==1 || $param['renew']==1 || $param['loop']==1){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_replace_price_not_support')];
            }
            if (isset($param['value'])){
                if (!is_numeric($param['value'])){
                    return ['status'=>400,'msg'=>lang_plugins('promo_code_type_replace_price_value_error')];
                }
                if ($param['value']<0){
                    return ['status'=>400,'msg'=>lang_plugins('promo_code_type_replace_price_value_error')];
                }
            }else{
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_replace_price_value_error')];
            }
        }else if($param['type']=='free'){
            if($param['host_upgrade']==1 || $param['loop']==1){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_free_not_support')];
            }
            $param['value'] = 0;
        }
        

        # 验证适用产品及规格
        $ProductModel = new ProductModel();
        foreach ($param['products'] as $value){
            $product = $ProductModel->find($value);
            if (empty($product)){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_product_is_not_exist')];
            }
        }
        foreach ($param['need_products'] as $value){
            $product = $ProductModel->find($value);
            if (empty($product)){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_product_is_not_exist')];
            }
        }

        $this->startTrans();

        try{
            $promoCode = $this->create([
                'code' => $param['code'],
                'type' => $param['type'],
                'value' => $param['value']??0,
                'client_type' => $param['client_type'],
                'start_time' => $param['start_time']??0,
                'end_time' => $param['end_time']??0,
                'max_times' => $param['max_times']??0,
                'single_user_once' => $param['single_user_once'],
                'upgrade' => $param['upgrade'],
                'host_upgrade' => $param['host_upgrade'],
                'renew' => $param['renew'],
                'loop' => $param['loop'],
                'cycle_limit' => $param['cycle_limit'],
                'cycle' => implode(',',$param['cycle']),
                'notes' => $param['notes']??'',
                'create_time' => time(),
                'status' => 1,
                'delete_time' => 0,
            ]);

            $PromoCodeProductModel = new PromoCodeProductModel();
            $insert = [];
            foreach ($param['products'] as $value){
                $insert[] = [
                    'addon_promo_code_id' => $promoCode->id,
                    'product_id' => $value,
                ];
            }
            $PromoCodeProductModel->saveAll($insert);

            $PromoCodeProductNeedModel = new PromoCodeProductNeedModel();
            $insert = [];
            foreach ($param['need_products'] as $value){
                $insert[] = [
                    'addon_promo_code_id' => $promoCode->id,
                    'product_id' => $value,
                ];
            }
            $PromoCodeProductNeedModel->saveAll($insert);

            active_log(lang_plugins('log_admin_create_promo_code',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{promo_code}'=>$param['code']]),'promo_code',$promoCode->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('create_fail')];
        }

        return ['status'=>200,'msg'=>lang_plugins('create_success')];
    }

    /**
     * 时间 2022-10-19
     * @title 编辑优惠码
     * @desc 编辑优惠码
     * @author theworld
     * @version v1
     * @param int param.id - 优惠码ID required
     * @param string param.client_type - 适用客户:all不限,new无产品用户,old用户必须存在激活中的产品 required
     * @param int param.start_time - 开始时间 required
     * @param int param.end_time - 结束时间  
     * @param int param.max_times - 最大使用次数:0不限 required
     * @param int param.single_user_once - 单用户一次:0关闭,1开启 required
     * @param int param.upgrade - 升降级:0关闭,1开启,仅百分比和免费支持开启 required
     * @param int param.host_upgrade - 升降级商品配置:0关闭,1开启,仅百分比支持开启 required
     * @param int param.renew - 续费:0关闭,1开启,仅百分比和免费支持开启 required
     * @param int param.loop - 循环优惠:0关闭,1开启,仅百分比支持开启 required
     * @param int param.cycle_limit - 周期限制:0关闭,1开启 required
     * @param array param.cycle - 周期:monthly月,quarterly季,semiannually半年,annually一年,biennially两年,triennially三年 周期限制开启时必填
     * @param string param.notes - 备注
     * @param array param.products - 可应用商品的ID
     * @param array param.need_products - 需求商品的ID
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updatePromoCode($param)
    {
        $promoCode =  $this->where('delete_time',0)->where('id',$param['id'])->find();
        if (empty($promoCode)){
            return ['status'=>400,'msg'=>lang_plugins('promo_code_is_not_exist')];
        }

        # 验证适用产品及规格
        $ProductModel = new ProductModel();
        foreach ($param['products'] as $value){
            $product = $ProductModel->find($value);
            if (empty($product)){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_product_is_not_exist')];
            }
        }
        foreach ($param['need_products'] as $value){
            $product = $ProductModel->find($value);
            if (empty($product)){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_product_is_not_exist')];
            }
        }

        # 判断value值
        if($promoCode['type']=='fixed_amount'){
            if($param['upgrade']==1 || $param['host_upgrade']==1 || $param['renew']==1 || $param['loop']==1){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_fixed_amount_not_support')];
            }
        }else if($promoCode['type']=='replace_price'){
            if($param['upgrade']==1 || $param['host_upgrade']==1 || $param['renew']==1 || $param['loop']==1){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_replace_price_not_support')];
            }
        }else if($promoCode['type']=='free'){
            if($param['host_upgrade']==1 || $param['loop']==1){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_type_free_not_support')];
            }
        }

        //$logDescription = log_description($promoCode->toArray(),$param,'promo_code',true);

        $this->startTrans();

        try{
            $this->update([
                'client_type' => $param['client_type'],
                'start_time' => $param['start_time']??0,
                'end_time' => $param['end_time']??0,
                'max_times' => $param['max_times']??0,
                'single_user_once' => $param['single_user_once'],
                'upgrade' => $param['upgrade'],
                'host_upgrade' => $param['host_upgrade'],
                'renew' => $param['renew'],
                'loop' => $param['loop'],
                'cycle_limit' => $param['cycle_limit'],
                'cycle' => implode(',',$param['cycle']),
                'notes' => $param['notes']??'',
                'update_time' => time(),
            ], ['id' => $param['id']]);

            $PromoCodeProductModel = new PromoCodeProductModel();
            $PromoCodeProductModel->where('addon_promo_code_id',$param['id'])->delete();
            $insert = [];
            foreach ($param['products'] as $value){
                $insert[] = [
                    'addon_promo_code_id' => $param['id'],
                    'product_id' => $value,
                ];
            }
            $PromoCodeProductModel->saveAll($insert);

            $PromoCodeProductNeedModel = new PromoCodeProductNeedModel();
            $PromoCodeProductNeedModel->where('addon_promo_code_id',$param['id'])->delete();
            $insert = [];
            foreach ($param['need_products'] as $value){
                $insert[] = [
                    'addon_promo_code_id' => $param['id'],
                    'product_id' => $value,
                ];
            }
            $PromoCodeProductNeedModel->saveAll($insert);

            //active_log(lang_plugins('log_admin_update_promo_code',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{promo_code}'=>$promoCode->code,'{description}'=>$logDescription]),'promo_code',$promoCode->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('update_fail')];
        }

        return ['status'=>200,'msg'=>lang_plugins('update_success')];

    }

    /**
     * 时间 2022-10-19
     * @title 删除优惠码
     * @desc 删除优惠码
     * @author theworld
     * @version v1
     * @param int id - 优惠码ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deletePromoCode($id)
    {
        $promoCode =  $this->where('delete_time',0)->where('id',$id)->find();
        if (empty($promoCode)){
            return ['status'=>400,'msg'=>lang_plugins('promo_code_is_not_exist')];
        }

        $this->startTrans();

        try{
            $this->update([
                'delete_time' => time()
            ], ['id' => $id]);

            active_log(lang_plugins('log_admin_delete_promo_code',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{promo_code}'=>$promoCode['code']]),'promo_code',$promoCode->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('delete_fail')];
        }

        return ['status'=>200,'msg'=>lang_plugins('delete_success')];
    }

    /**
     * 时间 2022-10-19
     * @title 启用/禁用优惠码
     * @desc 启用/禁用优惠码
     * @author theworld
     * @version v1
     * @param int param.id - 优惠码ID required
     * @param int param.status - 状态:0禁用,1启用 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function statusPromoCode($param)
    {
        $promoCode =  $this->where('delete_time',0)->where('id',$param['id'])->find();
        if (empty($promoCode)){
            return ['status'=>400,'msg'=>lang_plugins('promo_code_is_not_exist')];
        }
        $time = time();

        if ($promoCode['valid_time']){
            if ($promoCode['start_time']>$time || $promoCode['end_time']<$time){
                return ['status'=>400,'msg'=>lang_plugins('promo_code_valid')];
            }
        }

        if (!isset($param['status'])){
            return ['status'=>400,'msg'=>lang_plugins('param_error')];
        }
        if (!in_array($param['status'],[0,1])){
            return ['status'=>400,'msg'=>lang_plugins('promo_code_status')];
        }

        if ($promoCode->status == $param['status']){
            return ['status'=>400,'msg'=>lang_plugins('cannot_repeat_opreate')];
        }

        $status = $param['status'];

        $this->startTrans();
        try{
            $this->update([
                'status' => $status,
                'update_time' => $time,
            ],['id'=>intval($param['id'])]);

            # 记录日志
            if ($status == 1){
                active_log(lang_plugins('log_admin_enable_promo_code',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{promo_code}'=>$promoCode['code']]),'promo_code',$promoCode->id);
            }else{
                active_log(lang_plugins('log_admin_disable_promo_code',['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{promo_code}'=>$promoCode['code']]),'promo_code',$promoCode->id);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang_plugins('fail_message')];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-10-19
     * @title 获取随机优惠码
     * @desc 获取随机优惠码
     * @author theworld
     * @version v1
     * @return string - - 优惠码
     */
    public function generatePromoCode()
    {
        $PromoCodeLogic = new PromoCodeLogic();

        $code = $PromoCodeLogic->generatePromoCode();

        return $code;
    }

    /**
     * 时间 2022-10-20
     * @title 处理优惠码
     * @desc 处理优惠码
     * @author theworld
     * @version v1
     * @param string param.scene - 优惠码应用场景:new新购,renew续费,upgrade升降级 required
     * @param string param.promo_code - 优惠码 新购时必传
     * @param int param.host_id - 产品ID
     * @param int param.product_id - 商品ID
     * @param int param.order_id - 订单ID
     * @param int param.client_id - 用户ID
     * @param int param.amount - 单价 required
     * @param int param.billing_cycle_time - 周期时间 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return float data.discount 1.00 折扣金额
     * @return array data.order_items - 优惠码订单子项
     * @return int data.order_items[].host_id - 产品ID
     * @return int data.order_items[].product_id - 商品ID
     * @return int data.order_items[].type addon_promo_code 订单子项类型
     * @return int data.order_items[].rel_id - 优惠码ID
     * @return int data.order_items[].amount - 金额
     * @return int data.order_items[].description - 描述
     */
    public function clientPromoCode($param)
    {
        # 判断优惠码
        /*if (!isset($param['promo_code']) || empty($param['promo_code'])){
            return ['status'=>400,'msg'=>lang_plugins('param_error')];
        }*/

        $promoCode = $param['promo_code'] ?? '';

        //unset($param['promo_code']);
        $data = $param;

        $amount = $param['amount']??0;
        # 是否应用成功
        //$applySuccess = false;
        # 订单子项
        $orderItems = [];
        # 总折扣金额
        $discountTotal = 0;
        # 过滤相同优惠码
        //$promoCodes = array_unique($promoCodes);

        /*foreach ($promoCodes as $promoCode){
            $data['promo_code'] = $promoCode;*/
            $result = $this->clientPromoCodeSingleHandle($data);
            # 考虑叠加使用
            if ($result['status'] == 200){

                $discount = floatval($result['data']['discount']);
                $discount2 = floatval($result['data']['discount2'] ?? 0);
                # 判断金额
                $baseAmount = $amount;
                $amount = bcsub($amount,$discount,2);
                if ($amount<=0){
                    $discount = $baseAmount>0?$baseAmount:0;
                }

                $discountTotal = bcadd($discountTotal,$discount,2);

                //$applySuccess = true;

                # 记录至订单子项
                $PromoCodeModel = $this->find($result['data']['id']);
                if ($PromoCodeModel['type'] == 'percent'){
                    $description = lang_plugins('promo_code_type_percent_description',['{promo_code}'=>$promoCode,'{host_id}'=>$param['host_id']??0,'{value}'=>$PromoCodeModel['value']]);
                }else if ($PromoCodeModel['type'] == 'fixed_amount'){
                    $description = lang_plugins('promo_code_type_fixed_amount_description',['{promo_code}'=>$promoCode,'{host_id}'=>$param['host_id']??0,'{value}'=>$PromoCodeModel['value']]);
                }else if ($PromoCodeModel['type'] == 'replace_price'){
                    $description = lang_plugins('promo_code_type_replace_price_description',['{promo_code}'=>$promoCode,'{host_id}'=>$param['host_id']??0,'{value}'=>$PromoCodeModel['value']]);
                }else if ($PromoCodeModel['type'] == 'free'){
                    $description = lang_plugins('promo_code_type_free_description',['{promo_code}'=>$promoCode,'{host_id}'=>$param['host_id']??0]);
                }

                $orderItems[] = [
                    'host_id' => $param['host_id']??0,
                    'product_id' => $param['product_id']??0,
                    'type' => $this->name, # 类型存表名(除前缀)
                    'rel_id' => $result['data']['id'],
                    'amount' => -$discount,
                    'description' => $description,
                ];

                if(isset($param['host_id']) && !empty($param['host_id']) && $param['scene']=='new'){
                    $host = HostModel::find($param['host_id']);
                    if(!empty($host) && $host['is_delete'] == 0){
                        HostModel::update([
                            'first_payment_amount' => $host['first_payment_amount']-$discount,
                            'renew_amount' => $PromoCodeModel['loop']==1 ? (($host['renew_amount']-$discount2)>0 ? ($host['renew_amount']-$discount2) : 0) : $host['renew_amount'],
                        ], ['id' => $param['host_id']]);
                    }
                }

                if(!empty($promoCode)){
                    # 记录使用次数
                    $this->update([
                        'used' => $PromoCodeModel->used + 1,
                        'update_time' => time()
                    ], ['id' => $PromoCodeModel->id]);

                    PromoCodeLogModel::create([
                        'addon_promo_code_id' => $PromoCodeModel->id,
                        'host_id' => $param['host_id']??0,
                        'product_id' => $param['product_id']??0,
                        'order_id' => $param['order_id']??0,
                        'client_id' => $param['client_id']??0,
                        'scene' => $param['scene']??'',
                        'amount' => $param['amount']??0,
                        'discount' => $discount,
                        'create_time' => time(),
                    ]);
                }
                
            }
        //}

        $return = [
            'discount' => $discountTotal,
            'loop' => $PromoCodeModel['loop']??0,
            'order_items' => $orderItems
        ];

        return ['status'=>200, 'msg'=>lang_plugins('success_message'), 'data'=> $return];
    }

    /**
     * 时间 2022-10-20
     * @title 应用优惠码
     * @desc 应用优惠码,新购/续费/升降级等,可使用此接口对优惠码进行验证
     * @author theworld
     * @version v1
     * @param string param.scene - 优惠码应用场景:new新购,renew续费,upgrade升降级 required
     * @param string param.promo_code - 优惠码 新购时必传
     * @param int param.host_id - 产品ID
     * @param int param.product_id - 商品ID required
     * @param int param.qty - 数量 新购时必传
     * @param int param.amount - 单价 required
     * @param int param.billing_cycle_time - 周期时间 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return float data.discount 1.00 折扣金额
     * @return int data.id - 优惠码ID
     * @return int data.loop - 循环折扣0否1是
     */
    public function apply($param)
    {
        $discount = 0;

        $results = [];

        $param['qty'] = $param['qty']??1;

        if($param['scene']!='upgrade'){
            $OrderItemModel = new OrderItemModel();
            $promoCode = $OrderItemModel->alias('oi')
                    ->leftJoin('addon_promo_code pc','pc.id=oi.rel_id')
                    //->where('oi.order_id',$host->order_id)
                    ->where('oi.host_id',$param['host_id'] ?? 0)
                    ->where('oi.type','addon_promo_code')
                    ->value('code')??'';

            $param['promo_code'] = (isset($param['promo_code']) && !empty($param['promo_code'])) ?$param['promo_code']: $promoCode;
        }

        for ($i=0;$i<$param['qty'];$i++){
            $post = [
                'promo_code' => $param['promo_code'],
                'scene' => $param['scene'],
                'host_id' => $param['host_id'] ?? 0,
                'product_id' => $param['product_id'],
                'amount' => $param['amount'],
                'billing_cycle_time' => $param['billing_cycle_time'],
                'qty' => $param['qty']
            ];
            $result = $this->clientPromoCodeSingleHandle($post);

            $results[] = $result;

            if ($result['status'] == 200){
                // wyh 20240223 增加单用户一次限制
                if ($result['data']['single_user_once']==0 || $i==0){
                    $discount = bcadd($discount,$result['data']['discount']??0,2);
                }
                $id = $result['data']['id']??0;
                $loop = $result['data']['loop']??0;
            }
        }
        # 所有结果都为400,返回400
        if (!in_array(200,array_column($results,'status'))){
            if(!empty($param['promo_code'])){
                return ['status'=>400,'msg'=>$results[0]['msg']?:lang_plugins('fail_message')];
            }else{
                $discount = 0;
            }
        }

        $data = [
            'discount' => $discount,
            'id' => $id??0,
            'loop' => $loop??0
        ];

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>$data];
    }

    /**
     * 时间 2022-10-20
     * @title 处理单个优惠码
     * @desc 处理单个优惠码
     * @author theworld
     * @version v1
     * @param string param.scene - 优惠码应用场景:new新购,renew续费,upgrade升降级 required
     * @param string param.promo_code - 优惠码 新购时必传
     * @param int param.host_id - 产品ID
     * @param int param.product_id - 商品ID required
     * @param int param.amount - 单价 required
     * @param int param.billing_cycle_time - 周期时间 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return float data.discount 1.00 折扣金额
     * @return float data.discount2 - 续费折扣金额
     * @return int data.id - 优惠码ID
     * @return int data.loop - 循环折扣0否1是
     */
    private function clientPromoCodeSingleHandle($param)
    {
        # 判断优惠码
        $param['promo_code'] = $param['promo_code'] ?? '';
        if (empty($param['promo_code']) && !isset($param['host_id'])){
            return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
        }else if(empty($param['promo_code']) && isset($param['host_id'])){
            if($param['scene']=='new'){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
            }
            if($param['scene']=='renew'){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
            }
            $log = PromoCodeLogModel::where('host_id', $param['host_id'])->where('scene', 'new')->find();
            if(!empty($log)){
                $promoCode = $this->where('id',$log['addon_promo_code_id'])->where('delete_time',0)->find();
                if(!empty($promoCode)){
                    $promoCode = $promoCode->toArray();
                    if($promoCode['type']=='fixed_amount' || $promoCode['type']=='replace_price'){
                        $promoCode['upgrade'] = 0;
                        $promoCode['host_upgrade'] = 0;
                        $promoCode['renew'] = 0;
                        $promoCode['loop'] = 0;
                    }else if($promoCode['type']=='free'){
                        $promoCode['host_upgrade'] = 0;
                        $promoCode['loop'] = 0;
                    }
                    if($param['scene']=='upgrade' && $promoCode['host_upgrade']!=1){
                        return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
                    }/*else if($param['scene']=='renew' && $promoCode['loop']!=1){
                        return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
                    }*/
                }else{
                    return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
                }
            }else{
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
            }
        }else{
            $promoCode = $this->where('code',$param['promo_code'])->where('delete_time',0)->find();
            if (empty($promoCode)){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
            } 
            $promoCode = $promoCode->toArray();
            if($promoCode['type']=='fixed_amount' || $promoCode['type']=='replace_price'){
                $promoCode['upgrade'] = 0;
                $promoCode['host_upgrade'] = 0;
                $promoCode['renew'] = 0;
                $promoCode['loop'] = 0;
            }else if($promoCode['type']=='free'){
                $promoCode['host_upgrade'] = 0;
                $promoCode['loop'] = 0;
            }
        }

        $clientId = isset($param['client_id'])?intval($param['client_id']):get_client_id();

        $time = time();

        $amount = floatval($param['amount']);

        // wyh 20231219增 升降级时，未填写优惠码，判断不到优惠码使用范围
        if ($param['scene']=='upgrade' && !empty($promoCode)){
            $param['promo_code'] = $promoCode['code']??'';
        }

        if(!empty($param['promo_code'])){
            if ($promoCode['status'] == 0){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_has_expired')];
            }

            # 优惠码有效时间
            if ($promoCode['start_time'] > $time){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_not_found')];
            }
            if ($promoCode['end_time']>0 && $promoCode['end_time'] < $time){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_has_expired')];
            }

            $host = HostModel::where('client_id', $clientId)->column('product_id');

            # 优惠码适用客户
            if ($promoCode['client_type'] == 'new' && !empty($host)){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_only_new_client')];
            }

            $active = HostModel::where('client_id', $clientId)->where('status', 'Active')->column('product_id');

            if ($promoCode['client_type'] == 'old' && empty($active)){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_only_old_client')];
            }

            $count = PromoCodeLogModel::where('client_id', $clientId)->where('addon_promo_code_id', $promoCode['id'])->count();
            if($count>0 && $promoCode['single_user_once']==1){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_has_expired')];
            } 

            # 优惠码使用次数
            if (!empty($promoCode['max_times'])){
                if ($promoCode['used'] >= $promoCode['max_times']){
                    return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_has_expired')];
                }
            }

            # 判断适用场景
            $scene = $param['scene'];
            if($scene=='upgrade' && $promoCode['upgrade']!=1){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_upgrade_cannot_use')];
            }else if($scene=='renew' && $promoCode['renew']!=1){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_renew_cannot_use')];
            }

            # 判断适用场景时长(新规则)
            $billingCycleTime = intval($param['billing_cycle_time']);
            $cycle = explode(',',$promoCode['cycle']) ?: [];
            if ($promoCode['cycle_limit']==1){
                # 排除最大值
                $flag = false;
                foreach ($cycle as $v){
                    if(isset($this->applySceneTime[$v])){
                        if($this->applySceneTime[$v]['min']<=$billingCycleTime && $billingCycleTime<=$this->applySceneTime[$v]['max']){
                            $flag = true;
                            break;
                        }
                    }
                }

                if (!$flag){
                    return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_the_condition_cannot_use')];
                }
            }

            # 判断商品适用
            $productIds = PromoCodeProductModel::where('addon_promo_code_id',$promoCode['id'])->column('product_id');
            if (!empty($productIds) && !in_array($param['product_id'],$productIds)){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_product_cannot_use')];
            }



            $needProductIds = PromoCodeProductNeedModel::where('addon_promo_code_id',$promoCode['id'])->column('product_id');
            if (!empty($needProductIds) && empty(array_intersect($active,$needProductIds))){
                return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_the_condition_cannot_use')];
            }
        }

        if ($promoCode['type'] == 'replace_price' && $promoCode['value']>$amount){
            return ['status'=>400,'msg'=>lang_plugins('addon_promo_code_higher_cannot_use')];
        }

        if ($promoCode['type'] == 'percent'){ # 百分比
            $discount = bcdiv($amount*$promoCode['value'], 100, 2);
        }else if ($promoCode['type'] == 'fixed_amount'){ # 固定金额
            $discount = $promoCode['value']>$amount ? $amount : $promoCode['value'];
        }else if ($promoCode['type'] == 'replace_price'){ # 替换价格
            $discount = bcsub($amount, $promoCode['value'], 2)>0 ? bcsub($amount, $promoCode['value'], 2) : 0;
        }else if ($promoCode['type'] == 'free'){ # 免费
            $discount = $amount;
        }

        if(isset($param['host_id']) && !empty($param['host_id']) && $param['scene']=='new'){
            $host = HostModel::find($param['host_id']);

            // wyh 20230509 在原来基础之上优惠
            $OrderItemModel = new OrderItemModel();
            $orderItem = $OrderItemModel->where('host_id',$param['host_id'])
                ->where('type','host')
                ->where('rel_id',$param['host_id'])
                ->find();
            $host['renew_amount'] = $orderItem['amount'];

            if(!empty($host) && $promoCode['loop']==1){
                if ($promoCode['type'] == 'percent'){ # 百分比
                    $discount2 = bcdiv($host['renew_amount']*$promoCode['value'], 100, 2);
                }else if ($promoCode['type'] == 'fixed_amount'){ # 固定金额
                    $discount2 = $promoCode['value']>$host['renew_amount'] ? $host['renew_amount'] : $promoCode['value'];
                }else if ($promoCode['type'] == 'replace_price'){ # 替换价格
                    $discount2 = bcsub($host['renew_amount'], $promoCode['value'], 2)>0 ? bcsub($host['renew_amount'], $promoCode['value'], 2) : 0;
                }else if ($promoCode['type'] == 'free'){ # 免费
                    $discount2 = $host['renew_amount'];
                }
            }
        }

        // 20230601 加
        //$discount = $discount<0?-$discount:$discount;

        $data = [
            'id' => $promoCode['id'], # 优惠码ID
            'discount' => $discount, # 优惠金额
            'discount2' => $discount2 ?? 0, # 续费优惠金额
            'loop' => $promoCode['loop'],
            'single_user_once' => $promoCode['single_user_once']
        ];

        return ['status'=>200,'msg'=>lang_plugins('promo_code_apply_success'),'data'=>$data];
    }

    /**
     * 时间 2022-10-20
     * @title 订单创建后
     * @desc 订单创建后
     * @author theworld
     * @version v1
     * @param int param.id - 订单ID
     * @param object param.customfield - 自定义字段
     * @param int param.customfield.promo_code - 优惠码
     * @param array param.customfield.host_customfield - 产品自定义字段
     * @param int param.customfield.host_customfield[].id - 产品ID
     * @param int param.customfield.host_customfield[].customfield.promo_code - 优惠码
     * @return bool
     */
    public function afterOrderCreate($param)
    {
        $orderId = $param['id']??0;

        $OrderModel = new OrderModel();
        $order = $OrderModel->find($orderId);
        if (empty($order)){
            return false;
        }

        $promoCode = $promoCodeParam = $param['customfield']['promo_code']??'';
        $promoCode = is_string($promoCode) ? $promoCode : '';

        $hostPosition = $param['customfield']['host_customfield']??[];
        if(is_array($hostPosition) && !empty($hostPosition)){
            $hostPromoCode = [];
            foreach ($hostPosition as $key => $value) {
                $hostPromoCode[$value['id']] = $value['customfield']['promo_code'] ?? '';
            }
            $promoCode = '';
        }  

        /*if (empty($promoCode)){
            return false;
        }*/

        $OrderItemModel = new OrderItemModel();

        $orderItems = $OrderItemModel->alias('oi')
            ->field('oi.client_id,oi.order_id,oi.host_id,oi.amount,oi.product_id,h.billing_cycle_time,oi.type,oi.rel_id')
            ->leftJoin('host h','h.id=oi.host_id AND h.is_delete=0')
            ->where('oi.order_id',$orderId)
            ->whereIn('oi.type',['host','renew','upgrade']) # 仅新购/续费/升降级可使用优惠码
            ->select()
            ->toArray();
        if (empty($orderItems)){
            return false;
        }
        $items = [];

        $discountTotal = 0;

        foreach ($orderItems as $orderItem){
            $productId = $orderItem['product_id'];
            $promoCodeExist = $OrderItemModel->alias('oi')
                    ->leftJoin('addon_promo_code pc','pc.id=oi.rel_id')
                    //->where('oi.order_id',$host->order_id)
                    ->where('oi.host_id',$orderItem['host_id'])
                    ->where('oi.type','addon_promo_code')
                    ->value('code')??'';
            if($orderItem['type']!='upgrade'){
                $promoCode = !empty($promoCode)?$promoCode:$promoCodeExist;
            }
            
            // wyh 20230509 取续费金额的优惠码按周期原价来算
            if ($orderItem['type']=='renew'){
                $HostModel = new HostModel();
                $host = $HostModel->find($orderItem['host_id']);
                $ModuleLogic = new ModuleLogic();
                $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
                if($upstreamProduct){
                    $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                    $result = $ResModuleLogic->durationPrice($host);
                }else{
                    $result = $ModuleLogic->durationPrice($host);
                }
                $cycles = $result['data']??[];
                $IdcsmartRenewModel = new IdcsmartRenewModel();
                $renew = $IdcsmartRenewModel->find($orderItem['rel_id']);
                # 获取金额
                foreach ($cycles as $value){
                    if ($renew['new_billing_cycle'] == $value['billing_cycle']){
                        $amount = $value['price'];
                        break; # 只取一个值(存在开发者在模块中把周期写一样的情况)
                    }
                }
                $orderItem['amount'] = $amount??0;
            }
            // TODO 20231101升降级产品按原价计算
            if ($orderItem['type']=='upgrade'){
                $UpgradeModel = new UpgradeModel();
                $upgradeProduct = $UpgradeModel->where('id',$orderItem['rel_id'])->where('type','product')->find();
                if (!empty($upgradeProduct)){
                    $orderItem['amount'] = $upgradeProduct['price'];
                }
            }

            $data = [
                'order_id'=>$orderItem['order_id'],
                'client_id'=>$orderItem['client_id'],
                'host_id'=>$orderItem['host_id'],
                'product_id'=>$productId,
                'scene'=>$order['type'], # 订单类型
                'promo_code'=>$promoCode,
                'amount'=>$orderItem['amount'], # 单个产品金额
                'billing_cycle_time'=>intval($orderItem['billing_cycle_time'])
            ];
            $data['promo_code'] = $hostPromoCode[$orderItem['host_id']] ?? $promoCode;

            $result = $this->clientPromoCode($data);
            if ($result['status']==200){
                // wyh 20240313 修改 传了优惠码 || 续费时更换了周期，且旧优惠码可循环
                if (($promoCodeExist && $orderItem['type']=='upgrade') || !empty($promoCodeParam) || ($promoCodeExist && isset($result['data']['loop']) && $result['data']['loop']==1 && $orderItem['type']=='renew' && $host['billing_cycle_name']!=$renew['new_billing_cycle'])){
                    $discount = $result['data']['discount'];
                    if ($orderItem['type']!='renew'){
                        $discountTotal = bcadd($discountTotal,$discount,2);
                    }
                    foreach ($result['data']['order_items'] as $item){
                        $item['order_id'] = $orderId;
                        $item['client_id'] = $orderItem['client_id'];
                        $item['create_time'] = time();
                        $items[] = $item;
                    }
                }
            }
        }
        if(!empty($items)){
            $OrderItemModel->insertAll($items);

            $amount = bcsub($order['amount'],$discountTotal,2)>0?bcsub($order['amount'],$discountTotal,2):0;
            $order->save([
                'amount' => $amount,
                'status' => $amount>0 ? 'Unpaid' :'Paid', # 金额为0,修改为已支付状态
                'amount_unpaid' => $amount,
                'pay_time' => $amount>0 ? 0 : time(),
            ]);

            $ClientModel = new ClientModel();
            $client = $ClientModel->find($order['client_id']);
            if(is_array($promoCode) && !empty($promoCode)){
                $code = $this->where('code',$promoCode[0])->find();
                active_log(lang_plugins('promo_code_client_use_promo_code',['{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{promo_code}'=>implode(',',$promoCode),'{order_id}'=>$orderId]),'promo_code',$code->id);
            }else if(!empty($promoCode)){
                $code = $this->where('code',$promoCode)->find();
                active_log(lang_plugins('promo_code_client_use_promo_code',['{client}'=>'client#'.$client['id'].'#'.$client['username'].'#','{promo_code}'=>$promoCode,'{order_id}'=>$orderId]),'promo_code',$code->id);
            }
        }

        return true;
    }

    /**
     * 时间 2022-10-20
     * @title 每日定时任务
     * @desc 每日定时任务,启用/停用优惠码
     * @author theworld
     * @version v1
     * @return bool
     */
    public function dailyCron()
    {
        # 1、启用/停用优惠码
        $promoCodes = $this->select()
            ->toArray();
        $time = time();
        foreach ($promoCodes as $promoCode){
            if(!empty($promoCode['end_time'])){
                $status = $promoCode['status'];
                if ($promoCode['start_time']<=$time){
                    $status = 1;
                }
                if ($promoCode['end_time']<=$time){
                    $status = 0;
                }
                if ($promoCode['status'] != $status){
                    $this->update([
                        'status' => $status,
                        'update_time' => $time
                    ],['id'=>$promoCode['id']]);
                }
            }
        }
        return true;
    }

    /**
     * 时间 2022-10-20
     * @title 产品内页获取优惠码信息
     * @desc 产品内页获取优惠码信息
     * @author theworld
     * @version v1
     * @param int param.id - 产品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return array data.promo_code - 优惠码
     */
    public function hostPromoCode($param)
    {
        $id = $param['id']??0;

        $HostModel = new HostModel();

        $host = $HostModel->where('id',$id)->where('client_id',get_client_id())->find();

        if (empty($host)){
            return ['status'=>400,'msg'=>lang_plugins('promo_code_host_is_not_exist')];
        }

        $OrderItemModel = new OrderItemModel();
        $relIds = $OrderItemModel->where('host_id',$id)
            ->where('type',$this->name)
            ->column('rel_id');

        $promoCodes = $this->whereIn('id',$relIds)
            ->column('code');

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['promo_code'=>$promoCodes??[]]];
    }
}
