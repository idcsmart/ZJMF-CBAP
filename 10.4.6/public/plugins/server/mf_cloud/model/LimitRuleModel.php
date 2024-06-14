<?php 
namespace server\mf_cloud\model;

use think\Model;
use app\common\model\ProductModel;

/**
 * @title 限制规则模型
 * @use server\mf_cloud\model\LimitRuleModel
 */
class LimitRuleModel extends Model
{
	protected $name = 'module_mf_cloud_limit_rule';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'rule'          => 'string',
        'result'        => 'string',
        'rule_md5'      => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    // 可用的配置项类型
    // protected $ruleType = ['data_center','cpu','memory','image','system_disk','data_disk','bw','flow','ipv4_num','ipv6_num','recommend_config','duration'];
    protected $ruleType = ['data_center','cpu','memory','image'];

    /**
     * 时间 2024-05-10
     * @title 添加限制规则
     * @desc  添加限制规则
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   array param.rule - 条件数据 require
     * @param   array param.rule.cpu.value - CPU
     * @param   string param.rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.rule.memory.min - 内存最小值
     * @param   string param.rule.memory.max - 内存最大值
     * @param   string param.rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.rule.data_center.id - 数据中心ID
     * @param   string param.rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.rule.image.id - 操作系统ID
     * @param   string param.rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.result - 结果数据 require
     * @param   array param.result.cpu.value - CPU
     * @param   string param.result.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.result.memory.min - 内存最小值
     * @param   string param.result.memory.max - 内存最大值
     * @param   string param.result.memory.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.result.image.id - 操作系统ID
     * @param   string param.result.image.opt - 运算符(eq=等于,neq=不等于)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - 限制规则ID
     */
    public function limitRuleCreate($param)
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $productId = $ProductModel->id;

        $limitRuleRule = $this->limitRuleCheckAndFormat([
            'product_id' => $productId,
            'rule'       => $param['rule'],
        ]);
        if($limitRuleRule['status'] == 400){
            return $limitRuleRule;
        }
        $limitRuleResult = $this->limitRuleCheckAndFormat([
            'product_id' => $productId,
            'rule'       => $param['result'],
        ]);
        if($limitRuleResult['status'] == 400){
            return $limitRuleResult;
        }

