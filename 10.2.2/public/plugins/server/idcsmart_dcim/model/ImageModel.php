<?php 
namespace server\idcsmart_dcim\model;

use think\Model;
use think\db\Query;
use think\facade\Cache;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\idcsmart_dcim\logic\ToolLogic;
use server\idcsmart_dcim\idcsmart_dcim\Dcim;

class ImageModel extends Model{

	protected $name = 'module_idcsmart_dcim_image';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'image_group_id'    => 'int',
        'name'              => 'string',
        'enable'            => 'int',
        'charge'            => 'int',
        'price'             => 'float',
        'product_id'        => 'int',
        'rel_image_id'      => 'int',
    ];

    /**
     * 时间 2022-06-21
     * @title 镜像列表
     * @desc 镜像列表
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID
     * @param   string param.image_group_id - 镜像分组ID
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 镜像ID
     * @return  string data.list[].name - 镜像名称
     * @return  int data.list[].enable - 是否启用(0=禁用,1=启用)
     * @return  int data.list[].charge - 是否付费(0=不付费,1=付费)
     * @return  int data.list[].image_group_id - 镜像分组ID
     * @return  string data.list[].image_group_name - 分组名称
     * @return  string data.list[].price - 价格
     * @return  int data.image_group[].id - 镜像分组ID
     * @return  string data.image_group[].name - 镜像分组名称
     */
    public function imageList($param)
    {
        $param['product_id'] = $param['product_id'] ?? 0;

        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];

        $imageGroup = ImageGroupModel::where($where)->field('id,name')->select()->toArray();
        $imageGroupArr = array_column($imageGroup, 'name', 'id');

        if(isset($param['image_group_id']) && !empty($param['image_group_id']) ){
            $where[] = ['image_group_id', '=', $param['image_group_id']];
        }
        $image = $this->where($where)
                    ->field('id,image_group_id,name,enable,charge,price')
                    ->select()
                    ->toArray();

        foreach($image as $k=>$v){
            $image[$k]['image_group_name'] = $imageGroupArr[ $v['image_group_id'] ] ?? 'other';
        }

        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[
                'list'=>$image,
                'image_group'=>$imageGroup
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-09-24
     * @title 批量修改镜像
     * @desc 批量修改镜像
     * @author hh
     * @version v1
     * @param   int param[].id - 镜像ID require
     * @param   int param[].charge - 是否付费(0=不付费,1=付费)
     * @param   float param[].price - 金额
     * @param   int param[].enable - 是否启用(0=禁用,1=启用)
     */
    public function saveImage($param){
        foreach($param as $v){
            if($v['charge'] == 0){
                $v['price'] = 0;
            }
            $this->update($v, ['id'=>$v['id']], ['charge','price','enable']);
        }
        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }

    /**
     * 时间 2022-09-24
     * @title 前台镜像列表
     * @desc 前台镜像列表
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @return  array list - 镜像数据
     * @return  int list[].id - 镜像分组ID
     * @return  string list[].name - 镜像分组
     * @return  int list[].image[].id - 镜像ID
     * @return  string list[].image[].name - 镜像名称
     * @return  int list[].image[].charge - 是否付费
     * @return  string list[].image[].price - 价格
     */
    public function homeImageList($param){
        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];
        $where[] = ['enable', '=', 1];

        $imageGroup = ImageGroupModel::field('id,name')->where('product_id', $param['product_id'])->select()->toArray();
        foreach($imageGroup as $k=>$v){
            $where[2] = ['image_group_id', '=', $v['id']];

            $image = $this
                ->field('id,name,charge,price')
                ->where($where)
                ->order('name','asc')
                ->select()
                ->toArray();
            if(empty($image)){
                unset($imageGroup[$k]);
                continue;
            }

            $imageGroup[$k]['image'] = $image;
        }
        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[
                'list'=>array_values($imageGroup)
            ]
        ];
        return $result;
    }
    
    /**
     * 时间 2022-07-29
     * @title 检查产品是够购买过镜像
     * @desc 检查产品是够购买过镜像
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   int param.image_id - 镜像ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.price - 需要支付的金额(0.00表示镜像免费或已购买)
     */
    public function checkHostImage($param){
        // 验证产品和用户
        $host = HostModel::find($param['id']);
        if(empty($host) || $host['status'] != 'Active'){
            return ['status'=>400, 'msg'=>lang_plugins('host_not_create')];
        }
        // 前台判断
        $app = app('http')->getName();
        if($app == 'home'){
            if($host['client_id'] != get_client_id()){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
            }
        }
        $hostLink = HostLinkModel::where('host_id', $param['id'])->find();
        if(empty($hostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('host_not_create')];
        }
        $image = ImageModel::find($param['image_id'] ?? 0);
        if(empty($image) || $image['enable'] == 0){
            return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
        }
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => []
        ];

        $result['data']['price'] = '0.00';
        if($host['billing_cycle'] != 'free' && $image['charge'] == 1){
            $res = HostImageLinkModel::where('host_id', $param['id'])->where('image_id', $param['image_id'])->find();
            if(empty($res)){
                $result['data']['price'] = amount_format($image['price']);
            }
        }
        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 生成购买镜像订单
     * @desc 生成购买镜像订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   int param.image_id - 镜像ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createImageOrder($param){
        $res = $this->checkHostImage($param);
        if($res['status'] == 400){
            return $res;
        }
        if($res['data']['price'] == 0){
            return ['status'=>400, 'msg'=>lang_plugins('no_need_to_buy_this_image')];
        }

        $image = ImageModel::find($param['image_id']);
        $description = lang_plugins("buy_image", ['name'=>$image['name']]);

        $OrderModel = new OrderModel();

        $data = [
            'host_id'     => $param['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $description,
            'price_difference' => $res['data']['price'],
            'renew_price_difference' => 0,
            'upgrade_refund' => 0,
            'config_options' => [
                'type'     => 'buy_image',
                'image_id' => $param['image_id'],
            ]
        ];
        return $OrderModel->createOrder($data);
    }

    /**
     * 时间 2022-09-25
     * @title 拉取镜像
     * @desc 拉取镜像
     * @author hh
     * @version v1
     * @param   int $productId - 商品ID
     * @return  [type]            [description]
     */
    public function getProductImage($productId){
        $result = ['status'=>200, 'msg'=>lang_plugins('success_message')];

        $cacheKey = 'SYNC_IDCSMART_DCIM_IMAGE_'.$productId;
        if(Cache::has($cacheKey)){
            return $result;
        }
        Cache::set($cacheKey, 1, 180);

        $ProductModel = ProductModel::find($productId);
        if(empty($ProductModel)){
            Cache::delete($cacheKey);
            return ['status'=>400, 'msg'=>lang_plugins('product_id_error')];
        }
        if($ProductModel->getModule() != 'idcsmart_dcim'){
            Cache::delete($cacheKey);
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        if($ProductModel['type'] == 'server_group'){
            Cache::delete($cacheKey);
            return ['status'=>400, 'msg'=>lang_plugins('product_must_link_server_can_sync_image')];
        }
        $ServerModel = ServerModel::find($ProductModel['rel_id']);
        $ServerModel['password'] = aes_password_decode($ServerModel['password']);

        $Dcim = new Dcim($ServerModel);

        $res = $Dcim->getAllMirrorOs();
        if($res['status'] != 200){
            Cache::delete($cacheKey);
            return ['status'=>400, 'msg'=>lang_plugins('sync_image_failed')];
        }
        
        $imageLink = array_column($res['data']['group'], 'name', 'id');

        // 添加组
        $imageGroup = ImageGroupModel::field('id,name')->where('product_id', $productId)->select()->toArray();
        $imageGroup = array_column($imageGroup, 'id', 'name') ?? [];
        foreach($res['data']['group'] as $v){
            if(empty($imageGroup[$v['name']])){
                $ImageGroupModel = ImageGroupModel::create(['product_id'=>$productId, 'name'=>$v['name']]);
                $imageGroup[ $v['name'] ] = $ImageGroupModel->id;
            }
        }
         // 获取当前产品已填加的镜像
        $image = ImageModel::field('id,rel_image_id')->where('product_id', $productId)->select()->toArray();
        $image = array_column($image, 'id', 'rel_image_id');

        $data = [];
        foreach($res['data']['os'] as $v){
            if(!isset($image[$v['id']])){
                $one = [
                    'image_group_id'=>$imageGroup[ $imageLink[$v['group_id']] ],
                    'name'=>$v['name'],
                    'enable'=>1,
                    'charge'=>0,
                    'price'=>0.00,
                    'product_id'=>$productId,
                    'rel_image_id'=>$v['id'],
                ];

                $data[] = $one;
            }
        }
        if(!empty($data)){
            $ImageModel = new ImageModel();
            $ImageModel->insertAll($data);
        }
        
        Cache::delete($cacheKey);
        return $result;
    }


}