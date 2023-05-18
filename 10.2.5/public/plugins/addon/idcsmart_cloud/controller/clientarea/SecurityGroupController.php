<?php
namespace addon\idcsmart_cloud\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupModel;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupHostLinkModel;
use addon\idcsmart_cloud\validate\IdcsmartSecurityGroupValidate;

/**
 * @title 安全组管理
 * @desc 安全组管理
 * @use addon\idcsmart_cloud\controller\clientarea\SecurityGroupController
 */
class SecurityGroupController extends PluginBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartSecurityGroupValidate();
    }

    /**
     * 时间 2022-06-08
     * @title 安全组列表
     * @desc 安全组列表
     * @author theworld
     * @version v1
     * @url /console/v1/security_group
     * @method  GET
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,username,phone,email
     * @param string sort - 升/降序 asc,desc
     * @return array list - 安全组
     * @return int list[].id - 安全组ID
     * @return string list[].name - 名称 
     * @return string list[].description - 描述 
     * @return int list[].create_time - 创建时间 
     * @return int list[].host_num - 产品数量 
     * @return int list[].rule_num - 规则数量
     * @return int count - 安全组总数
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $IdcsmartSecurityGroupModel = new IdcsmartSecurityGroupModel();

        // 获取安全组列表
        $data = $IdcsmartSecurityGroupModel->idcsmartSecurityGroupList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-08
     * @title 安全组详情
     * @desc 安全组详情
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id
     * @method  GET
     * @param int id - 安全组ID required
     * @return object security_group - 安全组
     * @return int security_group.id - 安全组ID
     * @return string security_group.name - 名称 
     * @return string security_group.description - 描述 
     * @return int security_group.create_time - 创建时间 
     */
    public function index()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartSecurityGroupModel = new IdcsmartSecurityGroupModel();

        // 获取安全组
        $securityGroup = $IdcsmartSecurityGroupModel->indexIdcsmartSecurityGroup($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'security_group' => $securityGroup
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-08
     * @title 添加安全组
     * @desc 添加安全组
     * @author theworld
     * @version v1
     * @url /console/v1/security_group
     * @method  POST
     * @param string name - 名称 required
     * @param string description - 描述
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
        $IdcsmartSecurityGroupModel = new IdcsmartSecurityGroupModel();

        // 创建安全组
        $result = $IdcsmartSecurityGroupModel->createIdcsmartSecurityGroup($param);

        return json($result);
    }

    /**
     * 时间 2022-06-08
     * @title 修改安全组
     * @desc 修改安全组
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id
     * @method  PUT
     * @param int id - 安全组ID required
     * @param string name - 名称 required
     * @param string description - 描述
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
        $IdcsmartSecurityGroupModel = new IdcsmartSecurityGroupModel();

        // 修改安全组
        $result = $IdcsmartSecurityGroupModel->updateIdcsmartSecurityGroup($param);

        return json($result);
    }

    /**
     * 时间 2022-06-08
     * @title 删除安全组
     * @desc 删除安全组
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id
     * @method  DELETE
     * @param int id - 安全组ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartSecurityGroupModel = new IdcsmartSecurityGroupModel();

        // 删除安全组
        $result = $IdcsmartSecurityGroupModel->deleteIdcsmartSecurityGroup($param['id']);

        return json($result);
    }

    /**
     * 时间 2022-06-09
     * @title 安全组规则列表
     * @desc 安全组规则列表
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id/rule
     * @method  GET
     * @param int id - 安全组ID required
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,username,phone,email
     * @param string sort - 升/降序 asc,desc
     * @param string direction - 规则方向筛选in=进方向,out=出方向
     * @return array list - 安全组规则
     * @return int list[].id - 安全组规则ID
     * @return string list[].description - 描述 
     * @return string list[].direction - 规则方向in,out
     * @return string list[].protocol - 协议all,all_tcp,all_udp,tcp,udp,icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis,gre 
     * @return string list[].port - 端口范围 
     * @return string list[].ip - 授权IP 
     * @return int list[].create_time - 创建时间 
     * @return int count - 安全组规则总数
     */
    public function hostList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $IdcsmartSecurityGroupHostLinkModel = new IdcsmartSecurityGroupHostLinkModel();

        // 获取安全组规则列表
        $data = $IdcsmartSecurityGroupHostLinkModel->idcsmartSecurityGroupHostList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-09
     * @title 安全组实例列表
     * @desc 安全组实例列表
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id/host
     * @method  GET
     * @param int id - 安全组ID required
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 实例
     * @return int list[].id - 实例ID
     * @return string list[].name - 名称 
     * @return string list[].ip - IP
     * @return string list[].package - 套餐
     * @return int count - 实例总数
     */
    public function securityGroupHostList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $IdcsmartSecurityGroupHostLinkModel = new IdcsmartSecurityGroupHostLinkModel();

        // 关联安全组
        $data = $IdcsmartSecurityGroupHostLinkModel->idcsmartSecurityGroupHostList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }


    /**
     * 时间 2022-09-08
     * @title 关联安全组
     * @desc 关联安全组
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id/host/:host_id
     * @method  POST
     * @param int id - 安全组ID required
     * @param int host_id - 产品ID required
     */
    public function linkSecurityGroup()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('link')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        // 实例化模型类
        $IdcsmartSecurityGroupHostLinkModel = new IdcsmartSecurityGroupHostLinkModel();

        // 关联安全组
        $result = $IdcsmartSecurityGroupHostLinkModel->linkSecurityGroup($param);

        return json($result);
    }

    /**
     * 时间 2022-09-08
     * @title 取消关联安全组
     * @desc 取消关联安全组
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id/host/:host_id
     * @method  DELETE
     * @param int id - 安全组ID required
     * @param int host_id - 产品ID required
     */
    public function unlinkSecurityGroup()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('unlink')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }

        // 实例化模型类
        $IdcsmartSecurityGroupHostLinkModel = new IdcsmartSecurityGroupHostLinkModel();

        // 取消关联安全组
        $result = $IdcsmartSecurityGroupHostLinkModel->unlinkSecurityGroup($param);

        return json($result);
    }
}