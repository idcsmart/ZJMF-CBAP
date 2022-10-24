<?php 

use think\facade\Route;

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

# 前台,可登录不可登录的接口
Route::group('console/v1',function (){
    
    Route::get('product/:id/idcsmart_cloud/data_center', "\\server\\idcsmart_cloud\\controller\\home\\DataCenterController@list");
    Route::get('product/:id/idcsmart_cloud/bw_type', "\\server\\idcsmart_cloud\\controller\\home\\BwTypeController@list");
    Route::get('product/:id/idcsmart_cloud/package', "\\server\\idcsmart_cloud\\controller\\home\\PackageController@list");
    Route::get('product/:id/idcsmart_cloud/system_image', "\\server\\idcsmart_cloud\\controller\\home\\ImageController@systemImage");
    Route::get('product/:id/idcsmart_cloud/config', "\\server\\idcsmart_cloud\\controller\\home\\ConfigController@list");
    Route::get('idcsmart_cloud/duration_price', "\\server\\idcsmart_cloud\\controller\\home\\DurationPriceController@getConfigDurationPrice");

    Route::get('idcsmart_cloud/:id/vnc', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@vncPage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 前台需要登录的接口
Route::group('console/v1',function (){
    
	Route::get('idcsmart_cloud', "\\server\\idcsmart_cloud\\controller\\home\\HostController@list");
	Route::get('idcsmart_cloud/all', "\\server\\idcsmart_cloud\\controller\\home\\HostController@getAll");
	Route::get('idcsmart_cloud/:id', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@detail");
	Route::get('idcsmart_cloud/:id/status', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@status");
	Route::post('idcsmart_cloud/:id/on', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@on");
	Route::post('idcsmart_cloud/:id/off', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@off");
	Route::post('idcsmart_cloud/:id/reboot', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@reboot");
	Route::post('idcsmart_cloud/:id/hard_off', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@hardOff");
	Route::post('idcsmart_cloud/:id/hard_reboot', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@hardReboot");
	Route::post('idcsmart_cloud/:id/vnc', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@vnc");
	// Route::get('idcsmart_cloud/:id/vnc', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@vncPage");
	Route::post('idcsmart_cloud/:id/reset_password', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@resetPassword");
	Route::post('idcsmart_cloud/:id/rescue', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@rescue");
	Route::post('idcsmart_cloud/:id/rescue/exit', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@exitRescue");
	Route::post('idcsmart_cloud/:id/reinstall', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@reinstall");
	Route::get('idcsmart_cloud/:id/chart', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@chart");
	Route::get('idcsmart_cloud/:id/disk', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@disk");
	Route::get('idcsmart_cloud/:id/snapshot', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@snapshot");
	Route::post('idcsmart_cloud/:id/snapshot', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@snapshotCreate");
	Route::post('idcsmart_cloud/:id/snapshot/restore', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@snapshotRestore");
	Route::delete('idcsmart_cloud/:id/snapshot/:snapshot_id', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@snapshotDelete");
	Route::get('idcsmart_cloud/:id/backup', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@backup");
	Route::post('idcsmart_cloud/:id/backup', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@backupCreate");
	Route::post('idcsmart_cloud/:id/backup/restore', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@backupRestore");
	Route::delete('idcsmart_cloud/:id/backup/:backup_id', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@backupDelete");
	Route::get('idcsmart_cloud/:id/vpc', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@vpcNetwork");
	Route::put('idcsmart_cloud/:id/vpc', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@changeVpcNetwork");
	Route::get('idcsmart_cloud/:id/flow', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@flowDetail");
	Route::get('idcsmart_cloud/:id/log', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@log");

	Route::get('idcsmart_cloud/:id/package/upgrade', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@calUpgradePackagePrice");
	Route::post('idcsmart_cloud/:id/package/upgrade/order', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@createUpgradePackageOrder");
	Route::get('idcsmart_cloud/:id/image/check', "\\server\\idcsmart_cloud\\controller\\home\\ImageController@checkHostImage");
	Route::post('idcsmart_cloud/:id/image/order', "\\server\\idcsmart_cloud\\controller\\home\\ImageController@createImageOrder");
	Route::get('idcsmart_cloud/:id/template', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@template");
	Route::post('idcsmart_cloud/:id/template', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@templateCreate");
	Route::delete('idcsmart_cloud/:id/template/:template_id', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@templateDelete");
	Route::get('idcsmart_cloud/:id/remote_info', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@remoteInfo");
	Route::get('idcsmart_cloud/:id/ip', "\\server\\idcsmart_cloud\\controller\\home\\CloudController@ipList");
	    
})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\CheckHome::class)
->middleware(\app\http\middleware\ParamFilter::class);


# 后台,可登录不可登录的接口
Route::group(DIR_ADMIN . '/v1',function (){
    
    Route::get('idcsmart_cloud/:id/vnc', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@vncPage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 后台接口
Route::group(DIR_ADMIN . '/v1',function (){
    
	// 计算型号分组
	Route::post('idcsmart_cloud/cal_group', "\\server\\idcsmart_cloud\\controller\\admin\\CalGroupController@create");
	Route::get('idcsmart_cloud/cal_group', "\\server\\idcsmart_cloud\\controller\\admin\\CalGroupController@list");
	Route::put('idcsmart_cloud/cal_group/:id', "\\server\\idcsmart_cloud\\controller\\admin\\CalGroupController@update");
	Route::delete('idcsmart_cloud/cal_group/:id', "\\server\\idcsmart_cloud\\controller\\admin\\CalGroupController@delete");
	Route::put('idcsmart_cloud/cal_group/:id/order', "\\server\\idcsmart_cloud\\controller\\admin\\CalGroupController@updateOrder");

	// 计算型号
	Route::post('idcsmart_cloud/cal', "\\server\\idcsmart_cloud\\controller\\admin\\CalController@create");
	Route::get('idcsmart_cloud/cal', "\\server\\idcsmart_cloud\\controller\\admin\\CalController@list");
	Route::put('idcsmart_cloud/cal/:id', "\\server\\idcsmart_cloud\\controller\\admin\\CalController@update");
	Route::delete('idcsmart_cloud/cal/:id', "\\server\\idcsmart_cloud\\controller\\admin\\CalController@delete");
	Route::put('idcsmart_cloud/cal/:id/order', "\\server\\idcsmart_cloud\\controller\\admin\\CalController@updateOrder");

	// 数据中心
	Route::post('idcsmart_cloud/data_center', "\\server\\idcsmart_cloud\\controller\\admin\\DataCenterController@create");
	Route::get('idcsmart_cloud/data_center', "\\server\\idcsmart_cloud\\controller\\admin\\DataCenterController@list");
	Route::put('idcsmart_cloud/data_center/:id', "\\server\\idcsmart_cloud\\controller\\admin\\DataCenterController@update");
	Route::delete('idcsmart_cloud/data_center/:id', "\\server\\idcsmart_cloud\\controller\\admin\\DataCenterController@delete");
	Route::put('idcsmart_cloud/data_center/:id/order', "\\server\\idcsmart_cloud\\controller\\admin\\DataCenterController@updateOrder");

	// 带宽类型
	Route::post('idcsmart_cloud/bw_type', "\\server\\idcsmart_cloud\\controller\\admin\\BwTypeController@create");
	Route::get('idcsmart_cloud/bw_type', "\\server\\idcsmart_cloud\\controller\\admin\\BwTypeController@list");
	Route::put('idcsmart_cloud/bw_type/:id', "\\server\\idcsmart_cloud\\controller\\admin\\BwTypeController@update");
	Route::delete('idcsmart_cloud/bw_type/:id', "\\server\\idcsmart_cloud\\controller\\admin\\BwTypeController@delete");
	Route::put('idcsmart_cloud/bw_type/:id/order', "\\server\\idcsmart_cloud\\controller\\admin\\BwTypeController@updateOrder");

	// 带宽
	Route::post('idcsmart_cloud/bw', "\\server\\idcsmart_cloud\\controller\\admin\\BwController@create");
	Route::get('idcsmart_cloud/bw', "\\server\\idcsmart_cloud\\controller\\admin\\BwController@list");
	Route::put('idcsmart_cloud/bw/:id', "\\server\\idcsmart_cloud\\controller\\admin\\BwController@update");
	Route::delete('idcsmart_cloud/bw/:id', "\\server\\idcsmart_cloud\\controller\\admin\\BwController@delete");

	// 套餐
	Route::post('idcsmart_cloud/package', "\\server\\idcsmart_cloud\\controller\\admin\\PackageController@create");
	Route::get('idcsmart_cloud/package', "\\server\\idcsmart_cloud\\controller\\admin\\PackageController@list");
	Route::put('idcsmart_cloud/package/:id', "\\server\\idcsmart_cloud\\controller\\admin\\PackageController@update");
	Route::delete('idcsmart_cloud/package/:id', "\\server\\idcsmart_cloud\\controller\\admin\\PackageController@delete");

	// 周期价格
	Route::get('idcsmart_cloud/duration_price', "\\server\\idcsmart_cloud\\controller\\admin\\DurationPriceController@list");
	Route::put('idcsmart_cloud/duration_price', "\\server\\idcsmart_cloud\\controller\\admin\\DurationPriceController@save");

	// 镜像分组
	Route::post('idcsmart_cloud/image_group', "\\server\\idcsmart_cloud\\controller\\admin\\ImageGroupController@create");
	Route::get('idcsmart_cloud/image_group', "\\server\\idcsmart_cloud\\controller\\admin\\ImageGroupController@list");
	Route::put('idcsmart_cloud/image_group/:id', "\\server\\idcsmart_cloud\\controller\\admin\\ImageGroupController@update");
	Route::delete('idcsmart_cloud/image_group/:id', "\\server\\idcsmart_cloud\\controller\\admin\\ImageGroupController@delete");
	Route::put('idcsmart_cloud/image_group/:id/order', "\\server\\idcsmart_cloud\\controller\\admin\\ImageGroupController@updateOrder");
	Route::put('idcsmart_cloud/image_group/:id/enable', "\\server\\idcsmart_cloud\\controller\\admin\\ImageGroupController@enable");

	// 镜像
	Route::get('idcsmart_cloud/image', "\\server\\idcsmart_cloud\\controller\\admin\\ImageController@list");
	Route::put('idcsmart_cloud/image/:id/enable', "\\server\\idcsmart_cloud\\controller\\admin\\ImageController@enable");
	Route::put('idcsmart_cloud/image/:id', "\\server\\idcsmart_cloud\\controller\\admin\\ImageController@update");
	Route::get('idcsmart_cloud/image/sync', "\\server\\idcsmart_cloud\\controller\\admin\\ImageController@autoGetImage");
	Route::get('idcsmart_cloud/image/status', "\\server\\idcsmart_cloud\\controller\\admin\\ImageController@refreshImageStatus");
	Route::get('idcsmart_cloud/image/compare', "\\server\\idcsmart_cloud\\controller\\admin\\ImageController@imageCompare");

	// 其他设置
	Route::get('idcsmart_cloud/config', "\\server\\idcsmart_cloud\\controller\\admin\\ConfigController@index");
	Route::put('idcsmart_cloud/config', "\\server\\idcsmart_cloud\\controller\\admin\\ConfigController@save");

    // 产品操作
    Route::get('idcsmart_cloud/:id', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@detail");
    Route::get('idcsmart_cloud/:id/status', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@status");
	Route::post('idcsmart_cloud/:id/on', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@on");
	Route::post('idcsmart_cloud/:id/off', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@off");
	Route::post('idcsmart_cloud/:id/reboot', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@reboot");
	Route::post('idcsmart_cloud/:id/hard_off', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@hardOff");
	Route::post('idcsmart_cloud/:id/hard_reboot', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@hardReboot");
	Route::post('idcsmart_cloud/:id/vnc', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@vnc");
	// Route::get('idcsmart_cloud/:id/vnc', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@vncPage");
	Route::post('idcsmart_cloud/:id/reset_password', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@resetPassword");
	Route::post('idcsmart_cloud/:id/rescue', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@rescue");
	Route::post('idcsmart_cloud/:id/rescue/exit', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@exitRescue");
	Route::post('idcsmart_cloud/:id/reinstall', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@reinstall");
	Route::get('idcsmart_cloud/:id/chart', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@chart");
	Route::get('idcsmart_cloud/:id/disk', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@disk");
	Route::get('idcsmart_cloud/:id/snapshot', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@snapshot");
	Route::post('idcsmart_cloud/:id/snapshot', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@snapshotCreate");
	Route::post('idcsmart_cloud/:id/snapshot/restore', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@snapshotRestore");
	Route::delete('idcsmart_cloud/:id/snapshot/:snapshot_id', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@snapshotDelete");
	Route::get('idcsmart_cloud/:id/backup', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@backup");
	Route::post('idcsmart_cloud/:id/backup', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@backupCreate");
	Route::post('idcsmart_cloud/:id/backup/restore', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@backupRestore");
	Route::delete('idcsmart_cloud/:id/backup/:backup_id', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@backupDelete");
	Route::get('idcsmart_cloud/:id/vpc', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@vpcNetwork");
	Route::put('idcsmart_cloud/:id/vpc', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@changeVpcNetwork");
	Route::get('idcsmart_cloud/:id/flow', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@flowDetail");
	Route::get('idcsmart_cloud/:id/log', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@log");
	Route::get('idcsmart_cloud/:id/template', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@template");
	Route::post('idcsmart_cloud/:id/template', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@templateCreate");
	Route::delete('idcsmart_cloud/:id/template/:template_id', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@templateDelete");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\CheckAdmin::class);

// Route::get(DIR_ADMIN . '/v1/idcsmart_cloud/:id/vnc', "\\server\\idcsmart_cloud\\controller\\admin\\CloudController@vncPage");