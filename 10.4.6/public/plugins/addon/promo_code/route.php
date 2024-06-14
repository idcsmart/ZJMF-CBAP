<?php
/**
 * 插件自定义路由,此文档作为范例,注意不要和系统路由产生冲突
 * 说明:官方默认路由需要登录后才能访问,若需要免登录访问,需要自定义路由.
 * 默认路由,后台:/admin/addon?_plugin=demo_style&_controller=admin_index&_action=addhelp;
 * 前台:/console/addon?_plugin=demo_style&_controller=admin_index&_action=addhelp
 * @author wyh
 * @time 2022-06-02
 */
use think\facade\Route;

# 不需要登录
Route::get('country1', 'home/common/countryList')
    ->middleware(\app\http\middleware\Check::class); // 国家列表
# 前台需要登录授权,使用\app\http\middleware\CheckHome中间件
Route::get('country2', 'home/common/countryList')
    ->middleware(\app\http\middleware\CheckHome::class); // 国家列表
# 后台需要登录授权,使用\app\http\middleware\CheckAdmin中间件
Route::get('country3', 'home/common/countryList')
    ->middleware(\app\http\middleware\CheckAdmin::class); // 国家列表
# 允许跨域
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
Route::get('country4', 'home/common/countryList')
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ]);

# 前台
Route::group('console/v1',function (){
    # 应用优惠码
    Route::post('promo_code/apply', "\\addon\\promo_code\\controller\\clientarea\\IndexController@apply")
        ->append(['_plugin'=>'promo_code','_controller'=>'index','_action'=>'apply']); # 带上默认参数,可以使用继承控制器app\admin\controller\PluginBaseController的一些通用方法,也可以不追加这些参数(_plugin插件名称C风格,_controller控制器名称C风格,_action方法名称C风格)
    # 产品内页获取优惠码信息
    Route::get('promo_code/host/:id/promo_code', "\\addon\\promo_code\\controller\\clientarea\\IndexController@hostPromoCode")
        ->append(['_plugin'=>'promo_code','_controller'=>'index','_action'=>'host_promo_code']);
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckHome::class);

Route::group(DIR_ADMIN . '/v1',function (){
    # 优惠码列表
    Route::get('promo_code', "\\addon\\promo_code\\controller\\AdminIndexController@promoCodeList")
        ->append(['_plugin'=>'promo_code','_controller'=>'admin_index','_action'=>'promo_code_list']);
    # 获取随机优惠码
    Route::get('promo_code/generate', "\\addon\\promo_code\\controller\\AdminIndexController@generate")
        ->append(['_plugin'=>'promo_code','_controller'=>'admin_index','_action'=>'generate']);
    # 添加优惠码
    Route::post('promo_code', "\\addon\\promo_code\\controller\\AdminIndexController@create")
        ->append(['_plugin'=>'promo_code','_controller'=>'admin_index','_action'=>'create']);
    # 获取优惠码
    Route::get('promo_code/:id', "\\addon\\promo_code\\controller\\AdminIndexController@index")
        ->append(['_plugin'=>'promo_code','_controller'=>'admin_index','_action'=>'index']);
    # 编辑优惠码
    Route::put('promo_code/:id', "\\addon\\promo_code\\controller\\AdminIndexController@update")
        ->append(['_plugin'=>'promo_code','_controller'=>'admin_index','_action'=>'update']);
    # 启用/停用优惠码
    Route::put('promo_code/status', "\\addon\\promo_code\\controller\\AdminIndexController@status")
        ->append(['_plugin'=>'promo_code','_controller'=>'admin_index','_action'=>'status']);
    # 删除优惠码
    Route::delete('promo_code', "\\addon\\promo_code\\controller\\AdminIndexController@delete")
        ->append(['_plugin'=>'promo_code','_controller'=>'admin_index','_action'=>'delete']);
    # 优惠码使用记录
    Route::get('promo_code/:id/log', "\\addon\\promo_code\\controller\\AdminIndexController@logList")
        ->append(['_plugin'=>'promo_code','_controller'=>'admin_index','_action'=>'log_list']);
    # 产品优惠码使用记录
    Route::get('promo_code/host/:id/log', "\\addon\\promo_code\\controller\\AdminIndexController@hostPromoCodeLog")
        ->append(['_plugin'=>'promo_code','_controller'=>'admin_index','_action'=>'host_promo_code_log']);
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\ParamFilter::class)
    ->middleware(\app\http\middleware\CheckAdmin::class);


