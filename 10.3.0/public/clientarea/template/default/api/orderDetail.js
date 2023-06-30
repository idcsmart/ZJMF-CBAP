// 订单详情
function orderDetail(id) {
    return Axios.get(`/order/${id}`);
}

// 交易记录
function transactionDetail(params) {
    return Axios.get(`/transaction`, { params });
}

// 余额变更记录列表
function creditList(params) {
    return Axios.get(`/credit`, { params });
}