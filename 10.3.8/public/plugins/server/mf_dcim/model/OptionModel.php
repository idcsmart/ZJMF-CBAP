<?php 
namespace server\mf_dcim\model;

use think\Model;
use app\common\model\ProductModel;
use server\mf_dcim\logic\ToolLogic;
use server\mf_dcim\validate\LineBwValidate;
use server\mf_dcim\validate\LineIpValidate;
use server\mf_dcim\validate\LineFlowValidate;

/**
 * @title 配置参数模型
 * @use server\mf_dcim\model\OptionModel
 */
class OptionModel extends Model{

	protected $name = 'module_mf_dcim_option';

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
        'order'         => 'int',
        'other_config'  => 'string',
        'create_time'   => 'int',
    ];

    // rel_type
    const LINE_BW = 2;
    const LINE_FLOW = 3;
    const LINE_DEFENCE = 4;
    const LINE_IP = 5;
    const CPU = 6;
    const MEMORY = 7;
    const DISK = 8;
    const GPU = 9;

    /**
     * 时间 2023-01-31
     * @title 线路带宽配置详情
     * @desc 线路带宽配置详情
     * @author hh
     * @version v1
     * @param   int id - 通用配置ID require
     * @return  int id - 通用配置ID
     * @return  string type - 配置方式(radio=单选,step=阶梯,total=总量)
     * @return  string value - 带宽
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
     * @return  string value - IP数量
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
            'id'        => $option['id'],
            'value'     => $option['value'],
            'duration'  => $option['duration'],
        ];
        return $data;
    }

    public function cpuIndex($id){
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::CPU){
            return (object)[];
        }
        $data = [
            'id' => $option['id'],
            'value' => $option['value'],
            'duration' => $option['duration'],
        ];
        return $data;
    }

    public function memoryIndex($id){
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::MEMORY){
            return (object)[];
        }
        $data = [
            'id'            => $option['id'],
            'value'         => $option['value'],
            'order'         => $option['order'],
            'other_config'  => $option['other_config'],
            'duration'      => $option['duration'],
        ];
        return $data;
    }

    public function diskIndex($id){
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::DISK){
            return (object)[];
        }
        $data = [
            'id'            => $option['id'],
            'value'         => $option['value'],
            'order'         => $option['order'],
            'duration'      => $option['duration'],
        ];
        return $data;
    }

    public function gpuIndex($id){
        $option = $this->optionIndex($id);
        if(empty($option) || $option['rel_type'] != OptionModel::GPU){
            return (object)[];
        }
        $data = [
            'id'            => $option['id'],
            'value'         => $option['value'],
            'order'         => $option['order'],
            'duration'      => $option['duration'],
        ];
        return $data;
    }

    /**
     * 时间 2023-02-02
     * @title 添加配置
     * @desc 添加配置
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   string type - 配置方式(radio=单选,step=阶梯,total=总量) require
     * @param   int rel_type - 配置类型(2=线路带宽配置,3=线路流量配置,4=线路防护配置,5=线路附加IP) require
     * @param   int rel_id - 关联ID
     * @param   string value - 值
     * @param   int min_value - 最小值
     * @param   int max_value - 最大值
     * @param   int step - 步长
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格) require
     * @param   object other_config - 其他配置
     * @return  int id - 配置ID
     */
    public function optionCreate($param){
        if(in_array($param['rel_type'], [OptionModel::LINE_BW,OptionModel::LINE_IP,OptionModel::LINE_DEFENCE,OptionModel::LINE_FLOW])){
            $line = LineModel::find($param['rel_id']);
            if(empty($line)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found')];
            }
            $dataCenter = DataCenterModel::find($line['data_center_id']);
            if(empty($dataCenter)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found')];
            }
            $productId = $dataCenter['product_id'];

            $param['rel_id'] = $line['id'];
        }else{
            $productId = $param['product_id'];
        }
        
        // 验证周期价格
        $duration = DurationModel::where('product_id', $productId)->column('id');

        $this->startTrans();
        try{
            $type = 'radio';
            $insert = [
                'product_id'        => $productId,
                'rel_type'          => $param['rel_type'],
                'rel_id'            => $param['rel_id'],
                'order'             => $param['order'] ?? 0,
                'other_config'      => json_encode([]),
                'create_time'       => time(),
            ];

            if($param['rel_type'] == OptionModel::LINE_BW){
                if($line['bill_type'] != 'bw'){
                    throw new \Exception(lang_plugins('mf_dcim_line_not_bw_cannot_add_bw_rule'));
                }
                $type = $this
                    // ->where('product_id', $productId)
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
                $insert['other_config'] = json_encode([
                    'in_bw' => $param['other_config']['in_bw'] ?? '',
                ]);
            }else if($param['rel_type'] == OptionModel::LINE_FLOW){
                if($line['bill_type'] != 'flow'){
                    throw new \Exception(lang_plugins('mf_dcim_line_not_flow_cannot_add_flow_rule'));
                }
                $insert['other_config'] = json_encode([
                    'in_bw' => (int)$param['other_config']['in_bw'],
                    'out_bw' => (int)$param['other_config']['out_bw'],
                    'bill_cycle' => $param['other_config']['bill_cycle'] ?? 'month',
                ]);
            }else if($param['rel_type'] == OptionModel::LINE_DEFENCE){

            }else if($param['rel_type'] == OptionModel::LINE_IP){
                $LineIpValidate = new LineIpValidate();
                if (!$LineIpValidate->scene('option_create')->check($param)){
                    throw new \Exception(lang_plugins($LineIpValidate->getError()));
                }
            }else if($param['rel_type'] == OptionModel::CPU){

            }else if($param['rel_type'] == OptionModel::MEMORY){
                $insert['other_config'] = json_encode([
                    'memory_slot'   => (int)$param['other_config']['memory_slot'],
                    'memory'        => (int)$param['other_config']['memory'],
                ]);
            }

            $insert['type'] = $type;
            if($type == 'radio'){
                
                $whereSame = [];
                $whereSame[] = ['product_id', '=', $productId];
                $whereSame[] = ['rel_type', '=', $param['rel_type']];
                $whereSame[] = ['rel_id', '=', $param['rel_id']];
                $whereSame[] = ['value', '=', $param['value']];

                $same = $this
                        ->where($whereSame)
                        ->find();
                if(!empty($same)){
                    throw new \Exception('mf_dcim_already_add_the_same_option');
                }

                $insert['value'] = $param['value'];
            }else{

                $whereSame = [];
                $whereSame[] = ['product_id', '=', $productId];
                $whereSame[] = ['rel_type', '=', $param['rel_type']];
                $whereSame[] = ['rel_id', '=', $param['rel_id']];
                $whereSame[] = ['min_value', '<=', $param['max_value']];
                $whereSame[] = ['max_value', '>=', $param['min_value']];

                // 范围是否交叉
                $intersect = $this
                    ->where($whereSame)
                    ->find();
                if(!empty($intersect)){
                    throw new \Exception(lang_plugins('mf_dcim_option_intersect'));
                }
                $insert['min_value'] = $param['min_value'];
                $insert['max_value'] = $param['max_value'];
                $insert['step'] = $param['step'];
            }
            $option = $this->create($insert);

            $priceArr = [];
            foreach($duration as $v){
                if(isset($param['price'][$v])){
                    $priceArr[] = [
                        'product_id'    => $productId,
                        'rel_type'      => 'option',
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
            '',
            '',
            lang_plugins('mf_dcim_option_2'),
            lang_plugins('mf_dcim_option_3'),
            lang_plugins('mf_dcim_option_4'),
            lang_plugins('mf_dcim_option_5'),
            lang_plugins('mf_dcim_option_6'),
            lang_plugins('mf_dcim_option_7'),
            lang_plugins('mf_dcim_option_8'),
            lang_plugins('mf_dcim_option_9'),
        ];

        $nameType = [
            '',
            '',
            lang_plugins('mf_dcim_option_value_2'),
            lang_plugins('mf_dcim_option_value_3'),
            lang_plugins('mf_dcim_option_value_4'),
            lang_plugins('mf_dcim_option_value_5'),
            lang_plugins('mf_dcim_option_value_6'),
            lang_plugins('mf_dcim_option_value_7'),
            lang_plugins('mf_dcim_option_value_8'),
            lang_plugins('mf_dcim_option_value_9'),
        ];

        $description = lang_plugins('mf_dcim_log_add_option_success', [
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

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','value','order'])){
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
                    ->field('p.rel_id,p.price')
                    ->where('p.rel_type', 'option')
                    ->whereIn('p.rel_id', $id)
                    ->where('p.duration_id', $firstDuration['id'])
                    ->select()
                    ->toArray();

                $priceArr = [];
                foreach($price as $k=>$v){
                    $priceArr[ $v['rel_id'] ] = $v;
                }

                foreach($list as $k=>$v){
                    if(isset($v['type'])){
                        if($v['type'] == 'step'){
                            $list[$k]['price'] = $priceArr[$v['id']]['price'] ?? 0;
                            foreach($list as $kk=>$vv){
                                // 范围内的阶梯
                                if($v['min_value'] > $vv['max_value']){
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

                    // 处理公网IP格式
                    if($param['rel_type'] == OptionModel::LINE_IP){
                        if(strpos($v['value'], '_') !== false){
                            $v['value'] = explode(',', $v['value']);

                            $num = 0;
                            foreach($v['value'] as $vv){
                                $vv = explode('_', $vv);
                                $num += $vv[0];
                            }

                            $list[$k]['value'] = $num;
                        }
                    }
                    if(isset($v['other_config'])){
                        $list[$k]['other_config'] = json_decode($v['other_config'], true);
                    }
                }
            }else{
                foreach($list as $k=>$v){
                    $list[$k]['price'] = '0.00';
                    $list[$k]['duration'] = '';
                }
            }
        }
        // 根据value,min_value排序
        if($param['orderby'] == 'value,min_value'){
            usort($list, function($a, $b){
                return ((isset($a['value']) && (int)$a['value'] > (int)$b['value']) || (isset($a['min_value']) && (int)$a['min_value'] > (int)$b['min_value'])) ? 1 : -1;
            });
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
     * @param   string value - 值
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
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_option_not_found')];
        }
        $productId = $option['product_id'];
        $param['rel_type'] = $option['rel_type'];
        $param['rel_id'] = $option['rel_id'];
        $oldOtherConfig = json_decode($option['other_config'], true);

        // 验证周期价格
        $duration = DurationModel::field('id,name')->where('product_id', $productId)->select();

        $oldPrice = PriceModel::field('duration_id,price')->where('rel_type', 'option')->where('rel_id', $option->id)->select()->toArray();
        $oldPrice = array_column($oldPrice, 'price', 'duration_id');
        
        $this->startTrans();
        try{
            $type = $option['type'];

            $update = [
                'order' => $param['order'] ?? 0,
            ];
            if($param['rel_type'] == OptionModel::LINE_BW){
                $update['other_config'] = json_encode([
                    'in_bw' => $param['other_config']['in_bw'] ?? '',
                ]);
            }else if($param['rel_type'] == OptionModel::LINE_FLOW){
                $update['other_config'] = json_encode([
                    'in_bw' => $param['other_config']['in_bw'] ?? 0,
                    'out_bw' => $param['other_config']['out_bw'] ?? 0,
                    'bill_cycle' => $param['other_config']['bill_cycle'] ?? 'month',
                ]);
            }else if($param['rel_type'] == OptionModel::LINE_DEFENCE){

            }else if($param['rel_type'] == OptionModel::LINE_IP){

            }else if($param['rel_type'] == OptionModel::CPU){

            }else if($param['rel_type'] == OptionModel::MEMORY){
                $update['other_config'] = json_encode([
                    'memory_slot'   => (int)$param['other_config']['memory_slot'],
                    'memory'        => (int)$param['other_config']['memory'],
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

                // 必须是数字
                $same = $this
                        ->where($whereSame)
                        ->find();
                if(!empty($same)){
                    throw new \Exception(lang_plugins('mf_dcim_already_add_the_same_option'));
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

                // 范围是否交叉
                $intersect = $this
                    ->where($whereSame)
                    ->find();
                if(!empty($intersect)){
                    throw new \Exception(lang_plugins('mf_dcim_option_intersect'));
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
                        'rel_type'      => 'option',
                        'rel_id'        => $option->id,
                        'duration_id'   => $v['id'],
                        'price'         => $param['price'][$v['id']],
                    ];
                }
            }

            PriceModel::where('rel_type', 'option')->where('rel_id', $option->id)->delete();
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
            '',
            '',
            lang_plugins('mf_dcim_option_2'),
            lang_plugins('mf_dcim_option_3'),
            lang_plugins('mf_dcim_option_4'),
            lang_plugins('mf_dcim_option_5'),
            lang_plugins('mf_dcim_option_6'),
            lang_plugins('mf_dcim_option_7'),
            lang_plugins('mf_dcim_option_8'),
            lang_plugins('mf_dcim_option_9'),
        ];

        $nameType = [
            '',
            '',
            lang_plugins('mf_dcim_option_value_2'),
            lang_plugins('mf_dcim_option_value_3'),
            lang_plugins('mf_dcim_option_value_4'),
            lang_plugins('mf_dcim_option_value_5'),
            lang_plugins('mf_dcim_option_value_6'),
            lang_plugins('mf_dcim_option_value_7'),
            lang_plugins('mf_dcim_option_value_8'),
            lang_plugins('mf_dcim_option_value_9'),
        ];

        $billCycle = [
            'month'         => lang_plugins('mf_dcim_option_bill_cycle_month'),
            'last_30days'   => lang_plugins('mf_dcim_option_bill_cycle_last_30days'),
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
        if($option['rel_type'] == OptionModel::LINE_BW){
            $des['in_bw'] = lang_plugins('mf_dcim_line_bw_in_bw');
            $old['in_bw'] = $oldOtherConfig['in_bw'] ?? '';
            $new['in_bw'] = $param['other_config']['in_bw'] ?? '';
        }else if($option['rel_type'] == OptionModel::LINE_FLOW){
            $des['in_bw'] = lang_plugins('mf_dcim_line_flow_in_bw');
            $des['out_bw'] = lang_plugins('mf_dcim_line_flow_out_bw');
            $des['bill_cycle'] = lang_plugins('mf_dcim_line_flow_bill_cycle');

            $old['in_bw'] = $oldOtherConfig['in_bw'] ?? '';
            $old['out_bw'] = $oldOtherConfig['out_bw'] ?? '';
            $old['bill_cycle'] = $billCycle[ $oldOtherConfig['bill_cycle'] ?? 'month' ];

            $new['in_bw'] = $param['other_config']['in_bw'] ?? '';
            $new['out_bw'] = $param['other_config']['out_bw'] ?? '';
            $new['bill_cycle'] = $billCycle[ $param['other_config']['bill_cycle'] ?? 'month' ];
        }else if($option['rel_type'] == OptionModel::MEMORY){
            $des['memory_slot'] = lang_plugins('mf_dcim_memory_slot');
            $des['memory'] = lang_plugins('mf_dcim_memory_capacity');

            $old['memory_slot'] = $oldOtherConfig['memory_slot'] ?? '';
            $old['memory'] = $oldOtherConfig['memory'] ?? '';

            $new['memory_slot'] = $param['other_config']['memory_slot'];
            $new['memory'] = $param['other_config']['memory'];
        }

        // 每个周期的价格对比
        foreach($duration as $v){
            $des[ 'duration_'.$v['id'] ] = $v['name'].lang_plugins('price');
            $old[ 'duration_'.$v['id'] ] = $oldPrice[ $v['id'] ] ?? lang_plugins('null');
            $new[ 'duration_'.$v['id'] ] = $param['price'][$v['id']] ?? lang_plugins('null');
        }

        $description = ToolLogic::createEditLog($old, $new, $des);
        if(!empty($description)){
            $description = lang_plugins('mf_dcim_log_modify_option_success', [
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
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_option_not_found')];
        }
        if(isset($rel_type) && $option['rel_type'] != $rel_type){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_option_not_found')];
        }
        if($option['rel_type'] == OptionModel::CPU){
            // $count = PackageModel::where('cpu_option_id', $id)->count();
            // if($count > 0){
            //     return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_package_use_this_option_cannot_delete')];
            // }
        }else if($option['rel_type'] == OptionModel::MEMORY){
            // $count = PackageModel::where('mem_option_id', $id)->count();
            // if($count > 0){
            //     return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_package_use_this_option_cannot_delete')];
            // }
            $host = HostOptionLinkModel::alias('ohl')
                ->join('host h', 'ohl.host_id=h.id')
                ->where('ohl.option_id', $id)
                ->whereIn('h.status', ['Pending','Active','Suspended','Failed'])
                ->find();
            if(!empty($host)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_host_used_cannot_delete')];
            }
        }else if($option['rel_type'] == OptionModel::DISK){
            // $count = PackageModel::where('disk_option_id', $id)->count();
            // if($count > 0){
            //     return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_package_use_this_option_cannot_delete')];
            // }
            $host = HostOptionLinkModel::alias('ohl')
                ->join('host h', 'ohl.host_id=h.id')
                ->where('ohl.option_id', $id)
                ->whereIn('h.status', ['Pending','Active','Suspended','Failed'])
                ->find();
            if(!empty($host)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_host_used_cannot_delete')];
            }
        }else if($option['rel_type'] == OptionModel::GPU){
            $host = HostOptionLinkModel::alias('ohl')
                ->join('host h', 'ohl.host_id=h.id')
                ->where('ohl.option_id', $id)
                ->whereIn('h.status', ['Pending','Active','Suspended','Failed'])
                ->find();
            if(!empty($host)){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_host_used_cannot_delete')];
            }
        }

        $productId = $option['product_id'];
        $otherConfig = json_decode($option['other_config'], true);

        $this->startTrans();
        try{
            $this->where('id', $id)->delete();
            PriceModel::where('rel_type', 'option')->where('rel_id', $id)->delete();

            // 删除完了线路部分配置,自动关闭开关
            if(in_array($option['rel_type'], [OptionModel::LINE_DEFENCE])){
                // 是否还有对应配置,没有自动关闭开关
                $other = $this->where('rel_type', $option['rel_type'])->where('rel_id', $option['rel_id'])->find();
                if(empty($other)){
                    LineModel::where('id', $option['rel_id'])->update(['defence_enable'=>0]);
                }
            }
            // 删除内存,硬盘时
            if(in_array($option['rel_type'], [OptionModel::MEMORY, OptionModel::DISK, OptionModel::GPU])){
                PackageOptionLinkModel::where('option_id', $id)->delete();
                ModelConfigOptionLinkModel::where('option_id', $id)->delete();
            }

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('delete_success')];
        }

        $optionType = [
            '',
            '',
            lang_plugins('mf_dcim_option_2'),
            lang_plugins('mf_dcim_option_3'),
            lang_plugins('mf_dcim_option_4'),
            lang_plugins('mf_dcim_option_5'),
            lang_plugins('mf_dcim_option_6'),
            lang_plugins('mf_dcim_option_7'),
            lang_plugins('mf_dcim_option_8'),
            lang_plugins('mf_dcim_option_9'),
        ];

        $nameType = [
            '',
            '',
            lang_plugins('mf_dcim_option_value_2'),
            lang_plugins('mf_dcim_option_value_3'),
            lang_plugins('mf_dcim_option_value_4'),
            lang_plugins('mf_dcim_option_value_5'),
            lang_plugins('mf_dcim_option_value_6'),
            lang_plugins('mf_dcim_option_value_7'),
            lang_plugins('mf_dcim_option_value_8'),
            lang_plugins('mf_dcim_option_value_9'),
        ];

        $description = lang_plugins('mf_dcim_log_delete_option_success', [
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
                ->field('id,product_id,rel_type,rel_id,type,value,min_value,max_value,step,order,other_config')
                ->find($id);
        if(empty($option)){
            return [];
        }

        $duration = DurationModel::alias('d')
                    ->field('d.id,d.name,p.price')
                    ->leftJoin('module_mf_dcim_price p', 'p.rel_type="option" AND p.rel_id='.$id.' AND d.id=p.duration_id')
                    ->where('d.product_id', $option['product_id'])
                    ->withAttr('price', function($val){
                        return $val ?? '';
                    })
                    ->select()
                    ->toArray();

        $option = $option->toArray();
        $option['duration'] = $duration;
        $option['other_config'] = json_decode($option['other_config'], true);

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
    public function optionDurationPrice($productId, $relType, $relId = 0, $value = 0){
        $data = [];
        $match = false;

        $whereOption = [];
        $whereOption[] = ['product_id', '=', $productId];
        $whereOption[] = ['rel_type', '=', $relType];
        $whereOption[] = ['rel_id', '=', $relId];

        $type = OptionModel::where($whereOption)->value('type');
        if($type == 'radio'){
            $whereOption[] = ['value', '=', $value];

            $optionId = OptionModel::where($whereOption)->value('id');
            if(!empty($optionId)){
                $match = true;
                $data = PriceModel::field('duration_id,price')->where('rel_type', 'option')->where('rel_id', $optionId)->select()->toArray();
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

                $price = OptionModel::alias('o')
                        ->field('o.min_value,o.max_value,p.rel_id,p.duration_id,p.price')
                        ->where($wherePrice)
                        ->leftJoin('module_mf_dcim_price p', 'p.rel_type="option" AND o.id=p.rel_id')
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

                $price = PriceModel::field('duration_id,price')->where('rel_type', 'option')->where('rel_id', $optionId)->select()->toArray();
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
    public function matchOptionDurationPrice($productId, $relType, $relId = 0, $value = 0, $durationId = 0){
        $match = false;  // 配置是否匹配
        $price = null;   // 价格

        $whereOption = [];
        $whereOption[] = ['product_id', '=', $productId];
        $whereOption[] = ['rel_type', '=', $relType];
        $whereOption[] = ['rel_id', '=', $relId];

        $type = OptionModel::where($whereOption)->value('type');
        if($type == 'radio'){
            $whereOption[] = ['value', '=', $value];

            $option = OptionModel::where($whereOption)->find();
            if(!empty($option)){
                $match = true;
                $price = PriceModel::where('rel_type', 'option')->where('rel_id', $option['id'])->where('duration_id', $durationId)->value('price');
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

                $priceArr = OptionModel::alias('o')
                        ->field('o.min_value,o.max_value,p.rel_id,p.duration_id,p.price')
                        ->where($wherePrice)
                        ->where('p.duration_id', $durationId)
                        ->leftJoin('module_mf_dcim_price p', 'p.rel_type="option" AND o.id=p.rel_id')
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
                $price = PriceModel::field('duration_id,price')->where('rel_type', 'option')->where('rel_id', $optionId)->where('duration_id', $durationId)->value('price');
                $price = bcmul($price, $value);
            }
        }
        if($match){
            $option['other_config'] = json_decode($option['other_config'], true);
        }
        return ['match'=>$match, 'price'=>$price, 'option'=>$option ?? [] ];
    }

}