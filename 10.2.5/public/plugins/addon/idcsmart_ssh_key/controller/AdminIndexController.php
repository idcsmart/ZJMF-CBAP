<?php
namespace addon\idcsmart_ssh_key\controller;

use app\event\controller\PluginAdminBaseController;
use addon\idcsmart_ssh_key\model\IdcsmartSshKeyModel;

/**
 * @title SSH密钥(后台)
 * @desc SSH密钥(后台)
 * @use addon\idcsmart_ssh_key\controller\AdminIndexController
 */
class AdminIndexController extends PluginAdminBaseController
{
    /**
     * 时间 2022-07-07
     * @title SSH密钥列表
     * @desc SSH密钥列表
     * @author theworld
     * @version v1
     * @url /admin/v1/ssh_key
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - SSH密钥
     * @return int list[].id - SSH密钥ID
     * @return string list[].name - 名称
     * @return string list[].public_key - 公钥
     * @return string list[].finger_print - 指纹
     * @return string list[].client - 用户
     * @return int count - SSH密钥总数
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $IdcsmartSshKeyModel = new IdcsmartSshKeyModel();

        // 获取SSH密钥列表
        $data = $IdcsmartSshKeyModel->idcsmartSshKeyList($param, 'admin');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }
}