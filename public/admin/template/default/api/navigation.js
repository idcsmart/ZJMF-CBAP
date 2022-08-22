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
