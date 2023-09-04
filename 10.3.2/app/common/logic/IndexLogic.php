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
    public function index()
    {
        $data = $this->getIndexSaleInfo();
        $active = ClientModel::where('status', 1)->where('last_login_time', '>=', time()-15*24*3600)->count();
        $count = ClientModel::where('status', 1)->count();
        $data['active_client_count'] = $active;
        $data['active_client_percent'] = $count>0 ? bcmul(($active/$count), 100, 1) : '0.0';

        $data['today_sale_amount'] = amount_format(TransactionModel::where('create_time', '>=', strtotime(date("Y-m-d")))->sum('amount'));

        return $data;
    }

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
            $end = $end + $datetime;
            //$end = $end > strtotime(date("Y-m-d")) ? strtotime(date("Y-m-d")) : $end;
            $time = time();
            $end = $end > $time  ? $time : $end;
            $amount = TransactionModel::where('create_time', '>=', $start)->where('create_time', '<', $end)->sum('amount');
            $thisYearMonthAmount[] = ['month' => $i, 'amount' => amount_format($amount)];
        }
        return ['this_year_month_amount' => $thisYearMonthAmount];
    }

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

    public function getIndexSaleInfo()
    {
        # 获取今年销售额，截止到昨天
        $start = mktime(0,0,0,1,1,date("Y"));
        //$end = strtotime(date("Y-m-d"));
        $end = time();
        $datetime = $end-strtotime(date("Y-m-d"));

        $thisYearAmount = TransactionModel::where('create_time', '>=', $start)->where('create_time', '<', $end)->sum('amount');

        # 获取去年销售额，截止到去年的昨天同日期
        $start = mktime(0,0,0,1,1,date("Y")-1);
        if(date("m")==2){
            $t = date("t", $start);
            if(date("d")>$t){
                $end = strtotime(date((date("Y")-1)."-m-".$t));
            }else{
                $end = strtotime(date((date("Y")-1)."-m-d"));
            }
        }else{
            $end = strtotime(date((date("Y")-1)."-m-d"));
        }
        $prevYearAmount = TransactionModel::where('create_time', '>=', $start)->where('create_time', '<', $end+$datetime)->sum('amount');

        $thisYearAmountPercent = $prevYearAmount>0 ? bcmul(($thisYearAmount-$prevYearAmount)/$prevYearAmount, 100, 1) : 100;

        # 获取本月销售额， 截止到昨天
        $start = mktime(0,0,0,date("m"),1,date("Y"));
        //$end = strtotime(date("Y-m-d"));
        $end = time();
        $thisMonthAmount = TransactionModel::where('create_time', '>=', $start)->where('create_time', '<', $end)->sum('amount');

        # 获取上月销售额， 截止到上月的昨天同日期
        if(date("m")==1){
            $start = mktime(0,0,0,12,1,date("Y")-1);
        }else{
            $start = mktime(0,0,0,date("m")-1,1,date("Y"));
        }
        $t = date("t", $start);
        if(date("d")>$t){
            $end = $start+$t*24*3600;
        }else{
            $end = $start+date("d")*24*3600;
        }
        
        $prevMonthAmount = TransactionModel::where('create_time', '>=', $start)->where('create_time', '<', $end+$datetime)->sum('amount');

        $thisMonthAmountPercent = $prevMonthAmount>0 ? bcmul(($thisMonthAmount-$prevMonthAmount)/$prevMonthAmount, 100, 1) : 100;

        return ['this_year_amount' => amount_format($thisYearAmount), 'this_year_amount_percent' => $thisYearAmountPercent, 'this_month_amount' => amount_format($thisMonthAmount), 'this_month_amount_percent' => $thisMonthAmountPercent];
    }
}