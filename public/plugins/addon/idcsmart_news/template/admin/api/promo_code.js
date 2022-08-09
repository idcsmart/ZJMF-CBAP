//优惠码列表
function promocodelist(params) {
  return Axios.get("/promo_code", { params: { ...params } });
}
//获取优惠码
function filedetial(params) {
  return Axios.get("/promo_code/" + params.id, { params: { ...params } });
}
// 添加优惠码
function addpromocode(params) {
  return Axios.post("/promo_code", params);
}
//删除优惠码
function promocodedelete(params) {
  return Axios.delete("/promo_code/" + params.id, params);
}
//设置优惠码
function editpromocode(params) {
  return Axios.put("/promo_code/" + params.id, params);
}
//启用/禁用优惠码
function promocodehidden(params) {
  return Axios.put("/promo_code/" + params.id + "/status", params);
}
//获取随机优惠码
function generatecode(params) {
  return Axios.get("/promo_code/generate", { params: { ...params } });
}
//数据中心
function datalist(params) {
  return Axios.get("/idcsmart_cloud/data_center", { params: { ...params } });
}
//获取商品所有配置项
function productalllist(params) {
  return Axios.get("/product/" + params.id + "/all_config_option", {
    params: { ...params },
  });
}
//	商品列表
function productlist(params) {
  return Axios.get("/product", params);
}
