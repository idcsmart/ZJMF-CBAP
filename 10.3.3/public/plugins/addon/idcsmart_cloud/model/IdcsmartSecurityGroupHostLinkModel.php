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
        $param['orderby'] = 'id';//isset($param['orderby']) && in_array($param['orderby'], ['host_id']) ? 'aisghl.'.$param['orderby'] : 'aisghl.host_id';

        $clientId = get_client_id();
        if(empty($clientId)){
            return ['list' => [], 'count' => 0];
        }

    	$count = $this->alias('aisghl')
            ->leftJoin('host h', 'h.id=aisghl.host_id')
            ->group('aisghl.host_id')
            ->where(function ($query) use($param, $clientId) {
                if(!empty($clientId)){
                    $query->where('h.client_id', $clientId);
                }
                if(!empty($param['id'])){
                    $query->where('aisghl.addon_idcsmart_security_group_id', $param['id']);
                }
                // if(!empty($param['keywords'])){
                //     $query->where('h.name|michl.ip|micp.name', 'like', "%{$param['keywords']}%");
                // }
            })
            ->count();

        // 先查询实例所属模块
        $module = $this->alias('aisghl')
            ->leftJoin('host h', 'h.id=aisghl.host_id')
            ->leftJoin('server s', 'h.server_id=s.id')
            ->where(function ($query) use($param, $clientId) {
                if(!empty($clientId)){
                    $query->where('h.client_id', $clientId);
                }
                if(!empty($param['id'])){
                    $query->where('aisghl.addon_idcsmart_security_group_id', $param['id']);
                }
                // if(!empty($param['keywords'])){
                //     $query->where('h.name|michl.ip|micp.name', 'like', "%{$param['keywords']}%");
                // }
            })
            ->column('s.module');

        $module = array_unique($module);
        $module = array_intersect($module, ['idcsmart_cloud','common_cloud','mf_cloud','cloudpods']);

        $list = null;
        foreach($module as $v){
            if(is_null($list)){
                $list = $this
                        ->alias('aisghl')
                        ->field('h.id,h.name,hl.ip')
                        ->leftJoin('host h', 'h.id=aisghl.host_id')
                        ->leftJoin('server s', 'h.server_id=s.id')
                        ->leftJoin('module_'.$v.'_host_link hl', 'h.id=hl.host_id')
                        ->where(function ($query) use($param, $clientId) {
                            if(!empty($clientId)){
                                $query->where('h.client_id', $clientId);
                            }
                            if(!empty($param['id'])){
                                $query->where('aisghl.addon_idcsmart_security_group_id', $param['id']);
                            }
                            // if(!empty($param['keywords'])){
                            //     $query->where('h.name|michl.ip|micp.name', 'like', "%{$param['keywords']}%");
                            // }
                        })
                        ->where('s.module', $v)
                        ->group('h.id')
                        ->limit($param['limit'])
                        ->page($param['page'])
                        ->order($param['orderby'], $param['sort']);
            }else{
                $list->union(function($query) use ($v, $param, $clientId) {
                    $query->name('addon_idcsmart_security_group_host_link')
                        ->alias('aisghl')
                        ->field('h.id,h.name,hl.ip')
                        ->leftJoin('host h', 'h.id=aisghl.host_id')
                        ->leftJoin('server s', 'h.server_id=s.id')
                        ->leftJoin('module_'.$v.'_host_link hl', 'h.id=hl.host_id')
                        ->group('h.id')
                        ->where(function ($query) use($param, $clientId) {
                            if(!empty($clientId)){
                                $query->where('h.client_id', $clientId);
                            }
                            if(!empty($param['id'])){
                                $query->where('aisghl.addon_idcsmart_security_group_id', $param['id']);
                            }
                            // if(!empty($param['keywords'])){
                            //     $query->where('h.name|michl.ip|micp.name', 'like', "%{$param['keywords']}%");
                            // }
                        })
                        ->where('s.module', $v)
                        ->select();
                });
            }
        }
        if(!is_null($list)){
            $list = $list
                    ->select()
                    ->toArray();
        }

        return ['list' => $list ?? [], 'count' => $count];
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
        $securityGroupHostLink = $this->where('host_id', $param['host_id'])->where('addon_idcsmart_security_group_id', $param['id'])->find();
        if(!empty($securityGroupHostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('host_already_in_security_group')];
        }

        $server = ServerModel::find($host['server_id']);
        $server['password'] = aes_password_decode($server['password']);

        if($server['module']=='idcsmart_cloud'){
            $hostLink = HLM1::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            // 
            if($securityGroup['type'] != 'host'){
                return ['status'=>400, 'msg'=>lang_plugins('cannot_use_this_security_group')];
            }
        }else if($server['module']=='common_cloud'){
            $hostLink = HLM2::where('host_id', $param['host_id'])->find();
            if(empty($hostLink)){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if(empty($hostLink['rel_id'])){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_active')];
            }
            if($securityGroup['type'] != 'host'){
                return ['status'=>400, 'msg'=>lang_plugins('cannot_use_this_security_group')];
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
            $res = ['status'=>400, 'msg'=>lang_plugins('host_type_error')];

            $hookRes = hook('addon_idcsmart_cloud_link_security_group', ['server'=>$server, 'security_group'=>$securityGroup->toArray(), 'host_id'=>$param['host_id'] ]);
            foreach($hookRes as $v){
                if(!empty($v) && isset($v['status'])){
                    $res = $v;
                    break;
                }
            }
        }
        if(!isset($res)){
            $securityGroupLink = IdcsmartSecurityGroupLinkModel::where('addon_idcsmart_security_group_id', $param['id'])
                                ->where('server_id', $host['server_id'])
                                ->where('type', $hostLink['type'] ?? 'host')
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

                $post['type'] = $hostLink['type'] ?? 'host';
                // 自动创建安全组
                $securityGroupData = [
                    'name'                  => 'security-'.rand_str(12),
                    'description'           => $securityGroup['name'],
                    'uid'                   => $post['client'],
                    'type'                  => $post['type'],
                    'create_default_rule'   => 0,   // 不创建默认规则
                ];
                $securityGroupCreateRes = $IC->securityGroupCreate($securityGroupData);
                if($securityGroupCreateRes['status'] != 200){
                    return $securityGroupCreateRes;
                }
                $post['security_group'] = $securityGroupCreateRes['data']['id'];
                // 保存关联
                $IdcsmartSecurityGroupLinkModel = new IdcsmartSecurityGroupLinkModel();
                $IdcsmartSecurityGroupLinkModel->saveSecurityGroupLink([
                    'addon_idcsmart_security_group_id'  => $param['id'],
                    'server_id'                         => $server['id'],
                    'security_id'                       => $securityGroupCreateRes['data']['id'],
                    'type'                              => $post['type'],
                ]);
                // 创建规则
                $IdcsmartSecurityGroupRuleLinkModel = new IdcsmartSecurityGroupRuleLinkModel();
                $securityGroupRule = IdcsmartSecurityGroupRuleModel::where('addon_idcsmart_security_group_id', $param['id'])->select()->toArray();
                foreach($securityGroupRule as $v){
                    $ruleId = $v['id'];
                    unset($v['id'], $v['lock']);
                    $v = IdcsmartSecurityGroupRuleModel::transRule($v);

                    $securityGroupRuleCreateRes = $IC->securityGroupRuleCreate($securityGroupCreateRes['data']['id'], $v);
                    if($securityGroupRuleCreateRes['status'] == 200){
                        $IdcsmartSecurityGroupRuleLinkModel->saveSecurityGroupRuleLink([
                            'addon_idcsmart_security_group_rule_id' => $ruleId,
                            'server_id'                             => $server['id'],
                            'security_rule_id'                      => $securityGroupRuleCreateRes['data']['id'] ?? 0,
                            'type'                                  => $post['type'],
                        ]);
                    }
                }
                // 轻量版添加一条拒绝所有
                if($post['type'] == 'lightHost'){
                    $IC->securityGroupRuleCreate($securityGroupCreateRes['data']['id'], [
                        'description'   => lang_plugins('security_rule_deny_all'),
                        'direction'     => 'in',
                        'protocol'      => 'all',
                        'lock'          => 1,
                        'start_ip'      => '0.0.0.0',
                        'end_ip'        => '0.0.0.0',
                        'start_port'    => 1,
                        'end_port'      => 65535,
                        'priority'      => 1000,
                        'action'        => 'drop',
                    ]);
                }
            }
            $res = $IC->linkSecurityGroup($post['security_group'], ['type'=>1,'cloud'=>[$hostLink['rel_id']]]);
        }
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
        $securityGroupHostLink = $this->where('host_id', $param['host_id'])->where('addon_idcsmart_security_group_id', $param['id'])->find();
        if(empty($securityGroupHostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('host_not_in_security_group')];
        }

        $server = ServerModel::find($host['server_id']);
        $server['password'] = aes_password_decode($server['password']);

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
            $res = ['status'=>400, 'msg'=>lang_plugins('host_type_error')];

            $securityId = IdcsmartSecurityGroupLinkModel::where('addon_idcsmart_security_group_id', $param['id'])->where('server_id', $server['id'])->where('type', $hostLink['type'] ?? 'host')->value('security_id');
            if(!empty($securityId)){
                $hookRes = hook('addon_idcsmart_cloud_unlink_security_group', ['server'=>$server, 'security_id'=>$securityId, 'host_id'=>$param['host_id'] ]);
                foreach($hookRes as $v){
                    if(!empty($v) && isset($v['status'])){
                        $res = $v;
                        break;
                    }
                }
            }else{
                $res = ['status'=>200, 'msg'=>lang_plugins('delete_success')];
            }
        }
        if(!isset($res)){
            $IC = new IC($server);

            $res = $IC->delLinkSecurityGroup($hostLink['rel_id']);
        }
        if($res['status']==200){
            $this->where('host_id', $param['host_id'])->delete();
        }
        return $res;
    }

}