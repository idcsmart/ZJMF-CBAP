<?php 
namespace widget;

use app\common\lib\Widget;
use app\admin\model\AdminModel;

class OnlineAdmin extends Widget
{
	protected $title = '当前在线管理员';

    protected $weight = 100;
    
    protected $columns = 2;

    public function getData()
    {
    	$AdminModel = new AdminModel();
    	$data = $AdminModel->onlineAdminList([
    		'page'	=> 1,
    		'limit'	=> 5
    	]);
    	return $data;
    }

    public function output(){
    	$content = '';
    	$data = $this->getData();

    	foreach($data['list'] as $index=>$admin){
    		$index = $index+1;
    		$content .= <<<SUBHTML
    		<ul class="t-list__inner">
				<li class="t-list-item">
					<div class="t-list-item-main">
						<div class="t-list-item__content">
							<div class="customer-item">
								<div class="customer-ranking">
									<span class="ranking">{$index}</span> 
									<span class="customer-name mar-113">{$admin['name']}</span>
								</div> 
								<span class="visit_time-itme">{$admin['last_action_time']}</span>
							</div>
						</div>
					</div>
				</li>
			</ul>
SUBHTML;
    	}
    	return <<<HTML
<div class="bottom-item">
	<div class="statistics-title">当前在线管理员</div> 
	<div class="table-head">
		<div>
			<span class="index-item">序号</span>
			<span class="userName-item">管理员</span>
		</div> 
		<div class="time">上次活动时间</div>
	</div> 
	<div class="bottom-list t-list t-size-m">
		{$content}
		<div class="t-list__load"></div>
	</div>
</div>
HTML;
    }



}


