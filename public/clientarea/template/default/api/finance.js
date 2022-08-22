// 	订单列表
function orderList(params) {
  return Axios.get("/order", { params });
}

// 交易记录列表
function transactionList(params) {
  return Axios.get(`/transaction`, { params });
}

// 订单详情
function orderDetails(id) {
  return Axios.get(`/order/${id}`);
}

// 余额记录列表
function creditList(params) {
  return Axios.get(`/credit`, { params })
}
// 公共配置
function common() {
  return Axios.get(`/common`)
}

// 账户详情
function account() {
  return Axios.get(`/account`)
}

// 提现申请
function withdraw(params) {
  return Axios.post(`/withdraw`, params)
}

// 提现规则详情
function withdrawRule(params){
  return Axios.get(`/withdraw/rule`, {params})
}

// 充值
function recharge(params) {
  return Axios.post(`/recharge`, params)
}

// 支付方式
function gatewayList() {
  return Axios.get(`/gateway`)
}

// 支付
function pay(params) {
  return Axios.post(`/pay`, params)
}

// 支付状态
function getPayStatus(id){
  return Axios.get(`/pay/${id}/status`)
}

// 获取待审核金额
function unAmount(){
  return Axios.get(`/refund/pending/amount`)
}

// 使用/取消余额
function creditPay(params) {
  return Axios.post(`/credit`, params);
}

// 使用余额支付
function onlinePay(params) {
  return Axios.post(`/pay`, params);
}