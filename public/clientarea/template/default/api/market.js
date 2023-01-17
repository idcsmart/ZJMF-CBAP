
// 应用管理开始
/**
 * @获取应用列表
 */
function queryAppApi(params) {
  return Axios.get(`/app_market/app`, { params });
}
/**
 * @更改应用状态
 */
function changeAppStatusApi(params) {
  return Axios.put(`/app_market/app/${params.id}/retired`, params);
}
/**
 * @删除应用 
 */
function delAppApi(params) {
  return Axios.delete(`/app_market/app/${params.id}`, params);
}

// 服务管理

/**
 * @获取服务列表
 */
function queryServeApi(params) {
  return Axios.get(`/app_market/service`, { params });
}
/**
 * @更改服务状态
 */
function changeServeStatusApi(params) {
  return Axios.put(`/app_market/service/${params.id}/retired`, params);
}
/**
 * @删除服务
 */
function delServeApi(params) {
  return Axios.delete(`/app_market/service/${params.id}`, params);
}



// 订单管理开始
/**
 * @获取订单列表
 */
function queryOrderApi(params) {
  return Axios.get(`/app_market/order`, { params });
}



// 提现管理开始
/**
 * @获取提现列表破
 */
function queryWithdrawApi(params) {
  return Axios.get(`/withdraw`, { params });
}

/**
 * @获取开发者收入
 */
function queryIncomeApi(params) {
  return Axios.get(`/app_market/developer/income`, { params });
}





// 促销活动开始
/**
 * @获取活动列表
 */
function queryActivityApi(params) {
  return Axios.get(`/app_market/activity`, { params });
}

// 投诉列表开始
/**
 * @获取投诉举报列表
 */
function queryComplaintApi(params) {
  return Axios.get(`/app_market/market/complaint`, { params });
}

/**
 * @取消投诉
 */
function delComplaintApi(params) {
  return Axios.delete(`/app_market/complaint/${params.id}`, { params });
}
/**
 * @投诉详情
 */
function complaintDetailApi(params) {
  return Axios.get(`/app_market/complaint/${params.id}`, { params });
}
/**
 * @回复投诉
 */
function changeComplaintApi(params) {
  return Axios.post(`/app_market/complaint/${params.id}/reply`, params);
}


// 信息设置

/**
 * @获取开发者详情
 */
function queryMarketDetailApi(params) {
  return Axios.get(`/app_market/developer`, { params });
}

/**
 * @修改开发者
 */
function changeMarketApi(params) {
  return Axios.put(`/app_market/developer`, params);
}

/**
 * @申请开发者
 */
function creaetDevApi(params) {
  return Axios.post(`/app_market/developer`, params)
}

/**
 * @创建应用
 */
function creaetAppApi(params) {
  return Axios.post('/app_market/app', params)
}

/**
 * @获取应用详情
 */
function queryAppDetailApi(params) {
  return Axios.get(`/app_market/app/${params.id}`, params)
}

/**
 * @修改应用详情
 */
function changeAppApi(params) {
  return Axios.put(`/app_market/app/${params.id}`, params)
}

/**
 * @获取授权列表
 */
function queryAuthorizetApi(params) {
  return Axios.get(`/app_market/market/authorize`, { params });
}

/**
 * @获取我的应用
 */
function queryMyAppApi(params) {
  return Axios.get(`/app_market/market/my_app`, { params });
}

/**
 * @下载安装包
 */
function downloadMyAppApi(params) {
  return Axios.get(`/app_market/market/app/${params.id}/download`, { responseType: 'blob' });
}
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
    responseType: "blob"
  })
}


/**
 * @获取我的服务
 */
function queryMyServeApi(params) {
  return Axios.get(`/app_market/market/my_service`, { params });
}

/**
 * @获取用户授权详情
 */
function queryAuthorizeDetailApi(params) {
  return Axios.get(`/app_market/market/authorize/${params.id}`, { params });
}

/**
 * @获取文件列表
 */
function queryAllFileApi(params) {
  return Axios.get(`/file/folder`, { params });
}

function createServeApi(params) {
  return Axios.post('/app_market/service', params)
}

/**
 * @获取服务详情
 */
function queryServeDetailApi(params) {
  return Axios.get(`app_market/service/${params.id}`, { params })
}

/**
 * @修改服务
 */
function changeServeApi(params) {
  return Axios.put(`app_market/service/${params.id}`, params)
}

// 获取商店设置
function appConfiguration() {
  return Axios.get(`/app_market/configuration`)
}

// 提现规则详情
function withdrawRule(params) {
  return Axios.get(`/withdraw/rule/credit`, { params })
}
// 开发者提现
function withdraw(params) {
  return Axios.post(`/app_market/developer/withdraw`, params)
}
// 获取未参加活动的应用
function activityApp(params) {
  return Axios.get(`/app_market/activity/app`, { params })
}

// 新增活动
function addActivity(params) {
  return Axios.post(`/app_market/activity`, params)
}
// 活动详情
function activityDetail(id) {
  return Axios.get(`/app_market/activity/${id}`)
}
// 删除活动
function deleteActive(id) {
  return Axios.delete(`/app_market/activity/${id}`);
}
//修改活动
function editActive(params) {
  return Axios.put(`/app_market/activity/${params.id}`, params);
}

// 删除订单
function deleteOrder(id) {
  return Axios.delete(`/order/${id}`);
}