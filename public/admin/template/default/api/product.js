/* 商品管理API */

// 获取商品列表
function getProduct(params) {
  return Axios.get(`/product`, { params })
}
// 获取商品详情
function getProductDetail(id) {
  return Axios.get(`/product/${id}`)
}
// 新增商品
function addProduct(params) {
  return Axios.post(`/product`, params)
}
// 编辑商品
function editProduct(params) {
  return Axios.put(`/product/${params.id}`, params)
}
// 编辑商品接口
function editProductServer(id, params) {
  return Axios.put(`/product/${id}/server`, params)
}

// 选择接口获取配置 
function getProductConfig(id, params) {
  return Axios.get(`/product/${id}/server/config_option`, { params })
}

// 删除商品
function deleteProduct(id) {
  return Axios.delete(`/product/${id}`)
}
// 隐藏/显示商品
function toggleShow(id, hidden) {
  return Axios.put(`/product/${id}/${hidden}`)
}
// 分组显示/隐藏
function groupListShow(id, hidden) {
  return Axios.put(`/product/group/${id}/${hidden}`)
}

// 商品拖动排序
function changeOrder(params) {
  return Axios.put(`/product/order/${params.id}`, params)
}
// 获取商品一级分组
function getFirstGroup() {
  return Axios.get(`/product/group/first`)
}
// 获取商品二级分组
function getSecondGroup() {
  return Axios.get(`/product/group/second`)
}
// 新建商品分组
function addGroup(params) {
  return Axios.post(`/product/group`, params)
}
// 编辑商品分组
function updateGroup(params) {
  return Axios.put(`/product/group/${params.id}`, params)
}
// 删除商品分组
function deleteGroup(id) {
  return Axios.delete(`/product/group/${id}`)
}
// 获取产品相关的可升降级的商品
function getRelationList(id) {
  return Axios.get(`/product/${id}/upgrade`)
}

// 一级商品分组排序
function moveFirstGroup(params) {
  return Axios.put(`/product/group/first/order/${params.id}`, params)
}
// 移动商品至其他商品组
function moveProductGroup(params) {
  return Axios.put(`/product/group/${params.id}/product`, params)
}
// 拖动商品至其他二级分组
function dragProductGroup(params) {
  return Axios.put(`/product/order/${params.id}`, params)
}
// 拖动二级商品分组
function draySecondGroup(params) {
  return Axios.put(`/product/group/order/${params.id}`, params)
}
// 短信接口
function getSmsInterface() {
  return Axios.get('/sms')
}
// 邮件接口
function getEmailInterface() {
  return Axios.get('/email')
}
// 短信模板
function getSmsTemplate(name) {
  return Axios.get(`/notice/sms/${name}/template`)
}
// 邮件模板
function getEmailTemplate() {
  return Axios.get(`/notice/email/template`)
}
// 接口
function getInterface(params) {
  return Axios.get('/server', { params })
}
// 接口分组
function getGroup(params) {
  return Axios.get('/server/group', { params })
}