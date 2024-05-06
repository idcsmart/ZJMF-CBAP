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
class ConfigLimitModel extends Model
{
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
        'image_id'          => 'int',
        'create_time'       => 'int',
    ];

    /**
     * 时间 2023-02-01
     * @title 添加配置限制
     * @desc 添加配置限制
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   string param.type - 类型(cpu=CPU与内存限制,data_center=数据中心与计算限制,image=操作系统与计算限制) require
     * @param   int param.data_center_id - 数据中心ID requireIf,type=data_center
     * @param   array param.cpu - CPU核心数 require
     * @param   array param.memory - 内存容量 
     * @param   int param.min_memory - 内存最小值
     * @param   int param.max_memory - 内存最大值
     * @param   int param.image_id - 操作系统ID requireIf,type=image
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - 配置限制ID
     */
    public function configLimitCreate($param)
    {
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

            $where[] = ['min_memory', '=', $param['min_memory']];
            $where[] = ['max_memory', '=', $param['max_memory']];
        }

        if($param['type'] == 'cpu'){

        }else if($param['type'] == 'data_center'){
            $dataCenter = DataCenterModel::find($param['data_center_id']);
            if(empty($dataCenter) || $dataCenter['product_id'] != $ProductModel->id){
                return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
            }
            $insert['data_center_id'] = $param['data_center_id'];

            $where[] = ['data_center_id', '=', $param['data_center_id']];
        }else if($param['type'] == 'image'){
            $image = ImageModel::find($param['image_id']);
            if(empty($image) || $image['product_id'] != $ProductModel['id']){
                return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
            }
            $insert['image_id'] = $param['image_id'];

            $where[] = ['image_id', '=', $param['image_id']];
        }
        // 是否添加了相同配置
        $same = $this
                ->where($where)
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
            '{image}'       => isset($image) && !empty($image) ? $image['name'] : lang_plugins('null'),
            '{cpu}'         => $insert['cpu'],
            '{memory}'      => !empty($insert['memory']) ? $insert['memory'] : $insert['min_memory'].'-'.$insert['max_memory'],
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
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   string param.orderby - 排序字段(id)
     * @param   string param.sort - 升降序(asc,desc)
     * @param   int param.product_id - 商品ID
     * @param   string param.type - 类型(cpu=CPU与内存限制,data_center=数据中心与计算限制,image=操作系统与计算限制) require
     * @return  int list[].id - 配置限制ID
     * @return  array list[].cpu - CPU
     * @return  string list[].memory - 内存(为空表示范围,多个逗号分隔)
     * @return  int list[].min_memory - 内存最小值
     * @return  int list[].max_memory - 内存最大值
     * @return  int list[].data_center_id - 数据中心ID
     * @return  string list[].data_center - 数据中心名称
     * @return  int list[].country_id - 国家ID
     * @return  string list[].city - 城市
     * @return  string list[].area - 区域
     * @return  int list[].image_id - 操作系统ID
     * @return  string list[].image_name - 操作系统名称
     * @return  int list[].image_group_id - 操作系统分组ID
     * @return  int count - 总条数
     */
    public function configLimitList($param)
    {
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
        }else if($param['type'] == 'image'){
            $list = $this
                ->alias('cl')
                ->field('cl.id,cl.cpu,cl.memory,cl.min_memory,cl.max_memory,cl.image_id,i.name image_name,i.image_group_id')
                ->where($where)
                ->leftJoin('module_mf_cloud_image i', 'cl.image_id=i.id')
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
     * @param   int param.id - 限制配置ID require
     * @param   int param.data_center_id - 数据中心ID requireIf,type=data_center
     * @param   array param.cpu - CPU核心数 require
     * @param   array param.memory - 内存容量 
     * @param   int param.min_memory - 内存最小值
     * @param   int param.max_memory - 内存最大值
     * @param   int param.image_id - 操作系统ID requireIf,type=image
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function configLimitUpdate($param)
    {
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

            $where[] = ['min_memory', '=', $param['min_memory']];
            $where[] = ['max_memory', '=', $param['max_memory']];
        }

        if($param['type'] == 'cpu'){

        }else if($param['type'] == 'data_center'){
            $dataCenter = DataCenterModel::find($param['data_center_id']);
            if(empty($dataCenter) || $dataCenter['product_id'] != $configLimit['product_id']){
                return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
            }
            $insert['data_center_id'] = $param['data_center_id'];

            $where[] = ['data_center_id', '=', $param['data_center_id']];
        }else if($param['type'] == 'image'){
            $image = ImageModel::find($param['image_id']);
            if(empty($image) || $image['product_id'] != $configLimit['product_id']){
                return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
            }
            $insert['image_id'] = $param['image_id'];

            $where[] = ['image_id', '=', $param['image_id']];
        }

        // 是否添加了相同配置
        $same = $this
                ->where($where)
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
            'data_center'   => lang_plugins('data_center'),
            'image'         => lang_plugins('mf_cloud_os'),
            'cpu'           => 'CPU',
            'memory'        => lang_plugins('memory'),
        ];
        $old = [
            'data_center'   => empty($configLimit['data_center_id']) ? lang_plugins('null') : DataCenterModel::find($configLimit['data_center_id'])->getDataCenterName(),
            'image'         => empty($configLimit['image_id']) ? lang_plugins('null') : ImageModel::where('id', $configLimit['image_id'])->value('name'),
            'cpu'           => $configLimit['cpu'],
            'memory'        => !empty($configLimit['memory']) ? $configLimit['memory'] : $configLimit['min_memory'].'-'.$configLimit['max_memory'],
        ];
        $new = [
            'data_center'   => isset($dataCenter) && !empty($dataCenter) ? $dataCenter->getDataCenterName() : lang_plugins('null'),
            'image'         => isset($image) && !empty($image) ? $image['name'] : lang_plugins('null'),
            'cpu'           => $insert['cpu'],
            'memory'        => !empty($insert['memory']) ? $insert['memory'] : $insert['min_memory'].'-'.$insert['max_memory'],
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
     * @param   int id - 配置限制ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function configLimitDelete($id)
    {
        $configLimit = $this->find($id);
        if(empty($configLimit)){
            return ['status'=>400, 'msg'=>lang_plugins('config_limit_not_found')];
        }
        $this->where('id', $id)->delete();

        $dataCenter = DataCenterModel::find($configLimit['data_center_id']);
        $image = ImageModel::find($configLimit['image_id']);

        $description = lang_plugins('log_delete_config_limit_success', [
            '{data_center}' => isset($dataCenter) && !empty($dataCenter) ? $dataCenter->getDataCenterName() : lang_plugins('null'),
            '{image}'       => isset($image) && !empty($image) ? $image['name'] : lang_plugins('null'),
            '{cpu}'         => $configLimit['cpu'],
            '{memory}'      => !empty($configLimit['memory']) ? $configLimit['memory'] : $configLimit['min_memory'].'-'.$configLimit['max_memory'],
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
     * @param   int product_id - 商品ID require
     * @param   int param.cpu - cpu
     * @param   int param.memory - 内存
     * @param   int param.data_center_id - 数据中心ID
     * @param   int param.image_id - 操作系统ID
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function checkConfigLimit($product_id, $param)
    {
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
            }else if($v['type'] == 'image' && isset($param['image_id'])){
                if($param['image_id'] == $v['image_id']){
                    if(in_array($param['memory'], $v['memory']) || ($v['min_memory']<=$param['memory'] && $v['max_memory']>=$param['memory'])){
                        return ['status'=>400, 'msg'=>lang_plugins('cannot_select_this_config') ];
                    }
                }
            }
        }
        return ['status'=>200];
    }

    /**
     * 时间 2024-02-19
     * @title 获取配置限制规则
     * @desc  获取配置限制规则
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  string [].type - 类型(cpu=CPU与内存限制,data_center=数据中心与计算限制,image=操作系统与计算限制)
     * @return  int [].data_center_id - 数据中心ID
     * @return  array [].cpu - CPU
     * @return  array [].memory - 内存
     * @return  int [].min_memory - 最小内存
     * @return  int [].max_memory - 最大内存
     * @return  int [].image_id - 操作系统ID
     * @return  int [].image_group_id - 操作系统分组ID
     */
    public function getAllConfigLimit($product_id)
    {
        return $this
            ->alias('cl')
            ->field('cl.type,cl.data_center_id,cl.cpu,cl.memory,cl.min_memory,cl.max_memory,cl.image_id,i.image_group_id')
            ->where('cl.product_id', $product_id)
            ->leftJoin('module_mf_cloud_image i', 'cl.type="image" AND cl.image_id=i.id')
            ->withAttr('cpu', function($value){
                return explode(',', $value) ?? [];
            })
            ->withAttr('memory', function($value){
                return !empty($value) ? explode(',', $value) : [];
            })
            ->withAttr('image_group_id', function($value){
                return $value ?? 0;
            })
            ->select()
            ->toArray();
    }


}