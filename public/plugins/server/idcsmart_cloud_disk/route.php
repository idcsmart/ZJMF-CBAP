<?php 

use think\facade\Route;

# 前台,可登录不可登录的接口
Route::group('console/v1',function (){
    Route::get('product/:id/idcsmart_cloud_disk/package', "\\server\\idcsmart_cloud_disk\\controller\\home\\PackageController@list");
    Route::get('idcsmart_cloud_disk/duration_price', "\\server\\idcsmart_cloud_disk\\controller\\home\\DurationPriceController@getConfigDurationPrice");

})
	->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
	->middleware(\app\http\middleware\Check::class)
	->middleware(\app\http\middleware\ParamFilter::class);

// 前台需要登录的接口
Route::group('console/v1',function (){
    
	Route::get('idcsmart_cloud_disk', "\\server\\idcsmart_cloud_disk\\controller\\home\\HostController@list");
	Route::put('idcsmart_cloud_disk/:id/mount', "\\server\\idcsmart_cloud_disk\\controller\\home\\HostController@mount");
	Route::put('idcsmart_cloud_disk/:id/umount', "\\server\\idcsmart_cloud_disk\\controller\\home\\HostController@umount");
	Route::put('idcsmart_cloud_disk/:id/expansion', "\\server\\idcsmart_cloud_disk\\controller\\home\\HostController@expansion");
	    

})
	->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
	->middleware(\app\http\middleware\CheckHome::class)
	->middleware(\app\http\middleware\ParamFilter::class);

// 后台接口
Route::group(DIR_ADMIN . '/v1',function (){
    
	// 套餐
	Route::post('idcsmart_cloud_disk/package', "\\server\\idcsmart_cloud_disk\\controller\\admin\\PackageController@create");
	Route::get('idcsmart_cloud_disk/package', "\\server\\idcsmart_cloud_disk\\controller\\admin\\PackageController@list");
	Route::put('idcsmart_cloud_disk/package/:id', "\\server\\idcsmart_cloud_disk\\controller\\admin\\PackageController@update");
	Route::delete('idcsmart_cloud_disk/package/:id', "\\server\\idcsmart_cloud_disk\\controller\\admin\\PackageController@delete");

	// 周期价格
	Route::get('idcsmart_cloud_disk/duration_price', "\\server\\idcsmart_cloud_disk\\controller\\admin\\DurationPriceController@list");
	Route::put('idcsmart_cloud_disk/duration_price', "\\server\\idcsmart_cloud_disk\\controller\\admin\\DurationPriceController@save");

})
	->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckAdmin::class)
	->middleware(\app\http\middleware\ParamFilter::class);

