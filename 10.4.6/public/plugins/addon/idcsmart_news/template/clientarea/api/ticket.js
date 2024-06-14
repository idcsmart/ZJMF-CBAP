// 工单统计
function ticketStatistic(params) {
    return Axios.get(`/ticket/statistic`, { params })
}
// 工单列表
function ticketList(params) {
    return Axios.get(`/ticket`, { params })
}

// 工单类型
function ticketType(params) {
    return Axios.get(`/ticket/type`, { params })
}
// 获取产品列表
function hostAll(params) {
    return Axios.get(`/host`, { params })
}

// 创建工单
function createTicket(params) {
    return Axios.post(`/ticket`, params)
}

// 关闭工单
function closeTicket(params) {
    return Axios.put(`/ticket/${params.id}/close`, params)
}
// 催单
function urgeTicket(params) {
    return Axios.put(`/ticket/${params.id}/urge`, params)
}
// 查看工单
function ticketDetail(params) {
    return Axios.get(`/ticket/${params.id}`, { params })
}

// 回复工单
function replyTicket(params) {
    return Axios.post(`/ticket/${params.id}/reply`, params)
}
// 文件下载
function downloadFile(params) {
    return Axios.post(`ticket/download`, params)
}
