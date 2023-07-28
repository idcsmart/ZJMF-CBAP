<?php 
namespace server\mf_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\OrderModel;
use server\mf_cloud\idcsmart_cloud\IdcsmartCloud;
use server\mf_cloud\logic\ToolLogic;

class ConfigModel extends Model{

	protected $name = 'module_mf_cloud_config';

    // 设置字段信息
    protected $schema = [
        'id'                        => 'int',
        'product_id'                => 'int',
        'host_prefix'               => 'string',
        'host_length'               => 'int',
        'node_priority'             => 'int',
        'ip_mac_bind'               => 'int',
        'support_ssh_key'           => 'int',
        'rand_ssh_port'             => 'int',
        'support_normal_network'    => 'int',
        'support_vpc_network'       => 'int',
        'support_public_ip'         => 'int',
        'backup_enable'             => 'int',
        'snap_enable'               => 'int',
        'disk_limit_enable'         => 'int',
        'reinstall_sms_verify'      => 'int',
        'reset_password_sms_verify' => 'int',
        'niccard'                   => 'int',
        'cpu_model'                 => 'int',
        'ipv6_num'                  => 'string',
        'nat_acl_limit'             => 'string',
        'nat_web_limit'             => 'string',
        'memory_unit'               => 'string',
    ];

