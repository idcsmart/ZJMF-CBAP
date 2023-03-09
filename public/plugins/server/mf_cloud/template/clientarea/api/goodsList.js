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

