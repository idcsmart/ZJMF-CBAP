/* 意见反馈 */
function getFeedback(params) {
  return Axios.get(`/feedback`, { params });
}
// 意见反馈类型
function getFeedbackType(params) {
  return Axios.get(`/feedback/type`, { params });
}
function addAndUpdateFeedbackType(type, params) {
  if (type === "add") {
    return Axios.post(`/feedback/type`, params);
  } else if (type === "update") {
    return Axios.put(`/feedback/type/${params.id}`, params);
  }
}
// 删除
function delFeedbackType(params) {
  return Axios.delete(`/feedback/type/${params.id}`);
}
//下载文件
function downloadfile(params) {
  return Axios.get("/file/" + params.id + "/download", {
    params: { ...params },
    responseType: "blob",
  });
}

/* 方案咨询 */
function getConsult(params) {
  return Axios.get(`/consult`, { params });
}

/* 信息配置 */
function getConfigInfo() {
  return Axios.get(`/configuration/info`);
}
function saveConfigInfo(params) {
  return Axios.put(`/configuration/info`, params);
}
/* 友情链接 - friendly_link
   荣誉资质 - honor
   合作伙伴 - partner
*/
function getComInfo(name) {
  return Axios.get(`/${name}`);
}
function addAndUpdateComInfo(name, type, params) {
  if (type === "add") {
    return Axios.post(`/${name}`, params);
  } else if (type === "update") {
    return Axios.put(`/${name}/${params.id}`, params);
  }
}
// 删除
function delComInfo(name, id) {
  return Axios.delete(`/${name}/${id}`);
}

// 官网导航列表
function getNavList() {
  return Axios.get(`/web_nav`);
}

// 新增官网导航
function addNav(params) {
  return Axios.post(`/web_nav`, params);
}

// 修改官网导航
function updateNav(params) {
  return Axios.put(`/web_nav/${params.id}`, params);
}

// 删除官网导航
function delNav(id) {
  return Axios.delete(`/web_nav/${id}`);
}

// 修改官网导航是否展示
function updateNavShow(params) {
  return Axios.put(`/web_nav/${params.id}/status`, params);
}

// 拖动排序
function sortNav(params) {
  return Axios.put(`/web_nav/${params.id}/drag`, params);
}
