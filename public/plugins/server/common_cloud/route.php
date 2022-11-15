<?php 

use think\facade\Route;

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

# 前台,可登录不可登录的接口
Route::group('console/v1',function (){

    Route::get('product/:id/common_cloud/data_center', "\\server\\common_cloud\\controller\\home\\DataCenterController@list");
    Route::get('product/:id/common_cloud/package', "\\server\\common_cloud\\controller\\home\\PackageController@list");
    Route::post('product/:id/common_cloud/duration', "\\server\\common_cloud\\controller\\home\\PackageController@getAllDurationPrice");
    Route::get('product/:id/common_cloud/config', "\\server\\common_cloud\\controller\\home\\ConfigController@list");
    Route::get('product/:id/common_cloud/image', "\\server\\common_cloud\\controller\\home\\ImageController@list");
    Route::get('common_cloud/:id/vnc', "\\server\\common_cloud\\controller\\home\\CloudController@vncPage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 前台需要登录的接口
Route::group('console/v1',function (){
    
	Route::get('common_cloud', "\\server\\common_cloud\\controller\\home\\CloudController@list");
	// Route::get('common_cloud/all', "\\server\\common_cloud\\controller\\home\\HostController@getAll");
	Route::get('common_cloud/:id', "\\server\\common_cloud\\controller\\home\\CloudController@detail");
	Route::get('common_cloud/:id/status', "\\server\\common_cloud\\controller\\home\\CloudController@status");
	Route::post('common_cloud/:id/on', "\\server\\common_cloud\\controller\\home\\CloudController@on");
	Route::post('common_cloud/:id/off', "\\server\\common_cloud\\controller\\home\\CloudController@off");
	Route::post('common_cloud/:id/reboot', "\\server\\common_cloud\\controller\\home\\CloudController@reboot");
	Route::post('common_cloud/:id/hard_off', "\\server\\common_cloud\\controller\\home\\CloudController@hardOff");
	Route::post('common_cloud/:id/hard_reboot', "\\server\\common_cloud\\controller\\home\\CloudController@hardReboot");
	Route::post('common_cloud/:id/vnc', "\\server\\common_cloud\\controller\\home\\CloudController@vnc");
	// Route::get('common_cloud/:id/vnc', "\\server\\common_cloud\\controller\\home\\CloudController@vncPage");
	Route::post('common_cloud/:id/reset_password', "\\server\\common_cloud\\controller\\home\\CloudController@resetPassword");
	Route::post('common_cloud/:id/rescue', "\\server\\common_cloud\\controller\\home\\CloudController@rescue");
	Route::post('common_cloud/:id/rescue/exit', "\\server\\common_cloud\\controller\\home\\CloudController@exitRescue");
	Route::post('common_cloud/:id/reinstall', "\\server\\common_cloud\\controller\\home\\CloudController@reinstall");
	Route::get('common_cloud/:id/chart', "\\server\\common_cloud\\controller\\home\\CloudController@chart");
	Route::get('common_cloud/:id/disk', "\\server\\common_cloud\\controller\\home\\CloudController@disk");
	Route::get('common_cloud/:id/snapshot', "\\server\\common_cloud\\controller\\home\\CloudController@snapshot");
	Route::post('common_cloud/:id/snapshot', "\\server\\common_cloud\\controller\\home\\CloudController@snapshotCreate");
	Route::post('common_cloud/:id/snapshot/restore', "\\server\\common_cloud\\controller\\home\\CloudController@snapshotRestore");
	Route::delete('common_cloud/:id/snapshot/:snapshot_id', "\\server\\common_cloud\\controller\\home\\CloudController@snapshotDelete");
	Route::get('common_cloud/:id/backup', "\\server\\common_cloud\\controller\\home\\CloudController@backup");
	Route::post('common_cloud/:id/backup', "\\server\\common_cloud\\controller\\home\\CloudController@backupCreate");
	Route::post('common_cloud/:id/backup/restore', "\\server\\common_cloud\\controller\\home\\CloudController@backupRestore");
	Route::delete('common_cloud/:id/backup/:backup_id', "\\server\\common_cloud\\controller\\home\\CloudController@backupDelete");
	Route::get('common_cloud/:id/flow', "\\server\\common_cloud\\controller\\home\\CloudController@flowDetail");
	Route::get('common_cloud/:id/log', "\\server\\common_cloud\\controller\\home\\CloudController@log");
	Route::get('common_cloud/:id/package/upgrade', "\\server\\common_cloud\\controller\\home\\PackageController@calUpgradePackagePrice");
	Route::post('common_cloud/:id/package/upgrade/order', "\\server\\common_cloud\\controller\\home\\PackageController@createUpgradePackageOrder");
	Route::get('common_cloud/:id/image/check', "\\server\\common_cloud\\controller\\home\\ImageController@checkHostImage");
	Route::post('common_cloud/:id/image/order', "\\server\\common_cloud\\controller\\home\\ImageController@createImageOrder");
	Route::get('common_cloud/:id/remote_info', "\\server\\common_cloud\\controller\\home\\CloudController@remoteInfo");
	Route::get('common_cloud/:id/ip', "\\server\\common_cloud\\controller\\home\\CloudController@ipList");
	Route::get('common_cloud/:id/disk/price', "\\server\\common_cloud\\controller\\home\\CloudController@calBuyDiskPrice");
	Route::post('common_cloud/:id/disk/order', "\\server\\common_cloud\\controller\\home\\CloudController@createBuyDiskOrder");
	Route::post('common_cloud/:id/disk/resize', "\\server\\common_cloud\\controller\\home\\CloudController@calResizeDiskPrice");
	Route::post('common_cloud/:id/disk/resize/order', "\\server\\common_cloud\\controller\\home\\CloudController@createResizeDiskOrder");
	Route::get('common_cloud/:id/backup_config', "\\server\\common_cloud\\controller\\home\\CloudController@calBackupConfigPrice");
	Route::post('common_cloud/:id/backup_config/order', "\\server\\common_cloud\\controller\\home\\CloudController@createBackupConfigOrder");
	    
})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])
->middleware(\app\http\middleware\CheckHome::class)
->middleware(\app\http\middleware\ParamFilter::class)
->middleware(\server\common_cloud\middleware\CheckAuthMiddleware::class);


# 后台,可登录不可登录的接口
Route::group(DIR_ADMIN . '/v1',function (){
    
    // Route::get('common_cloud/:id/vnc', "\\server\\common_cloud\\controller\\admin\\CloudController@vncPage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 后台接口
Route::group(DIR_ADMIN . '/v1',function (){
    
    // 数据中心
	Route::post('common_cloud/data_center', "\\server\\common_cloud\\controller\\admin\\DataCenterController@create");
	Route::get('common_cloud/data_center', "\\server\\common_cloud\\controller\\admin\\DataCenterController@list");
	Route::put('common_cloud/data_center/:id', "\\server\\common_cloud\\controller\\admin\\DataCenterController@update");
	Route::delete('common_cloud/data_center/:id', "\\server\\common_cloud\\controller\\admin\\DataCenterController@delete");
	Route::put('common_cloud/data_center/:id/order', "\\server\\common_cloud\\controller\\admin\\DataCenterController@updateOrder");

	// 套餐
	Route::post('common_cloud/package', "\\server\\common_cloud\\controller\\admin\\PackageController@create");
	Route::get('common_cloud/package', "\\server\\common_cloud\\controller\\admin\\PackageController@list");
	Route::put('common_cloud/package/:id', "\\server\\common_cloud\\controller\\admin\\PackageController@update");
	Route::delete('common_cloud/package/:id', "\\server\\common_cloud\\controller\\admin\\PackageController@delete");
	Route::put('common_cloud/package/:id/order', "\\server\\common_cloud\\controller\\admin\\PackageController@updateOrder");

	// 备份/快照设置
	Route::post('common_cloud/backup_config', "\\server\\common_cloud\\controller\\admin\\BackupConfigController@create");
	Route::get('common_cloud/backup_config', "\\server\\common_cloud\\controller\\admin\\BackupConfigController@list");
	Route::put('common_cloud/backup_config/:id', "\\server\\common_cloud\\controller\\admin\\BackupConfigController@update");
	Route::delete('common_cloud/backup_config/:id', "\\server\\common_cloud\\controller\\admin\\BackupConfigController@delete");

	// 设置
	Route::get('common_cloud/config', "\\server\\common_cloud\\controller\\admin\\ConfigController@index");
	Route::put('common_cloud/config', "\\server\\common_cloud\\controller\\admin\\ConfigController@save");

	// 镜像
	Route::get('common_cloud/image', "\\server\\common_cloud\\controller\\admin\\ImageController@list");
	Route::put('common_cloud/image', "\\server\\common_cloud\\controller\\admin\\ImageController@batchSave");
	Route::get('common_cloud/image/sync', "\\server\\common_cloud\\controller\\admin\\ImageController@getImage");




	// Route::delete('common_cloud/:id/template/:template_id', "\\server\\common_cloud\\controller\\admin\\CloudController@templateDelete");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\CheckAdmin::class);

// Route::get(DIR_ADMIN . '/v1/common_cloud/:id/vnc', "\\server\\common_cloud\\controller\\admin\\CloudController@vncPage");