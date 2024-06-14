/* 通用接口API */

// 获取国家列表
function getCountry(params) {
  return Axios.get(`/country`, params);
}
// 获取支付接口
function getPayList() {
  return Axios.get("/gateway");
}
// 获取公共配置
function getCommon() {
  return Axios.get("/common");
}
// 获取系统配置
function getSystem() {
  return Axios.get("/configuration/system");
}
// 获取登录信息
function getLoginInfo() {
  return Axios.get("/login");
}
// 获取验证码
function getCaptcha() {
  return Axios.get("/captcha");
}
// 登录
function logIn(params) {
  return Axios.post("/login", params);
}
// 退出登录
function logout() {
  return Axios.post("/logout");
}
// 获取权限
function getAuthRole() {
  return Axios.get("/admin/auth");
}
// 全局搜索
function globalSearch(keywords) {
  return Axios.get(`/global_search?keywords=${keywords}`);
}
// 获取导航
function getMenus() {
  return Axios.get("/menu");
}

// 修改管理员密码
function editPass(params) {
  return Axios.put(`admin/password/update`, params);
}

// 生成token
function setToken() {
  return Axios.post(`/app_market/set_token`);
}

// 取消百度订单
function cancelOrder(params) {
  return Axios.post(`/baidu_cloud/order/cancel`, params);
}

// 获取已购买应用最新版本
function getActiveVersion() {
  return Axios.get("/app_market/app/version");
}

/* 获取系统版本 */
function version() {
  return Axios.get(`/system/version`);
}
// 系统设置
function getSystemOpt() {
  return Axios.get("/configuration/system");
}
function updateSystemOpt(params) {
  return Axios.put("/configuration/system", params);
}

/* 商品 */
// 获取商品列表
function getComProduct(params) {
  return Axios.get(`/product`, { params });
}
// 获取商品一级分组
function getFirstGroup() {
  return Axios.get(`/product/group/first`);
}
// 获取商品二级分组
function getSecondGroup() {
  return Axios.get(`/product/group/second`);
}
// 用户管理-用户列表
function getComClientList(params) {
  return Axios.get(`/client`, { params });
}
// 销售绑定用户列表
function saleClientList(params) {
  return Axios.get("/sale/client", { params });
}

function getUserInfo(params) {
  return Axios.get(`/client/${params.id}`);
}

function getAddon(params) {
  return Axios.get(`/active_plugin`, { params });
}
/* 全局搜索 */
function getGlobalSearch() {
  return Axios.get("common_search_table");
}
function sendGlobalSearch(params) {
  return Axios.get("/common_search", { params });
}
//  获取当前管理员信息，用来获取是否设置了操作密码
function getAdminInfo() {
  return Axios.get(`/login_info`);
}

// 修改管理员操作密码
function changePassword(params) {
  return Axios.put(`/admin/operate_password`, params);
}
