// 获取工单列表
function getUserOrder (params) {
  return Axios.get('/ticket', {'params': params});
}
// 获取工单详情
function getUserOrderDetail (id) {
  return Axios.get(`/ticket/${id}`);
}
// 回复工单
function replyUserOrder (id, params) {
  return Axios.post(`/ticket/${id}/reply`, params);
}
// 接收工单
function receiveUserOrder (id) {
  return Axios.put(`/ticket/${id}/receive`);
}
// 已解决工单
function resolvedUserOrder (id) {
  return Axios.put(`/ticket/${id}/resolved`);
}

// 获取内部工单列表
function getInternalOrder (params) {
  return Axios.get('/ticket/internal', {'params': params});
}
// 获取内部工单详情
function getInternalOrderDetail (id) {
  return Axios.get(`/ticket/internal/${id}`);
}
// 创建内部工单
function newInternalOrder (params) {
  return Axios.post('/ticket/internal', params);
}
// 接收内部工单
function receiveInternalOrder (id) {
  return Axios.put(`/ticket/internal/${id}/receive`);
}
// 回复内部工单
function replyInternalOrder (id, params) {
  return Axios.post(`/ticket/internal/${id}/reply`, params);
}
// 已解决内部工单
function resolvedInternalOrder (id) {
  return Axios.put(`/ticket/internal/${id}/resolved`);
}
// 转发内部工单
function forwardInternalOrder (id, params) {
  return Axios.put(`/ticket/internal/${id}/forward`, params);
}

// 获取工单类型
function getUserOrderType () {
  return Axios.get('/ticket/type');
}
// 新增工单类型
function orderTypeAdd (params) {
  return Axios.post('/ticket/type', params);
}
// 编辑工单类型
function orderTypeEdit(id, params) {
  return Axios.put(`/ticket/type/${id}`, params);
}
// 删除工单类型
function orderTypeDelete (id) {
  return Axios.delete(`/ticket/type/${id}`);
}

// 获取管理员分组（部门）数据
function getAdminRole (params) {
  return Axios.get('/admin/role', {'params': params});
}
// 获取管理员（人员）数据
function getAdminList (params) {
  return Axios.get('/admin', {'params': params});
}
// 获取产品数据
function getHost (params) {
  return Axios.get('/host', {'params': params});
}
// 获取用户
function getClient (params) {
  return Axios.get('/client', {'params': params});
}
// 文件下载
function downloadFile (data) {
  return Axios.post("/ticket/download",data,{
    'responseType': 'blob'  //设置响应的数据类型为一个包含二进制数据的 Blob 对象，必须设置！！！
  });
}
