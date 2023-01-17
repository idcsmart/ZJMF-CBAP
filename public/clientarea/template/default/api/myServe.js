
/**
 * @获取授权列表
 */
function queryAuthorizetApi (params) {
  return Axios.get(`/app_market/market/authorize`, { params });
}

/**
 * @获取我的应用
 */
function queryMyAppApi (params) {
  return Axios.get(`/app_market/market/my_app`, { params });
}

/**
 * @获取我的服务
 */
function queryMyServeApi (params) {
  return Axios.get(`/app_market/market/my_service`, { params });
}
