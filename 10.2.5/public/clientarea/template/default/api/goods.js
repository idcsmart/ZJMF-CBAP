// 产品订购地址
function goodsUrl(params) {
    return Axios.get(`/baidu_cloud/goods`, { params });
}
// 获取token
function goodsToken() {
    return Axios.get(`/baidu_cloud/token`);
}
// 产品订购
function goodsSettle(params) {
    return Axios.get(`/baidu_cloud/settle`, { params });
}
// 产品列表地址
function goodsList(params) {
    return Axios.get(`/baidu_cloud/list`, { params });
}