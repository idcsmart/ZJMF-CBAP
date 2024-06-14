<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\model\ProductModel;

/**
 * @title 模板控制器-物理服务器商品模型
 * @desc 模板控制器-物理服务器商品模型
 * @use app\common\model\PhysicalServerProductModel
 */
class PhysicalServerProductModel extends Model
{
    protected $name = 'physical_server_product';

    // 设置字段信息
    protected $schema = [
        'id'      		        => 'int',
        'area_id'               => 'int',
        'title'                 => 'string',
        'description'           => 'string',
        'cpu'                   => 'string',
        'memory'                => 'string',
        'disk'                  => 'string',
        'ip_num'                => 'string',
        'bandwidth'             => 'string',
        'duration'              => 'string',
        'tag'                   => 'string',
        'original_price'        => 'float',
        'original_price_unit'   => 'string',
        'selling_price'         => 'float',
        'selling_price_unit'    => 'string',
        'product_id'            => 'int',
        'create_time'           => 'int',
        'update_time'           => 'int',
    ];

    /**
     * 时间 2024-04-02
     * @title 物理服务器商品列表
     * @desc 物理服务器商品列表
     * @author theworld
     * @version v1
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @return array list -  商品
     * @return int list[].id - 商品ID
     * @return int list[].area_id - 区域ID
     * @return string list[].first_area - 一级区域
     * @return string list[].second_area - 二级区域
     * @return string list[].title - 标题
     * @return string list[].description - 描述
     * @return string list[].cpu - 处理器
     * @return string list[].memory - 内存
     * @return string list[].disk - 硬盘
     * @return string list[].ip_num - IP数量
     * @return string list[].bandwidth - 带宽
     * @return string list[].duration - 时长
     * @return string list[].tag - 标签
     * @return string list[].original_price - 原价
     * @return string list[].original_price_unit - 原价单位,month月year年
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
            ->field('a.id,a.area_id,b.first_area,b.second_area,a.title,a.description,a.cpu,a.memory,a.disk,a.ip_num,a.bandwidth,a.duration,a.tag,a.original_price,a.original_price_unit,a.selling_price,a.selling_price_unit,a.product_id')
            ->leftjoin('physical_server_area b', 'b.id=a.area_id')
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['original_price'] = amount_format($value['original_price']);
            $list[$key]['selling_price'] = amount_format($value['selling_price']);
        }

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2024-04-02
     * @title 创建物理服务器商品
     * @desc 创建物理服务器商品
     * @author theworld
     * @version v1
     * @param int param.area_id - 区域ID required
     * @param string param.title - 标题 required
     * @param string param.description - 描述 required
     * @param string param.cpu - 处理器 required
     * @param string param.memory - 内存 required
     * @param string param.disk - 硬盘 required
     * @param string param.ip_num - IP数量 required
     * @param string param.bandwidth - 带宽 required
     * @param string param.duration - 时长 required
     * @param string param.tag - 标签 required
     * @param float param.original_price - 原价 required
     * @param string param.original_price_unit - 原价单位,month月year年 required
     * @param float param.selling_price - 售价 required
     * @param string param.selling_price_unit - 售价单位,month月year年 required
     * @param int param.product_id - 关联商品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createProduct($param)
    {
        $PhysicalServerAreaModel = new PhysicalServerAreaModel();
        $area = $PhysicalServerAreaModel->where('id', $param['area_id'])->find();
        if(empty($area)){
            return ['status'=>400, 'msg'=>lang('physical_server_area_not_exist')];
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
                'description' => $param['description'],
                'cpu' => $param['cpu'],
                'memory' => $param['memory'],
                'disk' => $param['disk'],
                'ip_num' => $param['ip_num'],
                'bandwidth' => $param['bandwidth'],
                'duration' => $param['duration'],
                'tag' => $param['tag'],
                'original_price' => $param['original_price'],
                'original_price_unit' => $param['original_price_unit'],
                'selling_price' => $param['selling_price'],
                'selling_price_unit' => $param['selling_price_unit'],
                'product_id' => $param['product_id'],
                'create_time' => time()
            ]);

            # 记录日志
            active_log(lang('log_add_physical_server_product', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['title']]), 'physical_server_product', $product->id);

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
     * @title 编辑物理服务器商品
     * @desc 编辑物理服务器商品
     * @author theworld
     * @version v1
     * @param int param.id - 商品ID required
     * @param int param.area_id - 区域ID required
     * @param string param.title - 标题 required
     * @param string param.description - 描述 required
     * @param string param.cpu - 处理器 required
     * @param string param.memory - 内存 required
     * @param string param.disk - 硬盘 required
     * @param string param.ip_num - IP数量 required
     * @param string param.bandwidth - 带宽 required
     * @param string param.duration - 时长 required
     * @param string param.tag - 标签 required
     * @param float param.original_price - 原价 required
     * @param string param.original_price_unit - 原价单位,month月year年 required
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
            return ['status'=>400, 'msg'=>lang('physical_server_product_not_exist')];
        }

        $PhysicalServerAreaModel = new PhysicalServerAreaModel();
        $area = $PhysicalServerAreaModel->where('id', $param['area_id'])->find();
        if(empty($area)){
            return ['status'=>400, 'msg'=>lang('physical_server_area_not_exist')];
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
                'description' => $param['description'],
                'cpu' => $param['cpu'],
                'memory' => $param['memory'],
                'disk' => $param['disk'],
                'ip_num' => $param['ip_num'],
                'bandwidth' => $param['bandwidth'],
                'duration' => $param['duration'],
                'tag' => $param['tag'],
                'original_price' => $param['original_price'],
                'original_price_unit' => $param['original_price_unit'],
                'selling_price' => $param['selling_price'],
                'selling_price_unit' => $param['selling_price_unit'],
                'product_id' => $param['product_id'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'area_id'               => lang('physical_server_product_area_id'),
                'title'                 => lang('physical_server_product_title'),
                'description'           => lang('physical_server_product_description'),
                'cpu'                   => lang('physical_server_product_system_cpu'),
                'memory'                => lang('physical_server_product_system_memory'),
                'disk'                  => lang('physical_server_product_disk'),
                'ip_num'                => lang('physical_server_product_ip_num'),
                'bandwidth'             => lang('physical_server_product_bandwidth'),
                'duration'              => lang('physical_server_product_duration'),
                'tag'                   => lang('physical_server_product_tag'),
                'original_price'        => lang('physical_server_product_original_price'),
                'original_price_unit'   => lang('physical_server_product_original_price_unit'),
                'selling_price'         => lang('physical_server_product_selling_price'),
                'selling_price_unit'    => lang('physical_server_product_selling_price_unit'),
                'product_id'            => lang('physical_server_product_product_id'),
            ];

            $param['area_id'] = $area['first_area'].'-'.$area['second_area'];
            $old = $PhysicalServerAreaModel->where('id', $product['area_id'])->find();
            $product['area_id'] = $old['first_area'].'-'.$old['second_area'];

            $param['original_price_unit'] = lang('unit_'.$param['original_price_unit']);
            $product['original_price_unit'] = lang('unit_'.$product['original_price_unit']);

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
                $description = lang('log_update_physical_server_product', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $product['title'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'physical_server_product', $product->id);
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
     * @title 删除物理服务器商品
     * @desc 删除物理服务器商品
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
            return ['status'=>400, 'msg'=>lang('physical_server_product_not_exist')];
        }

        $this->startTrans();
        try {
            # 记录日志
            active_log(lang('log_delete_physical_server_product', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $product['title']]), 'physical_server_product', $product->id);
            
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
     * @title 物理服务器数据
     * @desc 物理服务器数据
     * @author theworld
     * @version v1
     * @return array list - 物理服务器产品配置
     * @return string list[].name - 一级区域名称
     * @return array list[].children - 二级区域
     * @return int list[].children[].id - 二级区域ID
     * @return string list[].children[].name - 二级区域名称
     * @return array list[].children[].children - 商品
     * @return int list[].children[].children[].id - 商品ID
     * @return int list[].children[].children[].area_id - 区域ID
     * @return string list[].children[].children[].title - 标题
     * @return string list[].children[].children[].description - 描述
     * @return string list[].children[].children[].cpu - 处理器
     * @return array list[].children[].children[].memory - 内存
     * @return array list[].children[].children[].disk - 硬盘
     * @return array list[].children[].children[].ip_num - IP数量
     * @return array list[].children[].children[].bandwidth - 带宽
     * @return array list[].children[].children[].duration - 时长
     * @return array list[].children[].children[].tag - 标签
     * @return string list[].children[].children[].original_price - 原价
     * @return string list[].children[].children[].original_price_unit - 原价单位
     * @return string list[].children[].children[].selling_price - 售价
     * @return string list[].children[].children[].selling_price_unit - 售价单位
     * @return int list[].children[].children[].product_id - 关联商品ID
     * @return array banner - 轮播图
     * @return int banner[].id - 轮播图ID
     * @return string banner[].img - 图片
     * @return string banner[].url - 跳转链接
     * @return string banner[].notes - 备注
     * @return int more_offers - 更多优惠0关闭1开启
     * @return array discount -  优惠
     * @return int discount[].id - 优惠ID
     * @return string discount[].title - 标题
     * @return string discount[].description - 描述
     * @return string discount[].url - 跳转链接
     */
    public function webData()
    {
        $list = $this->alias('a')
            ->field('a.id,a.area_id,b.first_area,b.second_area,a.title,a.description,a.cpu,a.memory,a.disk,a.ip_num,a.bandwidth,a.duration,a.tag,a.original_price,a.original_price_unit,a.selling_price,a.selling_price_unit,a.product_id')
            ->leftjoin('physical_server_area b', 'b.id=a.area_id')
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
                'description' => $value['description'],
                'cpu' => $value['cpu'],
                'memory' => explode(',', $value['memory']),
                'disk' => explode(',', $value['disk']),
                'ip_num' => explode(',', $value['ip_num']),
                'bandwidth' => explode(',', $value['bandwidth']),
                'duration' => explode(',', $value['duration']),
                'tag' => explode(',', $value['tag']),
                'original_price' => amount_format($value['original_price']),
                'original_price_unit' => $lang['unit_'.$value['original_price_unit']],
                'selling_price' => amount_format($value['selling_price']),
                'selling_price_unit' => $lang['unit_'.$value['selling_price_unit']],
                'product_id' => $value['product_id'],
            ];
        }

        $second = [];
        foreach ($list as $key => $value) {
            if(!isset($second[$value['first_area']])){
                $second[$value['first_area']] = [];
            }
            $second[$value['first_area']][$value['area_id']] = ['id' => $value['area_id'], 'name' => $value['second_area'], 'children' => $product[$value['area_id']]];
        }

        $first = [];
        foreach ($list as $key => $value) {
            if(!in_array($value['first_area'], array_column($first, 'name'))){
                $first[] = ['name' => $value['first_area'], 'children' => array_values($second[$value['first_area']])];
            }
        }

        $time = strtotime(date("Y-m-d"));
        $PhysicalServerBannerModel = new PhysicalServerBannerModel();
        $banners = $PhysicalServerBannerModel->field('id,img,url,notes')
            ->where('show', 1)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->order('order', 'asc')
            ->select()->toArray();

        

        $ConfigurationModel = new ConfigurationModel();
        $configuration = $ConfigurationModel->physicalServerList();

        if($configuration['physical_server_more_offers']==1){
            $PhysicalServerDiscountModel = new PhysicalServerDiscountModel();
            $discount = $PhysicalServerDiscountModel->field('id,title,description,url')
                ->select()
                ->toArray();
        }else{
            $discount = [];
        }

        return ['list' => array_values($first), 'banner' => $banners, 'more_offers' => $configuration['physical_server_more_offers'], 'discount' => $discount];

    }
}