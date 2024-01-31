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
        // 'network_type'      => 'string',
        'bw'                => 'int',
        'flow'              => 'int',
        'peak_defence'      => 'int',
        'ip_num'            => 'int',
        'upgrade_range'     => 'int',
        'hidden'            => 'int',
        'create_time'       => 'int',
        'gpu_num'           => 'int',
    ];

    const UPGRADE_DISABLE = 0;
    const UPGRADE_ALL = 1;
    const UPGRADE_CUSTOM = 2;

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
     * @param   int bw - 带宽 require
     * @param   int peak_defence - 防御峰值
     * @param   int ip_num - IP数量
     * @return  int id - 推荐配置ID
     */
    public function recommendConfigCreate($param){
        $DataCenterModel = DataCenterModel::find($param['data_center_id']);
        if(empty($DataCenterModel)){
            return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
        }
        $line = LineModel::find($param['line_id']);
        if(empty($line) || $line['data_center_id'] != $param['data_center_id']){
            return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
        }
        $param['product_id'] = $DataCenterModel['product_id'];

        $check = $this->recommendConfigCheck($param);
        if(!$check['data']['validate']){
            return ['status'=>400, 'msg'=>implode(',', array_column($check['data']['error'], 'msg'))];
        }

        $param['description'] = $param['description'] ?? '';
        $param['order'] = $param['order'] ?? 0;
        $param['data_disk_size'] = $param['data_disk_size'] ?? 0;
        $param['peak_defence'] = $param['peak_defence'] ?? 0;
        $param['system_disk_type'] = $param['system_disk_type'] ?? '';
        $param['data_disk_type'] = $param['data_disk_type'] ?? '';
        $param['create_time'] = time();
        $param['gpu_num'] = $param['gpu_num'] ?? 0;
        if(empty($param['data_disk_size'])){
            $param['data_disk_type'] = '';
        }
        // 验证周期价格
        $duration = DurationModel::where('product_id', $param['product_id'])->column('id');

        $this->startTrans();
        try{
            $recommendConfig = $this->create($param, ['product_id','name','description','order','data_center_id','line_id','cpu','memory','system_disk_size','system_disk_type','data_disk_size','data_disk_type','bw','flow','peak_defence','ip_num','create_time','gpu_num']);

            $priceArr = [];
            foreach($duration as $v){
                if(isset($param['price'][$v])){
                    $priceArr[] = [
                        'product_id'    => $param['product_id'],
                        'rel_type'      => PriceModel::REL_TYPE_RECOMMEND_CONFIG,
                        'rel_id'        => $recommendConfig->id,
                        'duration_id'   => $v,
                        'price'         => $param['price'][$v],
                    ];
                }
            }
            if(!empty($priceArr)){
                $PriceModel = new PriceModel();
                $PriceModel->insertAll($priceArr);
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();

            $result = [
                'status' => 400,
                'msg'    => $e->getMessage(),
            ];
            return $result;
        }

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
            $param['orderby'] = 'id';
        }

        $where = [];
        if(!empty($param['product_id'])){
            $where[] = ['product_id', '=', $param['product_id']];
        }
        if(isset($param['data_center_id']) && $param['data_center_id'] > 0){
            $where[] = ['data_center_id', '=', $param['data_center_id']];
        }

        $list = $this
                ->field('id,name,description,order,product_id,data_center_id,upgrade_range,hidden')
                ->where($where)
                ->page($param['page'], $param['limit'])
                ->order($param['orderby'], $param['sort'])
                ->select()
                ->toArray();
    
        $count = $this
                ->where($where)
                ->count();

        if(!empty($list)){
            $id = array_column($list, 'id');

            // 获取关联的套餐
            $upgradeRange = RecommendConfigUpgradeRangeModel::whereIn('recommend_config_id', $id)->select();
            $upgradeRangeArr = [];
            foreach($upgradeRange as $v){
                $upgradeRangeArr[ $v['recommend_config_id'] ][] = $v['rel_recommend_config_id'];
            }

            // 时间最短的周期
            $firstDuration = DurationModel::field('id,name,num,unit')->where('product_id', $list[0]['product_id'])->orderRaw('field(unit, "hour","day","month")')->order('num', 'asc')->find();
            if(!empty($firstDuration)){
                $price = PriceModel::alias('p')
                    ->field('p.rel_id,p.price')
                    ->where('p.product_id', $list[0]['product_id'])
                    ->where('p.rel_type', PriceModel::REL_TYPE_RECOMMEND_CONFIG)
                    ->whereIn('p.rel_id', $id)
                    ->where('p.duration_id', $firstDuration['id'])
                    ->select()
                    ->toArray();

                $priceArr = [];
                foreach($price as $k=>$v){
                    $priceArr[ $v['rel_id'] ] = $v;
                }

                foreach($list as $k=>$v){
                    $list[$k]['price'] = $priceArr[$v['id']]['price'] ?? '0.00';
                    $list[$k]['duration'] = $firstDuration['name'];
                    $list[$k]['rel_id'] = $upgradeRangeArr[ $v['id'] ] ?? [];
                }
            }else{
                foreach($list as $k=>$v){
                    $list[$k]['price'] = '0.00';
                    $list[$k]['duration'] = '';
                    $list[$k]['rel_id'] = $upgradeRangeArr[ $v['id'] ] ?? [];
                }
            }
        }
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
        if(empty($line) || $line['data_center_id'] != $param['data_center_id']){
            return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
        }
        $param['product_id'] = $recommendConfig['product_id'];
        $productId = $param['product_id'];

        $check = $this->recommendConfigCheck($param);
        if(!$check['data']['validate']){
            return ['status'=>400, 'msg'=>implode(',', array_column($check['data']['error'], 'msg'))];
        }

        $param['system_disk_type'] = $param['system_disk_type'] ?? '';
        $param['data_disk_type'] = $param['data_disk_type'] ?? '';

        // 验证周期价格
        $duration = DurationModel::field('id,name')->where('product_id', $productId)->select();

        $wherePrice = [
            ['product_id', '=', $productId],
            ['rel_type', '=', PriceModel::REL_TYPE_RECOMMEND_CONFIG],
            ['rel_id', '=', $recommendConfig['id']],
        ];

        $oldPrice = PriceModel::field('duration_id,price')->where($wherePrice)->select()->toArray();
        $oldPrice = array_column($oldPrice, 'price', 'duration_id');

        $this->startTrans();
        try{
            $this->update($param, ['id'=>$recommendConfig['id']], ['name','description','order','data_center_id','line_id','cpu','memory','system_disk_size','system_disk_type','data_disk_size','data_disk_type','bw','flow','peak_defence','ip_num','gpu_num']);

            $priceArr = [];
            foreach($duration as $v){
                if(isset($param['price'][$v['id']])){
                    $priceArr[] = [
                        'product_id'    => $productId,
                        'rel_type'      => PriceModel::REL_TYPE_RECOMMEND_CONFIG,
                        'rel_id'        => $recommendConfig['id'],
                        'duration_id'   => $v['id'],
                        'price'         => $param['price'][$v['id']],
                    ];
                }
            }

            PriceModel::where($wherePrice)->delete();
            if(!empty($priceArr)){
                $PriceModel = new PriceModel();
                $PriceModel->insertAll($priceArr);
            }

            // 如果数据中心变更,删除对应可升降级套餐
            if($recommendConfig['data_center_id'] != $DataCenterModel['id']){
                RecommendConfigUpgradeRangeModel::where('recommend_config_id', $recommendConfig['id'])->delete();
                RecommendConfigUpgradeRangeModel::where('rel_recommend_config_id', $recommendConfig['id'])->delete();
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();

            $result = [
                'status' => 400,
                'msg'    => $e->getMessage(),
            ];
            return $result;
        }

        $des = [
            'name'          => lang_plugins('mf_cloud_recommend_config_name'),
            'description'   => lang_plugins('mf_cloud_recommend_config_description'),
            'order'         => lang_plugins('mf_cloud_recommend_config_order'),
            'data_center'   => lang_plugins('data_center'),
            'line'          => lang_plugins('mf_cloud_line_name'),
            'cpu'           => 'CPU',
            'memory'        => lang_plugins('memory'),
            'system_disk'   => lang_plugins('system_disk'),
            'data_disk'     => lang_plugins('data_disk'),
            // 'network_type' => lang_plugins('mf_cloud_recommend_config_network_type'),
            'bw'            => lang_plugins('bw'),
            'flow'          => lang_plugins('flow'),
            'peak_defence'  => lang_plugins('mf_cloud_recommend_config_peak_defence'),
            'gpu_num'       => lang_plugins('mf_cloud_option_value_8'),
        ];

        // $networkType = [
        //     'normal'        => lang_plugins('mf_cloud_recommend_config_normal_network'),
        //     'vpc'           => lang_plugins('mf_cloud_recommend_config_vpc_network'),
        // ];

        $oldDataCenter = DataCenterModel::find( $recommendConfig['data_center_id'] );
        $oldLine = LineModel::find($recommendConfig['line_id']);

        $old = $recommendConfig->toArray();
        $old['data_center'] = $oldDataCenter ? $oldDataCenter->getDataCenterName() : lang_plugins('null');
        $old['line'] = $oldLine['name'] ?? lang_plugins('null');
        $old['system_disk'] = $old['system_disk_type'].$old['system_disk_size'].'G';
        $old['data_disk'] = $old['data_disk_type'].$old['data_disk_size'].'G';
        // $old['network_type'] = $networkType[ $old['network_type'] ];

        $param['data_center'] = $DataCenterModel->getDataCenterName();
        $param['line'] = $line['name'];
        $param['system_disk'] = $param['system_disk_type'].$param['system_disk_size'].'G';
        $param['data_disk'] = $param['data_disk_type'].$param['data_disk_size'].'G';
        // $param['network_type'] = $networkType[ $param['network_type'] ];

        // 每个周期的价格对比
        foreach($duration as $v){
            $des[ 'duration_'.$v['id'] ] = $v['name'].lang_plugins('price');
            $old[ 'duration_'.$v['id'] ] = $oldPrice[ $v['id'] ] ?? lang_plugins('null');
            $param[ 'duration_'.$v['id'] ] = $param['price'][$v['id']] ?? lang_plugins('null');
        }

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
     * @title 删除套餐
     * @desc 删除套餐
     * @author hh
     * @version v1
     * @param   int id - 配置ID require
     */
    public function recommendConfigDelete($id){
        $recommendConfig = $this->find($id);
        if(empty($recommendConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
        }
        // TODO 当有商品正在使用时不能删除

        $this->startTrans();
        try{
            $this->where('id', $id)->delete();

            PriceModel::where('product_id', $recommendConfig['product_id'])->where('rel_type', PriceModel::REL_TYPE_RECOMMEND_CONFIG)->where('rel_id', $id)->delete();
            RecommendConfigUpgradeRangeModel::where('recommend_config_id', $id)->delete();
            RecommendConfigUpgradeRangeModel::where('rel_recommend_config_id', $id)->delete();

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();

            $result = [
                'status' => 400,
                'msg'    => $e->getMessage(),
            ];
            return $result;
        }
        // 是否还有剩余套餐
        $count = $this->where('product_id', $recommendConfig['product_id'])->count();
        if($count == 0){
            ConfigModel::where('product_id', $recommendConfig['product_id'])->update(['only_sale_recommend_config'=>0]);
        }

        $description = lang_plugins('log_mf_cloud_delete_recommend_config_success', [
            '{name}'    => $recommendConfig['name'],
            '{cpu}'     => $recommendConfig['cpu'],
            '{memory}'  => $recommendConfig['memory'],
        ]);
        active_log($description, 'product', $recommendConfig['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-10-24
     * @title 套餐详情
     * @desc 套餐详情
     * @author hh
     * @version v1
     * @param   int id - 套餐ID require
     * @return  [type]     [description]
     */
    public function recommendConfigIndex($id){
        $recommendConfig = $this->find($id);
        if(empty($recommendConfig)){
            return (object)[];
        }
        $dataCenter = DataCenterModel::find($recommendConfig['data_center_id']);

        $duration = DurationModel::alias('d')
                    ->field('d.id,d.name,p.price')
                    ->leftJoin('module_mf_cloud_price p', 'p.product_id='.$recommendConfig['product_id'].' AND  p.rel_type='.PriceModel::REL_TYPE_RECOMMEND_CONFIG.' AND p.rel_id='.$id.' AND d.id=p.duration_id')
                    ->where('d.product_id', $recommendConfig['product_id'])
                    ->withAttr('price', function($val){
                        return $val ?? '';
                    })
                    ->select()
                    ->toArray();

        $recommendConfig = $recommendConfig->toArray();
        $recommendConfig['country_id'] = $dataCenter['country_id'] ?? 0;
        $recommendConfig['city'] = $dataCenter['city'] ?? '';
        $recommendConfig['duration'] = $duration;

        return $recommendConfig;
    }

    /**
     * 时间 2023-10-24
     * @title 保存套餐升降级范围
     * @desc 保存套餐升降级范围
     * @url /admin/v1/mf_cloud/recommend_config/upgrade_range
     * @method  POST
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   object recommend_config - 升降级范围(如{"5":{"upgrade_range":0, "rel_id": []}},5是套餐ID,upgrade_range:0=不可升降级,1=所有套餐,2=自选套餐,2的时候需要传入rel_id是所选套餐ID) require
     */
    public function saveUpgradeRange($param){
        $product = ProductModel::find($param['product_id'] ?? 0);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        $recommendConfig = $this
                        ->field('id,name,upgrade_range,data_center_id')
                        ->where('product_id', $product['id'])
                        ->select()
                        ->toArray();
        if(empty($recommendConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_please_add_recommend_config_first')];
        }
        $recommendConfigArr = [];
        $dataCenterRecommendConfig = [];
        foreach($recommendConfig as $v){
            $recommendConfigArr[$v['id']] = $v;
            $dataCenterRecommendConfig[$v['data_center_id']][$v['id']] = $v['name'];
        }
        unset($recommendConfig);
        // 验证
        if(!isset($param['recommend_config']) || empty($param['recommend_config'])){
            return ['status'=>400, 'msg'=>lang_plugins('param_error')];
        }
        foreach($param['recommend_config'] as $k=>$v){
            if(!isset($recommendConfigArr[$k])){
                return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
            }
            if(!isset($v['upgrade_range']) || !in_array($v['upgrade_range'], [self::UPGRADE_DISABLE, self::UPGRADE_ALL, self::UPGRADE_CUSTOM]) ){
                return ['status'=>400, 'msg'=>lang_plugins('param_error')];
            }
            if($v['upgrade_range'] == self::UPGRADE_CUSTOM){
                // 验证关联套餐
                if(!isset($v['rel_id']) || empty($v['rel_id']) || !is_array($v['rel_id'])){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_please_select_upgrade_recommend_config')];
                }
                foreach($v['rel_id'] as $relRecommendConfigId){
                    $relRecommendConfigId = (int)$relRecommendConfigId;
                    if(!isset($dataCenterRecommendConfig[ $recommendConfigArr[$k]['data_center_id'] ][$relRecommendConfigId])){
                        return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
                    }
                    if($relRecommendConfigId == $k){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_cannot_select_self_for_upgrade_recommend_config')];
                    }
                }
            }
        }

        $detail = '';
        $RecommendConfigUpgradeRangeModel = new RecommendConfigUpgradeRangeModel();

        foreach($param['recommend_config'] as $k=>$v){
            if($v['upgrade_range'] == self::UPGRADE_CUSTOM || $v['upgrade_range'] != $recommendConfigArr[$k]['upgrade_range']){
                $old = $this->upgradeRangeDesc($recommendConfigArr[$k]);

                $this->where('id', $k)->update(['upgrade_range'=>$v['upgrade_range'] ]);

                RecommendConfigUpgradeRangeModel::where('recommend_config_id', $k)->delete();
                if($v['upgrade_range'] == self::UPGRADE_CUSTOM){
                    $upgradeRange = [];
                    foreach($v['rel_id'] as $relRecommendConfigId){
                        $upgradeRange[] = [
                            'recommend_config_id'       => $k,
                            'rel_recommend_config_id'   => (int)$relRecommendConfigId,
                        ];
                    }
                    if(!empty($upgradeRange)){
                        $RecommendConfigUpgradeRangeModel->insertAll($upgradeRange);
                    }
                }
                $detail .= lang_plugins("log_mf_cloud_recommend_config_change", [
                    '{name}' => $recommendConfigArr[$k]['name'],
                    '{old}'  => $old,
                    '{new}'  => $this->upgradeRangeDesc([
                        'id'            => $k,
                        'upgrade_range' => $v['upgrade_range'],
                    ]),
                ]);
            }
        }
        if(!empty($detail)){
            $description = lang_plugins('log_mf_cloud_save_recommend_config_upgrade_range_success', [
                '{product}' => 'product#'.$product['id'].'#'.$product['name'].'#',
                '{detail}'  => $detail,
            ]);
            active_log($description, 'product', $product['id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-10-24
     * @title 获取可升降级套餐
     * @desc 获取可升降级套餐
     * @author hh
     * @version v1
     * @param   array $current_recommend_config - 当前套餐数据
     */
    public function getUpgradeRecommendConfig($current_recommend_config = []){
        $data = [];
        $count = 0;
        if(empty($current_recommend_config['upgrade_range'])){
            return ['list'=>$data, 'count'=>$count];
        }
        if($current_recommend_config['upgrade_range'] == self::UPGRADE_DISABLE){

        }else if($current_recommend_config['upgrade_range'] == self::UPGRADE_ALL){
            $where = [
                ['rc.product_id', '=', $current_recommend_config['product_id']],
                ['rc.data_center_id', '=', $current_recommend_config['data_center_id']],
                ['rc.id', '<>', $current_recommend_config['id']],
                ['rc.hidden', '=', 0],
            ];

            $data = $this
                    ->field('rc.*,l.gpu_name')
                    ->alias('rc')
                    ->leftJoin('module_mf_cloud_line l', 'rc.line_id=l.id')
                    ->where($where)
                    ->order('rc.order,rc.id', 'asc')
                    ->select()
                    ->toArray();

            $count = $this->alias('rc')->where($where)->count();
        }else if($current_recommend_config['upgrade_range'] == self::UPGRADE_CUSTOM){
            $id = RecommendConfigUpgradeRangeModel::where('recommend_config_id', $current_recommend_config['id'])->column('rel_recommend_config_id');
            if(!empty($id)){
                $where = [
                    ['rc.product_id', '=', $current_recommend_config['product_id']],
                    ['rc.data_center_id', '=', $current_recommend_config['data_center_id']],
                    ['rc.id', 'IN', $id],
                    ['rc.hidden', '=', 0],
                ];

                $data = $this
                        ->field('rc.*,l.gpu_name')
                        ->alias('rc')
                        ->leftJoin('module_mf_cloud_line l', 'rc.line_id=l.id')
                        ->where($where)
                        ->order('rc.order,rc.id', 'asc')
                        ->select()
                        ->toArray();

                $count = $this->alias('rc')->where($where)->count();
            }
        }
        return ['list'=>$data, 'count'=>$count];
    }

    protected function upgradeRangeDesc($recommend_config){
        $desc = '';
        if($recommend_config['upgrade_range'] == 0){
            $desc = lang_plugins('mf_cloud_upgrade_disable');
        }else if($recommend_config['upgrade_range'] == 1){
            $desc = lang_plugins('mf_cloud_all');
        }else if($recommend_config['upgrade_range'] == 2){
            $relRecommendConfig = RecommendConfigUpgradeRangeModel::alias('rcur')
                                ->join('module_mf_cloud_recommend_config rc', 'rcur.rel_recommend_config_id=rc.id')
                                ->where('rcur.recommend_config_id', $recommend_config['id'])
                                ->column('rc.name');
            $desc = implode(',', $relRecommendConfig);
        }
        return $desc;
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
        $param['gpu_num'] = $param['gpu_num'] ?? 0;
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
        }else{
            if($param['gpu_num'] > 0 && $line['gpu_enable'] == 0){
                $result['data']['error'][] = [
                    'field' => 'line_id',
                    'msg'   => lang_plugins('mf_cloud_line_not_enable_gpu'),
                ];
            }
        }
        // 验证CPU
        // $cpu = OptionModel::where('product_id', $param['product_id'])->where('rel_type', OptionModel::CPU)->where('value', $param['cpu'])->find();
        // if(empty($cpu)){
        //     $result['data']['error'][] = [
        //         'field' => 'cpu',
        //         'msg'   => lang_plugins('mf_cloud_please_check_cpu', ['{cpu}'=>$param['cpu']]),
        //     ];
        // }
        // // 验证内存
        // $memory = OptionModel::where('product_id', $param['product_id'])
        //             ->where('rel_type', OptionModel::MEMORY)
        //             ->where(function($query) use ($param) {
        //                 $query->whereOr('value', $param['memory'])
        //                       ->whereOr('min_value<='.$param['memory'].' AND max_value>='.$param['memory']);
        //             })
        //             ->select()
        //             ->toArray();
        // $memoryUnit = ConfigModel::where('product_id', $param['product_id'])->value('memory_unit') ?? 'GB';
        // if(!empty($memory)){
        //     $match = false;
        //     foreach($memory as $v){
        //         if($v['type'] == 'radio'){
        //             $match = true;
        //             break;
        //         }else{
        //             if(($param['memory'] - $v['min_value'])%$v['step'] == 0){
        //                 $match = true;
        //                 break;
        //             }
        //         }
        //     }
        //     if(!$match){
        //         $result['data']['error'][] = [
        //             'field' => 'memory',
        //             'msg'   => lang_plugins('mf_cloud_please_check_memory', ['{memory}'=>$param['memory'].$memoryUnit]),
        //         ];
        //     }
        // }else{
        //     $result['data']['error'][] = [
        //         'field' => 'memory',
        //         'msg'   => lang_plugins('mf_cloud_please_check_memory', ['{memory}'=>$param['memory'].$memoryUnit]),
        //     ];
        // }
        // // 验证系统盘
        // $systemDiskSize = OptionModel::where('product_id', $param['product_id'])
        //             ->where('rel_type', OptionModel::SYSTEM_DISK)
        //             ->where(function($query) use ($param) {
        //                 $query->whereOr('value', $param['system_disk_size'])
        //                       ->whereOr('min_value<='.$param['system_disk_size'].' AND max_value>='.$param['system_disk_size']);
        //             })
        //             ->select()
        //             ->toArray();
        // $param['system_disk_type'] = $param['system_disk_type'] ?? '';
        // if(!empty($systemDiskSize)){
        //     $match = false;
        //     foreach($systemDiskSize as $v){
        //         // 先匹配类型,类型不对直接pass
        //         $otherConfig = json_decode($v['other_config'], true);
        //         if($otherConfig['disk_type'] !== $param['system_disk_type']){
        //             continue;
        //         }
        //         if($v['type'] == 'radio'){
        //             $match = true;
        //             break;
        //         }else{
        //             if(($param['system_disk_size'] - $v['min_value'])%$v['step'] == 0){
        //                 $match = true;
        //                 break;
        //             }
        //         }
        //     }
        //     if(!$match){
        //         $result['data']['error'][] = [
        //             'field' => 'system_disk_size',
        //             'msg'   => lang_plugins('mf_cloud_please_check_system_disk', ['{system_disk}'=>$param['system_disk_type'].$param['system_disk_size']]),
        //         ];
        //     }
        // }else{
        //     $result['data']['error'][] = [
        //         'field' => 'system_disk_size',
        //         'msg'   => lang_plugins('mf_cloud_please_check_system_disk', ['{system_disk}'=>$param['system_disk_type'].$param['system_disk_size']]),
        //     ];
        // }
        // // 验证数据盘
        // if(isset($param['data_disk_size']) && $param['data_disk_size']>0){
        //     $dataDiskSize = OptionModel::where('product_id', $param['product_id'])
        //             ->where('rel_type', OptionModel::DATA_DISK)
        //             ->where(function($query) use ($param) {
        //                 $query->whereOr('value', $param['data_disk_size'])
        //                       ->whereOr('min_value<='.$param['data_disk_size'].' AND max_value>='.$param['data_disk_size']);
        //             })
        //             ->select()
        //             ->toArray();
        //     $param['data_disk_type'] = $param['data_disk_type'] ?? '';
        //     if(!empty($dataDiskSize)){
        //         $match = false;
        //         foreach($dataDiskSize as $v){
        //             // 先匹配类型,类型不对直接pass
        //             $otherConfig = json_decode($v['other_config'], true);
        //             if($otherConfig['disk_type'] !== $param['data_disk_type']){
        //                 continue;
        //             }
        //             if($v['type'] == 'radio'){
        //                 $match = true;
        //                 break;
        //             }else{
        //                 if(($param['data_disk_size'] - $v['min_value'])%$v['step'] == 0){
        //                     $match = true;
        //                     break;
        //                 }
        //             }
        //         }
        //         if(!$match){
        //             $result['data']['error'][] = [
        //                 'field' => 'data_disk_size',
        //                 'msg'   => lang_plugins('mf_cloud_please_check_data_disk', ['{data_disk}'=>$param['data_disk_type'].$param['data_disk_size']]),
        //             ];
        //         }
        //     }else{
        //         $result['data']['error'][] = [
        //             'field' => 'data_disk_size',
        //             'msg'   => lang_plugins('mf_cloud_please_check_data_disk', ['{data_disk}'=>$param['data_disk_type'].$param['data_disk_size']]),
        //         ];
        //     }
        // }else{
        //     $param['data_disk_size'] = 0;
        // }
        // $networkType = [
        //     'normal' => lang_plugins('mf_cloud_recommend_config_normal_network'),
        //     'vpc'    => lang_plugins('mf_cloud_recommend_config_vpc_network'),
        // ];
        // 验证网络类型
        // $config = ConfigModel::where('product_id', $param['product_id'])->find();
        // if(!empty($config)){
        //     if($config['support_'.$param['network_type'].'_network'] != 1){
        //         $result['data']['error'][] = [
        //             'field' => 'network_type',
        //             'msg'   => lang_plugins('mf_cloud_please_check_network_type', ['{network_type}'=>$networkType[$param['network_type']]]),
        //         ];
        //     }
        // }
        // if(!empty($line)){
        //     if($line['bill_type'] == 'bw'){
        //         if(isset($param['bw']) && !empty($param['bw']) && is_numeric($param['bw'])){
        //             // 验证带宽
        //             $bw = OptionModel::where('product_id', $param['product_id'])
        //                 ->where('rel_type', OptionModel::LINE_BW)
        //                 ->where('rel_id', $line['id'])
        //                 ->where(function($query) use ($param) {
        //                     $query->whereOr('value', $param['bw'])
        //                           ->whereOr('min_value<='.$param['bw'].' AND max_value>='.$param['bw']);
        //                 })
        //                 ->select()
        //                 ->toArray();
        //             $match = false;
        //             foreach($bw as $v){
        //                 if($v['type'] == 'radio'){
        //                     $match = true;
        //                     break;
        //                 }else{
        //                     if(($param['bw'] - $v['min_value'])%$v['step'] == 0){
        //                         $match = true;
        //                         break;
        //                     }
        //                 }
        //             }
        //             if(!$match){
        //                 $result['data']['error'][] = [
        //                     'field' => 'bw',
        //                     'msg'   => lang_plugins('mf_cloud_please_check_line_bw', ['{line}'=>$line['name'],'{bw}'=>$param['bw'] ]),
        //                 ];
        //             }
        //         }else{
        //             $result['data']['error'][] = [
        //                 'field' => 'bw',
        //                 'msg'   => lang_plugins('please_input_bw'),
        //             ];
        //         }
        //         $param['flow'] = 0;
        //     }else{
        //         if(isset($param['flow']) && is_numeric($param['flow']) && $param['flow']>=0){
        //             // 验证带宽
        //             $flow = OptionModel::where('product_id', $param['product_id'])
        //                 ->where('rel_type', OptionModel::LINE_FLOW)
        //                 ->where('rel_id', $line['id'])
        //                 ->where('value', $param['flow'])
        //                 ->find();
        //             if(empty($flow)){
        //                 $result['data']['error'][] = [
        //                     'field' => 'bw',
        //                     'msg'   => lang_plugins('mf_cloud_please_check_line_flow', ['{line}'=>$line['name'],'{flow}'=>$param['flow']]),
        //                 ];
        //             }
        //         }else{
        //             $result['data']['error'][] = [
        //                 'field' => 'flow',
        //                 'msg'   => lang_plugins('please_input_line_flow'),
        //             ];
        //         }
        //         $param['bw'] = 0;
        //     }
        //     // 验证防御峰值
        //     if(isset($param['peak_defence']) && $param['peak_defence']>0){
        //         if($line['defence_enable'] != 1){
        //             $result['data']['error'][] = [
        //                 'field' => 'peak_defence',
        //                 'msg'   => lang_plugins('mf_cloud_please_check_line_peak_defence', ['{peak_defence}'=>$param['peak_defence']]),
        //             ];
        //         }else{
        //             // 带宽没有关联线路直接不管
        //             $defence = OptionModel::where('product_id', $param['product_id'])
        //                     ->where('rel_type', OptionModel::LINE_DEFENCE)
        //                     ->whereIn('rel_id', $line['id'])
        //                     ->where('value', $param['peak_defence'])
        //                     ->value('id');
        //             if(empty($defence)){
        //                 $result['data']['error'][] = [
        //                     'field' => 'peak_defence',
        //                     'msg'   => lang_plugins('mf_cloud_please_check_line_peak_defence', ['{peak_defence}'=>$param['peak_defence']]),
        //                 ];
        //             }
        //         }
        //     }
        //     // 大于0才验证
        //     if(isset($param['ip_num']) && $param['ip_num'] > 0){
        //         $ipNum = OptionModel::where('product_id', $param['product_id'])
        //                 ->where('rel_type', OptionModel::LINE_IP)
        //                 ->whereIn('rel_id', $line['id'])
        //                 ->where('value', $param['ip_num'])
        //                 ->value('id');
        //         if(empty($ipNum)){
        //             $result['data']['error'][] = [
        //                 'field' => 'ip_num',
        //                 'msg'   => lang_plugins('请检查数据中心配置,IP数量配置是否支持{ip_num}个', ['{ip_num}'=>$param['ip_num']]),
        //             ];
        //         }
        //     }
        // }
        if(!empty($result['data']['error'])){
            $result['data']['validate'] = false;
        }
        return $result;
    }

    /**
     * 时间 2023-10-25
     * @title 根据套餐获取格式化参数
     * @desc  根据套餐获取格式化参数
     * @author hh
     * @version v1
     * @param   array $recommend_config - 套餐数据 require
     */
    public function formatRecommendConfig($recommend_config){
        $productId = $recommend_config['product_id'];

        $data = [
            'system_disk'       => [
                'size'          => $recommend_config['system_disk_size'],
                'type'          => $recommend_config['system_disk_type'],
                'store_id'      => 0,
            ],
            'data_disk'         => [
                'size'          => $recommend_config['data_disk_size'] ?? 0,
                'type'          => $recommend_config['data_disk_type'] ?? '',
                'store_id'      => 0,
            ],
            'cpu'               => $recommend_config['cpu'],
            'memory'            => $recommend_config['memory'] * 1024,
            'in_bw'             => $recommend_config['bw'],
            'out_bw'            => $recommend_config['bw'],
            'ip_num'            => $recommend_config['ip_num'],
            'ip_group'          => 0,
            'flow'              => $recommend_config['flow'],
            'link_clone'        => 0,
            // 高级参数
            'advanced_cpu'      => 0,
            'cpu_limit'         => 0,
            'advanced_bw'       => 0,
            'traffic_type'      => 3,               // 计费方向
            'bill_cycle'        => 'last_30days',  // 计费周期
            'gpu_num'           => $recommend_config['gpu_num'],
            'gpu_name'          => $recommend_config['gpu_num'] > 0 ? ($recommend_config['gpu_name'] ?? '') : '',
        ];

        // 匹配系统盘
        $optionSystemDisk = OptionModel::where('product_id', $productId)
                        ->where('rel_type', OptionModel::SYSTEM_DISK)
                        ->where('rel_id', 0)
                        ->whereLike('other_config', rtrim(str_replace('\\', '\\\\', json_encode(['disk_type'=>$recommend_config['system_disk_type'] ])), '}').'%')
                        ->where(function($query) use ($recommend_config) {
                            $query->whereOr('value', $recommend_config['system_disk_size'])
                                  ->whereOr('(min_value<='.$recommend_config['system_disk_size'].' AND max_value>='.$recommend_config['system_disk_size'].')');
                        })
                        ->find();
        if(!empty($optionSystemDisk)){
            $data['system_disk']['store_id'] = $optionSystemDisk['other_config']['store_id'] ?? 0;
        }
        // 匹配数据盘
        if(isset($recommend_config['data_disk_size']) && isset($recommend_config['data_disk_type'])){
            $optionDataDisk = OptionModel::where('product_id', $productId)
                        ->where('rel_type', OptionModel::DATA_DISK)
                        ->where('rel_id', 0)
                        ->whereLike('other_config', rtrim(str_replace('\\', '\\\\', json_encode(['disk_type'=>$recommend_config['data_disk_size']])), '}').'%')
                        ->where(function($query) use ($recommend_config) {
                            $query->whereOr('value', $recommend_config['data_disk_size'])
                                  ->whereOr('(min_value<='.$recommend_config['data_disk_size'].' AND max_value>='.$recommend_config['data_disk_size'].')');
                        })
                        ->find();
            if(!empty($optionDataDisk)){
                $data['data_disk']['store_id'] = $optionDataDisk['other_config']['store_id'] ?? 0;
            }
        }
        // 匹配CPU
        $optionCpu = OptionModel::where('product_id', $productId)->where('rel_type', OptionModel::CPU)->where('value', $recommend_config['cpu'])->find();
        if(!empty($optionCpu)){
            $data['advanced_cpu'] = $optionCpu['other_config']['advanced_cpu'] ?? 0;
            $data['cpu_limit'] = $optionCpu['other_config']['cpu_limit'] ?? 0;
        }
        // 匹配线路
        $line = LineModel::find($recommend_config['line_id']);
        if(!empty($line)){
            $data['link_clone'] = $line['link_clone'];
            $data['ip_group']   = $line['bw_ip_group'];

            // 只要开启设置了防御就使用防御的IP分组
            if($line['defence_enable'] == 1 && !empty($recommend_config['peak_defence'])){
                $data['ip_group'] = $line['defence_ip_group'];
            }
            // 线路带宽
            if($line['bill_type'] == 'bw'){
                $data['flow'] = 0;

                $optionBw = OptionModel::where('product_id', $productId)
                        ->where('rel_type', OptionModel::LINE_BW)
                        ->where('rel_id', $line['id'])
                        ->where(function($query) use ($recommend_config) {
                            $query->whereOr('value', $recommend_config['bw'])
                                  ->whereOr('(min_value<='.$recommend_config['bw'].' AND max_value>='.$recommend_config['bw'].')');
                        })
                        ->find();
                if(!empty($optionBw)){
                    $otherConfig = $optionBw['other_config'];

                    if(isset($otherConfig['in_bw']) && is_numeric($otherConfig['in_bw'])){
                        $data['in_bw'] = $otherConfig['in_bw'];
                    }
                    $data['advanced_bw'] = $otherConfig['advanced_bw'] ?? 0;
                }
            }else if($line['bill_type'] == 'flow'){
                $optionFlow = OptionModel::where('product_id', $productId)
                            ->where('rel_type', OptionModel::LINE_FLOW)
                            ->where('rel_id', $line['id'])
                            ->where('value', $recommend_config['flow'])
                            ->find();
                if(!empty($optionFlow)){
                    $otherConfig = $optionFlow['other_config'];

                    $data['in_bw'] = $otherConfig['in_bw'] ?? 0;
                    $data['out_bw'] = $otherConfig['out_bw'] ?? 0;
                    $data['traffic_type'] = $otherConfig['traffic_type'] ?? 3;
                    $data['bill_cycle'] = $otherConfig['bill_cycle'] ?? 'last_30days';
                }
            }
        }
        return $data;
    }

    public function updateHidden($param){
        $recommendConfig = $this->find($param['id']);
        if(empty($recommendConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
        }
        if($recommendConfig['hidden'] == $param['hidden']){
            return ['status'=>200, 'msg'=>lang_plugins('success_message')];
        }
        $this->update(['hidden'=>$param['hidden']], ['id'=>$recommendConfig['id']]);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    public function getNameAttr($value){
        if(app('http')->getName() == 'home'){
            $multiLanguage = hook_one('multi_language', [
                'replace' => [
                    'name' => $value,
                ],
            ]);
            if(isset($multiLanguage['name'])){
                $value = $multiLanguage['name'];
            }
        }
        return $value;
    }

    public function getDescriptionAttr($value){
        if(app('http')->getName() == 'home'){
            $multiLanguage = hook_one('multi_language', [
                'replace' => [
                    'description' => $value,
                ],
            ]);
            if(isset($multiLanguage['description'])){
                $value = $multiLanguage['description'];
            }
        }
        return $value;
    }

    public function getSystemDiskTypeAttr($value){
        if(app('http')->getName() == 'home' && !empty($value)){
            $multiLanguage = hook_one('multi_language', [
                'replace' => [
                    'system_disk_type' => $value,
                ],
            ]);
            if(isset($multiLanguage['system_disk_type'])){
                $value = $multiLanguage['system_disk_type'];
            }
        }
        return $value;
    }

    public function getDataDiskTypeAttr($value){
        if(app('http')->getName() == 'home' && !empty($value)){
            $multiLanguage = hook_one('multi_language', [
                'replace' => [
                    'data_disk_type' => $value,
                ],
            ]);
            if(isset($multiLanguage['data_disk_type'])){
                $value = $multiLanguage['data_disk_type'];
            }
        }
        return $value;
    }


}