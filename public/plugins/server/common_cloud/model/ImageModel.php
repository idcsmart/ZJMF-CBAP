<?php 
namespace server\common_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\common_cloud\logic\ToolLogic;

class ImageModel extends Model{

	protected $name = 'module_common_cloud_image';

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
        'filename'          => 'string',
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
        $oldImage = ImageModel::whereIn('id', array_column($param, 'id'))
                    ->select()
                    ->toArray();

        $oldImageArr = [];
        foreach($oldImage as $v){
            $oldImageArr[ $v['id'] ] = $v;
        }

        $desc = [
            'charge'=>lang_plugins('is_charge'),
            'price'=>lang_plugins('price'),
            'enable'=>lang_plugins('is_enable'),
        ];

        $description = '';
        $product_id = 0;
        foreach($param as $v){
            if($v['charge'] == 0){
                $v['price'] = 0;
            }
            if(empty($product_id)){
                $product_id = $oldImageArr[$v['id']]['product_id'];
            }
            $obj = $this->update($v, ['id'=>$v['id']], ['charge','price','enable']);

            $tmp = ToolLogic::createEditLog($oldImageArr[$v['id']], $v, $desc);
            if(!empty($tmp)){
                $description .= lang_plugins('log_modify_image_success', [
                    '{name}'=>$oldImageArr[$v['id']]['name'],
                    '{detail}'=>$tmp,
                ]);
            }
        }
        if(!empty($description)){
            active_log($description, 'product', $product_id);
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
        if($image['charge'] == 1){
            $res = HostImageLinkModel::where('host_id', $param['id'])->where('image_id', $param['image_id'])->find();
            if(!empty($res)){
                $result['data']['price'] = '0.00';
            }else{
                $result['data']['price'] = amount_format($image['price']);
            }
        }else{
            $result['data']['price'] = '0.00';
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
            'config_options' => [
                'type'     => 'buy_image',
                'image_id' => $param['image_id'],
            ]
        ];
        return $OrderModel->createOrder($data);
    }

    public function getDefaultUserInfo($ImageModel = null){
        $ImageModel = $ImageModel ?? $this;

        $imageGroup = ImageGroupModel::where('id', $ImageModel['image_group_id'] ?? 0)->value('name');

        if(stripos($imageGroup, 'windows') === 0){
            $result = [
                'username' => 'administrator',
                'port'     => 3306, 
            ];
        }else{
            $result = [
                'username' => 'root',
                'port'     => 22, 
            ];
        }
        return $result;
    }


}