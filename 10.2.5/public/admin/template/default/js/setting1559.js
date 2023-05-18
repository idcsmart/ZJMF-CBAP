(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('setting1559')[0]
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
                    editId: 0,
                    editNum: 0,
                    inputWidth: 80,
                    columns: [
                        {
                            colKey: 'name',
                            title: '管理员',
                            minWidth: 400,
                            ellipsis: true
                        },
                        {
                            colKey: 'search_limit',
                            title: '每月次数',
                            minWidth: 400,
                            ellipsis: true
                        },
                        {
                            colKey: 'search_used',
                            title: '当月已用',
                            width: 125,
                            ellipsis: true
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
                this.getSettingList()
            },
            methods: {
                // 底部分页 页面跳转事件
                changePage(e) {
                    this.params.page = e.current
                    this.params.limit = e.pageSize
                    this.getSettingList()
                },
                // 获取查询记录列表
                getSettingList() {
                    this.loading = true
                    settingList(this.params).then(res => {
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
                // 编辑次数
                edit(row) {
                    this.editId = row.id
                    this.editNum = row.search_limit
                    let width = row.search_limit.toString().length * 10
                    if (width < 40) {
                        this.inputWidth = 40
                    } else {
                        this.inputWidth = width
                    }
                },
                // 提交次数
                sub() {
                    const params = {
                        id: this.editId,
                        search_limit: this.editNum
                    }
                    setting(params).then(res => {
                        if (res.data.status === 200) {
                            this.editId = null
                            this.getSettingList()
                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                    })
                },
                inputChange(e) {
                    // placeholder的宽度
                    // 输入内容的宽度
                    let n = e.toString().length * 10
                    if (n < 40) {
                        this.inputWidth = 40
                    } else {
                        this.inputWidth = n
                    }

                }
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
