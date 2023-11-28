// 获取商品列表页
function getProduct(id) {
  return Axios.get(`/menu/${id}/host`)
}
// 获取详情
function getProductDetail(id) {
  return Axios.get(`/host/${id}/view`)
}
// 获取订购页
function getOrederConfig(params) {
  return Axios.get(`/product/${params.id}/config_option`, { params })
}
// 产品详情
function hostDetail(params) {
  return Axios.get(`/host/${params.id}`, { params });
}
// 产品合同是否逾期
function timeoutStatus(id) {
  return Axios.get(`/e_contract/host/${id}/timeout`);
}