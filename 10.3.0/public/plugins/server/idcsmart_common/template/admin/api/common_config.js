/* 	通用商品 */
// 	商品基础信息
function getCountry () {
  return Axios.get(`/country`)
}
function getProductInfo (product_id) {
  return Axios.get(`/idcsmart_common/product/${product_id}`)
}
function saveProductInfo (params) {
  return Axios.post(`/idcsmart_common/product/${params.product_id}`, params)
}

// 周期
function getProCycle (params) {
  return Axios.post(`/idcsmart_common/product/${params.product_id}/custom_cycle/${params.id}`)
}
function addAndUpdateProCycle (type, params) {
  if (type === 'add') {
    return Axios.post(`/idcsmart_common/product/${params.product_id}/custom_cycle`, params)
  } else if (type === 'update') {
    return Axios.put(`/idcsmart_common/product/${params.product_id}/custom_cycle/${params.id}`, params)
  }
}
function deleteProCycle (params) {
  return Axios.delete(`/idcsmart_common/product/${params.product_id}/custom_cycle/${params.id}`)
}

// 配置选项
function getConfigoption (params) {
  return Axios.get(`/idcsmart_common/product/${params.product_id}/configoption`)
}
function getConfigoptionDetail (params) {
  return Axios.get(`/idcsmart_common/product/${params.product_id}/configoption/${params.id}`)
}
function addAndUpdateConfigoption (type, params) {
  if (type === 'add') {
    return Axios.post(`/idcsmart_common/product/${params.product_id}/configoption`, params)
  } else if (type === 'update') {
    return Axios.put(`/idcsmart_common/product/${params.product_id}/configoption/${params.id}`, params)
  }
}
function deleteConfigoption (params) {
  return Axios.delete(`/idcsmart_common/product/${params.product_id}/configoption/${params.id}`)
}
// 显示/隐藏
function changeConfigoption (params) {
  return Axios.put(`/idcsmart_common/product/${params.product_id}/configoption/${params.id}/hidden`, params)
}


// 配置子项
function getConfigSubDetail (params) {
  return Axios.get(`/idcsmart_common/configoption/${params.product_id}/sub/${params.id}`)
}
function addAndUpdateConfigSub (type, params) {
  if (type === 'add') {
    return Axios.post(`/idcsmart_common/configoption/${params.product_id}/sub`, params)
  } else if (type === 'update') {
    return Axios.put(`/idcsmart_common/configoption/${params.product_id}/sub/${params.id}`, params)
  }
}
function deleteConfigSub (params) {
  return Axios.delete(`/idcsmart_common/configoption/${params.configoption_id}/sub/${params.id}`, params)
}



/* 内页模块相关 */
// 产品配置信息
function getProInfo (params) {
  return Axios.get(`/idcsmart_common/host/${params.id}`)
}
function saveProInfo (params) {
  return Axios.put(`/idcsmart_common/host/${params.id}`, params)
}

/* 子接口列表 */
function getChildInterface (params) {
  return Axios.get('/idcsmart_common/server', { params })
}
