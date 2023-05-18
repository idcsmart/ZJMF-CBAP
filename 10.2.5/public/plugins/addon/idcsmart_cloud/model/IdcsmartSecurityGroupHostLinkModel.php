<?php
namespace addon\idcsmart_cloud\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\model\HostModel;
use app\common\model\ClientModel;
use app\common\model\ServerModel;
use addon\idcsmart_cloud\logic\ToolLogic;
use server\idcsmart_cloud\model\HostLinkModel as HLM1;
use server\common_cloud\model\HostLinkModel as HLM2;
use server\mf_cloud\model\HostLinkModel as HLM3;
use addon\idcsmart_cloud\idcsmart_cloud\IdcsmartCloud as IC;

/**
 * @title 安全组产品关联表模型
 * @desc 安全组产品关联表模型
 * @use addon\idcsmart_cloud\model\IdcsmartSecurityGroupHostLinkModel
 */
class IdcsmartSecurityGroupHostLinkModel extends Model
{
    protected $name = 'addon_idcsmart_security_group_host_link';

    // 设置字段信息
    protected $schema = [
        'addon_idcsmart_security_group_id' 	=> 'int',
        'host_id'         					=> 'int',
    ];

    public function idcsmartSecurityGroupHostList($param)
    {
        $param['keywords'] = $param['keywords'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['host_id']) ? 'aisghl.'.$param['orderby'] : 'aisghl.host_id';

        $clientId = get_client_id();
        if(empty($clientId)){
            return ['list' => [], 'count' => 0];
        }

    	$count = $this->alias('aisghl')
            ->field('aisghl.host_id')
            ->leftJoin('host h', 'h.id=aisghl.host_id')
            ->leftJoin('module_idcsmart_cloud_host_link michl', 'h.id=michl.host_id')
            ->leftJoin('module_idcsmart_cloud_package micp', 'michl.module_idcsmart_cloud_package_id=micp.id')
            ->group('aisghl.host_id')
            ->where(function ($query) use($param, $clientId) {
                if(!empty($clientId)){
                    $query->where('h.client_id', $clientId);
                }
                if(!empty($param['id'])){
                    $query->where('aisghl.addon_idcsmart_security_group_id', $param['id']);
                }
                if(!empty($param['keywords'])){
                    $query->where('h.name|michl.ip|micp.name', 'like', "%{$param['keywords']}%");
                }
            })
            ->count();
        $list = $this->alias('aisghl')
            ->field('h.id,h.name,michl.ip,micp.name package')
            ->leftJoin('host h', 'h.id=aisghl.host_id')
            ->leftJoin('module_idcsmart_cloud_host_link michl', 'h.id=michl.host_id')
            ->leftJoin('module_idcsmart_cloud_package micp', 'michl.module_idcsmart_cloud_package_id=micp.id')
            ->group('aisghl.host_id')
            ->where(function ($query) use($param, $clientId) {
                if(!empty($clientId)){
                    $query->where('h.client_id', $clientId);
                }
                if(!empty($param['id'])){
                    $query->where('aisghl.addon_idcsmart_security_group_id', $param['id']);
                }
                if(!empty($param['keywords'])){
                    $query->where('h.name|michl.ip|micp.name', 'like', "%{$param['keywords']}%");
                }
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        return ['list' => $list, 'count' => $count];
    }

    public function linkSecurityGroup($param)
    {
        $clientId = get_client_id();
        
        $securityGroup = IdcsmartSecurityGroupModel::find($param['id']);
        if(empty($securityGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }
        if($securityGroup['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }

        $host = HostModel::find($param['host_id']);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
        }
        if($host['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
        }
        $server = ServerModel::find($host['server_id']);
        if($server['module']=='idcsmart_cloud'){
            $hostLink = HLM1::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
        }else if($server['module']=='common_cloud'){
            $hostLink = HLM2::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
        }else if($server['module']=='mf_cloud'){
            $hostLink = HLM3::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
        }else{
            return ['status'=>400, 'msg'=>lang_plugins('host_type_error')];
        }
        

        $securityGroupHostLink = $this->where('host_id', $param['host_id'])->where('addon_idcsmart_security_group_id', $param['id'])->find();
        if(!empty($securityGroupHostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('host_already_in_security_group')];
        }

        $securityGroupLink = IdcsmartSecurityGroupLinkModel::where('addon_idcsmart_security_group_id', $param['id'])
                                ->where('server_id', $host['server_id'])
                                ->find();
        $IC = new IC($server);

        if(!empty($securityGroupLink)){
            $post['security_group'] = $securityGroupLink['security_id'];
        }else{
            $client = ClientModel::find($clientId);

            

            $serverHash = ToolLogic::formatParam($server['hash']);

            // 开通参数
            $post = [];
            $post['hostname'] = $host['name'];

            // 定义用户参数
            $prefix = $serverHash['user_prefix'] ?? ''; // 用户前缀接口hash里面
            $username = $prefix.$client['id'];
            
            $userData = [
                'username'=>$username,
                'email'=>$client['email'] ?: '',
                'status'=>1,
                'real_name'=>$client['username'] ?: '',
                'password'=>rand_str()
            ];
            $IC->userCreate($userData);
            $userCheck = $IC->userCheck($username);
            if($userCheck['status'] != 200){
                return $userCheck;
            }
            $post['client'] = $userCheck['data']['id'];

            $post['type'] = $securityGroup['type'];
            // 自动创建安全组
            $securityGroupData = [
                'name' => $securityGroup['name'],
                'description' => $securityGroup['description'],
                'uid' => $post['client'],
                'type' => $securityGroup['type'],
                'create_default_rule' => 0,   // 不创建默认规则
            ];
            $securityGroupCreateRes = $IC->securityGroupCreate($securityGroupData);
            if($securityGroupCreateRes['status'] != 200){
                return $securityGroupCreateRes;
            }
            $post['security_group'] = $securityGroupCreateRes['data']['id'];
            // 保存关联
            $IdcsmartSecurityGroupLinkModel = new IdcsmartSecurityGroupLinkModel();
            $IdcsmartSecurityGroupLinkModel->saveSecurityGroupLink([
                'addon_idcsmart_security_group_id'=>$param['id'],
                'server_id'=>$server['id'],
                'security_id'=>$securityGroupCreateRes['data']['id'],
            ]);
            // 创建规则
            $IdcsmartSecurityGroupRuleLinkModel = new IdcsmartSecurityGroupRuleLinkModel();
            $securityGroupRule = IdcsmartSecurityGroupRuleModel::where('addon_idcsmart_security_group_id', $param['id'])->select()->toArray();
            foreach($securityGroupRule as $v){
                $ruleId = $v['id'];
                unset($v['id'], $v['lock']);
                $securityGroupRuleCreateRes = $IC->securityGroupRuleCreate($securityGroupCreateRes['data']['id'], $v);
                if($securityGroupRuleCreateRes['status'] == 200){
                    $IdcsmartSecurityGroupRuleLinkModel->saveSecurityGroupRuleLink([
                        'addon_idcsmart_security_group_rule_id'=>$ruleId,
                        'server_id'=>$server['id'],
                        'security_rule_id'=>$securityGroupRuleCreateRes['data']['id'] ?? 0
                    ]);
                }
            }
        }
        $res = $IC->linkSecurityGroup($post['security_group'], ['type'=>1,'cloud'=>[$hostLink['rel_id']]]);
        if($res['status']==200){
            $this->where('host_id', $param['host_id'])->delete();
            $this->create([
                'host_id' => $param['host_id'],
                'addon_idcsmart_security_group_id' => $param['id'],
            ]);
        }
        return $res;
    }

    public function unlinkSecurityGroup($param)
    {
        $clientId = get_client_id();

        $securityGroup = IdcsmartSecurityGroupModel::find($param['id']);
        if(empty($securityGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }
        if($securityGroup['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('security_group_is_not_exist')];
        }

        $host = HostModel::find($param['host_id']);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
        }
        if($host['client_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
        }
        $server = ServerModel::find($host['server_id']);
        if($server['module']=='idcsmart_cloud'){
            $hostLink = HLM1::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
        }else if($server['module']=='common_cloud'){
            $hostLink = HLM2::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
        }else if($server['module']=='mf_cloud'){
            $hostLink = HLM3::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
        }else{
            return ['status'=>400, 'msg'=>lang_plugins('host_type_error')];
        }

        $securityGroupHostLink = $this->where('host_id', $param['host_id'])->where('addon_idcsmart_security_group_id', $param['id'])->find();
        if(empty($securityGroupHostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('host_not_in_security_group')];
        }

        $IC = new IC($server);

        $res = $IC->delLinkSecurityGroup($hostLink['rel_id']);
        if($res['status']==200){
            $this->where('host_id', $param['host_id'])->delete();
        }
        return $res;
    }

}