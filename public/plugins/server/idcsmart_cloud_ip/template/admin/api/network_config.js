/* 网络 */
function getNetwork (params) {
  return Axios.get(`/idcsmart_cloud_ip/package`, { params })
}
// 创建，修改
function addAndUpdateNetwork (type, params) {
  if (type === 'add') {
    return Axios.post(`/idcsmart_cloud_disk/package`, params)
  } else if (type === 'update') {
    return Axios.put(`/idcsmart_cloud_ip/package/${params.id}`, params)
  }
}
// 删除
function delNetwork (id) {
  return Axios.delete(`/idcsmart_cloud_disk/package/${id}`)
}
/* 周期 */
function getDuration (id) {
  return Axios.get(`/idcsmart_cloud_ip/duration_price?product_id=${id}`)
}
// 修改
function updateDuration (params) {
  return Axios.put(`/idcsmart_cloud_ip/duration_price`, params)
}
/* 数据中心 */
function getDataCenter (params) {
  return Axios.get('/idcsmart_cloud/data_center', { params })
}