    /**
     * 时间 2022-06-20
     * @title 获取设置
     * @desc 获取设置
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  string host_prefix - 主机名前缀
     * @return  int host_length - 主机名长度(包含前缀)
     * @return  int node_priority - 开通平衡规则(1=数量平均,2=负载最低,3=内存最低)
     * @return  int ip_mac_bind - 嵌套虚拟化(0=关闭,1=开启)
     * @return  int support_ssh_key - 是否支持SSH密钥(0=关闭,1=开启)
     * @return  int rand_ssh_port - 随机SSH端口(0=关闭,1=开启)
     * @return  int support_normal_network - 经典网络(0=不支持,1=支持)
     * @return  int support_vpc_network - VPC网络(0=不支持,1=支持)
     * @return  int support_public_ip - 是否允许公网IP(0=不支持,1=支持)
     * @return  int backup_enable - 是否启用备份(0=不启用,1=启用)
     * @return  int snap_enable - 是否启用快照(0=不启用,1=启用)
     * @return  int disk_limit_enable - 性能限制(0=不启用,1=启用)
     * @return  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @return  int niccard - 网卡驱动(0=默认,1=Realtek 8139,2=Intel PRO/1000,3=Virtio)
     * @return  int cpu_model - CPU模式(0=默认,1=host-passthrough,2=host-model,3=custom)
     * @return  string ipv6_num - IPv6数量
     * @return  string nat_acl_limit - NAT转发限制
     * @return  string nat_web_limit - NAT建站限制
     * @return  bool is_agent - 是否是代理商(是的时候才能添加资源包)
     * @return  int backup_data[].num - 备份数量
     * @return  float backup_data[].float - 备份价格
     * @return  int snap_data[].num - 快照数量
     * @return  float snap_data[].float - 快照价格
     * @return  int resource_package[].id - 
     * @return  int resource_package[].rid - 资源包ID
     * @return  string resource_package[].name - 资源包名称
     */
    public function indexConfig($param){
        $ProductModel = ProductModel::find($param['product_id'] ?? 0);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }

        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];

        $config = $this
                ->where($where)
                ->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $ProductModel->id;
            $this->insert($insert);
        }else{
            unset($config['id'], $config['product_id']);
        }

        // 是否支持代理商
        $config['is_agent'] = false;
        if($ProductModel['type'] == 'server'){
            $server = ServerModel::find($ProductModel['rel_id']);
            if(!empty($server)){
                $hash = ToolLogic::formatParam($server['hash']);
                $config['is_agent'] = isset($hash['account_type']) && $hash['account_type'] == 'agent';
            }
        }

        $BackupConfigModel = new BackupConfigModel();
        $backupData = $BackupConfigModel->backupConfigList(['product_id'=>$param['product_id'], 'type'=>'backup']);
        $config['backup_data'] = $backupData['list'];

        $backupData = $BackupConfigModel->backupConfigList(['product_id'=>$param['product_id'], 'type'=>'snap']);
        $config['snap_data'] = $backupData['list'];


        $config['resource_package'] = [];
        if($config['is_agent']){
            $config['resource_package'] = ResourcePackageModel::field('id,rid,name')->where('product_id', $ProductModel->id)->select()->toArray();
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $config,
        ];
        return $result;
    }

    /**
     * 时间 2022-06-20
     * @title 保存其他设置
     * @desc 保存其他设置
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param  string host_prefix - 主机名前缀
     * @param  int host_length - 主机名长度(包含前缀)
     * @param  int node_priority - 开通平衡规则(1=数量平均,2=负载最低,3=内存最低)
     * @param  int ip_mac_bind - 嵌套虚拟化(0=关闭,1=开启)
     * @param  int support_ssh_key - 是否支持SSH密钥(0=关闭,1=开启)
     * @param  int rand_ssh_port - 随机SSH端口(0=关闭,1=开启)
     * @param  int support_normal_network - 经典网络(0=不支持,1=支持)
     * @param  int support_vpc_network - VPC网络(0=不支持,1=支持)
     * @param  int support_public_ip - 是否允许公网IP(0=不支持,1=支持)
     * @param  int backup_enable - 是否启用备份(0=不启用,1=启用)
     * @param  int snap_enable - 是否启用快照(0=不启用,1=启用)
     * @param  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @param  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @param  int niccard - 网卡驱动(0=默认,1=Realtek 8139,2=Intel PRO/1000,3=Virtio)
     * @param  int cpu_model - CPU模式(0=默认,1=host-passthrough,2=host-model,3=custom)
     * @param  string ipv6_num - IPv6数量
     * @param  string nat_acl_limit - NAT转发限制
     * @param  string nat_web_limit - NAT建站限制
     * @param  array data.backup_data - 允许备份数量数据
     * @param  int data.backup_data[].num - 数量
     * @param  float data.backup_data[].float - 价格
     * @param  array data.snap_data - 允许快照数量数据
     * @param  int data.snap_data[].num - 数量
     * @param  float data.snap_data[].float - 价格
     * @param  array data.resource_package - 资源包数据
     * @param  int data.resource_package[].rid - 资源包ID
     * @param  string data.resource_package[].name - 资源包名称
     */
    public function saveConfig($param){
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $isAgent = false;
        $productId = $ProductModel->id;
        if($ProductModel['type'] == 'server'){
            $server = ServerModel::find($ProductModel['rel_id']);
            if(!empty($server)){
                $hash = ToolLogic::formatParam($server['hash']);
                $isAgent = isset($hash['account_type']) && $hash['account_type'] == 'agent';
            }
        }

        // 验证下推荐配置里的网络类型
        if($param['support_normal_network'] == 0){
            $recommendConfig = RecommendConfigModel::where('product_id', $productId)->where('network_type', 'normal')->find();
            if(!empty($recommendConfig)){
                return ['status'=>400, 'msg'=>lang_plugins('config_conflict_please_edit_recommend_config')];
            }
        }
        if($param['support_vpc_network'] == 0){
            $recommendConfig = RecommendConfigModel::where('product_id', $productId)->where('network_type', 'vpc')->find();
            if(!empty($recommendConfig)){
                return ['status'=>400, 'msg'=>lang_plugins('config_conflict_please_edit_recommend_config')];
            }
        }
        
        $appendLog = '';
        if(isset($param['backup_data'])){
            if(count($param['backup_data']) > 5){
                return ['status'=>400, 'msg'=>lang_plugins('over_max_allow_num')];
            }
            if( count(array_unique(array_column($param['backup_data'], 'num'))) != count($param['backup_data'])){
                return ['status'=>400, 'msg'=>lang_plugins('already_add_the_same_number')];
            }
            $BackupConfigModel = new BackupConfigModel();
            $res = $BackupConfigModel->saveBackupConfig($param['product_id'], $param['backup_data'], 'backup');
            $appendLog .= $res['data']['desc'];
        }
        if(isset($param['snap_data'])){
            if(count($param['snap_data']) > 5){
                return ['status'=>400, 'msg'=>lang_plugins('over_max_allow_num')];
            }
            if( count(array_unique(array_column($param['snap_data'], 'num'))) != count($param['snap_data'])){
                return ['status'=>400, 'msg'=>lang_plugins('already_add_the_same_number')];
            }
            $BackupConfigModel = new BackupConfigModel();
            $res = $BackupConfigModel->saveBackupConfig($param['product_id'], $param['snap_data'], 'snap');
            $appendLog .= $res['data']['desc'];
        }
        if($isAgent && isset($param['resource_package'])){
            $ResourcePackageModel = new ResourcePackageModel();
            $ResourcePackageModel->saveResourcePackage($productId, $param['resource_package']);
        }

        $config = $this->where('product_id', $param['product_id'])->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $param['product_id'];
            $this->insert($insert);
        }
        $this->update($param, ['product_id'=>$param['product_id']], ['host_prefix','host_length','node_priority','ip_mac_bind','support_ssh_key','rand_ssh_port','support_normal_network','support_vpc_network','support_public_ip','backup_enable','snap_enable','reinstall_sms_verify','reset_password_sms_verify','niccard','cpu_model','ipv6_num','nat_acl_limit','nat_web_limit']);

        $switch = [lang_plugins('switch_off'), lang_plugins('switch_on')];
        $nodePriority = [
            '',
            lang_plugins('node_priority_1'),
            lang_plugins('node_priority_2'),
            lang_plugins('node_priority_3'),
        ];
        $niccard = [
            lang_plugins('默认'),
            'Realtek 8139',
            'Intel PRO/1000',
            'Virtio',
        ];
        $cpuModel = [
            lang_plugins('默认'),
            'host-passthrough',
            'host-model',
            'custom',
        ];

        $desc = [
            'node_priority'             => lang_plugins('mf_cloud_config_node_priority'),
            'ip_mac_bind'               => lang_plugins('mf_cloud_config_ip_mac_bind'),
            'support_ssh_key'           => lang_plugins('mf_cloud_config_support_ssh_key'),
            'rand_ssh_port'             => lang_plugins('mf_cloud_config_rand_ssh_port'),
            'support_normal_network'    => lang_plugins('mf_cloud_config_support_normal_network'),
            'support_vpc_network'       => lang_plugins('mf_cloud_config_support_vpc_network'),
            'backup_enable'             => lang_plugins('backup_enable'),
            'snap_enable'               => lang_plugins('snap_enable'),
            'reinstall_sms_verify'      => lang_plugins('mf_cloud_reinstall_sms_verify'),
            'reset_password_sms_verify' => lang_plugins('mf_cloud_reset_password_sms_verify'),
            'niccard'                   => lang_plugins('网卡驱动'),
            'cpu_model'                 => lang_plugins('CPU模式'),
            'ipv6_num'                  => lang_plugins('IPv6数量'),
            'nat_acl_limit'             => lang_plugins('NAT转发'),
            'nat_web_limit'             => lang_plugins('NAT建站'),
        ];

        $config['node_priority']                = $nodePriority[ $config['node_priority'] ];
        $config['ip_mac_bind']                  = $switch[ $config['ip_mac_bind'] ];
        $config['support_ssh_key']              = $switch[ $config['support_ssh_key'] ];
        $config['rand_ssh_port']                = $switch[ $config['rand_ssh_port'] ];
        $config['support_normal_network']       = $switch[ $config['support_normal_network'] ];
        $config['support_vpc_network']          = $switch[ $config['support_vpc_network'] ];
        $config['backup_enable']                = $switch[ $config['backup_enable'] ];
        $config['snap_enable']                  = $switch[ $config['snap_enable'] ];
        $config['reinstall_sms_verify']         = $switch[ $config['reinstall_sms_verify'] ];
        $config['reset_password_sms_verify']    = $switch[ $config['reset_password_sms_verify'] ];
        $config['niccard']                      = $niccard[ $config['niccard'] ];
        $config['cpu_model']                    = $cpuModel[ $config['cpu_model'] ];

        if(isset($param['node_priority']) && $param['node_priority'] !== '')   $param['node_priority'] = $nodePriority[ $param['node_priority'] ];
        if(isset($param['ip_mac_bind']) && $param['ip_mac_bind'] !== '') $param['ip_mac_bind'] = $switch[ $param['ip_mac_bind'] ];
        if(isset($param['support_ssh_key']) && $param['support_ssh_key'] !== '') $param['support_ssh_key'] = $switch[ $param['support_ssh_key'] ];
        if(isset($param['rand_ssh_port']) && $param['rand_ssh_port'] !== '') $param['rand_ssh_port'] = $switch[ $param['rand_ssh_port'] ];
        if(isset($param['support_normal_network']) && $param['support_normal_network'] !== '') $param['support_normal_network'] = $switch[ $param['support_normal_network'] ];
        if(isset($param['support_vpc_network']) && $param['support_vpc_network'] !== '') $param['support_vpc_network'] = $switch[ $param['support_vpc_network'] ];
        if(isset($param['backup_enable']) && $param['backup_enable'] !== '') $param['backup_enable'] = $switch[ $param['backup_enable'] ];
        if(isset($param['snap_enable']) && $param['snap_enable'] !== '') $param['snap_enable'] = $switch[ $param['snap_enable'] ];
        if(isset($param['reinstall_sms_verify']) && $param['reinstall_sms_verify'] !== '') $param['reinstall_sms_verify'] = $switch[ $param['reinstall_sms_verify'] ];
        if(isset($param['reset_password_sms_verify']) && $param['reset_password_sms_verify'] !== '') $param['reset_password_sms_verify'] = $switch[ $param['reset_password_sms_verify'] ];
        if(isset($param['niccard']) && $param['niccard'] !== '') $param['niccard'] = $niccard[ $param['niccard'] ];
        if(isset($param['cpu_model']) && $param['cpu_model'] !== '') $param['cpu_model'] = $cpuModel[ $param['cpu_model'] ];

        $description = ToolLogic::createEditLog($config, $param, $desc);
        if(!empty($description) || !empty($appendLog) ){
            $description = lang_plugins('log_modify_config_success', [
                '{detail}'=>$description.$appendLog,
            ]);
            active_log($description, 'product', $param['product_id']);
        }
        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }

    /**
     * 时间 2023-02-02
     * @title 切换配置开关
     * @desc 切换配置开关
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   string field - 要修改的字段 require
     * @param   int status - 状态(0=关闭,1=开启) require
     */
    public function toggleSwitch($param){
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $config = $this->where('product_id', $param['product_id'])->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $param['product_id'];
            $this->insert($insert);
        }
        $this->update([ $param['field'] => $param['status'] ], ['product_id'=>$ProductModel->id]);

        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }

    /**
     * 时间 2022-06-22
     * @title 前台获取所有设置
     * @desc 前台获取所有设置
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @return  int data.product_type - 产品模式(0=固定配置,1=自定义配置)
     * @return  int data.support_ssh_key - 是否支持SSH密钥(0=不支持,1=支持)
     * @return  int data.buy_data_disk - 是否支持独立订购(0=不支持,1=支持)
     * @return  float data.price - 每10G价格
     * @return  string data.disk_min_size - 最小容量
     * @return  string data.disk_max_size - 最大容量
     * @return  int data.disk_max_num - 最大附加数量
     * @return  int data.backup_enable - 是否启用备份(0=不启用,1=启用)
     * @return  int data.snap_enable - 是否启用快照(0=不启用,1=启用)
     * @return  int data.backup_option[].num - 备份数量
     * @return  string data.backup_option[].price - 价格
     * @return  string data.backup_option[].free - 免费价格
     * @return  string data.backup_option[].onetime_fee - 一次性价格
     * @return  string data.backup_option[].month_fee - 月
     * @return  string data.backup_option[].quarter_fee - 季度
     * @return  string data.backup_option[].half_year_fee - 半年
     * @return  string data.backup_option[].year_fee - 年
     * @return  string data.backup_option[].two_year - 两年
     * @return  string data.backup_option[].three_year - 三年
     * @return  int data.snap_option[].num - 快照数量
     * @return  string data.snap_option[].price - 价格
     * @return  string data.snap_option[].free - 免费价格
     * @return  string data.snap_option[].onetime_fee - 一次性价格
     * @return  string data.snap_option[].month_fee - 月
     * @return  string data.snap_option[].quarter_fee - 季度
     * @return  string data.snap_option[].half_year_fee - 半年
     * @return  string data.snap_option[].year_fee - 年
     * @return  string data.snap_option[].two_year - 两年
     * @return  string data.snap_option[].three_year - 三年
     */
    public function homeConfig($param){
        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];

        $config = $this
                ->where($where)
                ->find();

        if(empty($config)){
            $config = $this->getDefaultConfig();
        }
        unset($config['disk_store_id'], $config['niccard'], $config['cpu_model']);

        $ProductModel = ProductModel::find($param['product_id']);

        $config['backup_option'] = [];
        $config['snap_option'] = [];

        $BackupConfigModel = new BackupConfigModel();
        if($config['backup_enable']){
            $res = $BackupConfigModel->backupConfigList(['product_id'=>$param['product_id'], 'type'=>'backup']);

            $backup_option = $res['data']['list'];

            foreach($backup_option as $k=>$v){
                $backup_option[$k]['free'] = '0.00';
                $backup_option[$k]['onetime_fee'] = amount_format($v['price']);
                $backup_option[$k]['month_fee'] = amount_format($v['price']);
                $backup_option[$k]['quarter_fee'] = amount_format(bcmul($v['price'], 3));
                $backup_option[$k]['half_year_fee'] = amount_format(bcmul($v['price'], 6));
                $backup_option[$k]['year_fee'] = amount_format(bcmul($v['price'], 12));
                $backup_option[$k]['two_year'] = amount_format(bcmul($v['price'], 24));
                $backup_option[$k]['three_year'] = amount_format(bcmul($v['price'], 36));
            }

            $config['backup_option'] = $backup_option;
        }
        if($config['snap_enable']){
            $res = $BackupConfigModel->backupConfigList(['product_id'=>$param['product_id'], 'type'=>'snap']);

            $snap_option = $res['data']['list'];

            foreach($snap_option as $k=>$v){
                $snap_option[$k]['free'] = '0.00';
                $snap_option[$k]['onetime_fee'] = amount_format($v['price']);
                $snap_option[$k]['month_fee'] = amount_format($v['price']);
                $snap_option[$k]['quarter_fee'] = amount_format(bcmul($v['price'], 3));
                $snap_option[$k]['half_year_fee'] = amount_format(bcmul($v['price'], 6));
                $snap_option[$k]['year_fee'] = amount_format(bcmul($v['price'], 12));
                $snap_option[$k]['two_year'] = amount_format(bcmul($v['price'], 24));
                $snap_option[$k]['three_year'] = amount_format(bcmul($v['price'], 36));
            }

            $config['snap_option'] = $snap_option;
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $config,
        ];
        return $result;
    }

    /**
     * 时间 2023-02-01
     * @title 获取默认其他设置
     * @desc 获取默认其他设置
     * @author hh
     * @version v1
     * @return  string host_prefix - 主机名前缀
     * @return  string host_length - 主机名长度(包含前缀)
     * @return  string node_priority - 开通平衡规则(1=数量平均,2=负载最低,3=内存最低)
     * @return  string ip_mac_bind - 嵌套虚拟化(0=关闭,1=开启)
     * @return  string support_ssh_key - 是否支持SSH密钥(0=关闭,1=开启)
     * @return  string rand_ssh_port - 随机SSH端口(0=关闭,1=开启)
     * @return  string support_normal_network - 经典网络(0=不支持,1=支持)
     * @return  string support_vpc_network - VPC网络(0=不支持,1=支持)
     * @return  string support_public_ip - 是否允许公网IP(0=不支持,1=支持)
     * @return  string backup_enable - 是否启用备份(0=不启用,1=启用)
     * @return  string snap_enable - 是否启用快照(0=不启用,1=启用)
     * @return  string disk_limit_enable - 性能限制(0=不启用,1=启用)
     * @return  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @return  int niccard - 网卡驱动(0=默认,1=Realtek 8139,2=Intel PRO/1000,3=Virtio)
     * @return  int cpu_model - CPU模式(0=默认,1=host-passthrough,2=host-model,3=custom)
     * @return  string ipv6_num - IPv6数量
     * @return  string nat_acl_limit - NAT转发
     * @return  string nat_web_limit - NAT建站
     */
    public function getDefaultConfig(){
        $defaultConfig = [
            'host_prefix'               => 'C',
            'host_length'               => '15',
            'node_priority'             => '1',
            'ip_mac_bind'               => '0',
            'support_ssh_key'           => '0',
            'rand_ssh_port'             => '0',
            'support_normal_network'    => '1',
            'support_vpc_network'       => '0',
            'support_public_ip'         => '0',
            'backup_enable'             => '0',
            'snap_enable'               => '0',
            'disk_limit_enable'         => '0',
            'reinstall_sms_verify'      => '0',
            'reset_password_sms_verify' => '0',
            'niccard'                   => '0',
            'cpu_model'                 => '0',
            'ipv6_num'                  => '',
            'nat_acl_limit'             => '',
            'nat_web_limit'             => '',
            'memory_unit'               => 'GB',
        ];
        return $defaultConfig;
    }

    /**
     * 时间 2022-09-27
     * @title 验证磁盘大小数量
     * @desc 
     * @author hh
     * @version v1
     * @param   [type] $data        [description]
     * @param   [type] $ConfigModel [description]
     * @return  [type]              [description]
     */
    public function checkDiskArr($data, $ConfigModel = null){
        $ConfigModel = $ConfigModel ?? $this;
        if(count($data) > $ConfigModel['disk_max_num']){
            return ['status'=>400, 'msg'=>lang_plugins('over_max_disk_num', ['{num}'=>$ConfigModel['disk_max_num']]) ];
        }
        $size = 0;
        foreach($data as $v){
            $check = $this->checkDisk($v);
            if($check['status'] == 400){
                return $check;
            }
            $size += $v;
        }
        return ['status'=>200, 'data'=>['size'=>$size]];
    }

    public function checkDisk($size, $ConfigModel = null){
        $ConfigModel = $ConfigModel ?? $this;

        if(!is_numeric($size) || !preg_match('/^[\d]+$/', $size)){
            return ['status'=>400, 'msg'=>lang_plugins('extra_data_disk_size_must_be_number') ];
        }
        if(!empty($ConfigModel['disk_min_size']) && $size < $ConfigModel['disk_min_size']){
            return ['status'=>400, 'msg'=>lang_plugins('extra_data_disk_size_error')];
        }
        if(!empty($ConfigModel['disk_max_size']) && $size > $ConfigModel['disk_max_size']){
            return ['status'=>400, 'msg'=>lang('extra_data_disk_size_error')];
        }
        if($size%10 != 0){
            return ['status'=>400, 'msg'=>lang_plugins('data_disk_size_format_error')];
        }
        return ['status'=>200, 'msg'=>lang_plugins('success_message')];
    }


    /**
     * 时间 2022-09-25
     * @title 
     * @desc 
     * @author hh
     * @version v1
     * @param   string x             - x
     * @return  [type] [description]
     */
    public function calConfigPrice($param){
        bcscale(2);
        // 验证产品和用户
        $host = HostModel::find($param['id']);
        if(empty($host) || $host['status'] != 'Active'){
            return ['status'=>400, 'msg'=>lang_plugins('host_not_create')];
        }
        $productId = $host['product_id'];
        // 前台判断
        $app = app('http')->getName();
        if($app == 'home'){
            if($host['client_id'] != get_client_id()){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
            }
        }    
        $hostLink = HostLinkModel::where('host_id', $param['id'])->find();
        if(empty($hostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('host_not_create')];
        }
        if( $hostLink[ $param['type'].'_num' ] == $param['num']){
            return ['status'=>400, 'msg'=>lang_plugins('num_not_change')];
        }
        $ConfigModel = ConfigModel::where('product_id', $host['product_id'])->find();

        $type = ['backup'=>lang_plugins('backup'), 'snap'=>lang_plugins('snap')];

        $ServerModel = ServerModel::find($host['server_id']);
        $IdcsmartCloud = new IdcsmartCloud($ServerModel);
        // 当前已用数量
        $res = $IdcsmartCloud->cloudSnapshot($hostLink['rel_id'], ['per_page'=>999, 'type'=>$param['type']]);
        if($res['status'] != 200){
            return ['status'=>400, 'msg'=>lang_plugins('host_status_except_please_wait_and_retry')];
        }
        if($param['num'] < ($res['data']['meta']['total'] ?? 0 )){
            return ['status'=>400, 'msg'=>lang_plugins('backup_use_over_max', ['{num}'=>$param['num']]) ];
        }
        if(!isset($ConfigModel[$param['type'].'_enable']) || $ConfigModel[$param['type'].'_enable'] == 0){
            return ['status'=>400, 'msg'=>lang_plugins('not_support_buy_backup', ['{type}'=>$type[$param['type']] ]) ];
        }
        $arr = BackupConfigModel::where('product_id', $host['product_id'])
            ->where('type', $param['type'])
            ->select()
            ->toArray();
        $arr = array_column($arr, 'price', 'num');
        if(!isset($arr[ $param['num'] ])){
            return ['status'=>400, 'msg'=>lang_plugins('number_error')];
        }
        $configData = json_decode($hostLink['config_data'], true);

        // 匹配周期
        $duration = DurationModel::where('product_id', $productId)->where('num', $configData['duration']['num'])->where('unit', $configData['duration']['unit'])->find();
        if(empty($duration)){
            return ['status'=>400, 'msg'=>lang_plugins('暂不支持当前产品升级')];
        }
        $firstDuration = DurationModel::field('id,name,num,unit')->where('product_id', $productId)->orderRaw('field(unit, "hour","day","month")')->order('num', 'asc')->find();
        // 计算倍率
        $multiplier = 1;
        if($duration['unit'] == $firstDuration['unit']){
            $multiplier = round($duration['num']/$firstDuration['num'], 2);
        }else{
            if($duration['unit'] == 'day' && $firstDuration['unit'] == 'hour'){
                $multiplier = round($duration['num']*24/$firstDuration['num'], 2);
            }else if($duration['unit'] == 'month' && $firstDuration['unit'] == 'hour'){
                $multiplier = round($duration['num']*30*24/$firstDuration['num'], 2);
            }else if($duration['unit'] == 'month' && $firstDuration['unit'] == 'day'){
                $multiplier = round($duration['num']*30/$firstDuration['num'], 2);
            }
        }
        $diffTime = $host['due_time'] - time();

        $price = 0;
        $priceDifference = 0;

        // 原价,找不到数量就当成0
        $oldPrice = bcmul($arr[ $hostLink[ $param['type'].'_num' ] ] ?? 0, $multiplier);
        $price = bcmul($arr[ $param['num'] ], $multiplier);

        // 增加价格系数
        $oldPrice = bcmul($oldPrice, $duration['price_factor']);
        $price = bcmul($price, $duration['price_factor']);

        $backupConfigData = [
            'type'  => $param['type'],
            'num'   => $param['num'],
            'price' => $price,
        ];
    
        if($host['billing_cycle'] == 'free'){
            $price = 0;
            $priceDifference = 0;
        }else{
            // 周期
            $priceDifference = bcsub($price, $oldPrice);
            $price = $priceDifference * $diffTime/$host['billing_cycle_time'];
        }
        $description = $type[$param['type']].'数量：'.$hostLink[ $param['type'].'_num' ].' => '.$param['num'];

        $price = max(0, $price);
        $price = amount_format($price);
        
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' => $price,
                'description' => $description,
                'price_difference' => $priceDifference,
                'renew_price_difference' => $priceDifference,
                'backup_config' => $backupConfigData,
            ]
        ];

        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 生成磁盘扩容订单
     * @desc 生成磁盘扩容订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   array remove_disk_id - 要取消订购的磁盘ID
     * @param   array add_disk - 新增磁盘大小
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createBackupConfigOrder($param){
        $res = $this->calConfigPrice($param);
        if($res['status'] == 400){
            return $res;
        }

        $OrderModel = new OrderModel();

        $data = [
            'host_id'     => $param['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'renew_price_difference' => $res['data']['renew_price_difference'],
            'upgrade_refund' => 0,
            'config_options' => [
                'type'       => 'modify_backup',
                'backup_type' => $param['type'],
                'num' => $param['num'],
                'backup_config' => $res['data']['backup_config'],
            ],
            'customfield' => $param['customfield'] ?? [],
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2023-02-14
     * @title 按照设定规则生成主机名
     * @desc 按照设定规则生成主机名
     * @author hh
     * @version v1
     */
    public function createHostName($product_id){
        $config = $this->where('product_id', $product_id)->find();
        if(empty($config) || empty($config['host_length']) || empty($config['host_prefix'])){
            $name = 'C'.rand_str(11, 'NUMBER');
        }else{
            $name = $config['host_prefix'].rand_str($config['host_length'] - strlen($config['host_prefix']), 'NUMBER');
        }
        return $name;
    }



}