(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementById('cloudList')
        Vue.prototype.lang = window.lang
        new Vue({
            created() {
                this.getDataCenter(this.id)
                this.getCloudList()
                this.getCommon()
            },
            mounted() {
                // 关闭loading
                // document.getElementById('mainLoading').style.display = 'none';
            },
            updated() {
                // 关闭loading
                document.getElementById('mainLoading').style.display = 'none';
                document.getElementsByClassName('template')[0].style.display = 'block'
            },
            components: {
                asideMenu,
                topMenu,
                pagination,
            },
            data() {
                return {
                    imgUrl: `${url}`,
                    id: 30,
                    menuActiveId: 1,
                    commonData:{},
                    menuList: [
                        {
                            id: 1,
                            text: lang.cloud_menu_1
                        },
                        {
                            id: 2,
                            text: lang.cloud_menu_2
                        },
                        {
                            id: 3,
                            text: lang.cloud_menu_3
                        },
                        {
                            id: 4,
                            text: lang.cloud_menu_4
                        },
                        {
                            id: 5,
                            text: lang.cloud_menu_5
                        },
                    ],
                    powerStatus: {
                        on: { text: "开机", icon: `${url}/img/cloud/on.png` },
                        off: { text: "关机", icon: `${url}/img/cloud/off.png` },
                        operating: { text: "操作中", icon: `${url}/img/cloud/operating.png` },
                        fault: { text: "故障", icon: `${url}/img/cloud/fault.png` },
                        Suspended: { text: "已暂停", icon: `${url}/img/cloud/suspended.png` }
                    },
                    status: {
                        Unpaid: { text: "未付款", color: "#F64E60", bgColor: "#FFE2E5" },
                        Pending: { text: "开通中", color: "#3699FF", bgColor: "#E1F0FF" },
                        Active: { text: "已开通", color: "#1BC5BD", bgColor: "#C9F7F5" },
                        // Suspended:{text:"已暂停",color:"#F0142F",bgColor:"#FFE2E5"},
                        Deleted: { text: "已删除", color: "#9696A3", bgColor: "#F2F2F7" },
                        Failed: { text: "开通失败", color: "#FFA800", bgColor: "#FFF4DE" }
                    },
                    // 数据中心
                    center: [],
                    // 产品列表
                    cloudData: [],
                    loading: false,
                    params: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: 'id',
                        sort: 'desc',
                        keywords: '',
                        data_center_id: ''
                    },
                    timerId: null
                }
            },
            filters: {
                formateTime(time) {
                    if (time && time !== 0) {
                        return formateDate(time * 1000)
                    } else {
                        return "--"
                    }
                }
            },
            methods: {
                getCommon(){
                    this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
                    document.title = this.commonData.website_name + '-产品列表'
                },
                // 切换分页
                sizeChange(e) {
                    this.params.limit = e
                    this.params.page = 1
                    this.getCloudList()
                },
                currentChange(e) {
                    this.params.page = e
                    this.getCloudList()
                },
                // 数据中心选择框变化时
                selectChange() {
                    this.params.page = 1
                    this.getCloudList()
                },
                inputChange() {
                    // this.getCloudList()
                    // if (this.timerId) {
                    //     clearTimeout(this.timerId)
                    // }
                    // this.timerId = setTimeout(() => {
                        this.params.page = 1
                        this.getCloudList()
                    // }, 500)
                },
                // 获取数据中心 
                getDataCenter(id) {
                    dataCenter(id).then(res => {
                        if (res.data.status === 200) {
                            const list = res.data.data.list
                            let centerData = []
                            list.map(item => {

                                let label = item.country + "-" + item.city
                                item.area.map(area => {
                                    let itemData = {
                                        id: '',
                                        label
                                    }
                                    itemData.id = area.id
                                    itemData.label = itemData.label + "-" + area.area
                                    centerData.push(itemData)
                                })
                            })
                            this.center = centerData
                        }
                    })
                },
                // 获取产品列表
                getCloudList() {
                    this.loading = true
                    cloudList(this.params).then(res => {
                        if (res.data.status === 200) {
                            let list = res.data.data.list
                            this.cloudData = list
                            this.params.total = res.data.data.count
                        }
                        this.loading = false
                    })
                },
            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
