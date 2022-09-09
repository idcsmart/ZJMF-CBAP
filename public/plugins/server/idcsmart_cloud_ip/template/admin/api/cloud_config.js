/* 计算型号分组 */
// 分组列表
function getCalGroup (params) {
  return Axios.get(`/idcsmart_cloud/cal_group?product_id=${params.id}`, { params })
}
// 创建/修改分组
function createOrUpdateCalGroup (type, params) {
  if (type === 'add') {
    return Axios.post('/idcsmart_cloud/cal_group', params)
  } else if (type === 'update') {
    return Axios.put(`/idcsmart_cloud/cal_group/${params.id}`, params)
  }
}
// 删除分组
function deleteCalGroup (id) {
  return Axios.delete(`/idcsmart_cloud/cal_group/${id}`)
}
// 修改分组排序
function updateCalGroupOrder (params) {
  return Axios.put(`/idcsmart_cloud/cal_group/${params.id}/order`, params)
}

/* 计算型号列表 */
function getCal (params) {
  return Axios.get('/idcsmart_cloud/cal', { params })
}
function createOrUpdateCal (type, params) {
  if (type === 'add') {
    return Axios.post('/idcsmart_cloud/cal', params)
  } else if (type === 'update') {
    return Axios.put(`/idcsmart_cloud/cal/${params.id}`, params)
  }
}
// 删除
function deleteCalId (id) {
  return Axios.delete(`/idcsmart_cloud/cal/${id}`)
}
// 修改排序
function updateCalOrder (params) {
  return Axios.put(`/idcsmart_cloud/cal/${params.id}/order`, params)
}

/* 数据中心 */
function getDataCenter (params) {
  return Axios.get('/idcsmart_cloud/data_center', { params })
}
// 获取接口列表
function getInterface (params) {
  return Axios.get('/server', { params })
}
// 创建
function createOrUpdateDataCenter (type, params) {
  if (type === 'add') {
    return Axios.post('/idcsmart_cloud/data_center', params)
  } else if (type === 'update') {
    return Axios.put(`/idcsmart_cloud/data_center/${params.id}`, params)
  }
}
// 删除
function deleteDataCenter (id) {
  return Axios.delete(`/idcsmart_cloud/data_center/${id}`)
}
// 排序
function updateDataCenterOrder (params) {
  return Axios.put(`/idcsmart_cloud/data_center/${params.id}/order`, params)
}

/* 带宽类型 */
function getBwType (params) {
  return Axios.get(`/idcsmart_cloud/bw_type?product_id=${params.id}`, { params })
}

function createOrUpdateBwType (type, params) {
  if (type === 'add') {
    return Axios.post('/idcsmart_cloud/bw_type', params)
  } else if (type === 'update') {
    return Axios.put(`/idcsmart_cloud/bw_type/${params.id}`, params)
  }

}
// 删除
function deleteBwType (id) {
  return Axios.delete(`/idcsmart_cloud/bw_type/${id}`)
}
// 排序
function updateBwTypeOrder (params) {
  return Axios.put(`/idcsmart_cloud/bw_type/${params.id}/order`, params)
}
/* 带宽 */
function getBw (params) {
  return Axios.get('/idcsmart_cloud/bw', { params })
}
function createOrUpdateBw (type, params) {
  if (type === 'add') {
    return Axios.post('/idcsmart_cloud/bw', params)
  } else if (type === 'update') {
    return Axios.put(`/idcsmart_cloud/bw/${params.id}`, params)
  }
}
// 删除
function deleteBwId (id) {
  return Axios.delete(`/idcsmart_cloud/bw/${id}`)
}
/* 套餐 */
function getPackage (params) {
  return Axios.get('/idcsmart_cloud/package', { params })
}
function createOrUpdatePackage (type, params) {
  if (type === 'add') {
    return Axios.post('/idcsmart_cloud/package', params)
  } else if (type === 'update') {
    return Axios.put(`/idcsmart_cloud/package/${params.id}`, params)
  }
}

// 删除
function deletePackage (id) {
  return Axios.delete(`/idcsmart_cloud/package/${id}`)
}

/* 周期 */
function getDuration (id) {
  return Axios.get(`/idcsmart_cloud/duration_price?product_id=${id}`)
}
// 修改
function updateDuration (params) {
  return Axios.put(`/idcsmart_cloud/duration_price`, params)
}

/* 其他设置 */
function getSetting (id) {
  return Axios.get(`/idcsmart_cloud/config?product_id=${id}`)
}
// 修改
function updateSetting (params) {
  return Axios.put(`/idcsmart_cloud/config`, params)
}

/* 镜像分组 */
function getImageGroup (id) {
  return Axios.get(`/idcsmart_cloud/image_group?product_id=${id}`)
}
function createOrUpdateImageGroup (type, params) {
  if (type === 'add') {
    return Axios.post('/idcsmart_cloud/image_group', params)
  } else if (type === 'update') {
    return Axios.put(`/idcsmart_cloud/image_group/${params.id}`, params)
  }
}
// 删除
function deleteImageGroupId (id) {
  return Axios.delete(`/idcsmart_cloud/image_group/${id}`)
}
// 排序
function updateImageGroupOrder (params) {
  return Axios.put(`/idcsmart_cloud/image_group/${params.id}/order`, params)
}
// 切换是否启用
function changeImageGroupEnable (params) {
  return Axios.put(`/idcsmart_cloud/image_group/${params.id}/enable`, params)
}

/* 镜像 */
function getImage (params) {
  return Axios.get('/idcsmart_cloud/image', { params })
}
function createImage (params) {
  return Axios.post('/idcsmart_cloud/image', params)
}
// 修改
function updateImage (params) {
  return Axios.put(`/idcsmart_cloud/image/${params.id}`, params)
}
// 删除
function deleteImage (id) {
  return Axios.delete(`/idcsmart_cloud/image/${id}`)
}
// 排序
function updateImageOrder (params) {
  return Axios.put(`/idcsmart_cloud/image/${params.id}/order`, params)
}
// 切换是否启用
function changeImageEnable (params) {
  return Axios.put(`/idcsmart_cloud/image/${params.id}/enable`, params)
}
// 获取存在状态
function getImageStatus (id) {
  return Axios.get(`/idcsmart_cloud/image/status?product_id=${id}`)
}
// 镜像对比列表
function getImageCompare (id) {
  return Axios.post(`/idcsmart_cloud/image/compare?product_id=${id}`)
}
