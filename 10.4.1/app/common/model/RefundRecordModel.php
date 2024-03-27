<?php
namespace app\common\model;

use think\Model;
use think\Db;
use think\db\Query;

/**
 * @title 退款记录模型
 * @desc 退款记录模型
 * @use app\common\model\RefundRecordModel
 */
class RefundRecordModel extends Model
{
	protected $name = 'refund_record';

	// 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'order_id'              => 'int',
        'client_id'             => 'int',
        'admin_id'              => 'int',
        'type'      	        => 'string',
        'transaction_id'        => 'int',
        'amount'                => 'float',
        'create_time'           => 'int',
    ];

    /**
     * 时间 2023-01-29
     * @title 退款记录列表
     * @desc 退款记录列表
     * @author theworld
     * @version v1
     * @param int param.id - 订单ID required
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 退款记录
     * @return int list[].id - 退款记录ID 
     * @return int list[].create_time - 退款时间 
     * @return string list[].amount - 金额 
     * @return int list[].admin_id - 操作人ID 
     * @return string list[].admin_name - 操作人名称 
     * @return int count - 退款记录总数
     */
    public function refundRecordList($param)
    {
        $param['id'] = intval($param['id'] ?? 0);
        if(empty($param['id'])){
            return ['list' => [], 'count' => 0];
        }
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'a.'.$param['orderby'] : 'a.id';

        $where = function (Query $query) use($param) {
            if(!empty($param['id'])){
                $query->where('a.order_id', $param['id']);
            }
        };

        $count = $this->alias('a')
            ->field('a.id')
            ->leftJoin('admin b', 'a.admin_id=b.id')
            ->where($where)
            ->count();
        $list = $this->alias('a')
            ->field('a.id,a.create_time,a.amount,a.admin_id,b.name admin_name')
            ->leftJoin('admin b', 'a.admin_id=b.id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['amount'] = amount_format($value['amount']); // 处理金额格式
        }    

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2023-01-29
     * @title 删除退款记录
     * @desc 删除退款记录
     * @author theworld
     * @version v1
     * @param int id - 退款记录ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteRefundRecord($id)
    {
        // 验证ID
        $record = $this->find($id);
        if (empty($record)){
            return ['status'=>400, 'msg'=>lang('refund_record_is_not_exist')];
        }

        $this->startTrans();
        try {

            # 记录日志
            active_log(lang('admin_delete_refund_record', ['{admin}'=>request()->admin_name, '{refund_record}'=>'#'.$record->id]), 'refund_record', $record->id);

            $this->destroy($id);

            $refundAmount = $this->where('order_id', $record['order_id'])->sum('amount');

            OrderModel::update([
                'refund_amount' => $refundAmount,
                'update_time' => time(),
            ], ['id' => $record['order_id']]);

            if($record['type']=='transaction'){
                // 删除交易流水
                TransactionModel::destroy($record['transaction_id']);
            }else{
                update_credit([
                    'type' => 'Artificial',
                    'amount' => -$record['amount'],
                    'notes' => lang('order_cancel_refund', ['{id}' => $record['order_id']]),
                    'client_id' => $record['client_id'],
                    'order_id' => $record['order_id'],
                    'host_id' => 0
                ]);
            }            

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }

        return ['status' => 200, 'msg' => lang('delete_success')];
    }
}
