<?php 

use think\facade\Route;

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

# 前台,可登录不可登录的接口
Route::group('console/v1',function (){

	Route::get('reidcsmart_common/product/:product_id/configoption', "\\reserver\\idcsmart_common\\controller\\home\\CloudController@cartConfigoption");
    Route::post('reidcsmart_common/product/:product_id/configoption/calculate', "\\reserver\\idcsmart_common\\controller\\home\\CloudController@cartConfigoptionCalculate");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 前台需要登录的接口
Route::group('console/v1',function (){
    
    Route::get('reidcsmart_common/host', "\\reserver\\idcsmart_common\\controller\\home\\CloudController@hostList");
    Route::get('reidcsmart_common/host/:host_id/configoption', "\\reserver\\idcsmart_common\\controller\\home\\CloudController@hostConfigotpion");
    Route::get('reidcsmart_common/host/:host_id/configoption/area', "\\reserver\\idcsmart_common\\controller\\home\\CloudController@clientAreaOutput");
    Route::post('reidcsmart_common/host/:host_id/configoption/chart', "\\reserver\\idcsmart_common\\controller\\home\\CloudController@chartData");
    Route::post('reidcsmart_common/host/:host_id/provision/:func', "\\reserver\\idcsmart_common\\controller\\home\\CloudController@provisionFunc");
    Route::post('reidcsmart_common/host/:host_id/custom/provision', "\\reserver\\idcsmart_common\\controller\\home\\CloudController@provisionFuncCustom");
    // Route::get('reidcsmart_common/host/:host_id/upgrade', "\\reserver\\idcsmart_common\\controller\\home\\CloudController@upgradePage");  
    // Route::post('reidcsmart_common/host/:host_id/sync_upgrade_price', "\\reserver\\idcsmart_common\\controller\\home\\CloudController@syncUpgradePrice");
    // Route::post('reidcsmart_common/host/:host_id/upgrade', "\\reserver\\idcsmart_common\\controller\\home\\CloudController@upgrade");
    Route::get('reidcsmart_common/host/:host_id/upgrade_config', "\\reserver\\idcsmart_common\\controller\\home\\CloudController@upgradeConfigPage");
    Route::post('reidcsmart_common/host/:host_id/sync_upgrade_config_price', "\\reserver\\idcsmart_common\\controller\\home\\CloudController@syncUpgradeConfigPrice");
    Route::post('reidcsmart_common/host/:host_id/upgrade_config', "\\reserver\\idcsmart_common\\controller\\home\\CloudController@upgradeConfig");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])
->middleware(\app\http\middleware\CheckHome::class)
->middleware(\app\http\middleware\ParamFilter::class);
    //->middleware(\app\http\middleware\RejectRepeatRequest::class);
