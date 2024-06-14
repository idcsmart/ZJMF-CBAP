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

# 前台
Route::group('console/v1',function (){
    // 实名认证
    Route::get('certification/info', "addon\\idcsmart_certification\\controller\\clientarea\\CertificationController@certificationInfo")
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'certification_info']); # 实名认证基础信息

    Route::get('certification/plugin', 'addon\\idcsmart_certification\\controller\\clientarea\\CertificationController@certificationPlugin')
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'certification_plugin']); # 实名认证接口

    Route::get('certification/custom_fields', 'addon\\idcsmart_certification\\controller\\clientarea\\CertificationController@certificationCustomfields')
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'certification_customfields']); # 获取实名认证自定义字段

    Route::post('certification/person', 'addon\\idcsmart_certification\\controller\\clientarea\\CertificationController@certificationPerson')
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'certification_person']); # 个人认证

    Route::post('certification/company', 'addon\\idcsmart_certification\\controller\\clientarea\\CertificationController@certificationCompany')
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'certification_company']); # 企业认证

    Route::post('certification/convert', 'addon\\idcsmart_certification\\controller\\clientarea\\CertificationController@certificationConvert')
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'certification_convert']); # 个人转企业认证

    Route::get('certification/auth', 'addon\\idcsmart_certification\\controller\\clientarea\\CertificationController@certificationAuth')
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'certification_auth']); # 实名认证验证页面

    Route::get('certification/status', 'addon\\idcsmart_certification\\controller\\clientarea\\CertificationController@certificationStatus')
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'certification_status']); # 实名认证状态轮询

    Route::get('certification/plugin/config', 'addon\\idcsmart_certification\\controller\\clientarea\\CertificationController@certificationConfig')
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'certification_config']); # 实名认证接口配置

    Route::post('certification/plugin/order', 'addon\\idcsmart_certification\\controller\\clientarea\\CertificationController@certificationOrder')
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'certification_order']); # 生成实名认证订单
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\ParamFilter::class)
    ->middleware(\app\http\middleware\CheckHome::class)
    ->middleware(\app\http\middleware\RejectRepeatRequest::class);

# 后台
Route::group(DIR_ADMIN . '/v1',function (){
    Route::get('certification/config', "\\addon\\idcsmart_certification\\controller\\CertificationController@getConfig")
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'get_config']);
    Route::post('certification/config', "\\addon\\idcsmart_certification\\controller\\CertificationController@setConfig")
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'set_config']);
    Route::get('certification', "\\addon\\idcsmart_certification\\controller\\CertificationController@certificationList")
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'certification_list']);
    Route::get('certification/:id', "\\addon\\idcsmart_certification\\controller\\CertificationController@index")
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'index']);
    Route::put('certification/:id/approve', "\\addon\\idcsmart_certification\\controller\\CertificationController@approve")
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'approve']);
    Route::put('certification/:id/reject', "\\addon\\idcsmart_certification\\controller\\CertificationController@reject")
        ->append(['_plugin'=>'idcsmart_certification','_controller'=>'certification','_action'=>'reject']);

})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\ParamFilter::class)
    ->middleware(\app\http\middleware\CheckAdmin::class);