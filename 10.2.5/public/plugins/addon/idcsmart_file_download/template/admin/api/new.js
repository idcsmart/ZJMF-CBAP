//新闻列表
function helplist(params) {
  return Axios.get("/news", { params: { ...params } });
}
// 删除新闻
function helpdelete(params) {
  return Axios.delete("/news/" + params.id, params);
}
//隐藏/显示新闻
function helphidden(params) {
  return Axios.put("/news/" + params.id + "/hidden", params);
}
//获取新闻分类
function gethelptype() {
  return Axios.get("/news/type");
}
//添加新闻分类
function addhelptype(params) {
  return Axios.post("/news/type", params);
}
//修改新闻分类
function edithelptype(params) {
  return Axios.put("/news/type/" + params.id, params);
}
//删除新闻分类
function deletehelptype(params) {
  return Axios.delete("/news/type/" + params.id, { params: { ...params } });
}
//新闻详情
function helpdetial(params) {
  return Axios.get("/news/" + params.id, { params: { ...params } });
}
//添加新闻
function addhelp(params) {
  return Axios.post("/news", params);
}
//修改新闻
function edithelp(params) {
  return Axios.put("/news/" + params.id, params);
}
