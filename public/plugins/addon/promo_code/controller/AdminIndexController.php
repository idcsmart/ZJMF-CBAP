<?php
namespace addon\promo_code\controller;

use addon\promo_code\model\PromoCodeModel;
use addon\promo_code\model\PromoCodeLogModel;
use addon\promo_code\validate\PromoCodeValidate;
use app\event\controller\PluginAdminBaseController;

/**
 * @title 优惠码插件(基础版)
 * @desc 优惠码插件(基础版)
 * @use addon\promo_code\controller\IndexController
 */
class AdminIndexController extends PluginAdminBaseController
{

    public function initialize()
    {
        parent::initialize();
        $this->validate = new PromoCodeValidate();
    }

    /**
     * 时间 2022-10-19
     * @title 优惠码列表
     * @desc 优惠码列表
     * @author theworld
     * @version v1
     * @url /admin/v1/promo_code
     * @method  GET
     * @param string keywords - 关键字搜索:优惠码
     * @param string type - 优惠码类型:percent百分比,fixed_amount固定金额减免,replace_price覆盖价格,free免费
     * @param string status - 状态:Suspended已停用,Active启用中,Expiration已失效,Pending待生效
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,name
     * @param string sort - 升/降序 asc,desc
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
    public function promoCodeList()
    {
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $PromoCodeModel = new PromoCodeModel();

        $data = $PromoCodeModel->promoCodeList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-10-19
     * @title 获取优惠码
     * @desc 获取优惠码
     * @author theworld
     * @version v1
     * @url /admin/v1/promo_code/:id
     * @method  GET
     * @param int id - 优惠码ID required
     * @return object promo_code - 优惠码
     * @return int promo_code.id - ID
     * @return string promo_code.code - 优惠码
     * @return string promo_code.type - 优惠码类型:percent百分比,fixed_amount固定金额减免,replace_price覆盖价格,free免费
     * @return float promo_code.value - 优惠码数值
     * @return string promo_code.client_type - 适用客户:all不限,new无产品用户,old用户必须存在激活中的产品
     * @return int promo_code.start_time - 开始时间
     * @return int promo_code.end_time - 结束时间,为0代表无限
     * @return int promo_code.max_times - 最大使用次数:0不限
     * @return int promo_code.single_user_once - 单用户一次:0关闭,1开启
     * @return int promo_code.upgrade - 升降级:0关闭,1开启
     * @return int promo_code.host_upgrade - 升降级商品配置:0关闭,1开启
     * @return int promo_code.renew - 续费:0关闭,1开启
     * @return int promo_code.loop - 循环优惠:0关闭,1开启
     * @return int promo_code.cycle_limit - 周期限制:0关闭,1开启
     * @return array promo_code.cycle - 周期:monthly月,quarterly季,semiannually半年,annually一年,biennially两年,triennially三年
     * @return string promo_code.notes - 备注
     * @return array promo_code.products - 可应用商品的ID
     * @return array promo_code.need_products - 需求商品的ID
     */
    public function index()
    {
        $param = $this->request->param();

        $PromoCodeModel = new PromoCodeModel();

        $data = $PromoCodeModel->indexPromoCode($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'promo_code' => $data
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-10-19
     * @title 添加优惠码
     * @desc 添加优惠码
     * @author theworld
     * @version v1
     * @url /admin/v1/promo_code
     * @method  POST
     * @param string code - 优惠码 required
     * @param string type - 优惠码类型:percent百分比,fixed_amount固定金额减免,replace_price覆盖价格,free免费 required
     * @param float value - 优惠码数值 类型不为免费时必填
     * @param string client_type - 适用客户:all不限,new无产品用户,old用户必须存在激活中的产品 required
     * @param int start_time - 开始时间 required
     * @param int end_time - 结束时间  
     * @param int max_times - 最大使用次数:0不限 required
     * @param int single_user_once - 单用户一次:0关闭,1开启 required
     * @param int upgrade - 升降级:0关闭,1开启 required
     * @param int host_upgrade - 升降级商品配置:0关闭,1开启 required
     * @param int renew - 续费:0关闭,1开启 required
     * @param int loop - 循环优惠:0关闭,1开启 required
     * @param int cycle_limit - 周期限制:0关闭,1开启 required
     * @param array cycle - 周期:monthly月,quarterly季,semiannually半年,annually一年,biennially两年,triennially三年 周期限制开启时必填
     * @param string notes - 备注
     * @param array products - 可应用商品的ID
     * @param array need_products - 需求商品的ID
     */
    public function create()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $PromoCodeModel = new PromoCodeModel();

        $result = $PromoCodeModel->createPromoCode($param);

        return json($result);
    }

    /**
     * 时间 2022-10-19
     * @title 编辑优惠码
     * @desc 编辑优惠码
     * @author theworld
     * @version v1
     * @url /admin/v1/promo_code/:id
     * @method  PUT
     * @param int id - 优惠码ID required
     * @param string client_type - 适用客户:all不限,new无产品用户,old用户必须存在激活中的产品 required
     * @param int start_time - 开始时间 required
     * @param int end_time - 结束时间  
     * @param int max_times - 最大使用次数:0不限 required
     * @param int single_user_once - 单用户一次:0关闭,1开启 required
     * @param int upgrade - 升降级:0关闭,1开启 required
     * @param int host_upgrade - 升降级商品配置:0关闭,1开启 required
     * @param int renew - 续费:0关闭,1开启 required
     * @param int loop - 循环优惠:0关闭,1开启 required
     * @param int cycle_limit - 周期限制:0关闭,1开启 required
     * @param array cycle - 周期:monthly月,quarterly季,semiannually半年,annually一年,biennially两年,triennially三年 周期限制开启时必填
     * @param string notes - 备注
     * @param array products - 可应用商品的ID
     * @param array need_products - 需求商品的ID
     */
    public function update()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $PromoCodeModel = new PromoCodeModel();

        $result = $PromoCodeModel->updatePromoCode($param);

        return json($result);
    }

