<?php 

use think\facade\Route;

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

# 前台,可登录不可登录的接口
Route::group('console/v1',function (){

	// 订购页面
    Route::get('product/:id/rewhmcs_cloud/order_page', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@orderPage"); 
    Route::post('product/:id/rewhmcs_cloud/duration', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@getAllDurationPrice");

    // vnc
    Route::get('rewhmcs_cloud/:id/vnc', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@vncPage");


})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 前台需要登录的接口
Route::group('console/v1',function (){
	Route::get('product/:id/rewhmcs_cloud/upgrade_page', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@upgradePage"); 
    Route::get('rewhmcs_cloud/:id/image', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@imageList");
    Route::post('product/:id/rewhmcs_cloud/validate_settle', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@validateSettle");
	Route::get('rewhmcs_cloud', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@list");
	Route::get('rewhmcs_cloud/:id', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@detail");
	Route::get('rewhmcs_cloud/:id/status', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@status");
	Route::post('rewhmcs_cloud/:id/on', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@on");
	Route::post('rewhmcs_cloud/:id/off', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@off");
	Route::post('rewhmcs_cloud/:id/reboot', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@reboot");
	Route::post('rewhmcs_cloud/:id/hard_off', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@hardOff");
	Route::post('rewhmcs_cloud/:id/hard_reboot', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@hardReboot");
	Route::post('rewhmcs_cloud/:id/vnc', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@vnc");
	// Route::get('rewhmcs_cloud/:id/vnc', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@vncPage");
	Route::post('rewhmcs_cloud/:id/reset_password', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@resetPassword");
	Route::post('rewhmcs_cloud/:id/rescue', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@rescue");
	Route::post('rewhmcs_cloud/:id/rescue/exit', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@exitRescue");
	Route::post('rewhmcs_cloud/:id/reinstall', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@reinstall");
	Route::get('rewhmcs_cloud/:id/chart', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@chart");
	Route::get('rewhmcs_cloud/:id/snapshot', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@snapshot");
	Route::post('rewhmcs_cloud/:id/snapshot', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@snapshotCreate");
	Route::post('rewhmcs_cloud/:id/snapshot/restore', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@snapshotRestore");
	Route::delete('rewhmcs_cloud/:id/snapshot/:snapshot_id', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@snapshotDelete");
	Route::get('rewhmcs_cloud/:id/backup', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@backup");
	Route::post('rewhmcs_cloud/:id/backup', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@backupCreate");
	Route::post('rewhmcs_cloud/:id/backup/restore', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@backupRestore");
	Route::delete('rewhmcs_cloud/:id/backup/:backup_id', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@backupDelete");
	Route::get('rewhmcs_cloud/:id/flow', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@flowDetail");
	Route::get('rewhmcs_cloud/:id/flow_total', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@flowTotal");
	Route::get('rewhmcs_cloud/:id/log', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@log");



	Route::post('rewhmcs_cloud/:id/common_config', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@calCommonConfigPrice");
	Route::post('rewhmcs_cloud/:id/common_config/order', "\\reserver\\whmcs_cloud\\controller\\home\\CloudController@createCommonConfigOrder");

	
	    
})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])
->middleware(\app\http\middleware\CheckHome::class)
->middleware(\app\http\middleware\ParamFilter::class)
->middleware(\reserver\whmcs_cloud\middleware\CheckAuthMiddleware::class);
