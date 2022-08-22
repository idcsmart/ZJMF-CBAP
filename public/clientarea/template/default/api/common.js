
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
function globalSearch(params){
  return Axios.get('/global_search',{params})
}