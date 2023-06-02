(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementById('content')
        Vue.prototype.lang = window.lang
        Vue.prototype.moment = window.moment
        new Vue({
            components: {
                topMenu,
                asideMenu
            },
            created() {
                this.getCommontData()
                this.getData()
            },
            data() {
                return {
                    commontData: {},
                    params: {
                        type: "service",
                        pay_type: "",
                        system_type: "",
                        certify_type: "",
                        page: 1,
                        limit: 20,
                        orderby: "",
                        sort: "desc"
                    },
                    // 付费类型
                    payType: [
                        {
                            id: 1,
                            label: "全部",
                            value: ""
                        },
                        {
                            id: 2,
                            label: "免费",
                            value: "1"
                        },
                        {
                            id: 3,
                            label: "付费",
                            value: "0"
                        }
                    ],
                    // 系统类型
                    systemType: [
                        {
                            id: 1,
                            label: "全部",
                            value: ""
                        },
                        {
                            id: 2,
                            label: "魔方财务",
                            value: "finance"
                        },
                        {
                            id: 3,
                            label: "魔方云",
                            value: "cloud"
                        },
                        {
                            id: 4,
                            label: "DCIM",
                            value: "dcim"
                        },
                    ],
                    // 认证
                    certifyType: [
                        {
                            id: 1,
                            label: "全部",
                            value: ""
                        },
                        {
                            id: 2,
                            label: "官方认证",
                            value: "official"
                        },
                        {
                            id: 3,
                            label: "企业",
                            value: "company"
                        },
                        {
                            id: 4,
                            label: "个人",
                            value: "person"
                        },
                    ],
                    orderByType: [
                        {
                            id: 1,
                            label: "默认排序",
                            value: "id",
                        },
                        {
                            id: 2,
                            label: "更新时间",
                            value: "update_time",
                        },
                        {
                            id: 3,
                            label: "发布时间",
                            value: "create_time",
                        },
                        {
                            id: 4,
                            label: "优惠力度",
                            value: "discount",
                        },
                        {
                            id: 5,
                            label: "商品评分",
                            value: "score",
                        }
                    ],
                    dataList: [],
                    loading: false,
                }
            },
            filters: {

            },
            methods: {
                getCommontData() {
                    if (localStorage.getItem('common_set_before')) {
                        this.commontData = JSON.parse(localStorage.getItem('common_set_before'))
                    } else {
                        getCommon().then(res => {
                            if (res.data.status == 200) {
                                this.commontData = res.data.data
                                localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                            }
                        })
                    }
                },
                getData() {
                    this.loading = true
                    appList(this.params).then(res => {
                        if (res.data.status == 200) {
                            this.dataList = res.data.data.list
                        }
                        this.loading = false
                    }).catch((error) => {
                        this.loading = false
                    })
                },
                // 顶部筛选
                payClick(e) {
                    this.params.pay_type = e
                    this.getData()
                },
                certifyClick(e) {
                    this.params.certify_type = e
                    this.getData()
                },
                systemClick(e) {
                    this.params.system_type = e
                    this.getData()
                },
                // 应用点击
                itemClick(id, clientId) {
                    location.href = `shop_detail.html?id=${id}&clientId=${clientId}`
                },
                orderByClick(e) {
                    const { orderby, sort } = this.params
                    if (orderby == e) {
                        if (sort == 'desc') {
                            this.params.orderby = ""
                            this.params.sort = ""
                        }
                        else if (sort == 'asc') {
                            this.params.orderby = e
                            this.params.sort = "desc"
                        } else {
                            this.params.orderby = e
                            this.params.sort = "asc"
                        }
                    } else {
                        this.params.orderby = e
                        this.params.sort = "asc"
                    }
                    this.getData()
                }

            },

        }).$mount(template)

        const mainLoading = document.getElementById('mainLoading')
        setTimeout(() => {
            mainLoading && (mainLoading.style.display = 'none')
        }, 200)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
