(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('recording1559')[0]
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
                        sort: 'desc',
                        start_time:'',
                        end_time:''
                    },
                    total: 0,
                    pageSizeOptions: [20, 50, 100],
                    // 表格相关
                    data: [],
                    columns: [
                        {
                            colKey: 'description',
                            title: '详情',
                            minWidth: 400,
                            ellipsis: true
                        },
                        {
                            colKey: 'create_time',
                            title: '时间',
                            width: 200,
                        },
                        {
                            colKey: 'admin',
                            title: '查询人',
                            width: 125,
                        },
                        {
                            colKey: 'ip',
                            title: 'IP',
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
                this.maxHeight = document.getElementById('content').clientHeight - 170
                let timer = null
                window.onresize = () => {
                    if (timer) {
                        return
                    }
                    timer = setTimeout(() => {
                        this.maxHeight = document.getElementById('content').clientHeight - 170
                        clearTimeout(timer)
                        timer = null
                    }, 300)
                }
            },
            created() {
                // 获取查询记录列表
                this.getSeacrhLog()
            },
            methods: {
                // 底部分页 页面跳转事件
                changePage(e) {
                    this.params.page = e.current
                    this.params.limit = e.pageSize
                    this.getSeacrhLog()
                },
                // 获取查询记录列表
                getSeacrhLog() {
                    this.loading = true
                    seacrhLog(this.params).then(res => {
                        if (res.data.status === 200) {
                            this.loading = false
                            this.data = res.data.data.list
                            this.total = res.data.data.count
                        }

                    }).catch(error => {
                        this.loading = false
                        this.$message.error(error.data.msg)
                    })
                },
                handleChange(v) {
                    console.log('value:', v);
                  },
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
