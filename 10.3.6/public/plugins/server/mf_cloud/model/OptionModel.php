<?php 
namespace server\mf_cloud\model;

use think\Model;
use app\common\model\ProductModel;
use server\mf_cloud\validate\MemoryValidate;
use server\mf_cloud\validate\DiskValidate;
use server\mf_cloud\validate\LineBwValidate;
use server\mf_cloud\validate\LineFlowValidate;
use server\mf_cloud\logic\ToolLogic;

/**
 * @title 配置参数模型
 * @use server\mf_cloud\model\OptionModel
 */
class OptionModel extends Model{

	protected $name = 'module_mf_cloud_option';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'rel_type'      => 'int',
        'rel_id'        => 'int',
        'type'          => 'string',
        'value'         => 'string',
        'min_value'     => 'int',
        'max_value'     => 'int',
        'step'          => 'int',
        'other_config'  => 'string',
        'create_time'   => 'int',
    ];

    protected $json = ['other_config'];

    protected $jsonAssoc  = true;

    // rel_type
    const CPU = 0;
    const MEMORY = 1;
    const LINE_BW = 2;
    const LINE_FLOW = 3;
    const LINE_DEFENCE = 4;
    const LINE_IP = 5;
    const SYSTEM_DISK = 6;
    const DATA_DISK = 7;

    /**
     * 时间 2023-01-31
     * @title CPU配置详情
     * @desc CPU配置详情
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int value - 核心数
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     * @return  string other_config.advanced_cpu - 智能CPU配置规则
     * @return  string other_config.cpu_limit - CPU限制
     */
    public function cpuIndex($id){
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::CPU){
            return (object)[];
        }
        $data = [
            'id' => $option['id'],
            'value' => $option['value'],
            'duration' => $option['duration'],
            'other_config' => $option['other_config'],
        ];
        return $data;
    }

    /**
     * 时间 2023-01-31
     * @title 内存配置详情
     * @desc 内存配置详情
     * @author hh
     * @version v1
     * @param   int id - 配置ID require
     * @return  int id - 配置ID
     * @return  string type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int value - 内存
     * @return  int min_value - 最小值
     * @return  int max_value - 最大值
     * @return  int step - 最小变化值
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     */
    public function memoryIndex($id){
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::MEMORY){
            return (object)[];
        }
        $data = [
            'id' => $option['id'],
            'type' => $option['type'],
            'value' => $option['value'],
            'min_value' => $option['min_value'],
            'max_value' => $option['max_value'],
            'step' => $option['step'],
            'duration' => $option['duration'],
        ];
        return $data;
    }

    /**
     * 时间 2023-01-31
     * @title 线路带宽配置详情
     * @desc 线路带宽配置详情
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int value - 带宽
     * @return  int min_value - 最小值
     * @return  int max_value - 最大值
     * @return  int step - 最小变化值
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     * @return  string other_config.in_bw - 流入带宽
     * @return  string other_config.advanced_bw - 智能带宽配置规则
     */
    public function lineBwIndex($id){
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::LINE_BW){
            return (object)[];
        }
        $data = [
            'id' => $option['id'],
            'type' => $option['type'],
            'value' => $option['value'],
            'min_value' => $option['min_value'],
            'max_value' => $option['max_value'],
            'step' => $option['step'],
            'duration' => $option['duration'],
            'other_config' => $option['other_config'],
        ];
        return $data;
    }

    /**
     * 时间 2023-01-31
     * @title 线路流量配置详情
     * @desc 线路流量配置详情
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int value - 流量
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     * @return  int other_config.in_bw - 入站带宽
     * @return  int other_config.out_bw - 出站带宽
     * @return  string other_config.traffic_type - 计费方向(1=进,2=出,3=进+出)
     * @return  string other_config.bill_cycle - 计费周期(month=自然月,last_30days=购买日循环)
     */
    public function lineFlowIndex($id){
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::LINE_FLOW){
            return (object)[];
        }
        $data = [
            'id' => $option['id'],
            'value' => $option['value'],
            'duration' => $option['duration'],
            'other_config' => $option['other_config'],
        ];
        return $data;
    }

    /**
     * 时间 2023-01-31
     * @title 线路防护配置详情
     * @desc 线路防护配置详情
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int value - 流量
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     */
    public function lineDefenceIndex($id){
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::LINE_DEFENCE){
            return (object)[];
        }
        $data = [
            'id' => $option['id'],
            'value' => $option['value'],
            'duration' => $option['duration'],
        ];
        return $data;
    }

    /**
     * 时间 2023-01-31
     * @title 线路IP配置详情
     * @desc 线路IP配置详情
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  int value - IP数量
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     */
    public function lineIpIndex($id){
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::LINE_IP){
            return (object)[];
        }
        $data = [
            'id' => $option['id'],
            'value' => $option['value'],
            'duration' => $option['duration'],
        ];
        return $data;
    }

    /**
     * 时间 2023-01-31
     * @title 磁盘配置详情
     * @desc 磁盘配置详情
     * @author hh
     * @version v1
     * @param   int id - 配置ID require
     * @return  int id - 配置ID
     * @return  string type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  int value - 容量
     * @return  int min_value - 最小值
     * @return  int max_value - 最大值
     * @return  int step - 最小变化值
     * @return  int duration[].id - 周期ID
     * @return  string duration[].name - 周期名称
     * @return  string duration[].price - 价格
     * @return  string other_config.disk_type - 磁盘类型
     */
    public function diskIndex($id, $rel_type){
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != $rel_type){
            return (object)[];
        }
        $data = [
            'id' => $option['id'],
            'type' => $option['type'],
            'value' => $option['value'],
            'min_value' => $option['min_value'],
            'max_value' => $option['max_value'],
            'step' => $option['step'],
            'duration' => $option['duration'],
            'other_config' => $option['other_config'],
        ];
        return $data;
    }

    /**
     * 时间 2023-02-08
     * @title 获取磁盘类型
     * @desc 获取磁盘类型
     * @author hh
     * @version v1
     * @param  int product_id - 商品ID require
     */
    public function getDiskType($param){
        $list = [];
        $where = [];

        if(isset($param['product_id']) && !empty($param['product_id'])){
            $where[] = ['product_id', '=', $param['product_id']];
        }
        $where[] = ['rel_type', '=', $param['rel_type']];

        $option = $this->where($where)->column('other_config');

        foreach($option as $v){
            // $otherConfig = json_decode($v, true);
            $list[] = $v['other_config']['disk_type'] ?? '';
        }
        $list = array_unique($list);

        $data = [];
        foreach($list as $v){
            $data[] = [
                'name' => $v === '' ? '无' : $v,
                'value' => $v,
            ];
        }
        return ['list'=>$data];
    }

    /**
     * 时间 2023-02-02
     * @title 添加配置
     * @desc 添加配置
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   string type - 配置方式(radio=单选,step=阶梯,total=总量) require
     * @param   int rel_type - 配置类型(0=CPU配置,1=内存配置,2=线路带宽配置,3=线路流量配置,4=线路防护配置,5=线路附加IP,6=系统盘,7=数据盘) require
     * @param   int rel_id - 关联ID
     * @param   int value - 值
     * @param   int min_value - 最小值
     * @param   int max_value - 最大值
     * @param   int step - 步长
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格) require
     * @param   object other_config - 其他配置
     * @return  int id - 配置ID
     */
    public function optionCreate($param){
        if(in_array($param['rel_type'], [OptionModel::CPU,OptionModel::MEMORY,OptionModel::SYSTEM_DISK,OptionModel::DATA_DISK])){
            $ProductModel = ProductModel::find($param['product_id']);
            if(empty($ProductModel)){
                return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
            }
            if($ProductModel->getModule() != 'mf_cloud'){
                return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
            }
            $productId = $ProductModel['id'];

            $param['rel_id'] = 0;
        }else if(in_array($param['rel_type'], [OptionModel::LINE_BW,OptionModel::LINE_FLOW,OptionModel::LINE_DEFENCE,OptionModel::LINE_IP])){
            $line = LineModel::find($param['rel_id']);
            if(empty($line)){
                return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
            }
            $dataCenter = DataCenterModel::find($line['data_center_id']);
            if(empty($dataCenter)){
                return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
            }
            $productId = $dataCenter['product_id'];

            $param['rel_id'] = $line['id'];
        }
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$productId]);
        $config = $config['data'];

        // 验证周期价格
        $duration = DurationModel::where('product_id', $productId)->column('id');

        $this->startTrans();
        try{
            $type = 'radio';
            $insert = [
                'product_id'        => $productId,
                'rel_type'          => $param['rel_type'],
                'rel_id'            => $param['rel_id'],
                'other_config'      => json_encode([]),
                'create_time'       => time(),
            ];

            if($param['rel_type'] == OptionModel::CPU){
                if($config['type'] == 'hyperv'){
                    $insert['other_config'] = json_encode([
                        'advanced_cpu'  => '',
                        'cpu_limit'     => $param['other_config']['cpu_limit'] ?? '',
                    ]);
                }else{
                    $insert['other_config'] = json_encode([
                        'advanced_cpu'  => $param['other_config']['advanced_cpu'] ?? '',
                        'cpu_limit'     => $param['other_config']['cpu_limit'] ?? '',
                    ]);
                }
            }else if($param['rel_type'] == OptionModel::MEMORY){
                // 获取当前类型
                $type = $this
                    ->where('product_id', $productId)
                    ->where('rel_type', $param['rel_type'])
                    ->lock(true)
                    ->value('type');

                $noRaw = false;
                if(empty($type)){
                    $noRaw = true;
                    $type = $param['type'];
                }

                // 先放置在这点
                $MemoryValidate = new MemoryValidate();
                if($type == 'radio'){
                    if (!$MemoryValidate->scene('radio')->check($param)){
                        throw new \Exception(lang_plugins($MemoryValidate->getError()));
                    }
                }else{
                    if (!$MemoryValidate->scene('step')->check($param)){
                        throw new \Exception(lang_plugins($MemoryValidate->getError()));
                    }
                }
                // 没有内存时可以保存单位
                if($noRaw && isset($param['memory_unit']) && !empty($param['memory_unit'])){
                    ConfigModel::where('product_id', $productId)->update([
                        'memory_unit' => strtoupper($param['memory_unit']),
                    ]);
                }
            }else if($param['rel_type'] == OptionModel::LINE_BW){
                if($line['bill_type'] != 'bw'){
                    throw new \Exception(lang_plugins('mf_cloud_line_is_not_bw_cannot_add_bw_option'));
                }
                $type = $this
                    ->where('product_id', $productId)
                    ->where('rel_type', $param['rel_type'])
                    ->where('rel_id', $param['rel_id'])
                    ->lock(true)
                    ->value('type');
                if(empty($type)){
                    $type = $param['type'];
                }

                // 先放置在这点
                $LineBwValidate = new LineBwValidate();
                if($type == 'radio'){
                    if (!$LineBwValidate->scene('radio')->check($param)){
                        throw new \Exception(lang_plugins($LineBwValidate->getError()));
                    }
                }else{
                    if (!$LineBwValidate->scene('step')->check($param)){
                        throw new \Exception(lang_plugins($LineBwValidate->getError()));
                    }
                }
                if($config['type'] == 'hyperv'){
                    $insert['other_config'] = json_encode([
                        'in_bw'         => '',
                        'advanced_bw'   => $param['other_config']['advanced_bw'] ?? '',
                    ]);
                }else{
                    $insert['other_config'] = json_encode([
                        'in_bw'         => $param['other_config']['in_bw'] ?? '',
                        'advanced_bw'   => $param['other_config']['advanced_bw'] ?? '',
                    ]);
                }
            }else if($param['rel_type'] == OptionModel::LINE_FLOW){
                if($line['bill_type'] != 'flow'){
                    throw new \Exception(lang_plugins('mf_cloud_line_is_not_flow_cannot_add_flow_option'));
                }
                $insert['other_config'] = json_encode([
                    'in_bw' => (int)$param['other_config']['in_bw'],
                    'out_bw' => (int)$param['other_config']['out_bw'],
                    'traffic_type' => (int)$param['other_config']['traffic_type'],
                    'bill_cycle' => $param['other_config']['bill_cycle'] ?? 'month',
                ]);
            }else if($param['rel_type'] == OptionModel::LINE_DEFENCE){

            }else if($param['rel_type'] == OptionModel::LINE_IP){

            }else if($param['rel_type'] == OptionModel::SYSTEM_DISK){
                $type = $this
                    ->where('product_id', $productId)
                    ->where('rel_type', $param['rel_type'])
                    ->lock(true)
                    ->value('type');
                if(empty($type)){
                    $type = $param['type'];
                }

                // 先放置在这点
                $diskValidate = new diskValidate();
                if($type == 'radio'){
                    if (!$diskValidate->scene('radio')->check($param)){
                        throw new \Exception(lang_plugins($diskValidate->getError()));
                    }
                }else{
                    if (!$diskValidate->scene('step')->check($param)){
                        throw new \Exception(lang_plugins($diskValidate->getError()));
                    }
                }
                $insert['other_config'] = json_encode([
                    'disk_type' => $param['other_config']['disk_type'] ?? '',
                    'store_id'  => $param['other_config']['store_id'] ?? '',
                ]);
            }else if($param['rel_type'] == OptionModel::DATA_DISK){
                $type = $this
                    ->where('product_id', $productId)
                    ->where('rel_type', $param['rel_type'])
                    ->lock(true)
                    ->value('type');
                if(empty($type)){
                    $type = $param['type'];
                }
                // 先放置在这点
                $diskValidate = new diskValidate();
                if($type == 'radio'){
                    if (!$diskValidate->scene('radio')->check($param)){
                        throw new \Exception(lang_plugins($diskValidate->getError()));
                    }
                }else{
                    if (!$diskValidate->scene('step')->check($param)){
                        throw new \Exception(lang_plugins($diskValidate->getError()));
                    }
                }
                $insert['other_config'] = json_encode([
                    'disk_type' => $param['other_config']['disk_type'] ?? '',
                    'store_id'  => $param['other_config']['store_id'] ?? '',
                ]);
            }
            $insert['type'] = $type;
            if($type == 'radio'){
                
                $whereSame = [];
                $whereSame[] = ['product_id', '=', $productId];
                $whereSame[] = ['rel_type', '=', $param['rel_type']];
                $whereSame[] = ['rel_id', '=', $param['rel_id']];
                $whereSame[] = ['value', '=', $param['value']];

                if(in_array($param['rel_type'], [OptionModel::SYSTEM_DISK, OptionModel::DATA_DISK])){
                    $whereSame[] = ['other_config', 'LIKE', rtrim(str_replace('\\', '\\\\', json_encode(['disk_type'=>$param['other_config']['disk_type'] ?? ''])), '}').'%'];
                }
                $same = $this
                        ->where($whereSame)
                        ->find();
                if(!empty($same)){
                    throw new \Exception(lang_plugins('mf_cloud_already_add_the_same_option'));
                }

                $insert['value'] = $param['value'];
            }else{

                $whereSame = [];
                $whereSame[] = ['product_id', '=', $productId];
                $whereSame[] = ['rel_type', '=', $param['rel_type']];
                $whereSame[] = ['rel_id', '=', $param['rel_id']];
                $whereSame[] = ['min_value', '<=', $param['max_value']];
                $whereSame[] = ['max_value', '>=', $param['min_value']];

                if(in_array($param['rel_type'], [OptionModel::SYSTEM_DISK, OptionModel::DATA_DISK])){
                    $whereSame[] = ['other_config', 'LIKE', rtrim(str_replace('\\', '\\\\', json_encode(['disk_type'=>$param['other_config']['disk_type'] ?? ''])), '}').'%'];
                }

                // 范围是否交叉
                $intersect = $this
                    ->where($whereSame)
                    ->find();
                if(!empty($intersect)){
                    throw new \Exception(lang_plugins('mf_cloud_option_intersect'));
                }
                $insert['min_value'] = $param['min_value'];
                $insert['max_value'] = $param['max_value'];
                $insert['step'] = 1; //$param['step']; 不能设置步长
            }
            $option = $this->create($insert);

            $priceArr = [];
            foreach($duration as $v){
                if(isset($param['price'][$v])){
                    $priceArr[] = [
                        'product_id'    => $productId,
                        'rel_type'      => PriceModel::REL_TYPE_OPTION,
                        'rel_id'        => $option->id,
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
            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        $optionType = [
            lang_plugins('mf_cloud_option_0'),
            lang_plugins('mf_cloud_option_1'),
            lang_plugins('mf_cloud_option_2'),
            lang_plugins('mf_cloud_option_3'),
            lang_plugins('mf_cloud_option_4'),
            lang_plugins('mf_cloud_option_5'),
            lang_plugins('mf_cloud_option_6'),
            lang_plugins('mf_cloud_option_7'),
        ];

        $nameType = [
            lang_plugins('mf_cloud_option_value_0'),
            lang_plugins('mf_cloud_option_value_1'),
            lang_plugins('mf_cloud_option_value_2'),
            lang_plugins('mf_cloud_option_value_3'),
            lang_plugins('mf_cloud_option_value_4'),
            lang_plugins('mf_cloud_option_value_5'),
            lang_plugins('mf_cloud_option_value_6'),
            lang_plugins('mf_cloud_option_value_7'),
        ];

        $description = lang_plugins('log_mf_cloud_add_option_success', [
            '{option}' => $optionType[ $param['rel_type'] ],
            '{name}' => $nameType[ $param['rel_type'] ],
            '{detail}' => $type == 'radio' ? $param['value'] : $param['min_value'].'-'.$param['max_value'],
        ]);
        active_log($description, 'product', $productId);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$option->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-01-31
     * @title 获取配置列表
     * @desc 获取配置列表
     * @author hh
     * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int orderby - 排序
     * @param   int sort - 
     * @param   int product_id - 商品ID
     * @param   int rel_type - 配置类型
     * @param   int rel_id - 关联ID
     */
    public function optionList($param, $field = null){
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','value'])){
            $param['orderby'] = 'value,min_value';
        }

        $where = [];
        if(!empty($param['product_id'])){
            $where[] = ['product_id', '=', $param['product_id']];
        }
        $where[] = ['rel_type', '=', $param['rel_type']];
        if(isset($param['rel_id']) && $param['rel_id']>0){
            $where[] = ['rel_id', '=', $param['rel_id']];
        }

        $field = $field.',product_id' ?? 'id,product_id,type,value,min_value,max_value,step,other_config';

        $list = $this
                ->field($field)
                ->where($where)
                ->page($param['page'], $param['limit'])
                ->order($param['orderby'], 'asc')
                ->select()
                ->toArray();
    
        $count = $this
                ->where($where)
                ->count();
        
        // 计算列表价格
        if(!empty($list)){
            $id = array_column($list, 'id');

            // 时间最短的周期
            $firstDuration = DurationModel::field('id,name,num,unit')->where('product_id', $list[0]['product_id'])->orderRaw('field(unit, "hour","day","month")')->order('num', 'asc')->find();
            if(!empty($firstDuration)){
                $price = PriceModel::alias('p')
                    ->field('p.rel_id,p.price,o.other_config,o.rel_type')
                    ->where('p.product_id', $list[0]['product_id'])
                    ->where('p.rel_type', PriceModel::REL_TYPE_OPTION)
                    ->whereIn('p.rel_id', $id)
                    ->where('p.duration_id', $firstDuration['id'])
                    ->leftJoin('module_mf_cloud_option o', 'p.rel_id=o.id')
                    ->select()
                    ->toArray();

                $priceArr = [];
                foreach($price as $k=>$v){
                    $priceArr[ $v['rel_id'] ] = $v;
                }

                foreach($list as $k=>$v){
                    if(isset($v['type'])){
                        if($v['type'] == 'step'){
                            $disk_type = null;
                            if($param['rel_type'] == OptionModel::SYSTEM_DISK || $param['rel_type'] == OptionModel::DATA_DISK){
                                $disk_type = $v['other_config']['disk_type'] ?? '';
                            }
                            $list[$k]['price'] = $priceArr[$v['id']]['price'] ?? 0;
                            foreach($list as $kk=>$vv){
                                // 范围内的阶梯
                                if($v['min_value'] > $vv['max_value']){
                                    if($param['rel_type'] == OptionModel::SYSTEM_DISK || $param['rel_type'] == OptionModel::DATA_DISK){
                                        $vv['other_config'] = is_array($vv['other_config']) ? $vv['other_config'] : json_decode($vv['other_config'], true);
                                        if($disk_type !== $vv['other_config']['disk_type'] ?? ''){
                                            continue;
                                        }
                                    }
                                    $list[$k]['price'] = bcadd($list[$k]['price'], bcmul($priceArr[$vv['id']]['price'] ?? 0, $vv['max_value']-$vv['min_value']+1));
                                }
                            }
                        }else if($v['type'] == 'total'){
                            $list[$k]['price'] = isset($priceArr[$v['id']]['price']) ? bcmul($priceArr[$v['id']]['price'], $v['min_value']) : '0.00';
                        }else{
                            $list[$k]['price'] = $priceArr[$v['id']]['price'] ?? '0.00';
                        }
                    }else{
                        // 单选
                        $list[$k]['price'] = $priceArr[$v['id']]['price'] ?? '0.00';
                    }
                    $list[$k]['duration'] = $firstDuration['name'];

                    // if(isset($v['other_config'])){
                    //     $list[$k]['other_config'] = json_decode($v['other_config'], true);
                    // }
                }
            }else{
                foreach($list as $k=>$v){
                    $list[$k]['price'] = '0.00';
                    $list[$k]['duration'] = '';
                }
            }
        }
        return ['list'=>$list, 'count'=>$count];
    }

    /**
     * 时间 2023-02-02
     * @title 修改配置
     * @desc 修改配置
     * @author hh
     * @version v1
     * @param   int id - 配置ID require
     * @param   int value - 值
     * @param   int min_value - 最小值
     * @param   int max_value - 最大值
     * @param   int step - 步长
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格) require
     * @param   object other_config - 其他配置
     * @return  int id - 配置ID
     */
    public function optionUpdate($param){
        $option = $this->find($param['id']);
        if(empty($option)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_option_not_found')];
        }
        $productId = $option['product_id'];
        $param['rel_type'] = $option['rel_type'];
        $param['rel_id'] = $option['rel_id'];
        $oldOtherConfig = $option['other_config'];

        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$productId]);
        $config = $config['data'];

        // 验证周期价格
        $duration = DurationModel::field('id,name')->where('product_id', $productId)->select();

        $oldPrice = PriceModel::field('duration_id,price')->where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_OPTION)->where('rel_id', $option->id)->select()->toArray();
        $oldPrice = array_column($oldPrice, 'price', 'duration_id');
        
        $this->startTrans();
        try{
            $type = $option['type'];

            $update = [];
            if($param['rel_type'] == OptionModel::CPU){
                if($config['type'] == 'hyperv'){
                    $update['other_config'] = json_encode([
                        'advanced_cpu'  => '',
                        'cpu_limit'     => $param['other_config']['cpu_limit'] ?? '',
                    ]);
                }else{
                    $update['other_config'] = json_encode([
                        'advanced_cpu'  => $param['other_config']['advanced_cpu'] ?? '',
                        'cpu_limit'     => $param['other_config']['cpu_limit'] ?? '',
                    ]);
                }
            }else if($param['rel_type'] == OptionModel::MEMORY){
                
            }else if($param['rel_type'] == OptionModel::LINE_BW){
                if($config['type'] == 'hyperv'){
                    $update['other_config'] = json_encode([
                        'in_bw'         => '',
                        'advanced_bw'   => '',
                    ]);
                }else{
                    $update['other_config'] = json_encode([
                        'in_bw'         => $param['other_config']['in_bw'] ?? '',
                        'advanced_bw'   => $param['other_config']['advanced_bw'] ?? '',
                    ]);
                }
            }else if($param['rel_type'] == OptionModel::LINE_FLOW){
                $update['other_config'] = json_encode([
                    'in_bw' => $param['other_config']['in_bw'] ?? 0,
                    'out_bw' => $param['other_config']['out_bw'] ?? 0,
                    'traffic_type' => $param['other_config']['traffic_type'] ?? 1,
                    'bill_cycle' => $param['other_config']['bill_cycle'] ?? 'month',
                ]);
            }else if($param['rel_type'] == OptionModel::LINE_DEFENCE){

            }else if($param['rel_type'] == OptionModel::LINE_IP){

            }else if($param['rel_type'] == OptionModel::SYSTEM_DISK){
                $type = $this
                    ->where('product_id', $productId)
                    ->where('rel_type', $param['rel_type'])
                    ->lock(true)
                    ->value('type');
                if(empty($type)){
                    $type = $param['type'];
                }
                $update['other_config'] = json_encode([
                    'disk_type' => $param['other_config']['disk_type'] ?? '',
                    'store_id'  => $param['other_config']['store_id'] ?? '',
                ]);
            }else if($param['rel_type'] == OptionModel::DATA_DISK){
                $type = $this
                    ->where('product_id', $productId)
                    ->where('rel_type', $param['rel_type'])
                    ->lock(true)
                    ->value('type');
                if(empty($type)){
                    $type = $param['type'];
                }
                $update['other_config'] = json_encode([
                    'disk_type' => $param['other_config']['disk_type'] ?? '',
                    'store_id'  => $param['other_config']['store_id'] ?? '',
                ]);
            }
            // 类型不能修改了
            // $insert['type'] = $type;
            if($type == 'radio'){

                $whereSame = [];
                $whereSame[] = ['product_id', '=', $productId];
                $whereSame[] = ['rel_type', '=', $param['rel_type']];
                $whereSame[] = ['rel_id', '=', $param['rel_id']];
                $whereSame[] = ['value', '=', $param['value']];
                $whereSame[] = ['id', '<>', $param['id']];

                if(in_array($param['rel_type'], [OptionModel::SYSTEM_DISK, OptionModel::DATA_DISK])){
                    $whereSame[] = ['other_config', 'LIKE', rtrim(str_replace('\\', '\\\\', json_encode(['disk_type'=>$param['other_config']['disk_type'] ?? ''])), '}').'%'];
                }
                // 必须是数字
                $same = $this
                        ->where($whereSame)
                        ->find();
                if(!empty($same)){
                    throw new \Exception(lang_plugins('mf_cloud_already_add_the_same_option'));
                }

                $update['value'] = $param['value'];
            }else{

                $whereSame = [];
                $whereSame[] = ['product_id', '=', $productId];
                $whereSame[] = ['rel_type', '=', $param['rel_type']];
                $whereSame[] = ['rel_id', '=', $param['rel_id']];
                $whereSame[] = ['min_value', '<=', $param['max_value']];
                $whereSame[] = ['max_value', '>=', $param['min_value']];
                $whereSame[] = ['id', '<>', $param['id']];

                if(in_array($param['rel_type'], [OptionModel::SYSTEM_DISK, OptionModel::DATA_DISK])){
                    $whereSame[] = ['other_config', 'LIKE', rtrim(str_replace('\\', '\\\\', json_encode(['disk_type'=>$param['other_config']['disk_type'] ?? ''])), '}').'%'];
                }

                // 范围是否交叉
                $intersect = $this
                    ->where($whereSame)
                    ->find();
                if(!empty($intersect)){
                    throw new \Exception(lang_plugins('mf_cloud_option_intersect'));
                }
                $update['min_value'] = $param['min_value'];
                $update['max_value'] = $param['max_value'];
                $update['step'] = $param['step'];
            }
            $this->update($update, ['id'=>$option->id]);

            $priceArr = [];
            foreach($duration as $v){
                if(isset($param['price'][$v['id']])){
                    $priceArr[] = [
                        'product_id'    => $productId,
                        'rel_type'      => PriceModel::REL_TYPE_OPTION,
                        'rel_id'        => $option->id,
                        'duration_id'   => $v['id'],
                        'price'         => $param['price'][$v['id']],
                    ];
                }
            }

            PriceModel::where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_OPTION)->where('rel_id', $option->id)->delete();
            if(!empty($priceArr)){
                $PriceModel = new PriceModel();
                $PriceModel->insertAll($priceArr);
            }
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage() ];
        }

        $optionType = [
            lang_plugins('mf_cloud_option_0'),
            lang_plugins('mf_cloud_option_1'),
            lang_plugins('mf_cloud_option_2'),
            lang_plugins('mf_cloud_option_3'),
            lang_plugins('mf_cloud_option_4'),
            lang_plugins('mf_cloud_option_5'),
            lang_plugins('mf_cloud_option_6'),
            lang_plugins('mf_cloud_option_7'),
        ];

        $nameType = [
            lang_plugins('mf_cloud_option_value_0'),
            lang_plugins('mf_cloud_option_value_1'),
            lang_plugins('mf_cloud_option_value_2'),
            lang_plugins('mf_cloud_option_value_3'),
            lang_plugins('mf_cloud_option_value_4'),
            lang_plugins('mf_cloud_option_value_5'),
            lang_plugins('mf_cloud_option_value_6'),
            lang_plugins('mf_cloud_option_value_7'),
        ];

        $trafficType = [
            '',
            lang_plugins('mf_cloud_option_traffic_type_in'),
            lang_plugins('mf_cloud_option_traffic_type_out'),
            lang_plugins('mf_cloud_option_traffic_type_all'),
        ];

        $billCycle = [
            'month' => lang_plugins('mf_cloud_option_bill_cycle_month'),
            'last_30days' => lang_plugins('mf_cloud_option_bill_cycle_last_30days'),
        ];

        $des = [
            'value' => $nameType[ $param['rel_type'] ],
        ];

        $old = [
            'value' => $type == 'radio' ? $option['value'] : $option['min_value'].'-'.$option['max_value']
        ];

        $new = [
            'value' => $type == 'radio' ? $param['value'] : $param['min_value'].'-'.$param['max_value']
        ];
        if($option['rel_type'] == OptionModel::CPU){
            $des['advanced_cpu'] = lang_plugins('mf_cloud_advanced_cpu');
            $des['cpu_limit'] = lang_plugins('mf_cloud_cpu_limit');
            // $des['ipv6_num'] = lang_plugins('mf_cloud_ipv6_num');

            $old['advanced_cpu'] = $oldOtherConfig['advanced_cpu'] ?? '';
            $old['cpu_limit'] = $oldOtherConfig['cpu_limit'] ?? '';
            // $old['ipv6_num'] = $oldOtherConfig['ipv6_num'] ?? '';

            $new['advanced_cpu'] = $param['other_config']['advanced_cpu'] ?? '';
            $new['cpu_limit'] = $param['other_config']['cpu_limit'] ?? '';
            // $new['ipv6_num'] = $param['other_config']['ipv6_num'] ?? '';
        }else if($option['rel_type'] == OptionModel::LINE_BW){
            $des['in_bw'] = lang_plugins('mf_cloud_line_bw_in_bw');
            $des['advanced_bw'] = lang_plugins('mf_cloud_advanced_bw');

            $old['in_bw'] = $oldOtherConfig['in_bw'] ?? '';
            $old['advanced_bw'] = $oldOtherConfig['advanced_bw'] ?? '';

            $new['in_bw'] = $param['other_config']['in_bw'] ?? '';
            $new['advanced_bw'] = $param['other_config']['advanced_bw'] ?? '';
        }else if($option['rel_type'] == OptionModel::LINE_FLOW){
            $des['in_bw'] = lang_plugins('mf_cloud_line_flow_in_bw');
            $des['out_bw'] = lang_plugins('mf_cloud_line_flow_out_bw');
            $des['traffic_type'] = lang_plugins('mf_cloud_line_flow_traffic_type');
            $des['bill_cycle'] = lang_plugins('mf_cloud_line_flow_bill_cycle');

            $old['in_bw'] = $oldOtherConfig['in_bw'] ?? '';
            $old['out_bw'] = $oldOtherConfig['out_bw'] ?? '';
            $old['traffic_type'] = $trafficType[ $oldOtherConfig['traffic_type'] ?? 1];
            $old['bill_cycle'] = $billCycle[ $oldOtherConfig['bill_cycle'] ?? 'month' ];

            $new['in_bw'] = $param['other_config']['in_bw'] ?? '';
            $new['out_bw'] = $param['other_config']['out_bw'] ?? '';
            $new['traffic_type'] = $trafficType[ $param['other_config']['traffic_type'] ?? 1];
            $new['bill_cycle'] = $billCycle[ $param['other_config']['bill_cycle'] ?? 'month' ];
        }else if($option['rel_type'] == OptionModel::SYSTEM_DISK || $option['rel_type'] == OptionModel::DATA_DISK){
            $des['disk_type'] = lang_plugins('mf_cloud_disk_type');
            $des['store_id'] = lang_plugins('store_id');

            $old['disk_type'] = $oldOtherConfig['disk_type'] ?? '';
            $old['store_id'] = $oldOtherConfig['store_id'] ?? '';

            $new['disk_type'] = $param['other_config']['disk_type'] ?? '';
            $new['store_id'] = $param['other_config']['store_id'] ?? '';
        }
        // 每个周期的价格对比
        foreach($duration as $v){
            $des[ 'duration_'.$v['id'] ] = $v['name'].lang_plugins('price');
            $old[ 'duration_'.$v['id'] ] = $oldPrice[ $v['id'] ] ?? lang_plugins('null');
            $new[ 'duration_'.$v['id'] ] = $param['price'][$v['id']] ?? lang_plugins('null');
        }

        $description = ToolLogic::createEditLog($old, $new, $des);
        if(!empty($description)){
            $description = lang_plugins('log_mf_cloud_modify_option_success', [
                '{option}' => $optionType[ $param['rel_type'] ],
                '{detail}' => $description,
            ]);
            active_log($description, 'product', $productId);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success')
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
    public function optionDelete($id, $rel_type = null){
        $option = $this->find($id);
        if(empty($option)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_option_not_found')];
        }
        if(isset($rel_type) && $option['rel_type'] != $rel_type){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_option_not_found')];
        }
        $productId = $option['product_id'];
        $otherConfig = $option['other_config'];

        $this->startTrans();
        try{
            $this->where('id', $id)->delete();
            PriceModel::where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_OPTION)->where('rel_id', $id)->delete();

            // 删除完了线路部分配置,自动关闭开关
            if(in_array($option['rel_type'], [OptionModel::LINE_DEFENCE, OptionModel::LINE_IP])){
                // 是否还有对应配置,没有自动关闭开关
                $other = $this->where('product_id', $productId)->where('rel_type', $option['rel_type'])->where('rel_id', $option['rel_id'])->find();
                if(empty($other)){
                    if($option['rel_type'] == OptionModel::LINE_DEFENCE){
                        LineModel::where('id', $option['rel_id'])->update(['defence_enable'=>0]);
                    }else{
                        LineModel::where('id', $option['rel_id'])->update(['ip_enable'=>0]);
                    }
                }
            }
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('delete_success')];
        }

        $optionType = [
            lang_plugins('mf_cloud_option_0'),
            lang_plugins('mf_cloud_option_1'),
            lang_plugins('mf_cloud_option_2'),
            lang_plugins('mf_cloud_option_3'),
            lang_plugins('mf_cloud_option_4'),
            lang_plugins('mf_cloud_option_5'),
            lang_plugins('mf_cloud_option_6'),
            lang_plugins('mf_cloud_option_7'),
        ];

        $nameType = [
            lang_plugins('mf_cloud_option_value_0'),
            lang_plugins('mf_cloud_option_value_1'),
            lang_plugins('mf_cloud_option_value_2'),
            lang_plugins('mf_cloud_option_value_3'),
            lang_plugins('mf_cloud_option_value_4'),
            lang_plugins('mf_cloud_option_value_5'),
            lang_plugins('mf_cloud_option_value_6'),
            lang_plugins('mf_cloud_option_value_7'),
        ];

        $description = lang_plugins('log_mf_cloud_delete_option_success', [
            '{option}' => $optionType[ $option['rel_type'] ],
            '{name}' => $nameType[ $option['rel_type'] ],
            '{detail}' => $option['type'] == 'radio' ? $option['value'] : $option['min_value'].'-'.$option['max_value'],
        ]);
        active_log($description, 'product', $option['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-01-31
     * @title 
     * @desc 
     * @author hh
     * @version v1
     * @param   string x             - x
     * @return  [type] [description]
     */
    public function optionIndex($id): array{
        $option = $this
                ->field('id,product_id,rel_type,rel_id,type,value,min_value,max_value,step,other_config')
                ->find($id);
        if(empty($option)){
            return [];
        }

        $duration = DurationModel::alias('d')
                    ->field('d.id,d.name,p.price')
                    ->leftJoin('module_mf_cloud_price p', 'p.product_id='.$option['product_id'].' AND p.rel_type='.PriceModel::REL_TYPE_OPTION.' AND p.rel_id='.$id.' AND d.id=p.duration_id')
                    ->where('d.product_id', $option['product_id'])
                    ->withAttr('price', function($val){
                        return $val ?? '';
                    })
                    ->select()
                    ->toArray();

        $option = $option->toArray();
        $option['duration'] = $duration;
        // $option['other_config'] = json_decode($option['other_config'], true);

        return $option;
    }

    /**
     * 时间 2023-02-06
     * @title 匹配对应配置所有周期价格
     * @desc 匹配对应配置所有周期价格
     * @author hh
     * @version v1
     * @param   int  $productId - 商品ID require
     * @param   int  $relType   - 配置类型 require
     * @param   int  $relId     - 线路ID require
     * @param   int  $value     - 当前值 require
     */
    public function optionDurationPrice($productId, $relType, $relId = 0, $value = 0, $diskType = NULL){
        $data = [];
        $match = false;

        $whereOption = [];
        $whereOption[] = ['product_id', '=', $productId];
        $whereOption[] = ['rel_type', '=', $relType];
        $whereOption[] = ['rel_id', '=', $relId];

        if(!is_null($diskType)){
            $whereOption[] = ['other_config', 'LIKE', rtrim(str_replace('\\', '\\\\', json_encode(['disk_type'=>$diskType])), '}').'%'];
        }

        $type = OptionModel::where($whereOption)->value('type');
        if($type == 'radio'){
            $whereOption[] = ['value', '=', $value];

            $optionId = OptionModel::where($whereOption)->value('id');
            if(!empty($optionId)){
                $match = true;
                $data = PriceModel::field('duration_id,price')->where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_OPTION)->where('rel_id', $optionId)->select()->toArray();
                $data = array_column($data, 'price', 'duration_id');
            }
        }else if($type == 'step'){
            $whereOption[] = ['min_value', '<=', $value];
            $whereOption[] = ['max_value', '>=', $value];

            $option = OptionModel::where($whereOption)->find();
            if(!empty($option) && (($value - $option['min_value']) % $option['step'] == 0)){
                $match = true;

                $wherePrice = [];
                $wherePrice[] = ['o.product_id', '=', $productId];
                $wherePrice[] = ['o.rel_type', '=', $relType];
                $wherePrice[] = ['o.rel_id', '=', $relId];
                $wherePrice[] = ['o.min_value', '<=', $value];
                $wherePrice[] = ['p.rel_type', '=', PriceModel::REL_TYPE_OPTION];

                if(!is_null($diskType)){
                    $wherePrice[] = ['o.other_config', 'LIKE', rtrim(str_replace('\\', '\\\\', json_encode(['disk_type'=>$diskType])), '}').'%' ];
                }

                $price = OptionModel::alias('o')
                        ->field('o.min_value,o.max_value,p.rel_id,p.duration_id,p.price')
                        ->where($wherePrice)
                        ->leftJoin('module_mf_cloud_price p', 'o.id=p.rel_id')
                        ->order('o.min_value', 'asc')
                        ->group('p.rel_id,p.duration_id')
                        ->select();

                foreach($price as $v){
                    if($value > $v['max_value']){
                        $stepPrice = bcmul($v['max_value'] - $v['min_value'] + 1, $v['price']);
                    }else{
                        // 最后一层
                        $stepPrice = bcmul($value - $v['min_value'] + 1, $v['price']);
                    }
                    if(!isset($data[ $v['duration_id'] ])){
                        $data[ $v['duration_id'] ] = $stepPrice;
                    }else{
                        $data[ $v['duration_id'] ] = bcadd($data[ $v['duration_id'] ], $stepPrice);
                    }
                }
            }
        }else if($type == 'total'){
            $whereOption[] = ['min_value', '<=', $value];
            $whereOption[] = ['max_value', '>=', $value];

            $option = OptionModel::where($whereOption)->find();
            if(!empty($option) && (($value - $option['min_value']) % $option['step'] == 0)){
                $match = true;
                $optionId = $option['id'];

                $price = PriceModel::field('duration_id,price')->where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_OPTION)->where('rel_id', $optionId)->select()->toArray();
                foreach($price as $v){
                    $data[ $v['duration_id'] ] = bcmul($v['price'], $value);
                }                
            }
        }

        return ['match'=>$match, 'price'=>$data];
    }

    /**
     * 时间 2023-02-06
     * @title 匹配对应配置某个周期价格
     * @desc 匹配对应配置某个周期价格
     * @author hh
     * @version v1
     * @param   int  $productId - 商品ID require
     * @param   int  $relType   - 配置类型 require
     * @param   int  $relId     - 线路ID require
     * @param   int  $value     - 当前值 require
     */
    public function matchOptionDurationPrice($productId, $relType, $relId = 0, $value = 0, $durationId = 0, $diskType = NULL){
        $match = false;  // 配置是否匹配
        $price = null;   // 价格

        $whereOption = [];
        $whereOption[] = ['product_id', '=', $productId];
        $whereOption[] = ['rel_type', '=', $relType];
        $whereOption[] = ['rel_id', '=', $relId];

        if(!is_null($diskType)){
            $whereOption[] = ['other_config', 'LIKE', rtrim(str_replace('\\', '\\\\', json_encode(['disk_type'=>$diskType])), '}').'%'];
        }

        $type = OptionModel::where($whereOption)->value('type');
        if($type == 'radio'){
            $whereOption[] = ['value', '=', $value];

            $option = OptionModel::where($whereOption)->find();
            if(!empty($option)){
                $match = true;
                $price = PriceModel::where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_OPTION)->where('rel_id', $option['id'])->where('duration_id', $durationId)->value('price');
            }
        }else if($type == 'step'){
            $whereOption[] = ['min_value', '<=', $value];
            $whereOption[] = ['max_value', '>=', $value];

            $option = OptionModel::where($whereOption)->find();
            if(!empty($option) && (($value - $option['min_value']) % $option['step'] == 0)){
                $match = true;

                $wherePrice = [];
                $wherePrice[] = ['o.product_id', '=', $productId];
                $wherePrice[] = ['o.rel_type', '=', $relType];
                $wherePrice[] = ['o.rel_id', '=', $relId];
                $wherePrice[] = ['o.min_value', '<=', $value];
                $wherePrice[] = ['p.rel_type', '=', PriceModel::REL_TYPE_OPTION];

                if(!is_null($diskType)){
                    $wherePrice[] = ['o.other_config', 'LIKE', rtrim(str_replace('\\', '\\\\', json_encode(['disk_type'=>$diskType])), '}').'%'];
                }

                $priceArr = OptionModel::alias('o')
                        ->field('o.min_value,o.max_value,p.rel_id,p.duration_id,p.price')
                        ->where($wherePrice)
                        ->where('p.duration_id', $durationId)
                        ->leftJoin('module_mf_cloud_price p', 'o.id=p.rel_id')
                        ->order('o.min_value', 'asc')
                        ->group('p.rel_id')
                        ->select();

                foreach($priceArr as $v){
                    if(!is_numeric($v['price'])){
                        continue;
                    }
                    if($value > $v['max_value']){
                        $stepPrice = bcmul($v['max_value'] - $v['min_value'] + 1, $v['price']);
                    }else{
                        // 最后一层
                        $stepPrice = bcmul($value - $v['min_value'] + 1, $v['price']);
                    }
                    $price = bcadd($price ?? 0, $stepPrice);
                }
            }
        }else if($type == 'total'){
            $whereOption[] = ['min_value', '<=', $value];
            $whereOption[] = ['max_value', '>=', $value];

            $option = OptionModel::where($whereOption)->find();
            if(!empty($option) && (($value - $option['min_value']) % $option['step'] == 0)){
                $optionId = $option['id'];

                $match = true;
                $price = PriceModel::field('duration_id,price')->where('product_id', $productId)->where('rel_type', PriceModel::REL_TYPE_OPTION)->where('rel_id', $optionId)->where('duration_id', $durationId)->value('price');
                $price = bcmul($price, $value);
            }
        }
        // if($match){
        //     $option['other_config'] = json_decode($option['other_config'], true);
        // }
        return ['match'=>$match, 'price'=>$price, 'option'=>$option ?? [] ];
    }

}