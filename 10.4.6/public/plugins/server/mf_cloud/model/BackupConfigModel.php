<?php 
namespace server\mf_cloud\model;

use think\Model;
use app\common\model\ProductModel;
use server\mf_cloud\logic\ToolLogic;

/**
 * @title 备份配置模型
 * @use server\mf_cloud\model\BackupConfigModel
 */
class BackupConfigModel extends Model
{
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
     * @param   int param.product_id - 商品ID require
     * @param   int param.type - 类型(snap=快照,backup=备份) require
     * @return  int list[].id - 备份管理设置ID
     * @return  int list[].num - 数量
     * @return  float list[].price - 价格
     * @return  int count - 总条数
     */
    public function backupConfigList($param)
    {
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
     * 时间 2022-10-12
     * @title 保存商品备份/快照配置
     * @desc 保存商品备份/快照配置
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   int data[].num - 数量
     * @param   int data[].price - 价格
     * @param   string type backup 类型(snap=快照,backup=备份)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.desc - 变动描述
     */
    public function saveBackupConfig($product_id, $data, $type = 'backup')
    {
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