/* 优惠码 */
function getPromo (params) {
  return Axios.get(`/promo_code`, { params })
}
// 优惠码详情
function getPromoDetail (params) {
  return Axios.get(`/promo_code/${params.id}`)
}
// 添加/编辑优惠码
function addAndUpdatePromo (type, params) {
  if (type === 'add') {
    return Axios.post(`/promo_code`, params)
  } else if (type === 'update') {
    return Axios.put(`/promo_code/${params.id}`, params)
  }
}
// 删除
function delPromo (params) {
  return Axios.delete(`/promo_code`, {
    data: params
  })
}
// 启用/禁用优惠码
function changePromoStatus (params) {
  return Axios.put(`/promo_code/status`, params)
}
// 随机优惠码
function getRandomPromo () {
  return Axios.get('/promo_code/generate')
}
// 优惠码使用记录
function usePromoRecord (params) {
  return Axios.get(`/promo_code/${params.id}/log`, { params })
}

// 获取商品一级分组
function getFirstGroup () {
  return Axios.get(`/product/group/first`)
}
// 获取商品二级分组
function getSecondGroup () {
  return Axios.get(`/product/group/second`)
}
// 获取商品列表
function getProduct (params) {
  return Axios.get(`/product`, { params })
}