    /**
     * 时间 2022-10-19
     * @title 删除优惠码
     * @desc 删除优惠码
     * @author theworld
     * @version v1
     * @url /admin/v1/promo_code/:id
     * @method  DELETE
     * @param int id - 优惠码ID required
     */
    public function delete()
    {
        $param = $this->request->param();

        $PromoCodeModel = new PromoCodeModel();

        $result = $PromoCodeModel->deletePromoCode($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-10-19
     * @title 启用/禁用优惠码
     * @desc 启用/禁用优惠码
     * @author theworld
     * @version v1
     * @url /admin/v1/promo_code/:id/status
     * @method  PUT
     * @param int id - 优惠码ID required
     * @param int status - 状态:0禁用,1启用 required
     */
    public function status()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('status')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $PromoCodeModel = new PromoCodeModel();

        $result = $PromoCodeModel->statusPromoCode($param);

        return json($result);
    }

    /**
     * 时间 2022-10-19
     * @title 获取随机优惠码
     * @desc 获取随机优惠码
     * @author theworld
     * @version v1
     * @url /admin/v1/promo_code/generate
     * @method  GET
     * @return string code - 优惠码
     */
    public function generate()
    {
        $param = $this->request->param();

        $PromoCodeModel = new PromoCodeModel();

        $code = $PromoCodeModel->generatePromoCode();

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'code' => $code
            ]
        ];
        return json($result);
    }
    
    /**
     * 时间 2022-10-19
     * @title 优惠码使用记录
     * @desc 优惠码使用记录
     * @author theworld
     * @version v1
     * @url /admin/v1/promo_code/:id/log
     * @method  GET
     * @param int id - 优惠码ID
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 使用记录列表
     * @return int list[].id - ID
     * @return int list[].client_id - 用户ID
     * @return string list[].username - 用户名
     * @return int list[].order_id - 订单ID
     * @return float list[].amount - 优惠前金额
     * @return float list[].discount - 优惠金额
     * @return int list[].create_time - 使用时间
     * @return int count - 使用记录总数
     */
    public function logList()
    {
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $PromoCodeLogModel = new PromoCodeLogModel();

        $data = $PromoCodeLogModel->logList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-10-19
     * @title 产品优惠码使用记录
     * @desc 产品优惠码使用记录
     * @author theworld
     * @version v1
     * @url /admin/v1/promo_code/host/:id/log
     * @method  get
     * @param int id - 产品ID required
     * @return array list - 使用记录列表
     * @return int list[].id - ID
     * @return int list[].order_id - 订单ID
     * @return string list[].scene - 优惠码应用场景:new新购,renew续费,upgrade升降级
     * @return float list[].code - 优惠码
     * @return float list[].discount - 优惠金额
     * @return int list[].create_time - 使用时间
     * @return int count - 使用记录总数
     */
    public function hostPromoCodeLog()
    {
        $param = $this->request->param();

        $PromoCodeLogModel = new PromoCodeLogModel();

        $result = $PromoCodeLogModel->hostPromoCodeLog($param);

        return json($result);
    }
}