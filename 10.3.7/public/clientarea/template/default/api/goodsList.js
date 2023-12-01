// 获取商品一级分组
function productGroupFirst() {
    return Axios.get(`/product/group/first`)
}

//获取商品二级分组
function productGroupSecond(id) {
    return Axios.get(`/product/group/second?id=${id}`, id)
}

// 商品列表
function productGoods(params) {
    return Axios.get(`/product`, { params })
}

// 获取可用域名后缀
function domainSuffix(id) {
    return Axios.get(`/idcsmart_domain/domain_suffix?host_id=${id}`)
}

// 域名查询
function domainSearch(params) {
    return Axios.get(`/idcsmart_domain/check_domain`, { params })
}

// 获取域名价格
function domainPrice(params) {
    return Axios.get(`/idcsmart_domain/get_price`, { params })
}

// 获取whois信息
function domainWhois(params) {
    return Axios.get(`/idcsmart_domain/whois`, { params })
}

// 加入购物车
function addToCart(params) {
    return Axios.post(`/cart`, params, { timeout: 1000 * 60 * 20 })
}

// 结算购物车
function cartCheckout(params) {
    return Axios.post(`/cart/settle`, params)
}


// 编辑购物车商品
function updateCart(params) {
    return Axios.put(`/cart/${params.position}`, params)
}

// 删除购物车商品
function deleteCart(params) {
    return Axios.delete(`/cart/${params.position}`, params)
}

// 批量删除购物车商品
function deleteCartBatch(params) {
    return Axios.delete(`/cart/batch`, { params })
}

// 信息模板列表
function templateList(params) {
    return Axios.get(`/idcsmart_domain/info_template`, { params })
}

// 信息模板详情
function templateDetails(id) {
    return Axios.get(`/idcsmart_domain/info_template/${id}`)
}

// 新建信息模板
function templateAdd(params) {
    return Axios.post(`/idcsmart_domain/info_template`, params)
}

// 删除信息模板
function templateDelete(id) {
    return Axios.delete(`/idcsmart_domain/info_template/${id}`)
}

// 信息模板实名认证
function templateAuth(params) {
    return Axios.post(`/idcsmart_domain/info_template/${params.id}/certifications`, params)
}

// 支持的信息模板
function templateSupport(params) {
    return Axios.get(`/idcsmart_domain/info_template/support`, { params })
}

// 批量查询域名
function domainBatch(params) {
    return Axios.get(`/idcsmart_domain/bulk_check`, {
        params,
        timeout: 1000 * 60 * 20
    })
}

// 获取域名设置
function domainSetting() {
    return Axios.get(`/idcsmart_domain/config`)
}
