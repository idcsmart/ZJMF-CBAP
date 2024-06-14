<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\model\ProductModel;

/**
 * @title 模板控制器-服务器托管商品模型
 * @desc 模板控制器-服务器托管商品模型
 * @use app\common\model\ServerHostingProductModel
 */
class ServerHostingProductModel extends Model
{
    protected $name = 'server_hosting_product';

    // 设置字段信息
    protected $schema = [
        'id'      		        => 'int',
        'area_id'               => 'int',
        'title'                 => 'string',
        'region'                => 'string',
        'ip_num'                => 'string',
        'bandwidth'             => 'string',
        'defense'               => 'string',
        'bandwidth_price'       => 'float',
        'bandwidth_price_unit'  => 'string',
        'selling_price'         => 'float',
        'selling_price_unit'    => 'string',
        'product_id'            => 'int',
        'create_time'           => 'int',
        'update_time'           => 'int',
    ];

    /**
     * 时间 2024-04-02
     * @title 服务器托管商品列表
     * @desc 服务器托管商品列表
     * @author theworld
     * @version v1
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @return array list -  商品
     * @return int list[].id - 商品ID
     * @return int list[].area_id - 区域ID
     * @return string list[].first_area - 所属区域
     * @return string list[].title - 标题
     * @return string list[].region - 地域
     * @return string list[].ip_num - IP数量
     * @return string list[].bandwidth - 带宽
     * @return string list[].defense - 防御
     * @return string list[].bandwidth_price - 带宽价格
     * @return string list[].bandwidth_price_unit - 带宽价格单位,month/M/月year/M/年
     * @return string list[].selling_price - 售价
     * @return string list[].selling_price_unit - 售价单位,month月year年
     * @return int list[].product_id - 关联商品ID
     * @return int count - 商品数量
     */
    public function productList($param)
    {
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'a.'.$param['orderby'] : 'a.id';

        $count = $this->field('id')
            ->count();
        $list = $this->alias('a')
            ->field('a.id,a.area_id,b.first_area,a.title,a.region,a.ip_num,a.bandwidth,a.defense,a.bandwidth_price,a.bandwidth_price_unit,a.selling_price,a.selling_price_unit,a.product_id')
            ->leftjoin('server_hosting_area b', 'b.id=a.area_id')
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['bandwidth_price'] = amount_format($value['bandwidth_price']);
            $list[$key]['selling_price'] = amount_format($value['selling_price']);
        }

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2024-04-02
     * @title 创建服务器托管商品
     * @desc 创建服务器托管商品
     * @author theworld
     * @version v1
     * @param int param.area_id - 区域ID required
     * @param string param.title - 标题 required
     * @param string param.region - 地域 required
     * @param string param.ip_num - IP数量 required
     * @param string param.bandwidth - 带宽 required
     * @param string param.defense - 防御 required
     * @param float param.bandwidth_price - 带宽价格 required
     * @param string param.bandwidth_price_unit - 带宽价格单位,month/M/月year/M/年 required
     * @param float param.selling_price - 售价 required
     * @param string param.selling_price_unit - 售价单位,month月year年 required
     * @param int param.product_id - 关联商品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createProduct($param)
    {
        $ServerHostingAreaModel = new ServerHostingAreaModel();
        $area = $ServerHostingAreaModel->where('id', $param['area_id'])->find();
        if(empty($area)){
            return ['status'=>400, 'msg'=>lang('server_hosting_area_not_exist')];
        }

        $ProductModel = new ProductModel();
        $relProduct = $ProductModel->find($param['product_id']);
        if(empty($relProduct)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }

        $this->startTrans();
        try {
            $product = $this->create([
                'area_id' => $param['area_id'],
                'title' => $param['title'],
                'region' => $param['region'],
                'ip_num' => $param['ip_num'],
                'bandwidth' => $param['bandwidth'],
                'defense' => $param['defense'],
                'bandwidth_price' => $param['bandwidth_price'],
                'bandwidth_price_unit' => $param['bandwidth_price_unit'],
                'selling_price' => $param['selling_price'],
                'selling_price_unit' => $param['selling_price_unit'],
                'product_id' => $param['product_id'],
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_add_server_hosting_product', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['title']]), 'server_hosting_product', $product->id);

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
     * @title 编辑服务器托管商品
     * @desc 编辑服务器托管商品
     * @author theworld
     * @version v1
     * @param int param.id - 商品ID required
     * @param int param.area_id - 区域ID required
     * @param string param.title - 标题 required
     * @param string param.region - 地域 required
     * @param string param.ip_num - IP数量 required
     * @param string param.bandwidth - 带宽 required
     * @param string param.defense - 防御 required
     * @param float param.bandwidth_price - 带宽价格 required
     * @param string param.bandwidth_price_unit - 带宽价格单位,month/M/月year/M/年 required
     * @param float param.selling_price - 售价 required
     * @param string param.selling_price_unit - 售价单位,month月year年 required
     * @param int param.product_id - 关联商品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateProduct($param)
    {
        // 验证商品ID
        $product = $this->find($param['id']);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('server_hosting_product_not_exist')];
        }

        $ServerHostingAreaModel = new ServerHostingAreaModel();
        $area = $ServerHostingAreaModel->where('id', $param['area_id'])->find();
        if(empty($area)){
            return ['status'=>400, 'msg'=>lang('server_hosting_area_not_exist')];
        }
        
        $ProductModel = new ProductModel();
        $relProduct = $ProductModel->find($param['product_id']);
        if(empty($relProduct)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }

        $this->startTrans();
        try {
            $this->update([
                'area_id' => $param['area_id'],
                'title' => $param['title'],
                'region' => $param['region'],
                'ip_num' => $param['ip_num'],
                'bandwidth' => $param['bandwidth'],
                'defense' => $param['defense'],
                'bandwidth_price' => $param['bandwidth_price'],
                'bandwidth_price_unit' => $param['bandwidth_price_unit'],
                'selling_price' => $param['selling_price'],
                'selling_price_unit' => $param['selling_price_unit'],
                'product_id' => $param['product_id'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'area_id'               => lang('server_hosting_product_area_id'),
                'title'                 => lang('server_hosting_product_title'),
                'region'                => lang('server_hosting_product_region'),
                'ip_num'                => lang('server_hosting_product_ip_num'),
                'bandwidth'             => lang('server_hosting_product_bandwidth'),
                'defense'               => lang('server_hosting_product_defense'),
                'bandwidth_price'       => lang('server_hosting_product_bandwidth_price'),
                'bandwidth_price_unit'  => lang('server_hosting_product_bandwidth_price_unit'),
                'selling_price'         => lang('server_hosting_product_selling_price'),
                'selling_price_unit'    => lang('server_hosting_product_selling_price_unit'),
                'product_id'            => lang('server_hosting_product_product_id'),
            ];

            $param['area_id'] = $area['first_area'];
            $old = $ServerHostingAreaModel->where('id', $product['area_id'])->find();
            $product['area_id'] = $old['first_area'];

            $param['bandwidth_price_unit'] = lang('bandwidth_unit_'.$param['bandwidth_price_unit']);
            $product['bandwidth_price_unit'] = lang('bandwidth_unit_'.$product['bandwidth_price_unit']);

            $param['selling_price_unit'] = lang('unit_'.$param['selling_price_unit']);
            $product['selling_price_unit'] = lang('unit_'.$product['selling_price_unit']);

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
                $description = lang('log_update_server_hosting_product', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $product['title'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'server_hosting_product', $product->id);
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
     * @title 删除服务器托管商品
     * @desc 删除服务器托管商品
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
            return ['status'=>400, 'msg'=>lang('server_hosting_product_not_exist')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang('log_delete_server_hosting_product', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $product['title']]), 'server_hosting_product', $product->id);
            
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
     * @title 服务器托管数据
     * @desc 服务器托管数据
     * @author theworld
     * @version v1
     * @return array list - 服务器托管产品配置
     * @return int list[].id - 区域ID
     * @return string list[].name - 区域名称
     * @return array list[].children -  商品
     * @return int list[].children[].id - 商品ID
     * @return int list[].children[].area_id - 区域ID
     * @return string list[].children[].title - 标题
     * @return string list[].children[].region - 地域
     * @return array list[].children[].ip_num - IP数量
     * @return array list[].children[].bandwidth - 带宽
     * @return array list[].children[].defense - 防御
     * @return string list[].children[].bandwidth_price - 带宽价格
     * @return string list[].children[].bandwidth_price_unit - 带宽价格单位
     * @return string list[].children[].selling_price - 售价
     * @return string list[].children[].selling_price_unit - 售价单位
     * @return int list[].children[].product_id - 关联商品ID
     */
    public function webData()
    {
        $list = $this->alias('a')
            ->field('a.id,a.area_id,b.first_area,a.title,a.region,a.ip_num,a.bandwidth,a.defense,a.bandwidth_price,a.bandwidth_price_unit,a.selling_price,a.selling_price_unit,a.product_id')
            ->leftjoin('server_hosting_area b', 'b.id=a.area_id')
            ->select()
            ->toArray();

        $lang = lang();
        $product = [];
        foreach ($list as $key => $value) {
            if(!isset($product[$value['area_id']])){
                $product[$value['area_id']] = [];
            }
            $product[$value['area_id']][] = [
                'id' => $value['id'], 
                'area_id' => $value['area_id'], 
                'title' => $value['title'], 
                'region' => $value['region'],
                'ip_num' => explode(',', $value['ip_num']),
                'bandwidth' => explode(',', $value['bandwidth']),
                'defense' => explode(',', $value['defense']),
                'bandwidth_price' => amount_format($value['bandwidth_price']),
                'bandwidth_price_unit' => $lang['bandwidth_unit_'.$value['bandwidth_price_unit']],
                'selling_price' => amount_format($value['selling_price']),
                'selling_price_unit' => $lang['unit_'.$value['selling_price_unit']],
                'product_id' => $value['product_id'],
            ];
        }

        $first = [];
        foreach ($list as $key => $value) {
            $first[$value['area_id']] = ['id' => $value['area_id'], 'name' => $value['first_area'], 'children' => $product[$value['area_id']]];
        }

        return ['list' => array_values($first)];

    }
}