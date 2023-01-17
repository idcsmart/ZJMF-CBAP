function createServeApi (params) {
  return Axios.post('/app_market/service', params)
}

/**
 * @获取服务详情
 */
function queryServeDetailApi (params) {
  return Axios.get(`app_market/service/${params.id}`, { params })
}

/**
 * @修改服务
 */
function changeServeApi (params) {
  return Axios.put(`app_market/service/${params.id}`, params)
}