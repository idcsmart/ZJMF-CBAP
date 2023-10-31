<?php 
namespace widget;

use app\common\lib\Widget;
use app\common\logic\IndexLogic;

class ThisYearClient extends Widget
{
    protected $weight = 80;
    
    protected $columns = 2;

    protected $language = [
        'zh-cn' => [
            'title' => '本年大客户统计',
        ],
        'en-us' => [
            'title' => 'This year\'s major customer statistics',
        ],
    ];

    public function __construct(){
        $this->title = $this->lang('title');
    }

    public function getData()
    {
    	$data = (new IndexLogic())->thisYearClient();
    	return $data;
    }

    public function output(){
    	$content = '';
    	$data = $this->getData();
        $currencyPrefix = configuration('currency_prefix');
        $title = $this->lang('title');

    	foreach($data['clients'] as $index=>$client){
    		$index = $index+1;
            $client['amount'] = number_format($client['amount'], 2);

    		$content .= <<<SUBHTML
    		<li class="t-list-item">
					<div class="t-list-item-main">
						<div class="t-list-item__content">
							<div class="customer-item">
								<div class="customer-ranking">
									<span class="ranking">{$index}</span> 
									<span class="customer-name mar-113">
                    <a href="client_detail.htm?client_id={$client['id']}" class="aHover">{$client['username']}</a>
                  </span>
								</div> 
								<span class="visit_time-itme">{$currencyPrefix}{$client['amount']}</span>
							</div>
						</div>
					</div>
				</li>
SUBHTML;
    	}
    	return <<<HTML
<div class="bottom-item">
	<div class="statistics-title">{$title}</div> 
	<div class="bottom-list t-list t-size-m">
    <ul class="t-list__inner">
		  {$content}
    </ul>
	</div>
</div>
HTML;
    }



}


