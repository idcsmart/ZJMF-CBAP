/**
 * @创建应用
 */
function creaetAppApi (params) {
  return Axios.post('/app_market/app', params)
}

/**
 * @获取应用详情
 */
function queryAppDetailApi (params) {
  return Axios.get(`/app_market/app/${params.id}`, params)
}

/**
 * @修改应用详情
 */
function changeAppApi (params) {
  return Axios.put(`/app_market/app/${params.id}`, params)
}