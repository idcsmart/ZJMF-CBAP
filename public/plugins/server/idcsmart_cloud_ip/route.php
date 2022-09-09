<?php 

use think\facade\Route;

# 前台,可登录不可登录的接口
Route::group('console/v1',function (){
   
    Route::get('product/:id/idcsmart_cloud_ip/package', "\\server\\idcsmart_cloud_ip\\controller\\home\\PackageController@list");
    Route::get('idcsmart_cloud_ip/duration_price', "\\server\\idcsmart_cloud_ip\\controller\\home\\DurationPriceController@getConfigDurationPrice");

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
    
	Route::get('idcsmart_cloud_ip', "\\server\\idcsmart_cloud_ip\\controller\\home\\HostController@list");
	Route::put('idcsmart_cloud_ip/:id/mount', "\\server\\idcsmart_cloud_ip\\controller\\home\\HostController@mount");
	Route::put('idcsmart_cloud_ip/:id/umount', "\\server\\idcsmart_cloud_ip\\controller\\home\\HostController@umount");  

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
	Route::get('idcsmart_cloud_ip/package', "\\server\\idcsmart_cloud_ip\\controller\\admin\\PackageController@list");
	Route::put('idcsmart_cloud_ip/package/:id', "\\server\\idcsmart_cloud_ip\\controller\\admin\\PackageController@save");
	//Route::put('idcsmart_cloud_ip/package/:id/ip', "\\server\\idcsmart_cloud_ip\\controller\\admin\\PackageController@ipEnable");
	//Route::put('idcsmart_cloud_ip/package/:id/bw', "\\server\\idcsmart_cloud_ip\\controller\\admin\\PackageController@bwEnable");


	// 周期价格
	Route::get('idcsmart_cloud_ip/duration_price', "\\server\\idcsmart_cloud_ip\\controller\\admin\\DurationPriceController@list");
	Route::put('idcsmart_cloud_ip/duration_price', "\\server\\idcsmart_cloud_ip\\controller\\admin\\DurationPriceController@save");

})
	->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckAdmin::class)
	->middleware(\app\http\middleware\ParamFilter::class);

