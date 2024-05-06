
// 获取实名认证信息
function certificationInfo() {
    return Axios.get(`/certification/info`)
}

// 获取实名认证方式
function certificationPlugin() {
    return Axios.get(`/certification/plugin`)
}

//获取实名认证自定义字段    
function custom_fields(params) {
    return Axios.get(`/certification/custom_fields`, { params })
}

// 个人认证
function uploadPerson(params) {
    return Axios.post(`/certification/person`, params)
}

// 实名认证验证页面
function certificationAuth() {
    return Axios.get(`/certification/auth`)
}

// 获取系统状态
function certificationStatus() {
    return Axios.get(`certification/status`)
}
//企业认证
function uploadCompany(params) {
    return Axios.post(`/certification/company`, params)
}
