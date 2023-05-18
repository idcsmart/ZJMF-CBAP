<?php
namespace app\admin\controller;

use app\common\model\ClientRecordModel;
use app\admin\validate\ClientRecordValidate;

/**
 * @title 用户信息记录
 * @desc 用户信息记录
 * @use app\admin\controller\ClientRecordController
 */
class ClientRecordController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ClientRecordValidate();
    }

    /**
     * 时间 2023-03-21
     * @title 用户信息记录列表
     * @desc 用户信息记录列表
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id/record
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 用户信息记录
     * @return int list[].id - 用户信息记录ID 
     * @return string list[].content - 内容
     * @return array list[].attachment - 附件
     * @return int list[].admin_id - 管理员ID 
     * @return string list[].admin_name - 管理员名称
     * @return int list[].create_time - 创建时间
     * @return int count - 用户信息记录总数
     */
	public function list()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $ClientRecordModel = new ClientRecordModel();

        // 获取用户信息记录列表
        $data = $ClientRecordModel->clientRecordList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

    /**
     * 时间 2023-03-21
     * @title 新增用户信息记录
     * @desc 新增用户信息记录
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id/record
     * @method  POST
     * @param int id - 用户ID required
     * @param string content - 名称 required
     * @param array attachment - 附件
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
        $ClientRecordModel = new ClientRecordModel();
        
        // 新增用户信息记录
        $result = $ClientRecordModel->createClientRecord($param);

        return json($result);
    }

    /**
     * 时间 2023-03-21
     * @title 编辑用户信息记录
     * @desc 编辑用户信息记录
     * @author theworld
     * @version v1
     * @url /admin/v1/client/record/:id
     * @method  PUT
     * @param int id - 用户信息记录ID required
     * @param string content - 名称 required
     * @param array attachment - 附件
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
        $ClientRecordModel = new ClientRecordModel();
        
        // 编辑用户信息记录
        $result = $ClientRecordModel->updateClientRecord($param);

        return json($result);
    }

    /**
     * 时间 2023-03-21
     * @title 删除用户信息记录
     * @desc 删除用户信息记录
     * @author theworld
     * @version v1
     * @url /admin/v1/client/record/:id
     * @method  DELETE
     * @param int id - 用户信息记录ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $ClientRecordModel = new ClientRecordModel();
        
        // 删除用户信息记录
        $result = $ClientRecordModel->deleteClientRecord($param['id']);

        return json($result);

    }

    
}