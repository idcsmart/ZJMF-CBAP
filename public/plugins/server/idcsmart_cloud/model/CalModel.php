<?php 
namespace server\idcsmart_cloud\model;

use think\Model;
use think\db\Query;
use server\idcsmart_cloud\validate\CalValidate;
use server\idcsmart_cloud\logic\ToolLogic;

class CalModel extends Model{

	protected $name = 'module_idcsmart_cloud_cal';

    // 设置字段信息
    protected $schema = [
        'id'                				 => 'int',
        'product_id'                         => 'int',
        'name'              				 => 'string',
        'module_idcsmart_cloud_cal_group_id' => 'int',
        'cpu'        						 => 'int',
        'memory'        					 => 'int',
        'disk_size'                          => 'int',
        'description'        			     => 'string',
        'other_param'        				 => 'string',
        'price'        						 => 'float',
        'order'                              => 'int',
        'create_time'       				 => 'int',
        'update_time'       				 => 'int',
    ];
	
    /**
     * 时间 2022-06-13
     * @title 计算型号列表
     * @desc 计算型号列表
     * @author hh
     * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   string param.orderby - 排序字段(id,name,cpu,memory,disk_size,order,price)
     * @param   string param.sort - 升降序
     * @param   int param.module_idcsmart_cloud_cal_group_id - 搜索计算型号分组ID
     * @param   int param.product_id - 搜索商品ID
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 计算型号ID
     * @return  string data.list[].name - 名称
     * @return  int data.list[].module_idcsmart_cloud_cal_group_id - 计算型号分组ID
     * @return  int data.list[].cpu - CPU
     * @return  int data.list[].memory - 内存(MB)
     * @return  int data.list[].disk_size - 硬盘(GB)
     * @return  int data.list[].order - 排序
     * @return  string data.list[].other_param - 其他参数
     * @return  string data.list[].description - 描述
     * @return  string data.list[].price - 价格
     * @return  int data.list[].create_time - 创建时间
     * @return  int data.list[].update_time - 修改时间
     * @return  string data.list[].group_name - 计算型号分组名称
     * @return  int data.count - 总条数
     */
    public function calList($param)
    {
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id','name','cpu','memory','disk_size','order','price'])){
            $param['orderby'] = 'c.id';
        }else{
            $param['orderby'] = 'c.'.$param['orderby'];
        }

        $where = function (Query $query) use($param) {
            if(!empty($param['keywords'])){
                $query->where('c.name|cg.name', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['module_idcsmart_cloud_cal_group_id'])){
                $query->where('c.module_idcsmart_cloud_cal_group_id', $param['module_idcsmart_cloud_cal_group_id']);
            }
            if(!empty($param['product_id'])){
                $query->where('c.product_id', $param['product_id']);
            }
        };

        $cal = $this
                ->alias('c')
                ->field('c.*,cg.name group_name')
                ->leftjoin('idcsmart_module_idcsmart_cloud_cal_group cg', 'c.module_idcsmart_cloud_cal_group_id=cg.id')
                ->where($where)
                ->group('c.id')
                ->limit($param['limit'])
                ->page($param['page'])
                ->order($param['orderby'], $param['sort'])
                ->withAttr('price', function($value){
                    return amount_format($value);
                })
                ->select()
                ->toArray();

