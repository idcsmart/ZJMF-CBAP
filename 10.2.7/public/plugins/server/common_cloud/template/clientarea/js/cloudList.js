
const template = document.getElementById('cloudList')
Vue.prototype.lang = window.lang
new Vue({
    created() {
        this.analysisUrl()
        this.getDataCenter(this.id)
        this.getCloudList()
        this.getCommon()
    },
    components: {
        asideMenu,
        topMenu,
        pagination,
    },
    data() {
        return {
            imgUrl: `${url}`,
            id: 0,
            menuActiveId: 1,
            hostData: {},
            commonData: {},
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
                suspend: { text: "已暂停", icon: `${url}/img/cloud/suspended.png` } 
            },
            status: {
                Unpaid: { text: "未付款", color: "#F64E60", bgColor: "#FFE2E5" },
                Pending: { text: "开通中", color: "#3699FF", bgColor: "#E1F0FF" },
                Active: { text: "正常", color: "#1BC5BD", bgColor: "#C9F7F5" },
                Suspended: { text: "已暂停", color: "#F99600", bgColor: "#FFF4DE" },
                Deleted: { text: "已删除", color: "#9696A3", bgColor: "#F2F2F7" },
                Failed: { text: "开通中", color: "#3699FF", bgColor: "#E1F0FF" }
            },
            statusSelect: [
                {
                    id: 1,
                    status: 'Unpaid',
                    label: "未付款"
                },
                {
                    id: 2,
                    status: 'Pending',
                    label: "开通中"
                },
                {
                    id: 3,
                    status: 'Active',
                    label: "正常"
                },
                {
                    id: 4,
                    status: 'Suspended',
                    label: "已暂停"
                },
                {
                    id: 5,
                    status: 'Deleted',
                    label: "已删除"
                },
            ],
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
                data_center_id: '',
                status: '',
                m:null
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
        analysisUrl() {
            let url = window.location.href
            let getqyinfo = url.split('?')[1]
            let getqys = new URLSearchParams('?' + getqyinfo)
            let m = getqys.get('m')
            this.params.m = m
        },
        getCommon() {
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
            this.params.page = 1
            this.getCloudList()
        },
        centerSelectChange() {
            this.params.page = 1
            this.getCloudList()
        },
        statusSelectChange() {
            this.params.page = 1
            this.getCloudList()
        },
        // 获取数据中心 
        getDataCenter(id) {
            dataCenter(id).then(res => {
                if (res.data.status === 200) {
                    const list = res.data.data.list
                    let centerData = []
                    list && list.map(item => {
                        let label = item.name_zh
                        item.city.map(city => {
                            let itemData = {
                                id: '',
                                label,
                                iso: item.iso
                            }
                            itemData.id = city.id
                            itemData.label = itemData.label + "-" + city.name
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
        // 跳转产品详情
        toDetail(row) {
            location.href = `productdetail.htm?id=${row.id}`
        },
        // 跳转订购页
        toOrder() {
            const id = this.id
            location.href = `order.htm?id=${id}`
        },
    },

}).$mount(template)

