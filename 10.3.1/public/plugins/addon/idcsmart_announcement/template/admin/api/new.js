//新闻列表
function helplist(params) {
  return Axios.get("/announcement", { params: { ...params } });
}
// 删除新闻
function helpdelete(params) {
  return Axios.delete("/announcement/" + params.id, params);
}
//隐藏/显示新闻
function helphidden(params) {
  return Axios.put("/announcement/" + params.id + "/hidden", params);
}
//获取新闻分类
function gethelptype() {
  return Axios.get("/announcement/type");
}
//添加新闻分类
function addhelptype(params) {
  return Axios.post("/announcement/type", params);
}
//修改新闻分类
function edithelptype(params) {
  return Axios.put("/announcement/type/" + params.id, params);
}
//删除新闻分类
function deletehelptype(params) {
  return Axios.delete("/announcement/type/" + params.id, { params: { ...params } });
}
//新闻详情
function helpdetial(params) {
  return Axios.get("/announcement/" + params.id, { params: { ...params } });
}
//添加新闻
function addhelp(params) {
  return Axios.post("/announcement", params);
}
//修改新闻
function edithelp(params) {
  return Axios.put("/announcement/" + params.id, params);
}
