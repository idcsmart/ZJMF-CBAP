
/* 通用接口API */

// 获取国家列表
function getCountry(params) {
  return Axios.get(`/country`, params)
}
// 获取支付接口
function getPayList() {
  return Axios.get('/gateway')
}
// 获取公共配置
function getCommon() {
  return Axios.get('/common')
}
// 获取登录信息
function getLoginInfo() {
  return Axios.get('/login')
}
// 获取图形验证码
function getCaptcha() {
  return Axios.get('/captcha')
}

// 验证图形验证码
function checkCaptcha(params) {
  return Axios.post('/captcha', params)
}

// 注册
function regist(params) {
  return Axios.post('/register', params)
}

// 登录
function logIn(params) {
  return Axios.post('/login', params)
}

// 忘记密码
function forgetPass(params) {
  return Axios.post('/account/password_reset', params)
}

// 退出登录
function logout() {
  return Axios.post('/logout')
}

// 获取权限
function getAuthRole() {
  return Axios.get('/auth')
}
// 发送短信验证码
function phoneCode(params) {
  return Axios.post('/phone/code', params)
}

// 获取邮箱验证码
function emailCode(params) {
  return Axios.post('/email/code', params)
}

//  全局搜索
function globalSearch(params) {
  return Axios.get('/global_search', { params })
}

// 获取前台导航
function getMenu() {
  return Axios.get('/menu')
}

/* 停用相关 */
// 获取停用页面
function refundPage(params) {
  return Axios.get(`/refund`, { params })
}
// 申请停用
function refund(params) {
  return Axios.post(`/refund`, params)
}
// 取消停用
function cancel(params) {
  return Axios.put(`/refund/${params.id}/cancel`, params)
}
// 获取产品停用信息
function refundMsg(params) {
  return Axios.get(`/refund/host/${params.id}/refund`, { params })
}

// 账户详情
function account() {
  return Axios.get(`/account`)
}
// 支付方式
function gatewayList() {
  return Axios.get(`/gateway`)
}
// 支付
function pay(params) {
  return Axios.post(`/pay`, params)
}
// 支付状态
function getPayStatus(id) {
  return Axios.get(`/pay/${id}/status`)
}

// 使用/取消余额
function creditPay(params) {
  return Axios.post(`/credit`, params);
}
// 订单详情
function orderDetails(id) {
  return Axios.get(`/order/${id}`);
}
/* 续费相关 */
// 续费页面
function renewPage(params) {
  return Axios.get(`/host/${params.id}/renew`, { params })
}
// 续费提交
function renew(params) {
  return Axios.post(`/host/${params.id}/renew`, params)
}
// 商品列表
function productList(params) {
  return Axios.get(`/product`, { params })
}