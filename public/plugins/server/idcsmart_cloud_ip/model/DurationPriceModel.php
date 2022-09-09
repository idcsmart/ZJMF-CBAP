<?php 
namespace server\idcsmart_cloud_ip\model;

use think\Model;
use app\common\model\ProductModel;

class DurationPriceModel extends Model{

	protected $name = 'module_idcsmart_cloud_ip_duration_price';

    protected $pk = 'id';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'duration'      => 'int',
        'duration_name' => 'string',
        'display_name'  => 'string',
        'ip_ratio'      => 'float',
        'bw_ratio'      => 'float',
        'pay_type'      => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    # 添加默认周期价格
    public function defaultAdd($productId){
        $add = $this->find($productId);
        if(!empty($add)){
            return true;
        }
        $time = time();
        $data = [
            [
                'product_id'    => $productId,
                'duration'      => 30,
                'duration_name' => 'month',
                'display_name'  => '月',
                'ip_ratio'      => 1,
                'bw_ratio'      => 1,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $productId,
                'duration'      => 90,
                'duration_name' => 'quarterly',
                'display_name'  => '季度',
                'ip_ratio'      => 3,
                'bw_ratio'      => 3,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $productId,
                'duration'      => 180,
                'duration_name' => 'half_year',
                'display_name'  => '半年',
                'ip_ratio'      => 6,
                'bw_ratio'      => 6,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $productId,
                'duration'      => 365,
                'duration_name' => 'year',
                'display_name'  => '年',
                'ip_ratio'      => 10,
                'bw_ratio'      => 10,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $productId,
                'duration'      => 3*365,
                'duration_name' => 'three_year',
                'display_name'  => '3年',
                'ip_ratio'      => 30,
                'bw_ratio'      => 30,
                'create_time'   => $time,
            ],
            [
                'product_id'    => $productId,
                'duration'      => 5*365,
                'duration_name' => 'five_year',
                'display_name'  => '5年',
                'ip_ratio'      => 50,
                'bw_ratio'      => 50,
                'create_time'   => $time,
            ]
        ];
        $this->saveAll($data);
        return true;
    }

    # 获取周期价格
    public function durationPriceList($param){
        $product = ProductModel::find($param['product_id']);
        if(empty($product)){
            return ['list' => []];
        }
        // TODO 在这里设置还是其他时候?

        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];

        $durationPrice = $this
                        ->field('id,duration,display_name,ip_ratio,bw_ratio')
                        ->where($where)
                        ->order('duration', 'asc')
                        ->select()
                        ->toArray();

        return ['list' => $durationPrice];
    }

    # 保存所有周期价格
    public function saveDurationPrice($param){
        $id = array_column($param['data'], 'id');

        $durationPrice = $this
                        ->whereIn('id', $id)
                        ->select()
                        ->toArray();
        if(count($durationPrice) != count($id)){
            return ['status'=>400, 'msg'=>lang_plugins('id_error')];
        }
        // 暂时不能修改时长
        foreach($param['data'] as $v){
            $v['update_time'] = time();
            $this->update($v, ['id'=>$v['id']], ['ip_ratio','bw_ratio','update_time']);
        }
        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }

    # 根据配置获取周期价格
    public function configDurationPrice($param){
        $package = PackageModel::find($param['package_id'] ?? 0);
        if(empty($package)){
            return ['list' => []];
        }
        if($package['ip_enable']!=1){
            return ['list' => []];
        }
        $productId = $package['product_id'];
        $package['bw_price'] = json_decode($package['bw_price'], true);
        $param['size'] = $param['size'] ?? 0;

        // 获取所有周期价格,暂时只有前付
        $durationPrice = $this
                        ->field('id,display_name,ip_ratio,bw_ratio')
                        ->where('product_id', $productId)
                        ->where('pay_type', 'recurring_prepayment')
                        ->select()
                        ->toArray();

        foreach($durationPrice as $k=>$v){
            if($package['bw_enable']==1){
                $bwPrice = 0;
                foreach ($package['bw_price'] as $key => $value) {
                    if($param['size']>$value['max']){
                        $bwPrice += ($value['max']-$value['min'])*$value['price'];
                    }else{
                        $bwPrice += ($param['size']-$value['min'])*$value['price'];
                        break;
                    }
                }
                $durationPrice[$k]['price'] = amount_format(bcadd(bcmul($v['ip_ratio'], $package['ip_price']), bcmul($v['bw_ratio'], $bwPrice)));
            }else{
                $durationPrice[$k]['price'] = amount_format(bcmul($v['ip_ratio'], $package['ip_price']));
            }
            
            unset($durationPrice[$k]['ip_ratio'], $durationPrice[$k]['bw_ratio']);
        }
        
        return ['list' => $durationPrice];
    }

    /**
     * 时间 2022-06-22
     * @title 配置计算价格
     * @desc 配置计算价格
     * @author theworld
     * @version v1
     * @param   string x             - x
     * @return  [type] [description]
     */
    public function cartCalculatePrice($param){
        $PackageModel = PackageModel::find($param['package_id'] ?? 0);
        if(empty($PackageModel)){
            return ['status'=>400, 'msg'=>lang_plugins('套餐不存在')];
        }
        if($PackageModel['ip_enable']!=1){
            return ['status'=>400, 'msg'=>lang_plugins('当前套餐不可用')];
        }
        $productId = $PackageModel['product_id'];

        // 获取所有周期价格,暂时只有前付
        $durationPrice = $this
                        ->where('product_id', $productId)
                        ->where('pay_type', 'recurring_prepayment')
                        ->where('id', $param['duration_price_id'] ?? 0)
                        ->find();
        if(empty($durationPrice)){
            return ['status'=>400, 'msg'=>lang_plugins('周期错误')];
        }
        if($PackageModel['bw_enable']==1){
            $PackageModel['bw_price'] = json_decode($PackageModel['bw_price'], true);
            $bwPrice = 0;
            $param['size'] = isset($param['size']) ? intval($param['size']) : 0;
            foreach ($PackageModel['bw_price'] as $key => $value) {
                if($param['size']>$value['max']){
                    $bwPrice += ($value['max']-$value['min'])*$value['price'];
                }else{
                    $bwPrice += ($param['size']-$value['min'])*$value['price'];
                    break;
                }
            }
            $price = amount_format(bcadd(bcmul($durationPrice['ip_ratio'], $PackageModel['ip_price']), bcmul($durationPrice['bw_ratio'], $bwPrice)));
        }else{
            $price = amount_format(bcmul($durationPrice['ip_ratio'], $PackageModel['ip_price']));
        }
        
        $result = [
            'status'=>200,
            'msg'=>lang_plugins('success_message'),
            'data'=>[
                'price'=>$price,
                'billing_cycle'=>$durationPrice['display_name'],
                'duration'=>$durationPrice['duration']*24*3600,
                'description'=>lang_plugins('server_elastic_ip'),
                'content'=>lang_plugins('server_elastic_ip'),  // TODO 
            ]
        ];
        return $result;


    }

}