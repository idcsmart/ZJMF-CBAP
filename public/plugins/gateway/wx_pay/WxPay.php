<?php
namespace gateway\wx_pay;

use app\common\lib\Plugin;
use gateway\wx_pay\validate\WxPayValidate;
use gateway\wx_pay\lib\WxPayConfig;
use gateway\wx_pay\lib\WxPayUnifiedOrder;
use gateway\wx_pay\lib\WxPayApi;

require_once __DIR__ . '/lib/WxPayApi.php';
require_once __DIR__ . '/lib/WxPayData.php';
require_once __DIR__ . '/lib/WxPayConfig.php';

class WxPay extends Plugin
{
    // 插件基础信息
    public $info = array(
        'name'        => 'WxPay', // 必填 插件标识(唯一)
        'title'       => '微信支付', // 必填 插件显示名称
        'description' => '微信支付', // 必填 插件功能描述
        'author'      => '智简魔方', // 必填 插件作者
        'version'     => '1.0.0',  // 必填 插件版本
        'help_url'    => '', // 选填 申请链接
        'author_url'  => '', // 选填 作者链接
        'url'         => '', // 选填 图标地址(可以自定义支付图片地址)
    );

    // 临时订单生成规则,1:毫秒时间戳+8位随机数(21-22位长度订单号,默认规则),2:时间戳+8位随机数(18位长度订单号),3:10位随机数(10位长度订单号)
    public $orderRule=1;

    // 插件安装
    public function install()
    {
        return true;//安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        // 在这里不要try catch数据库异常，直接抛出上层会处理异常后回滚的
        return true;//卸载成功返回true，失败false
    }

    public function WxPayHandle($param)
    {
        $validate = new WxPayValidate();
        if(!$validate->check($param)){
            return ['status'=>400,'msg'=>$validate->getError()];
        }
        $domain = configuration('website_url');

        $data = [
            'product_id' => $param['out_trade_no'],
            'total_fee' => isset($param['finance']['total'])?$param['finance']['total']*100:0,
            'out_trade_no' => $param['out_trade_no'],
            'notify_url' => "{$domain}/gateway/wx_pay/index/notifyHandle",
            'trade_type' => 'NATIVE',
            'product_name' => $param['product'][0]??'商品',
            'attach' => $param['client']['id'].'@'.$param['finance']['total'], // 组合参数
            'fee_type' => 'CNY', // 境内商户仅支持人名币
        ];

        return $this->setPay($data);
    }

    private function setPay($param)
    {
        $input = new WxPayUnifiedOrder();
        $input->SetBody($param['product_name']); //商品名称
        $input->SetOut_trade_no($param['out_trade_no']); //商品订单号
        $input->SetTotal_fee($param['total_fee']);  //商品价格以分为初始单位
        $input->SetNotify_url($param['notify_url']); //回调地址
        $input->SetTrade_type($param['trade_type']);  //支付方式
        $input->SetProduct_id($param['product_id']); //商品自定义id
        $input->SetAttach($param['attach']); //商品自定义id
        $result = $this->GetPayUrl($input);
        //> 生成二维码
        if($result){
            if (!isset($result['code_url']) || !$result['code_url']){
                return ['status'=>400,'msg'=>$result['return_msg']?:'二维码制作失败'];
            }
            require_once 'phpqrcode/phpqrcode.php';

            $response = \QRcode::png($result['code_url'],false,0,4,5); # 这里需要修改扩展库的代码

            $base64 = 'data:png;base64,' . base64_encode($response->getData());

            return '<img src="'. $base64 .'" alt="" width="250" height="250">';

        }else{
            return ['status'=>400,'msg'=>'二维码制作失败'];
        }
    }

    /**
     * 生成直接支付url，支付url有效期为2小时,模式二
     * @param UnifiedOrderInput $input
     */
    public function GetPayUrl($input)
    {
        if($input->GetTrade_type() == "NATIVE")
        {
            try{
                $config = new WxPayConfig();
                $WxPayApi = new WxPayApi();
                $result = $WxPayApi->unifiedOrder($config, $input);
                return $result;
            } catch(\Exception $e) {
                var_dump($e->getMessage());die;
            }
        }
        return false;
    }

}