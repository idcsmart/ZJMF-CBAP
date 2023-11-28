// 统计
function staticstics() {
    return Axios.get(`/room_box/order/staticstics`)
}

// 订单列表
function orderList(params) {
    return Axios.get(`/room_box/order`, { params })
}

// 用户列表
function clientList(params) {
    return Axios.get(`/client`, { params })
}
// 生产完成
function finish(params) {
    return Axios.put(`/room_box/order/${params.id}/finish`)
}
// 已付尾款
function failPaid(params) {
    return Axios.put(`/room_box/order/${params.id}/fail_paid`)
}
// 已支付
function paid(params) {
    return Axios.put(`/room_box/order/${params.id}/paid`)
}

// 修改周期
function editCycle(params) {
    return Axios.put(`/room_box/order/${params.id}/cycle`, params)
}
// 开始生产
function beginProduction(params) {
    return Axios.put(`/room_box/order/${params.id}/production`, params)
}
// 交付商品
function delivery(params) {
    return Axios.put(`/room_box/order/${params.id}/delivery`, params)
}
// 订单详情
function orderDetails(params) {
    return Axios.get(`/room_box/order/${params.id}`)
}
// 获取基础配置
function getConfig() {
    return Axios.get(`/room_box/config`)
}
// 保存基础配置设置
function saveConfig(params) {
    return Axios.post(`/room_box/config`, params)
}