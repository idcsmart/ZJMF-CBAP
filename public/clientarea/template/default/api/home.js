// 会员中心首页
function indexData() {
    return Axios.get(`/index`)
}

//会员中心首页产品列表
function indexHost(params) {
    return Axios.get(`/index/host`, { params })
}
// 获取实名认证信息
function certificationInfo() {
    return Axios.get(`/certification/info`)
}
//工单列表
function ticket_list(params) {
    return Axios.get(`/ticket`, { params })
}
//会员中心首页新闻列表
function newsList(params) {
    return Axios.get(`/news/index`, { params })
}
// 推广者统计信息
function promoter_statistic() {
    return Axios.get(`/referral/promoter/statistic`)
}
