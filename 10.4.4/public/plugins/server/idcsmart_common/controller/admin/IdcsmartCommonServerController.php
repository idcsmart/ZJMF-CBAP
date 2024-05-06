<?php
namespace server\idcsmart_common\controller\admin;

use app\event\controller\BaseController;
use server\idcsmart_common\model\IdcsmartCommonServerModel;
use server\idcsmart_common\validate\IdcsmartCommonServerValidate;

/**
 * @title 通用商品-子接口
 * @desc 通用商品-子接口
 * @use server\idcsmart_common\controller\admin\IdcsmartCommonServerController
 */
class IdcsmartCommonServerController extends BaseController{

    /**
     * 时间 2023-6-8
     * @title 服务器列表
     * @desc 服务器列表
     * @url /admin/v1/idcsmart_common/server
     * @method  GET
     * @author wyh
     * @version v1
     * @return array list - 服务器列表
     * @return string list[].id - 服务器ID
     * @return string list[].name - 服务器名称
     * @return string list[].ip_address - ip地址
     * @return string list[].assigned_ips - 其他IP地址
     * @return string list[].hostname - 主机名
     * @return string list[].noc -
     * @return string list[].status_address - 服务器状态地址
     * @return string list[].username - 用户名
     * @return string list[].password - 密码
     * @return string list[].accesshash - 访问散列值
     * @return int list[].secure - 安全，1选中复选框使用（默认选中） SSL 连接模式;0不选中
     * @return string list[].port - 访问端口(默认80)
     * @return string list[].disabled - 1勾选禁用，0使用(默认)(单选框)
     * @return string list[].type - 接口类型
     * @return int list[].max_accounts - 最大账号数量（默认为200）
     * @return int list[].gid - 服务器组ID（下拉框）单选
     * @return string list[].group_name - 分组名称
     * @return int list[].used - 已使用数量
     * @return int count - 数量
     */
    public function serverList(){
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();

        $result = $IdcsmartCommonServerModel->serverList($param);

        return json($result);
    }

    /**
     * 时间 2023-6-8
     * @title 添加服务器
     * @desc 添加服务器
     * @url /admin/v1/idcsmart_common/server
     * @method  POST
     * @author wyh
     * @version v1
     * @param string name - 服务器名称 required
     * @param string ip_address - ip地址
     * @param string assigned_ips - 其他IP地址
     * @param string hostname - 主机名
     * @param string noc -
     * @param string status_address - 服务器状态地址
     * @param string username - 用户名
     * @param string password - 密码
     * @param string accesshash - 访问散列值
     * @param int secure - 安全，1选中复选框使用（默认选中） SSL 连接模式;0不选中
     * @param string port - 访问端口(默认80)
     * @param string disabled - 1勾选禁用，0使用(默认)(单选框)
     * @param string type - 接口类型
     * @param int max_accounts - 最大账号数量（默认为200）
     * @param int gid - 服务器组ID（下拉框）单选(调服务器分组列表接口，传modules，值为接口类型)
     */
    public function create(){
        $param = $this->request->only(['name','ip_address','assigned_ips','hostname','noc','status_address'
            ,'username','password','accesshash','secure','port','disabled','type','max_accounts','gid']);
        $IdcsmartCommonServerValidate = new IdcsmartCommonServerValidate();
        if (!$IdcsmartCommonServerValidate->scene("create")->check($param)){
            return json(['status'=>400,'msg'=>lang_plugins($IdcsmartCommonServerValidate->getError())]);
        }

        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();

        $result = $IdcsmartCommonServerModel->createServer($param);

        return json($result);
    }

    /**
     * 时间 2023-6-8
     * @title 服务器详情
     * @desc 服务器详情
     * @url /admin/v1/idcsmart_common/server/:id
     * @method  get
     * @author wyh
     * @version v1
     * @param int id - 服务器ID required
     * @return string name - 服务器名称 required
     * @return string ip_address - ip地址
     * @return string assigned_ips - 其他IP地址
     * @return string hostname - 主机名
     * @return string noc -
     * @return string status_address - 服务器状态地址
     * @return string username - 用户名
     * @return string password - 密码
     * @return string accesshash - 访问散列值
     * @return int secure - 安全，1选中复选框使用（默认选中） SSL 连接模式;0不选中
     * @return string port - 访问端口(默认80)
     * @return string disabled - 1勾选禁用，0使用(默认)(单选框)
     * @return string type - 接口类型
     * @return int max_accounts - 最大账号数量（默认为200）
     * @return int gid - 服务器组ID（下拉框）单选
     */
    public function index(){
        $param = $this->request->param();

        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();

        $result = $IdcsmartCommonServerModel->indexServer($param);

        return json($result);
    }

    /**
     * 时间 2023-6-8
     * @title 更新服务器
     * @desc 更新服务器
     * @url /admin/v1/idcsmart_common/server/:id
     * @method  PUT
     * @author wyh
     * @version v1
     * @param int id - 服务器ID required
     * @param string name - 服务器名称 required
     * @param string ip_address - ip地址
     * @param string assigned_ips - 其他IP地址
     * @param string hostname - 主机名
     * @param string noc -
     * @param string status_address - 服务器状态地址
     * @param string username - 用户名
     * @param string password - 密码
     * @param string accesshash - 访问散列值
     * @param int secure - 安全，1选中复选框使用（默认选中） SSL 连接模式;0不选中
     * @param string port - 访问端口(默认80)
     * @param int disabled - 1勾选禁用，0使用(默认)(单选框)
     * @param string type - 接口类型
     * @param int max_accounts - 最大账号数量（默认为200）
     * @param int gid - 服务器组ID（下拉框）单选
     */
    public function update(){
        $param = $this->request->only(['id','name','ip_address','assigned_ips','hostname','gid','noc','status_address'
            ,'username','password','accesshash','secure','port','disabled','type','max_accounts']);
        $IdcsmartCommonServerValidate = new IdcsmartCommonServerValidate();
        if (!$IdcsmartCommonServerValidate->scene("create")->check($param)){
            return json(['status'=>400,'msg'=>lang_plugins($IdcsmartCommonServerValidate->getError())]);
        }
        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();

        $result = $IdcsmartCommonServerModel->updateServer($param);

        return json($result);
    }

    /**
     * 时间 2023-6-8
     * @title 删除服务器
     * @desc 删除服务器
     * @url /admin/v1/idcsmart_common/server/:id
     * @method  DELETE
     * @author wyh
     * @version v1
     * @param int id - 服务器ID required
     */
    public function delete(){
        $param = $this->request->param();

        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();

        $result = $IdcsmartCommonServerModel->deleteServer($param);

        return json($result);
    }

    /**
     * 时间 2023-6-8
     * @title 测试服务器链接
     * @desc 测试服务器链接
     * @url /admin/v1/idcsmart_common/server/:id/status
     * @method  post
     * @author wyh
     * @version v1
     * @param int id - 服务器ID required
     */
    public function testLink(){
        $param = $this->request->param();

        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();

        $result = $IdcsmartCommonServerModel->testLinkServer($param);

        return json($result);
    }

    /**
     * 时间 2023-6-8
     * @title 模块列表
     * @desc 模块列表
     * @url /admin/v1/idcsmart_common/server/modules
     * @method  get
     * @author wyh
     * @version v1
     */
    public function getModules(){
        $param = $this->request->param();

        $IdcsmartCommonServerModel = new IdcsmartCommonServerModel();

        $result = $IdcsmartCommonServerModel->getModules($param);

        return json($result);
    }
}