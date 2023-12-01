// 获取文件夹
function getFileFolder() {
  return Axios.get(`/file/folder`)
}
// 文件列表
function getFileList(params) {
  return Axios.get(`/file`, { params })
}
// 下载文件
function downloadFile(params) {
  return Axios.get(`/file/${params.id}/download`, {
    responseType: "blob",
    timeout: 1000 * 60 * 30,
    params: params
  })
}