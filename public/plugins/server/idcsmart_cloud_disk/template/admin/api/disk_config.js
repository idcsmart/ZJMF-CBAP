/* 磁盘 */
function getDisk (params) {
  return Axios.get(`/idcsmart_cloud_disk/package`, { params })
}
// 创建，修改磁盘
function addAndUpdateDisk (type, params) {
  if (type === 'add') {
    return Axios.post(`/idcsmart_cloud_disk/package`, params)
  } else if (type === 'update') {
    return Axios.put(`idcsmart_cloud_disk/package/${params.id}`, params)
  }
}
// 删除
function delDisk (id) {
  return Axios.delete(`/idcsmart_cloud_disk/package/${id}`)
}
/* 周期 */
function getDuration (id) {
  return Axios.get(`/idcsmart_cloud_disk/duration_price?product_id=${id}`)
}
// 修改
function updateDuration (params) {
  return Axios.put(`/idcsmart_cloud_disk/duration_price`, params)
}
/* 数据中心 */
function getDataCenter (params) {
  return Axios.get('/idcsmart_cloud/data_center', { params })
}