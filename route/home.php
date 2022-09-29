<?php
use think\facade\Route;
Route::pattern([
    'id'   => '\d+',
    'page' => '\d+',
    'limit' => '\d+|max:50',
    'sort'   =>  'in:asc,desc',
]);
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

# 无需登录
Route::group('console/v1',function (){
    // 登录
    Route::post('login', 'home/login/login'); // 登录
    Route::post('register', 'home/login/register'); // 注册
    Route::post('account/password_reset', 'home/login/passwordReset'); // 找回密码
    
    //公共接口
    Route::get('country', 'home/common/countryList'); // 国家列表
    Route::get('captcha', 'home/common/captcha'); // 图形验证码
    Route::get('gateway', 'home/common/gateway'); // 支付接口
    Route::get('common', 'home/common/common'); // 公共配置
    Route::post('phone/code', 'home/common/sendPhoneCode'); // 发送手机验证码
	Route::post('email/code', 'home/common/sendEmailCode'); // 发送邮件验证码
    Route::post('upload', 'home/common/upload'); // 上传文件
    Route::get('menu', 'home/common/homeMenu'); // 获取导航

    // 购物车
    Route::get('product/group/first', 'home/product/productGroupFirstList'); // 获取商品一级分组
    Route::get('product/group/second', 'home/product/productGroupSecondList'); // 获取商品二级分组
    Route::get('product', 'home/product/productList'); // 商品列表
    Route::get('product/:id/config_option', 'home/product/moduleClientConfigOption'); // 商品配置页面
    Route::post('product/:id/config_option', 'home/product/moduleCalculatePrice'); // 修改配置计算价格
    Route::get('cart', 'home/cart/index'); // 获取购物车
    Route::post('cart', 'home/cart/create'); // 加入购物车
    Route::put('cart/:position', 'home/cart/update'); // 编辑购物车商品
    Route::delete('cart/:position', 'home/cart/delete'); // 删除购物车商品
    Route::put('cart/:position/qty', 'home/cart/updateQty'); // 获取购物车
    Route::delete('cart', 'home/cart/clear'); // 清空购物车
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\Check::class)
    ->middleware(\app\http\middleware\ParamFilter::class);

# 登录后访问
Route::group('console/v1',function (){
	Route::post('logout', 'home/account/logout'); // 注销
	// 账户管理
	Route::get('account', 'home/account/index'); // 账户详情
	Route::put('account', 'home/account/update'); // 账户编辑
	Route::put('account/phone/old', 'home/account/verifyOldPhone'); // 验证原手机
	Route::put('account/phone', 'home/account/updatePhone'); // 修改手机
	Route::put('account/email/old', 'home/account/verifyOldEmail'); // 验证原邮箱
	Route::put('account/email', 'home/account/updateEmail'); // 修改邮箱
    Route::put('account/password/code', 'home/account/codeUpdatePassword'); // 验证码修改密码
	Route::put('account/password', 'home/account/updatePassword'); // 修改密码
    Route::get('credit', 'home/account/creditList'); // 余额变更记录列表

	// 产品管理
	Route::get('host', 'home/host/hostList'); // 产品列表
    Route::get('host/:id', 'home/host/index'); // 产品详情
    Route::get('host/:id/module', 'home/host/clientArea'); // 产品内页模块
    Route::get('host/:id/upgrade/config_option', 'home/host/changeConfigOption'); // 产品升降级配置
    Route::post('host/:id/upgrade/config_option', 'home/host/changeConfigOptionCalculatePrice'); // 产品升降级配置计算价格
	Route::rule('module/:module/:controller/:method', 'home/module/customFunction', 'GET|POST'); // 模块自定义方法
    Route::put('host/:id/notes', 'home/host/updateHostNotes'); // 修改产品备注

	// 订单管理
	Route::get('order', 'home/order/orderList'); // 订单列表
	Route::get('order/:id', 'home/order/index'); // 订单详情

	// 消费管理
	Route::get('transaction', 'home/transaction/transactionList'); // 消费记录

	// 日志管理
	Route::get('log', 'home/log/logList'); // 操作日志

    // 支付
    Route::post('pay', 'home/pay/pay'); // 支付
    Route::get('pay/:id/status', 'home/pay/status'); // 支付状态
    Route::post('recharge', 'home/pay/recharge'); // 充值
    Route::post('credit', 'home/pay/credit'); // 使用(取消)余额

    // 购物车
    Route::post('cart/settle', 'home/cart/settle'); // 结算购物车
    Route::post('product/settle', 'home/product/settle'); // 结算商品

    // API密钥
    Route::get('api', 'home/api/list'); // API密钥列表
    Route::post('api', 'home/api/create'); // 创建API密钥
    Route::put('api/:id/white_list', 'home/api/whiteListSetting'); // API白名单设置
    Route::delete('api/:id', 'home/api/delete'); // 删除API密钥

    // 公共接口
    Route::get('global_search', 'home/common/globalSearch'); # 全局搜索

    // 实名认证
    Route::get('certification/info', 'home/certification/certificationInfo'); # 实名认证基础信息
    Route::get('certification/plugin', 'home/certification/certificationPlugin'); # 实名认证接口
    Route::get('certification/custom_fields', 'home/certification/certificationCustomfields'); # 获取实名认证自定义字段
    Route::post('certification/person', 'home/certification/certificationPerson'); # 个人认证
    Route::post('certification/company', 'home/certification/certificationCompany'); # 企业认证
    Route::post('certification/convert', 'home/certification/certificationConvert'); # 个人转企业认证
    Route::get('certification/auth', 'home/certification/certificationAuth'); # 实名认证验证页面
    Route::get('certification/status', 'home/certification/certificationStatus'); # 实名认证验证页面

})->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ])
    ->middleware(\app\http\middleware\CheckHome::class)
    ->middleware(\app\http\middleware\ParamFilter::class);


Route::get('','home/view/index'); // 前台首页
Route::get('[:view_html]','home/view/index')->ext('html'); // 前台模板
Route::get('/plugin/[:plugin_id]/[:view_html]','home/view/plugin')->ext('html'); //插件模板
Route::get('home/task','home/view/task'); // 测试
