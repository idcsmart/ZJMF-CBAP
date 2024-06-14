/* 模板控制器 */
const BASE_URL = "";
/*
 * name: 接口名称
 * type: add | update
 * params
 */
// 列表
function getControllerList(name, params) {
  return Axios.get(`/${name}`, { params });
}
function addAndUpdateController(name, type, params) {
  if (type === "add") {
    return Axios.post(`${BASE_URL}/${name}`, params);
  } else if (type === "update") {
    return Axios.put(`${BASE_URL}/${name}/${params.id}`, params);
  }
}
// 删除
function delController(name, params) {
  return Axios.delete(`${BASE_URL}/${name}/${params.id}`);
}
// 修改状态
function changeBaseStatus(name, params) {
  return Axios.put(`${BASE_URL}/${name}/${params.id}/show`, params);
}
// 修改排序
function changeBaseOrder(name, params) {
  return Axios.put(`${BASE_URL}/${name}/order`, params);
}
//

/* 配置相关 */
function getControllerConfig(name) {
  return Axios.get(`${BASE_URL}/configuration/${name}`);
}
function saveControllerConfig(name, params) {
  return Axios.put(`${BASE_URL}/configuration/${name}`, params);
}

// 模板控制器Tab
function getTemplateControllerTab(params) {
  return Axios.get(`/template_tab`, { params });
}

// 官网主题卸载
function uninstallTheme(theme) {
  return Axios.delete(`/plugin/template/${theme}`);
}

// 获取主题最新版本
function getThemeLatestVersion(theme) {
  return Axios.get(`/app_market/template/${theme}/version`);
}

// 官网主题升级
function upgradeTheme(params) {
  return Axios.post(`/plugin/template/${params.theme}/upgrade`, params);
}
