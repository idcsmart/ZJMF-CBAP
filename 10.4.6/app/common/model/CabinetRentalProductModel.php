<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 模板控制器-机柜租用商品模型
 * @desc 模板控制器-机柜租用商品模型
 * @use app\common\model\CabinetRentalProductModel
 */
class CabinetRentalProductModel extends Model
{
    protected $name = 'cabinet_rental_product';

    // 设置字段信息
    protected $schema = [
        'id'      	    => 'int',
        'title'         => 'string',
        'description'   => 'string',
        'price'         => 'float',
        'product_id'    => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2024-04-02
     * @title 机柜租用商品列表
     * @desc 机柜租用商品列表
     * @author theworld
     * @version v1
     * @return array list -  商品
     * @return int list[].id - 商品ID
     * @return string list[].title - 标题
     * @return string list[].description - 描述
     * @return string list[].price - 价格
     * @return int list[].product_id - 关联商品ID
     */
    public function productList($param)
    {
        $list = $this->field('id,title,description,price,product_id')
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['price'] = amount_format($value['price']);
        }

        return ['list' => $list];
    }

    /**
     * 时间 2024-04-02
     * @title 创建机柜租用商品
     * @desc 创建机柜租用商品
     * @author theworld
     * @version v1
     * @param string param.title - 标题 required
     * @param string param.description - 描述 required
     * @param float param.price - 价格 required
     * @param int param.product_id - 关联商品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createProduct($param)
    {
        $ProductModel = new ProductModel();
        $relProduct = $ProductModel->find($param['product_id']);
        if(empty($relProduct)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }

        $this->startTrans();
        try {
            $product = $this->create([
                'title' => $param['title'],
                'description' => $param['description'],
                'price' => $param['price'],
                'product_id' => $param['product_id'],
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_add_cabinet_rental_product', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['title']]), 'cabinet_rental_product', $product->id);

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
     * @title 编辑机柜租用商品
     * @desc 编辑机柜租用商品
     * @author theworld
     * @version v1
     * @param int param.id - 商品ID required
     * @param string param.title - 标题 required
     * @param string param.description - 描述 required
     * @param float param.price - 价格 required
     * @param int param.product_id - 关联商品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateProduct($param)
    {
        // 验证商品ID
        $product = $this->find($param['id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('cabinet_rental_product_not_exist')];
        }

        $ProductModel = new ProductModel();
        $relProduct = $ProductModel->find($param['product_id']);
        if(empty($relProduct)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }

        $this->startTrans();
        try {
            $this->update([
                'title' => $param['title'],
                'description' => $param['description'],
                'price' => $param['price'],
                'product_id' => $param['product_id'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'title'         => lang('cabinet_rental_product_title'),
                'description'   => lang('cabinet_rental_product_description'),
                'price'         => lang('cabinet_rental_product_price'),
                'product_id'    => lang('cabinet_rental_product_product_id'),
            ];

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $product[$k] != $param[$k]){
                    $old = $product[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_cabinet_rental_product', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $product['title'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'cabinet_rental_product', $product->id);
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
     * @title 删除机柜租用商品
     * @desc 删除机柜租用商品
     * @author theworld
     * @version v1
     * @param int id - 商品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteProduct($id)
    {
        // 验证区域ID
        $product = $this->find($id);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('cabinet_rental_product_not_exist')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang('log_delete_cabinet_rental_product', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $product['title']]), 'cabinet_rental_product', $product->id);
            
            $this->destroy($id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }
        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2024-04-02
     * @title 机柜租用数据
     * @desc 机柜租用数据
     * @author theworld
     * @version v1
     * @return array list -  商品
     * @return int list[].id - 商品ID
     * @return string list[].title - 标题
     * @return string list[].description - 描述
     * @return string list[].price - 价格
     * @return int list[].product_id - 关联商品ID
     */
    public function webData()
    {
        $list = $this->field('id,title,description,price,product_id')
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['price'] = amount_format($value['price']);
        }

        return ['list' => $list];

    }
}