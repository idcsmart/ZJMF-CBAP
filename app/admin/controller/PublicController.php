<?php
namespace app\admin\controller;

use app\admin\model\AdminModel;
use app\admin\validate\AdminValidate;
use app\common\model\ClientModel;

/**
 * @title 后台开放类
 * @desc 后台开放类,不需要授权
 */
class PublicController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new AdminValidate();
    }

    /**
     * 时间 2022-5-18
     * @title 登录信息
     * @desc 登录信息
     * @url /admin/v1/login
     * @method  get
     * @return int captcha_admin_login - 管理员登录图形验证码开关:1开启,0关闭
     * @return string website_name 智简魔方 网站名称
     * @author wyh
     * @version v1
     */
    public function loginInfo()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'captcha_admin_login' => configuration('captcha_admin_login'),
                'website_name' => configuration('website_name')
            ]
        ];

        return json($result);
    }

    /**
     * 时间 2022-5-13
     * @title 后台登录
     * @desc 后台登录
     * @url /admin/v1/login
     * @method  post
     * @param string name 测试员 用户名 required
     * @param string password 123456 密码 required
     * @param string remember_password 1 是否记住密码(1是,0否) required
     * @param string token d7e57706218451cbb23c19cfce583fef 图形验证码唯一识别码
     * @param string captcha 12345 图形验证码
     * @return object data - 返回数据
     * @return string data.jwt - jwt:登录后放在请求头Authorization里,拼接成如下格式:Bearer+空格+yJ0eX.test.ste
     * @version v1
     * @author wyh
     */
    public function login()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('login')->check($param)) {
            return json(['status' => 400, 'msg' => lang($this->validate->getError())]);
        }

        hook_one('before_admin_login', ['name' => $param['name'] ?? '', 'password' => $param['password'] ?? '', 'remember_password' => $param['remember_password'] ?? '',
            'token' => $param['token'] ?? '', 'captcha' => $param['captcha'] ?? '', 'customfield' => $param['customfield'] ?? []]);

        $result = (new AdminModel())->login($param);

        return json($result);
    }

    /**
     * 时间 2022-5-19
     * @title 图形验证码
     * @desc 图形验证码
     * @url /admin/v1/captcha
     * @method  get
     * @return string html - html文档
     * @version v1
     * @author wyh
     */
    public function captcha()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'html' => get_captcha(true)
            ]
        ];

        return json($result);
    }

    public function test()
    {
        $arr = [500,1,42,34,5,7,235,645,654,6455,62,253,25,2,2453];
        var_dump($this->bubbleSort($arr));die;
        $array = array(1,1);foreach ($array as $k=>$v){ $v = 2;}
        var_dump($array);
        $a = new ClientModel();
        var_dump(11);die;

        $array = [
            1 => [
                'id' => 10,
                'name' => 'wuyuhua'
            ],
            2 => [
                'id' => 20,
                'name' => 'test'
            ],
            3 => [
                'id' => 30,
                'name' => 'teaast'
            ],
        ];

        $array2 = [
            'wuyuhua' => 'aldfjklad',
            'test' => 'aldfjkasdf',
            'teaast' => 'adsfhasjkdf'
        ];

        $result = array_walk_recursive($array,function(&$value,$key,$other){
            $value = $key . ':' . $value . '-' . ($other[$value]??'');
            return $value;
        },$array2);
        var_dump($array,$result);die;

        var_dump(111);die;
        $IdcsmartCommonProductConfigoptionModel = new \server\zjmfapp\model\IdcsmartCommonProductConfigoptionModel();

        $IdcsmartCommonProductConfigoptionSubModel = new \server\zjmfapp\model\IdcsmartCommonProductConfigoptionSubModel();

        $IdcsmartCommonCustomCycleModel = new \server\zjmfapp\model\IdcsmartCommonCustomCycleModel();

        $IdcsmartCommonCustomCyclePricingModel = new \server\zjmfapp\model\IdcsmartCommonCustomCyclePricingModel();

        $ProductModel = new \app\common\model\ProductModel();

        $products = $ProductModel->select()->toArray();

        $ProductModel->startTrans();

        try{
            foreach ($products as $product){
                $productId = $product['id'];
                $customCycles = $IdcsmartCommonCustomCycleModel->alias('cc')
                    ->field('cc.id,cc.name,cc.cycle_time,cc.cycle_unit,ccp.amount,ccp.id as pricing_id')
                    ->leftJoin('module_zjmfapp_custom_cycle_pricing ccp','ccp.custom_cycle_id=cc.id AND ccp.type=\'product\'')
                    ->where('cc.product_id',$productId)
                    ->where('ccp.rel_id',$productId)
                    ->select()
                    ->toArray();
                $configoptions = $IdcsmartCommonProductConfigoptionModel->where('product_id',$productId)->select()->toArray();

                foreach ($customCycles as $customCycle){
                    $totalPrice = $customCycle['amount']??0;
                    foreach ($configoptions as $configoption){
                        $subs = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$configoption['id'])->select()->toArray();
                        foreach ($subs as $sub){
                            $subAmount = $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$customCycle['id'])
                                ->where('rel_id',$sub['id'])
                                ->where('type','configoption')
                                ->value('amount');
                            $totalPrice += $subAmount??0;
                        }
                    }
                    if ($totalPrice<=0){
                        $IdcsmartCommonCustomCycleModel->where('id',$customCycle['id'])->delete();
                        $IdcsmartCommonCustomCyclePricingModel->where('custom_cycle_id',$customCycle['id'])->delete();
                    }
                }
            }

            $ProductModel->commit();
        }catch (\Exception $e){
            $ProductModel->rollback();
            return json([
                'status' => 400,
                'msg' => $e->getMessage()
            ]);
        }

        return json([
            'status' => 200,
            'msg' => lang_plugins('success_message')
        ]);
    }

}