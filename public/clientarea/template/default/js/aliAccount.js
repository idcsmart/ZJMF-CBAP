(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementById('account')
        Vue.prototype.lang = window.lang
        new Vue({
            created() {
                this.getList()
                this.getCommon()
            },
            mounted() {
                // 关闭loading
                document.getElementById('mainLoading').style.display = 'none';
            },
            components: {
                pagination,
                aliAsideMenu,
                topMenu,
            },
            data() {
                return {
                    isShowList: false,
                    loading1: false,
                    dataList1: [],
                    timerId: null,
                    params: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: 'id',
                        sort: 'desc',
                        keywords: '',
                    },
                    commonData: {}
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
                // 获取通用信息
                getCommon() {
                    common().then(res => {
                        if (res.data.status === 200) {
                            this.commonData = res.data.data
                            document.title = this.commonData.website_name + '-充值记录'
                        }
                    })
                },
                // 返回阿里首页
                goBack() {
                    location.href = 'index.html'
                },
                // 获取充值记录列表
                getList() {
                    this.loading1 = true
                    rechargelist(this.params).then(res => {
                        if (res.data.status === 200) {
                            this.dataList1 = res.data.data.list
                            this.params.total = res.data.data.count
                        }
                        this.loading1 = false
                    })
                },
                sizeChange(e) {
                    this.params.limit = e
                    this.params.page = 1
                    this.getList()
                },
                currentChange(e) {
                    this.params.page = e
                    this.getList()
                },
                // 搜索框
                inputChange() {
                    // this.getCloudList()
                    this.params.page = 1
                    this.getList()
                    // if (this.timerId) {
                    //     clearTimeout(this.timerId)
                    // }
                    // this.timerId = setTimeout(() => {
                    //     this.params.page = 1
                    //     this.getList()
                    // }, 500)
                },
            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
