<?php
use think\facade\Route;
Route::pattern([
    'id'   => '\d+',
    'page' => '\d+',
    'limit' => '\d+|max:50',
    'sort'   =>  'in:asc,desc',
]);
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

# 开放接口
Route::group(DIR_ADMIN.'/v1',function (){
    Route::get('login', 'admin/public/loginInfo'); # 登录信息
    Route::post('login', 'admin/public/login'); # 登录
    Route::get('captcha', 'admin/public/captcha'); # 图形验证码
    Route::get('test', 'admin/public/test'); #测试接口
})
    ->allowCrossDomain([
        'Access-Control-Allow-Origin'        => $origin,
        'Access-Control-Allow-Credentials'   => 'true',
        'Access-Control-Max-Age'             => 600,
    ]
);

# 应用商店
Route::post(DIR_ADMIN.'/v1/app_market/check_token', 'admin/appMarket/checkToken'); # 校验token
Route::post(DIR_ADMIN.'/v1/app_market/app/:id/install', 'admin/appMarket/install'); # 安装应用

Route::group(DIR_ADMIN.'/v1',function (){
    Route::post('logout', 'admin/admin/logout'); #注销
	Route::get('index', 'admin/index/index'); # 首页
    Route::get('index/this_year_sale', 'admin/index/thisYearSale'); # 本年销售详情
    Route::get('index/this_year_client', 'admin/index/thisYearClient'); # 本年大客户
    Route::get('index/online_admin', 'admin/index/onlineAdmin'); # 在线管理员列表
    Route::get('index/visit_client', 'admin/index/visitClient'); # 最近访问用户列表

	# 管理员管理
    Route::get('admin', 'admin/admin/adminList'); # 管理员列表
    Route::get('admin/:id', 'admin/admin/index'); # 获取单个管理员
    Route::post('admin', 'admin/admin/create'); # 添加管理员
    Route::put('admin/:id', 'admin/admin/update'); # 修改管理员
    Route::delete('admin/:id', 'admin/admin/delete'); # 删除管理员
    Route::put('admin/:id/status', 'admin/admin/status'); # 管理员状态切换
    Route::put('admin/password/update', 'admin/admin/updatePassword'); # 修改管理员密码

	# 管理员分组管理
    Route::get('admin/role', 'admin/adminRole/adminRoleList'); # 管理员分组列表
    Route::get('admin/role/:id', 'admin/adminRole/index'); # 获取单个管理员分组
    Route::post('admin/role', 'admin/adminRole/create'); # 添加管理员分组
    Route::put('admin/role/:id', 'admin/adminRole/update'); # 修改管理员分组
    Route::delete('admin/role/:id', 'admin/adminRole/delete'); # 删除管理员分组
	
	# 用户管理
	Route::get('client', 'admin/client/clientList'); # 用户列表
	Route::get('client/:id', 'admin/client/index'); # 获取单个用户
    Route::post('client', 'admin/client/create'); # 添加用户
    Route::put('client/:id', 'admin/client/update'); # 修改用户
    Route::delete('client/:id', 'admin/client/delete'); # 删除用户
    Route::put('client/:id/status', 'admin/client/status'); # 用户状态切换
    Route::get('client/search', 'admin/client/search'); # 搜索用户
    Route::post('client/:id/login', 'admin/client/login'); # 以用户登录

    # 用户余额管理
    Route::get('client/:id/credit', 'admin/clientCredit/clientCreditList'); # 用户余额变更记录列表
    Route::put('client/:id/credit', 'admin/clientCredit/update'); # 更改用户余额
    Route::post('client/:id/recharge', 'admin/clientCredit/recharge'); # 充值

    # 订单管理
    Route::get('order', 'admin/order/orderList'); # 订单列表
    Route::get('order/:id', 'admin/order/index'); # 获取单个订单
    Route::post('order', 'admin/order/create'); # 添加订单
    Route::put('order/:id/amount', 'admin/order/updateAmount'); # 修改订单金额
    Route::put('order/item/:id', 'admin/order/updateOrderItem'); # 编辑人工调整的订单子项
    Route::delete('order/item/:id', 'admin/order/deleteOrderItem'); # 删除人工调整的订单子项
    Route::put('order/:id/status/paid', 'admin/order/paid'); # 标记支付
    Route::delete('order/:id', 'admin/order/delete'); # 删除订单
    Route::delete('order', 'admin/order/batchDelete'); # 批量删除订单
    Route::post('order/upgrade/amount', 'admin/order/getUpgradeAmount'); # 获取升降级订单金额
    Route::post('order/:id/refund', 'admin/order/orderRefund'); # 订单退款
    Route::post('order/:id/apply_credit', 'admin/order/orderApplyCredit'); # 订单退款
    Route::post('order/:id/remove_credit', 'admin/order/orderRemoveCredit'); # 订单退款
    Route::get('order/:id/refund_record', 'admin/order/refundRecordList'); # 订单退款记录列表
    Route::delete('refund_record/:id', 'admin/order/deleteRefundRecord'); # 删除退款记录
    Route::put('order/:id/gateway', 'admin/order/updateGateway'); # 修改订单支付方式
    Route::put('order/:id/notes', 'admin/order/updateNotes'); # 修改订单备注
    Route::post('product/settle', 'admin/order/settle'); # 后台结算商品
    Route::get('product/:id/config_option', 'admin/order/moduleClientConfigOption'); # 商品配置页面

    # 产品管理
    Route::get('host', 'admin/host/hostList'); # 产品列表
    Route::get('host/:id', 'admin/host/index'); # 获取单个产品
    Route::put('host/:id', 'admin/host/update'); # 修改产品
    Route::delete('host/:id', 'admin/host/delete'); # 删除产品
    Route::delete('host', 'admin/host/batchDelete'); # 批量删除产品
    Route::post('host/:id/module/create', 'admin/host/createAccount'); # 模块开通
    Route::post('host/:id/module/suspend', 'admin/host/suspendAccount'); # 模块暂停
    Route::post('host/:id/module/unsuspend', 'admin/host/unsuspendAccount'); # 模块解除暂停
    Route::post('host/:id/module/terminate', 'admin/host/terminateAccount'); # 模块删除
    Route::get('host/:id/module', 'admin/host/adminArea'); # 产品内页模块
    Route::get('host/:id/module/button', 'admin/host/moduleButton'); # 模块按钮输出
    Route::get('host/:id/module/field', 'admin/host/moduleField'); # 模块输入框输出
    Route::get('host/:id/upgrade/config_option', 'admin/host/changeConfigOption'); # 产品升降级配置
    Route::post('host/:id/upgrade/config_option', 'admin/host/changeConfigOptionCalculatePrice'); # 产品升降级配置计算价格
    Route::get('client/:id/host/all', 'admin/host/clientHost'); # 获取用户所有产品

    #交易流水管理
    Route::get('transaction', 'admin/transaction/transactionList'); # 交易流水列表
    Route::post('transaction', 'admin/transaction/create'); # 添加交易流水
    Route::put('transaction/:id', 'admin/transaction/update'); # 编辑交易流水
    Route::delete('transaction/:id', 'admin/transaction/delete'); # 删除交易流水

    #任务管理
    Route::get('task', 'admin/task/taskList'); # 任务列表
    Route::put('task/:id/retry', 'admin/task/retry'); # 重试

    #日志管理
    Route::get('log/system', 'admin/log/systemLogList'); # 系统日志列表
    Route::get('log/notice/email', 'admin/log/emailLogList'); # 邮件通知日志列表
    Route::get('log/notice/sms', 'admin/log/smsLogList'); # 短信通知日志列表

	# 配置项
    Route::get('configuration/system', 'admin/Configuration/systemList'); # 获取系统设置
    Route::put('configuration/system', 'admin/Configuration/systemUpdate'); # 保存系统设置
	Route::get('configuration/login', 'admin/Configuration/loginList'); # 获取登录设置
    Route::put('configuration/login', 'admin/Configuration/loginUpdate'); # 保存登录设置
	Route::get('configuration/security', 'admin/Configuration/securityList'); # 获取安全设置
    Route::put('configuration/security', 'admin/Configuration/securityUpdate'); # 保存安全设置
	Route::get('configuration/security/captcha', 'admin/Configuration/securityCaptcha'); # 图形验证码预览
	Route::get('configuration/currency', 'admin/Configuration/currencyList'); # 获取货币设置
    Route::put('configuration/currency', 'admin/Configuration/currencyUpdate'); # 保存货币设置
	Route::get('configuration/cron', 'admin/Configuration/cronList'); # 获取定时任务
    Route::put('configuration/cron', 'admin/Configuration/cronUpdate'); # 保存定时任务
    Route::get('configuration/theme', 'admin/Configuration/themeList'); # 获取主题设置
    Route::put('configuration/theme', 'admin/Configuration/themeUpdate'); # 保存主题设置
    Route::get('configuration/info', 'admin/Configuration/infoList'); # 获取信息配置
    Route::put('configuration/info', 'admin/Configuration/infoUpdate'); # 保存信息配置
    Route::get('configuration/debug', 'admin/Configuration/debugInfo'); # debug页面
    Route::put('configuration/debug', 'admin/Configuration/debug'); # 保存debug页面

	# 邮件模板管理
    Route::get('notice/email/template', 'admin/NoticeEmail/emailTemplateList'); # 获取邮件模板
	Route::get('notice/email/template/:id', 'admin/NoticeEmail/index'); # 获取单个邮件模板
    Route::post('notice/email/template', 'admin/NoticeEmail/create'); # 创建邮件模板
    Route::put('notice/email/template/:id', 'admin/NoticeEmail/update'); # 修改邮件模板
    Route::delete('notice/email/template/:id', 'admin/NoticeEmail/delete'); # 删除邮件模板
    Route::get('notice/email/:name/template/:id/test', 'admin/NoticeEmail/test'); # 测试邮件模板
	
	# 短信模板管理
	Route::get('notice/sms/:name/template', 'admin/NoticeSms/templateList'); # 获取短信模板
	Route::get('notice/sms/:name/template/:id', 'admin/NoticeSms/index'); # 获取单个短信模板
    Route::post('notice/sms/:name/template', 'admin/NoticeSms/create'); # 创建短信模板
    Route::put('notice/sms/:name/template/:id', 'admin/NoticeSms/update'); # 修改短信模板
    Route::delete('notice/sms/:name/template/:id', 'admin/NoticeSms/delete'); # 删除短信模板
    Route::get('notice/sms/:name/template/:id/test', 'admin/NoticeSms/test'); # 测试短信模板
    Route::get('notice/sms/:name/template/status', 'admin/NoticeSms/status'); # 更新模板审核状态
    Route::post('notice/sms/:name/template/audit', 'admin/NoticeSms/audit'); # 提交审核短信模板

	# 通知发送管理
    Route::get('notice/send', 'admin/NoticeSetting/settingList'); # 发送管理
    Route::put('notice/send', 'admin/NoticeSetting/update'); # 发送设置

    # 插件 module=gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表
    Route::get('plugin/sync', 'admin/plugin/sync'); # 插件同步
    Route::get('plugin/:id/download', 'admin/plugin/download'); # 插件下载
    Route::get('plugin/:module', 'admin/plugin/pluginList'); # 插件列表
    Route::post('plugin/:module/:name', 'admin/plugin/install'); # 插件安装
    Route::delete('plugin/:module/:name', 'admin/plugin/uninstall'); # 插件卸载
    Route::put('plugin/:module/:name/:status', 'admin/plugin/status'); # 禁用(启用)插件
    Route::get('plugin/:module/:name', 'admin/plugin/setting'); # 获取单个插件配置
    Route::put('plugin/:module/:name', 'admin/plugin/settingPost'); # 保存配置
    Route::post('plugin/:module/:name/upgrade', 'admin/plugin/upgrade'); # 插件升级

    # 商品与商品分组管理
    Route::get('product', 'admin/product/productList'); # 商品列表
    Route::get('product/:id', 'admin/product/index'); # 商品详情
    Route::post('product', 'admin/product/create'); # 新建商品
    Route::put('product/:id', 'admin/product/update'); # 编辑商品
    Route::put('product/:id/server', 'admin/product/updateServer'); # 编辑商品接口
    Route::delete('product/:id', 'admin/product/delete'); # 删除商品
    Route::put('product/:id/:hidden', 'admin/product/hidden'); # 隐藏/显示商品
    Route::put('product/order/:id', 'admin/product/order'); # 商品拖动排序
    Route::get('product/:id/upgrade', 'admin/product/upgrade'); # 获取商品关联的升降级商品
    Route::get('product/group/first', 'admin/productGroup/productGroupFirstList'); # 获取商品一级分组
    Route::get('product/group/second', 'admin/productGroup/productGroupSecondList'); # 获取商品二级分组
    Route::post('product/group', 'admin/productGroup/create'); # 新建商品分组
    Route::put('product/group/order/:id', 'admin/productGroup/order'); # 商品分组拖动排序
    Route::put('product/group/first/order/:id', 'admin/productGroup/orderFirst'); # 一级商品分组拖动排序
    Route::delete('product/group/:id', 'admin/productGroup/delete'); # 删除商品分组
    Route::put('product/group/:id', 'admin/productGroup/update'); # 编辑商品分组
    Route::put('product/group/:id/product', 'admin/productGroup/moveProduct'); # 移动商品至其他商品组
    Route::put('product/group/:id/:hidden', 'admin/productGroup/hidden'); # 隐藏/显示商品分组
    Route::get('product/:id/server/config_option', 'admin/product/moduleServerConfigOption'); # 选择接口获取配置
    Route::get('product/:id/config_option', 'admin/product/moduleAdminConfigOption'); # 商品配置页面
    Route::post('product/:id/config_option', 'admin/product/moduleCalculatePrice'); # 修改配置计算价格
    Route::get('product/:id/all_config_option', 'admin/product/moduleAllConfigOption'); # 获取商品所有配置项
    Route::get('module/:module/product', 'admin/product/moduleProductList'); # 根据模块获取商品
    Route::get('product/search', 'admin/product/productListSearch'); # 商品搜索
    Route::put('product/agentable', 'admin/product/saveAgentableProduct'); # 保存可代理商品
    Route::get('res_module/:module/product', 'admin/product/resModuleProductList'); # 根据上游模块获取商品
    Route::get('module/product', 'admin/product/modulesProductList'); # 根据模块获取商品
    Route::post('product/:id/copy', 'admin/product/copy'); # 复制商品


    # 公共接口
    Route::get('gateway', 'admin/common/gateway'); # 支付接口
    Route::get('sms', 'admin/common/sms'); # 短信接口
    Route::get('email', 'admin/common/email'); # 邮件接口
    Route::get('captcha_list', 'admin/common/captchaList'); # 验证码接口
    Route::get('common', 'admin/common/common'); # 公共配置
    Route::get('country', 'admin/common/countryList'); # 国家列表
    Route::get('auth', 'admin/common/authList'); # 权限列表 
    Route::get('admin/auth', 'admin/common/adminAuthList'); # 当前管理员权限列表 
    Route::post('upload', 'admin/common/upload'); # 上传文件
    Route::get('global_search', 'admin/common/globalSearch'); # 全局搜索
    Route::get('menu', 'admin/common/adminMenu'); # 获取导航
    Route::get('active_plugin', 'admin/common/activePluginList'); # 获取已激活插件
    Route::get('clientarea_auth', 'admin/common/clientareaAuthList'); # 权限列表 
    Route::get('product/all', 'admin/common/productList'); # 商品列表

    # 接口管理
    Route::get('server/group', 'admin/serverGroup/serverGroupList'); # 接口分组列表
    Route::post('server/group', 'admin/serverGroup/create'); # 新建接口分组
    Route::put('server/group/:id', 'admin/serverGroup/update'); # 修改接口分组
    Route::delete('server/group/:id', 'admin/serverGroup/delete'); # 删除接口分组
    Route::get('server', 'admin/server/serverList'); # 接口列表
    Route::post('server', 'admin/server/create'); # 新建接口
    Route::put('server/:id', 'admin/server/update'); # 编辑接口
    Route::delete('server/:id', 'admin/server/delete'); # 删除接口
    Route::get('server/:id/status', 'admin/server/status'); # 获取接口连接状态
    Route::get('module', 'admin/module/moduleList'); # 模块列表
    Route::rule('module/:module/:controller/:method', 'admin/module/customFunction', 'GET|POST'); # 后台模块自定义方法

    #系统升级
    Route::get('system/version', 'admin/upgradeSystem/systemVersion'); # 获取系统版本
    Route::get('system/upgrade_content', 'admin/upgradeSystem/upgradeContent'); # 获取更新内容
    Route::get('system/upgrade_download', 'admin/upgradeSystem/upgradeDownload'); # 更新下载
    Route::get('system/upgrade_download_progress', 'admin/upgradeSystem/upgradeDownloadProgress'); # 获取更新下载进度
    Route::get('system/auth', 'admin/upgradeSystem/getAuth'); # 获取授权信息
    Route::put('system/license', 'admin/upgradeSystem/updateLicense'); # 更换授权码

    # 导航管理
    Route::get('menu/admin', 'admin/menu/getAdminMenu'); # 接口分组列表
    Route::get('menu/home', 'admin/menu/getHomeMenu'); # 新建接口分组
    Route::put('menu/admin', 'admin/menu/saveAdminMenu'); # 修改接口分组
    Route::put('menu/home', 'admin/menu/saveHomeMenu'); # 删除接口分组

    # 应用商店
    Route::post('app_market/set_token', 'admin/appMarket/setToken'); # 设置token
    Route::get('app_market/app/version', 'admin/appMarket/getNewVersion'); # 安装应用

    # 供应商
    Route::get('supplier', 'admin/supplier/list');
    Route::get('supplier/:id', 'admin/supplier/index');
    Route::post('supplier', 'admin/supplier/create');
    Route::put('supplier/:id', 'admin/supplier/update');
    Route::delete('supplier/:id', 'admin/supplier/delete');
    Route::get('supplier/:id/status', 'admin/supplier/status');
    Route::get('supplier/:id/product', 'admin/supplier/product');

    # 上游产品
    Route::get('upstream/host', 'admin/upstreamHost/list');
    Route::get('upstream/host/:id', 'admin/upstreamHost/index');

    # 上游订单
    Route::get('upstream/order', 'admin/upstreamOrder/list');
    Route::get('upstream/sell_info', 'admin/upstreamOrder/sellInfo');

    # 上游商品
    Route::get('upstream/product', 'admin/upstreamProduct/list');
    Route::get('upstream/product/:id', 'admin/upstreamProduct/index');
    Route::post('upstream/product', 'admin/upstreamProduct/create');
    Route::put('upstream/product/:id', 'admin/upstreamProduct/update');
    Route::get('upstream/recommend/product', 'admin/upstreamProduct/recommendProductList');
    Route::post('upstream/recommend/product', 'admin/upstreamProduct/agentRecommendProduct');

    # 意见反馈
    Route::get('feedback', 'admin/feedback/feedbackList');
    Route::get('feedback/type', 'admin/feedback/feedbackTypeList');
    Route::post('feedback/type', 'admin/feedback/createFeedbackType');
    Route::put('feedback/type/:id', 'admin/feedback/updateFeedbackType');
    Route::delete('feedback/type/:id', 'admin/feedback/deleteFeedbackType');

    # 方案咨询
    Route::get('consult', 'admin/consult/list');

    # 友情链接
    Route::get('friendly_link', 'admin/friendlyLink/list');
    Route::post('friendly_link', 'admin/friendlyLink/create');
    Route::put('friendly_link/:id', 'admin/friendlyLink/update');
    Route::delete('friendly_link/:id', 'admin/friendlyLink/delete');

    # 荣誉资质
    Route::get('honor', 'admin/honor/list');
    Route::post('honor', 'admin/honor/create');
    Route::put('honor/:id', 'admin/honor/update');
    Route::delete('honor/:id', 'admin/honor/delete');

    # 合作伙伴
    Route::get('partner', 'admin/partner/list');
    Route::post('partner', 'admin/partner/create');
    Route::put('partner/:id', 'admin/partner/update');
    Route::delete('partner/:id', 'admin/partner/delete');

    # 用户信息记录
    Route::get('client/:id/record', 'admin/clientRecord/list');
    Route::post('client/:id/record', 'admin/clientRecord/create');
    Route::put('client/record/:id', 'admin/clientRecord/update');
    Route::delete('client/record/:id', 'admin/clientRecord/delete');

    # 挂件
    Route::get('widget', 'admin/widget/index');
    Route::put('widget/order', 'admin/widget/widgetSaveOrder');
    Route::put('widget/status', 'admin/widget/toggleWidget');
    Route::get('widget/output', 'admin/widget/output');
    Route::get('widget/data', 'admin/widget/getData');

})  
    ->allowCrossDomain([
            'Access-Control-Allow-Origin'        => $origin,
            'Access-Control-Allow-Credentials'   => 'true',
            'Access-Control-Max-Age'             => 600,
        ]
    )
    ->middleware(\app\http\middleware\CheckAdmin::class)
    ->middleware(\app\http\middleware\ParamFilter::class);

Route::get(DIR_ADMIN,'admin/view/index');//模板首页
Route::get(DIR_ADMIN.'/[:view_html]','admin/view/index')->ext('htm'); //后台模板
Route::get(DIR_ADMIN.'/plugin/[:name]/[:view_html]','admin/view/plugin')->ext('htm'); //后台插件模板