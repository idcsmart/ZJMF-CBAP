(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('withdrawal')[0]
        Vue.prototype.lang = window.lang
        Vue.prototype.moment = window.moment
        new Vue({
            data() {
                return {
                    // 分页相关
                    params: {
                        keywords: '',
                        page: 1,
                        limit: 20,
                        orderby: 'id',
                        sort: 'desc'
                    },
                    total: 0,
                    pageSizeOptions: [20, 50, 100],
                    // 表格相关
                    data: [],
                    columns: [
                        {
                            colKey: 'id',
                            title: 'ID',
                            width: 125,
                        },
                    ],
                    loading: false,
                    maxHeight: '',
                }
            },
            computed: {

            },
            mounted() {
                this.maxHeight = document.getElementById('content').clientHeight - 270
                let timer = null
                window.onresize = () => {
                    if (timer) {
                        return
                    }
                    timer = setTimeout(() => {
                        this.maxHeight = document.getElementById('content').clientHeight - 270
                        clearTimeout(timer)
                        timer = null
                    }, 300)
                }
            },
            created() {
                // 获取提现列表
                this.doGetWithdrawList()
            },
            methods: {
                // 搜索框 搜索
                seacrh() {
                    this.params.page = 1
                    // 重新拉取申请列表
                    this.doGetWithdrawList()
                },
                // 清空搜索框
                clearKey() {
                    this.params.keywords = ''
                    this.params.page = 1
                    // 重新拉取申请列表
                    this.doGetWithdrawList()
                },
                // 底部分页 页面跳转事件
                changePage() {
                    this.doGetWithdrawList()
                },
                // 获取申请列表
                doGetWithdrawList() {
                    this.loading = true
                    getWithdrawList(this.params).then(res => {
                        if (res.data.status === 200) {
                            this.total = res.data.data.count
                            this.data = res.data.data.list
                        }
                        this.loading = false
                    }).catch(error => {
                        this.loading = false
                    })
                }
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
