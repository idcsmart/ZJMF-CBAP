<?php 
namespace server\mf_cloud\model;

use think\Model;
use app\common\model\ProductModel;
use server\mf_cloud\logic\ToolLogic;
use server\mf_cloud\validate\ConfigLimitValidate;

/**
 * @title 配置限制模型
 * @use server\mf_cloud\model\ConfigLimitModel
 */
class ConfigLimitModel extends Model{

	protected $name = 'module_mf_cloud_config_limit';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_id'        => 'int',
        'type'              => 'string',
        'data_center_id'    => 'int',
        'line_id'           => 'int',
        'min_bw'            => 'int',
        'max_bw'            => 'int',
        'cpu'               => 'string',
        'memory'            => 'string',
        'min_memory'        => 'int',
        'max_memory'        => 'int',
        'create_time'       => 'int',
    ];

    /**
     * 时间 2023-02-01
     * @title 添加配置限制
     * @desc 添加配置限制
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   string type - 类型(cpu=CPU与内存限制,data_center=数据中心与计算限制,line=带宽与计算限制) require
     * @param   int data_center_id - 数据中心ID requireIf,type=data_center
     * @param   int line_id - 线路ID requireIf,type=line
     * @param   int min_bw - 带宽最小值 requireIf,type=line
     * @param   int max_bw - 带宽最大值 requireIf,type=line
     * @param   array cpu - CPU核心数 require
     * @param   array memory - 内存容量 
     * @param   int min_memory - 内存最小值
     * @param   int max_memory - 内存最大值
     * @return  int id - 配置限制ID
     */
    public function configLimitCreate($param){
        // 验证CPU
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $insert = [
            'product_id' => $ProductModel->id,
            'type'       => $param['type'],
        ];

        $cpu = OptionModel::where('product_id', $ProductModel->id)->where('rel_type', OptionModel::CPU)->column('value');
        $memoryType = OptionModel::where('product_id', $ProductModel->id)->where('rel_type', OptionModel::MEMORY)->value('type');
        if(empty($cpu)){
            return ['status'=>400, 'msg'=>lang_plugins('please_add_cpu_config_first')];
        }
        if(empty($memoryType)){
            return ['status'=>400, 'msg'=>lang_plugins('please_add_memory_config_first')];
        }
        // 推荐配置条件
        $where = [];
        $where[] = ['product_id', '=', $ProductModel->id];

        $insert['cpu'] = [];
        foreach($param['cpu'] as $v){
            if(!in_array($v, $cpu)){
                return ['status'=>400, 'msg'=>lang_plugins('cpu_config_not_found')];
            }
            $insert['cpu'][] = $v;
        }
        sort($insert['cpu']);

        $where[] = ['cpu', 'IN', $insert['cpu']];
        $insert['cpu'] = implode(',', $insert['cpu']);

        $ConfigLimitValidate = new ConfigLimitValidate();
        // 单选,验证
        if($memoryType == 'radio'){
            if (!$ConfigLimitValidate->scene('memory')->check($param)){
                return ['status' => 400 , 'msg' => lang_plugins($ConfigLimitValidate->getError())];
            }

            $memory = OptionModel::where('product_id', $ProductModel->id)->where('rel_type', OptionModel::MEMORY)->column('value');
            
            $insert['memory'] = [];
            foreach($param['memory'] as $v){
                if(!in_array($v, $memory)){
                    return ['status'=>400, 'msg'=>lang_plugins('memory_config_not_found')];
                }
                $insert['memory'][] = $v;
            }
            sort($insert['memory']);

            $where[] = ['memory', 'IN', $insert['memory']];
            $insert['memory'] = implode(',', $insert['memory']);
        }else{
            if (!$ConfigLimitValidate->scene('memory_range')->check($param)){
                return ['status' => 400 , 'msg' => lang_plugins($ConfigLimitValidate->getError())];
            }
            $insert['memory'] = '';
            $insert['min_memory'] = $param['min_memory'];
            $insert['max_memory'] = $param['max_memory'];

            $where[] = ['memory', '>=', $param['min_memory']];
            $where[] = ['memory', '<=', $param['max_memory']];
        }

        if($param['type'] == 'cpu'){

        }else if($param['type'] == 'data_center'){
            $dataCenter = DataCenterModel::find($param['data_center_id']);
            if(empty($dataCenter) || $dataCenter['product_id'] != $ProductModel->id){
                return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
            }
            $insert['data_center_id'] = $param['data_center_id'];

            $where[] = ['data_center_id', '=', $param['data_center_id']];
        }else if($param['type'] == 'line'){
            $line = LineModel::find($param['line_id']);
            if(empty($line) || $line['bill_type'] != 'bw'){
                return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
            }
            $dataCenter = DataCenterModel::find($line['data_center_id']);
            if(empty($dataCenter) || $dataCenter['product_id'] != $ProductModel->id){
                return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
            }
            $insert['line_id'] = $param['line_id'];
            $insert['min_bw'] = $param['min_bw'];
            $insert['max_bw'] = $param['max_bw'];

            $where[] = ['line_id', '=', $param['line_id']];
            $where[] = ['bw', '>=', $param['min_bw']];
            $where[] = ['bw', '<=', $param['max_bw']];
        }
        
        // 是否有相关联的推荐配置
        // $recommendConfig = RecommendConfigModel::where($where)->find();
        // if(!empty($recommendConfig)){
        //     return ['status'=>400, 'msg'=>lang_plugins('recommend_config_exist_this_config_cannot_add')];
        // }
        // 是否添加了相同配置
        $same = $this
                ->where($insert)
                ->find();
        if(!empty($same)){
            return ['status'=>400, 'msg'=>lang_plugins('already_add_the_same_config_limit')];
        }
        $insert['create_time'] = time();

        $this->startTrans();
        try{
            $configLimit = $this->create($insert);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('create_fail') ];
        }

        $description = lang_plugins('log_add_config_limit_success', [
            '{data_center}' => isset($dataCenter) && !empty($dataCenter) ? $dataCenter->getDataCenterName() : lang_plugins('null'),
            '{cpu}' => $insert['cpu'],
            '{memory}' => !empty($insert['memory']) ? $insert['memory'] : $insert['min_memory'].'-'.$insert['max_memory'],
        ]);

        active_log($description, 'product', $ProductModel->id);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$configLimit->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-02-01
     * @title 配置限制列表
     * @desc 配置限制列表
     * @author hh
     * @version v1
     * @param   string product_id - 商品ID require
     * @param   string type - 类型 require
     * @return  [type] [description]
     */
    public function configLimitList($param){
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');
        $param['type'] = $param['type'] ?? '';

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id'])){
            $param['orderby'] = 'id';
        }
        $list = [];
        $count = 0;

        $where = [];
        $where[] = ['cl.type', '=', $param['type']];
        if(!empty($param['product_id'])){
            $where[] = ['cl.product_id', '=', $param['product_id']];
        }
        if($param['type'] == 'cpu'){
            $list = $this
                ->alias('cl')
                ->field('cl.id,cl.cpu,cl.memory,cl.min_memory,cl.max_memory')
                ->where($where)
                ->withAttr('cpu', function($val){
                    return explode(',', $val);
                })
                ->page($param['page'], $param['limit'])
                ->order($param['orderby'], $param['sort'])
                ->select()
                ->toArray();
    
        }else if($param['type'] == 'data_center'){
            $language = get_client_lang();
            $countryField = ['en-us'=> 'nicename'];
            $countryName = $countryField[ $language ] ?? 'name_zh';

            $list = $this
                ->alias('cl')
                ->field('cl.id,cl.data_center_id,cl.cpu,cl.memory,cl.min_memory,cl.max_memory,CONCAT(c.'.$countryName.',"-",dc.city,"-",dc.area) data_center,dc.country_id,dc.city,dc.area')
                ->where($where)
                ->leftJoin('module_mf_cloud_data_center dc', 'cl.data_center_id=dc.id')
                ->leftJoin('country c', 'dc.country_id=c.id')
                ->withAttr('cpu', function($val){
                    return explode(',', $val);
                })
                ->page($param['page'], $param['limit'])
                ->order($param['orderby'], $param['sort'])
                ->select()
                ->toArray();
        }else if($param['type'] == 'line'){
            $list = $this
                ->alias('cl')
                ->field('cl.id,cl.line_id,cl.cpu,cl.memory,cl.min_memory,cl.max_memory,cl.min_bw,cl.max_bw,l.name line_name,l.data_center_id,dc.country_id,dc.city,dc.area')
                ->where($where)
                ->leftJoin('module_mf_cloud_line l', 'cl.line_id=l.id')
                ->leftJoin('module_mf_cloud_data_center dc', 'l.data_center_id=dc.id')
                ->withAttr('cpu', function($val){
                    return explode(',', $val);
                })
                ->page($param['page'], $param['limit'])
                ->order($param['orderby'], $param['sort'])
                ->select()
                ->toArray();
        }
        $count = $this
            ->alias('cl')
            ->where($where)
            ->count();

        return ['list'=>$list, 'count'=>$count];
    }


    /**
     * 时间 2023-02-01
     * @title 修改配置限制
     * @desc 修改配置限制
     * @author hh
     * @version v1
     * @param   int id - 限制配置ID require
     * @param   int data_center_id - 数据中心ID requireIf,type=data_center
     * @param   int line_id - 线路ID requireIf,type=line
     * @param   int min_bw - 带宽最小值 requireIf,type=line
     * @param   int max_bw - 带宽最大值 requireIf,type=line
     * @param   array cpu - CPU核心数 require
     * @param   array memory - 内存容量 
     * @param   int min_memory - 内存最小值
     * @param   int max_memory - 内存最大值
     */
    public function configLimitUpdate($param){
        $configLimit = $this->find($param['id']);
        if(empty($configLimit)){
            return ['status'=>400, 'msg'=>lang_plugins('config_limit_not_found')];
        }
        $param['type'] = $configLimit['type'];

        $cpu = OptionModel::where('product_id', $configLimit['product_id'])->where('rel_type', OptionModel::CPU)->column('value');
        $memoryType = OptionModel::where('product_id', $configLimit['product_id'])->where('rel_type', OptionModel::MEMORY)->value('type');
        if(empty($cpu)){
            return ['status'=>400, 'msg'=>lang_plugins('please_add_cpu_config_first')];
        }
        if(empty($memoryType)){
            return ['status'=>400, 'msg'=>lang_plugins('please_add_memory_config_first')];
        }
        // 推荐配置条件
        $where = [];
        $where[] = ['product_id', '=', $configLimit['product_id']];

        $insert['cpu'] = [];
        foreach($param['cpu'] as $v){
            if(!in_array($v, $cpu)){
                return ['status'=>400, 'msg'=>lang_plugins('cpu_config_not_found')];
            }
            $insert['cpu'][] = $v;
        }
        sort($insert['cpu']);

        $where[] = ['cpu', 'IN', $insert['cpu']];
        $insert['cpu'] = implode(',', $insert['cpu']);

        $ConfigLimitValidate = new ConfigLimitValidate();
        // 单选,验证
        if($memoryType == 'radio'){
            if (!$ConfigLimitValidate->scene('memory')->check($param)){
                return ['status' => 400 , 'msg' => lang_plugins($ConfigLimitValidate->getError())];
            }

            $memory = OptionModel::where('product_id', $configLimit['product_id'])->where('rel_type', OptionModel::MEMORY)->column('value');
            
            $insert['memory'] = [];
            foreach($param['memory'] as $v){
                if(!in_array($v, $memory)){
                    return ['status'=>400, 'msg'=>lang_plugins('memory_config_not_found')];
                }
                $insert['memory'][] = $v;
            }
            sort($insert['memory']);

            $where[] = ['memory', 'IN', $insert['memory']];
            $insert['memory'] = implode(',', $insert['memory']);
        }else{
            if (!$ConfigLimitValidate->scene('memory_range')->check($param)){
                return ['status' => 400 , 'msg' => lang_plugins($ConfigLimitValidate->getError())];
            }
            $insert['memory'] = '';
            $insert['min_memory'] = $param['min_memory'];
            $insert['max_memory'] = $param['max_memory'];

            $where[] = ['memory', '>=', $param['min_memory']];
            $where[] = ['memory', '<=', $param['max_memory']];
        }

        if($param['type'] == 'cpu'){

        }else if($param['type'] == 'data_center'){
            $dataCenter = DataCenterModel::find($param['data_center_id']);
            if(empty($dataCenter) || $dataCenter['product_id'] != $configLimit['product_id']){
                return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
            }
            $insert['data_center_id'] = $param['data_center_id'];

            $where[] = ['data_center_id', '=', $param['data_center_id']];
        }else if($param['type'] == 'line'){
            $line = LineModel::find($param['line_id']);
            if(empty($line) || $line['bill_type'] != 'bw'){
                return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
            }
            $dataCenter = DataCenterModel::find($line['data_center_id']);
            if(empty($dataCenter) || $dataCenter['product_id'] != $configLimit['product_id']){
                return ['status'=>400, 'msg'=>lang_plugins('line_not_found')];
            }
            $insert['line_id'] = $param['line_id'];
            $insert['min_bw'] = $param['min_bw'];
            $insert['max_bw'] = $param['max_bw'];

            $where[] = ['line_id', '=', $param['line_id']];
            $where[] = ['bw', '>=', $param['min_bw']];
            $where[] = ['bw', '<=', $param['max_bw']];
        }
        
        // 是否有相关联的推荐配置
        // $recommendConfig = RecommendConfigModel::where($where)->find();
        // if(!empty($recommendConfig)){
        //     return ['status'=>400, 'msg'=>lang_plugins('recommend_config_exist_this_config_cannot_add')];
        // }

        // 是否添加了相同配置
        $same = $this
                ->where($insert)
                ->where('id', '<>', $configLimit['id'])
                ->find();
        if(!empty($same)){
            return ['status'=>400, 'msg'=>lang_plugins('already_add_the_same_config_limit')];
        }

        $this->startTrans();
        try{
            $this->update($insert, ['id'=>$configLimit['id']]);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('create_fail').$e->getMessage() ];
        }

        $des = [
            'data_center' => lang_plugins('data_center'),
            'cpu' => 'CPU',
            'memory' => lang_plugins('memory')
        ];
        $old = [
            'data_center' => empty($configLimit['data_center_id']) ? lang_plugins('null') : DataCenterModel::find($configLimit['data_center_id'])->getDataCenterName(),
            'cpu' => $configLimit['cpu'],
            'memory' => !empty($configLimit['memory']) ? $configLimit['memory'] : $configLimit['min_memory'].'-'.$configLimit['max_memory'],
        ];
        $new = [
            'data_center' => isset($dataCenter) && !empty($dataCenter) ? $dataCenter->getDataCenterName() : lang_plugins('null'),
            'cpu' => $insert['cpu'],
            'memory' => !empty($insert['memory']) ? $insert['memory'] : $insert['min_memory'].'-'.$insert['max_memory'],
        ];

        $description = ToolLogic::createEditLog($old, $new, $des);
        if(!empty($description)){
            $description = lang_plugins('log_modify_config_limit_success', [
                '{id}' => $configLimit['id'],
                '{detail}' => $description,
            ]);
            active_log($description, 'product', $configLimit['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-01
     * @title 删除配置限制
     * @desc 删除配置限制
     * @author hh
     * @version v1
     * @param   int id - 配置ID require
     */
    public function configLimitDelete($id){
        $configLimit = $this->find($id);
        if(empty($configLimit)){
            return ['status'=>400, 'msg'=>lang_plugins('config_limit_not_found')];
        }

        $this->where('id', $id)->delete();


        $dataCenter = DataCenterModel::find($configLimit['data_center_id']);

        $description = lang_plugins('log_delete_config_limit_success', [
            '{data_center}' => isset($dataCenter) && !empty($dataCenter) ? $dataCenter->getDataCenterName() : lang_plugins('null'),
            '{cpu}' => $configLimit['cpu'],
            '{memory}' => !empty($configLimit['memory']) ? $configLimit['memory'] : $configLimit['min_memory'].'-'.$configLimit['max_memory'],
        ]);
        active_log($description, 'product', $configLimit['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-14
     * @title 验证商品配置限制
     * @desc 验证商品配置限制
     * @author hh
     * @version v1
     * @param   int $product_id - 商品ID require
     * @param   int param.cpu   - cpu
     * @param   int param.memory   - 内存
     * @param   int param.data_center_id   - 数据中心ID
     * @param   int param.line_id   - 线路ID
     * @param   int param.bw   - 带宽
     */
    public function checkConfigLimit($product_id, $param){
        $configLimit = $this->where('product_id', $product_id)->select();
        foreach($configLimit as $v){
            $v['cpu'] = explode(',', $v['cpu']);
            $v['memory'] = !empty($v['memory']) ? explode(',', $v['memory']) : [];
            if(!in_array($param['cpu'], $v['cpu'])){
                continue;
            }
            // CPU内存限制
            if($v['type'] == 'cpu'){
                if(in_array($param['memory'], $v['memory']) || ($v['min_memory']<=$param['memory'] && $v['max_memory']>=$param['memory'])){
                    return ['status'=>400, 'msg'=>lang_plugins('cannot_select_this_config') ];
                }
            }else if($v['type'] == 'data_center' && isset($param['data_center_id']) ){
                if($param['data_center_id'] == $v['data_center_id']){
                    if(in_array($param['memory'], $v['memory']) || ($v['min_memory']<=$param['memory'] && $v['max_memory']>=$param['memory'])){
                        return ['status'=>400, 'msg'=>lang_plugins('cannot_select_this_config') ];
                    }
                }
            }else if($v['type'] == 'line' && isset($param['line_id']) ){
                if($param['line_id'] == $v['line_id']){
                    if(isset($param['bw']) && $v['min_bw']<=$param['bw'] && $v['max_bw']>=$param['bw']){
                        if(in_array($param['memory'], $v['memory']) || ($v['min_memory']<=$param['memory'] && $v['max_memory']>=$param['memory'])){
                            return ['status'=>400, 'msg'=>lang_plugins('cannot_select_this_config') ];
                        }
                    }
                }
            }
        }
        return ['status'=>200];
    }

    public function getAllConfigLimit($product_id){
        return $this->field('type,data_center_id,line_id,min_bw,max_bw,cpu,memory,min_memory,max_memory')
                    ->where('product_id', $product_id)
                    ->withAttr('cpu', function($value){
                        return explode(',', $value) ?? [];
                    })
                    ->withAttr('memory', function($value){
                        return !empty($value) ? explode(',', $value) : [];
                    })
                    ->select()
                    ->toArray();
    }


}