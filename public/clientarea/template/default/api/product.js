// 获取商品列表页
function getProduct (id) {
  return Axios.get(`/menu/${id}/host`)
}
// 获取详情
function getProductDetail (id) {
  return Axios.get(`/host/${id}/view`)
}
// 获取订购页
function getOrederConfig (id) {
  return Axios.get(`/product/${id}/config_option`)
}