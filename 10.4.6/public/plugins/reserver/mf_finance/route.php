<?php 

use think\facade\Route;

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

# 前台,可登录不可登录的接口
Route::group('console/v1',function (){

	// 订购页面
    Route::get('product/:id/remf_finance/order_page', "\\reserver\\mf_finance\\controller\\home\\CloudController@orderPage");
    Route::get('product/:id/remf_finance/link', "\\reserver\\mf_finance\\controller\\home\\CloudController@link");
    Route::get('product/:id/remf_finance/duration', "\\reserver\\mf_finance\\controller\\home\\CloudController@cartConfigoption");

    Route::get('remf_finance/:id/vnc', "\\reserver\\mf_finance\\controller\\home\\CloudController@vncPage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 前台需要登录的接口
Route::group('console/v1',function (){

	Route::get('remf_finance', "\\reserver\\mf_finance\\controller\\home\\CloudController@list");
	Route::get('remf_finance/:id', "\\reserver\\mf_finance\\controller\\home\\CloudController@detail");
	Route::post('remf_finance/module/:id', "\\reserver\\mf_finance\\controller\\home\\CloudController@execute");
	Route::get('remf_finance', "\\reserver\\mf_finance\\controller\\home\\CloudController@list");

    Route::get('remf_finance/:id/status', "\\reserver\\mf_finance\\controller\\home\\CloudController@status");

    Route::get('remf_finance/:id/chart', "\\reserver\\mf_finance\\controller\\home\\CloudController@chart");
    Route::post('remf_finance/:id/image/order', "\\reserver\\mf_finance\\controller\\home\\CloudController@createImageOrder");
    Route::get('remf_finance/:id/ip', "\\reserver\\mf_finance\\controller\\home\\CloudController@ipList");
    Route::get('remf_finance/:id/ip_num', "\\reserver\\mf_finance\\controller\\home\\CloudController@calIpNumPrice");
    Route::post('remf_finance/:id/ip_num/order', "\\reserver\\mf_finance\\controller\\home\\CloudController@createIpNumOrder");
    Route::get('remf_finance/:id/common_config', "\\reserver\\mf_finance\\controller\\home\\CloudController@calCommonConfigPrice");
    Route::post('remf_finance/:id/common_config/order', "\\reserver\\mf_finance\\controller\\home\\CloudController@createCommonConfigOrder");
    Route::post('remf_finance/:id/custom/content', "\\reserver\\mf_finance\\controller\\home\\CloudController@postClientAreaContent");
    Route::post('remf_finance/:id/custom', "\\reserver\\mf_finance\\controller\\home\\CloudController@customFunc");
    Route::get('remf_finance/:id/trafficusage', "\\reserver\\mf_finance\\controller\\home\\CloudController@trafficusage");

    // 快照备份
    Route::get('remf_finance/:id/snapshot', "\\reserver\\mf_finance\\controller\\home\\CloudController@snapshot");
    Route::post('remf_finance/:id/snapshot', "\\reserver\\mf_finance\\controller\\home\\CloudController@snapshotPost");
    Route::delete('remf_finance/:id/snapshot/:snapshot_id', "\\reserver\\mf_finance\\controller\\home\\CloudController@snapshotDelete");
    Route::post('remf_finance/:id/snapshot/restore', "\\reserver\\mf_finance\\controller\\home\\CloudController@snapshotPut");
    Route::get('remf_finance/:id/backup', "\\reserver\\mf_finance\\controller\\home\\CloudController@backup");
    Route::post('remf_finance/:id/backup', "\\reserver\\mf_finance\\controller\\home\\CloudController@backupPost");
    Route::delete('remf_finance/:id/backup/:backup_id', "\\reserver\\mf_finance\\controller\\home\\CloudController@backupDelete");
    Route::post('remf_finance/:id/backup/restore', "\\reserver\\mf_finance\\controller\\home\\CloudController@backupPut");

    Route::get('remf_finance/:id/remote_info', "\\reserver\\mf_finance\\controller\\home\\CloudController@remoteInfo");

    Route::get('remf_finance/:id/disk', "\\reserver\\mf_finance\\controller\\home\\CloudController@disk");
    Route::get('remf_finance/:id/log', "\\reserver\\mf_finance\\controller\\home\\CloudController@log");
    Route::get('remf_finance/:id/flow', "\\reserver\\mf_finance\\controller\\home\\CloudController@flowDetail");
    Route::get('remf_finance/:id/image/check', "\\reserver\\mf_finance\\controller\\home\\CloudController@checkHostImage");

    // 升降级配置
    Route::get('remf_finance/:id/upgrade_config', "\\reserver\\mf_finance\\controller\\home\\CloudController@upgradeConfig");
    Route::post('remf_finance/:id/sync_upgrade_config_price', "\\reserver\\mf_finance\\controller\\home\\CloudController@syncUpgradeConfigPrice");
    Route::post('remf_finance/:id/upgrade_config', "\\reserver\\mf_finance\\controller\\home\\CloudController@upgradeConfigPost");
    // 升降级商品
    Route::get('remf_finance/:id/upgrade_product', "\\reserver\\mf_finance\\controller\\home\\CloudController@upgradeProduct");
    Route::post('remf_finance/:id/sync_upgrade_product_price', "\\reserver\\mf_finance\\controller\\home\\CloudController@syncUpgradeProductPrice");
    Route::post('remf_finance/:id/upgrade_product', "\\reserver\\mf_finance\\controller\\home\\CloudController@upgradeProductPost");

    Route::group('',function (){

        Route::post('remf_finance/:id/on', "\\reserver\\mf_finance\\controller\\home\\CloudController@on");
        Route::post('remf_finance/:id/off', "\\reserver\\mf_finance\\controller\\home\\CloudController@off");
        Route::post('remf_finance/:id/reboot', "\\reserver\\mf_finance\\controller\\home\\CloudController@reboot");
        Route::post('remf_finance/:id/vnc', "\\reserver\\mf_finance\\controller\\home\\CloudController@vnc");
        Route::post('remf_finance/:id/reset_password', "\\reserver\\mf_finance\\controller\\home\\CloudController@resetPassword");
        Route::post('remf_finance/:id/rescue', "\\reserver\\mf_finance\\controller\\home\\CloudController@rescue");
        Route::post('remf_finance/:id/reinstall', "\\reserver\\mf_finance\\controller\\home\\CloudController@reinstall");
        Route::post('remf_finance/:id/hard_off', "\\reserver\\mf_finance\\controller\\home\\CloudController@hardOff");
        Route::post('remf_finance/:id/hard_reboot', "\\reserver\\mf_finance\\controller\\home\\CloudController@hardReboot");
        Route::post('remf_finance/batch_operate', "\\reserver\\mf_finance\\controller\\home\\CloudController@batchOperate");
        Route::post('remf_finance/:id/exit_rescue', "\\reserver\\mf_finance\\controller\\home\\CloudController@exitRescue");

    })->middleware(\app\http\middleware\CheckClientOperatePassword::class);  // 需要验证操作密码

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])
->middleware(\app\http\middleware\CheckHome::class)
->middleware(\app\http\middleware\ParamFilter::class);
    //->middleware(\app\http\middleware\RejectRepeatRequest::class);
//->middleware(\reserver\mf_finance\middleware\CheckAuthMiddleware::class);

# 后台,可登录不可登录的接口
Route::group(DIR_ADMIN . '/v1',function (){
    
    Route::get('remf_finance/:id/vnc', "\\reserver\\mf_finance\\controller\\admin\\CloudController@vncPage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 后台接口
Route::group(DIR_ADMIN . '/v1',function (){
    
    // 实例操作接口,需要增加新的中间件用来验证权限
    Route::group('', function (){

        Route::post('remf_finance/:id/on', "\\reserver\\mf_finance\\controller\\admin\\CloudController@on");
        Route::post('remf_finance/:id/off', "\\reserver\\mf_finance\\controller\\admin\\CloudController@off");
        Route::post('remf_finance/:id/reboot', "\\reserver\\mf_finance\\controller\\admin\\CloudController@reboot");
        Route::post('remf_finance/:id/vnc', "\\reserver\\mf_finance\\controller\\admin\\CloudController@vnc");
        Route::post('remf_finance/:id/reset_password', "\\reserver\\mf_finance\\controller\\admin\\CloudController@resetPassword");
        Route::post('remf_finance/:id/rescue', "\\reserver\\mf_finance\\controller\\admin\\CloudController@rescue");
        Route::post('remf_finance/:id/reinstall', "\\reserver\\mf_finance\\controller\\admin\\CloudController@reinstall");
        Route::post('remf_finance/:id/hard_off', "\\reserver\\mf_finance\\controller\\admin\\CloudController@hardOff");
        Route::post('remf_finance/:id/hard_reboot', "\\reserver\\mf_finance\\controller\\admin\\CloudController@hardReboot");
        Route::post('remf_finance/:id/exit_rescue', "\\reserver\\mf_finance\\controller\\admin\\CloudController@exitRescue");

    })->middleware(\app\http\middleware\CheckAdminOperatePassword::class);  // 需要验证操作密码

    // 实例操作
    Route::get('remf_finance/:id', "\\reserver\\mf_finance\\controller\\admin\\CloudController@detail");
    Route::get('remf_finance/:id/status', "\\reserver\\mf_finance\\controller\\admin\\CloudController@status");
    Route::get('remf_finance/:id/remote_info', "\\reserver\\mf_finance\\controller\\admin\\CloudController@remoteInfo");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\CheckAdmin::class);
