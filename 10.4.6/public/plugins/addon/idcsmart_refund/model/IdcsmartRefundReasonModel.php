<?php
namespace addon\idcsmart_refund\model;

use addon\idcsmart_refund\IdcsmartRefund;
use app\admin\model\PluginModel;
use think\db\Query;
use think\Model;

/*
 * @author wyh
 * @time 2022-07-07
 */
class IdcsmartRefundReasonModel extends Model
{
    protected $name = 'addon_idcsmart_refund_reason';

    // 设置字段信息
    protected $schema = [
        'id'                               => 'int',
        'content'                          => 'string',
        'admin_id'                         => 'int',
        'create_time'                      => 'int',
        'update_time'                      => 'int',
    ];

    /**
     * 时间 2022-07-07
     * @title 停用原因列表
     * @desc 停用原因列表
     * @author wyh
     * @version v1
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序:id
     * @param string param.sort - 升/降序:asc,desc
     * @param string param.keywords - 关键字搜索:内容
     * @return array list - 停用原因列表
     * @return int list[].id - ID
     * @return string list[].content - 内容
     * @return string list[].admin_name - 提交人
     * @return string list[].create_time - 提交时间
     * @return int count - 数量
     */
    public function refundReasonList($param)
    {
        if (!isset($param['orderby']) || !in_array($param['orderby'],['id'])){
            $param['orderby'] = 'rr.id';
        }
        $where = function (Query $query) use ($param){
            if (isset($param['keywords']) && !empty($param['keywords'])){
                $query->where('rr.content','like',"%{$param['keywords']}%");
            }
        };
        $refundReasons = $this->alias('rr')
            ->field('rr.id,rr.content,a.name as admin_name,rr.create_time')
            ->leftJoin('admin a','a.id=rr.admin_id')
            ->where($where)
            ->select()
            ->toArray();

        $count = $this->alias('rr')
            ->field('rr.id,rr.content,a.name as admin_name,rr.create_time')
            ->leftJoin('admin a','a.id=rr.admin_id')
            ->where($where)
            ->count();

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['list'=>$refundReasons,'count'=>$count]];

    }

    /**
     * 时间 2022-07-07
     * @title 新增停用原因
     * @desc 新增停用原因
     * @author wyh
     * @version v1
     * @param string content - 内容 required
     */
    public function createRefundReason($param)
    {
        $this->startTrans();

        try{
            $refundReason = $this->create([
                'content' => $param['content']??'',
                'admin_id' => get_admin_id(),
                'create_time' => time()
            ]);

            active_log(lang_plugins('refund_create_refund_reason', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{reason}'=>$param['content']]), 'addon_idcsmart_refund_reason', $refundReason->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-07-07
     * @title 编辑停用原因
     * @desc 编辑停用原因
     * @author wyh
     * @version v1
     * @param int id - 停用原因ID required
     * @param string content - 内容 required
     */
    public function updateRefundReason($param)
    {
        $this->startTrans();

        try{
            $refundReason = $this->find($param['id']);
            if (empty($refundReason)){
                throw new \Exception(lang_plugins('refund_refund_reason_is_not_exist'));
            }

            $refundReason->save([
                'content' => $param['content']??'',
                'update_time' => time()
            ]);

            active_log(lang_plugins('refund_update_refund_reason', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{reason}'=>$param['content']]), 'addon_idcsmart_refund_reason', $refundReason->id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

    /**
     * 时间 2022-07-07
     * @title 删除停用原因
     * @desc 删除停用原因
     * @author wyh
     * @version v1
     * @param int id - 停用原因ID required
     */
    public function deleteRefundReason($id)
    {
        $this->startTrans();

        try{
            $refundReason = $this->find($id);
            if (empty($refundReason)){
                throw new \Exception(lang_plugins('refund_refund_reason_is_not_exist'));
            }
            $content = $refundReason['content'];

            $refundReason->delete();

            active_log(lang_plugins('refund_delete_refund_reason', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#','{reason}'=>$content]), 'addon_idcsmart_refund_reason', $id);

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang_plugins('delete_success')];
    }

    /**
     * 时间 2022-07-07
     * @title 获取停用原因详情
     * @desc 获取停用原因详情
     * @author wyh
     * @version v1
     * @param int id - 退款商品ID required
     * @return int id - ID
     * @return string content - 内容
     */
    public function indexRefundReason($id)
    {
        $refundReason = $this->field('id,content')
            ->find($id);

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['refund_reason'=>$refundReason?:(object)[]]];
    }

    /**
     * 时间 2022-07-07
     * @title 获取停用原因自定义设置
     * @desc 获取停用原因自定义设置
     * @author wyh
     * @version v1
     * @return int reason_custom - 停用原因是否自定义:1是,0否默认
     */
    public function indexCustomRefundReason()
    {
        $IdcsmartRefund = new IdcsmartRefund();
        $config = $IdcsmartRefund->getConfig();

        $reasonCustom = isset($config['reason_custom'])?intval($config['reason_custom']):0;

        return ['status'=>200,'msg'=>lang_plugins('success_message'),'data'=>['reason_custom'=>$reasonCustom]];
    }

    /**
     * 时间 2022-07-07
     * @title 停用原因自定义
     * @desc 停用原因自定义
     * @author wyh
     * @version v1
     * @param int reason_custom - 停用原因是否自定义:1是,0否默认 required
     */
    public function customRefundReason($param)
    {
        $PluginModel = new PluginModel();
        $idcsmartRefund = $PluginModel->where('name','IdcsmartRefund')->find();
        $confifg = json_decode($idcsmartRefund->config,true);
        if (isset($confifg['reason_custom']) && intval($confifg['reason_custom']) == $param['reason_custom']){
            return ['status'=>400,'msg'=>lang_plugins('cannot_repeat_opreate')];
        }
        $confifg['reason_custom'] = $param['reason_custom']??0;

        $idcsmartRefund->save([
            'config' => json_encode($confifg),
            'update_time' => time()
        ]);

        if ($param['reason_custom'] == 1){
            active_log(lang_plugins('refund_start_refund_reason_custom', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#']), 'plugin', $idcsmartRefund->id);
        }else{
            active_log(lang_plugins('refund_stop_refund_reason_custom', ['{admin}'=>'admin#'.get_admin_id().'#'.request()->admin_name.'#']), 'plugin', $idcsmartRefund->id);

        }

        return ['status'=>200,'msg'=>lang_plugins('success_message')];
    }

}
