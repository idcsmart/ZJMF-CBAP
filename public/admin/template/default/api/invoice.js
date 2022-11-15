
// 发票列表
function invoice(params) {
    return Axios.get('/invoice', { params })
}
// 发票详情
function invoiceDetail(params) {
    return Axios.get(`/invoice/${params.id}`, { params })
}

// 通过发票
function confirmInvoice(params) {
    return Axios.post(`/invoice/${params.id}/confirm`, params)
}

// 驳回发票
function rejectInvoice(params) {
    return Axios.post(`/invoice/${params.id}/reject`, params)
}

// 发出发票
function sendInvoice(params) {
    return Axios.post(`/invoice/${params.id}/send`, params)
}

// 发票抬头列表
function invoiceTitle(params) {
    return Axios.get(`/invoice_title`, { params })
}
// 批量删除发票抬头
function delInvoiceTitle(params) {
    return Axios.delete(`/invoice_title`, { params })
}
// 收件地址列表
function invoiceAddress(params) {
    return Axios.get(`invoice_address`, { params })
}
// 删除收件地址
function delInvoiceAddress(params) {
    return Axios.delete(`/invoice_address`, { params })
}

// 发票设置
function invoiceConfig(params) {
    return Axios.get(`invoice_config`, { params })
}

// 修改发票设置
function editInvoiceConfig(params) {
    return Axios.put(`/invoice_config`, params)
}
