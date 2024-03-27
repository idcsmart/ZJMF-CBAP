<?php 
namespace widget;

use app\common\lib\Widget;
use app\admin\model\AdminModel;

class OnlineAdmin extends Widget
{
    protected $weight = 100;
    
    protected $columns = 2;

    protected $language = [
        'zh-cn' => [
            'title' => '当前在线管理员',
            'index' => '序号',
            'admin' => '管理员',
            'last_active_time' => '上次活动时间',
        ],
        'en-us' => [
            'title' => 'current online administrator',
			'index' => 'index',
			'admin' => 'administrator',
			'last_active_time' => 'last active time',
        ],
        'zh-hk' => [
            'title' => '目前在線管理員',
            'index' => '序號',
            'admin' => '管理員',
            'last_active_time' => '上次活動時間',
        ],
    ];

    public function __construct(){
        $this->title = $this->lang('title');
    }

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

    	$title = $this->lang('title');
        $indexShow = $this->lang('index');
        $adminShow = $this->lang('admin');
        $lastActiveTime = $this->lang('last_active_time');

    	foreach($data['list'] as $index=>$admin){
    		$index = $index+1;
    		$content .= <<<SUBHTML
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
SUBHTML;
    	}
    	return <<<HTML
<div class="bottom-item">
	<div class="statistics-title">{$title}</div> 
	<div class="table-head">
		<div>
			<span class="index-item">{$indexShow}</span>
			<span class="userName-item">{$adminShow}</span>
		</div> 
		<div class="time">{$lastActiveTime}</div>
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