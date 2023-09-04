<?php 
namespace widget;

use app\common\lib\Widget;
use app\common\model\TransactionModel;

class TodaySale extends Widget
{
	protected $title = '今日销售额';

    protected $weight = 50;
    
    protected $columns = 1;

    protected $language = [
        'zh-cn' => [
            'title' => '今日销售额',
        ],
        'en-us' => [
            'title' => 'Sales today',
        ],
    ];

    public function __construct(){
        $this->title = $this->lang('title');
    }

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
        $title = $this->lang('title');

    	return <<<HTML
<div class="top-item"><div class="item-nums"><span class="num">{$data['today_sale_amount']}</span></div> <div class="item-title">
          {$title}{$currencySuffix}
        </div></div>
HTML;
    }



}


