<?php
namespace addon\promo_code\controller\clientarea;

use addon\promo_code\model\PromoCodeModel;
use app\event\controller\PluginBaseController;
use addon\promo_code\validate\PromoCodeValidate;

/**
 * @title 优惠码插件(基础版)
 * @desc 优惠码插件(基础版)
 * @use addon\promo_code\controller\IndexController
 */
class IndexController extends PluginBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new PromoCodeValidate();
    }

    /**
     * 时间 2022-10-20
     * @title 应用优惠码
     * @desc 应用优惠码,新购/续费/升降级等,可使用此接口对优惠码进行验证
     * @author theworld
     * @version v1
     * @url /console/v1/promo_code/apply
     * @method  POST
     * @param string scene - 优惠码应用场景:new新购,renew续费,upgrade升降级 required
     * @param string promo_code - 优惠码 新购时必传
     * @param int host_id - 产品ID
     * @param int product_id - 商品ID required
     * @param int qty - 数量 新购时必传
     * @param int amount - 单价 required
     * @param int billing_cycle_time - 周期时间 required
     * @return float discount 1.00 折扣金额
     */
    public function apply()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('apply')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        $PromoCodeModel = new PromoCodeModel();

        $result = $PromoCodeModel->apply($param);

        return json($result);
    }

    /**
     * 时间 2022-10-20
     * @title 产品内页获取优惠码信息
     * @desc 产品内页获取优惠码信息
     * @author theworld
     * @version v1
     * @url /console/v1/promo_code/host/:id/promo_code
     * @method  get
     * @param int id - 产品ID required
     * @return array promo_code - 优惠码
     */
    public function hostPromoCode()
    {
        $param = $this->request->param();

        $PromoCodeModel = new PromoCodeModel();

        $result = $PromoCodeModel->hostPromoCode($param);

        return json($result);
    }

}