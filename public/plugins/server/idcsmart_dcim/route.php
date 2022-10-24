<?php 

use think\facade\Route;

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

# 前台,可登录不可登录的接口
Route::group('console/v1',function (){

    Route::get('product/:id/idcsmart_dcim/data_center', "\\server\\idcsmart_dcim\\controller\\home\\DataCenterController@list");
    Route::get('product/:id/idcsmart_dcim/package', "\\server\\idcsmart_dcim\\controller\\home\\PackageController@list");
    Route::get('product/:id/idcsmart_dcim/image', "\\server\\idcsmart_dcim\\controller\\home\\ImageController@list");
    Route::get('idcsmart_dcim/:id/vnc', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@vncPage");
    Route::post('product/:id/idcsmart_dcim/duration', "\\server\\idcsmart_dcim\\controller\\home\\PackageController@getAllDurationPrice");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 前台需要登录的接口
Route::group('console/v1',function (){
    
	Route::get('idcsmart_dcim', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@list");
	// Route::get('idcsmart_dcim/all', "\\server\\idcsmart_dcim\\controller\\home\\HostController@getAll");
	Route::get('idcsmart_dcim/:id', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@detail");
	Route::get('idcsmart_dcim/:id/status', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@status");
	Route::post('idcsmart_dcim/:id/on', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@on");
	Route::post('idcsmart_dcim/:id/off', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@off");
	Route::post('idcsmart_dcim/:id/reboot', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@reboot");
	Route::post('idcsmart_dcim/:id/hard_off', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@hardOff");
	Route::post('idcsmart_dcim/:id/hard_reboot', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@hardReboot");
	Route::post('idcsmart_dcim/:id/vnc', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@vnc");
	// Route::get('idcsmart_dcim/:id/vnc', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@vncPage");
	Route::post('idcsmart_dcim/:id/reset_password', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@resetPassword");
	Route::post('idcsmart_dcim/:id/rescue', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@rescue");
	Route::post('idcsmart_dcim/:id/reinstall', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@reinstall");
	Route::get('idcsmart_dcim/:id/chart', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@chart");
	Route::get('idcsmart_dcim/:id/flow', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@flowDetail");
	Route::get('idcsmart_dcim/:id/log', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@log");

	Route::get('idcsmart_dcim/:id/package/upgrade', "\\server\\idcsmart_dcim\\controller\\home\\PackageController@calUpgradePackagePrice");
	Route::post('idcsmart_dcim/:id/package/upgrade/order', "\\server\\idcsmart_dcim\\controller\\home\\PackageController@createUpgradePackageOrder");
	Route::get('idcsmart_dcim/:id/image/check', "\\server\\idcsmart_dcim\\controller\\home\\ImageController@checkHostImage");
	Route::post('idcsmart_dcim/:id/image/order', "\\server\\idcsmart_dcim\\controller\\home\\ImageController@createImageOrder");
	Route::get('idcsmart_dcim/:id/ip', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@ipList");
	Route::get('idcsmart_dcim/:id/remote_info', "\\server\\idcsmart_dcim\\controller\\home\\CloudController@remoteInfo");
	    
})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\CheckHome::class)
->middleware(\app\http\middleware\ParamFilter::class);


# 后台,可登录不可登录的接口
Route::group(DIR_ADMIN . '/v1',function (){
    
    // Route::get('idcsmart_dcim/:id/vnc', "\\server\\idcsmart_dcim\\controller\\admin\\CloudController@vncPage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 后台接口
Route::group(DIR_ADMIN . '/v1',function (){
    
    // 数据中心
	Route::post('idcsmart_dcim/data_center', "\\server\\idcsmart_dcim\\controller\\admin\\DataCenterController@create");
	Route::get('idcsmart_dcim/data_center', "\\server\\idcsmart_dcim\\controller\\admin\\DataCenterController@list");
	Route::put('idcsmart_dcim/data_center/:id', "\\server\\idcsmart_dcim\\controller\\admin\\DataCenterController@update");
	Route::delete('idcsmart_dcim/data_center/:id', "\\server\\idcsmart_dcim\\controller\\admin\\DataCenterController@delete");
	Route::put('idcsmart_dcim/data_center/:id/order', "\\server\\idcsmart_dcim\\controller\\admin\\DataCenterController@updateOrder");

	// 套餐
	Route::post('idcsmart_dcim/package', "\\server\\idcsmart_dcim\\controller\\admin\\PackageController@create");
	Route::get('idcsmart_dcim/package', "\\server\\idcsmart_dcim\\controller\\admin\\PackageController@list");
	Route::put('idcsmart_dcim/package/:id', "\\server\\idcsmart_dcim\\controller\\admin\\PackageController@update");
	Route::delete('idcsmart_dcim/package/:id', "\\server\\idcsmart_dcim\\controller\\admin\\PackageController@delete");
	Route::put('idcsmart_dcim/package/:id/order', "\\server\\idcsmart_dcim\\controller\\admin\\PackageController@updateOrder");

	// 镜像
	Route::get('idcsmart_dcim/image', "\\server\\idcsmart_dcim\\controller\\admin\\ImageController@list");
	Route::put('idcsmart_dcim/image', "\\server\\idcsmart_dcim\\controller\\admin\\ImageController@batchSave");
	Route::get('idcsmart_dcim/image/sync', "\\server\\idcsmart_dcim\\controller\\admin\\ImageController@getImage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\CheckAdmin::class);

// Route::get(DIR_ADMIN . '/v1/idcsmart_dcim/:id/vnc', "\\server\\idcsmart_dcim\\controller\\admin\\CloudController@vncPage");