<?php 
namespace server\mf_dcim\model;

use think\Model;
use think\facade\Cache;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\mf_dcim\logic\ToolLogic;
use server\mf_dcim\idcsmart_dcim\Dcim;

class ImageModel extends Model{

	protected $name = 'module_mf_dcim_image';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_id'        => 'int',
        'image_group_id'    => 'int',
        'name'              => 'string',
        'enable'            => 'int',
        'charge'            => 'int',
        'price'             => 'float',
        'rel_image_id'      => 'int',
    ];

    /**
     * 时间 2023-02-01
     * @title 添加操作系统
     * @desc 添加操作系统
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param   string image_group_id - 操作系统分类ID require
     * @param   string name - 系统名称 require
     * @param   int charge - 是否收费(0=不收费,1=收费) require
     * @param   float price - 价格 require
     * @param   int enable - 是否可用(0=禁用,1=启用) require
     * @param   int rel_image_id - 操作系统ID require
     * @return  int id - 操作系统ID
     */
    public function imageCreate($param){
        $imageGroup = ImageGroupModel::find($param['image_group_id']);
        if(empty($imageGroup)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_group_not_found')];
        }
        $param['product_id'] = $imageGroup['product_id'];

        $image = $this->create($param, ['product_id','image_group_id','name','charge','price','enable','rel_image_id']);

        $description = lang_plugins('mf_dcim_log_add_image_success', ['{name}'=>$param['name']]);
        active_log($description, 'product', $param['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('create_success'),
            'data'   => [
                'id' => (int)$image->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2023-02-01
     * @title 操作系统列表
     * @desc 操作系统列表
     * @author hh
     * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   int product_id - 商品ID
     * @param   int image_group_id - 搜索操作系统分类ID
     * @param   string keywords - 搜索:操作系统名称
     * @return  int list[].id - 操作系统分类ID
     * @return  int list[].image_group_id - 操作系统分类ID
     * @return  string list[].name - 操作系统名称
     * @return  int list[].charge - 是否收费(0=否,1=是)
     * @return  string list[].price - 价格
     * @return  int list[].enable - 是否启用(0=否,1=是)
     * @return  int list[].rel_image_id - 魔方云操作系统ID
     * @return  string list[].image_group_name - 操作系统分类名称
     * @return  string list[].icon - 操作系统分类图标
     * @return  int count - 总条数
     */
    public function imageList($param){
        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');

        if (!isset($param['orderby']) || !in_array($param['orderby'], ['id'])){
            $param['orderby'] = 'id';
        }

        $where = [];
        if(isset($param['product_id']) && is_numeric($param['product_id'])){
            $where[] = ['i.product_id', '=', $param['product_id']];
        }
        if(isset($param['image_group_id']) && !empty($param['image_group_id']) ){
            $where[] = ['i.image_group_id', '=', $param['image_group_id']];
        }
        if(isset($param['keywords']) && $param['keywords'] !== ''){
            $where[] = ['i.name', 'LIKE', '%'.$param['keywords'].'%'];
        }
        
        $list = $this
            ->alias('i')
            ->field('i.id,i.image_group_id,i.name,i.charge,i.price,i.enable,i.rel_image_id,ig.name image_group_name,ig.icon')
            ->where($where)
            ->leftJoin('module_mf_dcim_image_group ig', 'i.image_group_id=ig.id')
            ->page($param['page'], $param['limit'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        $count = $this
            ->alias('i')
            ->where($where)
            ->count();

        return ['list'=>$list, 'count'=>$count];
    }

    /**
     * 时间 2023-02-01
     * @title 修改操作系统
     * @desc 修改操作系统
     * @author hh
     * @version v1
     * @param   int id - 操作系统ID require
     * @param   string image_group_id - 操作系统分类ID require
     * @param   string name - 系统名称 require
     * @param   int charge - 是否收费(0=不收费,1=收费) require
     * @param   float price - 价格 require
     * @param   int enable - 是否可用(0=禁用,1=启用) require
     * @param   int rel_image_id - 操作系统ID require
     * @return  int id - 操作系统ID
     */
    public function imageUpdate($param){
        $image = $this->find($param['id']);
        if(empty($image)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_not_found')];
        }
        $imageGroup = ImageGroupModel::find($param['image_group_id']);
        if(empty($imageGroup) || $image['product_id'] != $imageGroup['product_id']){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_group_not_found')];
        }

        $this->update($param, ['id'=>$image->id], ['image_group_id','name','charge','price','enable','rel_image_id']);

        $switch = [lang_plugins('mf_dcim_switch_off'), lang_plugins('mf_dcim_switch_on')];

        $des = [
            'image_group_id' => lang_plugins('mf_dcim_image_group'),
            'name' => lang_plugins('mf_dcim_image_name'),
            'charge' => lang_plugins('mf_dcim_image_charge'),
            'price' => lang_plugins('mf_dcim_price'),
            'enable' => lang_plugins('mf_dcim_image_enable'),
            'rel_image_id' => lang_plugins('mf_dcim_image_rel_image_id'),
        ];

        $old = $image->toArray();
        $old['image_group_id'] = ImageGroupModel::where('id', $image['image_group_id'])->value('name');
        $old['charge'] = $switch[ $old['charge'] ];
        $old['enable'] = $switch[ $old['enable'] ];

        $param['image_group_id'] = $imageGroup['name'];
        $param['charge'] = $switch[ $param['charge'] ];
        $param['enable'] = $switch[ $param['enable'] ];

        $description = ToolLogic::createEditLog($old, $param, $des);
        if(!empty($description)){
            $description = lang_plugins('mf_dcim_log_modify_image_success', ['{detail}'=>$description]);
            active_log($description, 'product', $image['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-02-01
     * @title 删除操作系统
     * @desc 删除操作系统
     * @author hh
     * @version v1
     * @param   int id - 操作系统ID require
     */
    public function imageDelete($id){
        $image = $this->find($id);
        if(empty($image)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_not_found')];
        }
        $this->startTrans();
        try{
            $this->where('id', $id)->delete();

            $this->commit();
        }catch(\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang_plugins('delete_fail')];
        }

        $description = lang_plugins('mf_dcim_log_delete_image_success', ['{name}'=>$image['name']]);
        active_log($description, 'product', $image['product_id']);

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('delete_success'),
        ];
        return $result;
    }
    
    /**
     * 时间 2023-02-06
     * @title 切换是否可用
     * @desc 切换是否可用
     * @author hh
     * @version v1
     * @param   int id - 操作系统ID require
     * @param   int enable - 是否启用(0=禁用,1=启用) require
     */
    public function toggleImageEnable($param){
        $image = $this->find($param['id']);
        if(empty($image)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_not_found')];
        }
        if($image['enable'] == $param['enable']){
            return ['status'=>200, 'msg'=>lang_plugins('success_message')];
        }

        $this->update(['enable'=>$param['enable']], ['id'=>$image['id']]);

        $act = [lang_plugins('mf_dcim_disable'), lang_plugins('mf_dcim_enable')];
        
        $description = lang_plugins('mf_dcim_log_toggle_image_enable_success', [
            '{act}' => $act[ $param['enable'] ],
            '{name}' => $image['name'],
        ]);
        active_log($description, 'product', $image['product_id']);

        return ['status'=>200, 'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2023-02-06
     * @title
     * @desc
     * @url
     * @method  POST
     * @author hh
     * @version v1
     * @param   string x      -             x
     * @param   [type] $param [description]
     * @return  [type]        [description]
     */
    public function homeImageList($param){
        $where = [];
        $where[] = ['product_id', '=', $param['product_id'] ?? 0];

        // 操作系统
        $imageGroup = ImageGroupModel::field('id,name,icon')->where($where)->order('order', 'asc')->order('id', 'desc')->select()->toArray();

        $image = ImageModel::field('id,image_group_id,name,charge,price')->where($where)->where('enable', 1)->order('name', 'asc')->select()->toArray();
        $imageArr = [];
        foreach($image as $v){
            $imageArr[ $v['image_group_id'] ][] = $v;
        }
        foreach($imageGroup as $k=>$v){
            if(isset($imageArr[$v['id']])){
                $imageGroup[$k]['image'] = $imageArr[ $v['id'] ];
            }else{
                unset($imageGroup[$k]);
            }
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list' => array_values($imageGroup)
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
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => []
        ];

        // 验证产品和用户
        $host = HostModel::find($param['id']);
        if(empty($host) || $host['status'] != 'Active'){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_link_dcim')];
        }
        // 前台判断
        $app = app('http')->getName();
        if($app == 'home'){
            if($host['client_id'] != get_client_id()){
                return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_host_not_found')];
            }
        }
        $hostLink = HostLinkModel::where('host_id', $param['id'])->find();
        if(empty($hostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_not_link_dcim')];
        }
        $image = ImageModel::find($param['image_id'] ?? 0);
        if(empty($image) || $image['enable'] == 0){
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_image_not_found')];
        }
        $configData = json_decode($hostLink['config_data'], true);
        $duration = DurationModel::where('product_id', $host['product_id'])->where('num', $configData['duration']['num'] ?? 0)->where('unit', $configData['duration']['unit'] ?? 'month')->find();
        
        $result['data']['price'] = '0.00';
        if($host['billing_cycle'] != 'free' && $image['charge'] == 1){
            $res = HostImageLinkModel::where('host_id', $param['id'])->where('image_id', $param['image_id'])->find();
            if(empty($res)){
                bcscale(2);
                $image['price'] = bcmul($image['price'], $duration['price_factor'] ?? 1);

                $result['data']['price'] = amount_format($image['price']);
                $result['data']['description'] = '购买镜像'.$image['name'];
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
            ],
            'customfield' => $param['customfield'] ?? [],
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

    /**
     * 时间 2022-09-25
     * @title 拉取镜像
     * @desc 拉取镜像
     * @author hh
     * @version v1
     * @param   int $productId - 商品ID
     */
    public function imageSync($productId){
        $result = ['status'=>200, 'msg'=>lang_plugins('success_message')];

        $cacheKey = 'SYNC_MF_DCIM_IMAGE_'.$productId;
        if(Cache::has($cacheKey)){
            return $result;
        }
        Cache::set($cacheKey, 1, 180);

        $ProductModel = ProductModel::find($productId);
        if(empty($ProductModel)){
            Cache::delete($cacheKey);
            return ['status'=>400, 'msg'=>lang_plugins('product_id_error')];
        }
        if($ProductModel->getModule() != 'mf_dcim'){
            Cache::delete($cacheKey);
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_mf_dcim_module')];
        }
        if($ProductModel['type'] == 'server_group'){
            Cache::delete($cacheKey);
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_product_must_link_server_can_sync_image')];
        }
        $ServerModel = ServerModel::find($ProductModel['rel_id']);
        $ServerModel['password'] = aes_password_decode($ServerModel['password']);

        $Dcim = new Dcim($ServerModel);

        $res = $Dcim->getAllMirrorOs();
        if($res['status'] != 200){
            Cache::delete($cacheKey);
            return ['status'=>400, 'msg'=>lang_plugins('mf_dcim_sync_image_failed')];
        }
        
        $imageLink = array_column($res['data']['group'], 'name', 'id');

        // 添加组
        $imageGroup = ImageGroupModel::field('id,name')->where('product_id', $productId)->select()->toArray();
        $imageGroup = array_column($imageGroup, 'id', 'name') ?? [];
        $index = 0;
        foreach($res['data']['group'] as $v){
            if(empty($imageGroup[$v['name']])){
                $ImageGroupModel = ImageGroupModel::create(['product_id'=>$productId, 'name'=>$v['name'], 'icon'=>$v['name'], 'order'=>$index ]);
                $imageGroup[ $v['name'] ] = $ImageGroupModel->id;
                $index++;
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