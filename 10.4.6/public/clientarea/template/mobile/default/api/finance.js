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
  return Axios.get(`/credit`, { params });
}
// 公共配置
function common() {
  return Axios.get(`/common`);
}

// 账户详情
function account() {
  return Axios.get(`/account`);
}

// 提现申请
function withdraw(params) {
  return Axios.post(`/withdraw`, params);
}

// 提现规则详情
function withdrawRule(params) {
  return Axios.get(`/withdraw/rule/credit`, { params });
}

// 充值
function recharge(params) {
  return Axios.post(`/recharge`, params);
}

// 支付方式
function gatewayList() {
  return Axios.get(`/gateway`);
}

// 支付
function pay(params) {
  return Axios.post(`/pay`, params);
}

// 支付状态
function getPayStatus(id) {
  return Axios.get(`/pay/${id}/status`);
}

// 获取待审核金额
function unAmount() {
  return Axios.get(`/refund/pending/amount`);
}

// 使用/取消余额
function creditPay(params) {
  return Axios.post(`/credit`, params);
}
// 删除订单
function delete_order(id) {
  return Axios.delete(`/order/${id}`);
}

// // 使用余额支付
// function onlinePay(params) {
//   return Axios.post(`/pay`, params);
// }

// 代金券
// 可领代金券列表
function voucherAvailable({ params }) {
  return Axios.get(`/voucher`, { params });
}

function voucherMine(params) {
  return Axios.get(`/voucher/mine`, { params });
}

function voucherGet(params) {
  return Axios.post(`/voucher/${params.id}/get`, params);
}

function combineOrder(params) {
  return Axios.post(`/order/combine`, params);
}

/* 申请合同列表 */
function contractOrder(params) {
  return Axios.get(`/e_contract/order`, { params });
}
/* 合同管理列表 */
function contractList(params) {
  return Axios.get(`/e_contract`, { params });
}

/* 保存甲方信息 */
function editPartInfo(params) {
  return Axios.put(`/e_contract/first_part_info`, params);
}
/* 获取甲方信息 */
function getPartInfo() {
  return Axios.get(`/e_contract/first_part_info`);
}

// 下载PDF
function downloadContract(id) {
  return Axios.post(`/e_contract/${id}/download`);
}

// 预览PDF
function viewContract(id) {
  return Axios.get(`/e_contract/${id}/preview`);
}

//取消合同
function cancelContrat(id) {
  return Axios.post(`/e_contract/${id}/cancel`);
}

//邮递纸质合同
function mailContract(params) {
  return Axios.post(`/e_contract/${params.id}/mail`, params);
}

// 获取实名认证信息
function certificationInfo() {
  return Axios.get(`/certification/info`);
}

// 出账列表
function creditLimtList(params) {
  return Axios.get(`/credit_limit/account`, { params });
}

// 授信详情
function creditDetail() {
  return Axios.get(`/credit_limit`);
}

//出账周期订单列表
function creditOrderList(params) {
  return Axios.get(`/credit_limit/account/${params.id}/order`, { params });
}
