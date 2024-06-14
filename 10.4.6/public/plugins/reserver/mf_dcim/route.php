<?php 

use think\facade\Route;

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

# 前台,可登录不可登录的接口
Route::group('console/v1',function (){

	// 订购页面
    Route::get('product/:id/remf_dcim/order_page', "\\reserver\\mf_dcim\\controller\\home\\CloudController@orderPage");
    Route::get('product/:id/remf_dcim/image', "\\reserver\\mf_dcim\\controller\\home\\CloudController@imageList");
    Route::post('product/:id/remf_dcim/duration', "\\reserver\\mf_dcim\\controller\\home\\CloudController@getAllDurationPrice");
    Route::get('product/:id/remf_dcim/line/:line_id', "\\reserver\\mf_dcim\\controller\\home\\CloudController@lineConfig");

    // vnc
    Route::get('remf_dcim/:id/vnc', "\\reserver\\mf_dcim\\controller\\home\\CloudController@vncPage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 前台需要登录的接口
Route::group('console/v1',function (){
    
    Route::post('product/:id/remf_dcim/validate_settle', "\\reserver\\mf_dcim\\controller\\home\\CloudController@validateSettle");
	Route::get('remf_dcim', "\\reserver\\mf_dcim\\controller\\home\\CloudController@list");
	Route::get('remf_dcim/:id', "\\reserver\\mf_dcim\\controller\\home\\CloudController@detail");
	Route::get('remf_dcim/:id/part', "\\reserver\\mf_dcim\\controller\\home\\CloudController@detailPart");
	Route::get('remf_dcim/:id/status', "\\reserver\\mf_dcim\\controller\\home\\CloudController@status");
	Route::get('remf_dcim/:id/chart', "\\reserver\\mf_dcim\\controller\\home\\CloudController@chart");
	Route::get('remf_dcim/:id/flow', "\\reserver\\mf_dcim\\controller\\home\\CloudController@flowDetail");
	Route::get('remf_dcim/:id/log', "\\reserver\\mf_dcim\\controller\\home\\CloudController@log");
	Route::get('remf_dcim/:id/image/check', "\\reserver\\mf_dcim\\controller\\home\\CloudController@checkHostImage");
	Route::post('remf_dcim/:id/image/order', "\\reserver\\mf_dcim\\controller\\home\\CloudController@createImageOrder");
	Route::get('remf_dcim/:id/remote_info', "\\reserver\\mf_dcim\\controller\\home\\CloudController@remoteInfo");
	Route::get('remf_dcim/:id/ip', "\\reserver\\mf_dcim\\controller\\home\\CloudController@ipList");
	Route::get('remf_dcim/:id/common_config', "\\reserver\\mf_dcim\\controller\\home\\CloudController@calCommonConfigPrice");
	Route::post('remf_dcim/:id/common_config/order', "\\reserver\\mf_dcim\\controller\\home\\CloudController@createCommonConfigOrder");

	Route::group('',function (){

		Route::post('remf_dcim/:id/on', "\\reserver\\mf_dcim\\controller\\home\\CloudController@on");
		Route::post('remf_dcim/:id/off', "\\reserver\\mf_dcim\\controller\\home\\CloudController@off");
		Route::post('remf_dcim/:id/reboot', "\\reserver\\mf_dcim\\controller\\home\\CloudController@reboot");
		Route::post('remf_dcim/batch_operate', "\\reserver\\mf_dcim\\controller\\home\\CloudController@batchOperate");
		Route::post('remf_dcim/:id/vnc', "\\reserver\\mf_dcim\\controller\\home\\CloudController@vnc");
		Route::post('remf_dcim/:id/reset_password', "\\reserver\\mf_dcim\\controller\\home\\CloudController@resetPassword");
		Route::post('remf_dcim/:id/rescue', "\\reserver\\mf_dcim\\controller\\home\\CloudController@rescue");
		Route::post('remf_dcim/:id/reinstall', "\\reserver\\mf_dcim\\controller\\home\\CloudController@reinstall");

	})->middleware(\app\http\middleware\CheckClientOperatePassword::class);  // 需要验证操作密码


})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])
->middleware(\app\http\middleware\CheckHome::class)
->middleware(\app\http\middleware\ParamFilter::class)
->middleware(\reserver\mf_dcim\middleware\CheckAuthMiddleware::class);
    //->middleware(\app\http\middleware\RejectRepeatRequest::class);


# 后台,可登录不可登录的接口
Route::group(DIR_ADMIN . '/v1',function (){
    
    Route::get('remf_dcim/:id/vnc', "\\reserver\\mf_dcim\\controller\\admin\\CloudController@vncPage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 后台接口
Route::group(DIR_ADMIN . '/v1',function (){
    
	// 实例操作接口,需要增加新的中间件用来验证权限
	Route::group('', function (){

		Route::post('remf_dcim/:id/on', "\\reserver\\mf_dcim\\controller\\admin\\CloudController@on");
		Route::post('remf_dcim/:id/off', "\\reserver\\mf_dcim\\controller\\admin\\CloudController@off");
		Route::post('remf_dcim/:id/reboot', "\\reserver\\mf_dcim\\controller\\admin\\CloudController@reboot");
		Route::post('remf_dcim/:id/vnc', "\\reserver\\mf_dcim\\controller\\admin\\CloudController@vnc");
		Route::post('remf_dcim/:id/reset_password', "\\reserver\\mf_dcim\\controller\\admin\\CloudController@resetPassword");
		Route::post('remf_dcim/:id/rescue', "\\reserver\\mf_dcim\\controller\\admin\\CloudController@rescue");
		Route::post('remf_dcim/:id/reinstall', "\\reserver\\mf_dcim\\controller\\admin\\CloudController@reinstall");

	})->middleware(\app\http\middleware\CheckAdminOperatePassword::class);  // 需要验证操作密码

	// 实例操作
	Route::get('remf_dcim/:id', "\\reserver\\mf_dcim\\controller\\admin\\CloudController@adminDetail");
	Route::get('remf_dcim/:id/status', "\\reserver\\mf_dcim\\controller\\admin\\CloudController@status");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\CheckAdmin::class);
