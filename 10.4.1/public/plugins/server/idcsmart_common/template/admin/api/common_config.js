/* 	通用商品 */
// 	商品基础信息
const base = "idcsmart_common";
function getCountry() {
  return Axios.get(`/country`);
}
function getProductInfo(product_id) {
  return Axios.get(`/${base}/product/${product_id}`);
}
function saveProductInfo(params) {
  return Axios.post(`/${base}/product/${params.product_id}`, params);
}

// 周期
function getProCycle(params) {
  return Axios.post(
    `/${base}/product/${params.product_id}/custom_cycle/${params.id}`
  );
}
function addAndUpdateProCycle(type, params) {
  if (type === "add") {
    return Axios.post(
      `/${base}/product/${params.product_id}/custom_cycle`,
      params
    );
  } else if (type === "update") {
    return Axios.put(
      `/${base}/product/${params.product_id}/custom_cycle/${params.id}`,
      params
    );
  }
}
function deleteProCycle(params) {
  return Axios.delete(
    `/${base}/product/${params.product_id}/custom_cycle/${params.id}`
  );
}

// 获取周期比例
function getDurationRatio(params) {
  return Axios.get(`/${base}/duration_ratio`, { params });
}
function saveDurationRatio(params) {
  return Axios.put(`/${base}/duration_ratio`, params);
}
// 周期比例填充
function fillDurationRatio(params) {
  return Axios.post(`/${base}/duration_ratio/fill`, params);
}

// 配置选项
function getConfigoption(params) {
  return Axios.get(`/${base}/product/${params.product_id}/configoption`);
}
function getConfigoptionDetail(params) {
  return Axios.get(
    `/${base}/product/${params.product_id}/configoption/${params.id}`
  );
}
function addAndUpdateConfigoption(type, params) {
  if (type === "add") {
    return Axios.post(
      `/${base}/product/${params.product_id}/configoption`,
      params
    );
  } else if (type === "update") {
    return Axios.put(
      `/${base}/product/${params.product_id}/configoption/${params.id}`,
      params
    );
  }
}
function deleteConfigoption(params) {
  return Axios.delete(
    `/${base}/product/${params.product_id}/configoption/${params.id}`
  );
}
// 显示/隐藏
function changeConfigoption(params) {
  return Axios.put(
    `/${base}/product/${params.product_id}/configoption/${params.id}/hidden`,
    params
  );
}

// 配置子项
function getConfigSubDetail(params) {
  return Axios.get(
    `/${base}/configoption/${params.product_id}/sub/${params.id}`
  );
}
function addAndUpdateConfigSub(type, params) {
  if (type === "add") {
    return Axios.post(`/${base}/configoption/${params.product_id}/sub`, params);
  } else if (type === "update") {
    return Axios.put(
      `/${base}/configoption/${params.product_id}/sub/${params.id}`,
      params
    );
  }
}
function deleteConfigSub(params) {
  return Axios.delete(
    `/${base}/configoption/${params.configoption_id}/sub/${params.id}`,
    params
  );
}

/* 内页模块相关 */
// 产品配置信息
function getProInfo(params) {
  return Axios.get(`/${base}/host/${params.id}`);
}
function saveProInfo(params) {
  return Axios.put(`/${base}/host/${params.id}`, params);
}

/* 子接口列表 */
function getChildInterface(params) {
  return Axios.get(`/${base}/server`, { params });
}

/* 通用模块自定义参数 */
function getChildModuleParams(params) {
  return Axios.get(
    `/${base}/product/${params.product_id}/module/${params.server_id}`
  );
}
// 配置项拖动排序
function dragOrderConfig(params) {
  return Axios.post(
    `/${base}/product/${params.product_id}/configoption/order`,
    params
  );
}

// 配置子项拖动排序
function dragSubOrderConfig(params) {
  return Axios.post(
    `/${base}/configoption/${params.configoption_id}/sub/order`,
    params
  );
}
