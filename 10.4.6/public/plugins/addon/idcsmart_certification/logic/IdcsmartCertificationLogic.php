<?php
namespace addon\idcsmart_certification\logic;

use addon\idcsmart_certification\IdcsmartCertification;
use addon\idcsmart_certification\model\CertificationLogModel;
use app\admin\model\PluginModel;
use app\common\model\OrderItemModel;
use app\common\model\OrderModel;

class IdcsmartCertificationLogic
{
    # 默认配置
    public static function getDefaultConfig($name = '')
    {
        $fileConfig = include dirname(__DIR__) . '/config/config.php';

        $dbConfig = (new IdcsmartCertification())->getConfig();

        $config = array_merge($fileConfig?:[],$dbConfig?:[]);

        return isset($config[$name])?$config[$name]:$config;
    }

    public function getConfig()
    {
        $PluginModel = new PluginModel();
        $plugin = $PluginModel->where('name','IdcsmartCertification')
            ->where('module','addon')
            ->find();
        return json_decode($plugin['config']??(object)[],true);
    }

    public function setConfig($param)
    {
        $old = $this->getConfig();
        $new = $param;
        $description = [];
        foreach ($old as $key=>$value){
            if (isset($new[$key]) && ($value != $new[$key])){
                $value = !empty($value) ? lang_plugins('addon_idcsmart_certification_open') : lang_plugins('addon_idcsmart_certification_close');
                $new[$key] = !empty($new[$key]) ? lang_plugins('addon_idcsmart_certification_open') : lang_plugins('addon_idcsmart_certification_close');
                $description[] = lang('log_admin_update_description',['{field}'=>lang_plugins('field_idcsmart_certification_'.$key),'{old}'=>$value,'{new}'=>$new[$key]]);
            }
        }

        $description = implode(',', $description);

        $PluginModel = new PluginModel();
        $PluginModel->where('name','IdcsmartCertification')
            ->where('module','addon')
            ->update([
                'config' => json_encode($param),
                'update_time' => time()
            ]);

        if(!empty($description)){
            # 记录日志
            active_log(lang_plugins('idcsmart_certification_update_config', ['{admin}'=>request()->admin_name, '{description}'=>$description]), 'admin', request()->admin_id);
        }
        return true;
    }

    /**
     * @title 实名认证接口配置
     * @desc 实名认证接口配置
     * @method  GET
     * @author wyh
     * @version v1
     * @param string name - 实名接口标识 required
     * @param string type - 验证类型:person个人,company企业 required
     * @return array  -
     * @return int free - 免费次数
     * @return float amount - 金额
     * @return int pay - 是否需要支付，1是0否
     * @return object order - 订单
     * @return int order.id - 订单ID
     * @return int order.status - 状态Paid已付款Unpaid未付款Cancelled已取消
     * @return int order.url - 跳转地址
     * @return int order.amount - 订单金额
     */
    public function checkPayData($param)
    {
        $name = $param['name']??"";

        $type = $param['type']??"";

        $clientId = $param['client_id']??0;

        $name = parse_name($name,1);

        $class = get_plugin_class($name, 'certification');

        $method = 'getConfig';

        if (method_exists($class,$method)){
            $obj = new $class;
            $config = $obj->$method();
        }

        $free = isset($config['free'])?intval($config['free']):0;

        $amount = isset($config['amount'])?floatval($config['amount']):floatval(0);
        // 是否需要支付(后台认证不需要支付)
        $pay = 0;
        $CertificationLogModel = new CertificationLogModel();
        $count = $CertificationLogModel->where('card_type',1)
            ->where('status','<>',4)
            ->where('client_id',$clientId)
            ->where('plugin_name',$name)
            ->count();
        if (($free==0 || ($free>0 && $count>$free)) && $amount>0){
            $pay = 1;
        }

        // 判断是否已有订单，并且是否已支付
        if ($type=='person'){
            $certificationType = 'certification_person';
        }else{
            $certificationType = 'certification_company';
        }
        /*
         * 获取最新的实名认证订单
         * 1、存在订单ID，并且未支付status==Unpaid，调支付接口支付
         * 2、存在订单ID，并且已支付status==Paid(未提交资料)，直接下一步
         * 3、不存在订单ID，且pay==1，表示需要生成订单，调生成实名认证订单接口，再调支付接口
         * 4、不存在订单ID，且pay==0，直接下一步
         * */
        $OrderItemModel = new OrderItemModel();
        $order = $OrderItemModel->alias('oi')
            ->field('o.status,o.id,o.return_url,o.amount')
            ->leftJoin('order o','oi.order_id=o.id')
            ->where('oi.client_id',$clientId)
            ->where('oi.type',$certificationType)
            ->where('oi.rel_id',0) // 表示可能存在订单，并且未进入实名资料提交这一步
            ->order('oi.id','desc')
            ->find();

        $data = [
            'free' => $free,
            'amount' => $amount,
            'pay' => $pay,
            'order' => $order??(object)[],
        ];

        return $data;
    }

    public function checkPay($param)
    {
        $data = $this->checkPayData($param);

        if (!empty($data['order']) && $data['order']['status']=='Unpaid'){
            throw new \Exception(lang_plugins('certification_order_unpaid'));
        }

        if ($data['pay']==1){
            throw new \Exception(lang_plugins('certification_need_pay'));
        }

        return true;
    }

}