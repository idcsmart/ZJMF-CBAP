<?php 
namespace server\mf_cloud\model;

use think\Model;
use app\common\model\ProductModel;
use server\mf_cloud\logic\ToolLogic;

/**
 * @title 性能限制模型
 * @use server\mf_cloud\model\DiskLimitModel
 */
class DiskLimitModel extends Model
{
	protected $name = 'module_mf_cloud_disk_limit';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_id'        => 'int',
        'type'              => 'string',
        'min_value'         => 'int',
        'max_value'         => 'int',
        'read_bytes'        => 'int',
        'write_bytes'       => 'int',
        'read_iops'         => 'int',
        'write_iops'        => 'int',
        'create_time'       => 'int',
    ];

    // type常量
    const SYSTEM_DISK = 'system';  // 系统盘
    const DATA_DISK = 'data';      // 数据盘

    /**
     * 时间 2023-02-01
     * @title 性能限制创建
     * @desc 性能限制创建
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   int param.min_value - 最小值 require
     * @param   int param.max_value - 最大值 require
     * @param   int param.read_bytes - 随机读 require
     * @param   int param.write_bytes - 随机写 require
     * @param   int param.read_iops - IOPS读 require
     * @param   int param.write_iops - IOPS写 require
     * @param   string type - 磁盘类型(system=系统盘,data=数据盘) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  int data.id - 性能限制ID
     */
    public function diskLimitCreate($param, $type)
    {
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        // 是否添加了相同配置
        $intersect = $this
                    ->where('product_id', $ProductModel->id)
                    ->where('type', $type)
                    ->where(sprintf('(min_value<=%d AND max_value>=%d)', $param['max_value'], $param['min_value']))
                    ->find();
        if(!empty($intersect)){
            return ['status'=>400, 'msg'=>lang_plugins('capacity_range_intersect')];
        }
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$ProductModel['id']]);
        $config = $config['data'];

        if($config['type'] == 'hyperv'){
            $param['read_bytes'] = 0;
            $param['write_bytes'] = 0;
        }else{
            $param['read_bytes'] = $param['read_bytes'] ?? 0;
            $param['write_bytes'] = $param['write_bytes'] ?? 0;
        }

        $param['product_id'] = $ProductModel->id;
        $param['type'] = $type;
        $param['create_time'] = time();

        $this->startTrans();
        try{
            $diskLimit = $this->create($param, ['product_id','type','min_value','max_value','read_bytes','write_bytes','read_iops','write_iops']);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('create_fail') ];
        }

        $diskType = [
            'system' => lang_plugins('system_disk'),
            'data' => lang_plugins('data_disk'),
        ];

        $description = lang_plugins('log_add_disk_limit_success', [
            '{disk_type}' => $diskType[ $type ],
            '{range}' => $param['min_value'].'-'.$param['max_value'],
        ]);
        active_log($description, 'product', $param['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$diskLimit->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-02-01
     * @title 性能限制列表
     * @desc 性能限制列表
     * @author hh
     * @version v1
     * @param   int param.page - 页数
     * @param   int param.limit - 每页条数
     * @param   string param.sort - 升降序(asc,desc)
     * @param   int param.product_id - 商品ID
     * @param   string type - 磁盘类型(system=系统盘,data=数据盘) require
     * @return  int list[].id - 性能限制ID
     * @return  int list[].min_value - 最小值
     * @return  int list[].max_value - 最大值
     * @return  int list[].read_bytes - 随机读
     * @return  int list[].write_bytes - 随机写
     * @return  int list[].read_iops - IOPS读
     * @return  int list[].write_iops - IOPS写
     * @return  int count - 总条数
     */
    public function diskLimitList($param, $type)
    {
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        $param['orderby'] = 'id';

        $where = [];
        if(!empty($param['product_id'])){
            $where[] = ['product_id', '=', $param['product_id']];
        }
        $where[] = ['type', '=', $type];

        $list = $this
                ->field('id,min_value,max_value,read_bytes,write_bytes,read_iops,write_iops')
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
     * 时间 2023-02-01
     * @title 性能限制修改
     * @desc 性能限制修改
     * @author hh
     * @version v1
     * @param   int param.id - 性能限制ID require
     * @param   int param.min_value - 最小值 require
     * @param   int param.max_value - 最大值 require
     * @param   int param.read_bytes - 随机读 require
     * @param   int param.write_bytes - 随机写 require
     * @param   int param.read_iops - IOPS读 require
     * @param   int param.write_iops - IOPS写 require
     * @param   string type - 磁盘类型(system=系统盘,data=数据盘) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function diskLimitUpdate($param, $type)
    {
        $diskLimit = $this->find($param['id']);
        if(empty($diskLimit) || $diskLimit['type'] != $type){
            return ['status'=>400, 'msg'=>lang_plugins('disk_limit_not_found')];
        }
        // 是否添加了相同配置
        $intersect = $this
                    ->where('product_id', $diskLimit['product_id'])
                    ->where('type', $type)
                    ->where(sprintf('(min_value<=%d AND max_value>=%d)', $param['max_value'], $param['min_value']))
                    ->where('id', '<>', $param['id'])
                    ->find();
        if(!empty($intersect)){
            return ['status'=>400, 'msg'=>lang_plugins('capacity_range_intersect')];
        }
        $ConfigModel = new ConfigModel();
        $config = $ConfigModel->indexConfig(['product_id'=>$diskLimit['product_id']]);
        $config = $config['data'];

        if($config['type'] == 'hyperv'){
            $param['read_bytes'] = 0;
            $param['write_bytes'] = 0;
        }else{
            $param['read_bytes'] = $param['read_bytes'] ?? 0;
            $param['write_bytes'] = $param['write_bytes'] ?? 0;
        }
        
        $this->startTrans();
        try{
            $this->update($param, ['id'=>$diskLimit->id], ['min_value','max_value','read_bytes','write_bytes','read_iops','write_iops']);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('update_fail') ];
        }

        $des = [
            'range' => lang_plugins('disk_limit_range'),
            'read_bytes' => lang_plugins('disk_limit_read_bytes'),
            'write_bytes' => lang_plugins('disk_limit_write_bytes)'),
            'read_iops' => lang_plugins('disk_limit_read_iops'),
            'write_iops' => lang_plugins('disk_limit_write_iops'),
        ];

        $old = $diskLimit->toArray();
        $old['range'] = $old['min_value'].'-'.$old['max_value'];

        $param['range'] = $param['min_value'].'-'.$param['max_value'];

        $diskType = [
            'system' => lang_plugins('system_disk'),
            'data' => lang_plugins('data_disk'),
        ];

        $description = ToolLogic::createEditLog($old, $param, $des);
        if(!empty($description)){
            $description = lang_plugins('log_update_disk_limit_success', [
                '{disk_type}' => $diskType[ $type ],
                '{detail}' => $description,
            ]);
            active_log($description, 'product', $diskLimit['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-01
     * @title 删除性能限制
     * @desc 删除性能限制
     * @author hh
     * @version v1
     * @param   int id - 性能限制ID require
     * @param   string type - 磁盘类型(system=系统盘,data=数据盘) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function diskLimitDelete($id, $type)
    {
        $diskLimit = $this->find($id);
        if(empty($diskLimit) || $diskLimit['type'] != $type){
            return ['status'=>400, 'msg'=>lang_plugins('disk_limit_not_found')];
        }
        $this->where('id', $id)->delete();

        $diskType = [
            'system' => lang_plugins('system_disk'),
            'data' => lang_plugins('data_disk'),
        ];

        $description = lang_plugins('log_delete_disk_limit_success', [
            '{disk_type}' => $diskType[ $diskLimit['type'] ],
            '{range}' => $diskLimit['min_value'].'-'.$diskLimit['max_value'],
        ]);
        active_log($description, 'product', $diskLimit['product_id']);


        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }



}