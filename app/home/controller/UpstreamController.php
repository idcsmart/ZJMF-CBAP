<?php
namespace app\home\controller;

use app\common\logic\UpstreamLogic;

/**
 * @title 上游管理
 * @desc 上游管理
 * @use app\home\controller\UpstreamController
 */
class UpstreamController extends HomeBaseController
{   
    /**
     * 时间 2023-02-15
     * @title 上游同步数据
     * @desc 上游同步数据
     * @author theworld
     * @version v1
     * @url /console/v1/upstream/push
     * @method  POST
     * @param int host_id - 产品ID
     * @param string data - 推送数据
     */
	public function sync()
    {
        $param = $this->request->param();
        
        // 实例化模型类
        $UpstreamLogic = new UpstreamLogic();

        // 上游同步数据
        $result = $UpstreamLogic->syncHost($param);

        return json($result);
	}
}