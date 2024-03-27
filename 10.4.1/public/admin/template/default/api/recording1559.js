// 修改设置
function setting(params) {
    return Axios.put('/finance_dcim_search/setting', params)
}
// 系统设置
function settingList(params) {
    return Axios.get('/finance_dcim_search/setting', { params })
}
// 资料查询
function dcimSearch(params) {
    return Axios.get('/finance_dcim_search', { params })
}
// 查询记录列表
function seacrhLog(params) {
    return Axios.get('/finance_dcim_search/log', { params })
}