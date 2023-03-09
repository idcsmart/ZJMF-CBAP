<?php
namespace app\admin\controller;

use app\common\model\FriendlyLinkModel;
use app\admin\validate\FriendlyLinkValidate;

/**
 * @title 友情链接
 * @desc 友情链接
 * @use app\admin\controller\FriendlyLinkController
 */
class FriendlyLinkController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new FriendlyLinkValidate();
    }

    /**
     * 时间 2023-02-28
     * @title 获取友情链接
     * @desc 获取友情链接
     * @author theworld
     * @version v1
     * @url /admin/v1/friendly_link
     * @method  GET
     * @return array list - 友情链接
     * @return int list[].id - 友情链接ID 
     * @return string list[].name - 名称 
     * @return string list[].url - 链接地址 
     */
    public function list()
    {  
        // 实例化模型类
        $FriendlyLinkModel = new FriendlyLinkModel();

        // 获取友情链接
        $data = $FriendlyLinkModel->friendlyLinkList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 添加友情链接
     * @desc 添加友情链接
     * @author theworld
     * @version v1
     * @url /admin/v1/friendly_link
     * @method  POST
     * @param string name - 名称 required
     * @param string url - 链接地址 required
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $FriendlyLinkModel = new FriendlyLinkModel();
        
        // 新建友情链接
        $result = $FriendlyLinkModel->createFriendlyLink($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 编辑友情链接
     * @desc 编辑友情链接
     * @author theworld
     * @version v1
     * @url /admin/v1/friendly_link/:id
     * @method  PUT
     * @param int id - 友情链接ID required
     * @param string name - 名称 required
     * @param string url - 链接地址 required
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $FriendlyLinkModel = new FriendlyLinkModel();
        
        // 修改友情链接
        $result = $FriendlyLinkModel->updateFriendlyLink($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 删除友情链接
     * @desc 删除友情链接
     * @author theworld
     * @version v1
     * @url /admin/v1/friendly_link/:id
     * @method  DELETE
     * @param int id - 友情链接ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $FriendlyLinkModel = new FriendlyLinkModel();
        
        // 删除友情链接
        $result = $FriendlyLinkModel->deleteFriendlyLink($param['id']);

        return json($result);

    }

    
}