// 获取公共配置
function getCommon() {
  return Axios.get('/common')
}
/* 系统设置API */

// 系统设置
function getSystemOpt() {
  return Axios.get('/configuration/system')
}
function updateSystemOpt(params) {
  return Axios.put('/configuration/system', params)
}
// 登录设置
function getLoginOpt() {
  return Axios.get('/configuration/login')
}
function updateLoginOpt(params) {
  return Axios.put('/configuration/login', params)
}
// 安全设置
function getSafeOpt() {
  return Axios.get('/configuration/security')
}
function updateSafeOpt(params) {
  return Axios.put('/configuration/security', params)
}
// 获取验证码列表
function getCaptchaList() {
  return Axios.get('/captcha_list')
}
// 图形验证码预览
function previewCode(params) {
  return Axios.get('/configuration/security/captcha', { params })
}
// 货币设置
function getCurrencyOpt() {
  return Axios.get('/configuration/currency')
}
function updateCurrencyOpt(params) {
  return Axios.put('/configuration/currency', params)
}

// 获取插件接口列表（ module: gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表）
function getMoudle(params) {
  return Axios.get(`/plugin/${params.module}`, { params })
}
// 获取配置
function getMoudleConfig(params) {
  return Axios.get(`/plugin/${params.module}/${params.name}`, params)
}
// 保存配置
function saveMoudleConfig(params) {
  return Axios.put(`/plugin/${params.module}/${params.name}`, params)
}
// 禁用/启用支付接口
function changeMoudle(params) {
  return Axios.put(`/plugin/${params.module}/${params.name}/${params.status}`)
}
// 卸载支付接口
function deleteMoudle(type, params) {
  if (type === 'install') {
    return Axios.post(`/plugin/${params.module}/${params.name}`)
  } else {
    return Axios.delete(`/plugin/${params.module}/${params.name}`)
  }
}

// 管理员
function getAdminList(params) {
  return Axios.get('/admin', { params })
}
function getAdminDetail(id) {
  return Axios.get(`/admin/${id}`)
}

function createAdmin(type, params) {
  if (type === 'create') {
    return Axios.post(`/admin`, params)
  } else {
    return Axios.put(`/admin/${params.id}`, params)
  }
}

function deleteAdmin(id) {
  return Axios.delete(`/admin/${id}`)
}
function changeAdminStatus(params) {
  return Axios.put(`/admin/${params.id}/status`, params)
}

// 管理员分组
function getAdminRole(params) {
  return Axios.get('/admin/role', { params })
}
function getAdminRoleDetail(id) {
  return Axios.get(`/admin/role/${id}`)
}
function createAdminRole(type, params) {
  if (type === 'create') {
    return Axios.post(`/admin/role`, params)
  } else {
    return Axios.put(`/admin/role/${params.id}`, params)
  }
}
function deleteAdminRole(id) {
  return Axios.delete(`/admin/role/${id}`)
}
// 获取权限
function getAllAuthRole() {
  return Axios.get('/auth')
}
// 短信接口
function getSmsInterface() {
  return Axios.get('/sms')
}
// 邮件接口
function getEmailInterface() {
  return Axios.get('/email')
}

/* 短信模板 */
function getSmsTemplate(name) {
  return Axios.get(`/notice/sms/${name}/template`)
}
function getSmsTemplateStatus(name) {
  return Axios.get(`/notice/sms/${name}/template/status`)
}
function getSmsTemplateDetail(params) {
  return Axios.get(`/notice/sms/${params.name}/template/${params.id}`)
}
function createTemplate(type, params) {
  if (type === 'create') {
    return Axios.post(`/notice/sms/${params.name}/template`, params)
  } else {
    return Axios.put(`/notice/sms/${params.name}/template/${params.id}`, params)
  }
}

function deleteSmsTemplate(params) {
  return Axios.delete(`/notice/sms/${params.name}/template/${params.id}`)
}
function testSmsTemplate(params) {
  return Axios.get(`/notice/sms/${params.name}/template/${params.id}/test`, { params })
}
// 批量提交短信模板审核
function batchSubmitById(params) {
  return Axios.post(`/notice/sms/${params.name}/template/audit`, params)
}
// 更新状态
function updateTemplateStatus(name) {
  return Axios.get(`/notice/sms/${name}/template/status`)
}

// 邮件模板
function getEmailTemplate() {
  return Axios.get(`/notice/email/template`)
}
function getEmailTemplateDetail(id) {
  return Axios.get(`/notice/email/template/${id}`)
}
function createEmailTemplate(type, params) {
  if (type === 'create') {
    return Axios.post(`/notice/email/template`, params)
  } else {
    return Axios.put(`/notice/email/template/${params.id}`, params)
  }
}
function deleteEmailTemplate(id) {
  return Axios.delete(`/notice/email/template/${id}`)
}
function testEmailTemplate(params) {
  return Axios.get(`/notice/email/${params.name}/template/${params.id}/test`, { params })
}

// 发送管理
function getSendList() {
  return Axios.get(`/notice/send`)
}
function updateSend(params) {
  return Axios.put(`/notice/send`, params)
}

/* 获取主题设置 */
function getThemeConfig() {
  return Axios.get(`/configuration/theme`)
}
function updateThemeConfig(params) {
  return Axios.put(`configuration/theme`, params)
}


/* 获取系统版本 */
function version() {
  return Axios.get(`/system/version`)
}

// 获取更新内容
function upContent() {
  return Axios.get(`/system/upgrade_content`)
}

// 更新下载
function upDown() {
  return Axios.get(`/system/upgrade_download`)
}

// 获取更新下载进度
function upProgress() {
  return Axios.get(`/system/upgrade_download_progress`)
}
// 获取公告
function newsList(params) {
  return Axios.get(`https://my.idcsmart.com/console/v1/news?addon_idcsmart_news_type_id=11`, { params })
}

// 获取已购买应用最新版本
function getActiveVersion () {
  return Axios.get('/app_market/app/version')
}
// 插件升级
function upgradePlugin (params) {
  return Axios.post(`/plugin/${params.module}/${params.name}/upgrade`)
}