<?php 
namespace server\mf_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ProductModel;
use server\mf_cloud\logic\ToolLogic;

/**
 * @title 推荐配置模型
 * @use   server\mf_cloud\model\RecommendConfigModel
 */
class RecommendConfigModel extends Model{

	protected $name = 'module_mf_cloud_recommend_config';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_id'        => 'int',
        'name'              => 'string',
        'description'       => 'string',
        'order'             => 'int',
        'data_center_id'    => 'int',
        'line_id'           => 'int',
        'cpu'               => 'int',
        'memory'            => 'int',
        'system_disk_size'  => 'int',
        'system_disk_type'  => 'string',
        'data_disk_size'    => 'int',
        'data_disk_type'    => 'string',
        'network_type'      => 'string',
        'bw'                => 'int',
        'flow'              => 'int',
        'peak_defence'      => 'int',
        'create_time'       => 'int',
    ];

    /**
     * 时间 2023-02-02
     * @title 添加推荐配置
     * @desc 添加推荐配置
     * @author hh
     * @version v1
     * @param   string name - 名称 require
     * @param   string description - 描述
     * @param   int order - 排序ID
     * @param   int data_center_id - 数据中心ID require
     * @param   int cpu - 核心数 require
     * @param   int memory - 内存大小 require
     * @param   int system_disk_size - 系统盘大小 require
     * @param   string system_disk_type - 系统盘类型
     * @param   int data_disk_size - 数据盘大小
     * @param   string data_disk_type - 数据盘类型
     * @param   string network_type - 网络类型(normal=经典网络,vpc=VPC网络) require
     * @param   int bw - 带宽 require
     * @param   int peak_defence - 防御峰值
     * @return  int id - 推荐配置ID
     */
    public function recommendConfigCreate($param){
        $DataCenterModel = DataCenterModel::find($param['data_center_id']);
        if(empty($DataCenterModel)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        $param['product_id'] = $DataCenterModel['product_id'];

        $check = $this->recommendConfigCheck($param);
        if(!$check['data']['validate']){
            return ['status'=>400, 'msg'=>implode(',', array_column($check['data']['error'], 'msg'))];
        }
        $ConfigLimitModel = new ConfigLimitModel();
        $check = $ConfigLimitModel->checkConfigLimit($param['product_id'], $param);
        if($check['status'] != 200){
            return ['status'=>400, 'msg'=>lang_plugins('this_config_in_recommend_config_cannot_add')];
        }

        $param['description'] = $param['description'] ?? '';
        $param['order'] = $param['order'] ?? 0;
        $param['data_disk_size'] = $param['data_disk_size'] ?? 0;
        $param['peak_defence'] = $param['peak_defence'] ?? 0;
        $param['system_disk_type'] = $param['system_disk_type'] ?? '';
        $param['data_disk_type'] = $param['data_disk_type'] ?? '';
        $param['create_time'] = time();
        if(empty($param['data_disk_size'])){
            $param['data_disk_type'] = '';
        }

        $recommendConfig = $this->create($param, ['product_id','name','description','order','data_center_id','line_id','cpu','memory','system_disk_size','system_disk_type','data_disk_size','data_disk_type','network_type','bw','flow','peak_defence','create_time']);

        $description = lang_plugins('log_mf_cloud_add_recommend_config_success', [
            '{name}' => $param['name'],
            '{cpu}' => $param['cpu'],
            '{memory}' => $param['memory'],
        ]);
        active_log($description, 'product', $param['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$recommendConfig->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-02-03
     * @title 推荐配置列表
     * @desc 推荐配置列表
     * @author hh
     * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序(id,order)
     * @param   string sort - 升降序(asc,desc)
     * @param   int product_id - 商品ID
     * @return  array list - 列表数据
     * @return  int list[].id - 推荐配置ID
     * @return  int list[].product_id - 商品ID
     * @return  string list[].name - 名称
     * @return  string list[].description - 描述
     * @return  string list[].order - 排序ID
     * @return  int list[].data_center_id - 数据中心ID
     * @return  int list[].cpu - CPU
     * @return  int list[].memory - 内存
     * @return  int list[].system_disk_size - 系统盘
     * @return  string list[].system_disk_type - 系统盘类型
     * @return  int list[].data_disk_size - 数据盘
     * @return  string list[].data_disk_type - 数据盘类型
     * @return  string list[].network_type - 网络类型(normal=经典网络,vpc=VPC网络)
     * @return  int list[].bw - 带宽
     * @return  int list[].peak_defence - 防护峰值
     * @return  int list[].create_time - 创建时间
     * @return  int count - 总条数
     */
    public function recommendConfigList($param){
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','order'])){
            $param['orderby'] = 'rc.id';
        }

        $where = [];
        if(!empty($param['product_id'])){
            $where[] = ['rc.product_id', '=', $param['product_id']];
        }

        $list = $this
                ->alias('rc')
                ->field('rc.*,dc.country_id,dc.city')
                ->where($where)
                ->leftJoin('module_mf_cloud_data_center dc', 'rc.data_center_id=dc.id')
                ->page($param['page'], $param['limit'])
                ->order($param['orderby'], $param['sort'])
                ->select()
                ->toArray();
    
        $count = $this
                ->alias('rc')
                ->where($where)
                ->count();
        
        return ['list'=>$list, 'count'=>$count];
    }

    /**
     * 时间 2023-02-02
     * @title 修改推荐配置
     * @desc 修改推荐配置
     * @author hh
     * @version v1
     * @param   int id - 推荐配置ID require
     * @param   string name - 名称 require
     * @param   string description - 描述
     * @param   int order - 排序ID
     * @param   int data_center_id - 数据中心ID require
     * @param   int cpu - 核心数 require
     * @param   int memory - 内存大小 require
     * @param   int system_disk_size - 系统盘大小 require
     * @param   string system_disk_type - 系统盘类型
     * @param   int data_disk_size - 数据盘大小
     * @param   string data_disk_type - 数据盘类型
     * @param   string network_type - 网络类型(normal=经典网络,vpc=VPC网络) require
     * @param   int bw - 带宽 require
     * @param   int peak_defence - 防御峰值
     * @return  int id - 推荐配置ID
     */
    public function recommendConfigUpdate($param){
        $recommendConfig = $this->find($param['id']);
        if(empty($recommendConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
        }
        $DataCenterModel = DataCenterModel::find($param['data_center_id']);
        if(empty($DataCenterModel) || $recommendConfig['product_id'] != $DataCenterModel['product_id']){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        $line = LineModel::find($param['line_id']);
        if(empty($line)){
            return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
        }

        $param['product_id'] = $recommendConfig['product_id'];

        $check = $this->recommendConfigCheck($param);
        if(!$check['data']['validate']){
            return ['status'=>400, 'msg'=>implode(',', array_column($check['data']['error'], 'msg'))];
        }
        $ConfigLimitModel = new ConfigLimitModel();
        $check = $ConfigLimitModel->checkConfigLimit($param['product_id'], $param);
        if($check['status'] != 200){
            return ['status'=>400, 'msg'=>lang_plugins('this_config_in_recommend_config_cannot_add')];
        }

        $param['system_disk_type'] = $param['system_disk_type'] ?? '';
        $param['data_disk_type'] = $param['data_disk_type'] ?? '';

        $this->update($param, ['id'=>$recommendConfig['id']], ['name','description','order','data_center_id','line_id','cpu','memory','system_disk_size','system_disk_type','data_disk_size','data_disk_type','network_type','bw','flow','peak_defence']);

        $des = [
            'name' => lang_plugins('mf_cloud_recommend_config_name'),
            'description' => lang_plugins('mf_cloud_recommend_config_description'),
            'order' => lang_plugins('mf_cloud_recommend_config_order'),
            'data_center' => lang_plugins('data_center'),
            'line' => lang_plugins('mf_cloud_line_name'),
            'cpu' => 'CPU',
            'memory' => lang_plugins('memory'),
            'system_disk' => lang_plugins('system_disk'),
            'data_disk' => lang_plugins('data_disk'),
            'network_type' => lang_plugins('mf_cloud_recommend_config_network_type'),
            'bw' => lang_plugins('bw'),
            'flow' => lang_plugins('flow'),
            'peak_defence' => lang_plugins('mf_cloud_recommend_config_peak_defence'),
        ];

        $networkType = [
            'normal' => lang_plugins('mf_cloud_recommend_config_normal_network'),
            'vpc' => lang_plugins('mf_cloud_recommend_config_vpc_network'),
        ];

        $oldDataCenter = DataCenterModel::find( $recommendConfig['data_center_id'] );
        $oldLine = LineModel::find($recommendConfig['line_id']);

        $old = $recommendConfig->toArray();
        $old['data_center'] = $oldDataCenter ? $oldDataCenter->getDataCenterName() : lang_plugins('null');
        $old['line'] = $oldLine['name'] ?? lang_plugins('null');
        $old['system_disk'] = $old['system_disk_type'].$old['system_disk_size'].'G';
        $old['data_disk'] = $old['data_disk_type'].$old['data_disk_size'].'G';
        $old['network_type'] = $networkType[ $old['network_type'] ];

        $param['data_center'] = $DataCenterModel->getDataCenterName();
        $param['line'] = $line['name'];
        $param['system_disk'] = $param['system_disk_type'].$param['system_disk_size'].'G';
        $param['data_disk'] = $param['data_disk_type'].$param['data_disk_size'].'G';
        $param['network_type'] = $networkType[ $param['network_type'] ];

        $description = ToolLogic::createEditLog($old, $param, $des, ['description']);
        if(!empty($description)){
            $description = lang_plugins('log_mf_cloud_modify_recommend_config_success', ['{detail}'=>$description]);
            active_log($description, 'product', $param['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-01-31
     * @title 删除配置
     * @desc 删除配置
     * @author hh
     * @version v1
     * @param   int id - 配置ID require
     */
    public function recommendConfigDelete($id){
        $recommendConfig = $this->find($id);
        if(empty($recommendConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
        }
        
        $this->where('id', $id)->delete();

        $description = lang_plugins('log_mf_cloud_delete_recommend_config_success', [
            '{name}' => $recommendConfig['name'],
            '{cpu}' => $recommendConfig['cpu'],
            '{memory}' => $recommendConfig['memory'],
        ]);
        active_log($description, 'product', $recommendConfig['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-02
     * @title 验证推荐配置是否存在
     * @desc 验证推荐配置是否存在
     * @author hh
     * @version v1
     * @param   int data_center_id - 数据中心ID require
     * @param   int cpu - 核心数 require
     * @param   int memory - 内存大小 require
     * @param   int system_disk_size - 系统盘大小 require
     * @param   string system_disk_type - 系统盘类型
     * @param   int data_disk_size - 数据盘大小
     * @param   string data_disk_type - 数据盘类型
     * @param   string network_type - 网络类型(normal=经典网络,vpc=VPC网络) require
     * @param   int bw - 带宽 require
     * @param   int peak_defence - 防御峰值
     */
    public function recommendConfigCheck(&$param){
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'validate' => true,
                'error'    => []
            ]
        ];
        $param['product_id'] = 0;
        // 验证数据中心
        $dataCenter = DataCenterModel::find($param['data_center_id']);
        if(empty($dataCenter)){
            $result['data']['error'][] = [
                'field' => 'data_center_id',
                'msg'   => lang_plugins('data_center_not_found'),
            ];
        }else{
            $param['product_id'] = $dataCenter['product_id'];
        }
        $line = LineModel::find($param['line_id']);
        if(empty($line)){
            $result['data']['error'][] = [
                'field' => 'line_id',
                'msg'   => lang_plugins('line_not_found'),
            ];
        }
        // 验证CPU
        $cpu = OptionModel::where('product_id', $param['product_id'])->where('rel_type', OptionModel::CPU)->where('value', $param['cpu'])->find();
        if(empty($cpu)){
            $result['data']['error'][] = [
                'field' => 'cpu',
                'msg'   => lang_plugins('mf_cloud_please_check_cpu', ['{cpu}'=>$param['cpu']]),
            ];
        }
        // 验证内存
        $memory = OptionModel::where('product_id', $param['product_id'])
                    ->where('rel_type', OptionModel::MEMORY)
                    ->where(function($query) use ($param) {
                        $query->whereOr('value', $param['memory'])
                              ->whereOr('min_value<='.$param['memory'].' AND max_value>='.$param['memory']);
                    })
                    ->select()
                    ->toArray();
        if(!empty($memory)){
            $match = false;
            foreach($memory as $v){
                if($v['type'] == 'radio'){
                    $match = true;
                    break;
                }else{
                    if(($param['memory'] - $v['min_value'])%$v['step'] == 0){
                        $match = true;
                        break;
                    }
                }
            }
            if(!$match){
                $result['data']['error'][] = [
                    'field' => 'memory',
                    'msg'   => lang_plugins('mf_cloud_please_check_memory', ['{memory}'=>$param['memory']]),
                ];
            }
        }else{
            $result['data']['error'][] = [
                'field' => 'memory',
                'msg'   => lang_plugins('mf_cloud_please_check_memory', ['{memory}'=>$param['memory']]),
            ];
        }
        // 验证系统盘
        $systemDiskSize = OptionModel::where('product_id', $param['product_id'])
                    ->where('rel_type', OptionModel::SYSTEM_DISK)
                    ->where(function($query) use ($param) {
                        $query->whereOr('value', $param['system_disk_size'])
                              ->whereOr('min_value<='.$param['system_disk_size'].' AND max_value>='.$param['system_disk_size']);
                    })
                    ->select()
                    ->toArray();
        $param['system_disk_type'] = $param['system_disk_type'] ?? '';
        if(!empty($systemDiskSize)){
            $match = false;
            foreach($systemDiskSize as $v){
                // 先匹配类型,类型不对直接pass
                $otherConfig = json_decode($v['other_config'], true);
                if($otherConfig['disk_type'] !== $param['system_disk_type']){
                    continue;
                }
                if($v['type'] == 'radio'){
                    $match = true;
                    break;
                }else{
                    if(($param['system_disk_size'] - $v['min_value'])%$v['step'] == 0){
                        $match = true;
                        break;
                    }
                }
            }
            if(!$match){
                $result['data']['error'][] = [
                    'field' => 'system_disk_size',
                    'msg'   => lang_plugins('mf_cloud_please_check_system_disk', ['{system_disk}'=>$param['system_disk_type'].$param['system_disk_size']]),
                ];
            }
        }else{
            $result['data']['error'][] = [
                'field' => 'system_disk_size',
                'msg'   => lang_plugins('mf_cloud_please_check_system_disk', ['{system_disk}'=>$param['system_disk_type'].$param['system_disk_size']]),
            ];
        }
        // 验证数据盘
        if(isset($param['data_disk_size']) && $param['data_disk_size']>0){
            $dataDiskSize = OptionModel::where('product_id', $param['product_id'])
                    ->where('rel_type', OptionModel::DATA_DISK)
                    ->where(function($query) use ($param) {
                        $query->whereOr('value', $param['data_disk_size'])
                              ->whereOr('min_value<='.$param['data_disk_size'].' AND max_value>='.$param['data_disk_size']);
                    })
                    ->select()
                    ->toArray();
            $param['data_disk_type'] = $param['data_disk_type'] ?? '';
            if(!empty($dataDiskSize)){
                $match = false;
                foreach($dataDiskSize as $v){
                    // 先匹配类型,类型不对直接pass
                    $otherConfig = json_decode($v['other_config'], true);
                    if($otherConfig['disk_type'] !== $param['data_disk_type']){
                        continue;
                    }
                    if($v['type'] == 'radio'){
                        $match = true;
                        break;
                    }else{
                        if(($param['data_disk_size'] - $v['min_value'])%$v['step'] == 0){
                            $match = true;
                            break;
                        }
                    }
                }
                if(!$match){
                    $result['data']['error'][] = [
                        'field' => 'data_disk_size',
                        'msg'   => lang_plugins('mf_cloud_please_check_data_disk', ['{data_disk}'=>$param['data_disk_type'].$param['data_disk_size']]),
                    ];
                }
            }else{
                $result['data']['error'][] = [
                    'field' => 'data_disk_size',
                    'msg'   => lang_plugins('mf_cloud_please_check_data_disk', ['{data_disk}'=>$param['data_disk_type'].$param['data_disk_size']]),
                ];
            }
        }
        $networkType = [
            'normal' => lang_plugins('mf_cloud_recommend_config_normal_network'),
            'vpc'    => lang_plugins('mf_cloud_recommend_config_vpc_network'),
        ];
        // 验证网络类型
        $config = ConfigModel::where('product_id', $param['product_id'])->find();
        if(!empty($config)){
            if($config['support_'.$param['network_type'].'_network'] != 1){
                $result['data']['error'][] = [
                    'field' => 'network_type',
                    'msg'   => lang_plugins('mf_cloud_please_check_network_type', ['{network_type}'=>$networkType[$param['network_type']]]),
                ];
            }
        }
        if(!empty($line)){
            if($line['bill_type'] == 'bw'){
                if(isset($param['bw']) && !empty($param['bw']) && is_numeric($param['bw'])){
                    // 验证带宽
                    $bw = OptionModel::where('product_id', $param['product_id'])
                        ->where('rel_type', OptionModel::LINE_BW)
                        ->where('rel_id', $line['id'])
                        ->where(function($query) use ($param) {
                            $query->whereOr('value', $param['bw'])
                                  ->whereOr('min_value<='.$param['bw'].' AND max_value>='.$param['bw']);
                        })
                        ->select()
                        ->toArray();
                    $match = false;
                    foreach($bw as $v){
                        if($v['type'] == 'radio'){
                            $match = true;
                            break;
                        }else{
                            if(($param['bw'] - $v['min_value'])%$v['step'] == 0){
                                $match = true;
                                break;
                            }
                        }
                    }
                    if(!$match){
                        $result['data']['error'][] = [
                            'field' => 'bw',
                            'msg'   => lang_plugins('mf_cloud_please_check_line_bw', ['{line}'=>$line['name'],'{bw}'=>$param['bw'] ]),
                        ];
                    }
                }else{
                    $result['data']['error'][] = [
                        'field' => 'bw',
                        'msg'   => lang_plugins('please_input_bw'),
                    ];
                }
                $param['flow'] = 0;
            }else{
                if(isset($param['flow']) && is_numeric($param['flow']) && $param['flow']>=0){
                    // 验证带宽
                    $flow = OptionModel::where('product_id', $param['product_id'])
                        ->where('rel_type', OptionModel::LINE_FLOW)
                        ->where('rel_id', $line['id'])
                        ->where('value', $param['flow'])
                        ->find();
                    if(empty($flow)){
                        $result['data']['error'][] = [
                            'field' => 'bw',
                            'msg'   => lang_plugins('mf_cloud_please_check_line_flow', ['{line}'=>$line['name'],'{flow}'=>$param['flow']]),
                        ];
                    }
                }else{
                    $result['data']['error'][] = [
                        'field' => 'flow',
                        'msg'   => lang_plugins('please_input_line_flow'),
                    ];
                }
                $param['bw'] = 0;
            }
            // 验证防御峰值
            if(isset($param['peak_defence']) && $param['peak_defence']>0){
                if($line['defence_enable'] != 1){
                    $result['data']['error'][] = [
                        'field' => 'peak_defence',
                        'msg'   => lang_plugins('mf_cloud_please_check_line_peak_defence', ['{peak_defence}'=>$param['peak_defence']]),
                    ];
                }else{
                    // 带宽没有关联线路直接不管
                    $defence = OptionModel::where('product_id', $param['product_id'])
                            ->where('rel_type', OptionModel::LINE_DEFENCE)
                            ->whereIn('rel_id', $line['id'])
                            ->where('value', $param['peak_defence'])
                            ->value('id');
                    if(empty($defence)){
                        $result['data']['error'][] = [
                            'field' => 'peak_defence',
                            'msg'   => lang_plugins('mf_cloud_please_check_line_peak_defence', ['{peak_defence}'=>$param['peak_defence']]),
                        ];
                    }
                }
            }
        }
        if(!empty($result['data']['error'])){
            $result['data']['validate'] = false;
        }
        return $result;
    }


}