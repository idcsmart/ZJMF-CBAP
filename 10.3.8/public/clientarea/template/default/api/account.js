// 获取国家列表
function getCountry(params) {
  return Axios.get(`/country`, params);
}

// 账户详情
function account() {
  return Axios.get(`/account`);
}
// 获取国家列表
function country(params) {
  return Axios.get(`/country`, { params });
}

// 编辑账户
function updateAccount(params) {
  return Axios.put(`/account`, params);
}
// 修改密码
function updatePassword(params) {
  return Axios.put(`/account/password`, params);
}

// 忘记密码
function forgetPass(params) {
  return Axios.post("/account/password_reset", params);
}

// 发送手机验证码
function phoneCode(params) {
  return Axios.post(`/phone/code`, params);
}
// 发送邮箱验证码
function emailCode(params) {
  return Axios.post(`/email/code`, params);
}

// 验证原手机
function verifiedPhone(params) {
  return Axios.put(`/account/phone/old`, params);
}
// 修改手机
function updatePhone(params) {
  return Axios.put(`/account/phone`, params);
}

// 验证原邮箱
function verifiedEmail(params) {
  return Axios.put(`/account/email/old`, params);
}
// 修改邮箱
function updateEmail(params) {
  return Axios.put(`/account/email`, params);
}

// 阿里云修改邮箱
function updateAliEmail(params) {
  return Axios.put(`/aliyun_agency/account/email`, params);
}

// 操作日志
function getLog(params) {
  return Axios.get(`/log`, { params });
}

// 取消关联
function cancelOauth(name) {
  return Axios.post(`/oauth/unbind/${name}`);
}

