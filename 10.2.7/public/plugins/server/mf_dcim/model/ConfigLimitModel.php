<?php 
namespace server\mf_dcim\model;

use think\Model;
use app\common\model\ProductModel;
use server\mf_dcim\logic\ToolLogic;
use server\mf_dcim\validate\ConfigLimitValidate;

/**
 * @title 配置限制模型
 * @use server\mf_dcim\model\ConfigLimitModel
 */
class ConfigLimitModel extends Model{

	protected $name = 'module_mf_dcim_config_limit';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_id'        => 'int',
        'type'              => 'string',
        'data_center_id'    => 'int',
        'line_id'           => 'int',
        'min_bw'            => 'string',
        'max_bw'            => 'string',
        'min_flow'          => 'string',
        'max_flow'          => 'string',
        'model_config_id'   => 'string',
        'create_time'       => 'int',
    ];

    /**
     * 时间 2023-02-01
     * @title 添加配置限制
     * @desc 添加配置限制
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int data_center_id - 数据中心ID require
     * @param   array model_config_id - 型号配置ID require
     * @param   int line_id - 线路ID
     * @param   string min_bw - 带宽最小值
     * @param   string max_bw - 带宽最大值
     * @param   string min_flow - 流量最小值
     * @param   string max_flow - 流量最大值
     * @return  int id - 配置限制ID
     */
    public function configLimitCreate($param){
        // 验证CPU
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_mf_dcim_module')];
        }
        $dataCenter = DataCenterModel::find($param['data_center_id']);
        if(empty($dataCenter) || $dataCenter['product_id'] != $ProductModel->id){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_data_center_not_found')];
        }
        $insert = [
            'product_id' => $ProductModel->id,
            'data_center_id' => $dataCenter->id,
        ];
        if(isset($param['line_id']) && !empty($param['line_id'])){
            $line = LineModel::find($param['line_id'] ?? 0);
            if(empty($line) || $line['data_center_id'] != $insert['data_center_id']){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found')];
            }
            $insert['line_id'] = $line['id'];

            if($line['bill_type'] == 'bw'){
                $insert['min_bw'] = $param['min_bw'] ?? '';
                $insert['max_bw'] = $param['max_bw'] ?? '';
            }else{
                $insert['min_flow'] = $param['min_flow'] ?? '';
                $insert['max_flow'] = $param['max_flow'] ?? '';
            }
        }
        // 验证型号配置
        $modelConfig = ModelConfigModel::field('id,name')->whereIn('id', $param['model_config_id'])->select()->toArray();
        if(count($modelConfig) != count($param['model_config_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_model_config_not_found')];
        }
        $insert['model_config_id'] = implode(',', array_column($modelConfig, 'id'));

        // 是否添加了相同配置
        $same = $this
                ->where($insert)
                ->find();
        if(!empty($same)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_already_add_the_same_config_limit')];
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

        $description = lang_plugins('mf_dcim_log_add_config_limit_success', [
            '{data_center}' => $dataCenter->getDataCenterName(),
            '{name}' => implode(',', array_column($modelConfig, 'name')),
        ]);
        active_log($description, 'product', $param['product_id']);

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
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序字段(id)
     * @param   string sort - 升降序
     * @param   int product_id - 商品ID
     * @return  array list - 列表数据
     * @return  int list[].id - 配置限制ID
     * @return  int list[].data_center_id - 数据中心ID
     * @return  int list[].line_id - 线路ID
     * @return  string list[].min_bw - 带宽最小值
     * @return  string list[].max_bw - 带宽最大值
     * @return  string list[].min_flow - 流量最小值
     * @return  string list[].max_flow - 流量最大值
     * @return  array list[].model_config_id - 型号配置ID
     * @return  string list[].line_name - 线路名称
     * @return  int list[].country_id - 国家ID
     * @return  string list[].city - 城市
     * @return  string list[].area - 区域
     * @return  int list[].model_config[].id - 型号配置ID
     * @return  string list[].model_config[].name - 型号配置名称
     * @return  int count - 总条数
     */
    public function configLimitList($param){
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id'])){
            $param['orderby'] = 'id';
        }
        $list = [];
        $count = 0;

        $where = [];
        if(!empty($param['product_id'])){
            $where[] = ['cl.product_id', '=', $param['product_id']];
        }
        $list = $this
            ->alias('cl')
            ->field('cl.id,cl.data_center_id,cl.line_id,cl.min_bw,cl.max_bw,cl.min_flow,cl.max_flow,cl.model_config_id,l.name line_name,l.bill_type,dc.country_id,dc.city,dc.area,c.iso,c.name_zh country_name')
            ->where($where)
            ->leftJoin('module_mf_dcim_line l', 'cl.line_id=l.id')
            ->leftJoin('module_mf_dcim_data_center dc', 'cl.data_center_id=dc.id')
            ->leftJoin('country c', 'dc.country_id=c.id')
            ->page($param['page'], $param['limit'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        if(!empty($list)){
            // 获取所有型号配置ID
            $modelConfig = ModelConfigModel::field('id,name')->select()->toArray();
            $modelConfig = array_column($modelConfig, 'name', 'id');

            foreach($list as $k=>$v){
                $v['model_config_id'] = explode(',', $v['model_config_id']);

                $arr = [];
                $modelConfigId = [];
                foreach($v['model_config_id'] as $vv){
                    if(isset($modelConfig[$vv])){
                        $modelConfigId[] = (int)$vv;
                        $arr[] = [
                            'id' => (int)$vv,
                            'name' => $modelConfig[$vv],
                        ];
                    }
                }
                $list[$k]['model_config'] = $arr;
                $list[$k]['model_config_id'] = $modelConfigId;
            }
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
     * @param   int data_center_id - 数据中心ID require
     * @param   array model_config_id - 型号配置ID require
     * @param   int line_id - 线路ID
     * @param   string min_bw - 带宽最小值
     * @param   string max_bw - 带宽最大值
     * @param   string min_flow - 流量最小值
     * @param   string max_flow - 流量最大值
     */
    public function configLimitUpdate($param){
        $configLimit = $this->find($param['id'] ?? 0);
        if(empty($configLimit)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_config_limit_not_found')];
        }
        $productId = $configLimit['product_id'];

        $dataCenter = DataCenterModel::find($param['data_center_id']);
        if(empty($dataCenter) || $dataCenter['product_id'] != $productId){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_data_center_not_found')];
        }
        $update = [
            'data_center_id' => $dataCenter->id,
        ];
        if(isset($param['line_id']) && !empty($param['line_id'])){
            $line = LineModel::find($param['line_id'] ?? 0);
            if(empty($line) || $line['data_center_id'] != $update['data_center_id']){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_line_not_found')];
            }
            $update['line_id'] = $line['id'];

            if($line['bill_type'] == 'bw'){
                $update['min_bw'] = $param['min_bw'] ?? '';
                $update['max_bw'] = $param['max_bw'] ?? '';
                $update['min_flow'] = '';
                $update['max_flow'] = '';
            }else{
                $update['min_flow'] = $param['min_flow'] ?? '';
                $update['max_flow'] = $param['max_flow'] ?? '';
                $update['min_bw'] = '';
                $update['max_bw'] = '';
            }
        }else{
            $update['line_id'] = 0;
            $update['min_bw'] = '';
            $update['max_bw'] = '';
            $update['min_flow'] = '';
            $update['max_flow'] = '';
        }
        // 验证型号配置
        $modelConfig = ModelConfigModel::field('id,name')->whereIn('id', $param['model_config_id'])->select()->toArray();
        if(count($modelConfig) != count($param['model_config_id'])){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_model_config_not_found')];
        }
        $update['model_config_id'] = implode(',', array_column($modelConfig, 'id') );
        
        // 是否添加了相同配置
        $same = $this
                ->where($update)
                ->where('id', '<>', $configLimit['id'])
                ->find();
        if(!empty($same)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_already_add_the_same_config_limit')];
        }

        $this->startTrans();
        try{
            $this->update($update, ['id'=>$configLimit['id']]);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('update_fail').$e->getMessage() ];
        }

        // 获取old
        $oldDataCenter  = DataCenterModel::find($configLimit['data_center_id']);
        $oldLine        = !empty($configLimit['line_id']) ? LineModel::where('id', $configLimit['line_id'])->value('name') : lang_plugins('mf_dcim_none');
        $oldModelConfig = ModelConfigModel::whereIn('id', $configLimit['model_config_id'])->column('name') ?? [];

        $des = [
            'data_center' => lang_plugins('mf_dcim_data_center'),
            'name'        => lang_plugins('mf_dcim_model_config'),
            'line'        => lang_plugins('mf_dcim_line'),
            'bw'          => lang_plugins('mf_dcim_bw'),
            'flow'        => lang_plugins('mf_dcim_flow'),
        ];
        $old = [
            'data_center'   => !empty($oldDataCenter) ? $oldDataCenter->getDataCenterName() : lang_plugins('mf_dcim_none'),
            'name'          => implode(',', $oldModelConfig),
            'line'          => $oldLine,
            'bw'            => is_numeric($configLimit['min_bw']) ? $configLimit['min_bw'].'-'.$configLimit['max_bw'] : lang_plugins('mf_dcim_none'),
            'flow'          => is_numeric($configLimit['min_flow']) ? $configLimit['min_flow'].'-'.$configLimit['max_flow'] : lang_plugins('mf_dcim_none'),
        ];
        $new = [
            'data_center'   => $dataCenter->getDataCenterName(),
            'name'          => implode(',', array_column($modelConfig, 'name') ),
            'line'          => $line['name'] ?? lang_plugins('mf_dcim_none'),
            'bw'            => is_numeric($update['min_bw']) ? $update['min_bw'].'-'.$update['max_bw'] : lang_plugins('mf_dcim_none'),
            'flow'          => is_numeric($update['min_flow']) ? $update['min_flow'].'-'.$update['max_flow'] : lang_plugins('mf_dcim_none'),
        ];

        $description = ToolLogic::createEditLog($old, $new, $des);
        if(!empty($description)){
            $description = lang_plugins('mf_dcim_log_modify_config_limit_success', [
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
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_config_limit_not_found')];
        }

        // 获取old
        $oldDataCenter  = DataCenterModel::find($configLimit['data_center_id']);
        $oldModelConfig = ModelConfigModel::whereIn('id', $configLimit['model_config_id'])->column('name') ?? [];

        $description = lang_plugins('mf_dcim_log_delete_config_limit_success', [
            '{data_center}' => !empty($oldDataCenter) ? $oldDataCenter->getDataCenterName() : lang_plugins('mf_dcim_none'),
            '{name}'        => implode(',', $oldModelConfig),
        ]);
        active_log($description, 'product', $configLimit['product_id']);

        $this->where('id', $id)->delete();

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
     * @param   int param.model_config_id   - 型号配置ID
     * @param   int param.data_center_id   - 数据中心ID
     * @param   int param.line_id   - 线路ID
     * @param   int param.bw   - 带宽
     * @param   int param.flow   - 流量
     */
    public function checkConfigLimit($product_id, $param){
        $configLimit = $this->where('product_id', $product_id)->where('data_center_id', $param['data_center_id'])->select();
        foreach($configLimit as $v){
            $v['model_config_id'] = explode(',', $v['model_config_id']);
            if(!in_array($param['model_config_id'], $v['model_config_id'])){
                continue;
            }
            if($v['line_id']>0 && $param['line_id'] != $v['line_id']){
                continue;
            }
            if(is_numeric($v['min_bw'])){
                if(isset($param['bw']) && is_numeric($param['bw']) && $v['min_bw']<=$param['bw'] && $v['max_bw']>=$param['bw']){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_cannot_select_this_config') ];
                }
            }else if(is_numeric($v['min_flow'])){
                if(isset($param['flow']) && is_numeric($param['flow']) && $v['min_flow']<=$param['flow'] && $v['max_flow']>=$param['flow']){
                    return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_cannot_select_this_config') ];
                }
            }else{
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_cannot_select_this_config') ];
            }
        }
        return ['status'=>200];
    }


}