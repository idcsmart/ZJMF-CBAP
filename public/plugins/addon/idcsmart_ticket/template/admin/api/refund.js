function getRefundList (params) {
    return Axios.get('/refund/product', {'params': params});
}

function addRefund (params) {
    return Axios.post('/refund/product', params);
}

function upDateRefund (params) {
    return Axios.put(`/refund/product/${params.id}`, params);
}

function deleteRefund(id) {
    return Axios.delete(`/refund/product/${id}`);
}
function getARefund(id) {
    return Axios.get(`/refund/product/${id}`);
}
function getARefundConfig(id) {
    return Axios.get(`/product/${id}/config_option`, {'id': id});
}
function getARefundConfigPrice(params) {
    return Axios.post('/product/:id/config_option', params);
}
function getProductDetail(id) {
    return Axios.get(`/product/${id}`, {'id': id});
}
function getProductList(params) {
    return Axios.get('/product', {'params': params});
}

//	停用原因列表
function reasonList() {
    return Axios.get('/refund/reason');
}

//新增停用原因
function addReason(params) {
    return Axios.post('/refund/reason',params);
}
//编辑停用原因
function putReason(params) {
    return Axios.put(`/refund/reason/${params.id}`,params);
}
//删除停用原因
function deleteReason(id) {
    return Axios.delete(`/refund/reason/${id}`);
}


//申请退款list
function getRefund(params) {
    return Axios.get('/refund',{'params': params});
}

//取消
function endRefund(id) {
    return Axios.put(`refund/${id}/cancel`);
}

//驳回
function NoRefund(data) {
    return Axios.put(`refund/${data.id}/reject`,data);
}

//通过
function okRefund(id) {
    return Axios.put(`refund/${id}/pending`);
}


//获取停用原因
function getReasonCustom() {
    return Axios.get('refund/reason/custom');
}

//设置停用原因
function setReasonCustom(data) {
    return Axios.post('refund/reason/custom',data);
}
