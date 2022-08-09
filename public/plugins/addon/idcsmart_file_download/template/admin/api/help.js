//帮助文档列表
function helplist(params) {
  return Axios.get("/help", { params: { ...params } });
}
// 删除帮助文档
function helpdelete(params) {
  return Axios.delete("/help/" + params.id, params);
}
//隐藏/显示帮助文档
function helphidden(params) {
  return Axios.put("/help/" + params.id + "/hidden", params);
}
//获取帮助文档分类
function gethelptype() {
  return Axios.get("/help/type");
}
//添加帮助文档分类
function addhelptype(params) {
  return Axios.post("/help/type", params);
}
//修改帮助文档分类
function edithelptype(params) {
  return Axios.put("/help/type/" + params.id, params);
}
//-删除帮助文档分类
function deletehelptype(params) {
  return Axios.delete("/help/type/" + params.id, { params: { ...params } });
}
//帮助文档详情
function helpdetial(params) {
  return Axios.get("/help/" + params.id, { params: { ...params } });
}
//添加帮助文档
function addhelp(params) {
  return Axios.post("/help", params);
}
//修改帮助文档
function edithelp(params) {
  return Axios.put("/help/" + params.id, params);
}
//获取帮助中心首页数据
function helpindex(params) {
  return Axios.get("/help/index", { params: { ...params } });
}
//	保存帮助中心首页数据
function savehelpindex(params) {
  return Axios.put("/help/index", params);
}
//帮助文档列表
function helplistIndex(params) {
  return Axios.get("/help", { params: { ...params } });
}
