<?php
namespace certification\idcsmartali;

use addon\idcsmart_certification\model\CertificationLogModel;
use app\common\lib\Plugin;

use app\common\model\ClientModel;
use certification\idcsmartali\logic\IdcsmartaliLogic;
use think\facade\Db;
require_once 'phpqrcode/phpqrcode.php';

class Idcsmartali extends Plugin
{
    # 基础信息
    public $info = array(
        'name'        => 'Idcsmartali',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '智简魔方-芝麻信用',
        'description' => '智简魔方-芝麻信用',
        'status'      => 1,
        'author'      => '智简魔方',
        'version'     => '1.0.0',
        'help_url'    => 'https://my.idcsmart.com/goods.html?id=817'
    );

    # 插件安装
    public function install()
    {
        return true;//安装成功返回true，失败false
    }

    # 插件卸载
    public function uninstall()
    {
        return true;//卸载成功返回true，失败false
    }

    # 个人认证
    public function IdcsmartaliPerson($certifi)
    {
        # 自定义字段 自行操作
        if ($certifi['card_type']==1){
            $cert_type = 'IDENTITY_CARD';
        }elseif ($certifi['card_type']==2){
            $cert_type = 'HOME_VISIT_PERMIT_HK_MC';
        }elseif ($certifi['card_type']==3){
            $cert_type = 'HOME_VISIT_PERMIT_TAIWAN';
        }elseif ($certifi['card_type']==4){
            $cert_type = 'RESIDENCE_PERMIT_HK_MC';
        }elseif ($certifi['card_type']==5){
            $cert_type = 'RESIDENCE_PERMIT_TAIWAN';
        }else{
            return "<h3 class=\"pt-2 font-weight-bold h2 py-4\"><img src=\"\" alt=\"\">接口不支持该认证类型</h3>";
        }

        $IdcsmartaliLogic = new IdcsmartaliLogic();
        $res1 = $IdcsmartaliLogic->getCertifyId($certifi['name'],$certifi['card'],$cert_type);

        $data = [
            'status' => 4,
            'auth_fail' => '',
            'certify_id' => '',
            'notes' => '', # 其他信息:这里可以存储自定义的实名认证返回数据,后台实名认证详情可查看
            'refresh' => 0, # 此字段解决一些插件如支付宝实名认证时，验证页面除了通过，即status=1时才刷新页面；其他的都不刷新验证页面。默认刷新1，0不刷新
        ];
        $clientId = get_client_id();
        if ($res1['status'] == 200){
            $certify_id = $res1['certify_id'];
            $data['certify_id'] = $certify_id;
            $res2 = $IdcsmartaliLogic->generateScanForm($certify_id);
            $url = $res2['url'];

            # 其他信息
            $time = date('Y-m-d H:i:s',time());
            $data['notes'] = "支付宝记录号:{$certify_id};\r\n"."实名认证方式:{$this->info['title']};\r\n"."实名认证接口提交时间:{$time}\r\n";

            $response = \QRcode::png($url,false,0,4,5,false); # 这里需要修改扩展库的代码
            $base64 = 'data:png;base64,' . base64_encode($response->getData());
            $data['client_id'] = $clientId;
            hook('update_certification_person',$data);

            $CertificationLogModel = new CertificationLogModel();
            $log = $CertificationLogModel->where('client_id',$clientId)
                ->where('type',1)
                ->order('id','desc')
                ->find();

            $ClientModel = new ClientModel();
            $client = $ClientModel->find($clientId);

            $html = "
            <div class='thirdBox-left'>
            <div class='left-box1'>
            <p>
                <span class='left-title'>用户名：</span>
                <span>{$client['username']}</span>
            </p>
            <p>
                <span class='left-title'>认证姓名：</span>
                <span>{$log['card_name']}</span>
            </p>
            <p>
                <span class='left-title'>认证号码：</span>
                <span>{$log['card_number']}</span>
            </p>
            </div>
            <div id='contentBox'>
                <img height='200' width='200' src=\"{$base64}\" alt=\"\">
            </div>
            <div class='left-box2'>
                <div class='sao-icon-box'>
                    <img src='/plugins/addon/idcsmart_certification/template/clientarea/img/account/sao-icon.png' alt=''>
                </div>
                <div class='sao-text'>
                    <p>打开手机支付宝</p>
                    <p>扫一扫继续认证</p>
                </div>
            </div>
        </div>
        <div class='thirdBox-right'>
        <img src='/plugins/addon/idcsmart_certification/template/clientarea/img/account/zfb-img.png' alt=''>
        </div>
            <script src='https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js'></script>
            <script>
                var timer = null
                var captchaTimer = setTimeout(() => { onLoad() }, 500)
                function getIdcsmartaliStatus() {
                    $.ajax({
                        url:'/certification/idcsmartali/index/status?certify_id=".$certify_id."&type=person&client_id=".$clientId."',
                        success:function(result) {
                          if(result.code === 1){
                            clearInterval(timer)
                            timer = null
                          }
                        }
                    })
                }
                function onLoad() {
                    timer = setInterval(() => {
                        getIdcsmartaliStatus()
                    }, 2000)
                }
            </script>
            ";

            # TODO 写js，传$certify_id调/certification/idcsmartali/index/status刷新状态:当code为1时,停止调接口
            return $html;
        }else{
            $data['auth_fail'] = $res1['msg']?:'实名认证接口配置错误,请联系管理员';
            return "<h3 class=\"pt-2 font-weight-bold h2 py-4\"><img src=\"\" alt=\"\">{$data['auth_fail']}</h3>";
        }
    }

