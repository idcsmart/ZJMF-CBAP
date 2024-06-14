<?php 

use think\facade\Route;

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

# 前台,可登录不可登录的接口
Route::group('console/v1',function (){

	// 订购页面
    Route::get('product/:id/remf_cloud/order_page', "\\reserver\\mf_cloud\\controller\\home\\CloudController@orderPage");
    Route::get('product/:id/remf_cloud/image', "\\reserver\\mf_cloud\\controller\\home\\CloudController@imageList");
    Route::post('product/:id/remf_cloud/duration', "\\reserver\\mf_cloud\\controller\\home\\CloudController@getAllDurationPrice");
    Route::get('product/:id/remf_cloud/config_limit', "\\reserver\\mf_cloud\\controller\\home\\CloudController@getAllConfigLimit");
    Route::get('product/:id/remf_cloud/vpc_network/search', "\\reserver\\mf_cloud\\controller\\home\\CloudController@vpcNetworkSearch");
    Route::get('product/:id/remf_cloud/line/:line_id', "\\reserver\\mf_cloud\\controller\\home\\CloudController@lineConfig");

    // vnc
    Route::get('remf_cloud/:id/vnc', "\\reserver\\mf_cloud\\controller\\home\\CloudController@vncPage");


})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 前台需要登录的接口
Route::group('console/v1',function (){
    
    Route::post('product/:id/remf_cloud/validate_settle', "\\reserver\\mf_cloud\\controller\\home\\CloudController@validateSettle");
	Route::get('remf_cloud', "\\reserver\\mf_cloud\\controller\\home\\CloudController@list");
	Route::get('remf_cloud/:id', "\\reserver\\mf_cloud\\controller\\home\\CloudController@detail");
	Route::get('remf_cloud/:id/part', "\\reserver\\mf_cloud\\controller\\home\\CloudController@detailPart");
	Route::get('remf_cloud/:id/status', "\\reserver\\mf_cloud\\controller\\home\\CloudController@status");
	Route::get('remf_cloud/:id/chart', "\\reserver\\mf_cloud\\controller\\home\\CloudController@chart");
	Route::get('remf_cloud/:id/disk', "\\reserver\\mf_cloud\\controller\\home\\CloudController@disk");
	Route::post('remf_cloud/:id/disk/:disk_id/unmount', "\\reserver\\mf_cloud\\controller\\home\\CloudController@diskUnmount");
	Route::post('remf_cloud/:id/disk/:disk_id/mount', "\\reserver\\mf_cloud\\controller\\home\\CloudController@diskMount");
	Route::get('remf_cloud/:id/snapshot', "\\reserver\\mf_cloud\\controller\\home\\CloudController@snapshot");
	Route::post('remf_cloud/:id/snapshot', "\\reserver\\mf_cloud\\controller\\home\\CloudController@snapshotCreate");
	Route::post('remf_cloud/:id/snapshot/restore', "\\reserver\\mf_cloud\\controller\\home\\CloudController@snapshotRestore");
	Route::delete('remf_cloud/:id/snapshot/:snapshot_id', "\\reserver\\mf_cloud\\controller\\home\\CloudController@snapshotDelete");
	Route::get('remf_cloud/:id/backup', "\\reserver\\mf_cloud\\controller\\home\\CloudController@backup");
	Route::post('remf_cloud/:id/backup', "\\reserver\\mf_cloud\\controller\\home\\CloudController@backupCreate");
	Route::post('remf_cloud/:id/backup/restore', "\\reserver\\mf_cloud\\controller\\home\\CloudController@backupRestore");
	Route::delete('remf_cloud/:id/backup/:backup_id', "\\reserver\\mf_cloud\\controller\\home\\CloudController@backupDelete");
	Route::get('remf_cloud/:id/flow', "\\reserver\\mf_cloud\\controller\\home\\CloudController@flowDetail");
	Route::get('remf_cloud/:id/log', "\\reserver\\mf_cloud\\controller\\home\\CloudController@log");
	Route::get('remf_cloud/:id/image/check', "\\reserver\\mf_cloud\\controller\\home\\CloudController@checkHostImage");
	Route::post('remf_cloud/:id/image/order', "\\reserver\\mf_cloud\\controller\\home\\CloudController@createImageOrder");
	Route::get('remf_cloud/:id/remote_info', "\\reserver\\mf_cloud\\controller\\home\\CloudController@remoteInfo");
	Route::get('remf_cloud/:id/ip', "\\reserver\\mf_cloud\\controller\\home\\CloudController@ipList");
	Route::post('remf_cloud/:id/disk/price', "\\reserver\\mf_cloud\\controller\\home\\CloudController@calBuyDiskPrice");
	Route::post('remf_cloud/:id/disk/order', "\\reserver\\mf_cloud\\controller\\home\\CloudController@createBuyDiskOrder");
	Route::post('remf_cloud/:id/disk/resize', "\\reserver\\mf_cloud\\controller\\home\\CloudController@calResizeDiskPrice");
	Route::post('remf_cloud/:id/disk/resize/order', "\\reserver\\mf_cloud\\controller\\home\\CloudController@createResizeDiskOrder");
	Route::post('remf_cloud/:id/simulate_physical_machine', "\\reserver\\mf_cloud\\controller\\home\\CloudController@simulatePhysicalMachine");
	Route::get('remf_cloud/:id/ipv6', "\\reserver\\mf_cloud\\controller\\home\\CloudController@ipv6List");

	// 这2个价格有问题
	Route::get('remf_cloud/:id/backup_config', "\\reserver\\mf_cloud\\controller\\home\\CloudController@calBackupConfigPrice");
	Route::post('remf_cloud/:id/backup_config/order', "\\reserver\\mf_cloud\\controller\\home\\CloudController@createBackupConfigOrder");

	Route::get('remf_cloud/:id/ip_num', "\\reserver\\mf_cloud\\controller\\home\\CloudController@calIpNumPrice");
	Route::post('remf_cloud/:id/ip_num/order', "\\reserver\\mf_cloud\\controller\\home\\CloudController@createIpNumOrder");

	Route::post('remf_cloud/:id/vpc_network', "\\reserver\\mf_cloud\\controller\\home\\CloudController@createVpcNetwork");
	Route::get('remf_cloud/:id/vpc_network', "\\reserver\\mf_cloud\\controller\\home\\CloudController@vpcNetworkList");
	Route::put('remf_cloud/:id/vpc_network/:vpc_network_id', "\\reserver\\mf_cloud\\controller\\home\\CloudController@vpcNetworkUpdate");
	Route::delete('remf_cloud/:id/vpc_network/:vpc_network_id', "\\reserver\\mf_cloud\\controller\\home\\CloudController@vpcNetworkDelete");
	Route::put('remf_cloud/:id/vpc_network', "\\reserver\\mf_cloud\\controller\\home\\CloudController@changeVpcNetwork");
	Route::get('remf_cloud/:id/real_data', "\\reserver\\mf_cloud\\controller\\home\\CloudController@cloudRealData");

	Route::get('remf_cloud/:id/common_config', "\\reserver\\mf_cloud\\controller\\home\\CloudController@calCommonConfigPrice");
	Route::post('remf_cloud/:id/common_config/order', "\\reserver\\mf_cloud\\controller\\home\\CloudController@createCommonConfigOrder");

	// NAT转发建站
	Route::get('remf_cloud/:id/nat_acl', "\\reserver\\mf_cloud\\controller\\home\\CloudController@natAclList");
	Route::post('remf_cloud/:id/nat_acl', "\\reserver\\mf_cloud\\controller\\home\\CloudController@natAclCreate");
	Route::delete('remf_cloud/:id/nat_acl', "\\reserver\\mf_cloud\\controller\\home\\CloudController@natAclDelete");
	Route::get('remf_cloud/:id/nat_web', "\\reserver\\mf_cloud\\controller\\home\\CloudController@natWebList");
	Route::post('remf_cloud/:id/nat_web', "\\reserver\\mf_cloud\\controller\\home\\CloudController@natWebCreate");
	Route::delete('remf_cloud/:id/nat_web', "\\reserver\\mf_cloud\\controller\\home\\CloudController@natWebDelete");

	// 套餐升降级
	Route::get('remf_cloud/:id/recommend_config', "\\reserver\\mf_cloud\\controller\\home\\CloudController@getUpgradeRecommendConfig");
	Route::get('remf_cloud/:id/recommend_config/price', "\\reserver\\mf_cloud\\controller\\home\\CloudController@calUpgradeRecommendConfig");
	Route::post('remf_cloud/:id/recommend_config/order', "\\reserver\\mf_cloud\\controller\\home\\CloudController@createUpgradeRecommendConfigOrder");

	Route::group('',function (){

		Route::post('remf_cloud/:id/on', "\\reserver\\mf_cloud\\controller\\home\\CloudController@on");
		Route::post('remf_cloud/:id/off', "\\reserver\\mf_cloud\\controller\\home\\CloudController@off");
		Route::post('remf_cloud/:id/reboot', "\\reserver\\mf_cloud\\controller\\home\\CloudController@reboot");
		Route::post('remf_cloud/:id/hard_off', "\\reserver\\mf_cloud\\controller\\home\\CloudController@hardOff");
		Route::post('remf_cloud/:id/hard_reboot', "\\reserver\\mf_cloud\\controller\\home\\CloudController@hardReboot");
		Route::post('remf_cloud/batch_operate', "\\reserver\\mf_cloud\\controller\\home\\CloudController@batchOperate");
		Route::post('remf_cloud/:id/vnc', "\\reserver\\mf_cloud\\controller\\home\\CloudController@vnc");
		Route::post('remf_cloud/:id/reset_password', "\\reserver\\mf_cloud\\controller\\home\\CloudController@resetPassword");
		Route::post('remf_cloud/:id/rescue', "\\reserver\\mf_cloud\\controller\\home\\CloudController@rescue");
		Route::post('remf_cloud/:id/rescue/exit', "\\reserver\\mf_cloud\\controller\\home\\CloudController@exitRescue");
		Route::post('remf_cloud/:id/reinstall', "\\reserver\\mf_cloud\\controller\\home\\CloudController@reinstall");

	})->middleware(\app\http\middleware\CheckClientOperatePassword::class);  // 需要验证操作密码

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])
->middleware(\app\http\middleware\CheckHome::class)
->middleware(\app\http\middleware\ParamFilter::class)
->middleware(\reserver\mf_cloud\middleware\CheckAuthMiddleware::class);
    //->middleware(\app\http\middleware\RejectRepeatRequest::class);


