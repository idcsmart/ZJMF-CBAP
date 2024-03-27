/* 管理相关API */

// 任务
function getTask (params) {
  return Axios.get('/task', { params })
}
function reloadTask (id) {
  return Axios.put(`/task/${id}/retry`)
}
// 用户管理-用户列表
function getClientList (params) {
  return Axios.get(`/client`, { params })
}
// 日志
function getSystemLog (params) {
  return Axios.get('/log/system', { params })
}
function getEmailLog (params) {
  return Axios.get('/log/notice/email', { params })
}
function getSmsLog (params) {
  return Axios.get('/log/notice/sms', { params })
}

// 自动化
function getTaskConfig () {
  return Axios.get('/configuration/cron')
}
function updateTaskConfig (params) {
  return Axios.put('/configuration/cron', params)
}

// 接口
function getInterface (params) {
  return Axios.get('/server', { params })
}
function addAndUpdateInterface (type, params) {
  if (type === 'create') {
    return Axios.post(`/server`, params)
  } else {
    return Axios.put(`/server/${params.id}`, params)
  }
}
function deleteInterface (id) {
  return Axios.delete(`/server/${id}`)
}
function getInterfaceStatus (id) {
  return Axios.get(`/server/${id}/status`)
}

// 获取模板类型
function getInterfaceType () {
  return Axios.get('/module')
}

// 接口分组
function getGroup (params) {
  return Axios.get('/server/group', { params })
}
function addAndUpdateGroup (type, params) {
  if (type === 'create') {
    return Axios.post(`/server/group`, params)
  } else {
    return Axios.put(`/server/group/${params.id}`, params)
  }
}
function deleteGroup (id) {
  return Axios.delete(`/server/group/${id}`)
}