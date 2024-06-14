<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 模板控制器-物理服务器优惠模型
 * @desc 模板控制器-物理服务器优惠模型
 * @use app\common\model\PhysicalServerDiscountModel
 */
class PhysicalServerDiscountModel extends Model
{
    protected $name = 'physical_server_discount';

    // 设置字段信息
    protected $schema = [
        'id'      		=> 'int',
        'title'         => 'string',
        'description'   => 'string',
        'url'           => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2024-04-02
     * @title 物理服务器优惠列表
     * @desc 物理服务器优惠列表
     * @author theworld
     * @version v1
     * @return array list -  优惠
     * @return int list[].id - 优惠ID
     * @return string list[].title - 标题
     * @return string list[].description - 描述
     * @return string list[].url - 跳转链接
     */
    public function discountList()
    {
        $list = $this->field('id,title,description,url')
            ->select()
            ->toArray();

        return ['list' => $list];
    }

    /**
     * 时间 2024-04-02
     * @title 创建物理服务器优惠
     * @desc 创建物理服务器优惠
     * @author theworld
     * @version v1
     * @param string param.title - 标题 required
     * @param string param.description - 描述 required
     * @param string param.url - 跳转链接 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createDiscount($param)
    {
        $this->startTrans();
        try {
            $discount = $this->create([
                'title' => $param['title'],
                'description' => $param['description'],
                'url' => $param['url'],
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_add_physical_server_discount', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['title']]), 'physical_server_discount', $discount->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('create_fail')];
        }
        return ['status' => 200, 'msg' => lang('create_success')];
    }

    /**
     * 时间 2024-04-02
     * @title 编辑物理服务器优惠
     * @desc 编辑物理服务器优惠
     * @author theworld
     * @version v1
     * @param int param.id - 优惠ID required
     * @param string param.title - 标题 required
     * @param string param.description - 描述 required
     * @param string param.url - 跳转链接 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateDiscount($param)
    {
        // 验证优惠ID
        $discount = $this->find($param['id']);
        if(empty($discount)){
            return ['status'=>400, 'msg'=>lang('physical_server_discount_not_exist')];
        }

        $this->startTrans();
        try {
            $this->update([
                'title' => $param['title'],
                'description' => $param['description'],
                'url' => $param['url'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'title'         => lang('physical_server_discount_title'),
                'description'   => lang('physical_server_discount_description'),
                'url'           => lang('physical_server_discount_url'),
            ];

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $discount[$k] != $param[$k]){
                    $old = $discount[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_physical_server_discount', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $discount['title'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'physical_server_discount', $discount->id);
            }

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2024-04-02
     * @title 删除物理服务器优惠
     * @desc 删除物理服务器优惠
     * @author theworld
     * @version v1
     * @param int id - 优惠ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteDiscount($id)
    {
        // 验证优惠ID
        $discount = $this->find($id);
        if(empty($discount)){
            return ['status'=>400, 'msg'=>lang('physical_server_discount_not_exist')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang('log_delete_physical_server_discount', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $discount['title']]), 'physical_server_discount', $discount->id);
            
            $this->destroy($id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang('delete_success')];
    }
}