// 实名认证列表
function getRealName (params) {
  return Axios.get('/certification', { params })
}
// 获取实名认证
function getRealNameDetail (id) {
  return Axios.get(`/certification/${id}`)
}
// 通过/驳回
function changeRealStatus (type,id) {
  if (type === 'pass') {
    return Axios.put(`/certification/${id}/approve`)
  }else if (type === 'reject'){
    return Axios.put(`/certification/${id}/reject`)
  }
}

// 获取实名认证配置
function getRealSetting () {
  return Axios.get(`/configuration/certification`)
}
function saveRealSetting (params) {
  return Axios.put(`/configuration/certification`, params)
}

// 获取插件接口列表（ module: gateway表示支付接口列表,addon插件列表,sms短信接口列表,mail邮件接口列表）
function getMoudle (params) {
  return Axios.get(`/plugin/${params.module}`, { params })
}
// 获取配置
function getMoudleConfig (params) {
  return Axios.get(`/plugin/${params.module}/${params.name}`, params)
}
// 保存配置
function saveMoudleConfig (params) {
  return Axios.put(`/plugin/${params.module}/${params.name}`, params)
}
// 禁用/启用支付接口
function changeMoudle (params) {
  return Axios.put(`/plugin/${params.module}/${params.name}/${params.status}`)
}
// 卸载支付接口
function deleteMoudle (type, params) {
  if (type === 'install') {
    return Axios.post(`/plugin/${params.module}/${params.name}`)
  } else {
    return Axios.delete(`/plugin/${params.module}/${params.name}`)
  }
}