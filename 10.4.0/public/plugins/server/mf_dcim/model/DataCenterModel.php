<?php 
namespace server\mf_dcim\model;

use think\Model;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\mf_dcim\logic\ToolLogic;

/**
 * @title 数据中心模型
 * @use server\mf_dcim\model\DataCenterModel
 */
class DataCenterModel extends Model
{
	protected $name = 'module_mf_dcim_data_center';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_id'        => 'int',
        'country_id'        => 'int',
        'city'              => 'string',
        'area'              => 'string',
        'order'             => 'int',
        'create_time'       => 'int',
    ];

    /**
     * 时间 2023-02-02
     * @title 数据中心列表
     * @desc 数据中心列表
     * @author hh
     * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   int param.product_id - 商品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.list[].id - 数据中心ID
     * @return  string data.list[].city - 城市
     * @return  string data.list[].area - 区域
     * @return  int data.list[].country_id - 国家ID
     * @return  int data.list[].order - 排序
     * @return  string data.list[].country_name - 国家
     * @return  int data.list[].line[].id - 线路ID
     * @return  int data.list[].line[].data_center_id - 数据中心ID
     * @return  string data.list[].line[].name - 线路名称
     * @return  string data.list[].line[].bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  int data.list[].line[].order - 排序
     * @return  string data.list[].line[].price - 价格
     * @return  string data.list[].line[].duration - 周期
     * @return  int count - 总条数
     */
    public function dataCenterList($param)
    {
        bcscale(2);
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');

        $where = [];
        if(!empty($param['product_id'])){
            $where[] = ['dc.product_id', '=', $param['product_id']];
        }

        $language = configuration('lang_admin');
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $dataCenter = $this
                ->alias('dc')
                ->field('dc.id,dc.city,dc.area,dc.country_id,dc.order,c.'.$countryName.' country_name')
                ->where($where)
                ->leftJoin('country c', 'dc.country_id=c.id')
                ->page($param['page'], $param['limit'])
                ->order('dc.order,dc.id', 'asc')
                ->select()
                ->toArray();
    
        $count = $this
                ->alias('dc')
                ->where($where)
                ->group('dc.id')
                ->count();

        if(!empty($dataCenter)){

            $line = LineModel::alias('l')
                    ->field('l.id,l.data_center_id,l.name,l.bill_type,l.order')
                    ->whereIn('l.data_center_id', array_column($dataCenter, 'id'))
                    ->order('l.order,l.id', 'asc')
                    ->select()
                    ->toArray();

            $lineId = array_column($line, 'id');

            if(!empty($lineId)){
                $firstDuration = DurationModel::field('id,name,num,unit')->where('product_id', $param['product_id'])->orderRaw('field(unit, "hour","day","month")')->order('num', 'asc')->find();

                // 直接显示最小周期
                if(!empty($firstDuration)){
                    $price = OptionModel::alias('o')
                            ->field('p.price,o.type,o.value,o.min_value,o.rel_id,o.rel_type')
                            ->leftJoin('module_mf_dcim_price p', 'p.rel_type="option" AND p.rel_id=o.id AND p.duration_id='.$firstDuration['id'])
                            ->whereIn('o.rel_type', [OptionModel::LINE_BW, OptionModel::LINE_FLOW])
                            ->whereIn('o.rel_id', $lineId)
                            ->order('o.value,o.min_value', 'asc')
                            ->select()
                            ->toArray();

                    $priceArr = [];
                    foreach($price as $k=>$v){
                        if($v['type'] == 'radio'){
                            $tempPrice = $v['price'] ?? 0;
                        }else{
                            $tempPrice = bcmul($v['price'] ?? 0, $v['min_value']);
                        }
                        if(!isset($priceArr[ $v['rel_id'] ])){
                            $priceArr[ $v['rel_id'] ] = [
                                'price' => $tempPrice,
                                'name'  => $firstDuration['name'],
                            ];
                        }else{
                            $priceArr[ $v['rel_id'] ]['price'] = min($priceArr[ $v['rel_id'] ]['price'], $tempPrice);
                        }
                    }

                    $price = OptionModel::alias('o')
                            ->field('p.price,o.rel_id,o.rel_type')
                            ->leftJoin('module_mf_dcim_price p', 'p.rel_type="option" AND p.rel_id=o.id AND p.duration_id='.$firstDuration['id'])
                            ->where('o.rel_type', OptionModel::LINE_IP)
                            ->whereIn('o.rel_id', $lineId)
                            ->order('o.value', 'asc')
                            ->select()
                            ->toArray();

                    $priceLineIp = [];
                    foreach($price as $v){
                        if(!isset($priceLineIp[ $v['rel_id'] ])){
                            $priceLineIp[ $v['rel_id'] ] = $v['price'] ?? 0;
                        }else{
                            $priceLineIp[ $v['rel_id'] ] = min($priceLineIp[ $v['rel_id'] ],  $v['price'] ?? 0);
                        }
                    }
                    foreach($priceLineIp as $k=>$v){
                        if(isset($priceArr[$k])){
                            $priceArr[$k]['price'] = bcadd($priceArr[$k]['price'], $v);
                        }else{
                            $priceArr[$k] = [
                                'price' => $v,
                                'name'  => $firstDuration['name'],
                            ];
                        }
                    }

                    $lineArr = [];
                    foreach($line as $k=>$v){
                        $v['price'] = isset($priceArr[$v['id']]['price']) ? amount_format($priceArr[$v['id']]['price']) : '0.00';
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
                'list'  => $dataCenter,
                'count' => $count,
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
     * @param   string param.area - 区域 require
     * @param   int param.order 0 排序
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
        if($ProductModel->getModule() != 'mf_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_mf_dcim_module')];
        }
        $CountryModel = CountryModel::find($param['country_id']);
        if(empty($CountryModel)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_country_id_error')];
        }
        // 是否添加了相同的数据中心
        $same = $this
                ->where('product_id', $param['product_id'])
                ->where('country_id', $param['country_id'])
                ->where('city', $param['city'])
                ->where('area', $param['area'])
                ->find();
        if(!empty($same)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_the_same_data_center_already_add')];
        }

        $param['create_time'] = time();
        $param['order'] = $param['order'] ?? 0;

        $this->startTrans();
        try{
            $dataCenter = $this->create($param, ['country_id','city','area','product_id','create_time','order']);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $language = configuration('lang_admin');
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $description = lang_plugins('mf_dcim_log_create_data_center_success', ['{name}'=>$CountryModel[ $countryName ].$param['city'].$param['area'] ]);
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
     * @param   string param.area - 区域 require
     * @param   int param.order - 排序
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function updateDataCenter($param)
    {
        $dataCenter = $this->find($param['id']);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_data_center_not_found')];
        }
        $CountryModel = CountryModel::find($param['country_id']);
        if(empty($CountryModel)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_country_id_error')];
        }

        // 是否添加了相同的数据中心
        $same = $this
                ->where('product_id', $dataCenter['product_id'])
                ->where('country_id', $param['country_id'])
                ->where('city', $param['city'])
                ->where('area', $param['area'])
                ->where('id', '<>', $dataCenter['id'])
                ->find();
        if(!empty($same)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_the_same_data_center_already_add')];
        }
        if(!is_numeric($param['order'])){
            unset($param['order']);
        }

        $this->startTrans();
        try{
            $this->update($param, ['id'=>$param['id']], ['country_id','city','area','order']);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $OldCountryModel = CountryModel::find($dataCenter['country_id']);

        $language = configuration('lang_admin');
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $des = [
            'country'   => lang_plugins('mf_dcim_country'),
            'city'      => lang_plugins('mf_dcim_city'),
            'area'      => lang_plugins('mf_dcim_area'),
        ];
        $old = [
            'country' => $OldCountryModel[ $countryName ],
            'city'    => $dataCenter['city'],
            'area'    => $dataCenter['area'],
        ];
        $new = [
            'country' => $CountryModel[ $countryName ],
            'city'    => $param['city'],
            'area'    => $param['area'],
        ];

        $description = ToolLogic::createEditLog($old, $new, $des);
        if(!empty($description)){
            $description = lang_plugins('mf_dcim_log_modify_data_center_success', [
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
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function deleteDataCenter($id)
    {
        $dataCenter = $this->find($id);
        if(empty($dataCenter)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_data_center_not_found')];
        }
        // 有线路正在使用
        $line = LineModel::where('data_center_id', $id)->find();
        if(!empty($line)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_cannot_delete_data_center_for_line_exist')];
        }
        
        $this->startTrans();
        try{
            $dataCenter->delete();

            // 删除对应数据中心的配置限制
            ConfigLimitModel::where('data_center_id', $id)->delete();

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $CountryModel = CountryModel::find($dataCenter['country_id']);

        $language = configuration('lang_admin');
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $description = lang_plugins('mf_dcim_log_delete_data_center_success', ['{name}'=>$CountryModel[ $countryName ].$dataCenter['city'].$dataCenter['area'] ]);
        active_log($description, 'product', $dataCenter['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-03
     * @title 数据中心选择
     * @desc 数据中心选择
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.list[].id - 国家ID
     * @return  string data.list[].iso - 国家图标
     * @return  string data.list[].name - 国家名称
     * @return  string data.list[].city[].name - 城市名称
     * @return  int data.list[].city[]area[].id - 数据中心ID
     * @return  string data.list[].city[].area[].name - 区域名称
     * @return  int data.list[].city[].area[].line[].id - 线路ID
     * @return  string data.list[].city.[]area[].line[].name - 线路名称
     * @return  string data.list[].city[].area[].line[].bill_type - 计费类型(bw=带宽,flow=流量)
     * @return  int data.list[].city[].area[].line[].defence_enable - 是否启用防护(0=未启用,1=启用)
     */
    public function dataCenterSelect($param)
    {
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

        $language = app('http')->getName() == 'home' ? get_client_lang() : get_system_lang(true);
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $country = CountryModel::field('id,iso,'.$countryName.' name')
                    ->whereIn('id', $country)
                    ->select()
                    ->toArray();

        $city = $this
                ->field('id,country_id,city,area')
                ->where($where)
                ->order('order,id', 'asc')
                ->select()
                ->toArray();

        $lineArr = [];
        // 获取线路
        if(!empty($city)){
            $dataCenterId = array_column($city, 'id');

            $line = LineModel::field('id,data_center_id,name,bill_type,defence_enable')->order('order,id', 'asc')->select();
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
     * @param   int param.product_id - 商品ID require
     * @return  int data_center[].id - 国家ID
     * @return  string data_center[].iso - 图标
     * @return  string data_center[].name - 名称
     * @return  string data_center[].city[].name - 城市
     * @return  int data_center[].city[].area[].id - 数据中心ID
     * @return  string data_center[].city[].area[].name - 区域
     * @return  int data_center[].city[].area[].line[].id - 线路ID
     * @return  string data_center[].city[].area[].line[].name - 线路名称
     * @return  int data_center[].city[].area[].line[].data_center_id - 数据中心ID
     * @return  string data_center[].city[].area[].line[].bill_type - 计费类型(bw=带宽计费,flow=流量计费)
     * @return  int model_config[].id - 型号配置ID
     * @return  string model_config[].name - 型号配置名称
     * @return  string model_config[].cpu - 处理器
     * @return  string model_config[].cpu_param - 处理器参数
     * @return  string model_config[].memory - 内存
     * @return  string model_config[].disk - 硬盘
     * @return  int model_config[].support_optional - 允许增值选配(0=不允许,1=允许)
     * @return  int model_config[].optional_only_for_upgrade - 增值仅用于升降级(0=关闭,1=开启)
     * @return  int model_config[].leave_memory - 剩余内存
     * @return  int model_config[].max_memory_num - 可增加内存数量
     * @return  int model_config[].max_disk_num - 可增加硬盘数量
     * @return  string model_config[].gpu - 显卡
     * @return  int model_config[].max_gpu_num - 可增加显卡数量
     * @return  int model_config[].optional_memory[].id - 选配内存配置ID
     * @return  string model_config[].optional_memory[].value - 选配内存配置名称
     * @return  int model_config[].optional_memory[].other_config.memory - 选配内存大小
     * @return  int model_config[].optional_memory[].other_config.memory_slot - 选配内存插槽
     * @return  int model_config[].optional_disk[].id - 选配硬盘配置ID
     * @return  string model_config[].optional_disk[].value - 选配硬盘配置名称
     * @return  int model_config[].optional_gpu[].id - 选配显卡配置ID
     * @return  string model_config[].optional_gpu[].value - 选配显卡配置名称
     * @return  int config_limit[].data_center_id - 数据中心ID
     * @return  int config_limit[].line_id - 线路ID
     * @return  string config_limit[].min_bw - 带宽最小值
     * @return  string config_limit[].max_bw - 带宽最大值
     * @return  string config_limit[].min_flow - 流量最小值
     * @return  string config_limit[].max_flow - 流量最大值
     * @return  array config_limit[].model_config_id - 型号配置ID
     */
    public function orderPage($param)
    {
        $param['product_id'] = $param['product_id'] ?? 0;

        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];
        
        $dataCenter = $this
                    ->field('id,country_id')
                    ->where('product_id', $param['product_id'])
                    ->order('order,id', 'asc')
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
                ->order('order,id', 'asc')
                ->column('country_id');
        }else{
            $country = [];
        }

        $data = [
            'data_center' => [],
        ];
        if(!empty($country)){
            $language = get_client_lang();
            $countryField = ['en-us'=> 'nicename'];
            $countryName = $countryField[ $language ] ?? 'name_zh';

            $country = CountryModel::field('id,iso,'.$countryName.' name')
                    ->whereIn('id', $country)
                    ->orderRaw('field(id, '.implode(',', $country).')')
                    ->select()
                    ->toArray();

            $city = $this
                    ->field('id,country_id,city,area')
                    ->where($where)
                    ->whereIn('id', $dataCenterId)
                    ->withAttr('city', function($value){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'city' => $value,
                            ],
                        ]);
                        if(isset($multiLanguage['city'])){
                            $value = $multiLanguage['city'];
                        }
                        return $value;
                    })
                    ->withAttr('area', function($value){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'area' => $value,
                            ],
                        ]);
                        if(isset($multiLanguage['area'])){
                            $value = $multiLanguage['area'];
                        }
                        return $value;
                    })
                    ->order('order,id', 'asc')
                    ->select()
                    ->toArray();

            // 获取线路
            if(!empty($city)){
                $dataCenterId = array_column($city, 'id');

                $line = LineModel::field('id,name,data_center_id,bill_type')
                        ->whereIn('data_center_id', $dataCenterId)
                        ->where(function($query) use ($param) {
                            if(isset($param['scene']) && $param['scene'] == 'package'){
                                $query->where('bill_type', 'bw');
                            }
                        })
                        ->withAttr('name', function($value){
                            $multiLanguage = hook_one('multi_language', [
                                'replace' => [
                                    'name' => $value,
                                ],
                            ]);
                            if(isset($multiLanguage['name'])){
                                $value = $multiLanguage['name'];
                            }
                            return $value;
                        })
                        ->order('order,id', 'asc')
                        ->select()
                        ->toArray();

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
                    'line' => $lineArr[ $v['id'] ] ?? [],
                ];
            }
            foreach($country as $k=>$v){
                $temp = $cityArr[ $v['id'] ] ?? [];
                foreach($temp as $kk=>$vv){
                    $country[$k]['city'][] = [
                        'name' => (string)$kk,
                        'area' => $vv['area']
                    ];
                }
            }

            $data['data_center'] = $country;
        }
        // 获取型号配置
        $data['model_config'] = ModelConfigModel::field('id,name,cpu,cpu_param,memory,disk,support_optional,optional_only_for_upgrade,leave_memory,max_memory_num,max_disk_num,gpu,max_gpu_num')
                                ->where($where)
                                ->where('hidden', 0)
                                ->withAttr('name', function($value){
                                    $multiLanguage = hook_one('multi_language', [
                                        'replace' => [
                                            'name' => $value,
                                        ],
                                    ]);
                                    if(isset($multiLanguage['name'])){
                                        $value = $multiLanguage['name'];
                                    }
                                    return $value;
                                })
                                ->withAttr('cpu', function($value){
                                    $multiLanguage = hook_one('multi_language', [
                                        'replace' => [
                                            'cpu' => $value,
                                        ],
                                    ]);
                                    if(isset($multiLanguage['cpu'])){
                                        $value = $multiLanguage['cpu'];
                                    }
                                    return $value;
                                })
                                ->withAttr('cpu_param', function($value){
                                    $multiLanguage = hook_one('multi_language', [
                                        'replace' => [
                                            'cpu_param' => $value,
                                        ],
                                    ]);
                                    if(isset($multiLanguage['cpu_param'])){
                                        $value = $multiLanguage['cpu_param'];
                                    }
                                    return $value;
                                })
                                ->withAttr('memory', function($value){
                                    $multiLanguage = hook_one('multi_language', [
                                        'replace' => [
                                            'memory' => $value,
                                        ],
                                    ]);
                                    if(isset($multiLanguage['memory'])){
                                        $value = $multiLanguage['memory'];
                                    }
                                    return $value;
                                })
                                ->withAttr('disk', function($value){
                                    $multiLanguage = hook_one('multi_language', [
                                        'replace' => [
                                            'disk' => $value,
                                        ],
                                    ]);
                                    if(isset($multiLanguage['disk'])){
                                        $value = $multiLanguage['disk'];
                                    }
                                    return $value;
                                })
                                ->order('order,id', 'asc')
                                ->select()
                                ->toArray();

        $modelConfigId = array_column($data['model_config'], 'id');
        // 获取选配内存/硬盘/显卡
        $optional = ModelConfigOptionLinkModel::alias('mcol')
                    ->field('mcol.model_config_id,mcol.option_id,mcol.option_rel_type,o.value,o.other_config')
                    ->join('module_mf_dcim_option o', 'mcol.option_id=o.id')
                    ->whereIn('mcol.model_config_id', $modelConfigId)
                    ->withAttr('value', function($value){
                        $multiLanguage = hook_one('multi_language', [
                            'replace' => [
                                'value' => $value,
                            ],
                        ]);
                        if(isset($multiLanguage['value'])){
                            $value = $multiLanguage['value'];
                        }
                        return $value;
                    })
                    ->order('o.order,o.id', 'asc')
                    ->select();

        $optionalMemory = [];
        $optionalDisk = [];
        $optionalGpu = [];

        foreach($optional as $v){
            if($v['option_rel_type'] == OptionModel::MEMORY){
                $optionalMemory[ $v['model_config_id'] ][] = [
                    'id'            => $v['option_id'],
                    'value'         => $v['value'],
                    'other_config'  => json_decode($v['other_config'], true),
                ];
            }else if($v['option_rel_type'] == OptionModel::DISK){
                $optionalDisk[ $v['model_config_id'] ][] = [
                    'id'            => $v['option_id'],
                    'value'         => $v['value'],
                ];
            }else if($v['option_rel_type'] == OptionModel::GPU){
                $optionalGpu[ $v['model_config_id'] ][] = [
                    'id'            => $v['option_id'],
                    'value'         => $v['value'],
                ];
            }
        }

        foreach($data['model_config'] as $k=>$v){
            $data['model_config'][$k]['optional_memory'] = [];
            $data['model_config'][$k]['optional_disk'] = [];
            $data['model_config'][$k]['optional_gpu'] = [];
            if($v['support_optional'] == 1 && $v['optional_only_for_upgrade'] == 0){
                if($v['max_memory_num'] > 0 && $v['leave_memory'] > 0){
                    $data['model_config'][$k]['optional_memory'] = $optionalMemory[ $v['id'] ] ?? [];
                }
                if($v['max_disk_num'] > 0){
                    $data['model_config'][$k]['optional_disk'] = $optionalDisk[ $v['id'] ] ?? [];
                }
                if($v['max_gpu_num'] > 0){
                    $data['model_config'][$k]['optional_gpu'] = $optionalGpu[ $v['id'] ] ?? [];
                }
            }
        }
        
        // 获取配置限制
        $data['config_limit'] = ConfigLimitModel::field('data_center_id,line_id,min_bw,max_bw,min_flow,max_flow,model_config_id')
                                ->where($where)
                                ->withAttr('model_config_id', function($v){
                                    return !empty($v) ? explode(',', $v) : [];
                                })
                                ->select()
                                ->toArray();
        return $data;
    }

    /**
     * 时间 2022-06-21
     * @title 数据中心选择
     * @desc 数据中心选择
     * @author hh
     * @version v1
     * @param   int param.id - 商品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.list[].id - 数据中心ID
     * @return  string data.list[].city - 城市
     * @return  string data.list[].area - 区域
     * @return  string data.list[].iso - 国家图标
     * @return  string data.list[].country_name - 国家名称
     */
    public function formatDisplay($param)
    {
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
     * @param   DataCenterModel $DataCenterModel - 数据中心实例
     * @return  string
     */
    public function getDataCenterName($DataCenterModel = null)
    {
        $DataCenterModel = $DataCenterModel ?? $this;

        $CountryModel = CountryModel::find($DataCenterModel['country_id']);

        $language = app('http')->getName() == 'home' ? get_client_lang() : get_system_lang(true);
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        if(app('http')->getName() == 'home'){
            $multiLanguage = hook_one('multi_language', [
                'replace' => [
                    'city' => $DataCenterModel['city'],
                    'area' => $DataCenterModel['area'],
                ],
            ]);
            if(isset($multiLanguage['city'])){
                $DataCenterModel['city'] = $multiLanguage['city'];
            }
            if(isset($multiLanguage['area'])){
                $DataCenterModel['area'] = $multiLanguage['area'];
            }
        }

        return ($CountryModel[ $countryName ] ?? '').'-'.$DataCenterModel['city'].'-'.$DataCenterModel['area'];
    }

    /**
     * 时间 2023-12-15
     * @title 获取数据中心国家名称
     * @desc  获取数据中心国家名称
     * @author hh
     * @version v1
     * @param   DataCenterModel $DataCenterModel - 数据中心实例
     * @return  string
     */
    public function getCountryName($DataCenterModel = null)
    {
        $DataCenterModel = $DataCenterModel ?? $this;

        $CountryModel = CountryModel::find($DataCenterModel['country_id']);

        $language = app('http')->getName() == 'home' ? get_client_lang() : get_system_lang(true);
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';
        return $CountryModel[ $countryName ] ?? '';
    }

}