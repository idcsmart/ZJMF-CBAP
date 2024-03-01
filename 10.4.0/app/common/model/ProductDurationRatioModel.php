<?php 
namespace app\common\model;

use think\Model;
use think\facade\Db;
use app\common\model\ProductModel;

/**
 * @title 周期比例模型
 * @use app\common\model\ProductDurationRatioModel
 */
class ProductDurationRatioModel extends Model{

    // 表名
	protected $name = 'product_duration_ratio';

    // 关联的周期表名
    protected $linkTable = '';

    // 设置字段信息
    protected $schema = [
        'product_id'    => 'int',
        'duration_id'   => 'int',
        'ratio'         => 'string',
    ];

    /**
     * 时间 2023-10-20
     * @title 获取周期比例
     * @desc  获取周期比例
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  int [].id - 关联周期表ID
     * @return  string [].name - 周期名称
     * @return  string [].num - 周期时长
     * @return  string [].unit - 周期单位(hour=小时,day=天,month=月)
     * @return  float [].ratio - 周期比例
     * @return  float [].price_factor - 价格系数
     */
    public function indexRatio($product_id)
    {
        try{
            $DurationModel = Db::name($this->linkTable);

            $data = $DurationModel
                    ->alias('d')
                    ->field('d.id,d.name,d.num,d.unit,pdr.ratio,d.price_factor')
                    ->leftJoin('product_duration_ratio pdr', 'd.id=pdr.duration_id AND pdr.product_id='.$product_id)
                    ->where('d.product_id', $product_id)
                    ->orderRaw('field(d.unit, "hour","day","month")')
                    ->order('d.num', 'asc')
                    ->withAttr('ratio', function($val){
                        return $val ?? '';
                    })
                    ->group('d.id')
                    ->select()
                    ->toArray();
        }catch(\Exception $e){
            $data = [];
        }
        return $data;
    }

    /**
     * 时间 2023-10-20
     * @title 保存周期比例
     * @desc  保存周期比例
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   object param.ratio - 比例(如{"2":"1.5"},键是周期ID,值是比例) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function saveRatio($param)
    {
        $productId = $param['product_id'] ?? 0;
        $product = ProductModel::find($productId);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist') ];
        }
        $old = $this->indexRatio($productId);

        $data = [];
        $detail = '';
        foreach($old as $v){
            if(isset($param['ratio'][$v['id']]) && $param['ratio'][$v['id']] > 0){
                $data[] = [
                    'product_id'    => $productId,
                    'duration_id'   => $v['id'],
                    'ratio'         => $param['ratio'][$v['id']],
                ];
                if($v['ratio'] != $param['ratio'][$v['id']]){
                    $detail .= lang('log_product_duration_ratio_change', [
                        '{name}' => $v['name'],
                        '{old}'  => $v['ratio'] ?? lang('null'),
                        '{new}'  => $param['ratio'][$v['id']],
                    ]);
                }
            }
        }
        if(empty($data) || count($old) != count($data)){
            return ['status'=>400, 'msg'=>lang('please_input_all_duration_ratio')];
        }

        $this->startTrans();
        try{
            $this->where('product_id', $param['product_id'])->delete();
            $this->insertAll($data);
            
            $this->commit();
        }catch(\Exception $e){
            $this->rollback();

            $result = [
                'status' => 400,
                'msg'    => $e->getMessage(),
            ];
            return $result;
        }

        if(!empty($detail)){
            $description = lang('log_save_product_duration_ratio', [
                '{product}' => 'product#'.$productId.'#'.$product['name'].'#',
                '{detail}'  => $detail,
            ]);
            active_log($description, 'product', $productId);
        }

        $result = [
            'status' => 200,
            'msg'    => lang('save_success'),
        ];
        return $result;
    }

    /**
     * 时间 2023-10-20
     * @title 计算自动填充
     * @desc  计算自动填充
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @param   array param.price - 价格(如["2"=>"1.5"],键是周期ID,值是价格) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  array data.list - 填充的价格(键是周期ID,值是填充的价格)
     */
    public function autoFill($param)
    {
        bcscale(2);

        $productId = $param['product_id'] ?? 0;
        $product = ProductModel::find($productId);
        if(empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        $data = $this->indexRatio($productId);
        if(empty($data)){
            return ['status'=>400, 'msg'=>lang('please_set_duration_ratio_first')];
        }
        $baseDuration = null;

        $res = [];
        foreach($data as $k=>$v){
            // 最小的周期作为基准
            if(isset($param['price'][$v['id']]) && $param['price'][$v['id']] > 0){
                $baseDuration = $v;
                $baseDuration['price'] = $param['price'][$v['id']];
                break;
            }
        }
        if(is_null($baseDuration)){
            return ['status'=>400, 'msg'=>lang('please_set_at_lease_one_price')];
        }
        foreach($data as $v){
            if(empty($v['ratio'])){
                return ['status'=>400, 'msg'=>lang('please_set_duration_ratio_first')];
            }
            if(!isset($res[$v['id']])){
                if($v['id'] == $baseDuration['id']){
                    $res[$v['id']] = amount_format($baseDuration['price']);
                }else{
                    $res[$v['id']] = amount_format(bcdiv(bcmul($baseDuration['price'], $v['ratio']), $baseDuration['ratio']));
                }
            }
        }

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'list'  => $res,
            ],
        ];
        return $result;
    }





}