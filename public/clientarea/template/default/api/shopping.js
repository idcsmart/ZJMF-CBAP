// 获取购物车
function cartList() {
    return Axios.get(`/cart`)
}

//修改购物车商品数量
function editGoodsNum(index, num) {
    return Axios.put(`/cart/${index}/qty`, { qty: num })
}

//批量删除购物车商品
function deleteGoods(arr) {
    const params = { positions: arr }
    return Axios.delete(`/cart/batch`, { data: params })
}

// 修改配置计算价格
function configOption(id, config) {
    return Axios.post(`/product/${id}/config_option`, { config_options: config })
}

// 结算购物车
function cart_settle(params) {
    return Axios.post(`/cart/settle`, params)
}

// 获取商品折扣金额
function clientLevelAmount(params) {
    return Axios.get(`/client_level/product/${params.id}/amount`, { params });
}


// 结算商品
function product_settle(params) {
    return Axios.post(`/product/settle`, params)
}
//支付接口 
function payLisy() {
    return Axios.get(`/gateway`)
}

// 商品列表
function productDetail(id) {
    return Axios.get(`/product/${id}`)
}