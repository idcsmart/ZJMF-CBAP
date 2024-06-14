<?php
namespace app\home\controller;

use app\common\model\ApiModel;
use app\home\validate\ApiValidate;

/**
 * @title API管理
 * @desc API管理
 * @use app\home\controller\ApiController
 */
class ApiController extends HomeBaseController
{   
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ApiValidate();
    }

    /**
     * 时间 2022-07-06
     * @title API密钥列表
     * @desc API密钥列表
     * @author theworld
     * @version v1
     * @url /console/v1/api
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - API密钥
     * @return int list[].name - API密钥名称 
     * @return int list[].id - API密钥ID 
     * @return string list[].token - token 
     * @return int list[].create_time - 创建时间 
     * @return string list[].status - 白名单状态0关闭1开启
     * @return string list[].ip - 白名单IP 
     * @return int count - API日志总数
     * @return int create_api - 是否可创建API:0否1是
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $ApiModel = new ApiModel();

        // 获取API密钥列表
        $data = $ApiModel->apiList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-07-06
     * @title 创建API密钥
     * @desc 创建API密钥
     * @author theworld
     * @version v1
     * @url /console/v1/api
     * @method  POST
     * @param string name - 名称 required
     * @return object api - API密钥
     * @return int api.name - API密钥名称 
     * @return int api.id - API密钥ID 
     * @return string api.token - token 
     * @return int api.create_time - 创建时间 
     * @return string api.private_key - 私钥 
     */
    public function create()
    {
        $param = $this->request->param();
        
        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ApiModel = new ApiModel();

        // 创建API密钥
        $result = $ApiModel->createApi($param);

        return json($result);
    }

    /**
     * 时间 2022-07-06
     * @title API白名单设置
     * @desc API白名单设置
     * @author theworld
     * @version v1
     * @url /console/v1/api/:id/white_list
     * @method  PUT
     * @param int id - API密钥ID required
     * @param int status - 白名单状态0关闭1开启 required
     * @param string ip - 白名单IP 白名单开启时必填
     */
    public function whiteListSetting()
    {
        $param = $this->request->param();
        
        // 参数验证
        if (!$this->validate->scene('white_list')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ApiModel = new ApiModel();

        // API白名单设置
        $result = $ApiModel->whiteListSetting($param);

        return json($result);
    }

    /**
     * 时间 2022-07-06
     * @title 删除API密钥
     * @desc 删除API密钥
     * @author theworld
     * @version v1
     * @url /console/v1/api/:id
     * @method  DELETE
     * @param int id - API密钥ID required
     */
    public function delete()
    {
        $param = $this->request->param();
        
        // 实例化模型类
        $ApiModel = new ApiModel();

        // 删除API
        $result = $ApiModel->deleteApi($param['id']);

        return json($result);
    }
}