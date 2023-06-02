// 用户管理-用户列表
function getIndex() {
    return Axios.get(`/index`)
}
// 本年销售详情
function saleData() {
    return Axios.get(`/index/this_year_sale`)
}

// 本年大客户
function clientData() {
    return Axios.get(`/index/this_year_client`)
}


//在线管理员列表
function online_admin(params) {
    return Axios.get(`/index/online_admin`, { params })
}
//最近访问用户列表
function visit_client(params) {
    return Axios.get(`/index/visit_client`, { params })
}