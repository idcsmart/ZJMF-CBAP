// 获取订购页套餐
function orderPackge(params) {
    return Axios.get(`/product/${params.product_id}/common_cloud/package`, { params });
}

// 获取数据中心
function dataCenter(params) {
    return Axios.get(`/product/${params.id}/common_cloud/data_center`, { params });
}
// 获取其它设置
function config(params) {
    return Axios.get(`/product/${params.product_id}/common_cloud/config`, { params });
}
// 获取可用操作系统
function image(params) {
    return Axios.get(`/product/${params.id}/common_cloud/image`, { params });
}
// 检查产品是否购买过镜像
function checkImage(params) {
    return Axios.get(`/common_cloud/${params.id}/image/check`, { params });
}
// 生成购买镜像订单
function imageOrder(params) {
    return Axios.post(`/common_cloud/${params.id}/image/order`, params);
}
// 获取SSH秘钥列表
function sshKey(params) {
    return Axios.get(`/ssh_key`, { params });
}
// 修改配置计算价格
function configPrice(params) {
    return Axios.post(`/product/${params.id}/config_option`, params);
}
// 加入购物车
function cart(params) {
    return Axios.post(`/cart`, params);
}

// 结算商品
function settle(params) {
    return Axios.post(`/product/settle`, params)
}

// 应用优惠码
function promoCode(params) {
    return Axios.post(`/promo_code/apply`, params)
}

// 获取商品折扣金额
function clientLevelAmount(params) {
    return Axios.get(`/client_level/product/${params.id}/amount`, { params });
}

// 获取套餐所有周期价格
function duration(params) {
    return Axios.post(`/product/${params.id}/common_cloud/duration`, params)
}