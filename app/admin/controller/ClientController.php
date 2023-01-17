<?php
namespace app\admin\controller;

use app\common\model\ClientModel;
use app\admin\validate\ClientValidate;

/**
 * @title 用户管理
 * @desc 用户管理
 * @use app\admin\controller\ClientController
 */
class ClientController extends AdminBaseController
{	
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ClientValidate();
    }

	/**
     * 时间 2022-05-10
     * @title 用户列表
     * @desc 用户列表
     * @author theworld
     * @version v1
     * @url /admin/v1/client
     * @method  GET
     * @param object custom_field - 自定义字段,key为自定义字段名称,value为自定义字段的值
     * @param string keywords - 关键字,搜索范围:用户ID,姓名,邮箱,手机号
     * @param int client_id - 用户ID,精确搜索
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
     * @return int count - 用户总数
     */
	public function clientList()
	{
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $ClientModel = new ClientModel();

        // 获取用户列表
        $data = $ClientModel->clientList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

    /**
     * 时间 2022-05-10
     * @title 用户详情
     * @desc 用户详情
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id
     * @method  GET
     * @param int id - 用户ID required
     * @return object client - 用户
     * @return int client.id - 用户ID 
     * @return string client.username - 姓名 
     * @return string client.email - 邮箱 
     * @return int client.phone_code - 国际电话区号 
     * @return string client.phone - 手机号 
     * @return string client.company - 公司 
     * @return string client.country - 国家 
     * @return string client.address - 地址 
     * @return string client.language - 语言 
     * @return string client.notes - 备注
     * @return int client.status - 状态;0:禁用,1:正常 
     * @return int client.register_time - 注册时间 
     * @return int client.last_login_time - 上次登录时间 
     * @return string client.last_login_ip - 上次登录IP
     * @return string client.credit - 余额 
     * @return string client.consume - 消费 
     * @return string client.refund - 退款 
     * @return string client.withdraw - 提现 
     * @return int client.host_num - 产品数量 
     * @return int client.host_active_num - 已激活产品数量
     * @return array client.login_logs - 登录记录
     * @return string client.login_logs[].ip - IP
     * @return int client.login_logs[].login_time - 登录时间
     */
    public function index()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $ClientModel = new ClientModel();

        // 获取用户
        $client = $ClientModel->indexClient($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'client' => $client
            ]
        ];
        return json($result);
    }

	/**
     * 时间 2022-05-10
     * @title 新建用户
     * @desc 新建用户
     * @author theworld
     * @version v1
     * @url /admin/v1/client
     * @method  POST
     * @param string username - 姓名
     * @param string email - 邮箱 邮箱手机号两者至少输入一个
     * @param int phone_code - 国际电话区号 输入手机号时必须传此参数
     * @param string phone - 手机号 邮箱手机号两者至少输入一个
     * @param string password - 密码 required
     * @param string repassword - 重复密码 required
     * @return int id - 用户ID
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
        $ClientModel = new ClientModel();
        
        // 新建用户
        $result = $ClientModel->createClient($param);

        return json($result);
	}

	/**
     * 时间 2022-05-10
     * @title 修改用户
     * @desc 修改用户
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id
     * @method  PUT
     * @param int id - 用户ID required
     * @param string username - 姓名
     * @param string email - 邮箱 邮箱手机号两者至少输入一个
     * @param int phone_code - 国际电话区号 输入手机号时必须传此参数
     * @param string phone - 手机号 邮箱手机号两者至少输入一个
     * @param string company - 公司
     * @param string country - 国家
     * @param string address - 地址
     * @param string language - 语言
     * @param string notes - 备注
     * @param string password - 密码 为空代表不修改
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
        $ClientModel = new ClientModel();
        
        // 修改用户
        $result = $ClientModel->updateClient($param);

        return json($result);
	}

	/**
     * 时间 2022-05-10
     * @title 删除用户
     * @desc 删除用户
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id
     * @method  DELETE
     * @param int id - 用户ID required
     */
	public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 删除用户
        $result = $ClientModel->deleteClient($param);

        return json($result);

	}

    /**
     * 时间 2022-5-26
     * @title 用户状态切换
     * @desc 用户状态切换
     * @author theworld
     * @version v1
     * @url /admin/v1/client/:id/status
     * @method  put
     * @param int id - 用户ID required
     * @param int status 1 状态:0禁用,1启用 required
     */
    public function status()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('status')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 更改状态
        $result = $ClientModel->updateClientStatus($param);

        return json($result);
    }

    /**
     * 时间 2022-05-16
     * @title 搜索用户
     * @desc 搜索用户
     * @author theworld
     * @version v1
     * @url /admin/v1/client/search
     * @method  GET
     * @param string keywords - 关键字,搜索范围:用户ID,姓名,邮箱,手机号
     * @return array list - 用户
     * @return int list[].id - 用户ID 
     * @return string list[].username - 姓名
     */
    public function search()
    {
        // 接收参数
        $param = $this->request->param();
        $keywords = $param['keywords'] ?? '';
        
        // 实例化模型类
        $ClientModel = new ClientModel();

        // 获取用户列表
        $data = $ClientModel->searchClient($keywords);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-05-30
     * @title 以用户登录
     * @desc 以用户登录
     * @author wyh
     * @version v1
     * @url /admin/v1/client/:id/login
     * @method  POST
     * @param int id - 用户ID required
     * @return string jwt - jwt:获取后放在请求头Authorization里,拼接成如下格式:Bearer yJ0eX.test.ste
     */
    public function login()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $ClientModel = new ClientModel();

        // 获取用户列表
        $result = $ClientModel->loginByClient(intval($param['id']));

        return json($result);
    }
}