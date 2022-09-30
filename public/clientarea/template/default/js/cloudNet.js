(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('template')[0]
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                asideMenu,
                topMenu,
                cloudTop,
                pagination,
            },
            created() {
                // 获取产品id
                this.id = location.href.split('?')[1].split('=')[1]
                this.getCommonData()
                this.getIpList()
            },
            data() {
                return {
                    commonData: {},
                    params: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: 'id',
                        sort: 'desc',
                        keywords: '',
                    },
                    dataList: [],
                    loading: false,
                    id: null
                }
            },
            filters: {
                formateTime(time) {
                    if (time && time !== 0) {
                        return formateDate(time * 1000)
                    } else {
                        return "--"
                    }
                },

            },
            methods: {
                // 获取通用配置
                getCommonData() {
                    getCommon().then(res => {
                        if (res.data.status === 200) {
                            this.commonData = res.data.data
                            localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                        }
                    })
                },
                // 每页展示数改变
                sizeChange(e) {
                    this.params.limit = e
                    this.params.page = 1
                    // 获取列表
                },
                // 当前页改变
                currentChange(e) {
                    this.params.page = e
                },
                // 获取ip列表
                getIpList() {
                    const params = {
                        id: this.id,
                        ...this.params
                    }
                    this.loading = true
                    ipList(params).then(res => {
                        if (res.data.status === 200) {
                            this.params.total = res.data.data.count
                            this.dataList = res.data.data.list
                        }
                        this.loading = false
                    })
                },

            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
