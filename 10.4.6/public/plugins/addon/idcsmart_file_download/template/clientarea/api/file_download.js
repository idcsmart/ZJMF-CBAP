// 获取文件夹
function getFileFolder() {
  return Axios.get(`/file/folder`);
}
// 文件列表
function getFileList(params) {
  return Axios.get(`/file`, { params });
}
// 下载文件
function downloadFile(id) {
  return Axios.get(`/file/${id}/download`);
}
