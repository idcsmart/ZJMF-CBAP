<?php
namespace addon\idcsmart_refund\model;

use app\common\logic\ModuleLogic;
use app\common\model\ProductModel;
use think\db\Query;
use think\Model;

/*
 * @author wyh
 * @time 2022-07-06
 */
class IdcsmartRefundProductModel extends Model
{
    protected $name = 'addon_idcsmart_refund_product';

    // 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'product_id'                       => 'int',
        'admin_id'                         => 'int',
        'type'                             => 'string',
        'require'                          => 'string',
        'range_control'                    => 'int',
        'range'                            => 'int',
        'rule'                             => 'string',
        'ratio_value'                      => 'float',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
    ];

    /**
     * 时间 2022-07-06
     * @title 退款商品列表
     * @desc 退款商品列表
     * @author wyh
     * @version v1
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序:id
     * @param string param.sort - 升/降序:asc,desc
     * @param string param.keywords - 关键字搜索:商品名称
     * @return array list - 退款商品列表
     * @return int list[].id - ID
     * @return string list[].product_name - 商品名称
     * @return array list[].config_option - 商品配置
     * @return string list[].config_option[].name - 配置名称
     * @return string list[].config_option[].field - 配置字段
     * @return string list[].config_option[].type - 配置类型
     * @return array list[].config_option[].option - 配置选项{"name":1,"value":1}
     * @return string list[].config_option[].option[].name - 选项名称
     * @return string list[].config_option[].option[].value - 选项值
     * @return string list[].admin_name - 提交人
     * @return string list[].create_time - 提交时间
     * @return string list[].type - 退款类型
     */
    public function refundProductList($param)
    {
        if (!isset($param['orderby']) || !in_array($param['orderby'],['id'])){
            $param['orderby'] = 'rp.id';
        }
        $where = function (Query $query) use ($param){
            if (isset($param['keywords']) && !empty($param['keywords'])){
                $query->where('p.name','like',"%{$param['keywords']}%");
            }
        };

        $refundProducts = $this->alias('rp')
            ->field('rp.id,p.name as product_name,a.name as admin_name,rp.create_time,rp.type,rp.product_id')
            ->leftJoin('product p','p.id=rp.product_id')
            ->leftJoin('admin a','a.id=rp.admin_id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        $count = $this->alias('rp')
            ->leftJoin('product p','p.id=rp.product_id')
            ->leftJoin('admin a','a.id=rp.admin_id')
            ->where($where)
            ->count();
        $ModuleLogic = new ModuleLogic();
        $ProductModel = new ProductModel();
        foreach ($refundProducts as &$refundProduct){
            $product = $ProductModel->find($refundProduct['product_id']);
            unset($refundProduct['product_id']);
            if (!empty($product)){
                $refundProduct['config_option'] = $ModuleLogic->allConfigOption($product);
            }
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['list'=>$refundProducts,'count'=>$count]];

    }

    /**
     * 时间 2022-07-07
     * @title 新增退款商品
     * @desc 新增退款商品
     * @author wyh
     * @version v1
     * @param int product_id - 商品ID required
     * @param string type - 退款类型:Artificial人工，Auto自动 required
     * @param string require - 退款要求:First首次订购,Same同类商品首次订购
     * @param int range_control - 购买后X天内控制:0否默认,1是(当range不传值或值为null时,传此字段为0;否则传1) required
     * @param int range - 购买后X天内
     * @param string rule - 退款规则:Day按天退款,Month按月退款,Ratio按比例退款 required
     * @param float ratio_value 0.00 比例,当rule=Ratio时,需要传此值,默认为0
     */
    public function createRefundProduct($param)
    {
        $this->startTrans();

        try{
            # 验证商品是否已加至退款商品
            $existRefundProduct = $this->where('product_id',$param['product_id'])->find();
            if (!empty($existRefundProduct)){
                throw new \Exception(lang_plugins('refund_refund_product_is_exist'));
            }

            $refundProduct = $this->create([
                'product_id' => $param['product_id'],
                'admin_id' => get_admin_id(),
                'type' => $param['type']??'Artificial',
                'require' => $param['require']??'',
                'range_control' => $param['range_control']??0,
                'range' => $param['range']??0,
                'rule' => $param['rule']??'',
                'ratio_value' => $param['rule']=='Ratio'?$param['ratio_value']:0,
                'create_time' => time(),
            ]);

            $ProductModel = new ProductModel();
            $product = $ProductModel->find($param['product_id']);
            active_log(lang_plugins('refund_create_refund_product', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$param['product_id'].'#'.$product['name'].'#']), 'addon_idcsmart_refund_product', $refundProduct->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-07-07
     * @title 编辑退款商品
     * @desc 编辑退款商品
     * @author wyh
     * @version v1
     * @param int id - 退款商品ID required
     * @param int product_id - 商品ID required
     * @param string type - 退款类型:Artificial人工，Auto自动 required
     * @param string require - 退款要求:First首次订购,Same同类商品首次订购
     * @param int range_control - 购买后X天内控制:0否默认,1是(当range不传值或值为null时,传此字段为0;否则传1) required
     * @param int range - 购买后X天内
     * @param string rule - 退款规则:Day按天退款,Month按月退款,Ratio按比例退款 required
     * @param float ratio_value 0.00 比例,当rule=Ratio时,需要传此值,默认为0
     */
    public function updateRefundProduct($param)
    {
        $this->startTrans();

        try{
            $refundProduct = $this->find($param['id']);
            if (empty($refundProduct)){
                throw new \Exception(lang_plugins('refund_refund_product_is_not_exist'));
            }

            # 验证商品是否已加至退款商品
            $existRefundProduct = $this->where('product_id',$param['product_id'])
                ->where('id','<>',$param['id'])
                ->find();
            if (!empty($existRefundProduct)){
                throw new \Exception(lang_plugins('refund_refund_product_is_exist'));
            }

            $refundProduct->save([
                'product_id' => $param['product_id'],
                'type' => $param['type']??'Artificial',
                'require' => $param['require']??'',
                'range_control' => $param['range_control']??0,
                'range' => $param['range']??0,
                'rule' => $param['rule']??'',
                'ratio_value' => $param['ratio_value']??0,
                'update_time' => time(),
            ]);

            $ProductModel = new ProductModel();
            $product = $ProductModel->find($param['product_id']);
            active_log(lang_plugins('refund_update_refund_product', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$param['product_id'].'#'.$product['name'].'#']), 'addon_idcsmart_refund_product', $refundProduct->id);


            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-07-07
     * @title 删除退款商品
     * @desc 删除退款商品
     * @author wyh
     * @version v1
     * @param int id - 退款商品ID required
     */
    public function deleteRefundProduct($id)
    {
        $this->startTrans();

        try{

            $refundProduct = $this->find($id);
            if (empty($refundProduct)){
                throw new \Exception('refund_refund_product_is_not_exist');
            }

            $refundProduct->delete();

            $ProductModel = new ProductModel();
            $product = $ProductModel->find($refundProduct['product_id']);
            active_log(lang_plugins('refund_delete_refund_product', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{product}'=>'product#'.$refundProduct['product_id'].'#'.$product['name'].'#']), 'addon_idcsmart_refund_product', $id);


            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>lang_plugins('delete_fail')];
        }

        return ['status'=>200,'msg'=>lang_plugins('delete_success')];
    }

    /**
     * 时间 2022-07-07
     * @title 获取退款商品详情
     * @desc 获取退款商品详情
     * @author wyh
     * @version v1
     * @param int id - 退款商品ID required
     * @return int id - ID
     * @return int product_id - 商品ID
     * @return array config_option - 商品配置
     * @return string config_option[].name - 配置名称
     * @return string config_option[].field - 配置字段
     * @return string config_option[].type - 配置类型
     * @return array config_option[].option - 配置选项{"name":1,"value":1}
     * @return string config_option[].option[].name - 选项名称
     * @return string config_option[].option[].value - 选项值
     * @return string type - 退款类型
     * @return string require - 退款要求
     * @return int range_control - 购买后X天内控制:0否默认,1是
     * @return int range - 购买天数(若range_control==0,range默认填充空,即使返回0也填充为空)
     * @return string rule - 退款规则
     * @return float ratio_value - 比例
     */
    public function indexRefundProduct($id)
    {
        $refundProduct = $this->field('id,product_id,type,require,range_control,range,rule,ratio_value')
            ->find($id);

        if (empty($refundProduct)){
            return ['status'=>400,'msg'=>lang_plugins('refund_refund_product_is_not_exist')];
        }
        $ModuleLogic = new ModuleLogic();
        $ProductModel = new ProductModel();
        $product = $ProductModel->find($refundProduct['product_id']);
        $refundProduct['config_option'] = $ModuleLogic->allConfigOption($product);

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['refund_product'=>$refundProduct?:(object)[]]];
    }

}
