// 推广者基础信息
function promoterInfo() {
    return Axios.get(`/recommend/promoter`)
}
// 未开启描述
function description() {
    return Axios.get(`/recommend/description`)
}
// 推广页面链接
function systemUrl() {
    return Axios.get(`/recommend/promoter/system_url`)
}
// 获取自定义链接列表
function customerUrl() {
    return Axios.get(`/recommend/promoter/url`)
}
// 生成链接
function postUrl(params) {
    return Axios.post(`/recommend/promoter/url`, params)
}
// 删除链接
function delUrl(params) {
    return Axios.delete(`/recommend/promoter/url/${params.id}`, params)
}
// 获取推介记录列表
function recommendList(params) {
    return Axios.get(`/recommend`, { params })
}
// 获取提现记录列表
function withdrawList(params) {
    return Axios.get(`/withdraw`, { params })
}
// 获取推介政策
function recommendConfig(params) {
    return Axios.get(`/recommend/config`, { params })
}
// 开启推介计划
function openRecommend() {
    return Axios.post(`recommend/promoter`)
}