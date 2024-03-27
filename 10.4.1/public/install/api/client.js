/* 用户管理 + 业务管理 API */

// 用户管理-用户列表
function getClientList (params) {
  return Axios.get(`/client`, { params })
}
// 用户管理-添加用户
function addClient (params) {
  return Axios.post(`/client`, params)
}
// 用户管理-切换状态
function changeOpen (id, params) {
  return Axios.put(`/client/${id}/status`, params)
}
// 用户管理-修改资料
function updateClient (id, params) {
  return Axios.put(`/client/${id}`, params)
}
// 用户管理-删除用户
function deleteClient (id) {
  return Axios.delete(`/client/${id}`,)
}
// 用户管理-用户详情
function getClientDetail (id) {
  return Axios.get(`/client/${id}`)
}
// 以用户登录
function loginByUserId (id) {
  return Axios.post(`/client/${id}/login`)
}
// 获取用户退款
function getRefund (id) {
  return Axios.get(`/refund/client/${id}/amount`)
}
// 用户余额管理-用户余额变更记录列表
function getMoneyDetail (id, params) {
  return Axios.get(`/client/${id}/credit`, { params })
}

// 用户余额管理-更改用户余额
function updateClientDetail (id, params) {
  return Axios.put(`/client/${id}/credit`, params)
}

// 用户信息-产品列表
function getClientPro (id, params) {
  return Axios.get(`/host?client_id=${id}`, { params })
}
// 用户信息-订单管理
function getClientOrder (id) {
  return Axios.get(`/order?client_id=${id}`)
}
// 用户信息-交易流水
function getClientOrder (params) {
  return Axios.get(`/transaction`, { params })
}
// 产品管理-删除流水
function deleteFlow (id) {
  return Axios.delete(`/transaction/${id}`)
}
// 产品管理-新增流水
function addFlow (params) {
  return Axios.post(`/transaction`, params)
}

// 用户信息-日志
function getLog (id, params) {
  return Axios.get(`/log/system?client_id=${id}`, { params })
}

// 产品管理-删除产品
function deletePro (id) {
  return Axios.delete(`/host/${id}`)
}

/* 业务管理相关API */

// 订单管理-订单列表
function getOrder (params) {
  return Axios.get('/order', { params })
}
// 订单管理-新建订单
function createOrder (params) {
  return Axios.post('/order', params)
}

// 订单管理-订单详情
function getOrderDetail (id) {
  return Axios.get(`/order/${id}`)
}

// 订单管理-调整订单金额
function updateOrder (params) {
  return Axios.put(`/order/${params.id}/amount`, params)
}

// 订单管理-删除订单
function delOrderDetail (params) {
  return Axios.delete(`/order/${params.id}`, { params })
}
// 订单管理-标记支付
function signPayOrder (params) {
  return Axios.put(`/order/${params.id}/status/paid`, params)
}

// 获取商品一级分组
function getFirstGroup () {
  return Axios.get(`/product/group/first`)
}
// 获取商品一级分组
function getSecondGroup () {
  return Axios.get(`/product/group/second`)
}

// 获取商品列表
function getProList (params) {
  return Axios.get(`/product`, { params })
}
// 获取产品列表
function getShopList (params) {
  return Axios.get(`/host`, { params })
}
// 获取产品相关的可升降级的商品
function getRelationList (id) {
  return Axios.get(`/product/${id}/upgrade`)
}

// 获取商品配置项参数
function getProConfig (params) {
  return Axios.get(`product/${params.id}/config_option`, { params })
}
// 根据商品配置请求价格
function getProPrice (params) {
  return Axios.post(`/product/${params.id}/config_option`, params)
}

// 获取产品详情
function getProductDetail (id) {
  return Axios.get(`/host/${id}`)
}
// 修改产品
function updateProduct (id, params) {
  return Axios.put(`/host/${id}`, params)
}
// 接口
function getInterface (params) {
  return Axios.get('/server', { params })
}
// 获取升降级订单金额
function getUpgradeAmount (params) {
  return Axios.post('/order/upgrade/amount', params)
}
// 产品模块
function getproModule (id) {
  return Axios.get(`/host/${id}/module`)
}
// 续费页面
function getSingleRenew (id) {
  return Axios.get(`/host/${id}/renew`, { id })
}
// 续费
function postSingleRenew (params) {
  return Axios.post(`/host/${params.id}/renew`, params)
}

// 批量续费页面
function getRenewBatch (params) {
  return Axios.get(`/host/renew/batch?ids=[${params.ids}]&client_id=${params.client_id}`)
}
// 批量续费
function postRenewBatch (params) {
  return Axios.post(`/host/renew/batch`, params)
}
// 系统设置
function getSystemOpt () {
  return Axios.get('/configuration/system')
}

// 充值
function recharge(params){
  return Axios.post(`/client/${params.client_id}/recharge`,params)
}