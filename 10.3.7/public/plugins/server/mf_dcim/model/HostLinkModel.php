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
        'package_id'        => 'int',
        'power_status'      => 'string',
        'ip'                => 'string',
        'additional_ip'     => 'string',
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
        $whereDataCenter = [];
        $whereOr = [];

        $where[] = ['h.client_id', '=', $clientId];
        $whereDataCenter[] = ['h.client_id', '=', $clientId];
        $where[] = ['h.status', '<>', 'Cancelled'];
        $whereDataCenter[] = ['h.status', '<>', 'Cancelled'];
        if(isset($param['keywords']) && trim($param['keywords']) !== ''){
            $whereOr[] = ['pro.name|h.name|hl.ip', 'LIKE', '%'.$param['keywords'].'%'];
            try{
                $language = get_client_lang();

                $filterProductId = ProductModel::alias('p')
                    ->leftJoin('addon_multi_language ml', 'p.name=ml.name')
                    ->leftJoin('addon_multi_language_value mlv', 'ml.id=mlv.language_id AND mlv.language="'.$language.'"')
                    ->whereLike('p.name|mlv.value', '%'.$param['keywords'].'%')
                    ->limit(200)
                    ->column('p.id');
                if(!empty($filterProductId)){
                    $whereOr[] = ['h.product_id', 'IN', $filterProductId];
                }
            }catch(\Exception $e){
                
            }
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
                    $whereDataCenter[] = ['h.product_id', 'IN', $MenuModel['product_id'] ];
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
            $whereDataCenter[] = ['h.id', 'IN', $hostId];
        }

        $language = get_client_lang();
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $count = $this
            ->alias('hl')
            ->join('host h', 'hl.host_id=h.id')
            ->leftJoin('product pro', 'h.product_id=pro.id')
            ->leftJoin('module_mf_dcim_data_center dc', 'hl.data_center_id=dc.id')
            ->leftJoin('module_mf_dcim_image i', 'hl.image_id=i.id')
            ->where($where)
            ->where(function($query) use ($whereOr){
                if(!empty($whereOr)){
                    $query->whereOr($whereOr);
                }
            })
            ->count();

        $host = $this
            ->alias('hl')
            ->field('h.id,h.name,h.status,h.active_time,h.due_time,h.client_notes,pro.name product_name,c.'.$countryName.' country,c.iso country_code,dc.city,dc.area,hl.ip,hl.power_status,i.name image_name,ig.name image_group_name,ig.icon')
            ->join('host h', 'hl.host_id=h.id')
            ->leftJoin('product pro', 'h.product_id=pro.id')
            ->leftJoin('module_mf_dcim_data_center dc', 'hl.data_center_id=dc.id')
            ->leftJoin('country c', 'dc.country_id=c.id')
            ->leftJoin('module_mf_dcim_image i', 'hl.image_id=i.id')
            ->leftJoin('module_mf_dcim_image_group ig', 'i.image_group_id=ig.id')
            ->where($where)
            ->where(function($query) use ($whereOr){
                if(!empty($whereOr)){
                    $query->whereOr($whereOr);
                }
            })
            ->withAttr('status', function($val){
                return $val == 'Failed' ? 'Pending' : $val;
            })
            ->withAttr('product_name', function($val){
                if(!empty($val)){
                    $multiLanguage = hook_one('multi_language', [
                        'replace' => [
                            'product_name' => $val,
                        ],
                    ]);
                    if(isset($multiLanguage['product_name'])){
                        $val = $multiLanguage['product_name'];
                    }
                }
                return $val;
            })
            ->withAttr('city', function($val){
                $multiLanguage = hook_one('multi_language', [
                    'replace' => [
                        'city' => $val,
                    ],
                ]);
                if(isset($multiLanguage['city'])){
                    $val = $multiLanguage['city'];
                }
                return $val;
            })
            ->withAttr('area', function($val){
                $multiLanguage = hook_one('multi_language', [
                    'replace' => [
                        'area' => $val,
                    ],
                ]);
                if(isset($multiLanguage['area'])){
                    $val = $multiLanguage['area'];
                }
                return $val;
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->group('h.id')
            ->select()
            ->toArray();

        // 获取所有可用数据中心
        $dataCenter = $this
                    ->alias('hl')
                    ->field('dc.id,dc.city,dc.area,c.'.$countryName.' country_name,c.iso')
                    ->join('host h', 'hl.host_id=h.id')
                    ->join('module_mf_dcim_data_center dc', 'hl.data_center_id=dc.id')
                    ->leftJoin('product pro', 'h.product_id=pro.id')
                    ->leftJoin('country c', 'dc.country_id=c.id')
                    ->withAttr('city', function($val){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'city' => $val,
                            ],
                        ]);
                        if(isset($multiLanguage['city'])){
                            $val = $multiLanguage['city'];
                        }
                        return $val;
                    })
                    ->withAttr('area', function($val){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'area' => $val,
                            ],
                        ]);
                        if(isset($multiLanguage['area'])){
                            $val = $multiLanguage['area'];
                        }
                        return $val;
                    })
                    ->where($whereDataCenter)
                    ->where(function($query) use ($whereOr){
                        if(!empty($whereOr)){
                            $query->whereOr($whereOr);
                        }
                    })
                    ->group('dc.id')
                    ->select()
                    ->toArray();

        $result['data']['list']  = $host;
        $result['data']['count'] = $count;
        $result['data']['data_center'] = $dataCenter;
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
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => (object)[],
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
            $adminField = $this->getAdminField($configData);
            if(!empty($hostLink['package_id'])){
                $adminField['cpu'] = ToolLogic::packageConfigLanguage($adminField['cpu']);
                $adminField['memory'] = ToolLogic::packageConfigLanguage($adminField['memory']);
                $adminField['disk'] = ToolLogic::packageConfigLanguage($adminField['disk']);
            }

            $data = [];
            $data['order_id'] = $host['order_id'];
            $data['ip'] = $hostLink['ip'];
            $data['additional_ip'] = $hostLink['additional_ip'];
            $data['power_status'] = $hostLink['power_status'];
            
            $data['model_config'] = [
                'id'        => $configData['model_config']['id'] ?? 0,
                'name'      => $adminField['model_name'],
                'cpu'       => $adminField['cpu'],
                'cpu_param' => $adminField['cpu_param'],
                'memory'    => $adminField['memory'],
                'disk'      => $adminField['disk'],
            ];
            $data['package'] = [
                'id'                => $hostLink['package_id'],
                'name'              => $adminField['model_name'],
                'cpu'               => $adminField['cpu'],
                'memory'            => $adminField['memory'],
                'disk'              => $adminField['disk'],
                // 'memory_used'       => $adminField['memory_used'],
                // 'memory_num_used'   => $adminField['memory_num_used'],
                // 'disk_num_used'     => $adminField['disk_num_used'],
            ];

            $data['line'] = [
                'id'        => $configData['line']['id'] ?? 0,
                'name'      => $configData['line']['name'] ?? '',
                'bill_type' => $configData['line']['bill_type'] ?? 'bw',
            ];
            $data['bw'] = $adminField['bw'];
            if(isset($configData['flow'])){
                $data['flow'] = $adminField['flow'];
            }
            $data['ip_num'] = $adminField['ip_num'];
            $data['peak_defence'] = $adminField['defence'];
            
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
                'id'    => $dataCenter['id'],
                'city'  => $dataCenter['city'],
                'area'  => $dataCenter['area'],
            ];
            $country = CountryModel::find($dataCenter['country_id']);

            $language = get_client_lang();
            $countryField = ['en-us'=> 'nicename'];
            $countryName = $countryField[ $language ] ?? 'name_zh';

            $data['data_center']['country'] = $country[ $countryName ];
            $data['data_center']['iso'] = $country['iso'];
            
            $data['image'] = $image ?? (object)[];
            $data['config'] = ConfigModel::field('reinstall_sms_verify,reset_password_sms_verify,manual_resource')->where('product_id', $host['product_id'])->find() ?? (object)[];

            if(!empty($data['package']['id'])){
                $HostOptionLinkModel = new HostOptionLinkModel();
                $hostOption = $HostOptionLinkModel->getHostOptional($hostId);

                foreach($hostOption['optional_memory'] as $v){
                    $data['package']['optional_memory'][ $v['option_id'] ] = $v['num'];
                }
                foreach($hostOption['optional_disk'] as $v){
                    $data['package']['optional_disk'][ $v['option_id'] ] = $v['num'];
                }
                $data['package']['optional_memory'] = $data['package']['optional_memory'] ?? (object)[];
                $data['package']['optional_disk'] = $data['package']['optional_disk'] ?? (object)[];
            }

            $multiLanguage = hook_one('multi_language', [
                'replace' => [
                    'model_config_name' => $data['model_config']['name'],
                    'cpu'               => $data['model_config']['cpu'],
                    'cpu_param'         => $data['model_config']['cpu_param'],
                    'memory'            => $data['model_config']['memory'],
                    'disk'              => $data['model_config']['disk'],
                    'line_name'         => $data['line']['name'],
                    'city'              => $data['data_center']['city'],
                    'area'              => $data['data_center']['area'],
                    'package_name'      => $data['package']['name'],
                ],
            ]);
            $data['model_config']['name'] = $multiLanguage['name'] ?? $data['model_config']['name'];
            $data['model_config']['cpu'] = $multiLanguage['cpu'] ?? $data['model_config']['cpu'];
            $data['model_config']['cpu_param'] = $multiLanguage['cpu_param'] ?? $data['model_config']['cpu_param'];
            $data['model_config']['memory'] = $multiLanguage['memory'] ?? $data['model_config']['memory'];
            $data['model_config']['disk'] = $multiLanguage['disk'] ?? $data['model_config']['disk'];
            $data['line']['name'] = $multiLanguage['line_name'] ?? $data['line']['name'];
            $data['data_center']['city'] = $multiLanguage['city'] ?? $data['data_center']['city'];
            $data['data_center']['area'] = $multiLanguage['area'] ?? $data['data_center']['area'];
            $data['package']['name'] = $multiLanguage['package_name'] ?? $data['package']['name'];

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

            $language = get_client_lang();
            $countryField = ['en-us'=> 'nicename'];
            $countryName = $countryField[ $language ] ?? 'name_zh';

            $data['data_center']['country'] = $country[ $countryName ];
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
        
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);

        if($config['data']['manual_resource']==1){
            return [
                'status'=>200,
                'msg'   =>lang_plugins('mf_dcim_host_create_success')
            ];
        }

        // 获取当前配置
        $hostLink = $this->where('host_id', $hostId)->find();
        if(!empty($hostLink) && $hostLink['rel_id'] > 0){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_host_already_created')];
        }

        $configData = json_decode($hostLink['config_data'], true);
        $adminField = $this->getAdminField($configData);

        $line = LineModel::find($configData['line']['id']);
        if(!empty($line)){
            $configData['line'] = $line->toArray();
        }

        if(!empty($hostLink['package_id'])){
            $package = PackageModel::find($hostLink['package_id']);
            if(empty($package)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_package_not_found')];
            }
            $post['server_group'] = $package['group_id'];
            $post['out_bw'] = $adminField['bw'] ?? $package['bw'];
            $post['in_bw'] = $adminField['in_bw'] ?? $package['bw'];
            $post['limit_traffic'] = 0;

        }else{
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

            $post['in_bw'] = $adminField['in_bw'] == '' ? $adminField['bw'] : $adminField['in_bw'];
            $post['out_bw'] = $adminField['bw'];    
            $post['limit_traffic'] = $adminField['flow'] ?? 0;
        }
        // 带宽NO_CHANGE判断
        if($post['in_bw'] == 'NC' || $post['in_bw'] == 'NO_CHANGE'){
            $post['in_bw'] = 'NO_CHANGE';
        }
        if($post['out_bw'] == 'NC' || $post['out_bw'] == 'NO_CHANGE'){
            $post['out_bw'] = 'NO_CHANGE';
        }
        $ipNum = $adminField['ip_num'];
        if(is_numeric($ipNum)){
            $post['ip_num'] = $ipNum;
        }else if($ipNum == 'NO_CHANGE' || $ipNum == 'NC'){
            $post['ip_num'] = 'NO_CHANGE';
        }else{  //分组形式2_2,1_1  数量_分组id
            $ipNum = ToolLogic::formatDcimIpNum($ipNum);
            if($ipNum === false){
                $result['status'] = 400;
                $result['msg'] = lang_plugins('mf_dcim_custom_ip_num_format_error');
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

            $ips = explode("\r\n", $res['data']['ips']);
            foreach($ips as $k=>$v){
                if($v == $update['ip']){
                    unset($ips[$k]);
                }else{
                    $ips[$k] = str_replace(',', '，', $v);
                }
            }
            $update['additional_ip'] = trim(implode(';', $ips), ';');
            
            $this->where('id', $hostLink['id'])->update($update);

            if(!empty($update['ip'])){
                HostModel::where('id', $hostLink['host_id'])->update(['name'=>$update['ip']]);
            }
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
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);

        if($config['data']['manual_resource']==1){
            return [
                'status'=>200,
                'msg'   =>lang_plugins('mf_dcim_suspend_success')
            ];
        }
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
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);

        if($config['data']['manual_resource']==1){
            return [
                'status'=>200,
                'msg'   =>lang_plugins('mf_dcim_unsuspend_success')
            ];
        }

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
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);

        if($config['data']['manual_resource']==1){
            if($this->isEnableManualResource()){
                $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
                $manual_resource = $ManualResourceModel->where('host_id', $param['host']['id'])->find();
                if(!empty($manual_resource)){
                    $ManualResourceModel->where('host_id', $param['host']['id'])->update(['host_id' => 0, 'update_time' => time()]);
                }
            }
            return [
                'status'=>200,
                'msg'   =>lang_plugins('delete_success')
            ];
        }

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
            $this->where('host_id', $param['host']['id'])->update(['rel_id'=>0, 'ip'=>'']);

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
            $oldAdminField = $this->getAdminField($configData);
            $adminField = $oldAdminField;

            $oldConfigData = $configData;
            $newConfigData = $custom['new_config_data'];
            foreach($newConfigData as $k=>$v){
                $configData[$k] = $v;
            }
            $newAdminField = $custom['new_admin_field'] ?? [];
            foreach($newAdminField as $k=>$v){
                $adminField[$k] = $v;
            }
            $configData['admin_field'] = $adminField;

            // 保存新的配置
            $update = [
                'config_data' => json_encode($configData),
            ];
            $this->update($update, ['host_id'=>$hostId]);
            if(!empty($hostLink['package_id'])){
                HostOptionLinkModel::where('host_id', $hostId)->delete();

                if(isset($custom['optional']) && !empty($custom['optional'])){
                    $HostOptionLinkModel = new HostOptionLinkModel();
                    $HostOptionLinkModel->insertAll($custom['optional']);
                }
            }

            $id = $hostLink['rel_id'] ?? 0;
            if(empty($id)){
                $description = lang_plugins('mf_dcim_log_upgrade_config_fail_for_no_dcim_id');
                active_log($description, 'host', $hostId);
                return ['status'=>400, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
            }
            $Dcim = new Dcim($param['server']);

            $description = [];
            // 有升降级IP
            if(isset($newConfigData['ip'])){
                $ipGroup = 0;
                // 获取下线路信息
                $line = LineModel::find($configData['line']['id']);
                if(!empty($line)){
                    if($line['defence_enable'] == 1 && !empty($adminField['defence'])){
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
                        $update['ip'] = $detail['server']['zhuip'] ?? '';
                        $update['additional_ip'] = trim(implode(';', $detail['ip']['ipaddress'] ?? []), ';');

                        $this->where('host_id', $hostId)->update($update);
                        if(!empty($update['ip'])){
                            HostModel::where('id', $hostId)->update(['name'=>$update['ip']]);
                        }
                    }
                    $description[] = lang_plugins('mf_dcim_upgrade_ip_num_success');
                }else{
                    $description[] = lang_plugins('mf_dcim_upgrade_ip_num_fail') . $res['msg'];
                }
            }
            // 带宽型,只变更带宽
            if($configData['line']['bill_type'] == 'bw'){
                if(isset($newConfigData['bw'])){
                    $oldInBw = is_numeric($oldAdminField['in_bw']) ? $oldAdminField['in_bw'] : $oldAdminField['bw'];
                    $oldOutBw = $oldAdminField['bw'];

                    $newInBw = $configData['bw']['value'];
                    $newOutBw = $configData['bw']['value'];

                    if(is_numeric($configData['bw']['other_config']['in_bw'])){
                        $newInBw = $configData['bw']['other_config']['in_bw'];
                    }
                    // 修改带宽
                    if($oldInBw != $newInBw){
                        $res = $Dcim->modifyInBw(['num'=>$newInBw, 'server_id'=>$id]);
                        if($res['status'] == 200){
                            $description[] = lang_plugins('mf_dcim_upgrade_in_bw_success');
                        }else{
                            $description[] = lang_plugins('mf_dcim_upgrade_in_bw_fail') . $res['msg'];
                        }
                    }
                    if($oldOutBw != $newOutBw){
                        $res = $Dcim->modifyOutBw(['num'=>$newOutBw, 'server_id'=>$id]);
                        if($res['status'] == 200){
                            $description[] = lang_plugins('mf_dcim_upgrade_out_bw_success');
                        }else{
                            $description[] = lang_plugins('mf_dcim_upgrade_out_bw_fail') . $res['msg'];
                        }
                    }
                }
            }else{
                if(isset($newConfigData['flow'])){
                    // 流量型
                    $oldFlow = $oldAdminField['flow'];
                    $newFlow = $configData['flow']['value'];

                    if($oldFlow != $newFlow){
                        $post['id'] = $id;
                        $post['traffic'] = $newFlow;

                        $res = $Dcim->modifyFlowLimit($post);
                        if($res['status'] == 200){
                            $description[] = lang_plugins('mf_dcim_upgrade_flow_success');
                        }else{
                            $description[] = lang_plugins('mf_dcim_upgrade_flow_fail').$res['msg'];
                        }
                    }
                    
                    $oldInBw = $oldAdminField['in_bw'];
                    $oldOutBw = $oldAdminField['bw'];

                    $newInBw = $configData['flow']['other_config']['in_bw'];
                    $newOutBw = $configData['flow']['other_config']['out_bw'];

                    // 修改带宽
                    if($oldInBw != $newInBw){
                        $res = $Dcim->modifyInBw(['num'=>$newInBw, 'server_id'=>$id]);
                        if($res['status'] == 200){
                            $description[] = lang_plugins('mf_dcim_upgrade_in_bw_success');
                        }else{
                            $description[] = lang_plugins('mf_dcim_upgrade_in_bw_fail').$res['msg'];
                        }
                    }
                    if($oldOutBw != $newOutBw){
                        $res = $Dcim->modifyOutBw(['num'=>$newOutBw, 'server_id'=>$id]);
                        if($res['status'] == 200){
                            $description[] = lang_plugins('mf_dcim_upgrade_out_bw_success');
                        }else{
                            $description[] = lang_plugins('mf_dcim_upgrade_out_bw_fail') . $res['msg'];
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
                                    $descrition[] = lang_plugins('mf_dcim_upgrade_flow_unsuspend_success', [
                                        '{total}'   => $total,
                                        '{used}'    => $used,
                                    ]);
                                }else{
                                    $descrition[] = lang_plugins('mf_dcim_upgrade_flow_unsuspend_success', [
                                        '{total}'   => $total,
                                        '{used}'    => $used,
                                        '{reason}'  => $unsuspendRes['msg'],
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
            $description = lang_plugins('mf_dcim_upgrade_config_complete') . implode(',', $description);
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
                    $update['ip'] = $detail['server']['zhuip'] ?? '';
                    $update['additional_ip'] = trim(implode(';', $detail['ip']['ipaddress'] ?? []), ';');

                    $this->where('host_id', $hostId)->update($update);

                    if(!empty($update['ip'])){
                        HostModel::where('id', $hostId)->update(['name'=>$update['ip']]);
                    }
                }
                $description = lang_plugins('mf_dcim_log_upgrade_public_ip_num_success');
            }else{
                $description = lang_plugins('mf_dcim_log_upgrade_public_ip_num_fail', [
                    '{reason}' => $res['msg'],
                ]);
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
            'package_id'        => $custom['package_id'] ?? 0,
            'additional_ip'     => '',
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
        if(isset($configData['optional']) && !empty($configData['optional'])){
            $hostOption = [];
            foreach($configData['optional'] as $v){
                $hostOption[] = [
                    'host_id'   => $param['host_id'],
                    'option_id' => $v['id'],
                    'num'       => $v['num'],
                ];
            }
            $HostOptionLinkModel = new HostOptionLinkModel();
            $HostOptionLinkModel->insertAll($hostOption);
        }

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
        $adminField = $this->getAdminField($configData);

        $data = [
            'id'                => $param['product']['id'],
            'model_config_id'   => $configData['model_config']['id'] ?? 0,
            'package_id'        => $hostLink['package_id'] ?: '',
            'data_center_id'    => $configData['data_center']['id'],
            'line_id'           => $configData['line']['id'],
            'bw'                => $adminField['bw'],
            'ip_num'            => $adminField['ip_num'],
        ];
        
        if(empty($hostLink['package_id'])){
            $data['flow'] = $adminField['flow'];
        }else{
            $optional = HostOptionLinkModel::alias('hol')
                        ->field('hol.option_id,hol.num,o.rel_type')
                        ->join('module_mf_dcim_option o', 'hol.option_id=o.id')
                        ->where('hol.host_id', $hostId)
                        ->select();

            $data['optional_memory'] = [];
            $data['optional_disk'] = [];

            foreach($optional as $v){
                if($v['rel_type'] == OptionModel::MEMORY){
                    $data['optional_memory'][ $v['option_id'] ] = $v['num'];
                }else if($v['rel_type'] == OptionModel::DISK){
                    $data['optional_disk'][ $v['option_id'] ] = $v['num'];
                }
            }
        }
        if(!empty($adminField['defence'])){
            $data['peak_defence'] = $adminField['defence'];
        }
        
        $DurationModel = new DurationModel();
        $result = $DurationModel->getAllDurationPrice($data, true, $param['host']['client_id']);
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
            $price = 0;
        }else{
            $modelConfigPrice = ModelConfigModel::alias('mc')
                    ->field('mc.id,p.price,p.duration_id')
                    ->leftJoin('module_mf_dcim_price p', 'p.product_id='.$productId.' AND p.rel_type="model_config" AND p.rel_id=mc.id')
                    ->where('mc.product_id', $productId)
                    ->group('mc.id,p.duration_id')
                    ->select();

            $modelConfigPriceArr = [];
            foreach($modelConfigPrice as $v){
                $v['duration_id'] = $v['duration_id'] ?? 0;
                $v['price'] = $v['price'] ?? 0;
                
                $modelConfigPriceArr[ $v['id'] ][ $v['duration_id'] ] = $v['price'];
            }

            $optionPrice = OptionModel::alias('o')
                ->field('o.id,o.type,o.rel_type,o.value,o.min_value,o.max_value,p.duration_id,p.price')
                ->leftJoin('module_mf_dcim_price p', 'p.product_id='.$productId.' AND p.rel_type="option" AND o.id=p.rel_id')
                ->where('o.product_id', $productId)
                ->whereIn('o.rel_type', [OptionModel::LINE_BW,OptionModel::LINE_FLOW])
                ->group('o.id,p.duration_id')
                ->select();

            $optionPriceArr = [];
            foreach($optionPrice as $v){
                $v['duration_id'] = $v['duration_id'] ?? 0;
                $v['price'] = $v['price'] ?? 0;

                if($v['type'] == 'radio'){
                    $price = $v['price'];
                }else if($v['type'] == 'step'){
                    $price = $v['price'];
                }else if($v['type'] == 'total'){
                    $price = bcmul($v['min_value'], $v['price']);
                }else{
                    $price = 0;
                }
                $optionPriceArr[ $v['id'] ][ $v['duration_id'] ] = $price;
            }

            $price = null;
            $cycle = '';
            $duration = DurationModel::field('id,price,name')->where('product_id', $productId)->select();
            foreach($duration as $v){
                $tempPrice = $v['price'];

                $minModelConfigPrice = null;
                $minOptionPrice = null;
                foreach($modelConfigPriceArr as $durationPrice){
                    if(is_null($minModelConfigPrice)){
                        $minModelConfigPrice = $durationPrice[ $v['id'] ] ?? 0;
                    }else{
                        $minModelConfigPrice = min($minModelConfigPrice, $durationPrice[ $v['id'] ] ?? 0);
                    }
                    if($minModelConfigPrice == 0){
                        break;
                    }
                }
                foreach($optionPriceArr as $durationPrice){
                    if(is_null($minOptionPrice)){
                        $minOptionPrice = $durationPrice[ $v['id'] ] ?? 0;
                    }else{
                        $minOptionPrice = min($minOptionPrice, $durationPrice[ $v['id'] ] ?? 0);
                    }
                    if($minOptionPrice == 0){
                        break;
                    }
                }
                if(!is_null($minModelConfigPrice)){
                    $tempPrice = bcadd($tempPrice, $minModelConfigPrice);
                }
                if(!is_null($minOptionPrice)){
                    $tempPrice = bcadd($tempPrice, $minOptionPrice);
                }
                if(is_null($price)){
                    $price = $tempPrice;
                    $cycle = $v['name'];
                }else{
                    if($tempPrice < $price){
                        $price = $tempPrice;
                        $cycle = $v['name'];
                    }
                }
                if($price == 0){
                    break;
                }
            }
            if(empty($modelConfigPrice) || $price > 0){
                // 有套餐优先套餐价格
                $packagePrice = PackageModel::alias('pkg')
                                ->field('pkg.id,p.price,p.duration_id')
                                ->leftJoin('module_mf_dcim_price p', 'p.product_id='.$productId.' AND p.rel_type="'.PriceModel::TYPE_PACKAGE.'" AND pkg.id=p.rel_id')
                                ->where('pkg.product_id', $productId)
                                ->group('pkg.id,p.duration_id')
                                ->select()
                                ->toArray();
                $packagePriceArr = [];
                foreach($packagePrice as $v){
                    $v['duration_id'] = $v['duration_id'] ?? 0;
                    $v['price'] = $v['price'] ?? 0;
                    
                    $packagePriceArr[ $v['id'] ][ $v['duration_id'] ] = $v['price'];
                }

                foreach($duration as $v){
                    $tempPrice = $v['price'];

                    $minPackagePrice = null;
                    foreach($packagePriceArr as $durationPrice){
                        if(is_null($minPackagePrice)){
                            $minPackagePrice = $durationPrice[ $v['id'] ] ?? 0;
                        }else{
                            $minPackagePrice = min($minPackagePrice, $durationPrice[ $v['id'] ] ?? 0);
                        }
                        if($minPackagePrice == 0){
                            break;
                        }
                    }
                    if(!is_null($minPackagePrice)){
                        $tempPrice = bcadd($tempPrice, $minPackagePrice);
                    }
                    if(is_null($price)){
                        $price = $tempPrice;
                        $cycle = $v['name'];
                    }else{
                        if($tempPrice < $price){
                            $price = $tempPrice;
                            $cycle = $v['name'];
                        }
                    }
                    if($price == 0){
                        break;
                    }
                }
            }
            $price = $price ?? 0;
        }
        return ['price'=>$price, 'cycle'=>$cycle, 'product'=>$ProductModel];
    }


    public function adminField($param){
        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        if(empty($hostLink)){
            return [];
        }

        $configData = json_decode($hostLink['config_data'], true);
        $adminField = $this->getAdminField($configData);

        $dataCenter = DataCenterModel::find($configData['data_center']['id'] ?? 0);
        if(!empty($dataCenter)){
            $configData['data_center'] = $dataCenter->toArray();
        }
        $line = LineModel::find($configData['line']['id'] ?? 0);
        if(!empty($line)){
            $configData['line'] = $line->toArray();
        }
        $image = ImageModel::find($hostLink['image_id']);
        $modelConfig = ModelConfigModel::find($configData['model_config']['id'] ?? 0);
        if(!empty($modelConfig)){
            $configData['model_config'] = $modelConfig->toArray();
        }

        $DataCenterModel = new DataCenterModel();

        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);

        $data = [];
        
        if($config['data']['manual_resource']==1 && $this->isEnableManualResource()){
            $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
            $manual_resource = $ManualResourceModel->where('host_id', $param['host']['id'])->find();
            $hostLink['config_data'] = !empty($hostLink) ? json_decode($hostLink['config_data'], true) : [];
            $configData['model_config'] = $hostLink['config_data']['model_config'];
            $image = $hostLink['config_data']['image'];

            // 基础配置
            $data[] = [
                'name' => lang_plugins('mf_dcim_base_config'),
                'field'=> [
                    [
                        'name'      => lang_plugins('mf_dcim_data_center'),
                        'key'       => 'data_center',
                        'value'     => $DataCenterModel->getDataCenterName($configData['data_center']),
                        'disable'   => true,
                    ],
                    [
                        'name'      => lang_plugins('mf_dcim_manual_resource'),
                        'key'       => 'manual_resource',
                        'value'     => !empty($manual_resource) ? ($manual_resource['dedicated_ip'].'('.$manual_resource['id'].')') : '',
                        'disable'   => true,
                    ],
                ],
            ];
        }else{
            // 基础配置
            $data[] = [
                'name' => lang_plugins('mf_dcim_base_config'),
                'field'=> [
                    [
                        'name'      => lang_plugins('mf_dcim_data_center'),
                        'key'       => 'data_center',
                        'value'     => $DataCenterModel->getDataCenterName($configData['data_center']),
                        'disable'   => true,
                    ],
                    [
                        'name'  => lang_plugins('mf_dcim_server_id'),
                        'key'   => 'zjmf_dcim_id',
                        'value' => $hostLink['rel_id'],
                    ],
                ],
            ];
        }
        // 机型规格
        $data[] = [
            'name' => lang_plugins('mf_dcim_model_specification'),
            'field'=> [
                [
                    'name'      => lang_plugins('mf_dcim_model'),
                    'key'       => 'model_config_name',
                    'value'     => $adminField['model_name'],
                    'disable'   => false,
                ],
                [
                    'name'      => lang_plugins('mf_dcim_model_config_cpu'),
                    'key'       => 'model_config_cpu',
                    'value'     => $adminField['cpu'],
                    'disable'   => false,
                ],
            ],
        ];
        if(empty($hostLink['package_id'])){
            $data[1]['field'][] = [
                'name'      => lang_plugins('mf_dcim_model_config_cpu_param'),
                'key'       => 'model_config_cpu_param',
                'value'     => $adminField['cpu_param'],
                'disable'   => false,
            ];
        }
        $data[1]['field'][] = [
            'name'      => lang_plugins('mf_dcim_model_config_memory'),
            'key'       => 'model_config_memory',
            'value'     => $adminField['memory'],
            'disable'   => false,
        ];
        $data[1]['field'][] = [
            'name'      => lang_plugins('mf_dcim_model_config_disk'),
            'key'       => 'model_config_disk',
            'value'     => $adminField['disk'],
            'disable'   => false,
        ];
        if($config['data']['manual_resource']==1 && $this->isEnableManualResource()){
            $images = ImageModel::where('product_id', $param['product']['id'])->select()->toArray();
            
            $data[1]['field'][] = [
                'name'      => lang_plugins('mf_dcim_image'),
                'key'       => 'image',
                'value'     => intval($image['id'] ?? 0),
                'disable'   => false,
                'options'   => $images,
            ];
        }else{
            $data[1]['field'][] = [
                'name'      => lang_plugins('mf_dcim_image'),
                'key'       => 'image',
                'value'     => $image['name'] ?? '',
                'disable'   => true,
            ];
        }
        // 网络配置
        $data[] = [
            'name' => lang_plugins('mf_dcim_network_config'),
            'field'=> [
                [
                    'name'      => lang_plugins('mf_dcim_line'),
                    'key'       => 'line',
                    'value'     => $configData['line']['name'] ?? '',
                    'disable'   => true,
                ],
            ],
        ];

        $data[2]['field'][] = [
            'name'      => lang_plugins('bw'),
            'key'       => 'bw',
            'value'     => $adminField['bw'],
        ];
        $data[2]['field'][] = [
            'name'      => lang_plugins('mf_dcim_line_bw_in_bw'),
            'key'       => 'in_bw',
            'value'     => $adminField['in_bw'],
        ];
        if(isset($configData['flow'])){
            $data[2]['field'][] = [
                'name'      => lang_plugins('mf_dcim_option_value_3'),
                'key'       => 'flow',
                'value'     => $adminField['flow'],
            ];
        }
        if($config['data']['manual_resource']==1 && $this->isEnableManualResource()){
            $assigned_ips = $manual_resource['assigned_ips'] ?? '';
            $assigned_ips = array_unique(explode("\n", $assigned_ips));
            $data[2]['field'][] = [
                'name'  => lang_plugins('mf_dcim_ip_num'),
                'key'  => 'ip_num',
                'value'  => count($assigned_ips)>0 ? count($assigned_ips) : '',
            ];
        }else{
            $data[2]['field'][] = [
                'name'  => lang_plugins('mf_dcim_ip_num'),
                'key'  => 'ip_num',
                'value'  => $adminField['ip_num'],
            ];
        }
        $data[2]['field'][] = [
            'name'  => lang_plugins('mf_dcim_ip'),
            'key'   => 'ip',
            'value' => $hostLink['ip'],
        ];
        $data[2]['field'][] = [
            'name'  => lang_plugins('mf_dcim_additional_ip'),
            'key'   => 'additional_ip',
            'value' => $hostLink['additional_ip'],
        ];
        $data[2]['field'][] = [
            'name'  => lang_plugins('mf_dcim_option_value_4'),
            'key'   => 'defence',
            'value' => $adminField['defence'],
        ];
        return $data;
    }


    public function hostUpdate($param){
        $hostId = $param['host']['id'];
        $moduleAdminField  = $param['module_admin_field'];

        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        if(!empty($hostLink)){
            $oriAdminField = $this->adminField($param);

            $adminField = [];
            foreach($oriAdminField as $k=>$v){
                foreach($v['field'] as $kk=>$vv){
                    $adminField[ $vv['key'] ] = $vv['value'];
                }
            }

            $configData = json_decode($hostLink['config_data'], true);
            $configData['admin_field'] = $configData['admin_field'] ?? [];
            
            $update = [];           // 修改的参数
            $postFlow = [];         // 流量修改参数
            $bw = [];               // 带宽参数
            $ip_change = false;     // IP数量是否变更
            $input_ip = false;

            $ConfigModel = new ConfigModel();
            $config = $ConfigModel->indexConfig(['product_id'=>$param['product']['id'] ]);

            $configData['admin_field']['model_name'] = $moduleAdminField['model_config_name'];
            $configData['admin_field']['cpu'] = $moduleAdminField['model_config_cpu'];
            $configData['admin_field']['cpu_param'] = $moduleAdminField['model_config_cpu_param'] ?? '';
            $configData['admin_field']['memory'] = $moduleAdminField['model_config_memory'];
            $configData['admin_field']['disk'] = $moduleAdminField['model_config_disk'];
            // $configData['admin_field']['memory_used'] = $moduleAdminField['memory_used'] ?? 0;
            // $configData['admin_field']['memory_num_used'] = $moduleAdminField['memory_num_used'] ?? 0;
            // $configData['admin_field']['disk_num_used'] = $moduleAdminField['disk_num_used'] ?? 0;

            if($config['data']['manual_resource']==1 && $this->isEnableManualResource()){
                $ManualResourceModel = new \addon\manual_resource\model\ManualResourceModel();
                $manual_resource = $ManualResourceModel->where('host_id', $param['host']['id'])->find();

                $image = ImageModel::find($moduleAdminField['image']);
                $configData['image'] = $image;
                $update['image_id'] = $image['id'];
            }
            // 带宽
            if(isset($moduleAdminField['bw']) && is_numeric($moduleAdminField['bw']) && $moduleAdminField['bw'] != $adminField['bw']){
                $configData['admin_field']['bw'] = $moduleAdminField['bw'];

                $bw['in_bw'] = $moduleAdminField['bw'];
                $bw['out_bw'] = $moduleAdminField['bw'];
            }
            if(isset($moduleAdminField['in_bw']) && is_numeric($moduleAdminField['in_bw']) && $moduleAdminField['in_bw'] != $adminField['in_bw']){
                $configData['admin_field']['in_bw'] = $moduleAdminField['in_bw'];

                $bw['in_bw'] = $moduleAdminField['in_bw'];
            }
            // 流量
            if(isset($moduleAdminField['flow']) && $moduleAdminField['flow'] != $adminField['flow']){
                $configData['admin_field']['flow'] = $moduleAdminField['flow'];

                $postFlow['id'] = $hostLink['rel_id'] ?? 0;
                $postFlow['traffic'] = (int)$moduleAdminField['flow'];
            }
            if(isset($moduleAdminField['defence']) && $moduleAdminField['defence'] != $adminField['defence']){
                $configData['admin_field']['defence'] = (int)$moduleAdminField['defence'];
            }
            if(isset($moduleAdminField['ip_num']) && $moduleAdminField['ip_num'] != $adminField['ip_num']){
                if($moduleAdminField['ip_num'] == 'NC'){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_ip_num_cannot_modify_to_nc')];
                }
                $configData['admin_field']['ip_num'] = $moduleAdminField['ip_num'];

                $ip_change = true;
            }
            if(isset($moduleAdminField['ip']) && $moduleAdminField['ip'] != $adminField['ip']){
                $update['ip'] = $moduleAdminField['ip'];
                $input_ip = true;
            }
            if(isset($moduleAdminField['additional_ip']) && $moduleAdminField['additional_ip'] != $adminField['additional_ip']){
                $update['additional_ip'] = $moduleAdminField['additional_ip'];
                $input_ip = true;
            }
            $Dcim = new Dcim($param['server']);

            $serverHash = ToolLogic::formatParam($param['server']['hash']);
            $prefix = $serverHash['user_prefix'] ?? '';

            $detail = '';
            if(isset($adminField['zjmf_dcim_id']) && isset($moduleAdminField['zjmf_dcim_id']) && is_numeric($moduleAdminField['zjmf_dcim_id']) && $adminField['zjmf_dcim_id'] != $moduleAdminField['zjmf_dcim_id']){
                $update['rel_id'] = (int)$moduleAdminField['zjmf_dcim_id'];
                $hostLink['rel_id'] = $update['rel_id'];

                if(!empty($update['rel_id'])){
                    // 获取服务器是否不是空闲
                    $dcimDetail = $Dcim->detail(['id'=>$update['rel_id']]);
                    if($dcimDetail['status'] != 200){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_modify_dcimid_fail').$dcimDetail['msg'] ];
                    }
                    if($dcimDetail['server']['status'] != 1){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_modify_dcimid_fail').lang_plugins('mf_dcim_server_is_not_free')];
                    }
                    // 尝试分配为该机器,调用同步接口
                    $postData = [
                        'id'            => $update['rel_id'],
                        'hostid'        => $param['host']['id'],
                        'user_id'       => $prefix . $param['host']['client_id'],
                        'remote_user_id'=> $param['host']['client_id'],
                        'domainstatus'  => 'Active',
                        'starttime'     => date('Y-m-d H:i:s', $param['host']['create_time']),
                        // 'token'         => defined('AUTHCODE') ? AUTHCODE : configuration('system_license'),
                    ];
                    if($param['host']['due_time'] > 0){
                        $postData['expiretime'] = date('Y-m-d H:i:s', $param['host']['due_time']);
                    }
                    $assign = $Dcim->ipmiSync($postData);
                    if($assign['status'] == 200){
                        $detail .= ','.lang_plugins('mf_dcim_assign_dcimid_success').': '.$update['rel_id'];

                        $assign['ips'] = array_filter(explode("\r\n", $assign['ips']), function($value) use ($assign) {
                            return $value != $assign['zhuip'];
                        });

                        $update['password'] = aes_password_encode($assign['password']);
                        $update['additional_ip'] = trim(implode(';', $assign['ips']), ';');

                        if(!isset($update['ip'])){
                            $update['ip'] = $assign['zhuip'] ?: '';
                        }
                        if(!empty($assign['zhuip'])){
                            HostModel::where('id', $hostId)->update(['name'=>$assign['zhuip']]);
                        }
                    }else{
                        return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_modify_dcimid_fail').$assign['msg'] ];
                    }
                }
                // 空闲原机器
                if(!empty($adminField['zjmf_dcim_id'])){
                    $postData = [
                        'id'            => $adminField['zjmf_dcim_id'],
                        'hostid'        => $param['host']['id'],
                        'user_id'       => $prefix . $param['host']['client_id'],
                        'remote_user_id'=> $param['host']['client_id'],
                        'domainstatus'  => 'Free',
                        'starttime'     => '',
                        // 'token'         => '',
                    ];
                    $free = $Dcim->ipmiSync($postData);
                    if($free['status'] == 200){
                        $detail .= ','.lang_plugins('mf_dcim_free_dcimid_success').': '.$adminField['zjmf_dcim_id'];
                    }else{
                        $detail .= lang_plugins('mf_dcim_free_dcimid_fail', [
                            '{dcimid}' => $adminField['zjmf_dcim_id'],
                            '{reason}' => $free['msg'],
                        ]);
                    }
                }
            }

            $update['config_data'] = json_encode($configData);
            HostLinkModel::update($update, ['host_id'=>$hostId]);
            
            $id = $hostLink['rel_id'] ?? 0;
            if(empty($id)){
                if(!empty($detail)){
                    $description = lang_plugins('mf_dcim_log_host_update_complete', [
                        '{host}'    => 'host#'.$param['host']['id'].'#'.$param['host']['name'].'#',
                        '{detail}'  => $detail,
                    ]);
                    active_log($description, 'host', $param['host']['id']);
                }
                return ['status'=>200, 'msg'=>lang_plugins('not_input_idcsmart_cloud_id')];
            }

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
                $ip_num = $configData['admin_field']['ip_num'];

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
                        if(!$input_ip){
                            $this->where('host_id', $hostId)->update([
                                'ip'            =>  $detailRes['server']['zhuip'] ?? '',
                                'additional_ip' => trim(implode(';', $detailRes['ip']['ipaddress'] ?? []), ';'),
                            ]);

                            if(!empty($detailRes['server']['zhuip'])){
                                HostModel::where('id', $hostId)->update(['name'=>$detail['server']['zhuip']]);
                            }
                        }
                    }
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_ip_num_success');
                }else{
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_ip_num_fail').$res['msg'];
                }
            }
            if(!empty($postFlow)){
                $postFlow['id'] = $id;

                $res = $Dcim->modifyFlowLimit($postFlow);
                if($res['status'] == 200){
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_flow_success');
                }else{
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_flow_fail').$res['msg'];
                }
            }
            // 修改带宽
            if(isset($bw['in_bw'])){
                $res = $Dcim->modifyInBw(['num'=>$bw['in_bw'], 'server_id'=>$id]);
                if($res['status'] == 200){
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_in_bw_success');
                }else{
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_in_bw_fail').$res['msg'];
                }
            }
            if(isset($bw['out_bw'])){
                $res = $Dcim->modifyOutBw(['num'=>$bw['out_bw'], 'server_id'=>$id]);
                if($res['status'] == 200){
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_out_bw_success');
                }else{
                    $detail .= ','.lang_plugins('mf_dcim_upgrade_out_bw_fail').$res['msg'];
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

    /**
     * 时间 2023-09-26
     * @title 是否启用手动资源插件
     * @desc 是否启用手动资源插件
     * @author theworld
     * @version v1
     * @return  bool
     */
    public function isEnableManualResource(){
        $plugin = PluginModel::where('name', 'ManualResource')->where('status', 1)->where('module', 'addon')->find();
        return !empty($plugin);
    }

    public function getAdminField($configData = []){
        $adminField = $configData['admin_field'] ?? [];
        // 以前没有admin_field的转换
        if(empty($adminField)){
            $adminField['model_name'] = $configData['model_config']['name'] ?? '';
            $adminField['cpu'] = $configData['model_config']['cpu'] ?? '';
            $adminField['cpu_param'] = $configData['model_config']['cpu'] ?? '';
            $adminField['memory'] = $configData['model_config']['cpu'] ?? '';
            $adminField['disk'] = $configData['model_config']['cpu'] ?? '';
            $adminField['memory_used'] = 0;
            $adminField['memory_num_used'] = 0;
            $adminField['disk_num_used'] = 0;

            $in_bw = '';
            $out_bw = '';
            if(isset($configData['bw'])){
                $in_bw = $configData['bw']['other_config']['in_bw'] ?: $configData['bw']['value'];
                $out_bw = $configData['bw']['value'];
            }else if(isset($configData['flow'])){
                $in_bw = $configData['flow']['other_config']['in_bw'];
                $out_bw = $configData['flow']['other_config']['out_bw'];
            }
            $adminField['bw'] = (string)$out_bw;
            $adminField['in_bw'] = $in_bw;
            $adminField['ip_num'] = $configData['ip']['value'] ?? '';
            $adminField['flow'] = $configData['flow']['value'] ?? '';
            $adminField['defence'] = $configData['defence']['value'] ?? '';
        }else{
            // 强转下
            $adminField['bw'] = (string)$adminField['bw'];
            $adminField['ip_num'] = (string)$adminField['ip_num'];
        }
        return $adminField;
    }

    /**
     * 时间 2023-11-17
     * @title 产品转移
     * @desc  产品转移
     * @author hh
     * @version v1
     */
    public function hostTransfer($param){
        $hostLink = $this->where('host_id', $param['host']['id'])->find();
        if(empty($hostLink) || empty($hostLink['rel_id'])){
            return ['status'=>200, 'msg'=>lang_plugins('success_message')];
        }
        $Dcim = new Dcim($param['module_param']['server']);

        $serverHash = ToolLogic::formatParam($param['module_param']['server']['hash']);
        $prefix = $serverHash['user_prefix'] ?? '';

        // 尝试分配为该机器,调用同步接口
        $postData = [
            'id'            => $hostLink['rel_id'],
            'hostid'        => $param['host']['id'],
            'user_id'       => $prefix . $param['target_client']['id'],
            'remote_user_id'=> $param['target_client']['id'],
            'domainstatus'  => 'Active',
            // 'starttime'     => date('Y-m-d H:i:s', $param['host']['create_time']),
            // 'token'         => defined('AUTHCODE') ? AUTHCODE : configuration('system_license'),
        ];
        if($param['module_param']['host']['due_time'] > 0){
            $postData['expiretime'] = date('Y-m-d H:i:s', $param['module_param']['host']['due_time']);
        }
        $res = $Dcim->ipmiSync($postData);
        return $res;
    }


}