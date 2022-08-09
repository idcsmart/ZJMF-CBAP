// 插件列表
function getAddon (params) {
  return Axios.get(`/plugin/addon`, { params })
}
// 启用禁用插件
function changeAddonStatus (params) {
  return Axios.put(`/plugin/addon/${params.name}/${params.status}`)
}
// 安装/卸载插件
function deleteMoudle (type, name) {
  if (type === 'install') {
    return Axios.post(`/plugin/addon/${name}`)
  } else {
    return Axios.delete(`/plugin/addon/${name}`)
  }
}