    # 企业认证
    public function IdcsmartaliCompany($certifi)
    {
        # 自定义字段 自行操作
        if ($certifi['card_type']==1){
            $cert_type = 'IDENTITY_CARD';
        }elseif ($certifi['card_type']==2){
            $cert_type = 'HOME_VISIT_PERMIT_HK_MC';
        }elseif ($certifi['card_type']==3){
            $cert_type = 'HOME_VISIT_PERMIT_TAIWAN';
        }elseif ($certifi['card_type']==4){
            $cert_type = 'RESIDENCE_PERMIT_HK_MC';
        }elseif ($certifi['card_type']==5){
            $cert_type = 'RESIDENCE_PERMIT_TAIWAN';
        }else{
            return "<h3 class=\"pt-2 font-weight-bold h2 py-4\"><img src=\"\" alt=\"\">接口不支持该认证类型</h3>";
        }
        $IdcsmartaliLogic = new IdcsmartaliLogic();
        $res1 = $IdcsmartaliLogic->getCertifyId($certifi['name'],$certifi['card'],$cert_type);
        $data = [
            'status' => 4,
            'auth_fail' => '',
            'certify_id' => '',
            'notes' => '',
            'refresh' => 0, # 此字段解决一些插件如支付宝实名认证时，验证页面除了通过，即status=1时才刷新页面；其他的都不刷新验证页面。默认刷新1，0不刷新
        ];

        $clientId = get_client_id();
        if ($res1['status'] == 200){
            $certify_id = $res1['certify_id'];
            $data['certify_id'] = $certify_id;
            $res2 = $IdcsmartaliLogic->generateScanForm($certify_id);
            $url = $res2['url'];
            # 其他信息
            $time = date('Y-m-d H:i:s',time());
            $data['notes'] = "支付宝记录号:{$certify_id};\r\n"."实名认证方式:{$this->info['title']};\r\n"."实名认证接口提交时间:{$time}\r\n";

            $response = \QRcode::png($url,false,0,4,5,false); # 这里需要修改扩展库的代码
            $base64 = 'data:png;base64,' . base64_encode($response->getData());
            $data['client_id'] = $clientId;
            hook('update_certification_company',$data);

            $CertificationLogModel = new CertificationLogModel();
            $log = $CertificationLogModel->where('client_id',$clientId)
                ->whereIn('type',[2,3])
                ->order('id','desc')
                ->find();

            $html = "
            <div class='thirdBox-left'>
            <div class='left-box1'>
            <p>
                <span class='left-title'>认证企业：</span>
                <span>{$log['company']}</span>
            </p>
            <p>
                <span class='left-title'>企业信用代码：</span>
                <span>{$log['company_organ_code']}</span>
            </p>
            </div>
            <div id='contentBox'>
                <img height='200' width='200' src=\"{$base64}\" alt=\"\">
            </div>
            <div class='left-box2'>
                <div class='sao-icon-box'>
                    <img src='/plugins/addon/idcsmart_certification/template/clientarea/img/account/sao-icon.png' alt=''>
                </div>
                <div class='sao-text'>
                    <p>打开手机支付宝</p>
                    <p>扫一扫继续认证</p>
                </div>
            </div>
        </div>
        <div class='thirdBox-right'>
        <img src='/plugins/addon/idcsmart_certification/template/clientarea/img/account/zfb-img.png' alt=''>
        </div>
            <script src='https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js'></script>
            <script>
                var timer = null
                var captchaTimer = setTimeout(() => { onLoad() }, 500)
                function getIdcsmartaliStatus() {
                    $.ajax({
                        url:'/certification/idcsmartali/index/status?certify_id=".$certify_id."&type=company&client_id=".$clientId."',
                        success:function(result) {
                          if(result.code === 1){
                            clearInterval(timer)
                            timer = null
                          }
                        }
                    })
                }
                function onLoad() {
                    timer = setInterval(() => {
                        getIdcsmartaliStatus()
                    }, 2000)
                }
            </script>
            ";
            return $html;
        }else{
            $data['auth_fail'] = $res1['msg']?:'实名认证接口配置错误,请联系管理员';
            return "<h3 class=\"pt-2 font-weight-bold h2 py-4\"><img src=\"\" alt=\"\">{$data['auth_fail']}</h3>";
        }
    }

    # 前台自定义字段输出
    public function IdcsmartaliCollectionInfo($type)
    {
        if ($type=='person'){
            $data = [];
        }elseif ($type=='company'){
            $data = [
                'name' => [
                    'title' => '姓名',
                    'type'  => 'text',
                    'value' => '',
                    'tip'   => '',
                    'required'   => true, # 是否必填
                ],
                'card' => [
                    'title' => '身份证号码',
                    'type'  => 'text',
                    'value' => '',
                    'tip'   => '',
                    'required'   => true, # 是否必填
                ],
            ];
        }else{
            $data = [];
        }

        return $data;
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
        $con = require dirname(__DIR__).'/idcsmartali/config/config.php';

        $config = array_merge($con,$config);

        return $config;
    }
}