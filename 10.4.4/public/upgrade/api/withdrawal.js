// 获取提现列表
function getWithdrawList(params) {
    return Axios.get('/withdraw',{params})
}

// 提现审核
function withdrawAudit(params){
    return Axios.put(`/withdraw/${params.id}/audit`)
}