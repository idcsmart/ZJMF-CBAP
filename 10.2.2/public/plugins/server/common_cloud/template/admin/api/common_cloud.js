/* 通用云配置 */
function getCountry () {
  return Axios.get(`/country`)
}
/* 套餐配置 */
function getPackage (params) {
  return Axios.get(`/common_cloud/package`, { params })
}
// 创建/修改
function createOrUpdatePackage (type, params) {
  if (type === 'add') {
    return Axios.post('/common_cloud/package', params)
  } else if (type === 'update') {
    return Axios.put(`/common_cloud/package/${params.id}`, params)
  }
}
// 删除
function deletePackage (id) {
  return Axios.delete(`/common_cloud/package/${id}`)
}
// 修改排序
function updatePackageOrders (params) {
  return Axios.put(`/common_cloud/package/${params.id}/order`, params)
}

/* 数据中心 */
function getDataCenter (params) {
  return Axios.get(`/common_cloud/data_center`, { params })
}
// 创建/修改
function createOrUpdateDataCenter (type, params) {
  if (type === 'add') {
    return Axios.post('/common_cloud/data_center', params)
  } else if (type === 'update') {
    return Axios.put(`/common_cloud/data_center/${params.id}`, params)
  }
}
// 删除
function deleteDataCenter (id) {
  return Axios.delete(`/common_cloud/data_center/${id}`)
}
// 修改排序
function updateDataCenterOrders (params) {
  return Axios.put(`/common_cloud/data_center/${params.id}/order`, params)
}

/* 镜像管理 */
function getImage(params) {
  return Axios.get(`/common_cloud/image`, { params })
}
function updateImage(params) {
  return Axios.put(`/common_cloud/image`, params)
}
function getImageStatus(params) {
  return Axios.get(`/common_cloud/image/sync`, { params })
}

/* 备份/快照 */
function getBackupConfig(params) {
  return Axios.get(`/common_cloud/backup_config`, { params })
}
// 创建/修改
function createOrUpdateBackup (type, params) {
  if (type === 'add') {
    return Axios.post('/common_cloud/backup_config', params)
  } else if (type === 'update') {
    return Axios.put(`/common_cloud/backup_config/${params.id}`, params)
  }
}
// 删除
function deleteBackup (id) {
  return Axios.delete(`/common_cloud/backup_config/${id}`)
}
function getOtherConfig(params) {
  return Axios.get(`/common_cloud/config`, { params })
}
function saveOtherConfig(params) {
  return Axios.put(`/common_cloud/config`, params)
}