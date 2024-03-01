// 插件列表
function getAddon(params) {
  return Axios.get(`/plugin/addon`, { params })
}
// 启用禁用插件
function changeAddonStatus(params) {
  return Axios.put(`/plugin/addon/${params.name}/${params.status}`)
}
// 安装/卸载插件
function deleteMoudle(type, name) {
  if (type === 'install') {
    return Axios.post(`/plugin/addon/${name}`)
  } else {
    return Axios.delete(`/plugin/addon/${name}`)
  }
}
// 获取导航
function getMenus() {
  return Axios.get('/menu')
}
// 获取已购买应用最新版本
function getActiveVersion() {
  return Axios.get('/app_market/app/version')
}
// 插件升级
function upgradePlugin(params) {
  return Axios.post(`/plugin/${params.module}/${params.name}/upgrade`)
}

// 获取系统版本
function getSysyemVersion () {
  return Axios.get('/system/version')
}

// 同步插件
function syncPlugins () {
  return Axios.get('/plugin/sync')
}

// 下载插件
function downloadPlugin (id) {
  return Axios.get(`/plugin/${id}/download`)
}

/* hook */
function getHookPlugin () {
  return Axios.get('/plugin/hook')
}
function changeHookOrder (params) {
  return Axios.put('/plugin/hook/order', params)
}
