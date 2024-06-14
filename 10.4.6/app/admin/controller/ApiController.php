<?php
namespace app\admin\controller;

use app\common\model\ApiModel;
use app\admin\validate\ApiValidate;

/**
 * @title API管理
 * @desc API管理
 * @use app\admin\controller\ApiController
 */
class ApiController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ApiValidate();
    }

    /**
     * 时间 2024-04-28
     * @title 获取API设置
     * @desc 获取API设置
     * @author theworld
     * @version v1
     * @url /admin/v1/api/config
     * @method  GET
     * @return int client_create_api - 用户API创建权限0关闭1开启
     * @return int client_create_api_type - 用户API创建权限类型0全部用户1指定用户可创建2指定用户不可创建
     */
    public function getConfig()
    {
        //实例化模型类
        $ApiModel = new ApiModel();
        
        //获取API设置
        $data = $ApiModel->getConfig();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data,
        ];
       return json($result);
    }

    /**
     * 时间 2024-04-28
     * @title 保存API设置
     * @desc 保存API设置
     * @author theworld
     * @version v1
     * @url /admin/v1/api/config
     * @method  PUT
     * @param int client_create_api - 用户API创建权限0关闭1开启
     * @param int client_create_api_type - 用户API创建权限类型0全部用户1指定用户可创建2指定用户不可创建
     */
    public function updateConfig()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('config')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ApiModel())->updateConfig($param);

        return json($result);
    }

    /**
     * 时间 2024-04-28
     * @title API指定用户列表
     * @desc API指定用户列表
     * @author theworld
     * @version v1
     * @url /admin/v1/api/client
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 用户
     * @return int list[].id - 用户ID 
     * @return string list[].username - 姓名 
     * @return string list[].email - 邮箱 
     * @return int list[].phone_code - 国际电话区号 
     * @return string list[].phone - 手机号 
     * @return int list[].status - 状态;0:禁用,1:正常 
     * @return string list[].company - 公司 
     * @return int list[].host_num - 产品数量 
     * @return int list[].host_active_num - 已激活产品数量
     * @return array list[].custom_field - 自定义字段
     * @return string list[].custom_field[].name - 名称
     * @return string list[].custom_field[].value - 值
     * @return bool list[].certification 是否实名认证true是false否
     * @return string list[].certification_type 实名类型person个人company企业
     * @return int count - 用户总数
     */
    public function clientList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $ApiModel = new ApiModel();

        // 获取用户列表
        $data = $ApiModel->clientList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-28
     * @title 添加API指定用户
     * @desc 添加API指定用户
     * @author theworld
     * @version v1
     * @url /admin/v1/api/client/:id
     * @method  POST
     * @param int id - 用户ID
     */
    public function addClient()
    {
        $param = $this->request->param();

        $result = (new ApiModel())->addClient($param['id']);

        return json($result);
    }

    /**
     * 时间 2024-04-28
     * @title 移除API指定用户
     * @desc 移除API指定用户
     * @author theworld
     * @version v1
     * @url /admin/v1/api/client/:id
     * @method  DELETE
     * @param int id - 用户ID
     */
    public function removeClient()
    {
        $param = $this->request->param();

        $result = (new ApiModel())->removeClient($param['id']);

        return json($result);
    }

}

