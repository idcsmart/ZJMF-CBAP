// 产品列表
function getMfList (params) {
  return Axios.get(`/product/common_cloud`, { params });
}
// 获取订购页面配置
function getOrderConfig (params) {
  return Axios.get(`/product/${params.id}/remf_cloud/order_page`, { params });
}
// 获取操作系统列表
function getSystemList (params) {
  return Axios.get(`/product/${params.id}/remf_cloud/image`);
}
function productInfo(id) {
  return Axios.get(`/product/${id}`);
}
// 获取商品配置所有周期价格
function getDuration (params) {
  return Axios.post(`/product/${params.id}/remf_cloud/duration`, params);
}
// 修改配置计算价格
function calcPrice (params) {
  return Axios.post(`/product/${params.id}/config_option`, params)
}
// 结算商品
function settle (params) {
  return Axios.post(`/product/settle`, params)
}
// 使用优惠码
function usePromo (params) {
  return Axios.post(`/promo_code/apply`, params)
}
// 加入购物车
function addToCart (params) {
  return Axios.post(`/cart`, params);
}
// 修改购物车
function updateCart (params) {
  return Axios.put(`/cart/${params.position}`, params);
}
// 获取购物车
function getCart () {
  return Axios.get(`/cart`);
}
// 获取线路详情
function getLineDetail (params) {
  return Axios.get(`/product/${params.id}/remf_cloud/line/${params.line_id}`);
}
// 获取ssh列表
function getSshList (params) {
  return Axios.get(`/ssh_key`, { params });
}
// 获取安全组
function getGroup (params) {
  return Axios.get(`/security_group`, { params });
}
// 获取VPC
function getVpc (params) {
  return Axios.get(`/product/${params.id}/remf_cloud/vpc_network/search`, { params });
}
// 用户等级折扣
function getLevelDiscount(params) {
  return Axios.get(`/client_level/product/${params.id}/amount`, { params });
}