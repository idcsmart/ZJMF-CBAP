
/* 安全组 */
function getGroup (params) {
  return Axios.get(`/security_group`, { params });
}
function getGroupDetail (id) {
  return Axios.get(`/security_group/${id}`);
}
function addAndUpdateGroup (type, params) {
  if (type === 'add') {
    return Axios.post(`/security_group`, params);
  } else if (type === 'update') {
    return Axios.put(`/security_group/${params.id}`, params);
  }
}
function deleteGroup (id) {
  return Axios.delete(`/security_group/${id}`);
}

/* 安全组规则 */
function getGroupRules (params) {
  return Axios.get(`/security_group/${params.id}/rule`, { params });
}
function addAndUpdateGroupRules (type, params) {
  if (type === 'add') {
    return Axios.post(`/security_group/${params.id}/rule`, params);
  } else if (type === 'update') {
    return Axios.put(`/security_group/rule/${params.id}`, params);
  }
}
function deleteGroupRules (id) {
  return Axios.delete(`/security_group/rule/${id}`);
}
// 批量添加安全组规则
function batchRules (params) {
  return Axios.post(`/security_group/${params.id}/rule/batch`, params);
}

// 安全组实例列表
function getGroupCloud (params) {
  return Axios.get(`/security_group/${params.id}/host`, {params});
}
// 关联安全组
function concatCloud (params) {
  return Axios.post(`/security_group/${params.id}/host/${params.host_id}`,params);
}
// 取消关联实例
function cancelConcatCloud (params) {
  return Axios.delete(`/security_group/${params.id}/host/${params.host_id}`);
}
// 获取所有可用实例
// function getAllCloud () {
//   return Axios.get(`/idcsmart_cloud/all`);
// }
function getAllCloud (params) {
  return Axios.get(`/mf_cloud`, {params});
}