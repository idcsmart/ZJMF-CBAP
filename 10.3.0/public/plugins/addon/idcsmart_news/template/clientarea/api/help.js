// 帮助中心首页
function helpIndex(params) {
    return Axios.get(`/help/index`, { params })
}
// 帮助文档列表
function helpList(params) {
    return Axios.get(`/help`, { params })
}
// 帮助文档详情
function helpDetails(params) {
    return Axios.get(`/help/${params.id}`, { params })
}
// 文件下载
function downloadFile(params) {
    return Axios.post(`ticket/download`, params)
}
