<?php
namespace addon\idcsmart_sub_account\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\model\ClientModel;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ServerModel;
use app\admin\model\PluginModel;
use addon\idcsmart_sub_account\IdcsmartSubAccount;
use app\common\logic\ModuleLogic;
use app\home\model\ClientareaAuthModel;
use app\home\model\ClientareaAuthRuleModel;

/**
 * @title 子账户模型
 * @desc 子账户模型
 * @use addon\idcsmart_sub_account\model\IdcsmartSubAccountModel
 */
class IdcsmartSubAccountModel extends Model
{
    protected $name = 'addon_idcsmart_sub_account';

    // 设置字段信息
    protected $schema = [
        'id'      		        => 'int',
        'parent_id'             => 'int',
        'client_id'             => 'int',
        'auth'                  => 'string',
        'notice'                => 'string',
        'visible_product'       => 'string',
        'module'                => 'string',
        'host_id'               => 'string',
        'last_login_time'       => 'int',
        'last_login_ip'         => 'string',
        'last_action_time'      => 'int',
        'create_time'           => 'int',
        'update_time'           => 'int',
        'downstream_client_id'  => 'int',
    ];

    private function checkClient()
    {
        return get_client_id()==get_client_id(false);
    }

    # 子账户列表
    public function idcsmartSubAccountList($param, $app = '')
    {
        if($app=='admin'){
            $param['client_id'] = $param['id'] ?? 0;
        }else{
            /*if(!$this->checkClient()){
                return ['list' => [], 'count' => 0];
            }*/
            $param['client_id'] = get_client_id();
            if(empty($param['client_id'])){
                return ['list' => [], 'count' => 0];
            }
            
        }
        
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'aisa.'.$param['orderby'] : 'aisa.id';

    	$count = $this->alias('aisa')
            ->field('aisa.id')
            ->where(function ($query) use($param, $app) {
                if(!empty($param['client_id'])){
                    $query->where('aisa.parent_id', $param['client_id']);
                }
            })
            ->count();
        $list = $this->alias('aisa')
            ->field('aisa.client_id id,c.status,c.username,c.email,c.phone_code,c.phone,c.last_action_time')
            ->leftJoin('client c', 'c.id=aisa.client_id')
            ->where(function ($query) use($param, $app) {
                if(!empty($param['client_id'])){
                    $query->where('aisa.parent_id', $param['client_id']);
                }
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            if($app=='home'){
                unset($list[$key]['email'], $list[$key]['phone_code'], $list[$key]['phone']);
            }
        }
        return ['list' => $list, 'count' => $count];
    }

    public function idcsmartSubAccountDetail($id, $app='home'){

        if($app=='home'){
            if(!$this->checkClient()){
                return (object)[];
            }

            $clientId = get_client_id();
            // 验证用户ID
            $client = ClientModel::find($clientId);

            if (empty($client)){
                return (object)[];
            }
            
            // 验证子账户ID
            $idcsmartSubAccount = $this->alias('aisa')
                ->field('aisa.client_id id,c.username,c.email,c.phone_code,c.phone,aisa.auth,aisa.notice,aisa.visible_product,aisa.module,aisa.host_id')
                ->leftJoin('client c', 'c.id=aisa.client_id')
                ->where('aisa.parent_id', $clientId)
                ->where('aisa.client_id', $id)
                ->find();
        }else{
            // 验证子账户ID
            $idcsmartSubAccount = $this->alias('aisa')
                ->field('aisa.client_id id,c.username,c.email,c.phone_code,c.phone,aisa.auth,aisa.notice,aisa.visible_product,aisa.module,aisa.host_id')
                ->leftJoin('client c', 'c.id=aisa.client_id')
                ->where('aisa.client_id', $id)
                ->find();
        }
        
        if(empty($idcsmartSubAccount)){
            return (object)[];
        }

        $idcsmartSubAccount['auth'] = json_decode($idcsmartSubAccount['auth'], true);
        $idcsmartSubAccount['notice'] = json_decode($idcsmartSubAccount['notice'], true);
        $idcsmartSubAccount['project_id'] = IdcsmartSubAccountProjectModel::where('addon_idcsmart_sub_account_id', $id)->column('addon_idcsmart_project_id');
        $idcsmartSubAccount['module'] = json_decode($idcsmartSubAccount['module'], true);
        $idcsmartSubAccount['host_id'] = json_decode($idcsmartSubAccount['host_id'], true);

        return $idcsmartSubAccount;
    }

    # 创建子账户
    public function createIdcsmartSubAccount($param)
    {
        if(!$this->checkClient()){
            return ['status'=>400, 'msg'=>lang_plugins('fail_message')];
        }

        $clientId = get_client_id();
        // 验证用户ID
        $client = ClientModel::find($clientId);

        if (empty($client)){
            return ['status'=>400, 'msg'=>lang_plugins('fail_message')];
        }
        

        $count = $this->where('parent_id', $clientId)->count();
        if($count>=10){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_sub_account_create_max')];
        }

        $PluginModel = new PluginModel();
        $hasProject =  $PluginModel->checkPlugin('IdcsmartProject','addon');

        if(!$hasProject){
            if($param['visible_product']=='module'){
                $param['module'] = $param['module'] ?? [];
                $param['host_id'] = [];
                $ModuleLogic = new ModuleLogic();

                $module = $ModuleLogic->getModuleList();
                $module = array_column($module, 'name');

                if(count(array_diff($param['module'], $module))>0){
                    return ['status'=>400, 'msg'=>lang_plugins('param_error')];
                }

            }else if($param['visible_product']=='host'){
                $param['module'] = [];
                $param['host_id'] = $param['host_id'] ?? [];
                $host = HostModel::where('client_id', $clientId)->column('id');
                if(count(array_diff($param['host_id'], $host))>0){
                    return ['status'=>400, 'msg'=>lang_plugins('param_error')];
                }
            }
        }else{
            $param['module'] = [];
            $param['host_id'] = [];
        }
        $param['auth'] = $param['auth'] ?? [];
        $auth = ClientareaAuthModel::column('id');
        if(count(array_diff($param['auth'], $auth))>0){
            return ['status'=>400, 'msg'=>lang_plugins('param_error')];
        }
        $authId = ClientareaAuthModel::where('title', 'clientarea_auth_account_info')->value('id');
        if(!empty($authId)){
            $authId = ClientareaAuthModel::where('title', 'clientarea_auth_outline')->where('parent_id', $authId)->value('id');
            if(!empty($authId)){
                if(!in_array($authId, $param['auth'])){
                    return ['status'=>400, 'msg'=>lang_plugins('clientarea_auth_outline_require')];
                }
            }
        }

        $this->startTrans();
        try {
            $client = ClientModel::create([
                'username' => (isset($param['username']) && !empty($param['username']))?$param['username']:((isset($param['email']) && !empty($param['email']))?explode('@',$param['email'])[0]:((isset($param['phone']) && !empty($param['phone']))?$param['phone']:'')),
                'email' => $param['email']  ?? '',
                'phone_code' => $param['phone_code'] ?? 44,
                'phone' => $param['phone'] ?? '',
                'password' => idcsmart_password($param['password']), // 密码加密
                'language' => configuration('lang_home')??'zh-cn',
                'create_time' => time()
            ]);

            $idcsmartSubAccount = $this->create([
                'parent_id' => $clientId,
                'client_id' => $client->id,
                'auth' => json_encode($param['auth']),
                'notice' => json_encode($param['notice'] ?? []),
                'visible_product' => $param['visible_product'] ?? '',
                'module' => json_encode($param['module']),
                'host_id' => json_encode($param['host_id']),
                'create_time' => time(),
            ]);
            
            if($hasProject){
                $list = [];
                foreach ($param['project_id'] as $key => $value) {
                    $list[] = [
                        'addon_idcsmart_sub_account_id' => $idcsmartSubAccount->id,
                        'addon_idcsmart_project_id' => $value
                    ];
                }
                if(!empty($list)){
                    $IdcsmartSubAccountProjectModel = new IdcsmartSubAccountProjectModel();
                    $IdcsmartSubAccountProjectModel->saveAll($list);
                }
            }
            

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('create_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('create_success')];
    }

    # 编辑子账户
    public function updateIdcsmartSubAccount($param, $app='home')
    {
        if($app=='home'){
            if(!$this->checkClient()){
                return ['status'=>400, 'msg'=>lang_plugins('fail_message')];
            }

            $clientId = get_client_id();
            // 验证用户ID
            $client = ClientModel::find($clientId);

            if (empty($client)){
                return ['status'=>400, 'msg'=>lang_plugins('fail_message')];
            }
            
        }
        

        // 验证子账户ID
        $idcsmartSubAccount = $this->where('client_id', $param['id'])->find();
        if(empty($idcsmartSubAccount)){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_sub_account_is_not_exist')];
        }

        $client = ClientModel::find($param['id']);
        if(empty($client)){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_sub_account_is_not_exist')];
        }

        if($app=='home'){
            if($idcsmartSubAccount['parent_id']!=$clientId){
                return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_sub_account_is_not_exist')];
            }
        }

        $PluginModel = new PluginModel();
        $hasProject =  $PluginModel->checkPlugin('IdcsmartProject','addon');

        if(!$hasProject){
            if($param['visible_product']=='module'){
                $param['module'] = $param['module'] ?? [];
                $param['host_id'] = [];
                $ModuleLogic = new ModuleLogic();

                $module = $ModuleLogic->getModuleList();
                $module = array_column($module, 'name');

                if(count(array_diff($param['module'], $module))>0){
                    return ['status'=>400, 'msg'=>lang_plugins('param_error')];
                }

            }else if($param['visible_product']=='host'){
                $param['module'] = [];
                $param['host_id'] = $param['host_id'] ?? [];
                $host = HostModel::where('client_id', $idcsmartSubAccount['parent_id'])->column('id');
                if(count(array_diff($param['host_id'], $host))>0){
                    return ['status'=>400, 'msg'=>lang_plugins('param_error')];
                }
            }
        }else{
            $param['module'] = [];
            $param['host_id'] = [];
        }
        $param['auth'] = $param['auth'] ?? [];
        $auth = ClientareaAuthModel::column('id');
        if(count(array_diff($param['auth'], $auth))>0){
            return ['status'=>400, 'msg'=>lang_plugins('param_error')];
        }
        $authId = ClientareaAuthModel::where('title', 'clientarea_auth_account_info')->value('id');
        if(!empty($authId)){
            $authId = ClientareaAuthModel::where('title', 'clientarea_auth_outline')->where('parent_id', $authId)->value('id');
            if(!empty($authId)){
                if(!in_array($authId, $param['auth'])){
                    return ['status'=>400, 'msg'=>lang_plugins('clientarea_auth_outline_require')];
                }
            }
        }


        $this->startTrans();
        try {
            ClientModel::update([
                'email' => $param['email'] ?? '',
                'phone_code' => $param['phone_code'] ?? 44,
                'phone' => $param['phone'] ?? '',
                'password' => !empty($param['password']) ? idcsmart_password($param['password']) : $client['password'], // 密码加密
                'update_time' => time()
            ], ['id' => $param['id']]);

            $this->update([
                'auth' => json_encode($param['auth']),
                'notice' => json_encode($param['notice'] ?? []),
                'visible_product' => $param['visible_product'] ?? '',
                'module' => json_encode($param['module']),
                'host_id' => json_encode($param['host_id']),
                'update_time' => time()
            ], ['client_id' => $param['id']]);

            Cache::delete('home_auth_rule_'.$param['id']);

            if($hasProject){
                IdcsmartSubAccountProjectModel::where('addon_idcsmart_sub_account_id', $idcsmartSubAccount['id'])->delete();
                $list = [];
                foreach ($param['project_id'] as $key => $value) {
                    $list[] = [
                        'addon_idcsmart_sub_account_id' => $idcsmartSubAccount['id'],
                        'addon_idcsmart_project_id' => $value
                    ];
                }
                if(!empty($list)){
                    $IdcsmartSubAccountProjectModel = new IdcsmartSubAccountProjectModel();
                    $IdcsmartSubAccountProjectModel->saveAll($list);
                }
            }else{
                IdcsmartSubAccountProjectModel::where('addon_idcsmart_sub_account_id', $idcsmartSubAccount['id'])->delete();
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
        return ['status' => 200, 'msg' => lang_plugins('update_success')];
    }

    # 删除子账户
    public function deleteIdcsmartSubAccount($id)
    {
        if(!$this->checkClient()){
            return ['status'=>400, 'msg'=>lang_plugins('fail_message')];
        }

        $clientId = get_client_id();
        // 验证用户ID
        $client = ClientModel::find($clientId);

        if (empty($client)){
            return ['status'=>400, 'msg'=>lang_plugins('fail_message')];
        }



        // 验证子账户ID
        $idcsmartSubAccount = $this->where('client_id', $id)->find();
        if(empty($idcsmartSubAccount)){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_sub_account_is_not_exist')];
        }

        if($idcsmartSubAccount['parent_id']!=$clientId){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_sub_account_is_not_exist')];
        }
        $count = IdcsmartSubAccountHostModel::where('addon_idcsmart_sub_account_id', $idcsmartSubAccount['id'])->count();
        if($count>0){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_sub_account_cannot_delete')];
        }

        $this->startTrans();
        try {
            $this->destroy($idcsmartSubAccount['id']);

            IdcsmartSubAccountProjectModel::where('addon_idcsmart_sub_account_id', $idcsmartSubAccount['id'])->delete();
            IdcsmartSubAccountHostModel::where('addon_idcsmart_sub_account_id', $idcsmartSubAccount['id'])->delete();

            ClientModel::destroy($id);
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang_plugins('delete_success')];
    }

    # 子账户状态切换
    public function updateIdcsmartSubAccountStatus($param)
    {
        if(!$this->checkClient()){
            return ['status'=>400, 'msg'=>lang_plugins('fail_message')];
        }

        $clientId = get_client_id();
        // 验证用户ID
        $client = ClientModel::find($param['id']);

        if (empty($client)){
            return ['status'=>400, 'msg'=>lang_plugins('fail_message')];
        }

        // 验证子账户ID
        $idcsmartSubAccount = $this->where('client_id', $param['id'])->find();
        if(empty($idcsmartSubAccount)){
            return ['status'=>400, 'msg'=>lang_plugins('addon_idcsmart_sub_account_is_not_exist')];
        }

        $status = intval($param['status']);

        if ($client['status'] == $status){
            return ['status' => 400, 'msg' => lang_plugins('cannot_repeat_opreate')];
        }
        $this->startTrans();
        try{
            ClientModel::update([
                'status' => $status,
                'update_time' => time(),
            ],['id' => $param['id']]);

            $this->commit();
        }catch (\Exception $e){
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang_plugins('fail_message')];
        }

        return ['status' => 200, 'msg' => lang_plugins('success_message')];
    }

    public function getSubAccountHost($id){
        $subAccount = $this->where('client_id', $id)->find();
        if(!empty($subAccount)){
            $PluginModel = new PluginModel();
            $hasProject =  $PluginModel->checkPlugin('IdcsmartProject','addon');

            if(!$hasProject){
                $clientId = $subAccount['parent_id'];
                if($subAccount['visible_product']=='module'){
                    $subAccount['module'] = json_decode($subAccount['module'], true) ?? [];
                    $hostId = HostModel::alias('h')
                        ->leftJoin('server s', 's.id=h.server_id')
                        ->where('h.client_id', $clientId)
                        ->whereIn('s.module', $subAccount['module'])
                        ->column('h.id');
                }else if($subAccount['visible_product']=='host'){
                    $subAccount['host_id'] = json_decode($subAccount['host_id'], true) ?? [];
                    $hostId = HostModel::where('client_id', $clientId)->whereIn('id', $subAccount['host_id'])->column('id');
                }
            }else{
                $hostId = IdcsmartSubAccountProjectModel::alias('a')
                    ->field('a.addon_idcsmart_sub_account_id id,b.host_id')
                    ->leftjoin('addon_idcsmart_project_host b','b.project_id=a.addon_idcsmart_project_id')
                    ->where('a.addon_idcsmart_sub_account_id', $subAccount['id'])
                    ->select()
                    ->toArray();
            }
            return ['status' => 200, 'data' => ['host' => $hostId ?? []]];
        }else{
            return ['status' => 400];
        }
        
    }

    public function getSubAccountAuthRule($id)
    {
        $subAccount = $this->where('client_id', $id)->find();

        if(!empty($subAccount)){
            $auth = json_decode($subAccount['auth'], true);
            $rules = ClientareaAuthRuleModel::alias('ar')
                ->leftjoin('clientarea_auth_rule_link arl', 'arl.clientarea_auth_rule_id=ar.id')
                ->leftjoin('clientarea_auth au', 'au.id=arl.clientarea_auth_id')
                ->whereIn('au.id', $auth)
                ->column('ar.name');
        }else{
            $rules = [];
        }
        
        return $rules;
    }

    /**
     * 时间 2022-5-27
     * @title 当前子账户权限列表
     * @desc 当前子账户权限列表
     * @author theworld
     * @version v1
     * @return array list - 权限列表
     * @return int list[].id - 权限ID
     * @return string list[].title - 权限标题
     * @return string list[].url - 地址
     * @return int list[].order - 排序
     * @return int list[].parent_id - 父级ID
     * @return array list[].child - 权限子集
     * @return int list[].child[].id - 权限ID
     * @return string list[].child[].title - 权限标题
     * @return string list[].child[].url - 地址
     * @return int list[].child[].order - 排序
     * @return int list[].child[].parent_id - 父级ID
     * @return array list[].child[].child - 权限子集
     * @return int list[].child[].child[].id - 权限ID
     * @return string list[].child[].child[].title - 权限标题
     * @return string list[].child[].child[].url - 地址
     * @return int list[].child[].child[].order - 排序
     * @return int list[].child[].child[].parent_id - 父级ID
     * @return array rules - 权限规则
     */
    public function authList($id)
    {
        $rules = ClientareaAuthRuleModel::alias('ar')
            ->field('ar.title,ar.name,arl.clientarea_auth_id')
            ->leftjoin('clientarea_auth_rule_link arl', 'arl.clientarea_auth_rule_id=ar.id')
            ->select()
            ->toArray();

        $auths = $this->select()->toArray();
        $ruleList = [];
        foreach ($rules as $key => $value) {
            $ruleList[$value['clientarea_auth_id']][] = $value['name'];
        }

        $subAccount = $this->where('client_id', $id)->find();
        $authId = json_decode($subAccount['auth'], true);
        
        $auths = ClientareaAuthModel::field('id,title,url,order,parent_id,module')
            ->whereIn('id', $authId)
            ->order('order', 'asc')
            ->group('id')
            ->select()->toArray();
        
        // 将数组转换成树形结构
        $tree = [];
        $rules = [];
        if (is_array($auths)) {
            $refer = [];
            foreach ($auths as $key => $data) {
                $auths[$key]['title'] = !empty($data['module']) ? lang_plugins($data['title']) : lang($data['title']);
                $auths[$key]['rules'] = $ruleList[$data['id']] ?? [];
                $rules = array_merge($rules, $auths[$key]['rules']);
                $refer[$data['id']] = &$auths[$key];
            }
            foreach ($auths as $key => $data) {
                // 判断是否存在parent  获取他的父类id
                $parentId = $data['parent_id'];
                // 0为父类id的时候
                if ($parentId==0) {
                    $tree[] = &$auths[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent['child'][$data['id']] = &$auths[$key];
                        $parent['child'] = array_values($parent['child']);
                    }
                }
            }
        }
        return ['list' => $tree, 'rule' => $rules];
    }

    // 获取父账户ID
    public function getClientParentId($id)
    {
        $subAccount = $this->where('client_id', $id)->find();

        return $subAccount['parent_id'] ?? 0;
    }

    // 批量获取父账户信息
    public function idcsmartSubAccountParentList($param)
    {
        $param['client_id'] = !empty($param['id']) ? array_filter(explode(',', $param['id'])) : [];

        $list = $this->alias('aisa')
            ->field('aisa.client_id id,aisa.parent_id,c.username')
            ->leftJoin('client c', 'c.id=aisa.parent_id')
            ->where(function ($query) use($param) {
                if(!empty($param['client_id'])){
                    $query->whereIn('aisa.client_id', $param['client_id']);
                }
            })
            ->select()
            ->toArray();
        return ['list' => $list];
    }

    public function beforeTaskCreate($param)
    {
        if(!isset($param['task_data']['customfield']['sub_account'])){
            if(isset($param['type']) && in_array($param['type'], ['email', 'sms'])){
                $param['task_data'] = $param['task_data'] ?? [];
                $param['task_data']['name'] = $param['task_data']['name'] ?? '';
                if(isset($param['task_data']['name']) && in_array($param['task_data']['name'], ['host_pending', 'host_active', 'host_suspend', 'host_unsuspend', 'host_terminate', 'host_upgrad', 'host_renewal_first', 'host_renewal_second', 'host_renewal_second', 'host_overdue_first', 'host_overdue_second', 'host_overdue_third'])){
                    $param['task_data']['host_id'] = $param['task_data']['host_id'] ?? 0;
                    $host = HostModel::find($param['task_data']['host_id']);

                    if(!empty($host)){
                        $server = ServerModel::find($host['server_id']);

                        $subAccount = $this->where('parent_id', $host['client_id'])->select()->toArray();
                        if(!empty($subAccount)){
                            $taskList = [];

                            $PluginModel = new PluginModel();
                            $hasProject =  $PluginModel->checkPlugin('IdcsmartProject','addon');
                            if($hasProject){
                                $hostId = IdcsmartSubAccountProjectModel::alias('a')
                                    ->field('a.addon_idcsmart_sub_account_id id,b.host_id')
                                    ->leftjoin('addon_idcsmart_project_host b','b.project_id=a.addon_idcsmart_project_id')
                                    ->whereIn('a.addon_idcsmart_sub_account_id', array_column($subAccount, 'id'))
                                    ->select()
                                    ->toArray();
                                $hostList = [];
                                foreach ($hostId as $key => $value) {
                                    $hostList[$value['id']][] = $value['host_id'];
                                }    

                                foreach ($subAccount as $key => $value) {
                                    $value['notice'] = json_decode($value['notice'], true) ?? [];
                                    if(!in_array('product', $value['notice'])){
                                        continue;
                                    }
                                    $value['host'] = $hostList[$value['id']] ?? [];
                                    if(!in_array($param['task_data']['host_id'], $value['host'])){
                                        continue;
                                    }
                                    $taskList[] = $value['client_id'];
                                }
                            }else{
                                foreach ($subAccount as $key => $value) {
                                    $value['notice'] = json_decode($value['notice'], true) ?? [];
                                    if(!in_array('product', $value['notice'])){
                                        continue;
                                    }
                                    if($value['visible_product']=='host'){
                                        $value['host_id'] = json_decode($value['host_id'], true) ?? [];
                                        if(!in_array($param['task_data']['host_id'], $value['host_id'])){
                                            continue;
                                        }
                                        $taskList[] = $value['client_id'];
                                    }else if($value['visible_product']=='module'){
                                        $value['module'] = json_decode($value['module'], true) ?? [];
                                        if(!in_array($server['module'], $value['module'])){
                                            continue;
                                        }
                                        $taskList[] = $value['client_id'];
                                    }
                                    
                                }
                            }

                            foreach ($taskList as $key => $value) {
                                $taskData = $param['task_data'];
                                $taskData['client_id'] = $value;
                                $taskData['customfield']['sub_account'] = $value;
                                add_task([
                                    'type' => $param['type'],
                                    'description' => $param['description'],
                                    'task_data' => $taskData,      
                                ]); 
                            }

                        }


                    }
                }else if(in_array($param['task_data']['name'], ['order_create', 'order_overdue', 'admin_order_amount', 'order_pay', 'order_recharge'])){
                    $param['task_data']['order_id'] = $param['task_data']['order_id'] ?? 0;
                    $order = OrderModel::find($param['task_data']['order_id']);
                    if(!empty($order)){
                        $subAccount = $this->where('parent_id', $order['client_id'])->select()->toArray();
                        if(!empty($subAccount)){
                            $taskList = [];

                            foreach ($subAccount as $key => $value) {
                                $value['notice'] = json_decode($value['notice'], true) ?? [];
                                if(!in_array('order', $value['notice'])){
                                    continue;
                                }
                                $taskList[] = $value['client_id'];
                            }

                            foreach ($taskList as $key => $value) {
                                $taskData = $param['task_data'];
                                $taskData['client_id'] = $value;
                                $taskData['customfield']['sub_account'] = $value;
                                add_task([
                                    'type' => $param['type'],
                                    'description' => $param['description'],
                                    'task_data' => $taskData,      
                                ]); 
                            }
                        }
                    }
                }else if(in_array($param['task_data']['name'], ['admin_reply_ticket', 'client_close_ticket', 'client_create_ticket'])){
                    $param['task_data']['client_id'] = $param['task_data']['client_id'] ?? 0;
                    $client = ClientModel::find($param['task_data']['client_id']);
                    if(!empty($client)){
                        $subAccount = $this->where('parent_id', $client['id'])->select()->toArray();
                        if(!empty($subAccount)){
                            $taskList = [];

                            foreach ($subAccount as $key => $value) {
                                $value['notice'] = json_decode($value['notice'], true) ?? [];
                                if(!in_array('ticket', $value['notice'])){
                                    continue;
                                }
                                $taskList[] = $value['client_id'];
                            }

                            foreach ($taskList as $key => $value) {
                                $taskData = $param['task_data'];
                                $taskData['client_id'] = $value;
                                $taskData['customfield']['sub_account'] = $value;
                                add_task([
                                    'type' => $param['type'],
                                    'description' => $param['description'] ?? '',
                                    'task_data' => $taskData,      
                                ]); 
                            }
                        }
                    }
                }else if(in_array($param['task_data']['name'], ['recommend_notice'])){
                    $param['task_data']['client_id'] = $param['task_data']['client_id'] ?? 0;
                    $client = ClientModel::find($param['task_data']['client_id']);
                    if(!empty($client)){
                        $subAccount = $this->where('parent_id', $client['id'])->select()->toArray();
                        if(!empty($subAccount)){
                            $taskList = [];

                            foreach ($subAccount as $key => $value) {
                                $value['notice'] = json_decode($value['notice'], true) ?? [];
                                if(!in_array('recommend', $value['notice'])){
                                    continue;
                                }
                                $taskList[] = $value['client_id'];
                            }

                            foreach ($taskList as $key => $value) {
                                $taskData = $param['task_data'];
                                $taskData['client_id'] = $value;
                                $taskData['customfield']['sub_account'] = $value;
                                add_task([
                                    'type' => $param['type'],
                                    'description' => $param['description'],
                                    'task_data' => $taskData,      
                                ]); 
                            }
                        }
                    }
                }
            }
        }else{
            return false;
        }
    }

    public function afterHostCreate($param)
    {
        if (request()->is_api){
            $param['id'] = $param['id'] ?? 0;
            $downstreamClientId = intval($param['param']['downstream_client_id'] ?? 0);
            if(!empty($param['id']) && !empty($downstreamClientId)){
                $clientId = get_client_id();
                $idcsmartSubAccount = $this->where('parent_id', $clientId)->where('downstream_client_id', $downstreamClientId)->find();

                if(empty($idcsmartSubAccount)){
                    $client = ClientModel::create([
                        'username' => '下游账户'.$downstreamClientId,
                        'email' => '',
                        'phone_code' => 44,
                        'phone' => '',
                        'password' => idcsmart_password('12345678'), // 密码加密
                        'language' => configuration('lang_home')??'zh-cn',
                        'create_time' => time(),
                    ]);

                    $idcsmartSubAccount = $this->create([
                        'parent_id' => $clientId,
                        'client_id' => $client->id,
                        'auth' => json_encode([]),
                        'notice' => json_encode([]),
                        'visible_product' => 'module',
                        'module' => json_encode([]),
                        'host_id' => json_encode([]),
                        'create_time' => time(),
                        'downstream_client_id' => $downstreamClientId,
                    ]);
                }
                $list = [['addon_idcsmart_sub_account_id' =>  $idcsmartSubAccount->id, 'host_id' => $param['id']]];
                $IdcsmartSubAccountHostModel = new IdcsmartSubAccountHostModel();
                $IdcsmartSubAccountHostModel->saveAll($list);
            }
        }
    }
}