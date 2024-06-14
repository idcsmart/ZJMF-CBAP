// 销售信息
function sellInfo (params) {
  return Axios.get(`/upstream/sell_info`, { params });
}

// 订单列表
function orderList (params) {
  return Axios.get(`/upstream/order`, { params });
}

// 商品列表
function upstreamList (params) {
  return Axios.get(`/upstream/product`, { params });
}

// 获取供应商商品列表
function supplierGoodsList (id) {
  return Axios.get(`/supplier/${id}/product`);
}

// 供应商列表
function supplierList (params) {
  return Axios.get(`/supplier`, { params });
}

// 添加商品
function addUpstreamProduct (params) {
  return Axios.post(`/upstream/product`, params);
}

// 编辑商品
function editUpstreamProduct (params) {
  return Axios.put(`/upstream/product/${params.id}`, params);
}

// 商品详情
function upstreamProductDetail (id) {
  return Axios.get(`/upstream/product/${id}`);
}

// 添加供应商
function addSupplier (params) {
  return Axios.post(`/supplier`, params);
}

//编辑供应商
function editSupplier (id, params) {
  return Axios.put(`/supplier/${id}`, params);
}

//供应商详情
function supplierDrtail (id) {
  return Axios.get(`/supplier/${id}`);
}

// 删除供应商
function delSupplier (id) {
  return Axios.delete(`/supplier/${id}`);
}

// 检查供应商接口连接状态
function supplierStatus (id) {
  return Axios.get(`/supplier/${id}/status`);
}
// 订单管理-调整订单金额
function updateOrder (params) {
  return Axios.put(`/order/${params.id}/amount`, params);
}

// 产品列表
function upstreamHost (params) {
  return Axios.get(`/upstream/host`, { params });
}

//产品详情
function upHostDetail (params) {
  return Axios.get(`/upstream/host/${params.id}`, { params });
}

// 推荐代理商品列表
function recomProList (params) {
  return Axios.get(`/upstream/recommend/product`, { params });
}

//代理推荐商品
function recomProduct (params) {
  return Axios.post(`/upstream/recommend/product`, params);
}
// 查看所有IP
function getAllIp (params) {
  return Axios.get(`/host/${params.id}/ip`);
}

// 编辑兑换汇率
function updateRate (params) {
  return Axios.put(`/supplier/${params.id}/rate`, params);
}
