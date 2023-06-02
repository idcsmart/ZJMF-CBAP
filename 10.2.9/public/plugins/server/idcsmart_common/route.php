<?php 

use think\facade\Route;

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// 不需要登录
Route::group('console/v1',function (){
    Route::get('idcsmart_common/product/:product_id/configoption', "\\server\\idcsmart_common\\controller\\home\\IdcsmartCommonProductController@cartConfigoption");
    Route::post('idcsmart_common/product/:product_id/configoption/calculate', "\\server\\idcsmart_common\\controller\\home\\IdcsmartCommonProductController@cartConfigoptionCalculate");
})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 前台需要登录的接口
Route::group('console/v1',function (){
    
	Route::get('idcsmart_common/host', "\\server\\idcsmart_common\\controller\\home\\IdcsmartCommonProductController@hostList");

    Route::get('idcsmart_common/host/:host_id/configoption', "\\server\\idcsmart_common\\controller\\home\\IdcsmartCommonProductController@hostConfigotpion");

    Route::get('idcsmart_common/host/:host_id/upgrade', "\\server\\idcsmart_common\\controller\\home\\IdcsmartCommonProductController@upgradePage");

    Route::post('idcsmart_common/host/:host_id/sync_upgrade_price', "\\server\\idcsmart_common\\controller\\home\\IdcsmartCommonProductController@syncUpgradePrice");

    Route::post('idcsmart_common/host/:host_id/upgrade', "\\server\\idcsmart_common\\controller\\home\\IdcsmartCommonProductController@upgrade");

    Route::get('idcsmart_common/host/:host_id/upgrade_config', "\\server\\idcsmart_common\\controller\\home\\IdcsmartCommonProductController@upgradeConfigPage");

    Route::post('idcsmart_common/host/:host_id/sync_upgrade_config_price', "\\server\\idcsmart_common\\controller\\home\\IdcsmartCommonProductController@syncUpgradeConfigPrice");

    Route::post('idcsmart_common/host/:host_id/upgrade_config', "\\server\\idcsmart_common\\controller\\home\\IdcsmartCommonProductController@upgradeConfig");
})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\CheckHome::class)
->middleware(\app\http\middleware\ParamFilter::class);

// 后台接口
Route::group(DIR_ADMIN . '/v1',function (){
    // 商品基础信息
	Route::get('idcsmart_common/product/:product_id', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductController@index");
	// 保存商品基础信息
	Route::post('idcsmart_common/product/:product_id', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductController@create");
	// 获取自定义周期详情
	Route::get('idcsmart_common/product/:product_id/custom_cycle/:id', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductController@customCycle");
	// 添加自定义周期
	Route::post('idcsmart_common/product/:product_id/custom_cycle', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductController@createCustomCycle");
	// 修改自定义周期
	Route::put('idcsmart_common/product/:product_id/custom_cycle/:id', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductController@updateCustomCycle");
	// 删除自定义周期
	Route::delete('idcsmart_common/product/:product_id/custom_cycle/:id', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductController@deleteCustomCycle");

    // 配置项
    Route::get('idcsmart_common/product/:product_id/configoption', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductConfigoptionController@configoptionList");
    Route::get('idcsmart_common/product/:product_id/configoption/quantity', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductConfigoptionController@quantityConfigoption");
    Route::get('idcsmart_common/product/:product_id/configoption/:id', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductConfigoptionController@index");
    Route::post('idcsmart_common/product/:product_id/configoption', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductConfigoptionController@create");
    Route::put('idcsmart_common/product/:product_id/configoption/:id', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductConfigoptionController@update");
    Route::delete('idcsmart_common/product/:product_id/configoption/:id', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductConfigoptionController@delete");
    Route::put('idcsmart_common/product/:product_id/configoption/:id/hidden', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductConfigoptionController@hidden");

    // 配置子项
    #Route::get('idcsmart_common/configoption/:configoption_id/sub', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductConfigoptionSubController@configoptionSubList");
    Route::get('idcsmart_common/configoption/:configoption_id/sub/:id', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductConfigoptionSubController@index");
    Route::post('idcsmart_common/configoption/:configoption_id/sub', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductConfigoptionSubController@create");
    Route::put('idcsmart_common/configoption/:configoption_id/sub/:id', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductConfigoptionSubController@update");
    Route::delete('idcsmart_common/configoption/:configoption_id/sub/:id', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonProductConfigoptionSubController@delete");

    Route::get('idcsmart_common/host/:id', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonHostController@index");
    Route::put('idcsmart_common/host/:id', "\\server\\idcsmart_common\\controller\\admin\\IdcsmartCommonHostController@update");


})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\CheckAdmin::class);