<?php
namespace gateway\global_ali_pay;

use app\common\lib\Plugin;
use think\facade\Db;
use gateway\global_ali_pay\validate\AliPayValidate;
use gateway\global_ali_pay\lib\AlipaySubmit;

class GlobalAliPay extends Plugin
{
    public $info = array(
        'name' => 'GlobalAliPay',//Demo插件英文名，改成你的插件英文就行了
        'title' => '支付宝国际支付',
        'description' => '支付宝国际支付',
        'author'      => 'idcsmart',
        'version'     => '1.0',  // 必填 插件版本
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

    public function globalAliPayHandle($param)
    {
        $alipay_config = $this->Config();
        $data = array(
            "service" => $alipay_config['service'],
            "partner" => $alipay_config['partner'],
            "notify_url" => $alipay_config['notify_url'],
            "return_url" => $alipay_config['return_url'],
            "refer_url" => $alipay_config['refer_url'],
        );

        $aliValidate = new AliPayValidate();
        if (!$aliValidate->check($param)) {
            return json(['status' => 400, 'msg' => $aliValidate->getError()]);
        }

        $data['body'] = str_replace('服务费',' Service Fee',isset($param['product'][0])?$param['product'][0]:'商品');
        $data['out_trade_no'] = $param['out_trade_no'];
        $data['subject'] =$data['body'];

        $data['currency'] = $alipay_config['currency']??'HKD';
        $data['total_fee'] = $param['finance']['total'];

        $data['product_code'] = 'NEW_WAP_OVERSEAS_SELLER';//'NEW_OVERSEAS_SELLER';

        //************************************************************/
        $trade_information = array();
        $trade_information['business_type'] =  5; //> 业务类型 当前只支持业务类型为 销售商品
        $trade_information['other_business_type'] =  $data['body'];
        $trade_information = json_encode($trade_information);
        $data['trade_information'] = $trade_information;
        # $data[$trade_information] = 'trade_information';
        $data['_input_charset'] = trim(strtolower($alipay_config['input_charset']));
        $data['qr_pay_mode'] = 4;
        $data['qrcode_width'] = 200;
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $url = $alipaySubmit->buildRequestParaToString($data);

        $html = "<a href='$url' target='_blank'>去支付</a>";
        return $html;
    }

    // 获取配置
    public function config()
    {
        $config = Db::name('plugin')->where('name', $this->info['name'])->value('config');
        if (!empty($config) && $config != "null") {
            $config = json_decode($config, true);
        } else {
            $config = [];
        }
        $con = require dirname(__DIR__).'/global_ali_pay/config/config.php';
        $config = array_merge($con,$config);

        return $config;
    }

}
