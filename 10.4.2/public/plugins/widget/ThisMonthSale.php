<?php 
namespace widget;

use app\common\lib\Widget;
use app\common\model\TransactionModel;

class ThisMonthSale extends Widget
{
    protected $weight = 40;
    
    protected $columns = 1;

    protected $language = [
        'zh-cn' => [
            'title' => '本月销售额',
            'mom' => '同比',
        ],
        'en-us' => [
            'title' => 'Sales this month',
            'mom' => 'month-on-month',
        ],
        'zh-hk' => [
            'title' => '本月銷售額',
            'mom' => '同比',
        ],
    ];

    public function __construct(){
        $this->title = $this->lang('title');
    }

    public function getData()
    {
        $end = time();
        $datetime = $end-strtotime(date("Y-m-d"));
    	$start = mktime(0,0,0,date("m"),1,date("Y"));

        $thisMonthAmount = TransactionModel::where('create_time', '>=', $start)->where('create_time', '<', $end)->sum('amount');

        /*if(date("m")==1){
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
        
        $prevMonthAmount = TransactionModel::where('create_time', '>=', $start)->where('create_time', '<', $end+$datetime)->sum('amount');*/
        $start = mktime(0,0,0,date("m"),1,date("Y")-1);
        $t = date("t", $start);
        if(date("d")>$t){
            $end = $start+$t*24*3600;
        }else{
            $end = $start+date("d")*24*3600;
        }
        
        $prevMonthAmount = TransactionModel::where('create_time', '>=', $start)->where('create_time', '<', $end+$datetime)->sum('amount');

        $thisMonthAmountPercent = $prevMonthAmount>0 ? bcmul(($thisMonthAmount-$prevMonthAmount)/$prevMonthAmount, 100, 1) : 100;

        $data = [
            'this_month_amount'         => amount_format($thisMonthAmount), 
            'this_month_amount_percent' => $thisMonthAmountPercent
        ];
    	return $data;
    }

    public function output(){
    	$data = $this->getData();
        $data['this_month_amount'] = number_format($data['this_month_amount'], 2);
        $currencySuffix = configuration('currency_suffix');
        if(!empty($currencySuffix)){
            $currencySuffix = '（'.$currencySuffix.'）';
        }
        $title = $this->lang('title');
        $mom = $this->lang('mom');
        if($data['this_month_amount_percent']>=0){
            return <<<HTML
            <div class="top-item increase-bg"><div class="item-nums"><span class="num">{$data['this_month_amount']}</span> <span class="trend up-green-text">{$mom} ↑ {$data['this_month_amount_percent']}%</span></div> <div class="item-title">
                      {$title}{$currencySuffix}
                    </div></div>
HTML;
        }else{
            return <<<HTML
            <div class="top-item decline-bg"><div class="item-nums"><span class="num">{$data['this_month_amount']}</span> <span class="trend down-red-text">{$mom} ↓ {$data['this_month_amount_percent']}%</span></div> <div class="item-title">
                      {$title}{$currencySuffix}
                    </div></div>
HTML;
        }
    
    }



}