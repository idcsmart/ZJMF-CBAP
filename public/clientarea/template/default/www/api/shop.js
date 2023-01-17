// 获取公共配置
function getCommon() {
    return Axios.get("/common");
}
// 获取国家列表
function getCountry(params) {
    return Axios.get(`/country`, params);
}

// 获取图形验证码
function getCaptcha() {
    return Axios.get("/captcha");
}

// 验证图形验证码
function checkCaptcha(params) {
    return Axios.post("/captcha", params);
}
// 注册
function regist(params) {
    return Axios.post("/register", params);
}

// 登录
function logIn(params) {
    return Axios.post("/login", params);
}

// 忘记密码
function forgetPass(params) {
    return Axios.post("/account/password_reset", params);
}

// 发送短信验证码
function phoneCode(params) {
    return Axios.post("/phone/code", params);
}

// 获取邮箱验证码
function emailCode(params) {
    return Axios.post("/email/code", params);
}

// 首页
function marketIndex(params) {
    return Axios.get(`/app_market/market/index`, { params })
}
// 应用详情
function appDetails(params) {
    return Axios.get(`/app_market/market/app/${params.id}`, { params })
}
// 获取开发者详情
function developer(params) {
    return Axios.get(`/app_market/market/developer/${params.id}`)
}
// 应用的评论
function appEvaluation(params) {
    return Axios.get(`/app_market/app/${params.id}/evaluation`)
}
// 应用列表
function appList(params) {
    return Axios.get(`/app_market/market/app`, { params })
}

// 投诉列表
function complaint(params) {
    return Axios.get(`/app_market/market/complaint`, { params })
}

// 订单列表
function order(params) {
    return Axios.get(`/app_market/market/order`, { params })
}

// 我的应用 列表
function clientAppList(params) {
    return Axios.get(`/app_market/market/my_app`, { params })
}

// 我的服务 列表
function clientServiceLiist(params) {
    return Axios.get(`/app_market/market/my_service`, { params })
}

// 用户授权列表
function authorize(params) {
    return Axios.get(`/app_market/market/authorize`, { params })
}

// 投诉订单
function complaintOrder(params) {
    return Axios.post(`/app_market/market/order/${params.id}/complaint`, params)
}
// 投诉详情
function complaintOrderDetail(params) {
    return Axios.get(`/app_market/market/complaint/${params.id}`, { params })
}
// 取消投诉
function delComplaint(params) {
    return Axios.delete(`/app_market/market/complaint/${params.id}`, params)
}
// 回复投诉
function replyComplaint(params) {
    return Axios.post(`/app_market/market/complaint/${params.id}/reply`, params)
}
// 删除订单
function delOrder(params) {
    return Axios.delete(`order/${params.id}`)
}
// 确认收货
function finishOrder(params) {
    return Axios.put(`/app_market/market/order/${params.id}/finish`)
}
// 服务完成
function finishService(params) {
    return Axios.put(`/app_market/market/order/${params.id}/service_finish`)
}
// 修改退款金额
function editRefund(params) {
    return Axios.put(`/app_market/market/order/${params.id}/refund`, params)
}
// 申请退款
function doRefund(params) {
    return Axios.post(`/app_market/market/order/${params.id}/refund`, params)
}

// 支付方式
function gatewayList() {
    return Axios.get(`/gateway`);
}
// 账户详情
function accountDetail() {
    return Axios.get(`/account`);
}
// 使用/取消余额
function creditPay(params) {
    return Axios.post(`/credit`, params);
}
// 支付
function pay(params) {
    return Axios.post(`/pay`, params);
}
// 订单详情
function orderDetails(id) {
    return Axios.get(`/order/${id}`);
}
// 支付状态
function getPayStatus(id) {
    return Axios.get(`/pay/${id}/status`);
}
// 账户详情
function account() {
    return Axios.get(`/account`);
}
// 订单退款详情
function refundDetails(params) {
    return Axios.get(`/app_market/market/order/${params.id}/refund`)
}
// 退款回复
function replyRefund(params) {
    return Axios.post(`/app_market/market/order/${params.id}/refund/reply`, params)
}
// 评价应用
function evaluation(params) {
    return Axios.post(`/app_market/market/order/${params.id}/evaluation`, params)
}
// 开发者详情
function developerDetail(id) {
    return Axios.get(`/app_market/market/developer/${id}`)
}

//购买应用
function buyApp(params) {
    return Axios.post(`/app_market/market/app/${params.id}/settle`, params)
}
// 下载应用安装包
function download(params) {
    return Axios.get(`/app_market/market/app/${params.id}/download`)
}

// 商品投诉
function productComplaint(params) {
    return Axios.post(`/app_market/market/product/${params.id}/complaint`, params)
}
// 商品评论列表
function evaluationList(params) {
    return Axios.get(`/app_market/market/app/${params.id}/evaluation`, { params })
}

// 续费开始
// 续费页面
function renewMsg(params) {
    return Axios.get(`/host/${params.id}/renew`)
}
// 续费
function doRenew(params) {
    return Axios.post(`/host/${params.id}/renew`, params)
}
// 续费结束

// 授权地址后台路径检查
function checkAddress(params) {
    return Axios.post(`/app_market/market/install/check`, params)
}