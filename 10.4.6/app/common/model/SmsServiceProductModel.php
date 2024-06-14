<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\model\ProductModel;

/**
 * @title 模板控制器-短信服务商品模型
 * @desc 模板控制器-短信服务商品模型
 * @use app\common\model\SmsServiceProductModel
 */
class SmsServiceProductModel extends Model
{
    protected $name = 'sms_service_product';

    // 设置字段信息
    protected $schema = [
        'id'      	    => 'int',
        'title'         => 'string',
        'description'   => 'string',
        'price'         => 'float',
        'price_unit'    => 'string',
        'product_id'    => 'int',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2024-04-02
     * @title 短信服务商品列表
     * @desc 短信服务商品列表
     * @author theworld
     * @version v1
     * @return array list -  商品
     * @return int list[].id - 商品ID
     * @return string list[].title - 标题
     * @return string list[].description - 描述
     * @return string list[].price - 价格
     * @return string list[].price_unit - 价格单位,month月year年
     * @return int list[].product_id - 关联商品ID
     */
    public function productList($param)
    {
        $list = $this->field('id,title,description,price,price_unit,product_id')
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['price'] = amount_format($value['price']);
        }

        return ['list' => $list];
    }

    /**
     * 时间 2024-04-02
     * @title 创建短信服务商品
     * @desc 创建短信服务商品
     * @author theworld
     * @version v1
     * @param string param.title - 标题 required
     * @param string param.description - 描述 required
     * @param float param.price - 价格 required
     * @param string param.price_unit - 价格单位,month月year年 required
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
                'price_unit' => $param['price_unit'],
                'product_id' => $param['product_id'],
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_add_sms_service_product', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['title']]), 'sms_service_product', $product->id);

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
     * @title 编辑短信服务商品
     * @desc 编辑短信服务商品
     * @author theworld
     * @version v1
     * @param int param.id - 商品ID required
     * @param string param.title - 标题 required
     * @param string param.description - 描述 required
     * @param float param.price - 价格 required
     * @param string param.price_unit - 价格单位,month月year年 required
     * @param int param.product_id - 关联商品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateProduct($param)
    {
        // 验证商品ID
        $product = $this->find($param['id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('sms_service_product_not_exist')];
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
                'price_unit' => $param['price_unit'],
                'product_id' => $param['product_id'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'title'         => lang('sms_service_product_title'),
                'description'   => lang('sms_service_product_description'),
                'price'         => lang('sms_service_product_price'),
                'price_unit'    => lang('sms_service_product_price_unit'),
                'product_id'    => lang('sms_service_product_product_id'),
            ];

            $param['price_unit'] = lang('unit_'.$param['price_unit']);
            $product['price_unit'] = lang('unit_'.$product['price_unit']);

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
                $description = lang('log_update_sms_service_product', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $product['title'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'sms_service_product', $product->id);
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
     * @title 删除短信服务商品
     * @desc 删除短信服务商品
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
            return ['status'=>400, 'msg'=>lang('sms_service_product_not_exist')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang('log_delete_sms_service_product', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $product['title']]), 'sms_service_product', $product->id);
            
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
     * @title 短信服务数据
     * @desc 短信服务数据
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
        $list = $this->field('id,title,description,price,price_unit,product_id')
            ->select()
            ->toArray();

        $lang = lang();
        foreach ($list as $key => $value) {
            $list[$key]['price'] = amount_format($value['price']);
            $list[$key]['price_unit'] = $lang['unit_'.$value['price_unit']];
        }

        return ['list' => $list];
    }
}