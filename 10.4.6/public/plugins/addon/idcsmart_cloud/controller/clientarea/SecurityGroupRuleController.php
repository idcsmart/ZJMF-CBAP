<?php
namespace addon\idcsmart_cloud\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_cloud\model\IdcsmartSecurityGroupRuleModel;
use addon\idcsmart_cloud\validate\IdcsmartSecurityGroupValidate;

/**
 * @title 安全组规则管理
 * @desc 安全组规则管理
 * @use addon\idcsmart_cloud\controller\clientarea\SecurityGroupRuleController
 */
class SecurityGroupRuleController extends PluginBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartSecurityGroupValidate();
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
     * @param string direction - 规则方向in,out
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
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
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();

        // 获取安全组规则列表
        $data = $IdcsmartSecurityGroupRuleModel->idcsmartSecurityGroupRuleList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-09
     * @title 安全组规则详情
     * @desc 安全组规则详情
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/rule/:id
     * @method  GET
     * @param int id - 安全组规则ID required
     * @return object security_group_rule - 安全组规则
     * @return int security_group_rule.id - 安全组规则ID
     * @return string security_group_rule.description - 描述 
     * @return string security_group_rule.direction - 规则方向in,out
     * @return string security_group_rule.protocol - 协议all,all_tcp,all_udp,tcp,udp,icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis,gre 
     * @return string security_group_rule.port - 端口范围 
     * @return string security_group_rule.ip - 授权IP 
     * @return int security_group_rule.create_time - 创建时间 
     */
    public function index()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();

        // 获取安全组规则
        $securityGroupRule = $IdcsmartSecurityGroupRuleModel->indexIdcsmartSecurityGroupRule($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'security_group_rule' => $securityGroupRule
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-09
     * @title 添加安全组规则
     * @desc 添加安全组规则
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id/rule
     * @method  POST
     * @param int id - 安全组ID required
     * @param string description - 描述
     * @param string direction - 规则方向in,out required
     * @param string protocol - 协议all,all_tcp,all_udp,tcp,udp,icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis,gre required
     * @param string port - 端口范围 required
     * @param string ip - 授权IP required
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create_rule')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();

        // 创建安全组规则
        $result = $IdcsmartSecurityGroupRuleModel->createIdcsmartSecurityGroupRule($param);

        return json($result);
    }

    /**
     * 时间 2022-08-26
     * @title 批量添加安全组规则
     * @desc 批量添加安全组规则
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/:id/rule/batch
     * @method  POST
     * @param  array rule - 规则数组
     * @param  string rule[].description - 描述
     * @param  string rule[].direction - 规则方向in,out require
     * @param  string rule[].protocol - 协议all,all_tcp,all_udp,tcp,udp,icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis,gre require
     * @param  string rule[].port - 端口范围 require
     * @param  string rule[].ip - 授权IP require
     * @return int success_num - 添加成功的规则数量
     */
    public function batchCreate()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();

        // 创建安全组规则
        $result = $IdcsmartSecurityGroupRuleModel->batchCreate($param);

        return json($result);
    }

    /**
     * 时间 2022-06-09
     * @title 修改安全组规则
     * @desc 修改安全组规则
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/rule/:id
     * @method  PUT
     * @param int id - 安全组规则ID required
     * @param string description - 描述
     * @param string direction - 规则方向in,out required
     * @param string protocol - 协议all,all_tcp,all_udp,tcp,udp,icmp,ssh,telnet,http,https,mssql,oracle,mysql,rdp,postgresql,redis,gre required
     * @param string port - 端口范围 required
     * @param string ip - 授权IP required
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update_rule')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();

        // 修改安全组规则
        $result = $IdcsmartSecurityGroupRuleModel->updateIdcsmartSecurityGroupRule($param);

        return json($result);
    }

    /**
     * 时间 2022-06-09
     * @title 删除安全组规则
     * @desc 删除安全组规则
     * @author theworld
     * @version v1
     * @url /console/v1/security_group/rule/:id
     * @method  DELETE
     * @param int id - 安全组规则ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartSecurityGroupRuleModel = new IdcsmartSecurityGroupRuleModel();

        // 删除安全组规则
        $result = $IdcsmartSecurityGroupRuleModel->deleteIdcsmartSecurityGroupRule($param['id']);

        return json($result);
    }
}