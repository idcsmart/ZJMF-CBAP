<?php
namespace addon\idcsmart_certification\controller\clientarea;


use addon\idcsmart_certification\logic\IdcsmartCertificationLogic;
use addon\idcsmart_certification\model\CertificationLogModel;
use app\event\controller\PluginBaseController;
use addon\idcsmart_certification\validate\CertificationValidate;

/**
 * @title 实名认证(前台接口)
 * @desc 实名认证(前台接口)
 * @use addon\idcsmart_certification\controller\clientarea\CertificationController
 */
class CertificationController extends PluginBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new CertificationValidate();
        if (!IdcsmartCertificationLogic::getDefaultConfig('certification_open') && request()->action() != 'certificationInfo'){
            echo json_encode(['status'=>400,'msg'=>lang_plugins('certification_is_not_open')]);die;
        }
    }

    /**
     * 时间 2022-9-23
     * @title 获取实名认证信息
     * @desc 获取实名认证信息
     * @url /console/v1/certification/info
     * @method  GET
     * @author wyh
     * @version v1
     * @return int certification_open - 实名认证是否开启:1开启默认,0关
     * @return int certification_company_open - 企业认证是否开启:1开启默认,0关
     * @return int certification_upload - 是否需要上传证件照:1是,0否默认
     * @return int certification_uncertified_cannot_buy_product - 未认证无法购买产品:1是,0否默认
     * @return int is_certification - 是否实名认证:1是,0否默认
     * @return object person - 个人认证信息
     * @return string person.username - 申请人
     * @return string person.company - 公司
     * @return string person.card_name - 姓名
     * @return string person.card_number - 证件号
     * @return int person.create_time - 认证时间
     * @return string person.status - 状态:1已认证，2未通过，3待审核，4已提交资料
     * @return object company - 企业认证信息
     * @return string company.username - 申请人
     * @return string company.company - 公司
     * @return string company.card_name - 姓名
     * @return string company.card_number - 证件号
     * @return string company.certification_company - 实名认证企业
     * @return string company.company_organ_code - 企业代码
     * @return int company.create_time - 认证时间
     * @return string company.status - 状态:1已认证，2未通过，3待审核，4已提交资料
     */
    public function certificationInfo()
    {
        $param = $this->request->param();

        $CertificationLogModel = new  CertificationLogModel();

        $result = $CertificationLogModel->certificationInfo($param);

        return json($result);
    }

    /**
     * @title 实名认证接口
     * @desc 实名认证接口
     * @url /console/v1/certification/plugin
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 支付接口
     * @return int list[].id - ID
     * @return string list[].title - 名称
     * @return string list[].name - 标识
     * @return string list[].url - 图片:base64格式
     * @return array list[].certification_type - 接口支持的类型:person个人,company企业
     * @return int count - 总数
     */
    public function certificationPlugin()
    {
        $data = certification_list();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];

        return json($result);
    }

    /**
     * 时间 2022-9-23
     * @title 获取实名认证自定义字段
     * @desc 获取实名认证自定义字段
     * @url /console/v1/certification/custom_fields
     * @method  get
     * @author wyh
     * @version v1
     * @param string name - 实名接口标识 required
     * @param string type - 验证类型:person个人,company企业 required
     * @return array custom_fields - 自定义字段
     * @return string custom_fields.title - 名称
     * @return string custom_fields.type -  字段类型:text文本,select下拉,file文件
     * @return string custom_fields.options - 字段类型为checkbox复选框,select下拉,radio单选时的选项:选项也是键值,传键
     * @return string custom_fields.tip - 提示
     * @return string custom_fields.required - 是否必填:bool
     * @return string custom_fields.field - 字段名,提交时的键值
     */
    public function certificationCustomfields()
    {
        $param = $this->request->param();

        $CertificationLogModel = new  CertificationLogModel();

        $data = $CertificationLogModel->getCertificationCustomFields($param['name']??'',$param['type']??'');

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'custom_fields' => $data
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2022-9-23
     * @title 个人认证
     * @desc 个人认证
     * @url /console/v1/certification/person
     * @method  post
     * @author wyh
     * @version v1
     * @param string plugin_name - 实名接口 required
     * @param string card_name - 姓名 required
     * @param string card_type - 证件类型:1身份证、2港澳通行证、3台湾通行证、4港澳居住证、5台湾居住证、6海外护照、7中国以外驾照、8其他 required
     * @param string card_number - 证件号码 required
     * @param string phone - 手机号
     * @param string img_one - 身份证正面照,调系统上传文件接口(console/v1/upload获取到savename)
     * @param string img_two - 身份证反面照,调系统上传文件接口(console/v1/upload获取到savename)
     * @param object custom_fields - 其他自定义字段,例{"cert_type":"IDENTITY_CARD"},文件类型先调系统上传文件接口(console/v1/upload获取到savename),
     */
    public function certificationPerson()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('create_person')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $CertificationLogModel = new  CertificationLogModel();

        $result = $CertificationLogModel->certificationPerson($param);

        return json($result);
    }

    /**
     * 时间 2022-9-24
     * @title 企业认证
     * @desc 企业认证
     * @url /console/v1/certification/company
     * @method  post
     * @author wyh
     * @version v1
     * @param string plugin_name - 实名接口 required
     * @param string card_name - 姓名 required
     * @param string card_type - 证件类型:1身份证、2港澳通行证、3台湾通行证、4港澳居住证、5台湾居住证、6海外护照、7中国以外驾照、8其他 required
     * @param string card_number - 证件号码 required
     * @param string company - 公司 required
     * @param string company_organ_code - 公司代码 required
     * @param string phone - 手机号
     * @param string img_one - 身份证正面照,调系统上传文件接口(console/v1/upload获取到savename)
     * @param string img_two - 身份证反面照,调系统上传文件接口(console/v1/upload获取到savename)
     * @param string img_three - 营业执照,调系统上传文件接口(console/v1/upload获取到savename)
     * @param object custom_fields - 其他自定义字段,例{"cert_type":"IDENTITY_CARD"},文件类型先调系统上传文件接口(console/v1/upload获取到savename),
     */
    public function certificationCompany()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('create_company')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $CertificationLogModel = new  CertificationLogModel();

        $result = $CertificationLogModel->certificationCompany($param);

        return json($result);
    }

    /**
     * 时间 2022-9-24
     * @title 个人转企业
     * @desc 个人转企业
     * @url /console/v1/certification/convert
     * @method  post
     * @author wyh
     * @version v1
     * @param string plugin_name - 实名接口 required
     * @param string card_name - 姓名 required
     * @param string card_type - 证件类型:1身份证、2港澳通行证、3台湾通行证、4港澳居住证、5台湾居住证、6海外护照、7中国以外驾照、8其他 required
     * @param string card_number - 证件号码 required
     * @param string company - 公司 required
     * @param string company_organ_code - 公司代码 required
     * @param string phone - 手机号
     * @param string img_one - 身份证正面照,调系统上传文件接口(console/v1/upload获取到savename)
     * @param string img_two - 身份证反面照,调系统上传文件接口(console/v1/upload获取到savename)
     * @param string img_three - 营业执照,调系统上传文件接口(console/v1/upload获取到savename)
     * @param object custom_fields - 其他自定义字段,例{"cert_type":"IDENTITY_CARD"},文件类型先调系统上传文件接口(console/v1/upload获取到savename),
     */
    public function certificationConvert()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('create_company')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $CertificationLogModel = new  CertificationLogModel();

        $param['convert'] = 1;

        $result = $CertificationLogModel->certificationCompany($param);

        return json($result);
    }

    /**
     * 时间 2022-9-24
     * @title 实名认证验证页面
     * @desc 实名认证验证页面
     * @url /console/v1/certification/auth
     * @method  get
     * @author wyh
     * @version v1
     * @return array
     * @return string code - status==400时,返回data.code：code==10000时,重定向至提交资料页面;code==10001时,调基础信息/console/v1/certification/info,并加载相应页面,比如已通过页面/待审核页面/未通过页面
     * @return string html - status==200时,返回data.html文档,由实名接口放回(返回页面正确,默认认证方式的html里需要轮询调接口,/certification/idcsmartali/index/status获取状态);同时轮询调系统状态接口/console/v1/certification/status
     */
    public function certificationAuth()
    {
        $CertificationLogModel = new  CertificationLogModel();

        $result = $CertificationLogModel->certificationAuth();

        return json($result);
    }

    /**
     * 时间 2022-9-24
     * @title 获取实名认证状态
     * @desc 获取实名认证状态,在验证页面轮询调用
     * @url /console/v1/certification/status
     * @method  get
     * @author wyh
     * @version v1
     * @return array
     * @return int status - 当status==400时,表示无认证信息,直接跳转至提交资料页面;
     * @return string code - 当status==200,code:1通过,2未通过,3待审核,4提交资料;code==2,refersh==0时继续轮询调接口,其他所有情况都终止轮询;
     */
    public function certificationStatus()
    {
        $CertificationLogModel = new  CertificationLogModel();

        $result = $CertificationLogModel->certificationStatus();

        return json($result);
    }

    /**
     * @title 实名认证接口配置
     * @desc 实名认证接口配置
     * @url /console/v1/certification/plugin/config
     * @time 2024-06-07
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
    public function certificationConfig()
    {
        $param = $this->request->param();

        $CertificationLogModel = new  CertificationLogModel();

        $data = $CertificationLogModel->certificationConfig($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];

        return json($result);
    }

    /**
     * @title 生成实名认证订单
     * @desc 生成实名认证订单
     * @url /console/v1/certification/plugin/order
     * @time 2024-06-11
     * @method  POST
     * @author wyh
     * @version v1
     * @param string name - 实名接口标识 required
     * @param string type - 验证类型:person个人,company企业 required
     * @return int order_id - 订单ID
     */
    public function certificationOrder()
    {
        $param = $this->request->param();

        $CertificationLogModel = new  CertificationLogModel();

        $result = $CertificationLogModel->certificationOrder($param);

        return json($result);
    }



}