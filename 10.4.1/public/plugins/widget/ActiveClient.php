<?php 
namespace widget;

use app\common\lib\Widget;
use app\common\model\ClientModel;

class ActiveClient extends Widget
{
    protected $weight = 60;
    
    protected $columns = 1;

    protected $language = [
        'zh-cn' => [
            'title' => '活跃用户',
            'activity_rate' => '活跃率',
            'person' => '人',
        ],
        'en-us' => [
            'title' => 'Active User',
            'activity_rate' => 'Active Rate',
            'person' => 'Person',
        ],
        'zh-hk' => [
            'title' => '活躍用戶',
            'activity_rate' => '活躍率',
            'person' => '人',
        ],
    ];

    public function __construct(){
        $this->title = $this->lang('title');
    }

    public function getData()
    {
    	$data = [];
    	$active = ClientModel::where('status', 1)->where('last_login_time', '>=', time()-15*24*3600)->count();
        $count = ClientModel::where('status', 1)->count();
        $data['active_client_count'] = $active;
        $data['active_client_percent'] = $count>0 ? bcmul(($active/$count), 100, 1) : '0.0';
    	return $data;
    }

    public function output(){
    	$data = $this->getData();

        $activeUser = $this->lang('title') . '（'. $this->lang('person') .'）';
        $acitveRate = $this->lang('activity_rate');
        
    	return <<<HTML
<div class="top-item active-div"><div class="item-nums"><span class="num">{$data['active_client_count']}</span></div> <div class="item-title">
          {$activeUser}
        </div> <div class="trend blue-text active-box">{$acitveRate} {$data['active_client_percent']}%</div> <div class="histogram-box"><span></span> <span></span> <span></span> <span></span></div></div>
HTML;
    }



}


