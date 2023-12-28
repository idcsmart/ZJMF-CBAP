// 通用商品
function getCommonDetail (id) {
  return Axios.get(`/product/${id}/remf_finance/order_page`)
}
// 商品详情
function getReDetails (id) {
  return Axios.get(`/product/${id}`)
}
function productInfo(id) {
  return Axios.get(`/product/${id}`);
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
  return Axios.get(`/rewhmcs_cloud/host`, { params });
}
// 产品列表
function getCommonListDetail (id) {
  return Axios.get(`/rewhmcs_cloud/host/${id}/configoption`);
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

// 修改配置重新计算周期价格
function calculate (id) {
  return Axios.get(`/product/${id}/remf_finance/duration`);
}
// 获取商品折扣金额
function clientLevelAmount(params) {
  return Axios.get(`/client_level/product/${params.id}/amount`, { params });
}
// 加入购物车
function addToCart(params) {
  return Axios.post(`/cart`, params);
}
// 修改购物车
function updateCart(params) {
  return Axios.put(`/cart/${params.position}`, params);
}
// 获取购物车
function getCart () {
  return Axios.get('/cart')
}
// 获取级联的数据
function getCascader (params) {
  return Axios.get(`/product/${params.id}/remf_finance/link`, {params})
}