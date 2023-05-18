/**
 * @获取子账户列表
 */
function queryChildAccountListAPI(params) {
  return Axios.get(`/sub_account`, { params });
}
/**
 * @删除子账户
 */
function delChildAccountAPI(params) {
  return Axios.delete(`/sub_account/${params.id}`, { params });
}
/**
 * @获取子账户详情
 */
function queryChildAccountDteailAPI(params) {
  return Axios.get(`/sub_account/${params.id}`, params);
}
/**
 * @编辑子账户
 */
function editChildAccountDteailAPI(params) {
  return Axios.put(`/sub_account/${params.id}`, params);
}
/**
 * @修改子账户状态
 * @param {*id:Number}
 */
function changeChildAccountDteailAPI(params) {
  return Axios.put(`/sub_account/${params.id}/status`, params);
}

/**
 * @获取项目列表
 */
function queryProjectListAPI(params) {
  return Axios.get(`/project `, { params });
}

/**
 * @获取权限列表
 */
function queryPermissionsListAPI(params) {
  return Axios.get(`/auth `, { params });
}

/**
 * @获取用户所有产品
 */
function queryAllProductAPI(params) {
  return Axios.get(`/host/all  `, { params });
}

/**
 * @获取用户所有模块
 */
function queryAllModelAPI(params) {
  return Axios.get(`/module  `, { params });
}

/**
 * @创建子账户
 */
function createChilAccountAPI(params) {
  return Axios.post(`/sub_account  `, params);
}

/**
 * @获取国家及区号
 */
function queryCountryAPI(params) {
  return Axios.get(`/country  `, params);
}