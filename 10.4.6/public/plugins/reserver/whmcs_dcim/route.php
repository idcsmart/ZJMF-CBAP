<?php 

use think\facade\Route;

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

# 前台,可登录不可登录的接口
Route::group('console/v1',function (){

	// 订购页面
    Route::get('product/:id/rewhmcs_dcim/order_page', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@orderPage");
    Route::post('product/:id/rewhmcs_dcim/duration', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@getAllDurationPrice");
    // Route::get('product/:id/rewhmcs_dcim/config_limit', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@getAllConfigLimit");
    Route::get('product/:id/rewhmcs_dcim/line/:line_id', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@lineConfig");

    // vnc
    Route::get('rewhmcs_dcim/:id/vnc', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@vncPage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 前台需要登录的接口
Route::group('console/v1',function (){
	Route::get('product/:id/rewhmcs_dcim/upgrade_page', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@upgradePage");
    Route::get('rewhmcs_dcim/:id/image', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@imageList");
    Route::post('product/:id/rewhmcs_dcim/validate_settle', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@validateSettle");
	Route::get('rewhmcs_dcim', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@list");
	Route::get('rewhmcs_dcim/:id', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@detail");
	Route::get('rewhmcs_dcim/:id/status', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@status");
	Route::get('rewhmcs_dcim/:id/chart', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@chart");
	Route::get('rewhmcs_dcim/:id/flow', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@flowDetail");
	Route::get('rewhmcs_dcim/:id/log', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@log");
	Route::post('rewhmcs_dcim/:id/image/order', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@createImageOrder");
	Route::get('rewhmcs_dcim/:id/remote_info', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@remoteInfo");
	Route::get('rewhmcs_dcim/:id/ip', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@ipList");
	Route::get('rewhmcs_dcim/:id/ip_num', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@calIpNumPrice");
	Route::post('rewhmcs_dcim/:id/ip_num/order', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@createIpNumOrder");
	Route::post('rewhmcs_dcim/:id/common_config', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@calCommonConfigPrice");
	Route::post('rewhmcs_dcim/:id/common_config/order', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@createCommonConfigOrder");

	Route::group('',function (){

		Route::post('rewhmcs_dcim/:id/on', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@on");
		Route::post('rewhmcs_dcim/:id/off', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@off");
		Route::post('rewhmcs_dcim/:id/reboot', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@reboot");
		Route::post('rewhmcs_dcim/batch_operate', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@batchOperate");
		Route::post('rewhmcs_dcim/:id/vnc', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@vnc");
		Route::post('rewhmcs_dcim/:id/reset_password', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@resetPassword");
		Route::post('rewhmcs_dcim/:id/rescue', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@rescue");
		Route::post('rewhmcs_dcim/:id/reinstall', "\\reserver\\whmcs_dcim\\controller\\home\\CloudController@reinstall");

	})->middleware(\app\http\middleware\CheckClientOperatePassword::class);  // 需要验证操作密码

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])
->middleware(\app\http\middleware\CheckHome::class)
->middleware(\app\http\middleware\ParamFilter::class)
->middleware(\reserver\whmcs_dcim\middleware\CheckAuthMiddleware::class);
    //->middleware(\app\http\middleware\RejectRepeatRequest::class);

# 后台,可登录不可登录的接口
Route::group(DIR_ADMIN . '/v1',function (){
    
    Route::get('rewhmcs_dcim/:id/vnc', "\\reserver\\whmcs_dcim\\controller\\admin\\CloudController@vncPage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 后台接口
Route::group(DIR_ADMIN . '/v1',function (){
    
	// 实例操作接口,需要增加新的中间件用来验证权限
	Route::group('', function (){

		Route::post('rewhmcs_dcim/:id/on', "\\reserver\\whmcs_dcim\\controller\\admin\\CloudController@on");
		Route::post('rewhmcs_dcim/:id/off', "\\reserver\\whmcs_dcim\\controller\\admin\\CloudController@off");
		Route::post('rewhmcs_dcim/:id/reboot', "\\reserver\\whmcs_dcim\\controller\\admin\\CloudController@reboot");
		Route::post('rewhmcs_dcim/:id/vnc', "\\reserver\\whmcs_dcim\\controller\\admin\\CloudController@vnc");
		Route::post('rewhmcs_dcim/:id/reset_password', "\\reserver\\whmcs_dcim\\controller\\admin\\CloudController@resetPassword");
		Route::post('rewhmcs_dcim/:id/rescue', "\\reserver\\whmcs_dcim\\controller\\admin\\CloudController@rescue");
		Route::post('rewhmcs_dcim/:id/reinstall', "\\reserver\\whmcs_dcim\\controller\\admin\\CloudController@reinstall");

	})->middleware(\app\http\middleware\CheckAdminOperatePassword::class);  // 需要验证操作密码

	// 实例操作
	Route::get('rewhmcs_dcim/:id/image', "\\reserver\\whmcs_dcim\\controller\\admin\\CloudController@imageList");
	Route::get('rewhmcs_dcim/:id', "\\reserver\\whmcs_dcim\\controller\\admin\\CloudController@detail");
	Route::get('rewhmcs_dcim/:id/status', "\\reserver\\whmcs_dcim\\controller\\admin\\CloudController@status");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\CheckAdmin::class);

