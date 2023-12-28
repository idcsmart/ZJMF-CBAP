// 首页挂件
function getWidget () {
  return Axios.get(`/widget`)
}
function saveWidget (params) {
  return Axios.put(`/widget/order`, params)
}
function changeWidget (params) {
  return Axios.put(`/widget/status`, params)
}
function getWidgetContent (params) {
  return Axios.get(`/widget/output`, { params })
}