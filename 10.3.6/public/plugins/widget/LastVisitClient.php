<?php 
namespace widget;

use app\common\lib\Widget;
use app\common\model\ClientModel;

class LastVisitClient extends Widget
{
    protected $weight = 90;
    
    protected $columns = 2;

    protected $language = [
        'zh-cn' => [
            'title' => '最近访问用户统计',
            'index' => '序号',
            'client_name' => '用户名称',
            'visit_time' => '访问时间',
        ],
        'en-us' => [
            'title' => 'Recently visited user statistics',
            'index' => 'index',
            'client_name' => 'username',
            'visit_time' => 'visit time',
        ],
    ];

    public function __construct(){
        $this->title = $this->lang('title');
    }

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

        $title = $this->lang('title');
        $indexShow = $this->lang('index');
        $clientName = $this->lang('client_name');
        $visitTime = $this->lang('visit_time');

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
	<div class="statistics-title">{$title}</div> 
	<div class="table-head">
		<div>
			<span class="index-item">{$indexShow}</span>
			<span class="userName-item">{$clientName}</span>
		</div> 
		<div class="time">{$visitTime}</div>
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


