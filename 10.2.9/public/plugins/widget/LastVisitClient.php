<?php 
namespace widget;

use app\common\lib\Widget;
use app\common\model\ClientModel;

class LastVisitClient extends Widget
{
	protected $title = '最近访问用户统计';

    protected $weight = 90;
    
    protected $columns = 2;

    public function getData()
    {
    	$ClientModel = new ClientModel();
    	$data = $ClientModel->visitClientList([
    		'page'	=> 1,
    		'limit'	=> 5
    	]);
    	return $data;
    }

    public function output(){
    	$content = '';
    	$data = $this->getData();

    	foreach($data['list'] as $index=>$client){
    		$index = $index+1;
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
								<span class="visit_time-itme">{$client['visit_time']}</span>
							</div>
						</div>
					</div>
				</li>
SUBHTML;
    	}

    	return <<<HTML
<div class="bottom-item">
	<div class="statistics-title">最近访问用户统计</div> 
	<div class="table-head">
		<div>
			<span class="index-item">序号</span>
			<span class="userName-item">用户名称</span>
		</div> 
		<div class="time">访问时间</div>
	</div> 
	<div class="bottom-list t-list t-size-m">
    <ul class="t-list__inner">
		  {$content}
    </ul>
	</div>
</div>
HTML;
    }



}