        $count = $this
                ->alias('c')
                ->where($where)
                ->group('c.id')
                ->count();

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'=>$cal,
                'count'=>$count
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-06-13
     * @title 创建计算型号
     * @desc 创建计算型号
     * @author hh
     * @version v1
     * @param   string param.name - 显示名称 require
     * @param   int param.module_idcsmart_cloud_cal_group_id - 分组ID require
     * @param   int param.cpu - CPU require
     * @param   int param.memory - 内存(MB) require
     * @param   int param.disk_size - 硬盘(GB) require
     * @param   int param.price - 单价 require
     * @param   string param.description - 描述
     * @param   string param.other_param - 其他参数
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.id - 计算型号ID
     */
    public function createCal($param)
    {
        $calGroup = CalGroupModel::find((int)$param['module_idcsmart_cloud_cal_group_id']);
        if(empty($calGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('cal_group_not_found')];
        }
        $param['create_time'] = time();
        $param['order'] = $param['order'] ?? 0;
        $param['description'] = $param['description'] ?? 0;
        $param['other_param'] = $param['other_param'] ?? '';
        $param['product_id'] = $calGroup['product_id'];

        $cal = $this->create($param, ['name','module_idcsmart_cloud_cal_group_id','cpu','memory','disk_size','description','other_param','price','product_id','order','create_time']);

        $description = lang_plugins('log_create_cal_success', ['{name}'=>$param['name']]);
        active_log($description, 'product', $calGroup['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$cal->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2022-06-13
     * @title 修改计算型号
     * @desc 修改计算型号
     * @author hh
     * @version v1
     * @param   int param.id - 计算型号ID require
     * @param   string param.name - 显示名称 require
     * @param   int param.module_idcsmart_cloud_cal_group_id - 分组ID require
     * @param   int param.cpu - CPU require
     * @param   int param.memory - 内存(MB) require
     * @param   int param.disk_size - 硬盘(GB) require
     * @param   int param.price - 单价 require
     * @param   string param.description - 描述
     * @param   string param.other_param - 其他参数
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function updateCal($param)
    {
        $cal = $this->find($param['id']);
        if(empty($cal)){
            return ['status'=>400, 'msg'=>lang_plugins('cal_not_found')];
        }
        $calGroup = CalGroupModel::find((int)$param['module_idcsmart_cloud_cal_group_id']);
        if(empty($calGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('cal_group_not_found')];
        }
        if($cal['product_id'] != $calGroup['product_id']){
            return ['status'=>400, 'msg'=>lang_plugins('cannot_select_other_product_cal_group')];
        }
        $param['update_time'] = time();

        $this->update($param, ['id'=>$param['id']], ['name','module_idcsmart_cloud_cal_group_id','cpu','memory','disk_size','price','description','other_param','update_time','order']);

        $desc = [
            'name'=>lang_plugins('name'),
            'module_idcsmart_cloud_cal_group_id'=>lang_plugins('cal_group'),
            'cpu'=>'CPU',
            'memory'=>lang_plugins('memory'),
            'disk_size'=>lang_plugins('disk'),
            'price'=>lang_plugins('price'),
            'description'=>lang_plugins('description'),
            'other_param'=>lang_plugins('other_param'),
        ];

        $cal['module_idcsmart_cloud_cal_group_id'] = CalGroupModel::where('id', $cal['module_idcsmart_cloud_cal_group_id'])->value('name');
        $param['module_idcsmart_cloud_cal_group_id'] = $calGroup['name'];

        $description = ToolLogic::createEditLog($cal, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_cal_success', [
                '{name}'=>$cal['name'],
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $calGroup['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2022-06-10
     * @title 删除计算型号
     * @desc 删除计算型号
     * @author hh
     * @version v1
     * @param   int id - 计算型号ID require
     */
    public function deleteCal($id)
    {
        $cal = $this->find($id);
        if(empty($cal)){
            return ['status'=>400, 'msg'=>lang_plugins('cal_not_found')];
        }
        if($cal->isUse()){
            return ['status'=>400, 'msg'=>lang_plugins('cal_is_using_cannot_delete')];
        }

        $this->startTrans();
        try{
            $cal->delete();

            // TODO 删除其他关联
            
            
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        $description = lang_plugins('log_delete_cal_success', ['{name}'=>$cal['name']]);
        active_log($description, 'product', $cal['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }
    
    /**
     * 时间 2022-06-13
     * @title 修改计算型号排序
     * @desc 修改计算型号排序
     * @author hh
     * @version v1
     * @param   int param.id - 计算型号ID required
     * @param   int param.order - 排序 required
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function updateOrder($param)
    {
        $cal = $this->find($param['id']);
        if(empty($cal)){
            return ['status'=>400, 'msg'=>lang_plugins('cal_not_found')];
        }
        $param['update_time'] = time();

        $this->update($param, ['id'=>$param['id']], ['order','update_time']);

        $desc = [
            'order'=>lang_plugins('order'),
        ];

        $description = ToolLogic::createEditLog($cal, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_cal_success', [
                '{name}'=>$cal['name'],
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $cal['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2022-06-15
     * @title 计算型号是否正在使用
     * @desc 计算型号是否正在使用
     * @author hh
     * @version v1
     * @return  bool
     */
    public function isUse(){
        $package = PackageModel::where('module_idcsmart_cloud_cal_id', $this->id)->find();
        if(!empty($package)){
            return true;
        }
        return false;
    }
    



}

