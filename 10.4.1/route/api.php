<?php
# API开放接口
use think\facade\Route;
Route::pattern([
    'id'   => '\d+',
    'page' => '\d+',
    'limit' => '\d+|max:50',
    'sort'   =>  'in:asc,desc',
]);
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

Route::group('api/v1',function (){
    Route::get('product', 'api/product/product'); # 所有商品列表
    Route::get('group/product', 'api/product/groupProduct'); # 所有商品列表
    Route::get('product/:id', 'api/product/index'); # 商品详情
    Route::get('product/:id/resource', 'api/product/downloadResource'); # 商品资源
    Route::post('auth', 'api/auth/auth'); # 鉴权
    //Route::post('host/sync', 'api/auth/hostSync'); # 同步信息
    //Route::get('product/:id/all_config_option', 'api/product/allConfigoption'); # 所有配置
})
    ->middleware(\app\http\middleware\Check::class)
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ]
);

Route::group('api',function (){
    Route::post('host/sync', 'api/auth/hostSync'); # 同步信息
})
    ->allowCrossDomain([
            'Access-Control-Allow-Origin'        => $origin,
            'Access-Control-Allow-Credentials'   => 'true',
            'Access-Control-Max-Age'             => 600,
        ]
    );