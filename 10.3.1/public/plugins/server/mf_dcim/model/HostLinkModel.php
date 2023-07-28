<?php 
namespace server\mf_dcim\model;

use think\Model;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\MenuModel;
use app\admin\model\PluginModel;
use app\common\model\CountryModel;
use server\mf_dcim\logic\ToolLogic;
use server\mf_dcim\idcsmart_dcim\Dcim;
use addon\idcsmart_renew\model\IdcsmartRenewAutoModel;

/**
 * @title 产品关联模型
 * @use server\mf_dcim\model\HostLinkModel
 */
class HostLinkModel extends Model{

	protected $name = 'module_mf_dcim_host_link';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'host_id'           => 'int',
        'rel_id'            => 'int',
        'data_center_id'    => 'int',
        'image_id'          => 'int',
        'power_status'      => 'string',
        'ip'                => 'string',
        'password'          => 'string',
        'config_data'       => 'string',
        'create_time'       => 'int',
        'update_time'       => 'int',
    ];

    /**
     * 时间 2023-02-08
     * @title DCIM产品列表页
     * @desc DCIM产品列表页
     * @author hh
     * @version v1
     * @param   int param.page 1 页数
     * @param   int param.limit - 每页条数
     * @param   string param.orderby - 排序(id,due_time,status)
     * @param   string param.sort - 升/降序
     * @param   string param.keywords - 关键字搜索
     * @param   int param.data_center_id - 数据中心搜索
     * @param   string param.status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 列表数据
     * @return  string data.list[].name - 产品标识
     * @return  string data.list[].status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
     * @return  int data.list[].due_time - 到期时间
     * @return  string data.list[].country - 国家
     * @return  string data.list[].country_code - 国家代码
     * @return  string data.list[].city - 城市
     * @return  string data.list[].package_name - 套餐名称
     * @return  string data.list[].ip - IP
     * @return  string data.list[].image_name - 镜像名称
     * @return  string data.list[].image_group_name - 镜像分组名称
     * @return  string data.list[].power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  int data.list[].active_time - 开通时间
     * @return  string data.list[].product_name - 商品名称
     * @return  string data.list[].icon - 镜像图标
     */
    public function idcsmartCloudList($param){
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'  => [],
                'count' => [],
            ]
        ];

        $clientId = get_client_id();

        if(empty($clientId)){
            return $result;
        }
        
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id','due_time','status']) ? $param['orderby'] : 'id';
        $param['orderby'] = 'h.'.$param['orderby'];  

        $where = [];
        $where[] = ['h.client_id', '=', $clientId];
        $where[] = ['h.status', '<>', 'Cancelled'];
        if(isset($param['keywords']) && trim($param['keywords']) !== ''){
            $where[] = ['pro.name|h.name|hl.ip', 'LIKE', '%'.$param['keywords'].'%'];
        }
        if(isset($param['data_center_id']) && !empty($param['data_center_id'])){
            $where[] = ['hl.data_center_id', '=', $param['data_center_id']];
        }
        if(isset($param['status']) && !empty($param['status'])){
            if($param['status'] == 'Pending'){
                $where[] = ['h.status', 'IN', ['Pending','Failed']];
            }else if(in_array($param['status'], ['Unpaid','Active','Suspended','Deleted'])){
                $where[] = ['h.status', '=', $param['status']];
            }
        }
        if(isset($param['m']) && !empty($param['m'])){
            $MenuModel = MenuModel::where('menu_type', 'module')
                        ->where('module', 'mf_dcim')
                        ->where('id', $param['m'])
                        ->find();
            if(!empty($MenuModel) && !empty($MenuModel['product_id'])){
                $MenuModel['product_id'] = json_decode($MenuModel['product_id'], true);
                if(!empty($MenuModel['product_id'])){
                    $where[] = ['h.product_id', 'IN', $MenuModel['product_id'] ];
                }
            }
        }

        // 获取子账户可见产品
        $res = hook('get_client_host_id', ['client_id' => get_client_id(false)]);
        $res = array_values(array_filter($res ?? []));
        foreach ($res as $key => $value) {
            if(isset($value['status']) && $value['status']==200){
                $hostId = $value['data']['host'];
            }
        }
        if(isset($hostId) && !empty($hostId)){
            $where[] = ['h.id', 'IN', $hostId];
        }

        $count = $this
            ->alias('hl')
            ->join('host h', 'hl.host_id=h.id')
            ->leftJoin('product pro', 'h.product_id=pro.id')
            ->leftJoin('module_mf_dcim_data_center dc', 'hl.data_center_id=dc.id')
            ->leftJoin('module_mf_dcim_image i', 'hl.image_id=i.id')
            ->where($where)
            ->count();

        $host = $this
            ->alias('hl')
            ->field('h.id,h.name,h.status,h.active_time,h.due_time,pro.name product_name,c.name_zh country,c.iso country_code,dc.city,dc.area,hl.ip,hl.power_status,i.name image_name,ig.name image_group_name,ig.icon')
            ->join('host h', 'hl.host_id=h.id')
            ->leftJoin('product pro', 'h.product_id=pro.id')
            ->leftJoin('module_mf_dcim_data_center dc', 'hl.data_center_id=dc.id')
            ->leftJoin('country c', 'dc.country_id=c.id')
            ->leftJoin('module_mf_dcim_image i', 'hl.image_id=i.id')
            ->leftJoin('module_mf_dcim_image_group ig', 'i.image_group_id=ig.id')
            ->where($where)
            ->withAttr('status', function($val){
                return $val == 'Failed' ? 'Pending' : $val;
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->group('h.id')
            ->select()
            ->toArray();

        $result['data']['list']  = $host;
        $result['data']['count'] = $count;
        return $result;
    }

    /**
     * 时间 2022-06-30
     * @title 详情
     * @desc 详情
     * @author hh
     * @version v1
     * @param   int $hostId - 产品ID
     * @return  int data.rel_id - 魔方云ID
     * @return  string data.ip - IP地址
     * @return  int data.backup_num - 允许备份数量
     * @return  int data.snap_num - 允许快照数量
     * @return  string data.power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  int data.data_center.id - 数据中心ID
     * @return  string data.data_center.city - 城市
     * @return  string data.data_center.country_name - 国家
     * @return  string data.data_center.iso - 图标
     * @return  int data.image.id - 镜像ID
     * @return  string data.image.name - 镜像名称
     * @return  string data.image.image_group_name - 镜像分组
     * @return  int data.package.id - 套餐ID
     * @return  string data.package.name - 套餐名称
     * @return  string data.package.description - 套餐描述
     * @return  string data.package.cpu - cpu
     * @return  string data.package.memory - 内存(MB)
     * @return  string data.package.in_bw - 进带宽
     * @return  string data.package.out_bw - 出带宽
     * @return  string data.package.system_disk_size - 系统盘(GB)
     * @return  int data.security_group.id - 关联的安全组ID(0=没关联)
     * @return  string data.security_group.name - 关联的安全组名称
     * @return  string data.duration - 周期
     * @return  string data.first_payment_amount - 首付金额
     * @return  int data.config.reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int data.config.reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     */
    public function detail($hostId){
        $res = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>(object)[]
        ];
        $host = HostModel::find($hostId);
        if(empty($host)){
            return $res;
        }
        if(app('http')->getName() == 'home' && $host['client_id'] != get_client_id()){
            return $res;
        }
        $hostLink = $this->where('host_id', $hostId)->find();
        
        if(!empty($hostLink)){
            $configData = json_decode($hostLink['config_data'], true);

            $data = [];
            $data['order_id'] = $host['order_id'];
            $data['ip'] = $hostLink['ip'];
            $data['power_status'] = $hostLink['power_status'];
            
            $data['model_config'] = [
                'id'    => $configData['model_config']['id'],
                'name' => $configData['model_config']['name'],
                'cpu' => $configData['model_config']['cpu'],
                'cpu_param' => $configData['model_config']['cpu_param'],
                'memory' => $configData['model_config']['memory'],
                'disk' => $configData['model_config']['disk'],
            ];

            $data['line'] = [
                'id' => $configData['line']['id'] ?? 0,
                'name' => $configData['line']['name'] ?? '',
                'bill_type' => $configData['line']['bill_type'] ?? 'bw',
            ];
            $data['bw'] = $configData['bw']['value'] ?? 0;
            if(isset($configData['flow'])){
                $data['flow'] = $configData['flow']['value'];
            }
            $data['ip_num'] = $configData['ip']['value'] ?? 0;
            $data['peak_defence'] = $configData['defence']['value'] ?? 0;
            
            $image = ImageModel::alias('i')
                    ->field('i.id,i.name,ig.name image_group_name,ig.icon')
                    ->leftJoin('module_mf_dcim_image_group ig', 'i.image_group_id=ig.id')
                    ->where('i.id', $hostLink['image_id'])
                    ->find();
            if(!empty($image)){
                if($image['image_group_name'] == 'Windows'){
                    $data['username'] = 'administrator';
                }else{
                    $data['username'] = 'root';
                }
            }else{
                $data['username'] = '';
            }
            $data['password'] = aes_password_decode($hostLink['password']);

            $dataCenter = DataCenterModel::find($configData['data_center']['id']);
            if(empty($dataCenter)){
                $dataCenter = $configData['data_center'];
            }
            $data['data_center'] = [
                'id' => $dataCenter['id'],
                'city' => $dataCenter['city'],
                'area' => $dataCenter['area'],
            ];
            $country = CountryModel::find($dataCenter['country_id']);
            $data['data_center']['country'] = $country['name_zh'];
            $data['data_center']['iso'] = $country['iso'];
            
            $data['image'] = $image ?? (object)[];
            $data['config'] = ConfigModel::field('reinstall_sms_verify,reset_password_sms_verify')->where('product_id', $host['product_id'])->find() ?? (object)[];

            $res['data'] = $data;
        }
        return $res;
    }

    /**
     * 时间 2023-02-27
     * @title
     * @desc
     * @url
     * @method  POST
     * @author hh
     * @version v1
     * @param   string x       -             x
     * @param   [type] $hostId [description]
     * @return  [type]         [description]
     */
    public function detailPart($hostId){
        $res = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>(object)[]
        ];

        $data = [];
        // 先不弄缓存试试
        $cache = '';//cache('MODULE_MF_CLOUD_DETAIL_'.$hostId);
        if(!empty($cache)){
            if(app('http')->getName() == 'home' && $cache['client_id'] != get_client_id()){
                return $res;
            }
            $data = [
                'data_center' => $cache['data_center'],
                'ip' => $cache['ip'],
                'power_status' => $cache['power_status'],
                'image' => $cache['image'],
            ];
        }else{
            $host = HostModel::find($hostId);
            if(empty($host)){
                return $res;
            }
            if(app('http')->getName() == 'home' && $host['client_id'] != get_client_id()){
                return $res;
            }

            $hostLink = $this->where('host_id', $hostId)->find();
            $configData = json_decode($hostLink['config_data'], true);

            $dataCenter = DataCenterModel::find($configData['data_center']['id']);
            if(empty($dataCenter)){
                $dataCenter = $configData['data_center'];
            }
            $data['data_center'] = [
                'id' => $dataCenter['id'],
                'city' => $dataCenter['city'],
                'area' => $dataCenter['area'],
            ];
            $country = CountryModel::find($dataCenter['country_id']);
            $data['data_center']['country'] = $country['name_zh'];
            $data['data_center']['iso'] = $country['iso'];

            $data['ip'] = $hostLink['ip'];
            $data['power_status'] = $hostLink['power_status'];
            
            $image = ImageModel::alias('i')
                    ->field('i.id,i.name,ig.name image_group_name,ig.icon')
                    ->leftJoin('module_mf_dcim_image_group ig', 'i.image_group_id=ig.id')
                    ->where('i.id', $hostLink['image_id'])
                    ->find();
            $data['image'] = $image ?? (object)[];
        }
        $res['data'] = $data;
        return $res;
    }

    /* 模块定义操作 */

    /**
     * 时间 2023-02-09
     * @title 模块开通
     * @desc 模块开通
     * @author hh
     * @version v1
     * @param   ServerModel $param.server - ServerModel实例
     * @param   HostModel $param.host - HostModel实例
     * @param   ProductModel $param.product - ProductModel实例
     */
    public function createAccount($param){
        $Dcim = new Dcim($param['server']);

        $serverHash = ToolLogic::formatParam($param['server']['hash']);
        $prefix = $serverHash['user_prefix'] ?? ''; // 用户前缀接口hash里面

        $hostId = $param['host']['id'];
        $productId = $param['product']['id'];

        // 开通参数
        $post = [];
        $post['user_id'] = $prefix . $param['client']['id'];
        
        // 获取当前配置
        $hostLink = $this->where('host_id', $hostId)->find();
        if(!empty($hostLink) && $hostLink['rel_id'] > 0){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_host_already_created')];
        }
        $configData = json_decode($hostLink['config_data'], true);
        $line = LineModel::find($configData['line']['id']);
        if(!empty($line)){
            $configData['line'] = $line->toArray();
        }
        // 线路带宽
        if($configData['line']['bill_type'] == 'bw' && isset($configData['bw'])){
            $optionBw = OptionModel::where('product_id', $productId)->where('rel_type', OptionModel::LINE_BW)->where('rel_id', $configData['line']['id'])->where(function($query) use ($configData) {
                $query->whereOr('value', $configData['bw']['value'])
                      ->whereOr('(min_value<="'.$configData['bw']['value'].'" AND max_value>="'.$configData['bw']['value'].'")');
            })->find();
            if(!empty($optionBw)){
                $configData['bw']['other_config'] = json_decode($optionBw['other_config'], true);
            }
        }
        $modelConfig = ModelConfigModel::find($configData['model_config']['id']);
        if(!empty($modelConfig)){
            $configData['model_config'] = $modelConfig->toArray();
        }
        
        $post['server_group'] = $configData['model_config']['group_id'];

        if($configData['line']['bill_type'] == 'bw'){
            $post['in_bw'] = $configData['bw']['value'];
            $post['out_bw'] = $configData['bw']['value'];

            if(is_numeric($configData['bw']['other_config']['in_bw'])){
                $post['in_bw'] = $configData['bw']['other_config']['in_bw'];
            }
            $post['limit_traffic'] = 0;
        }else{
            // 流量
            $post['in_bw'] = $configData['flow']['other_config']['in_bw'];
            $post['out_bw'] = $configData['flow']['other_config']['out_bw'];

            $post['limit_traffic'] = $configData['flow']['value'];
        }
        // 带宽NO_CHANGE判断
        if($post['in_bw'] == 'NC' || $post['in_bw'] == 'NO_CHANGE'){
            $post['in_bw'] = 'NO_CHANGE';
        }
        if($post['out_bw'] == 'NC' || $post['out_bw'] == 'NO_CHANGE'){
            $post['out_bw'] = 'NO_CHANGE';
        }
        $ipNum = $configData['ip']['value'];
        if(is_numeric($ipNum)){
            $post['ip_num'] = $ipNum;
        }else if($ipNum == 'NO_CHANGE' || $ipNum == 'NC'){
            $post['ip_num'] = 'NO_CHANGE';
        }else{  //分组形式2_2,1_1  数量_分组id
            $ipNum = ToolLogic::formatDcimIpNum($ipNum);
            if($ipNum === false){
                $result['status'] = 400;
                $result['msg'] = 'IP数量格式有误';
                return $result;
            }
            $post['ip_num'] = $ipNum;
        }
        // 可以使用设置的IP分组
        if(is_numeric($post['ip_num'])){
            if($configData['line']['defence_enable'] == 1 && is_numeric($configData['line']['defence_ip_group']) && isset($configData['defence'])){
                $ipGroup = $configData['line']['defence_ip_group'];
            }else if(is_numeric($configData['line']['bw_ip_group'])){
                $ipGroup = $configData['line']['bw_ip_group'];
            }
            if(isset($ipGroup) && !empty($ipGroup)){
                $post['ip_num'] = [$ipGroup => $post['ip_num']];
            }
        }
        $image = ImageModel::find($configData['image']['id']);
        if(!empty($image)){
            $configData['image'] = $image->toArray();
        }
        $post['os'] = $configData['image']['rel_image_id'];
        $post['hostid'] = $hostId;
        
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);

        if($config['data']['rand_ssh_port'] == 1){
            $post['port'] = mt_rand(100, 65535);
        }
        
        $res = $Dcim->create($post);
        if($res['status'] == 200){
            $result = [
                'status'=>200,
                'msg'   =>lang_plugins('mf_dcim_host_create_success')
            ];

            $update = [];
            $update['rel_id'] = $res['data']['id'];
            $update['password'] = aes_password_encode($res['data']['password']);
            $update['ip'] = $res['data']['zhuip'] ?? '';
            
            $this->where('id', $hostLink['id'])->update($update);
        }else{
            $result = [
                'status'=>400,
                'msg'=>$res['msg'] ?: lang_plugins('mf_dcim_host_create_fail'),
            ];
            $this->where('id', $hostLink['id'])->update(['power_status'=>'fault']);
        }
        return $result;
    }

    /**
     * 时间 2023-02-09
     * @title 模块暂停
     * @desc 模块暂停
     * @author hh
     * @version v1
     */
    public function suspendAccount($param){
        $hostLink = HostLinkModel::where('host_id', $param['host']['id'])->find();
        $id = $hostLink['rel_id'] ?? 0;
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_link_dcim')];
        }
        $Dcim = new Dcim($param['server']);
        $res = $Dcim->suspend(['id'=>$id, 'hostid'=>$param['host']['id']]);
        if($res['status'] == 200){
            $result = [
                'status'=>200,
                'msg'=>lang_plugins('mf_dcim_suspend_success'),
            ];
        }else{
            $result = [
                'status'=>400,
                'msg'=>lang_plugins('mf_dcim_suspend_fail'),
            ];
        }
        return $result;
    }

    /**
     * 时间 2023-02-09
     * @title 模块解除暂停
     * @desc 模块解除暂停
     * @author hh
     * @version v1
     */
    public function unsuspendAccount($param){
        $hostLink = HostLinkModel::where('host_id', $param['host']['id'])->find();
        $id = $hostLink['rel_id'] ?? 0;
        if(empty($id)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_link_dcim')];
        }
        $Dcim = new Dcim($param['server']);
        $res = $Dcim->unsuspend(['id'=>$id, 'hostid'=>$param['host']['id']]);
        if($res['status'] == 200){
            $result = [
                'status'=>200,
                'msg'=>lang_plugins('mf_dcim_unsuspend_success'),
            ];
        }else{
            $result = [
                'status'=>400,
                'msg'=>lang_plugins('mf_dcim_unsuspend_fail'),
            ];
        }
        return $result;
    }

    /**
     * 时间 2023-02-09
     * @title 模块删除
     * @desc 模块删除
     * @author hh
     * @version v1
     */
    public function terminateAccount($param){
        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        $id = $hostLink['rel_id'] ?? 0;
        if(empty($id)){
            $result = [
                'status'    => 200,
                'msg'       => lang_plugins('delete_success'),
            ];
            return $result;
            // return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_link_dcim')];
        }
        $Dcim = new Dcim($param['server']);
        $res = $Dcim->delete(['id'=>$id, 'hostid'=>$param['host']['id']]);
        if($res['status'] == 200){
            $configData = json_decode($hostLink['config_data'], true);

            $notes = [
                '产品标识：'.$param['host']['name'],
                'IP地址：'.$hostLink['ip'],
                '操作系统：'.$configData['image']['name'],
                'ID：'.$hostLink['rel_id']
            ];
            HostLinkModel::where('host_id', $param['host']['id'])->update(['rel_id'=>0, 'ip'=>'']);

            HostModel::where('id', $param['host']['id'])->update(['notes'=>implode("\r\n", $notes)]);

            $result = [
                'status'=>200,
                'msg'=>lang_plugins('delete_success'),
            ];

        }else{
            $result = [
                'status'=>400,
                'msg'=>lang_plugins('delete_fail'),
            ];
        }
        return $result;
    }

    public function renew($param){
        $hostId = $param['host']['id'];
        $productId = $param['product']['id'];

        $hostLink = $this->where('host_id', $hostId)->find();
        if(!empty($hostLink)){
            $configData = json_decode($hostLink['config_data'], true);

            // 获取当前周期
            $duration = DurationModel::where('product_id', $productId)->where('name', $param['host']['billing_cycle_name'])->find();
            if(!empty($duration)){
                $configData['duration'] = $duration;
                $this->where('host_id', $hostId)->update(['config_data'=>json_encode($configData)]);
            }
        }
    }

    /**
     * 时间 2022-06-28
     * @title 升降级后调用
     * @author hh
     * @version v1
     */
    public function changePackage($param){
        // 判断是什么类型
        if(!isset($param['custom']['type'])){
            return ['status'=>400, 'msg'=>lang_plugins('param_error')];
        }
        $productId = $param['product']['id'];   // 商品ID
        $hostId    = $param['host']['id'];      // 产品ID
        $custom    = $param['custom'] ?? [];    // 升降级参数

        if($custom['type'] == 'buy_image'){
            // 购买镜像
            $HostImageLinkModel = new HostImageLinkModel();
            $HostImageLinkModel->saveLink($hostId, $custom['image_id']);
        }else if($custom['type'] == 'upgrade_common_config'){
            $hostLink = $this->where('host_id', $hostId)->find();

            $configData = json_decode($hostLink['config_data'], true);
            $oldConfigData = $configData;
            $newConfigData = $custom['new_config_data'];
            foreach($newConfigData as $k=>$v){
                $configData[$k] = $v;
            }

            // 保存新的配置
            $update = [
                'config_data' => json_encode($configData),
            ];
            $this->update($update, ['host_id'=>$hostId]);
            
            $id = $hostLink['rel_id'] ?? 0;
            if(empty($id)){
                $description = '升降级配置失败,原因:未关联DCIMID';
                active_log($description, 'host', $hostId);
                return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
            }
            $Dcim = new Dcim($param['server']);

            // 有升降级IP
            if(isset($newConfigData['ip'])){
                $ipGroup = 0;
                // 获取下线路信息
                $line = LineModel::find($configData['line']['id']);
                if(!empty($line)){
                    if($line['defence_enable'] == 1 && isset($configData['defence']['value']) && !empty($configData['defence']['value'])){
                        $ipGroup = $line['defence_ip_group'];
                    }else if($line['bill_type'] == 'bw'){
                        $ipGroup = $line['bw_ip_group'];
                    }
                }

                $post = [];
                $post['id'] = $id;

                $ipNum = $newConfigData['ip']['value'];
                if(is_numeric($ipNum)){
                    if(!empty($ipGroup)){
                        $post['ip_num'][ $ipGroup ] = $ipNum;
                    }else{
                        $post['ip_num'] = $ipNum;
                    }
                }else if($ipNum == 'NO_CHANGE' || $ipNum == 'NC'){
                    $post['ip_num'] = 'NO_CHANGE';
                }else{  //分组形式2_2,1_1  数量_分组id
                    $ipNum = ToolLogic::formatDcimIpNum($ipNum);
                    // if($ipNum === false){
                    //     $result['status'] = 400;
                    //     $result['msg'] = 'IP数量格式有误';
                    //     return $result;
                    // }
                    $post['ip_num'] = $ipNum;
                }
                $res = $Dcim->modifyIpNum($post);
                if($res['status'] == 200){
                    // 重新获取IP
                    $detail = $Dcim->detail(['id'=>$id]);
                    if($detail['status'] == 200){
                        $update = [];
                        $update['ip'] = $detail['ip']['subnet_ip'][0]['ipaddress'] ?? $detail['ipaddress'][0] ?? '';

                        $this->where('host_id', $hostId)->update($update);
                    }
                    $description[] = '修改公网IP数量成功';
                }else{
                    $description[] = '修改公网IP数量失败,原因:'.$res['msg'];
                }
            }
            // 带宽型,只变更带宽
            if($configData['line']['bill_type'] == 'bw'){
                $oldInBw = $oldConfigData['bw']['value'];
                $oldOutBw = $oldConfigData['bw']['value'];

                if(is_numeric($oldConfigData['bw']['other_config']['in_bw'])){
                    $oldInBw = $oldConfigData['bw']['other_config']['in_bw'];
                }

                $newInBw = $configData['bw']['value'];
                $newOutBw = $configData['bw']['value'];

                if(is_numeric($configData['bw']['other_config']['in_bw'])){
                    $newInBw = $configData['bw']['other_config']['in_bw'];
                }
                // 修改带宽
                if($oldInBw != $newInBw){
                    $res = $Dcim->modifyInBw(['num'=>$newInBw, 'server_id'=>$id]);
                    if($res['status'] == 200){
                        $description[] = '修改进带宽成功';
                    }else{
                        $description[] = '修改进带宽失败,原因:'.$res['msg'];
                    }
                }
                if($oldOutBw != $newOutBw){
                    $res = $Dcim->modifyOutBw(['num'=>$newOutBw, 'server_id'=>$id]);
                    if($res['status'] == 200){
                        $description[] = '修改出带宽成功';
                    }else{
                        $description[] = '修改出带宽失败,原因:'.$res['msg'];
                    }
                }
            }else{
                // 流量型
                $oldFlow = $oldConfigData['flow']['value'];
                $newFlow = $configData['flow']['value'];

                if($oldFlow != $newFlow){
                    $post['id'] = $id;
                    $post['traffic'] = $newFlow;

                    $res = $Dcim->modifyFlowLimit($post);
                    if($res['status'] == 200){
                        $description[] = '修改流量设置成功';
                    }else{
                        $description[] = '修改流量设置失败,原因:'.$res['msg'];
                    }
                }
                
                $oldInBw = $oldConfigData['flow']['other_config']['in_bw'];
                $oldOutBw = $oldConfigData['flow']['other_config']['out_bw'];

                $newInBw = $configData['flow']['other_config']['in_bw'];
                $newOutBw = $configData['flow']['other_config']['out_bw'];

                // 修改带宽
                if($oldInBw != $newInBw){
                    $res = $Dcim->modifyInBw(['num'=>$newInBw, 'server_id'=>$id]);
                    if($res['status'] == 200){
                        $description[] = '修改进带宽成功';
                    }else{
                        $description[] = '修改进带宽失败,原因:'.$res['msg'];
                    }
                }
                if($oldOutBw != $newOutBw){
                    $res = $Dcim->modifyOutBw(['num'=>$newOutBw, 'server_id'=>$id]);
                    if($res['status'] == 200){
                        $description[] = '修改出带宽成功';
                    }else{
                        $description[] = '修改出带宽失败,原因:'.$res['msg'];
                    }
                }

                // 检查当前是否还超额
                if($param['host']['status'] == 'Suspended' && $param['host']['suspend_type'] == 'overtraffic'){
                    $post = [];
                    $post['id'] = $id;
                    $post['hostid'] = $hostId;
                    $post['unit'] = 'GB';

                    $flow = $Dcim->flow($post);
                    if($flow['status'] == 200){
                        $data = $flow['data'][ $configData['flow']['other_config']['bill_cycle'] ?? 'month' ];

                        $percent = str_replace('%', '', $data['used_percent']);

                        $total = $flow['limit'] > 0 ? $flow['limit'] + $flow['temp_traffic'] : 0;
                        $used = round($total * $percent / 100, 2);
                        if($percent < 100){
                            $unsuspendRes = $param['host']->unsuspendAccount($param['host']['id']);
                            if($unsuspendRes['status'] == 200){
                                $descrition[] = sprintf('流量限额:%dGB,已用:%sGB,解除因流量超额的暂停成功', $total, $used);
                            }else{
                                $descrition[] = sprintf('流量限额:%dGB,已用:%sGB,解除因流量超额的暂停失败,原因:%s', $total, $used, $unsuspendRes['msg']);
                            }
                        }
                    }
                }
            }
            $description = '模块升降级配置完成,'.implode(',', $description);
            active_log($description, 'host', $hostId);
        }else if($custom['type'] == 'upgrade_ip_num'){
            // 升级IP数量
            $hostLink = $this->where('host_id', $hostId)->find();
            $id = $hostLink['rel_id'] ?? 0;

            // 直接保存configData
            $configData = json_decode($hostLink['config_data'], true);
            $oldIpNum = $configData['ip']['value'] ?? 0;
            $configData['ip'] = $custom['ip_data'];

            $this->where('id', $hostLink['id'])->update(['config_data'=>json_encode($configData)]);

            $ipGroup = 0;
            // 获取下线路信息
            $line = LineModel::find($configData['line']['id']);
            if(!empty($line)){
                if($line['defence_enable'] == 1 && isset($configData['defence']['value']) && !empty($configData['defence']['value'])){
                    $ipGroup = $line['defence_ip_group'];
                }else if($line['bill_type'] == 'bw'){
                    $ipGroup = $line['bw_ip_group'];
                }
            }

            $post = [];
            $post['id'] = $id;

            $ipNum = $custom['ip_data']['value'];
            if(is_numeric($ipNum)){
                if(!empty($ipGroup)){
                    $post['ip_num'][ $ipGroup ] = $ipNum;
                }else{
                    $post['ip_num'] = $ipNum;
                }
            }else if($ipNum == 'NO_CHANGE' || $ipNum == 'NC'){
                $post['ip_num'] = 'NO_CHANGE';
            }else{  //分组形式2_2,1_1  数量_分组id
                $ipNum = ToolLogic::formatDcimIpNum($ipNum);
                // if($ipNum === false){
                //     $result['status'] = 400;
                //     $result['msg'] = 'IP数量格式有误';
                //     return $result;
                // }
                $post['ip_num'] = $ipNum;
            }
            
            $Dcim = new Dcim($param['server']);
            $res = $Dcim->modifyIpNum($post);
            if($res['status'] == 200){

                // 重新获取IP
                $detail = $Dcim->detail(['id'=>$id]);
                if($detail['status'] == 200){
                    $update = [];
                    $update['ip'] = $detail['ip']['subnet_ip'][0]['ipaddress'] ?? $detail['ipaddress'][0] ?? '';

                    $this->where('host_id', $hostId)->update($update);
                }

                $description = '模块升降级公网IP数量成功';
            }else{
                $description = '模块升降级公网IP数量失败,原因:'.$res['msg'];
            }
            active_log($description, 'host', $hostId);
        }
        return ['status'=>200];
    }

    /**
     * 时间 2023-02-09
     * @title 结算后
     * @desc 结算后
     * @author hh
     * @version v1
     * @param   [type] $param [description]
     * @return  [type]        [description]
     */
    public function afterSettle($param){
        // 参数不需要重新验证了,计算已经验证了
        $custom = $param['custom'] ?? [];
        $hostId = $param['host_id'];
        
        $configData = DurationModel::$configData;

        $data = [
            'host_id'           => $param['host_id'],
            'data_center_id'    => $custom['data_center_id'] ?? 0,
            'image_id'          => $custom['image_id'],
            'power_status'      => 'on',
            'config_data'       => json_encode($configData),
            'create_time'       => time(),
        ];
        $res = $this->where('host_id', $param['host_id'])->find();
        if(empty($res)){
            $this->create($data);
        }else{
            $this->update($data, ['host_id'=>$param['host_id']]);
        }
        $hostData = [
            'client_notes' => $custom['notes'] ?? '',
        ];
        HostModel::where('id', $param['host_id'])->update($hostData);

        // 镜像是否收费
        if($configData['image']['charge'] == 1){
            $HostImageLinkModel = new HostImageLinkModel();
            $HostImageLinkModel->saveLink($param['host_id'], $configData['image']['id']);
        }
        // 自动续费
        if(isset($custom['auto_renew']) && $custom['auto_renew'] == 1){
            $enableIdcsmartRenewAddon = PluginModel::where('name', 'IdcsmartRenew')->where('module', 'addon')->where('status',1)->find();
            if($enableIdcsmartRenewAddon && class_exists('addon\idcsmart_renew\model\IdcsmartRenewAutoModel')){
                IdcsmartRenewAutoModel::where('host_id', $hostId)->delete();
                IdcsmartRenewAutoModel::create([
                    'host_id' => $hostId,
                    'status'  => 1,
                ]);
            }
        }
    }

    /**
     * 时间 2023-02-20
     * @title 获取当前配置所有周期价格
     * @desc 获取当前配置所有周期价格
     * @author hh
     * @version v1
     * @param   [type] $param [description]
     */
    public function durationPrice($param){
        $hostId = $param['host']['id'];

        $hostLink = $this->where('host_id', $hostId)->find();
        $configData = json_decode($hostLink['config_data'], true);

        $data = [
            'id'    => $param['product']['id'],
            'model_config_id' => $configData['model_config']['id'],
            'data_center_id' => $configData['data_center']['id'],
            'line_id' => $configData['line']['id'],
        ];
        if($configData['line']['bill_type'] == 'bw'){
            $data['bw'] = $configData['bw']['value'];
        }else{
            $data['flow'] = $configData['flow']['value'];
        }
        if(isset($configData['defence'])){
            $data['peak_defence'] = $configData['defence']['value'];
        }
        if(isset($configData['ip'])){
            $data['ip_num'] = $configData['ip']['value'];
        }
        $DurationModel = new DurationModel();
        $result = $DurationModel->getAllDurationPrice($data, true);
        if($result['status'] == 400){
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('success_message'),
                'data'   => [],
            ];
        }else{
            foreach($result['data'] as $k=>$v){
                if(empty($v['num'])){
                    unset($result['data'][$k]);
                    continue;
                }
                $result['data'][$k]['duration'] = strtotime('+ '.$v['num'].' '.$v['unit'], $param['host']['due_time']) - $param['host']['due_time'];
                $result['data'][$k]['billing_cycle'] = $v['name'];
                $result['data'][$k]['price'] = amount_format($v['price']);
                unset($result['data'][$k]['name'], $result['data'][$k]['num'], $result['data'][$k]['unit'], $result['data'][$k]['discount']);
            }
            $result['data'] = array_values($result['data']);
        }
        return $result;
    }

    public function currentConfigOption($param){
        $hostId = $param['host']['id'];

        $hostLink = $this->where('host_id', $hostId)->find();
        $configData = json_decode($hostLink['config_data'], true);

        $data = [
            'model_config_id' => $configData['model_config']['id'],
            'data_center_id' => $configData['data_center']['id'],
            'line_id' => $configData['line']['id'],
            'duration_id' => $configData['duration']['id'],
        ];
        if($configData['line']['bill_type'] == 'bw'){
            $data['bw'] = $configData['bw']['value'];
        }else{
            $data['flow'] = $configData['flow']['value'];
        }
        if(isset($configData['defence'])){
            $data['peak_defence'] = $configData['defence']['value'];
        }
        if(isset($configData['ip'])){
            $data['ip_num'] = $configData['ip']['value'];
        }
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data,
        ];
        return $result;
    }

    /**
     * 时间 2023-02-09
     * @title 获取商品最低价格周期
     * @desc 获取商品最低价格周期
     * @author hh
     * @version v1
     * @param   [type] $productId [description]
     * @return  [type]            [description]
     */
    public function getPriceCycle($productId){
        $ProductModel = ProductModel::find($productId);
        if(empty($ProductModel)){
            return false;
        }
        bcscale(2);

        $cycle = null;
        if($ProductModel['pay_type'] == 'free'){
            $price = 0;
        }else if($ProductModel['pay_type'] == 'onetime'){
            // 价格怎么算了?
            $price = 0;
        }else{
            // 获取线路最低价格
            $option = OptionModel::alias('o')
                ->field('o.*,p.duration_id,p.price,d.price_factor')
                ->join('module_mf_dcim_price p', 'p.rel_type="option" AND o.id=p.rel_id')
                ->leftJoin('module_mf_cloud_duration d', 'p.duration_id=d.id')
                ->where('o.product_id', $productId)
                ->whereIn('o.rel_type', [OptionModel::LINE_BW,OptionModel::LINE_FLOW])
                ->group('o.id,p.duration_id')
                ->order('p.price', 'asc')
                ->select()
                ->toArray();

            $minPrice = [];
            foreach($option as $v){
                if($v['type'] == 'radio'){
                    $price = $v['price'];
                }else if($v['type'] == 'step'){
                    $price = bcmul($v['min_value'], $v['price']);
                }else if($v['type'] == 'total'){
                    $price = bcmul($v['min_value'], $v['price']);
                }else{
                    $price = 0;
                }
                $price = bcmul($price, $v['price_factor'] ?? 1);
                
                if(!isset($minPrice[ $v['duration_id'] ])){
                    $minPrice[ $v['duration_id'] ] = $price;
                }else{
                    if($price < $minPrice[ $v['duration_id'] ]){
                        $minPrice[ $v['duration_id'] ] = $price;
                    }
                }
            }
            // 获取下型号配置
            $modelConfig = ModelConfigModel::where('product_id', $productId)->column('id');
            if(!empty($modelConfig)){
                $price = PriceModel::field('duration_id,price')->where('rel_type', 'model_config')->whereIn('rel_id', $modelConfig)->select()->toArray();

                foreach($price as $v){
                    if(!isset($minPrice[ $v['duration_id'] ])){
                        $minPrice[ $v['duration_id'] ] = $v['price'];
                    }else{
                        $minPrice[ $v['duration_id'] ] += $v['price'];
                    }
                }
            }

            $price = null;
            $durationId = 0;
            foreach($minPrice as $k=>$v){
                if(is_null($price)){
                    $price = $v;
                    $durationId = $k;
                }else{
                    if($v < $price){
                        $price = $v;
                        $durationId = $k;
                    }
                }
            }

            $price = $price ?? 0;
            $cycle = DurationModel::where('id', $durationId)->value('name') ?? '';
        }
        return ['price'=>$price, 'cycle'=>$cycle, 'product'=>$ProductModel];
    }


    public function adminField($param){
        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        if(empty($hostLink)){
            return [];
        }
        
        $configData = !empty($hostLink) ? json_decode($hostLink['config_data'], true) : [];

        $in_bw = '';
        $out_bw = '';
        if(isset($configData['bw'])){
            $in_bw = $configData['bw']['other_config']['in_bw'] ?: $configData['bw']['value'];
            $out_bw = $configData['bw']['value'];
        }else if(isset($configData['flow'])){
            $in_bw = $configData['flow']['other_config']['in_bw'];
            $out_bw = $configData['flow']['other_config']['out_bw'];
        }

        $data = [];
        // 带宽型
        if(isset($configData['bw'])){
            $data[] = [
                'name'      => lang_plugins('bw'),
                'key'       => 'bw',
                'value'     => $configData['bw']['value'] ?? '',
            ];
            $data[] = [
                'name'      => lang_plugins('mf_cloud_line_bw_in_bw'),
                'key'       => 'in_bw',
                'value'     => $configData['bw']['other_config']['in_bw'] ?? '',
            ];
        }else if(isset($configData['flow'])){
            $data[] = [
                'name'      => lang_plugins('mf_cloud_option_value_3'),
                'key'       => 'flow',
                'value'     => $configData['flow']['value'] ?? '',
            ];
            $data[] = [
                'name'      => lang_plugins('mf_cloud_out_server_bw'),
                'key'       => 'out_bw',
                'value'     => $configData['flow']['other_config']['out_bw'] ?? '',
            ];
            $data[] = [
                'name'      => lang_plugins('mf_cloud_in_server_bw'),
                'key'       => 'in_bw',
                'value'     => $configData['flow']['other_config']['in_bw'] ?? '',
            ];
        }
        $data[] = [
            'name'  => lang_plugins('mf_cloud_option_value_4'),
            'key'  => 'defence',
            'value'  => $configData['defence']['value'] ?? '',
        ];
        $data[] = [
            'name'  => lang_plugins('mf_cloud_append_ip_num'),
            'key'  => 'ip_num',
            'value'  => $configData['ip']['value'] ?? '',
        ];
        return $data;
    }


    public function hostUpdate($param){
        $hostId = $param['host']['id'];
        $moduleAdminField  = $param['module_admin_field'];

        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        if(!empty($hostLink)){
            $adminField = $this->adminField($param);
            $adminField = array_column($adminField, 'value', 'key');

            $configData = json_decode($hostLink['config_data'], true);
            
            $post = [];             // 流量修改参数
            $bw = [];               // 带宽参数
            $change = false;        // 是否变更
            $ip_change = false;     // IP数量是否变更

            // 带宽型
            if(isset($configData['bw'])){
                if(isset($moduleAdminField['bw']) && is_numeric($moduleAdminField['bw']) && $moduleAdminField['bw'] != $adminField['bw']){
                    $configData['bw']['value'] = $moduleAdminField['bw'];

                    $bw['in_bw'] = $moduleAdminField['bw'];
                    $bw['out_bw'] = $moduleAdminField['bw'];
                    $change = true;
                }
                if(isset($moduleAdminField['in_bw']) && is_numeric($moduleAdminField['in_bw']) && $moduleAdminField['in_bw'] != $adminField['in_bw']){
                    $configData['bw']['other_config']['in_bw'] = $moduleAdminField['in_bw'];

                    // 使用带宽参数
                    if($moduleAdminField['in_bw'] === '' && is_numeric($adminField['in_bw'])){
                        if($configData['bw']['value'] != $adminField['in_bw']){
                            $bw['in_bw'] = $configData['bw']['value'];
                        }
                    }else{
                        $bw['in_bw'] = $moduleAdminField['in_bw'];
                    }
                    $change = true;
                }
            }else if(isset($configData['flow'])){
                // 流量型
                if(isset($moduleAdminField['flow']) && $moduleAdminField['flow'] != $adminField['flow']){
                    $configData['flow']['value'] = $moduleAdminField['flow'];

                    $post['id'] = $hostLink['rel_id'] ?? 0;
                    $post['traffic'] = (int)$moduleAdminField['flow'];
                    $change = true;
                }
                if(isset($moduleAdminField['in_bw']) && is_numeric($moduleAdminField['in_bw']) && $moduleAdminField['in_bw'] != $adminField['in_bw']){
                    $configData['flow']['other_config']['in_bw'] = $moduleAdminField['in_bw'];

                    $bw['in_bw'] = $moduleAdminField['in_bw'];
                    $change = true;
                }
                if(isset($moduleAdminField['out_bw']) && is_numeric($moduleAdminField['out_bw']) && $moduleAdminField['out_bw'] != $adminField['out_bw']){
                    $configData['flow']['other_config']['out_bw'] = $moduleAdminField['out_bw'];

                    $bw['out_bw'] = $moduleAdminField['out_bw'];
                    $change = true;
                }
            }
            if(isset($moduleAdminField['defence']) && $moduleAdminField['defence'] != $adminField['defence']){
                if(!isset($configData['defence'])){
                    $configData['defence'] = [
                        'value' => 0,
                        'price' => 0,
                    ];
                }
                $configData['defence']['value'] = (int)$moduleAdminField['defence'];

                $change = true;
            }
            if(isset($moduleAdminField['ip_num']) && $moduleAdminField['ip_num'] != $adminField['ip_num']){
                if($moduleAdminField['ip_num'] == 'NC'){
                    return ['status'=>400, 'msg'=>'附加IP数量不能修改为NC'];
                }
                
                if(!isset($configData['ip_num'])){
                    $configData['ip_num'] = [
                        'value' => 0,
                        'price' => 0,
                    ];
                }
                $configData['ip']['value'] = $moduleAdminField['ip_num'];

                $change = true;
                $ip_change = true;
            }

            if($change){
                $update = [
                    'config_data' => json_encode($configData),
                ];
                HostLinkModel::update($update, ['host_id'=>$hostId]);
            }
            
            $id = $hostLink['rel_id'] ?? 0;
            if(empty($id)){
                return ['status'=>200, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
            }
            
            $Dcim = new Dcim($param['server']);

            $detail = '';

            // 有升降级IP
            if($ip_change){
                $ipGroup = 0;
                // 获取下线路信息
                $line = LineModel::find($configData['line']['id']);
                if(!empty($line)){
                    if($line['defence_enable'] == 1 && isset($configData['defence']['value']) && !empty($configData['defence']['value'])){
                        $ipGroup = $line['defence_ip_group'];
                    }else{
                        $ipGroup = $line['bw_ip_group'];
                    }
                }

                $post = [];
                $post['id'] = $id;
                $ip_num = $configData['ip']['value'];

                if(is_numeric($ip_num)){
                    if(!empty($ipGroup)){
                        $post['ip_num'][$ipGroup] = $ip_num;
                    }else{
                        $post['ip_num'] = $ip_num;
                    }
                }else if($ip_num == 'NO_CHANGE'){
                    $post['ip_num'] = $ip_num;
                }else{  //分组形式2_2,1_1  数量_分组id
                    $ip_num = ToolLogic::formatDcimIpNum($ip_num);
                    if($ip_num === false){
                        // $result['status'] = 400;
                        // $result['msg'] = 'IP数量格式有误';
                        // return $result;
                    }else{
                        $post['ip_num'] = $ip_num;
                    }
                }
                // if(!empty($ipGroup)){
                //     $post['ip_num'][ $ipGroup ] = $configData['ip']['value'];
                // }else{
                //     $post['ip_num'] = $configData['ip']['value'];
                // }
                $res = $Dcim->modifyIpNum($post);
                if($res['status'] == 200){
                    // 重新获取IP
                    $detailRes = $Dcim->detail(['id'=>$id]);
                    if($detailRes['status'] == 200){
                        $update = [];
                        $update['ip'] = $detailRes['ip']['subnet_ip'][0]['ipaddress'] ?? $detailRes['ipaddress'][0] ?? '';

                        $this->where('host_id', $hostId)->update($update);
                    }
                    $detail .= ',修改公网IP数量成功';
                }else{
                    $detail .= ',修改公网IP数量失败,原因:'.$res['msg'];
                }
            }
            // 带宽型,只变更带宽
            if($configData['line']['bill_type'] == 'bw'){
                // 修改带宽
                if(isset($bw['in_bw'])){
                    $res = $Dcim->modifyInBw(['num'=>$bw['in_bw'], 'server_id'=>$id]);
                    if($res['status'] == 200){
                        $detail .= ',修改进带宽成功';
                    }else{
                        $detail .= ',修改进带宽失败,原因:'.$res['msg'];
                    }
                }
                if(isset($bw['out_bw'])){
                    $res = $Dcim->modifyOutBw(['num'=>$bw['out_bw'], 'server_id'=>$id]);
                    if($res['status'] == 200){
                        $detail .= ',修改出带宽成功';
                    }else{
                        $detail .= ',修改出带宽失败,原因:'.$res['msg'];
                    }
                }
            }else{
                if(!empty($post)){
                    $post['id'] = $id;

                    $res = $Dcim->modifyFlowLimit($post);
                    if($res['status'] == 200){
                        $detail .= ',修改流量设置成功';
                    }else{
                        $detail .= ',修改流量设置失败,原因:'.$res['msg'];
                    }
                }
                // 修改带宽
                if(isset($bw['in_bw'])){
                    $res = $Dcim->modifyInBw(['num'=>$bw['in_bw'], 'server_id'=>$id]);
                    if($res['status'] == 200){
                        $detail .= ',修改进带宽成功';
                    }else{
                        $detail .= ',修改进带宽失败,原因:'.$res['msg'];
                    }
                }
                if(isset($bw['out_bw'])){
                    $res = $Dcim->modifyOutBw(['num'=>$bw['out_bw'], 'server_id'=>$id]);
                    if($res['status'] == 200){
                        $detail .= ',修改出带宽成功';
                    }else{
                        $detail .= ',修改出带宽失败,原因:'.$res['msg'];
                    }
                }
                // 检查当前是否还超额
                // if($param['host']['status'] == 'Suspended' && $param['host']['suspend_type'] == 'overtraffic'){
                //     $post = [];
                //     $post['id'] = $id;
                //     $post['hostid'] = $hostId;
                //     $post['unit'] = 'GB';

                //     $flow = $Dcim->flow($post);
                //     if($flow['status'] == 200){
                //         $data = $flow['data'][ $configData['flow']['other_config']['bill_cycle'] ?? 'month' ];

                //         $percent = str_replace('%', '', $data['used_percent']);

                //         $total = $flow['limit'] > 0 ? $flow['limit'] + $flow['temp_traffic'] : 0;
                //         $used = round($total * $percent / 100, 2);
                //         if($percent < 100){
                //             $unsuspendRes = $param['host']->unsuspendAccount($param['host']['id']);
                //             if($unsuspendRes['status'] == 200){
                //                 $descrition[] = sprintf('流量限额:%dGB,已用:%sGB,解除因流量超额的暂停成功', $total, $used);
                //             }else{
                //                 $descrition[] = sprintf('流量限额:%dGB,已用:%sGB,解除因流量超额的暂停失败,原因:%s', $total, $used, $unsuspendRes['msg']);
                //             }
                //         }
                //     }
                // }
            }
            if(!empty($detail)){
                $description = lang_plugins('mf_dcim_log_host_update_complete', [
                    '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                    '{detail}'  => $detail,
                ]);
                active_log($description, 'host', $param['host']['id']);
            }
        }
        return ['status'=>200, 'msg'=>lang_plugins('success_message')];
    }


}