        $rule = json_encode($limitRuleRule['data']);
        $ruleMd5 = md5($rule);
        // 是否已存在
        $exist = $this
                ->where('product_id', $productId)
                ->where('rule_md5', $ruleMd5)
                ->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_limit_rule_exist_the_same_rule')];
        }

        $insert = [
            'product_id' => $ProductModel->id,
            'rule'       => $rule,
            'result'     => json_encode($limitRuleResult['data']),
            'rule_md5'   => md5($rule),
            'create_time'=> time(),
        ];

        $limitRule = $this->create($insert);

        $description = lang_plugins('log_mf_cloud_limit_rule_create_success', [
            '{name}'    => $ProductModel['name'],
            '{rule}'    => $this->limitRuleDescription([
                'product_id'    => $param['product_id'],
                'rule'          => $limitRuleRule['data'],
                'result'        => $limitRuleResult['data'],
            ]),
        ]);

        active_log($description, 'product', $ProductModel->id);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$limitRule->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2024-05-10
     * @title 修改限制规则
     * @desc  修改限制规则
     * @author hh
     * @version v1
     * @param   int param.id - 限制规则ID require
     * @param   array param.rule - 条件数据 require
     * @param   array param.rule.cpu.value - CPU
     * @param   string param.rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.rule.memory.min - 内存最小值
     * @param   string param.rule.memory.max - 内存最大值
     * @param   string param.rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.rule.data_center.id - 数据中心ID
     * @param   string param.rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.rule.image.id - 操作系统ID
     * @param   string param.rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.result - 结果数据 require
     * @param   array param.result.cpu.value - CPU
     * @param   string param.result.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.result.memory.min - 内存最小值
     * @param   string param.result.memory.max - 内存最大值
     * @param   string param.result.memory.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.result.image.id - 操作系统ID
     * @param   string param.result.image.opt - 运算符(eq=等于,neq=不等于)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function limitRuleUpdate($param)
    {
        $limitRule = $this->find($param['id']);
        if(empty($limitRule)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_limit_rule_not_found')];
        }
        $ProductModel = ProductModel::find($limitRule['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $productId = $ProductModel->id;

        $limitRuleRule = $this->limitRuleCheckAndFormat([
            'product_id' => $productId,
            'rule'       => $param['rule'],
        ]);
        if($limitRuleRule['status'] == 400){
            return $limitRuleRule;
        }
        $limitRuleResult = $this->limitRuleCheckAndFormat([
            'product_id' => $productId,
            'rule'       => $param['result'],
        ]);
        if($limitRuleResult['status'] == 400){
            return $limitRuleResult;
        }

        $rule = json_encode($limitRuleRule['data']);
        $ruleMd5 = md5($rule);
        // 是否已存在
        $exist = $this
                ->where('product_id', $productId)
                ->where('rule_md5', $ruleMd5)
                ->where('id', '<>', $limitRule->id)
                ->find();
        if(!empty($exist)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_limit_rule_exist_the_same_rule')];
        }

        $this->update([
            'rule'          => $rule,
            'result'        => json_encode($limitRuleResult['data']),
            'rule_md5'      => md5($rule),
            'update_time'   => time(),
        ], ['id'=>$limitRule['id']]);

        $oldRule = $this->limitRuleDescription([
            'product_id' => $limitRule['product_id'],
            'rule'       => json_decode($limitRule['rule'], true),
            'result'     => json_decode($limitRule['result'], true),
        ]);
        $newRule = $this->limitRuleDescription([
            'product_id' => $limitRule['product_id'],
            'rule'       => $limitRuleRule['data'],
            'result'     => $limitRuleResult['data'],
        ]);

        if($oldRule != $newRule){
            $description = lang_plugins('log_mf_cloud_limit_rule_update_success', [
                '{name}'    => $ProductModel['name'],
                '{old}'     => $oldRule,
                '{new}'     => $newRule,
            ]);

            active_log($description, 'product', $ProductModel->id);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2024-05-10
     * @title 限制规则列表
     * @desc  限制规则列表
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @return  int list[].id - 限制规则ID
     * @return  array list[].rule.cpu.value - CPU
     * @return  string list[].rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @return  string list[].rule.memory.min - 内存最小值
     * @return  string list[].rule.memory.max - 内存最大值
     * @return  string list[].rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @return  array list[].rule.data_center.id - 数据中心ID
     * @return  array list[].rule.data_center.name - 数据中心名称
     * @return  string list[].rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @return  array list[].rule.image.id - 操作系统ID
     * @return  array list[].rule.image.name - 操作系统名称
     * @return  string list[].rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @return  array list[].result.cpu.value - CPU
     * @return  string list[].result.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @return  string list[].result.memory.min - 内存最小值
     * @return  string list[].result.memory.max - 内存最大值
     * @return  string list[].result.memory.opt - 运算符(eq=等于,neq=不等于)
     * @return  array list[].result.image.id - 操作系统ID
     * @return  array list[].result.image.name - 操作系统名称
     * @return  string list[].result.image.opt - 运算符(eq=等于,neq=不等于)
     */
    public function limitRuleList($param)
    {   
        $where = [];
        if(isset($param['product_id']) && !empty($param['product_id'])){
            $where[] = ['product_id', '=', $param['product_id']];
        }
        $dataCenter = [];
        $image = [];

        $list = $this
                ->field('id,rule,result')
                ->where($where)
                ->order('id', 'asc')
                ->select()
                ->toArray();

        foreach($list as $k=>$v){
            $v['rule'] = json_decode($v['rule'], true);
            $v['result'] = json_decode($v['result'], true);

            if(isset($v['rule']['data_center']) || isset($v['result']['data_center'])){
                if(empty($dataCenter)){
                    $dataCenterList = DataCenterModel::field('id,country_id,city,area')->where($where)->select();
                    foreach($dataCenterList as $vv){
                        $dataCenter[ $vv['id'] ] = $vv->getDataCenterName();
                    }
                }
            }
            if(isset($v['rule']['image']) || isset($v['result']['image'])){
                if(empty($image)){
                    $image = ImageModel::field('id,name')->where($where)->select()->toArray();
                    $image = array_column($image, 'name', 'id');
                }
            }

            // 获取显示
            if(isset($v['rule']['data_center']['id'])){
                $name = [];
                foreach($v['rule']['data_center']['id'] as $vv){
                    if(isset($dataCenter[ $vv ])){
                        $name[] = $dataCenter[$vv];
                    }
                }
                $v['rule']['data_center']['name'] = $name;
            }
            if(isset($v['rule']['image']['id'])){
                $name = [];
                foreach($v['rule']['image']['id'] as $vv){
                    if(isset($image[ $vv ])){
                        $name[] = $image[$vv];
                    }
                }
                $v['rule']['image']['name'] = $name;
            }
            // 获取显示
            if(isset($v['result']['data_center']['id'])){
                $name = [];
                foreach($v['result']['data_center']['id'] as $vv){
                    if(isset($dataCenter[ $vv ])){
                        $name[] = $dataCenter[$vv];
                    }
                }
                $v['result']['data_center']['name'] = $name;
            }
            if(isset($v['result']['image']['id'])){
                $name = [];
                foreach($v['result']['image']['id'] as $vv){
                    if(isset($image[ $vv ])){
                        $name[] = $image[$vv];
                    }
                }
                $v['result']['image']['name'] = $name;
            }
            $list[$k]['rule'] = $v['rule'];
            $list[$k]['result'] = $v['result'];
        }

        return ['list'=>$list];
    }

    /**
     * 时间 2024-05-10
     * @title 删除限制规则
     * @desc  删除限制规则
     * @author hh
     * @version v1
     * @param   int id - 限制规则ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function limitRuleDelete($id)
    {
        $limitRule = $this->find($id);
        if(empty($limitRule)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_limit_rule_not_found')];
        }
        $limitRule->delete();

        $product = ProductModel::find($limitRule['product_id']);
        $rule = $this->limitRuleDescription([
            'product_id'    => $limitRule['product_id'],
            'rule'          => json_decode($limitRule['rule'], true),
            'result'        => json_decode($limitRule['result'], true),
        ]);

        $description = lang_plugins('log_mf_cloud_limit_rule_delete_success', [
            '{name}'    => $product['name'],
            '{rule}'    => $rule,
        ]);
        active_log($description, 'product', $limitRule['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2024-05-11
     * @title 生成限制规则描述
     * @desc  生成限制规则描述
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   array param.rule - 条件数据 require
     * @param   array param.rule.cpu.value - CPU
     * @param   string param.rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.rule.memory.min - 内存最小值
     * @param   string param.rule.memory.max - 内存最大值
     * @param   string param.rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.rule.data_center.id - 数据中心ID
     * @param   string param.rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.rule.image.id - 操作系统ID
     * @param   string param.rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.result - 结果数据 require
     * @param   array param.result.cpu.value - CPU
     * @param   string param.result.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @param   string param.result.memory.min - 内存最小值
     * @param   string param.result.memory.max - 内存最大值
     * @param   string param.result.memory.opt - 运算符(eq=等于,neq=不等于)
     * @param   array param.result.image.id - 操作系统ID
     * @param   string param.result.image.opt - 运算符(eq=等于,neq=不等于)
     * @return  string
     */
    public function limitRuleDescription($param)
    {
        $desc = [
            'cpu'               => 'CPU',
            'memory'            => lang_plugins('memory'),
            'data_center'       => lang_plugins('data_center'),
            'ipv4_num'          => lang_plugins('mf_cloud_option_value_5'),
            'ipv6_num'          => lang_plugins('mf_cloud_ipv6_num'),
            'bw'                => lang_plugins('bw'),
            'flow'              => lang_plugins('flow'),
            'system_disk'       => lang_plugins('system_disk'),
            'data_disk'         => lang_plugins('data_disk'),
            'image'             => lang_plugins('mf_cloud_image'),
            'recommend_config'  => lang_plugins('mf_cloud_recommend_config'),
            'duration'          => lang_plugins('mf_cloud_duration'),
        ];

        $ruleDesc = [];   // 条件
        $resultDesc = []; // 结果

        $opt = [
            'eq'  => lang_plugins('mf_cloud_limit_rule_eq'),
            'neq' => lang_plugins('mf_cloud_limit_rule_eq'),
        ];

        // 按照顺序生成
        foreach($this->ruleType as $type){
            if(isset($param['rule'][$type])){
                if($type == 'cpu'){
                    $ruleDesc[] = 'CPU' . $opt[ $param['rule'][$type]['opt'] ] . implode(',', $param['rule'][$type]['value']);
                }else if(in_array($type, ['memory','ipv4_num','ipv6_num','bw','flow','system_disk','data_disk'])){
                    $min = strval($param['rule'][$type]['min'] ?? '');
                    $max = strval($param['rule'][$type]['max'] ?? '');

                    $ruleDesc[] = lang_plugins('mf_cloud_limit_rule_range_desc', [
                        '{type}'    => $desc[ $type ],
                        '{opt}'     => $opt[ $param['rule'][$type]['opt'] ],
                        '{min}'     => $min === '' ? lang_plugins('null') : $min,
                        '{max}'     => $max === '' ? lang_plugins('null') : $max,
                    ]);
                }else if($type == 'data_center'){
                    $id = $param['rule'][$type]['id'];
                    $name = [];

                    $dataCenter = DataCenterModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->select();
                    foreach($dataCenter as $v){
                        $name[] = $v->getDataCenterName();
                    }

                    $ruleDesc[] = $desc[$type] . $opt[ $param['rule'][$type]['opt'] ] . implode(',', $name);
                }else if($type == 'image'){
                    $id = $param['rule'][$type]['id'];
                    $name = ImageModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->column('name');
                    
                    $ruleDesc[] = $desc[$type] . $opt[ $param['rule'][$type]['opt'] ] . implode(',', $name);
                }else if($type == 'recommend_config'){
                    $id = $param['rule'][$type]['id'];
                    $name = RecommendConfigModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->column('name');
                    
                    $ruleDesc[] = $desc[$type] . $opt[ $param['rule'][$type]['opt'] ] . implode(',', $name);
                }else if($type == 'duration'){
                    $id = $param['rule'][$type]['id'];
                    $name = DurationModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->column('name');

                    $ruleDesc[] = $desc[$type] . $opt[ $param['rule'][$type]['opt'] ] . implode(',', $name);
                }
            }
            if(isset($param['result'][$type])){
                if($type == 'cpu'){
                    $resultDesc[] = 'CPU' . $opt[ $param['result'][$type]['opt'] ] . implode(',', $param['result'][$type]['value']);
                }else if(in_array($type, ['memory','ipv4_num','ipv6_num','bw','flow','system_disk','data_disk'])){
                    $min = strval($param['result'][$type]['min'] ?? '');
                    $max = strval($param['result'][$type]['max'] ?? '');

                    $resultDesc[] = lang_plugins('mf_cloud_limit_rule_range_desc', [
                        '{type}'    => $desc[ $type ],
                        '{opt}'     => $opt[ $param['result'][$type]['opt'] ],
                        '{min}'     => $min === '' ? lang_plugins('null') : $min,
                        '{max}'     => $max === '' ? lang_plugins('null') : $max,
                    ]);
                }else if($type == 'data_center'){
                    $id = $param['result'][$type]['id'];
                    $name = [];

                    $dataCenter = DataCenterModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->select();
                    foreach($dataCenter as $v){
                        $name[] = $v->getDataCenterName();
                    }

                    $resultDesc[] = $desc[$type] . $opt[ $param['result'][$type]['opt'] ] . implode(',', $name);
                }else if($type == 'image'){
                    $id = $param['result'][$type]['id'];
                    $name = ImageModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->column('name');
                    
                    $resultDesc[] = $desc[$type] . $opt[ $param['result'][$type]['opt'] ] . implode(',', $name);
                }else if($type == 'recommend_config'){
                    $id = $param['result'][$type]['id'];
                    $name = RecommendConfigModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->column('name');
                    
                    $resultDesc[] = $desc[$type] . $opt[ $param['result'][$type]['opt'] ] . implode(',', $name);
                }else if($type == 'duration'){
                    $id = $param['result'][$type]['id'];
                    $name = DurationModel::where('product_id', $param['product_id'])->whereIn('id', $id)->order('id', 'asc')->column('name');

                    $resultDesc[] = $desc[$type] . $opt[ $param['result'][$type]['opt'] ] . implode(',', $name);
                }
            }
        }

        $description = lang_plugins('mf_cloud_limit_rule_description', [
            '{rule}'    => implode(',', $ruleDesc),
            '{result}'  => implode(',', $resultDesc),
        ]);
        return $description;
    }
    
    /**
     * 时间 2024-05-11
     * @title 前台限制规则列表
     * @desc  前台限制规则列表
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  int [].id - 限制规则ID
     * @return  array [].rule - 条件数据
     * @return  array [].rule.cpu.value - CPU
     * @return  string [].rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @return  string [].rule.memory.min - 内存最小值
     * @return  string [].rule.memory.max - 内存最大值
     * @return  string [].rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @return  array [].rule.data_center.id - 数据中心ID
     * @return  string [].rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @return  array [].rule.image.id - 操作系统ID
     * @return  string [].rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @return  array [].result - 结果数据
     * @return  array [].result.cpu.value - CPU
     * @return  string [].result.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @return  string [].result.memory.min - 内存最小值
     * @return  string [].result.memory.max - 内存最大值
     * @return  string [].result.memory.opt - 运算符(eq=等于,neq=不等于)
     * @return  array [].result.data_center.id - 数据中心ID
     * @return  string [].result.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @return  array [].result.image.id - 操作系统ID
     * @return  string [].result.image.opt - 运算符(eq=等于,neq=不等于)
     */
    public function homeLimitRule($product_id)
    {
        $data = $this
            ->field('id,rule,result')
            ->where('product_id', $product_id)
            ->withAttr('rule', function($val){
                return json_decode($val, true);
            })
            ->withAttr('result', function($val){
                return json_decode($val, true);
            })
            ->order('id', 'asc')
            ->select()
            ->toArray();
        return $data;
    }

    /**
     * 时间 2024-05-11
     * @title 验证参数是否在范围内
     * @desc  验证参数是否在范围内
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   array param    - 要验证的参数 require
     * @param   int param.cpu  - CPU
     * @param   int param.memory - 内存
     * @param   int param.data_center_id - 数据中心ID
     * @param   int param.ip_num - IPv4数量
     * @param   int param.ipv6_num - IPv6数量
     * @param   int param.bw - 带宽
     * @param   int param.flow - 流量
     * @param   int param.system_disk.size - 系统盘大小
     * @param   int param.data_disk[].size - 数据盘大小
     * @param   int param.image_id - 操作系统ID
     * @param   int param.recommend_config_id - 套餐ID
     * @param   int param.duration_id - 周期ID
     * @param   int param.line_id - 线路ID
     * @param   array param.checkResult - 需要验证的结果类型,不传表示全部
     */
    public function checkLimitRule($product_id, $param, $checkResult = [])
    {
        $limitRule = $this->homeLimitRule($product_id);
        if(!empty($limitRule)){
            $lineType = 'bw';
            if(isset($param['line_id']) && !empty($param['line_id'])){
                $line = LineModel::find($param['line_id']);
                if(!empty($line)){
                    $lineType = $line['bill_type'];
                    $param['data_center_id'] = $line['data_center_id'];
                }
            }
            $param['data_center_id'] = $param['data_center_id'] ?? 0;
            $param['cpu'] = $param['cpu'] ?? 0;
            $param['memory'] = $param['memory'] ?? 0;
            $param['image_id'] = $param['image_id'] ?? 0;

            // 从上到下依次匹配
            if(isset($param['recommend_config_id']) && !empty($param['recommend_config_id'])){
                // 套餐暂无验证


            }else{
                foreach($limitRule as $v){
                    // 匹配条件
                    $matchRule = $this->limitRuleMatch($v['rule'], $param);
                    if($matchRule){
                        // 匹配结果
                        $matchResult = $this->limitRuleMatch($v['result'], $param, $checkResult);
                        if(!$matchResult){
                            return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_cannot_select_this_config') ];
                        }
                    }
                }
            }
        }
        return ['status'=>200, 'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2024-05-24
     * @title 限制规则是否在规则/结果内
     * @desc  限制规则是否在规则/结果内
     * @author hh
     * @version v1
     * @param   array rule - 条件/结果单条数据 require
     * @param   array rule.cpu.value - CPU
     * @param   string rule.cpu.opt - 运算符(eq=等于,neq=不等于)
     * @param   string rule.memory.min - 内存最小值
     * @param   string rule.memory.max - 内存最大值
     * @param   string rule.memory.opt - 运算符(eq=等于,neq=不等于)
     * @param   array rule.data_center.id - 数据中心ID
     * @param   string rule.data_center.opt - 运算符(eq=等于,neq=不等于)
     * @param   array rule.image.id - 操作系统ID
     * @param   string rule.image.opt - 运算符(eq=等于,neq=不等于)
     * @param   int param.data_center_id - 数据中心ID
     * @param   int param.cpu - CPU
     * @param   int param.memory - 内存
     * @param   int param.image_id - 操作系统ID
     * @param   array param.checkRule - 需要验证的类型,不在的算通过,通常在部分结果匹配
     * @return  bool
     */
    public function limitRuleMatch($rule, $param, $checkRule = [])
    {
        $param['data_center_id'] = $param['data_center_id'] ?? 0;
        $param['cpu'] = $param['cpu'] ?? 0;
        $param['memory'] = $param['memory'] ?? 0;
        $param['image_id'] = $param['image_id'] ?? 0;

        $matchNum = 0;  // 匹配条件数量
        // 匹配条件
        if(isset($rule['data_center'])){
            if(!empty($checkRule) && !in_array('data_center', $checkRule)){
                $matchNum++;
            }else{
                $match = in_array($param['data_center_id'], $rule['data_center']['id']);
                if($rule['data_center']['opt'] == 'neq'){
                    $match = !$match;
                }
                if($match) $matchNum++;
            }
        }
        if(isset($rule['cpu'])){
            if(!empty($checkRule) && !in_array('cpu', $checkRule)){
                $matchNum++;
            }else{
                $match = in_array($param['cpu'], $rule['cpu']['value']);
                if($rule['cpu']['opt'] == 'neq'){
                    $match = !$match;
                }
                if($match) $matchNum++;
            }
        }
        if(isset($rule['memory'])){
            if(!empty($checkRule) && !in_array('memory', $checkRule)){
                $matchNum++;
            }else{
                $min = intval($rule['memory']['min'] ?: 0);
                $max = intval($rule['memory']['max'] ?: 99999999);
                $match = $param['memory'] >= $min && $param['memory'] <= $max;
                if($rule['memory']['opt'] == 'neq'){
                    $match = !$match;
                }
                if($match) $matchNum++;
            }
        }
        if(isset($rule['image'])){
            if(!empty($checkRule) && !in_array('image', $checkRule)){
                $matchNum++;
            }else{
                $match = in_array($param['image_id'], $rule['image']['id']);
                if($rule['image']['opt'] == 'neq'){
                    $match = !$match;
                }
                if($match) $matchNum++;
            }
        }
        return $matchNum === count($rule);
    }

    /**
     * 时间 2024-05-24
     * @title 验证并格式化规则/结果
     * @desc  验证并格式化规则/结果
     * @author hh
     * @version v1
     * @param   array param.rule - 格式化的规则/结果 require
     * @param   int param.product_id - 商品ID require
     */
    public function limitRuleCheckAndFormat($param)
    {   
        $rule = $param['rule'] ?? [];
        $productId = $param['product_id'] ?? 0;
    
        $data = [];
        foreach($this->ruleType as $v){
            if(isset($rule[$v])){
                $ruleItem = $rule[$v];
                if($v == 'cpu'){
                    // 验证CPU选项
                    $cpu = OptionModel::where('product_id', $productId)->where('rel_type', OptionModel::CPU)->whereIn('value', $ruleItem['value'])->column('value');
                    if(count($cpu) != count($ruleItem['value'])){
                        return ['status'=>400, 'msg'=>lang_plugins('cpu_config_not_found')];
                    }
                    sort($cpu);

                    $data[$v]['value'] = $cpu;
                    $data[$v]['opt'] = $ruleItem['opt'];
                }else if(in_array($v, ['memory','ipv4_num','ipv6_num','bw','flow','system_disk','data_disk'])){
                    $data[$v]['min'] = strval($ruleItem['min'] ?? '');
                    $data[$v]['max'] = strval($ruleItem['max'] ?? '');
                    $data[$v]['opt'] = $ruleItem['opt'];
                }else if($v == 'data_center'){
                    // 验证数据中心
                    $dataCenterId = DataCenterModel::where('product_id', $productId)->whereIn('id', $ruleItem['id'])->order('id', 'asc')->column('id');
                    if(count($dataCenterId) != count($ruleItem['id'])){
                        return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
                    }

                    $data[$v]['id'] = $dataCenterId;
                    $data[$v]['opt'] = $ruleItem['opt'];
                }else if($v == 'image'){
                    // 验证操作系统
                    $imageId = ImageModel::where('product_id', $productId)->whereIn('id', $ruleItem['id'])->order('id', 'asc')->column('id');
                    if(count($imageId) != count($ruleItem['id'])){
                        return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_os_not_found')];
                    }

                    $data[$v]['id'] = $imageId;
                    $data[$v]['opt'] = $ruleItem['opt'];
                }else if($v == 'recommend_config'){
                    // 验证套餐
                    $recommendConfigId = RecommendConfigModel::where('product_id', $productId)->whereIn('id', $ruleItem['id'])->order('id', 'asc')->column('id');
                    if(count($recommendConfigId) != count($ruleItem['id'])){
                        return ['status'=>400, 'msg'=>lang_plugins('recommend_config_not_found')];
                    }

                    $data[$v]['id'] = $recommendConfigId;
                    $data[$v]['opt'] = $ruleItem['opt'];
                }else if($v == 'duration'){
                    // 验证周期
                    $durationId = DurationModel::where('product_id', $productId)->whereIn('id', $ruleItem['id'])->order('id', 'asc')->column('id');
                    if(count($durationId) != count($ruleItem['id'])){
                        return ['status'=>400, 'msg'=>lang_plugins('duration_not_found')];
                    }

                    $data[$v]['id'] = $durationId;
                    $data[$v]['opt'] = $ruleItem['opt'];
                }
            }
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data,
        ];
        return $result;
    }






}