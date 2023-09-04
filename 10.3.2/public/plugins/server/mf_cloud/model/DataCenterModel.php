<?php 
namespace server\mf_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\mf_cloud\logic\ToolLogic;

/**
 * @title 数据中心模型
 * @use server\mf_cloud\model\DataCenterModel
 */
class DataCenterModel extends Model{

	protected $name = 'module_mf_cloud_data_center';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_id'        => 'int',
        'country_id'        => 'int',
        'city'              => 'string',
        'area'              => 'string',
        'cloud_config'      => 'string',
        'cloud_config_id'   => 'int',
        'order'             => 'int',
        'create_time'       => 'int',
    ];

    /**
     * 时间 2023-02-02
     * @title 数据中心列表
     * @desc 数据中心列表
     * @author hh
     * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int product_id - 商品ID
     * @return  int list[].id - 数据中心ID
     * @return  string list[].city - 城市
     * @return  string list[].area - 区域
     * @return  string list[].country_name - 国家
     * @return  int list[].line[].id - 线路ID
     * @return  int list[].line[].data_center_id - 数据中心ID
     * @return  string list[].line[].name - 线路名称
     * @return  string list[].line[].bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  string list[].line[].price - 价格
     * @return  string list[].line[].duration - 周期
     * @return  int count - 总条数
     */
    public function dataCenterList($param)
    {
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id'])){
            $param['orderby'] = 'dc.id';
        }
        $where = [];
        if(!empty($param['product_id'])){
            $where[] = ['dc.product_id', '=', $param['product_id']];
        }

        $language = configuration('lang_admin');
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $dataCenter = $this
                ->alias('dc')
                ->field('dc.id,dc.city,dc.area,dc.country_id,dc.cloud_config,dc.cloud_config_id,c.'.$countryName.' country_name')
                ->where($where)
                ->leftJoin('country c', 'dc.country_id=c.id')
                ->page($param['page'], $param['limit'])
                // ->order($param['orderby'], $param['sort'])
                ->select()
                ->toArray();
    
        $count = $this
                ->alias('dc')
                ->where($where)
                ->group('dc.id')
                ->count();

        if(!empty($dataCenter)){

            $line = LineModel::alias('l')
                    ->field('l.id,l.data_center_id,l.name,l.bill_type')
                    ->select()
                    ->toArray();

            $lineId = array_column($line, 'id');

            if(!empty($lineId)){
                $firstDuration = DurationModel::field('id,name,num,unit')->where('product_id', $param['product_id'])->orderRaw('field(unit, "hour","day","month")')->order('num', 'asc')->find();

                // 直接显示最小周期
                if(!empty($firstDuration)){

                    $price = PriceModel::alias('p')
                        ->field('p.price,o.type,o.value,o.min_value,o.rel_id,o.rel_type')
                        ->leftJoin('module_mf_cloud_option o', 'p.option_id=o.id')
                        ->whereIn('o.rel_type', [OptionModel::LINE_BW, OptionModel::LINE_FLOW])
                        ->whereIn('o.rel_id', $lineId)
                        ->where('p.duration_id', $firstDuration['id'])
                        ->order('o.value,o.min_value', 'asc')
                        ->select()
                        ->toArray();

                    $priceArr = [];
                    foreach($price as $k=>$v){
                        if(isset($priceArr[ $v['rel_id'] ])){
                            continue;
                        }
                        if($v['type'] == 'radio'){
                            $priceArr[ $v['rel_id'] ] = [
                                'price' => $v['price'],
                                'name'  => $firstDuration['name'],
                            ];
                        }else{
                            $priceArr[ $v['rel_id'] ] = [
                                'price' => bcmul($v['price'], $v['min_value']),
                                'name'  => $firstDuration['name'],
                            ];
                        }
                    }

                    $lineArr = [];
                    foreach($line as $k=>$v){
                        $v['price'] = $priceArr[$v['id']]['price'] ?? '0.00';
                        $v['duration'] = $priceArr[$v['id']]['name'] ?? '';
                        $lineArr[ $v['data_center_id'] ][] = $v;
                    }
                }else{
                    $lineArr = [];
                    foreach($line as $k=>$v){
                        $v['price'] = '0.00';
                        $v['duration'] = '';
                        $lineArr[ $v['data_center_id'] ][] = $v;
                    }
                }
            }

            foreach($dataCenter as $k=>$v){
                $dataCenter[$k]['line'] = $lineArr[ $v['id'] ] ?? [];
            }
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'=>$dataCenter,
                'count'=>$count
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-06-15
     * @title 创建数据中心
     * @desc 创建数据中心
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   int param.country_id - 国家 require
     * @param   string param.city - 城市 require
     * @param   string param.cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID) require
     * @param   int param.config_rel_id - 魔方云配置关联ID require
     * @param   int param.order - 排序
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.id - 数据中心ID
     */
    public function createDataCenter($param)
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $CountryModel = CountryModel::find($param['country_id']);
        if(empty($CountryModel)){
            return ['status'=>400, 'msg'=>lang_plugins('country_id_error')];
        }
        // 是否添加了相同的数据中心
        $same = $this
                ->where('product_id', $param['product_id'])
                ->where('country_id', $param['country_id'])
                ->where('city', $param['city'])
                ->where('area', $param['area'])
                ->find();
        if(!empty($same)){
            return ['status'=>400, 'msg'=>lang_plugins('the_same_data_center_already_add')];
        }

        $param['create_time'] = time();

        $this->startTrans();
        try{
            $dataCenter = $this->create($param, ['country_id','city','area','cloud_config','cloud_config_id','product_id','create_time']);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $description = lang_plugins('log_create_data_center_success', ['{name}'=>$dataCenter->getDataCenterName() ]);
        active_log($description, 'product', $ProductModel['id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$dataCenter->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2022-06-15
     * @title 修改数据中心
     * @desc 修改数据中心
     * @author hh
     * @version v1
     * @param   int param.id - 数据中心ID required
     * @param   int param.country_id - 国家 require
     * @param   string param.city - 城市 require
     * @param   string param.cloud_config - 魔方云配置(node=节点ID,area=区域ID,node_group=节点分组ID) require
     * @param   int param.config_rel_id - 魔方云配置关联ID require
     * @param   int param.order - 排序
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.id - 数据中心ID
     */
    public function updateDataCenter($param)
    {
        $dataCenter = $this->find($param['id']);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        $CountryModel = CountryModel::find($param['country_id']);
        if(empty($CountryModel)){
            return ['status'=>400, 'msg'=>lang_plugins('country_id_error')];
        }

        $OldCountryModel = CountryModel::find($dataCenter['country_id']);

        // 是否添加了相同的数据中心
        $same = $this
                ->where('product_id', $dataCenter['product_id'])
                ->where('country_id', $param['country_id'])
                ->where('city', $param['city'])
                ->where('area', $param['area'])
                ->where('id', '<>', $dataCenter['id'])
                ->find();
        if(!empty($same)){
            return ['status'=>400, 'msg'=>lang_plugins('the_same_data_center_already_add')];
        }

        $this->startTrans();
        try{
            $this->update($param, ['id'=>$param['id']], ['country_id','city','area','cloud_config','cloud_config_id']);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $type = [
            'node'=>lang_plugins('node_id'),
            'area'=>lang_plugins('area_id'),
            'node_group'=>lang_plugins('node_group_id'),
        ];

        $desc = [
            'country'=>lang_plugins('country'),
            'city'=>lang_plugins('city'),
            'area'=>lang_plugins('area'),
            'cloud_config'=>lang_plugins('cloud_config'),
        ];

        $language = configuration('lang_admin');
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $old = [
            'country'=>$OldCountryModel[ $countryName ],
            'city'=>$dataCenter['city'],
            'area'=>$dataCenter['area'],
            'cloud_config'=>$type[ $dataCenter['cloud_config'] ].$dataCenter['cloud_config_id'],
        ];
        $new = [
            'country'=>$CountryModel[ $countryName ],
            'city'=>$param['city'],
            'area'=>$param['area'],
            'cloud_config'=>$type[ $param['cloud_config'] ].$param['cloud_config_id'],
        ];

        $description = ToolLogic::createEditLog($old, $new, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_data_center_success', [
                '{name}'=>$old['country'].$old['city'].$old['area'],
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $dataCenter['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2022-06-15
     * @title 删除数据中心
     * @desc 删除数据中心
     * @author hh
     * @version v1
     * @param   int id - 数据中心ID require
     */
    public function deleteDataCenter($id)
    {
        $dataCenter = $this->find($id);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        // 有线路正在使用
        $line = LineModel::where('data_center_id', $id)->find();
        if(!empty($line)){
            return ['status'=>400, 'msg'=>lang_plugins('cannot_delete_data_center_for_line_exist')];
        }
        // 推荐配置正在使用
        $recommendConfig = RecommendConfigModel::where('data_center_id', $id)->find();
        if(!empty($recommendConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('config_conflict_please_edit_recommend_config')];
        }
        
        $this->startTrans();
        try{
            $dataCenter->delete();

            // 删除对应数据中心的配置限制
            ConfigLimitModel::where('type', 'data_center')->where('data_center_id', $id)->delete();

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $CountryModel = CountryModel::find($dataCenter['country_id']);

        $language = configuration('lang_admin');
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $description = lang_plugins('log_delete_data_center_success', ['{name}'=>$CountryModel[ $countryName ].$dataCenter['city'].$dataCenter['area'] ]);
        active_log($description, 'product', $dataCenter['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2022-06-17
     * @title 修改数据中心排序
     * @desc 修改数据中心排序
     * @author hh
     * @version v1
     * @param   int param.id - 数据中心ID required
     * @param   int param.order - 排序 required
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function updateOrder($param)
    {
        $dataCenter = $this->find($param['id']);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }

        $this->update($param, ['id'=>$param['id']], ['order']);

        $desc = [
            'order'=>lang_plugins('order'),
        ];

        $description = ToolLogic::createEditLog($dataCenter, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_data_center_success', [
                '{name}'=>$dataCenter->getDataCenterName(),
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $dataCenter['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-03
     * @title 数据中心选择
     * @desc 数据中心选择
     * @author hh
     * @version v1
     * @param   string x             - x
     * @return  [type] [description]
     */
    public function dataCenterSelect($param){
        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[]
        ];

        $where = [];
        if(isset($param['product_id']) && $param['product_id']>0){
            $where[] = ['product_id', '=', $param['product_id']];
        }

        $country = $this
                ->where($where)
                ->column('country_id');

        if(empty($country)){
            return $result;
        }

        $language = !empty(get_client_id()) ? get_client_lang() : get_system_lang(true);
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $country = CountryModel::field('id,iso,'.$countryName.' name')
                    ->whereIn('id', $country)
                    ->select()
                    ->toArray();

        $city = $this
                ->field('id,country_id,city,area')
                ->where($where)
                // ->order('order', 'asc')
                ->select()
                ->toArray();

        $lineArr = [];
        // 获取线路
        if(!empty($city)){
            $dataCenterId = array_column($city, 'id');

            $line = LineModel::field('id,data_center_id,name,bill_type,defence_enable')->select();
            foreach($line as $v){
                $lineArr[ $v['data_center_id'] ][] = [
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'bill_type' => $v['bill_type'],
                    'defence_enable' => $v['defence_enable'],
                ];
            }
        }

        $cityArr = [];
        foreach($city as $k=>$v){
            $cityArr[ $v['country_id'] ][ $v['city'] ]['area'][] = [
                'id' => $v['id'],
                'name' => $v['area'],
                'line' => $lineArr[ $v['id'] ] ?? []
            ];
        }
        foreach($country as $k=>$v){
            $temp = $cityArr[ $v['id'] ] ?? [];
            foreach($temp as $kk=>$vv){
                $country[$k]['city'][] = [
                    'name' => $kk,
                    'area' => $vv['area']
                ];
            }
        }
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list' => $country
            ]
        ];
        return $result;
    }

    /**
     * 时间 2023-02-06
     * @title 获取订购页面配置
     * @desc 获取订购页面配置
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  [type] [description]
     */
    public function orderPage($param){
        $param['product_id'] = $param['product_id'] ?? 0;

        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];
        
        $dataCenter = $this
                    ->field('id,country_id')
                    ->where('product_id', $param['product_id'])
                    ->select()
                    ->toArray();

        $dataCenterId = array_column($dataCenter, 'id');
        if(!empty($dataCenterId)){
            $dataCenterId = LineModel::whereIn('data_center_id', $dataCenterId)->column('data_center_id');
        }
        
        if(!empty($dataCenterId)){
            $country = $this
                ->where($where)
                ->whereIn('id', $dataCenterId)
                ->column('country_id');
        }else{
            $country = [];
        }

        $data = [
            'data_center' => [],
        ];
        if(!empty($country)){
            if(isset($param['scene']) && $param['scene'] == 'recommend'){
                // 获取推荐配置
                $recommendConfig = RecommendConfigModel::field('id,name,description,data_center_id,line_id,cpu,memory,system_disk_size,system_disk_type,data_disk_size,data_disk_type,network_type,bw,flow,peak_defence')
                                        ->where($where)
                                        ->order('order', 'asc')
                                        ->select()
                                        ->toArray();

                $recommendConfigArr = [];
                foreach($recommendConfig as $v){
                    $recommendConfigArr[ $v['data_center_id'] ][] = $v;
                }

                if(!empty($recommendConfig)){
                    $city = $this
                        ->field('id,country_id,city,area')
                        ->where($where)
                        ->whereIn('id', array_column($recommendConfig, 'data_center_id'))
                        ->order('order,id', 'asc')
                        ->select()
                        ->toArray();
                }else{
                    $city = [];
                    $country = [];
                }
            }else{
                $city = $this
                    ->field('id,country_id,city,area')
                    ->where($where)
                    ->whereIn('id', $dataCenterId)
                    ->order('order,id', 'asc')
                    ->select()
                    ->toArray();
            }
            if(!empty($city)){
                $language = get_client_lang();
                $countryField = ['en-us'=> 'nicename'];
                $countryName = $countryField[ $language ] ?? 'name_zh';

                $country = array_column($city, 'country_id');

                $country = CountryModel::field('id,iso,'.$countryName.' name')
                    ->whereIn('id', $country)
                    ->select()
                    ->toArray();
            }

            // 获取线路
            if(!empty($city)){
                $dataCenterId = array_column($city, 'id');

                $line = LineModel::field('id,name,data_center_id,bill_type')->whereIn('data_center_id', $dataCenterId)->select()->toArray();

                // 获取所有线路配置
                if(!empty($line)){
                    $lineArr = [];
                    foreach($line as $v){
                        $lineArr[ $v['data_center_id'] ][] = $v;
                    }
                }
            }

            $cityArr = [];
            foreach($city as $k=>$v){
                $cityArr[ $v['country_id'] ][ $v['city'] ]['area'][] = [
                    'id' => $v['id'],
                    'name' => $v['area'],
                    'recommend_config' => $recommendConfigArr[ $v['id'] ] ?? [],
                    'line' => $lineArr[ $v['id'] ] ?? [],
                ];
            }
            foreach($country as $k=>$v){
                $temp = $cityArr[ $v['id'] ] ?? [];
                foreach($temp as $kk=>$vv){
                    $country[$k]['city'][] = [
                        'name' => (string) $kk,
                        'area' => $vv['area']
                    ];
                }
            }

            $data['data_center'] = $country;
        }
        // cpu
        $data['cpu'] = OptionModel::field('id,value')->where($where)->where('rel_type', OptionModel::CPU)->order('value', 'asc')->select()->toArray();
        // 内存
        $data['memory'] = OptionModel::field('id,type,value,min_value,max_value,step')->where($where)->where('rel_type', OptionModel::MEMORY)->order('value,min_value', 'asc')->select()->toArray();
        // 储存配置
        $data['system_disk'] = OptionModel::field('id,type,value,min_value,max_value,step,other_config')
                            ->where($where)
                            ->where('rel_type', OptionModel::SYSTEM_DISK)
                            ->withAttr('other_config', function($val){
                                return json_decode($val, 'true');
                            })
                            ->order('value,min_value', 'asc')
                            ->select()
                            ->toArray();
        $data['data_disk'] = OptionModel::field('id,type,value,min_value,max_value,step,other_config')
                            ->where($where)
                            ->where('rel_type', OptionModel::DATA_DISK)
                            ->withAttr('other_config', function($val){
                                return json_decode($val, 'true');
                            })
                            ->order('value,min_value', 'asc')
                            ->select()
                            ->toArray();

        // 获取配置
        $config = ConfigModel::field('type,support_ssh_key,support_normal_network,support_vpc_network,support_public_ip,backup_enable,snap_enable,ip_mac_bind,nat_acl_limit,nat_web_limit,ipv6_num,memory_unit')
                ->where($where)
                ->find();

        $config['ip_mac_bind_enable'] = !empty($config['ip_mac_bind']) ? 1 : 0;
        $config['nat_acl_limit_enable'] = is_numeric($config['nat_acl_limit']) ? 1 : 0;
        $config['nat_web_limit_enable'] = is_numeric($config['nat_web_limit']) ? 1 : 0;
        $config['ipv6_num_enable'] = is_numeric($config['ipv6_num']) ? 1 : 0;
        unset($config['ip_mac_bind'],$config['nat_acl_limit'],$config['nat_web_limit'],$config['ipv6_num']);

        $data['config'] = $config;
        
        // 如果开启了备份快照才返回
        if($config['backup_enable'] == 1){
            $data['backup_config'] = BackupConfigModel::field('id,num,price')->where($where)->where('type', 'backup')->order('num', 'asc')->select()->toArray();
        }else{
            $data['backup_config'] = [];
        }
        
        if($config['snap_enable'] == 1){
            $data['snap_config'] = BackupConfigModel::field('id,num,price')->where($where)->where('type', 'snap')->order('num', 'asc')->select()->toArray();
        }else{
            $data['snap_config'] = [];
        }

        // 获取配置限制
        $data['config_limit'] = ConfigLimitModel::field('type,data_center_id,line_id,min_bw,max_bw,cpu,memory,min_memory,max_memory')
                                ->where($where)
                                ->select()
                                ->toArray();

        // 代理商资源包
        $data['resource_package'] = [];
        $ProductModel = ProductModel::find($param['product_id']);
        if(!empty($ProductModel) && $ProductModel['type'] == 'server'){
            $server = ServerModel::find($ProductModel['rel_id']);
            if(!empty($server)){
                $hash = ToolLogic::formatParam($server['hash']);
                if(isset($hash['account_type']) && $hash['account_type'] == 'agent'){
                    $data['resource_package'] = ResourcePackageModel::field('id,name')
                                ->where('product_id', $param['product_id'])
                                ->select()
                                ->toArray();
                }
            }
        }
        return $data;
    }

    /**
     * 时间 2022-06-21
     * @title 获取数据中心
     * @desc 获取数据中心格式化显示
     * @author hh
     * @version v1
     * @param   int param.id - 商品ID
     * @return  array data.list - 列表数据
     * @return  string data.list[].id - 国家ID
     * @return  string data.list[].iso - 图标
     * @return  string data.list[].name_zh - 国家名称
     * @return  int data.list[].city[].id - 数据中心ID
     * @return  string data.list[].city[].name - 城市
     */
    public function formatDisplay($param){
        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[]
        ];
        $param['id'] = $param['id'] ?: 0;

        $where = [];
        if($param['id']>0){
            $where[] = ['dc.product_id', '=', $param['id']];
        }

        $language = get_client_lang();
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $list = $this
                ->field('dc.id,dc.city,dc.area,c.iso,c.'.$countryName.' country_name')
                ->alias('dc')
                ->leftJoin('country c', 'dc.country_id=c.id')
                ->where($where)
                ->select()
                ->toArray();

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list' => $list
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-09-28
     * @title 获取数据中心名称
     * @desc 获取数据中心名称
     * @author hh
     * @version v1
     * @param   DataCenterModel $DataCenterModel 
     * @return  string
     */
    public function getDataCenterName($DataCenterModel = null){
        $DataCenterModel = $DataCenterModel ?? $this;

        $CountryModel = CountryModel::find($DataCenterModel['country_id']);

        $language = !empty(get_client_id()) ? get_client_lang() : get_system_lang(true);
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        return ($CountryModel[ $countryName ] ?? '').'-'.$DataCenterModel['city'].'-'.$DataCenterModel['area'];
    }



}