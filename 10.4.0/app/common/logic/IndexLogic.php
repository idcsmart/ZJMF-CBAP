<?php 
namespace app\common\logic;

use app\common\model\ClientModel;
use app\common\model\TransactionModel;

/**
 * @title 首页逻辑
 * @desc 首页逻辑
 * @use app\common\logic\IndexLogic
 */
class IndexLogic
{   
    /**
     * 时间 2022-5-27
     * @title 本年销售详情
     * @desc 本年销售详情
     * @author theworld
     * @version v1
     * @return array this_year_month_amount - 本年销售详情
     * @return int this_year_month_amount.month - 月份
     * @return string this_year_month_amount.amount - 销售额
     */
    public function thisYearSale()
    {
        # 获取今年销售额，截止到昨天
        $start = mktime(0,0,0,1,1,date("Y"));
        //$end = strtotime(date("Y-m-d"));
        $end = time();
        $datetime = $end-strtotime(date("Y-m-d"));
        
        $thisYearMonthAmount = [];

        for($i=1;$i<=date("m");$i++){
            $start = mktime(0,0,0,$i,1,date("Y"));
            $end = $start+date("t", $start)*24*3600;
            if($i==date("m")){
                $end = $end + $datetime;
                //$end = $end > strtotime(date("Y-m-d")) ? strtotime(date("Y-m-d")) : $end;
                $time = time();
                $end = $end > $time  ? $time : $end;
            }
            
            $amount = TransactionModel::where('create_time', '>=', $start)->where('create_time', '<', $end)->sum('amount');
            $thisYearMonthAmount[] = ['month' => $i, 'amount' => amount_format($amount)];
        }
        return ['this_year_month_amount' => $thisYearMonthAmount];
    }

    /**
     * 时间 2022-5-27
     * @title 本年大客户统计
     * @desc 本年大客户统计
     * @author theworld
     * @version v1
     * @return array clients - 本年大客户
     * @return int clients.id - 用户ID
     * @return string clients.username - 用户名
     * @return string clients.email - 邮箱
     * @return string clients.phone_code - 国际电话区号
     * @return string clients.phone - 手机号
     * @return string clients.company - 公司
     * @return string clients.amount - 消费金额
     */
    public function thisYearClient()
    {
        # 获取今年销售额，截止到昨天
        $start = mktime(0,0,0,1,1,date("Y"));
        //$end = strtotime(date("Y-m-d"));
        $end = time();
        $datetime = $end-strtotime(date("Y-m-d"));

        $clients = TransactionModel::alias('t')
            ->field('c.id,c.username,c.email,c.phone_code,c.phone,c.company,sum(t.amount) amount')
            ->leftjoin('client c','c.id=t.client_id')
            ->where('t.create_time', '>=', $start)
            ->where('t.create_time', '<', $end)
            ->where('c.id', '>', 0)
            ->group('t.client_id')
            ->select()->toArray();
        array_multisort(array_column($clients, 'amount'), SORT_DESC, $clients);
        $clients = array_slice($clients, 0, 7);
        return ['clients' => $clients];
    }
}