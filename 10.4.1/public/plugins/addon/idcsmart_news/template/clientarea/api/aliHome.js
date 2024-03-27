// 阿里云首页

// 发送邀请
function invite(params) {
    return Axios.post(`/aliyun_agency/invite`, params);
}

// 获取邀请状态
function getInviteState(params) {
    return Axios.get(`/aliyun_agency/invite`, params);
}

// 阿里充值
function recharge(params) {
    return Axios.post(`/aliyun_agency/recharge`, params);
}
// 支付
function pay(params) {
    return Axios.post(`/pay`, params)
}

// 充值记录列表
function rechargelist(params) {
    return Axios.get(`/aliyun_agency/recharge`, {params});
}

// 支付方式
function gatewayList() {
    return Axios.get(`/gateway`)
}

// 支付状态
function getPayStatus(id) {
    return Axios.get(`/pay/${id}/status`)
}
// 公共配置
function common() {
    return Axios.get(`/common`)
}

// 账户详情
function account() {
    return Axios.get(`/account`)
}
