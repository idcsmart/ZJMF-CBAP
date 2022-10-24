// api 秘钥列表
function apiList(params) {
    return Axios.get(`/api`, { params })
}
// 创建api秘钥
function createApi(params) {
    return Axios.post(`/api`, params)
}
// APi白名单设置
function whiteApi(params) {
    return Axios.put(`/api/${params.id}/white_list`, params)
}
// 删除API秘钥
function delApi(params) {
    return Axios.delete(`/api/${params.id}`, params)
}

// ssh 密钥列表
function sshList(params) {
    return Axios.get(`/ssh_key`, { params })
}
// 创建SSH密钥
function createSsh(params) {
    return Axios.post(`/ssh_key`, params)
}
// 编辑SSH密钥
function editSsh(params) {
    return Axios.put(`/ssh_key/${params.id}`, params)
}
// 删除SSH秘钥
function delSsh(params) {
    return Axios.delete(`/ssh_key/${params.id}`, params)
}

// 日志列表
function logList(params) {
    return Axios.get(`/log`, { params })
}