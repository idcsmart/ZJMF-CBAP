/* 用户管理 + 业务管理 API */

// 用户管理-用户列表
function getClientList(params, id) {
  if (id) {
    return Axios.get(`/client?custom_field[IdcsmartClientLevel_level]=${id}`, {
      params,
    });
  } else {
    return Axios.get(`/client`, { params });
  }
}
// 用户管理-添加用户
function addClient(params) {
  return Axios.post(`/client`, params);
}
// 用户管理-切换状态
function changeOpen(id, params) {
  return Axios.put(`/client/${id}/status`, params);
}
// 用户管理-修改资料
function updateClient(id, params) {
  return Axios.put(`/client/${id}`, params);
}
// 用户管理-删除用户
function deleteClient(id) {
  return Axios.delete(`/client/${id}`);
}
// 用户管理-用户详情
function getClientDetail(id) {
  return Axios.get(`/client/${id}`);
}
// 以用户登录
function loginByUserId(id) {
  return Axios.post(`/client/${id}/login`);
}
// 获取用户退款
function getRefund(id) {
  return Axios.get(`/refund/client/${id}/amount`);
}
// 用户余额管理-用户余额变更记录列表
function getMoneyDetail(id, params) {
  return Axios.get(`/client/${id}/credit`, { params });
}

// 用户余额管理-更改用户余额
function updateClientDetail(id, params) {
  return Axios.put(`/client/${id}/credit`, params);
}

// 用户信息-产品列表
function getClientPro(id, params) {
  return Axios.get(`/host?client_id=${id}`, { params });
}
// 用户信息-订单管理
function getClientOrder(id) {
  return Axios.get(`/order?client_id=${id}`);
}
// 用户信息-交易流水
function getClientOrder(params) {
  return Axios.get(`/transaction`, { params });
}
// 产品管理-删除流水
function deleteFlow(id) {
  return Axios.delete(`/transaction/${id}`);
}
// 产品管理-新增/编辑流水
function addAndUpdateFlow(type, params) {
  if (type === "add") {
    return Axios.post(`/transaction`, params);
  } else if (type === "update") {
    return Axios.put(`/transaction/${params.id}`, params);
  }
}
// 用户信息-日志
function getLog(id, params) {
  return Axios.get(`/log/system?client_id=${id}`, { params });
}

// 产品管理-删除产品
function deletePro(id) {
  return Axios.delete(`/host/${id}`);
}

/* 业务管理相关API */

// 订单管理-订单列表
function getOrder(params) {
  return Axios.get("/order", { params });
}
// 订单管理-新建订单
function createOrder(params) {
  return Axios.post("/order", params);
}

// 订单管理-订单详情
function getOrderDetail(id) {
  return Axios.get(`/order/${id}`);
}

// 订单管理-调整订单金额
function updateOrder(params) {
  return Axios.put(`/order/${params.id}/amount`, params);
}
// 订单管理-编辑人工调整的订单子项
function updateArtificialOrder(params) {
  return Axios.put(`/order/item/${params.id}`, params);
}
// 订单管理-删除订单
function delOrderDetail(params) {
  return Axios.delete(`/order/${params.id}`, { params });
}
// 订单管理-标记支付
function signPayOrder(params) {
  return Axios.put(`/order/${params.id}/status/paid`, params);
}

// 获取商品一级分组
function getFirstGroup() {
  return Axios.get(`/product/group/first`);
}
// 获取商品一级分组
function getSecondGroup() {
  return Axios.get(`/product/group/second`);
}

// 获取商品列表
function getProList(params) {
  return Axios.get(`/product`, { params });
}
// 获取产品列表
function getShopList(params) {
  return Axios.get(`/host`, { params });
}
// 获取产品相关的可升降级的商品
function getRelationList(id) {
  return Axios.get(`/product/${id}/upgrade`);
}

// 获取商品配置项参数
function getProConfig(params) {
  return Axios.get(`product/${params.id}/config_option`, { params });
}
// 根据商品配置请求价格
function getProPrice(params) {
  return Axios.post(`/product/${params.id}/config_option`, params);
}

// 获取产品详情
function getProductDetail(id) {
  return Axios.get(`/host/${id}`);
}
// 修改产品
function updateProduct(id, params) {
  return Axios.put(`/host/${id}`, params);
}
// 接口
function getInterface(params) {
  return Axios.get("/server", { params });
}
// 获取升降级订单金额
function getUpgradeAmount(params) {
  return Axios.post("/order/upgrade/amount", params);
}
// 产品模块
function getproModule(id) {
  return Axios.get(`/host/${id}/module`);
}
// 续费页面
function getSingleRenew(id) {
  return Axios.get(`/host/${id}/renew`);
}
// 续费
function postSingleRenew(params) {
  return Axios.post(`/host/${params.id}/renew`, params);
}