# 后台,可登录不可登录的接口
Route::group(DIR_ADMIN . '/v1',function (){
    
    Route::get('remf_cloud/:id/vnc', "\\reserver\\mf_cloud\\controller\\admin\\CloudController@vncPage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 后台接口
Route::group(DIR_ADMIN . '/v1',function (){
    
	// 实例操作接口,需要增加新的中间件用来验证权限
	Route::group('', function (){

		Route::post('remf_cloud/:id/on', "\\reserver\\mf_cloud\\controller\\admin\\CloudController@on");
		Route::post('remf_cloud/:id/off', "\\reserver\\mf_cloud\\controller\\admin\\CloudController@off");
		Route::post('remf_cloud/:id/reboot', "\\reserver\\mf_cloud\\controller\\admin\\CloudController@reboot");
		Route::post('remf_cloud/:id/hard_off', "\\reserver\\mf_cloud\\controller\\admin\\CloudController@hardOff");
		Route::post('remf_cloud/:id/hard_reboot', "\\reserver\\mf_cloud\\controller\\admin\\CloudController@hardReboot");
		Route::post('remf_cloud/:id/vnc', "\\reserver\\mf_cloud\\controller\\admin\\CloudController@vnc");
		Route::post('remf_cloud/:id/reset_password', "\\reserver\\mf_cloud\\controller\\admin\\CloudController@resetPassword");
		Route::post('remf_cloud/:id/rescue', "\\reserver\\mf_cloud\\controller\\admin\\CloudController@rescue");
		Route::post('remf_cloud/:id/rescue/exit', "\\reserver\\mf_cloud\\controller\\admin\\CloudController@exitRescue");
		Route::post('remf_cloud/:id/reinstall', "\\reserver\\mf_cloud\\controller\\admin\\CloudController@reinstall");
	})->middleware(\app\http\middleware\CheckAdminOperatePassword::class);  // 需要验证操作密码

	// 实例操作
	Route::get('remf_cloud/:id', "\\reserver\\mf_cloud\\controller\\admin\\CloudController@adminDetail");
	Route::get('remf_cloud/:id/status', "\\reserver\\mf_cloud\\controller\\admin\\CloudController@status");
	Route::get('remf_cloud/:id/remote_info', "\\reserver\\mf_cloud\\controller\\admin\\CloudController@remoteInfo");


})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\CheckAdmin::class);
