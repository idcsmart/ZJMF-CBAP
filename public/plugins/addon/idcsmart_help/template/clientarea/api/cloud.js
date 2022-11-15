// 产品接口
// 获取数据中心
function dataCenter(id) {
    return Axios.get(`/product/${id}/idcsmart_cloud/data_center`);
}
/* 产品列表 */
function cloudList(params) {
    return Axios.get(`/idcsmart_cloud`, { params });
}
