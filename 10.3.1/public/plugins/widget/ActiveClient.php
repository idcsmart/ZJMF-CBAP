<?php 
namespace widget;

use app\common\lib\Widget;
use app\common\model\ClientModel;

class ActiveClient extends Widget
{
	protected $title = '活跃用户';

    protected $weight = 60;
    
    protected $columns = 1;

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
        
    	return <<<HTML
<div class="top-item active-div"><div class="item-nums"><span class="num">{$data['active_client_count']}</span></div> <div class="item-title">
          活跃用户（人）
        </div> <div class="trend blue-text active-box">活跃率 {$data['active_client_percent']}%</div> <div class="histogram-box"><span></span> <span></span> <span></span> <span></span></div></div>
HTML;
    }



}


