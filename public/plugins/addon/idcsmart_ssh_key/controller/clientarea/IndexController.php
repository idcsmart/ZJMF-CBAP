<?php
namespace addon\idcsmart_ssh_key\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_ssh_key\model\IdcsmartSshKeyModel;
use addon\idcsmart_ssh_key\validate\IdcsmartSshKeyValidate;

/**
 * @title SSH密钥
 * @desc SSH密钥
 * @use addon\idcsmart_ssh_key\controller\clientarea\IndexController
 */
class IndexController extends PluginBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartSshKeyValidate();
    }

    /**
     * 时间 2022-07-07
     * @title SSH密钥列表
     * @desc SSH密钥列表
     * @author theworld
     * @version v1
     * @url /console/v1/ssh_key
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
     * @return int count - SSH密钥总数
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $IdcsmartSshKeyModel = new IdcsmartSshKeyModel();

        // 获取SSH密钥列表
        $data = $IdcsmartSshKeyModel->idcsmartSshKeyList($param, 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-07-07
     * @title 创建SSH密钥
     * @desc 创建SSH密钥
     * @author theworld
     * @version v1
     * @url /console/v1/ssh_key
     * @method  POST
     * @param string name - 名称 required
     * @param string public_key - 公钥 required
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartSshKeyModel = new IdcsmartSshKeyModel();

        // 创建SSH密钥
        $result = $IdcsmartSshKeyModel->createIdcsmartSshKey($param);

        return json($result);
    }

    /**
     * 时间 2022-07-07
     * @title 编辑SSH密钥
     * @desc 编辑SSH密钥
     * @author theworld
     * @version v1
     * @url /console/v1/ssh_key/:id
     * @method  PUT
     * @param int id - SSH密钥ID required
     * @param string name - 名称 required
     * @param string public_key - 公钥 required
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartSshKeyModel = new IdcsmartSshKeyModel();

        // 编辑SSH密钥
        $result = $IdcsmartSshKeyModel->updateIdcsmartSshKey($param);

        return json($result);
    }

    /**
     * 时间 2022-07-07
     * @title 删除SSH密钥
     * @desc 删除SSH密钥
     * @author theworld
     * @version v1
     * @url /console/v1/ssh_key/:id
     * @method  DELETE
     * @param int id - SSH密钥ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartSshKeyModel = new IdcsmartSshKeyModel();

        // 删除SSH密钥
        $result = $IdcsmartSshKeyModel->deleteIdcsmartSshKey($param['id']);

        return json($result);
    }
}