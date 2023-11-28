<?php
namespace certification\idcsmartali\controller;

use app\home\controller\BaseController;
use certification\idcsmartali\logic\IdcsmartaliLogic;

/**
 * @desc 实名认证控制器
 * @author wyh
 * @version 1.0
 * @time 2022-0924
 */
class IndexController extends BaseController
{
    /**
     * 时间 2022-9-24
     * @title 刷新实名认证状态
     * @desc 刷新实名认证状态
     * @url certification/idcsmartali/index/status?certify_id=134&type=person&client_id=1
     * @method  get
     * @author wyh
     * @version v1
     * @param string certify_id - 认证证书 required
     * @param string type - person个人认证,company企业认证 required
     * @param int client_id - 客户ID required
     * @return string code - 当code为1时,停止调接口
     */
    public function status()
    {
        $param = $this->request->param();

        $IdcsmartaliLogic = new IdcsmartaliLogic();

        $result = $IdcsmartaliLogic->getAliyunAuthStatus($param['certify_id']??'',$param['type']??'person',$param['client_id']??0);

        return json($result);
    }

}