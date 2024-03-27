// 提现列表
function getWithdrawal (params) {
  return Axios.get('/withdraw', { params })
}
// 审核通过/驳回
function changeStatus (params) {
  return Axios.put(`/withdraw/${params.id}/audit`, params)
}
// 提现列表
function getWithdrawalRules (params) {
  return Axios.get('/withdraw/rule', { params })
}
// 开启/关闭规则
function changeRuleStatus (params) {
  return Axios.put(`/withdraw/rule/${params.id}/status`, params)
}
// 获取提现来源
function getSource () {
  return Axios.get('/withdraw/source')
}
// 保存提现来源
function submitSource (params) {
  return Axios.put('/withdraw/source', params)
}
// 新增提现规则
function createRules (params) {
  return Axios.post('/withdraw/rule', params)
}
// 编辑提现规则
function updateRules (params) {
  return Axios.put(`/withdraw/rule/${params.id}`, params)
}
// 删除提现规则
function deleteRules (id) {
  return Axios.delete(`/withdraw/rule/${id}`)
}
// 提现规则详情
function ruleDetail (id) {
  return Axios.get(`/withdraw/rule/${id}`)
}
// 插件列表
function getAddon (params) {
  return Axios.get(`/plugin/addon`, { params })
}

// 获取余额提现设置
function getCreditRule () {
  return Axios.get(`/withdraw/rule/credit`)
}
// 保存余额提现设置
function saveCreditRule (params) {
  return Axios.put(`/withdraw/rule/credit`, params)
}
// 修改提现状态
function changeWithdrawStatus (params) {
  return Axios.put(`/withdraw/${params.id}/status`, params)
}

// 确认已汇款
function submitPay (params) {
  return Axios.put(`/withdraw/${params.id}/confirm_remit`, params)
}
// 修改提现交易流水号
function updateTransaction (params) {
  return Axios.put(`/withdraw/${params.id}/transaction_number`, params)
}

// 提现方式列表
function getWithdrawWay () {
  return Axios.get(`/withdraw/method`)
}
function andAndUpdateWithdrawWay (type, params) {
  if (type === 'add') {
    return Axios.post(`/withdraw/method`, params)
  } else if (type === 'update') {
    return Axios.put(`/withdraw/method/${params.id}`, params)
  }
}
function delWithdrawWay (id) {
  return Axios.delete(`/withdraw/method/${id}`)
}

// 驳回原因列表
function getRejectReason () {
  return Axios.get(`/withdraw/reject_reason`)
}
function andAndUpdateRejectReason (type, params) {
  if (type === 'add') {
    return Axios.post(`/withdraw/reject_reason`, params)
  } else if (type === 'update') {
    return Axios.put(`/withdraw/reject_reason/${params.id}`, params)
  }
}
function delRejectReason (id) {
  return Axios.delete(`/withdraw/reject_reason/${id}`)
}