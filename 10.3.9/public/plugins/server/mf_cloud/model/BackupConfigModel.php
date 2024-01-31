<?php 
namespace server\mf_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\mf_cloud\logic\ToolLogic;

/**
 * @title 备份配置模型
 * @use server\mf_cloud\model\BackupConfigModel
 */
class BackupConfigModel extends Model{

	protected $name = 'module_mf_cloud_backup_config';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'num'           => 'int',
        'type'          => 'string',
        'price'         => 'float',
        'product_id'    => 'int',
    ];

    /**
     * 时间 2023-02-01
     * @title 备份管理设置列表
     * @desc 备份管理设置列表
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int type - 类型(snap=快照,backup=备份) require
     * @return  
     */
    public function backupConfigList($param){
        $data = [];
        $count = 0;

        if(isset($param['product_id']) && !empty($param['product_id'])){

            $where = [];
            $where[] = ['product_id', '=', $param['product_id']];
            $where[] = ['type', '=', $param['type']];

            $data = $this
                    ->field('id,num,price')
                    ->where($where)
                    ->order('num', 'asc')
                    ->select()
                    ->toArray();

            $count = $this->where($where)->count();
        }
        return ['list'=>$data, 'count'=>$count];
    }

    /**
     * 时间 2022-09-23
     * @title 创建备份管理设置
     * @desc 创建备份管理设置
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID
     * @param   int param.num - 允许的数量
     * @param   string param.type - 类型(snap=快照,backup=备份)
     * @param   float param.price - 价格
     * @return  int data.id - 创建的ID
     */
    public function createBackupConfig($param){
        $ProductModel = ProductModel::find($param['product_id'] ?? 0);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }

        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];
        $where[] = ['num', '=', $param['num']];
        $where[] = ['type', '=', $param['type']];

        // 是否已填加
        $add = $this->where($where)->find();
        if(!empty($add)){
            return ['status'=>400, 'msg'=>lang_plugins('already_add_the_same_number')];
        }
        $count = $this->where($where)->count();
        if($count >=5){
            return ['status'=>400, 'msg'=>lang_plugins('over_max_allow_num')];
        }
        $param['create_time'] = time();
        
        $this->startTrans();
        try{
            $backupConfig = $this->create($param, ['num','type','price','product_id']);

            $count = $this->where($where)->count();
            if($count >=6 ){
                throw new \Exception(lang_plugins('over_max_allow_num'));
            }
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }
        $type = [
            'snap'=>lang_plugins('snap'),
            'backup'=>lang_plugins('backup'),
        ];

        $description = lang_plugins('log_add_backup_config_success', [
            '{type}'=>$type[$param['type']] ?? '',
            '{num}'=>$param['num'],
            '{price}'=>$param['price'],
        ]);
        active_log($description, 'product', $ProductModel['id']);
        
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$backupConfig->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2022-09-23
     * @title 修改备份/快照设置
     * @desc 修改备份/快照设置
     * @author hh
     * @version v1
     * @param   int param.id - 设置ID require
     * @param   int param.num - 允许数量 require
     * @param   float param.price - 价格 require
     */
    public function updateBackupConfig($param){
        $backupConfig = $this->find($param['id']);
        if(empty($backupConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('')];
        }

        $where = [];
        $where[] = ['product_id', '=', $backupConfig['product_id']];
        $where[] = ['num', '=', $param['num']];
        $where[] = ['type', '=', $backupConfig['type']];
        $where[] = ['id', '<>', $param['id']];

        // 是否已填加
        $add = $this->where($where)->find();
        if(!empty($add)){
            return ['status'=>400, 'msg'=>lang_plugins('already_add_the_same_number')];
        }
        
        $this->startTrans();
        try{
            $this->update($param, ['id'=>$param['id']], ['num','price']);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }

        // 日志
        $type = [
            'snap'=>lang_plugins('snap'),
            'backup'=>lang_plugins('backup'),
        ];

        $desc = [
            'num'=>lang_plugins('num'),
            'price'=>lang_plugins('price'),
        ];
        
        $description = ToolLogic::createEditLog($backupConfig, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_backup_config_success', [
                '{type}'=>$type[$backupConfig['type']] ?? '',
                '{detail}'=>$description,
            ]);

            active_log($description, 'product', $ProductModel['id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }


    /**
     * 时间 2022-09-23
     * @title 删除允许的数量 
     * @desc 删除允许的数量
     * @author hh
     * @version v1
     * @param   int id - 设置ID require
     */
    public function deleteBackupConfig($id){
        $backupConfig = $this->find($id);
        if(empty($backupConfig)){
            return ['status'=>400, 'msg'=>lang_plugins('id_error')];
        }
        
        $this->startTrans();
        try{
            $backupConfig->delete();

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>$e->getMessage()];
        }
        $type = [
            'snap'=>lang_plugins('snap'),
            'backup'=>lang_plugins('backup'),
        ];

        $description = lang_plugins('log_delete_backup_config_success', [
            '{type}'=>$type[$backupConfig['type']] ?? '',
            '{num}'=>$backupConfig['num'],
            '{price}'=>$backupConfig['price'],
        ]);
        active_log($description, 'product', $backupConfig['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2022-10-12
     * @title 保存商品备份/快照配置
     * @desc 保存商品备份/快照配置
     * @author hh
     * @version v1
     * @param   int $product_id - 商品ID
     * @param   array $data     - 数据数组
     */
    public function saveBackupConfig($product_id, $data, $type = 'backup'){
        $old = $this->field('num,price')
                    ->where('product_id', $product_id)
                    ->where('type', $type)
                    ->select()
                    ->toArray();
        $old = array_column($old, 'price', 'num');

        $backup_data = [];
        foreach($data as $v){
            $backup_data[] = [
                'num'=>$v['num'],
                'type'=>$type,
                'price'=>$v['price'],
                'product_id'=>$product_id,
            ];
        }

        $typeDes = [
            'snap'=>lang_plugins('snap'),
            'backup'=>lang_plugins('backup'),
        ];

        $this->startTrans();
        try{
            $this->where('product_id', $product_id)->where('type', $type)->delete();
            $this->insertAll($backup_data);

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('update_failed')];
        }

        $description = '';
        foreach($backup_data as $v){
            if(isset($old[$v['num']])){
                if($v['price'] != $old[$v['num']]){
                    $description .= lang_plugins('modify_backup_price', [
                        '{type}'=>$typeDes[$type],
                        '{num}'=>$v['num'],
                        '{old_price}'=>$old[$v['num']],
                        '{new_price}'=>$v['price'],
                    ]);
                }
                unset($old[$v['num']]);
            }else{
                $description .= lang_plugins('add_backup_num', [
                    '{type}'=>$typeDes[$type],
                    '{num}'=>$v['num'],
                ]);
            }
        }

        if(!empty($old)){
            $description .= lang_plugins('del_backup_num', [
                '{type}'=>$typeDes[$type],
                '{num}'=>implode(',', array_keys($old)),
            ]);
        }
        return ['status'=>200, 'msg'=>lang_plugins('success_message'), 'data'=>['desc'=>$description] ];
    }










}