// 通用商品
function getCommonDetail(id) {
  return Axios.get(`/idcsmart_common/product/${id}/configoption`);
}
// 修改配置计算价格
function calcPrice(params) {
  return Axios.post(`/product/${params.id}/config_option`, params);
}
// 结算商品
function settle(params) {
  return Axios.post(`/product/settle`, params);
}
// 获取国家
function getCountry() {
  return Axios.get(`/country`);
}
// 账户详情
function account() {
  return Axios.get(`/account`);
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

// 使用/取消余额
function creditPay(params) {
  return Axios.post(`/credit`, params);
}
// 订单详情
function orderDetails(id) {
  return Axios.get(`/order/${id}`);
}

// 产品列表
function getCommonList(params) {
  return Axios.get(`/idcsmart_common/host`, { params });
}
// 产品列表
function getCommonListDetail(id) {
  return Axios.get(`/idcsmart_common/host/${id}/configoption`);
}
// 通用详情
function getCommonDetail(id) {
  return Axios.get(`/host/${id}`);
}
// 商品详情
function productInfo (id) {
  return Axios.get(`/product/${id}`)
}
// 修改产品备注
function changeNotes(params) {
  return Axios.put(`/host/${params.id}/notes`, params);
}

/* 退款 */
function getRefundInfo(id) {
  return Axios.get(`/refund/host/${id}/refund`);
}
function getRefund(host_id) {
  return Axios.get(`/refund?host_id=${host_id}`);
}
function submitRefund(params) {
  return Axios.post(`/refund`, params);
}
function cancelRefund(params) {
  return Axios.put(`/refund/${params.id}/cancel`, params);
}
/* 产品内页获取优惠码信息 */
function getPromoCode(id) {
  return Axios.get(`/promo_code/host/${id}/promo_code`);
}
// 加入购物车
function addToCart(params) {
  return Axios.post(`/cart`, params);
}
// 修改购物车
function updateCart(params) {
  return Axios.put(`/cart/${params.position}`, params);
}
// 获取商品折扣金额
function clientLevelAmount(params) {
  return Axios.get(`/client_level/product/${params.id}/amount`, { params });
}
// 获取自动续费状态
function renewStatus(params) {
  return Axios.get(`/host/${params.id}/renew/auto`, { params });
}
// 自动续费开关
function rennewAuto(params) {
  return Axios.put(`/host/${params.id}/renew/auto`, params);
}

/* 升降级 */

//产品升降级页面
function upgradePage(host_id) {
  return Axios.get(`/idcsmart_common/host/${host_id}/upgrade`);
}
// 购买应用升降级页面
function upAppPage(host_id) {
  return Axios.get(`/zjmfapp/host/${host_id}/upgrade`);
}
// 购买应用配置升降级页面
function upgradeAppPage(host_id) {
  return Axios.get(`/zjmfapp/host/${host_id}/upgrade_config`);
}

//产品配置升降级页面
function upgradeConfigPage(host_id) {
  return Axios.get(`/idcsmart_common/host/${host_id}/upgrade_config`);
}
//产品升降级异步获取升降级价格
function upgradePrice(id, params) {
  return Axios.post(`/idcsmart_common/host/${id}/sync_upgrade_price`, params);
}
// 修改配置重新计算周期价格
function calculate(params) {
  return Axios.post(
    `/idcsmart_common/product/${params.id}/configoption/calculate`,
    params
  );
}
//产品配置升降级异步获取升降级价格
function syncUpgradePrice(id, params) {
  return Axios.post(
    `/idcsmart_common/host/${id}/sync_upgrade_config_price`,
    params
  );
}
// 产品升降级
function upgradeHost(id, params) {
  return Axios.post(`/idcsmart_common/host/${id}/upgrade`, params);
}
//产品配置升降级
function upgradeConfigHost(host_id, params) {
  return Axios.post(`/idcsmart_common/host/${host_id}/upgrade_config`, params);
}

// 产品合同是否逾期
function timeoutStatus(id) {
  return Axios.get(`/e_contract/host/${id}/timeout`);
}

//  前台产品内页图表页面
function chartList(params) {
  return Axios.post(
    `/idcsmart_common/host/${params.id}/configoption/chart`,
    params
  );
}

// 执行子模块方法
function provision(params) {
  return Axios.post(
    `/idcsmart_common/host/${params.id}/provision/${params.func}`,
    params
  );
}
// 前台产品内页自定义页面输出
function configArea(params) {
  return Axios.get(`/idcsmart_common/host/${params.id}/configoption/area`,{params});
}

function getLog(params) {
  return Axios.get(`/idcsmart_common/${params.id}/log`, { params });
}
