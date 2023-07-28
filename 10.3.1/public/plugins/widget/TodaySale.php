<?php 
namespace widget;

use app\common\lib\Widget;
use app\common\model\TransactionModel;

class TodaySale extends Widget
{
	protected $title = '今日销售额';

    protected $weight = 50;
    
    protected $columns = 1;

    public function getData()
    {
    	$data = [];
    	$data['today_sale_amount'] = amount_format(TransactionModel::where('create_time', '>=', strtotime(date("Y-m-d")))->sum('amount'));
    	return $data;
    }

    public function output(){
    	$data = $this->getData();
        $data['today_sale_amount'] = number_format($data['today_sale_amount'], 2);
        $currencySuffix = configuration('currency_suffix');
        if(!empty($currencySuffix)){
            $currencySuffix = '（'.$currencySuffix.'）';
        }

    	return <<<HTML
<div class="top-item"><div class="item-nums"><span class="num">{$data['today_sale_amount']}</span></div> <div class="item-title">
          今日销售额{$currencySuffix}
        </div></div>
HTML;
    }



}


