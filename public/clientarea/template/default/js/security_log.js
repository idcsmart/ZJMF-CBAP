(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('template')[0]
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                asideMenu,
                topMenu,
                pagination,
            },
            created() {
                this.getCommonData()
            },
            mounted() {
                this.getLogList()
            },
            updated() {
                // // 关闭loading
                // document.getElementById('mainLoading').style.display = 'none';
                // document.getElementsByClassName('template')[0].style.display = 'block'
            },
            destroyed() {

            },
            data() {
                return {
                    params: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: 'id',
                        sort: 'desc',
                        keywords: '',
                        type:"api",
                    },
                    commonData: {},
                    activeName: "3",
                    loading: false,
                    dataList: [],
  
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
                // 获取通用配置
                getCommonData() {
                    getCommon().then(res => {
                        if (res.data.status === 200) {
                            this.commonData = res.data.data
                            localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                            document.title = this.commonData.website_name + '-工单系统'
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
                inputChange() {
                    this.params.page = 1
                    this.getLogList()
                },
                handleClick(tap,event) {
                    if (this.activeName == 1) {
                        location.href = "security.html"
                    }
                    if (this.activeName == 2) {
                        location.href = "security_ssh.html"
                    }
                },

                getLogList() {
                    this.loading = true
                    logList(this.params).then(res => {
                        if (res.data.status === 200) {
                            this.dataList = res.data.data.list
                            this.params.total = res.data.data.count
                        }
                        this.loading = false
                    }).catch(err => {
                        this.loading = false
                    })
                },
            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
