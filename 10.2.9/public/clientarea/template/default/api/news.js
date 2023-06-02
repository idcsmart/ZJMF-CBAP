// 获取新闻分类
function getNews () {
  return Axios.get(`/news/type`)
}
// 新闻列表
function getNewsList (params) {
  return Axios.get(`/news`, { params })
}
// 新闻详情
function getNewsDetail (id) {
  return Axios.get(`/news/${id}`)
}