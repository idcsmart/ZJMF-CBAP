// 通用商品
function getCommonDetail (id) {
  return Axios.get(`/idcsmart_common/product/${id}/configoption`)
}
// 修改配置计算价格
function calcPrice (params) {
  return Axios.post(`/product/${params.id}/config_option`, params)
}
// 结算商品
function settle (params) {
  return Axios.post(`/product/settle`, params)
}
// 获取国家
function getCountry () {
  return Axios.get(`/country`)
}
// 账户详情
function account () {
  return Axios.get(`/account`)
}
// 支付方式
function gatewayList () {
  return Axios.get(`/gateway`)
}
// 支付
function pay (params) {
  return Axios.post(`/pay`, params)
}
// 支付状态
function getPayStatus (id) {
  return Axios.get(`/pay/${id}/status`)
}

// 使用/取消余额
function creditPay (params) {
  return Axios.post(`/credit`, params);
}
// 订单详情
function orderDetails (id) {
  return Axios.get(`/order/${id}`);
}

// 产品列表
function getCommonList (params) {
  return Axios.get(`/idcsmart_common/host`, { params });
}
// 产品列表
function getCommonListDetail (id) {
  return Axios.get(`/idcsmart_common/host/${id}/configoption`);
}


/* 退款 */
function getRefundInfo (id) {
  return Axios.get(`/refund/host/${id}/refund`);
}
function getRefund (host_id) {
  return Axios.get(`/refund?host_id=${host_id}`);
}
function submitRefund (params) {
  return Axios.post(`/refund`, params);
}
function cancelRefund (params) {
  return Axios.put(`/refund/${params.id}/cancel`, params);
}