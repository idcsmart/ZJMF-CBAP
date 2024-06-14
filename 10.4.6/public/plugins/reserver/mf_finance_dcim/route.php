<?php 

use think\facade\Route;

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

# 前台,可登录不可登录的接口
Route::group('console/v1',function (){

	// 订购页面
    Route::get('product/:id/remf_finance_dcim/order_page', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@orderPage");
    Route::get('product/:id/remf_finance_dcim/link', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@link");
    Route::get('product/:id/remf_finance_dcim/duration', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@cartConfigoption");

    // vnc
    Route::get('remf_finance_dcim/:id/vnc', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@vncPage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 前台需要登录的接口
Route::group('console/v1',function (){

	Route::get('remf_finance_dcim', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@list");
	Route::get('remf_finance_dcim/:id', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@detail");
    Route::get('remf_finance_dcim/:id/status', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@status");
    Route::get('remf_finance_dcim/:id/chart', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@chart");
    
    Route::get('remf_finance_dcim/:id/log', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@log");

    // 升降级配置
    Route::get('remf_finance_dcim/:id/upgrade_config', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@upgradeConfig");
    Route::post('remf_finance_dcim/:id/sync_upgrade_config_price', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@syncUpgradeConfigPrice");
    Route::post('remf_finance_dcim/:id/upgrade_config', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@upgradeConfigPost");
    // 升降级商品
    Route::get('remf_finance_dcim/:id/upgrade_product', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@upgradeProduct");
    Route::post('remf_finance_dcim/:id/sync_upgrade_product_price', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@syncUpgradeProductPrice");
    Route::post('remf_finance_dcim/:id/upgrade_product', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@upgradeProductPost");

    Route::group('',function (){

        Route::post('remf_finance_dcim/:id/on', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@on");
        Route::post('remf_finance_dcim/:id/off', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@off");
        Route::post('remf_finance_dcim/:id/reboot', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@reboot");
        Route::post('remf_finance_dcim/batch_operate', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@batchOperate");
        Route::post('remf_finance_dcim/:id/reset_bmc', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@resetBmc");
        Route::post('remf_finance_dcim/:id/vnc', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@vnc");
        Route::post('remf_finance_dcim/:id/reset_password', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@resetPassword");
        Route::post('remf_finance_dcim/:id/rescue', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@rescue");
        Route::post('remf_finance_dcim/:id/cancel_task', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@cancelTask");
        Route::post('remf_finance_dcim/:id/reinstall', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@reinstall");

    })->middleware(\app\http\middleware\CheckClientOperatePassword::class);  // 需要验证操作密码

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])
->middleware(\app\http\middleware\CheckHome::class)
->middleware(\app\http\middleware\ParamFilter::class);
    //->middleware(\app\http\middleware\RejectRepeatRequest::class);
// ->middleware(\reserver\mf_finance_dcim\middleware\CheckAuthMiddleware::class);


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

        Route::post('remf_finance_dcim/:id/on', "\\reserver\\mf_finance_dcim\\controller\\admin\\CloudController@on");
        Route::post('remf_finance_dcim/:id/off', "\\reserver\\mf_finance_dcim\\controller\\admin\\CloudController@off");
        Route::post('remf_finance_dcim/:id/reboot', "\\reserver\\mf_finance_dcim\\controller\\admin\\CloudController@reboot");
        Route::post('remf_finance_dcim/:id/reset_bmc', "\\reserver\\mf_finance_dcim\\controller\\admin\\CloudController@resetBmc");
        Route::post('remf_finance_dcim/:id/vnc', "\\reserver\\mf_finance_dcim\\controller\\admin\\CloudController@vnc");
        Route::post('remf_finance_dcim/:id/reset_password', "\\reserver\\mf_finance_dcim\\controller\\admin\\CloudController@resetPassword");
        Route::post('remf_finance_dcim/:id/rescue', "\\reserver\\mf_finance_dcim\\controller\\admin\\CloudController@rescue");
        Route::post('remf_finance_dcim/:id/cancel_task', "\\reserver\\mf_finance_dcim\\controller\\admin\\CloudController@cancelTask");
        Route::post('remf_finance_dcim/:id/reinstall', "\\reserver\\mf_finance_dcim\\controller\\admin\\CloudController@reinstall");

    })->middleware(\app\http\middleware\CheckAdminOperatePassword::class);  // 需要验证操作密码

    // 实例操作
    Route::get('remf_finance_dcim/:id', "\\reserver\\mf_finance_dcim\\controller\\admin\\CloudController@detail");
    Route::get('remf_finance_dcim/:id/status', "\\reserver\\mf_finance_dcim\\controller\\admin\\CloudController@status");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\CheckAdmin::class);
