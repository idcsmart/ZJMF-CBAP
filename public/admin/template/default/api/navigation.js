// 获取后台导航
function adminMenu(){
    return Axios.get(`/menu/admin`)
}
// 获取前台导航
function homeMenu(){
    return Axios.get(`/menu/home`)
}
// 保存前台导航
function saveHomeMenu(params){
    return Axios.put(`/menu/home`,params)
}
// 保存后台导航
function saveAdminMenu(params){
    return Axios.put(`/menu/admin`,params)
}

// 根据模块获取商品列表
function productBymodule(params){
    return Axios.get(`/module/${params.module}/product`)
}

// 获取后台导航
function leftMenu(){
    return Axios.get(`/menu`)
}