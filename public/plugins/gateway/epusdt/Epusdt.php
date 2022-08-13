<?php
namespace gateway\epusdt;

use app\common\lib\Plugin;
use think\facade\Db;

/**
 * @desc Easy Payment Usdt
 * @author wyh
 * @version 1.0
 * @time 2022-07-22
 */
class Epusdt extends Plugin
{
    // 插件基础信息
    public $info = array(
        'name'        => 'Epusdt', // 必填 插件标识(唯一)
        'title'       => 'Easy Payment Usdt', // 必填 插件显示名称
        'description' => 'Easy Payment Usdt是一个由Go语言编写的私有化部署Usdt支付中间件(Trc20网络),站长或开发者可通过Epusdt提供的http api集成至您的任何系统,无需过多的配置,仅仅依赖mysql和redis,可实现USDT的在线支付和消息回调，这一切在优雅和顷刻间完成', // 必填 插件功能描述
        'author'      => 'idcsmart', // 必填 插件作者
        'version'     => '1.0',  // 必填 插件版本
        'help_url'    => 'https://github.com/assimon/epusdt/blob/master/wiki/BT_RUN.md', // 选填 申请链接
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
        return true;//卸载成功返回true，失败false
    }

    public function EpusdtHandle($param)
    {
        # 订单号
        $outTradeNo = $param['out_trade_no'];
        # 金额
        $totalAmount = $param['finance']['total'];

        $config = $this->Config();
        $signData = [
            'amount' => floatval($totalAmount),
            'notify_url' => $config['notify_url'],
            'order_id' => $outTradeNo,
            'redirect_url' => $config['redirect_url']
        ];
        $signature = $this->epusdtSign($signData,$config['api_auth_token']);
        $signData['signature'] = $signature;
        $result = $this->curlRequest($config['epusdt_url'],json_encode($signData));

        $resData = json_decode($result,true);
        if (!isset($resData['status_code']) || $resData['status_code']!=200){
            return ['status'=>400,'msg'=>$resData['message']??"支付错误"];
        }

        $url = $resData['data']['payment_url'];
        $html = "<a href='$url' target='_blank'>去支付</a>";

        return $html;
    }

    // 获取配置
    public function Config()
    {
        $config = Db::name('plugin')->where('name', $this->info['name'])->value('config');
        if (!empty($config) && $config != "null") {
            $config = json_decode($config, true);
        } else {
            $config = [];
        }
        $con = require dirname(__DIR__).'/epusdt/config/config.php';
        $config = array_merge($con,$config);

        return $config;
    }

    public function epusdtSign($parameter, $signKey)
    {
        ksort($parameter);
        reset($parameter); //内部指针指向数组中的第一个元素
        $sign = '';
        $urls = '';
        foreach ($parameter as $key => $val) {
            if ($val == '') continue;
            if ($key != 'signature') {
                if ($sign != '') {
                    $sign .= "&";
                    $urls .= "&";
                }
                $sign .= "$key=$val"; //拼接为url参数形式
                $urls .= "$key=" . urlencode($val); //拼接为url参数形式
            }
        }
        $sign = md5($sign . $signKey);//密码追加进入开始MD5签名
        return $sign;
    }

    public function curlRequest($url, $data=null, $method='post', $header = array("content-type: application/json"), $https=true, $timeout = 30){
        $method = strtoupper($method);
        $ch = curl_init();//初始化
        curl_setopt($ch, CURLOPT_URL, $url);//访问的URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//只获取页面内容，但不输出
        if($https){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//https请求 不验证证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//https请求 不验证HOST
        }
        if ($method != "GET") {
            if($method == 'POST'){
                curl_setopt($ch, CURLOPT_POST, true);//请求方式为post请求
            }
            if ($method == 'PUT' || strtoupper($method) == 'DELETE') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); //设置请求方式
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//请求数据
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
        //curl_setopt($ch, CURLOPT_HEADER, false);//设置不需要头信息
        $result = curl_exec($ch);//执行请求
        curl_close($ch);//关闭curl，释放资源
        return $result;
    }
}