// 批量续费页面
function getRenewBatch(params) {
  return Axios.get(`/host/renew/batch`, { params });
}
// 批量续费
function postRenewBatch(params) {
  return Axios.post(`/host/renew/batch`, params);
}
// 系统设置
function getSystemOpt() {
  return Axios.get("/configuration/system");
}

// 充值
function recharge(params) {
  return Axios.post(`/client/${params.client_id}/recharge`, params);
}

// 获取用户等级
function getClientLevel(id) {
  return Axios.get(`/client_level/client/${id}`);
}
function updateClientLevel(params) {
  return Axios.put(`/client_level/client`, params);
}
// 所有用户等级
function getAllLevel() {
  return Axios.get(`/client_level/all`);
}

// 插件列表
function getAddon(params) {
  return Axios.get(`/active_plugin`, { params });
}
// 	产品优惠码使用记录
function proPromoRecord(params) {
  return Axios.get(`/promo_code/host/${params.id}/log`, { params });
}
/**
 * @获取子账户对应主账户
 * @param string
 */
function getAdminAccountApi(params) {
  return Axios.get(`/sub_account/parent`, { params });
}

/**
 * @获取子账户列表
 * @param string
 */
function getchildAccountListApi(params) {
  return Axios.get(`/sub_account`, { params });
}


/* 1-7新增产品手动开通等 */
// 模块开通
function createModule(params) {
  return Axios.post(`/host/${params.id}/module/create`)
}
function suspendModule(params) {
  return Axios.post(`/host/${params.id}/module/suspend`, params)
}
function unsuspendModule(params) {
  return Axios.post(`/host/${params.id}/module/unsuspend`, params)
}
function delModule(params) {
  return Axios.post(`/host/${params.id}/module/terminate`, params)
}

// 批量删除产品
function deleteHost(params) {
  return Axios.delete(`/host`, { params });
}


/* 2023-1-30新增订单详情 */
// 订单详情
function getOrderDetails(params) {
  return Axios.get(`/order/${params.id}`)
}
// 订单退款
function orderRefund(params) {
  return Axios.post(`/order/${params.id}/refund`, params)
}
// 订单退款记录列表
function getOrderRefundRecord(params) {
  return Axios.get(`/order/${params.id}/refund_record`)
}
// 删除退款记录
function delOrderRecord(params) {
  return Axios.delete(`/refund_record/${params.id}`)
}
// 订单应用余额
function orderApplyCredit(params) {
  return Axios.post(`/order/${params.id}/apply_credit`, params)
}
// 订单扣除余额
function orderRemoveCredit(params) {
  return Axios.post(`/order/${params.id}/remove_credit`, params)
}
// 修改订单支付方式
function changePayway(params) {
  return Axios.put(`/order/${params.id}/gateway`, params)
}
// 修改订单备注
function changeOrderNotes(params) {
  return Axios.put(`/order/${params.id}/notes`, params)
}
// 订单管理-删除人工调整的订单子项
function delArtificialOrder(params) {
  return Axios.delete(`/order/item/${params.id}`, params)
}

// 1-31 模块按钮输出
function getMoudleBtns(params) {
  return Axios.get(`/host/${params.id}/module/button`)
}

//产品详情
function upHostDetail(id) {
  return Axios.get(`/upstream/host/${id}`)
}


/* 个人资料-信息记录 */

function getRecordList(params) {
  return Axios.get(`/client/${params.id}/record`, { params })
}
function addAndUpdateRecord(type, params) {
  if (type === 'add') {
    return Axios.post(`/client/${params.id}/record`, params)
  } else if (type === 'update') {
    return Axios.put(`/client/record/${params.id}`, params)
  }
}
function deleteRecord(params) {
  return Axios.delete(`/client/record/${params.id}`)
}

// 获取支付接口
function getPayList() {
  return Axios.get('/gateway')
}

// 获取用户自定义字段和值
function clientCustomDetail(id) {
  return Axios.get(`/client/${id}/client_custom_field_value`);
}

// 获取商品下拉优化插件配置
function getSelectConfig() {
  return Axios.get(`/product_drop_down_select/config`)
}

// 产品内页模块输入框输出
function hostField(id) {
  return Axios.get(`/host/${id}/module/field`);
}

// 批量删除订单
function batchDelOrder (params) {
  return Axios.delete(`/order`, { params });
}