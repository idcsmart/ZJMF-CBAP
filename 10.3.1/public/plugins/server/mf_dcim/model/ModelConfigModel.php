<?php 
namespace server\mf_dcim\model;

use think\Model;
use app\common\model\ProductModel;
use server\mf_dcim\logic\ToolLogic;

/**
 * @title 型号配置模型
 * @use   server\mf_dcim\model\ModelConfigModel
 */
class ModelConfigModel extends Model{

	protected $name = 'module_mf_dcim_model_config';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_id'        => 'int',
        'name'              => 'string',
        'group_id'          => 'int',
        'cpu'              	=> 'string',
        'cpu_param'         => 'string',
        'memory'            => 'string',
        'disk'              => 'string',
        'create_time'       => 'int',
        'update_time'       => 'int',
    ];

    /**
     * 时间 2023-02-15
     * @title 添加型号配置
     * @desc 添加型号配置
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   string name - 配置名称 require
     * @param   int group_id - 分组ID require
     * @param   string cpu - 处理器 require
     * @param   string cpu_param - 处理器参数 require
     * @param   string memory - 内存 require
     * @param   string disk - 硬盘 require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格) require
     * @return  int id - 型号配置ID
     */
    public function modelConfigCreate($param){
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $productId = $ProductModel->id;
        $param['create_time'] = time();
       
        // 验证周期价格
        $duration = DurationModel::where('product_id', $productId)->column('id');

        $this->startTrans();
        try{
        	$modelConfig = $this->create($param, ['product_id','name','group_id','cpu','cpu_param','memory','disk','create_time']);

        	$priceArr = [];
            foreach($duration as $v){
                if(isset($param['price'][$v])){
                    $priceArr[] = [
                        'product_id'    => $productId,
                        'rel_type'    	=> 'model_config',
                        'rel_id'     	=> $modelConfig->id,
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
        	return ['status'=>400, 'msg'=>lang_plugins('create_fail')];
        }

        $description = lang_plugins('mf_dcim_log_add_model_config_success', [
            '{name}' => $param['name'],
        ]);
        active_log($description, 'product', $productId);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$modelConfig->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-02-15
     * @title 型号配置列表
     * @desc 型号配置列表
     * @author hh
     * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序(id,order)
     * @param   string sort - 升降序(asc,desc)
     * @param   int product_id - 商品ID
     * @return  array list - 列表数据
     * @return  int list[].id - 型号配置ID
     * @return  string list[].name - 配置名称
     * @return  int list[].group_id - 分组ID
     * @return  string list[].cpu - 处理器
     * @return  string list[].cpu_param - 处理器参数
     * @return  string list[].memory - 内存
     * @return  string list[].disk - 硬盘
     * @return  int count - 总条数
     */
    public function modelConfigList($param){
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id'])){
            $param['orderby'] = 'id';
        }
        $where = [];
        if(!empty($param['product_id'])){
            $where[] = ['product_id', '=', $param['product_id']];
        }

        $list = $this
                ->field('id,name,group_id,cpu,cpu_param,memory,disk')
                ->where($where)
                ->page($param['page'], $param['limit'])
                ->order($param['orderby'], $param['sort'])
                ->select()
                ->toArray();
    
        $count = $this
                ->where($where)
                ->count();
        
        return ['list'=>$list, 'count'=>$count];
    }

    /**
     * 时间 2023-02-15
     * @title 修改型号配置
     * @desc 修改型号配置
     * @author hh
     * @version v1
     * @param   int id - 型号配置ID require
     * @param   string name - 配置名称 require
     * @param   int group_id - 分组ID require
     * @param   string cpu - 处理器 require
     * @param   string cpu_param - 处理器参数 require
     * @param   string memory - 内存 require
     * @param   string disk - 硬盘 require
     * @param   object price - 周期价格(如{"5":"12"},5是周期ID,12是价格) require
     */
    public function modelConfigUpdate($param){
        $modelConfig = $this->find($param['id']);
        if(empty($modelConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_model_config_not_found')];
        }
        $productId = $modelConfig['product_id'];
        // 验证周期价格
        $duration = DurationModel::field('id,name')->where('product_id', $productId)->select()->toArray();

        $oldPrice = PriceModel::field('duration_id,price')->where('rel_type','model_config')->where('rel_id', $param['id'])->select()->toArray();
        $oldPrice = array_column($oldPrice, 'price', 'duration_id');

        $param['update_time'] = time();
        $this->startTrans();
        try{
        	$this->update($param, ['id'=>$modelConfig['id']], ['name','group_id','cpu','cpu_param','memory','disk','update_time']);

        	$priceArr = [];
            foreach($duration as $v){
                if(isset($param['price'][$v['id']])){
                    $priceArr[] = [
                        'product_id'    => $productId,
                        'rel_type'    	=> 'model_config',
                        'rel_id'     	=> $modelConfig->id,
                        'duration_id'   => $v['id'],
                        'price'         => $param['price'][$v['id']],
                    ];
                }
            }

            PriceModel::where('rel_type', 'model_config')->where('rel_id', $modelConfig->id)->delete();
            if(!empty($priceArr)){
                $PriceModel = new PriceModel();
                $PriceModel->insertAll($priceArr);
            }

            $this->commit();
        }catch(\Exception $e){
        	$this->rollback();
        	return ['status'=>400, 'msg'=>lang_plugins('update_fail')];
        }

        $des = [
            'name'      => lang_plugins('mf_dcim_model_config_name'),
            'group_id'  => lang_plugins('mf_dcim_model_config_group_id'),
            'cpu'       => lang_plugins('mf_dcim_model_config_cpu'),
            'cpu_param' => lang_plugins('mf_dcim_model_config_cpu_param'),
            'memory'    => lang_plugins('mf_dcim_model_config_memory'),
            'disk'      => lang_plugins('mf_dcim_model_config_disk'),
        ];
        $old = $modelConfig->toArray();

        // 每个周期的价格对比
        foreach($duration as $v){
            $des[ 'duration_'.$v['id'] ] = $v['name'].lang_plugins('mf_dcim_price');
            $old[ 'duration_'.$v['id'] ] = $oldPrice[ $v['id'] ] ?? lang_plugins('null');
            $param[ 'duration_'.$v['id'] ] = $param['price'][$v['id']] ?? lang_plugins('null');
        }
        $description = ToolLogic::createEditLog($old, $param, $des);
        if(!empty($description)){
            $description = lang_plugins('mf_dcim_log_modify_model_config_success', [
                '{detail}' => $description,
            ]);
            active_log($description, 'product', $productId);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-15
     * @title 删除型号配置
     * @desc 删除型号配置
     * @author hh
     * @version v1
     * @param   int id - 配置ID require
     */
    public function modelConfigDelete($id){
        $modelConfig = $this->find($id);
        if(empty($modelConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_model_config_not_found')];
        }
        
        $this->startTrans();
        try{
            $this->where('id', $id)->delete();
            PriceModel::where('rel_type', 'model_config')->where('rel_id', $id)->delete();

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('delete_fail')];
        }

        $description = lang_plugins('mf_dcim_log_delete_model_config_success', [
            '{name}' => $modelConfig['name'],
        ]);
        active_log($description, 'product', $modelConfig['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-20
     * @title 
     * @desc 
     * @author hh
     * @version v1
     * @param   string x             - x
     * @return  [type] [description]
     */
    public function modelConfigIndex($id){
        $modelConfig = $this->field('id,product_id,name,group_id,cpu,cpu_param,memory,disk')->find($id);
        if(empty($modelConfig)){
            return (object)[];
        }
        $modelConfig = $modelConfig->toArray();

        $duration = DurationModel::alias('d')
                    ->field('d.id,d.name,p.price')
                    ->leftJoin('module_mf_dcim_price p', 'p.rel_type="model_config" AND p.rel_id='.$id.' AND d.id=p.duration_id')
                    ->where('d.product_id', $modelConfig['product_id'])
                    ->withAttr('price', function($val){
                        return $val ?? '';
                    })
                    ->select()
                    ->toArray();

        unset($modelConfig['product_id']);

        $modelConfig['duration'] = $duration;
        return $modelConfig;
    }



}