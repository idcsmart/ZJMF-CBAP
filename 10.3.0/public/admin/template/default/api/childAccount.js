/**
 * @获取子账户详情
 */
function getChildAccountDetailAPI(id) {
  return Axios.get(`/sub_account/${id}`);
}

/**
 * @获取模块列表
 */
function queryTreeAPI(id) {
  return Axios.get(`/clientarea_auth`);
}

/**
 * @获取产品列表
 */
function queryProductListAPI(id) {
  return Axios.get(`/client/${id}/host/all`);
}

/**
 * @编辑子账户
 */
function editProductAPI(parasm) {
  return Axios.put(`/sub_account/${parasm.id}`, parasm);
}

/**
 * @获取权限树
 */
function queryModelAPI(id) {
  return Axios.get(`/module`);
}
