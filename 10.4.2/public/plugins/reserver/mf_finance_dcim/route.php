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
    Route::post('remf_finance_dcim/:id/on', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@on");
    Route::post('remf_finance_dcim/:id/off', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@off");
    Route::post('remf_finance_dcim/:id/reboot', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@reboot");
    Route::post('remf_finance_dcim/:id/reset_bmc', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@resetBmc");
    Route::post('remf_finance_dcim/:id/vnc', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@vnc");
    Route::post('remf_finance_dcim/:id/reset_password', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@resetPassword");
    Route::post('remf_finance_dcim/:id/rescue', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@rescue");
    Route::post('remf_finance_dcim/:id/cancel_task', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@cancelTask");
    Route::post('remf_finance_dcim/:id/reinstall', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@reinstall");
    Route::get('remf_finance_dcim/:id/chart', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@chart");
    
    Route::get('remf_finance_dcim/:id/log', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@log");

    // 升降级配置
    Route::get('remf_finance_dcim/:id/upgrade_config', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@upgradeConfig");
    Route::post('remf_finance_dcim/:id/sync_upgrade_config_price', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@syncUpgradeConfigPrice");
    Route::post('remf_finance_dcim/:id/upgrade_config', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@upgradeConfigPost");
    Route::get('remf_finance_dcim/:id/upgrade_config_page', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@upgradeConfigPostPage");
    // 升降级商品
    Route::get('remf_finance_dcim/:id/upgrade_product', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@upgradeProduct");
    Route::post('remf_finance_dcim/:id/sync_upgrade_product_price', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@syncUpgradeProductPrice");
    Route::post('remf_finance_dcim/:id/upgrade_product', "\\reserver\\mf_finance_dcim\\controller\\home\\CloudController@upgradeProductPost");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])
->middleware(\app\http\middleware\CheckHome::class)
->middleware(\app\http\middleware\ParamFilter::class);
    //->middleware(\app\http\middleware\RejectRepeatRequest::class);
// ->middleware(\reserver\mf_finance_dcim\middleware\CheckAuthMiddleware::class);
