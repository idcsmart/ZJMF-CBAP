<?php
/**
 * 插件自定义路由,此文档作为范例,注意不要和系统路由产生冲突
 * 说明:官方默认路由需要登录后才能访问,若需要免登录访问,需要自定义路由.
 * 默认路由,后台:/admin/addon?_plugin=demo_style&_controller=admin_index&_action=addhelp;
 * 前台:/console/addon?_plugin=demo_style&_controller=admin_index&_action=addhelp
 * @author wyh
 * @time 2022-06-20
 */
use think\facade\Route;

# 前台
Route::group('console/v1',function (){
    # 退款页面
    Route::get('refund', "\\addon\\idcsmart_refund\\controller\\clientarea\\RefundController@refundPage")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'refund','_action'=>'refund_page']);# 带上默认参数,可以使用继承控制器app\admin\controller\PluginBaseController的一些通用方法,也可以不追加这些参数(_plugin插件名称C风格,_controller控制器名称C风格,_action方法名称C风格)
   # 停用退款
    Route::post('refund', "\\addon\\idcsmart_refund\\controller\\clientarea\\RefundController@refund")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'refund','_action'=>'refund']);
    # 取消
    Route::put('refund/:id/cancel', "\\addon\\idcsmart_refund\\controller\\clientarea\\RefundController@cancel")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'refund','_action'=>'cancel']);
})
    ->middleware(\app\http\middleware\ParamFilter::class)
    ->middleware(\app\http\middleware\CheckHome::class);

# 后台
Route::group(DIR_ADMIN . '/v1',function (){
    # 退款商品列表
    Route::get('refund/product', "\\addon\\idcsmart_refund\\controller\\RefundProductController@refundProductList")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'refund_product_list']);
    # 新增退款商品
    Route::post('refund/product', "\\addon\\idcsmart_refund\\controller\\RefundProductController@create")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'create']);
    # 编辑退款商品
    Route::put('refund/product/:id', "\\addon\\idcsmart_refund\\controller\\RefundProductController@update")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'update']);
    # 删除退款商品
    Route::delete('refund/product/:id', "\\addon\\idcsmart_refund\\controller\\RefundProductController@delete")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'delete']);
    # 获取退款商品详情
    Route::get('refund/product/:id', "\\addon\\idcsmart_refund\\controller\\RefundProductController@index")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'index']);

    # 停用原因列表
    Route::get('refund/reason', "\\addon\\idcsmart_refund\\controller\\RefundReasonController@refundReasonList")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'refund_product_list']);
    # 新增停用原因
    Route::post('refund/reason', "\\addon\\idcsmart_refund\\controller\\RefundReasonController@create")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'create']);
    # 编辑停用原因
    Route::put('refund/reason/:id', "\\addon\\idcsmart_refund\\controller\\RefundReasonController@update")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'update']);
    # 删除停用原因
    Route::delete('refund/reason/:id', "\\addon\\idcsmart_refund\\controller\\RefundReasonController@delete")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'delete']);
    # 获取停用原因详情
    Route::get('refund/reason/:id', "\\addon\\idcsmart_refund\\controller\\RefundReasonController@index")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'index']);
    # 获取停用原因自定义设置
    Route::get('refund/reason/custom', "\\addon\\idcsmart_refund\\controller\\RefundReasonController@custom")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'custom']);
    # 停用原因自定义
    Route::post('refund/reason/custom', "\\addon\\idcsmart_refund\\controller\\RefundReasonController@customSet")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'custom_set']);

    # 停用列表
    Route::get('refund', "\\addon\\idcsmart_refund\\controller\\RefundController@refundList")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'refund_list']);
    # 通过
    Route::put('refund/:id/pending', "\\addon\\idcsmart_refund\\controller\\RefundController@pending")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'pending']);
    # 驳回
    Route::put('refund/:id/reject', "\\addon\\idcsmart_refund\\controller\\RefundController@reject")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'reject']);
    # 取消
    Route::put('refund/:id/cancel', "\\addon\\idcsmart_refund\\controller\\RefundController@cancel")
        ->append(['_plugin'=>'idcsmart_refund','_controller'=>'Refund','_action'=>'cancel']);

})
    ->middleware(\app\http\middleware\ParamFilter::class)
    ->middleware(\app\http\middleware\CheckAdmin::class);