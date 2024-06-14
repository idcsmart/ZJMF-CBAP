//获取文件夹
function folderlist(params) {
  return Axios.get("/file/folder", { params: { ...params } });
}
// 添加文件夹
function addfolder(params) {
  return Axios.post("/file/folder", params);
}
//删除文件夹
function folderdelete(params) {
  return Axios.delete("/file/folder/" + params.id, params);
}
//修改文件夹
function editfolder(params) {
  return Axios.put("/file/folder/" + params.id, params);
}
//文件列表
function filelist(params) {
  return Axios.get("/file", { params: { ...params } });
}
//文件详情
function filedetial(params) {
  return Axios.get("/file/" + params.id, { params: { ...params } });
}
//上传文件
function unloadfile(params) {
  return Axios.post("/file", params);
}
//编辑文件
function editfile(params) {
  return Axios.put("/file/" + params.id, params);
}
//-删除文件
function deletefile(params) {
  return Axios.delete("/file/" + params.id, { params: { ...params } });
}
//隐藏/显示文件
function filehidden(params) {
  return Axios.put("/file/" + params.id + "/hidden", params);
}
//移动文件
function movefile(params) {
  return Axios.put("/file/" + params.id + "/move", params);
}
//下载文件
function downloadfile(params) {
  return Axios.get("/file/" + params.id + "/download");
}
//	商品列表
function productlist(params) {
  return Axios.get("/product", params);
}

function checkDef(params) {
  return Axios.put(`/file/folder/${params.id}/default`);
}

// 文件排序

function fileOrder(params) {
  return Axios.put(`/file/order`, params);
}
