(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('search1559')[0]
        Vue.prototype.lang = window.lang
        Vue.prototype.moment = window.moment
        new Vue({
            data() {
                return {
                    // 分页相关
                    params: {
                        type: 'ip',
                        keywords: '',
                        page: 1,
                        limit: 20,
                        orderby: 'id',
                        sort: 'desc'
                    },
                    total: 0,
                    pageSizeOptions: [20, 50, 100],
                    state: {
                        Pending: { text: "待开通" },
                        Active: { text: "已激活" },
                        Suspended: { text: "已暂停" },
                        Cancelled: { text: "被取消" },
                        Fraud: { text: "有欺诈" },
                        Completed: { text: "已完成" },
                        Deleted: { text: "被删除" },
                        Failed: { text: "开通失败" },
                    },
                    // 是否显示 次数用尽
                    isTimesOver: false,
                    // 是否显示 无数据返回
                    isResult: false,
                    resultText: "",
                    // 是否展示user数据返回
                    isShowUser: false,
                    // 是否显示表格
                    isShowTable: false,
                    // 返回数据
                    userData: {},
                    // 获取到的 table 数据
                    data: [],
                    // 展示的table数据
                    showData: [],
                    columns: [
                        {
                            colKey: 'dedicatedip',
                            title: 'IP地址',
                            width: 150,
                            ellipsis: true
                        },
                        {
                            colKey: 'assignedips',
                            title: '所有IP',
                            minWidth: 400,
                            ellipsis: true
                        },
                        {
                            colKey: 'domainstatus',
                            title: '状态',
                            width: 150,
                            ellipsis: true
                        },
                        {
                            colKey: 'regdate',
                            title: '开通时间',
                            width: 200,
                            ellipsis: true
                        },
                        {
                            colKey: 'nextduedate',
                            title: '到期时间',
                            width: 200,
                            ellipsis: true
                        },
                    ],
                    options: [
                        {
                            id: 1,
                            label: 'IP',
                            value: 'ip'
                        },
                        {
                            id: 2,
                            label: '邮箱',
                            value: 'email'
                        },
                        {
                            id: 3,
                            label: '手机',
                            value: 'phone'
                        },
                        {
                            id:4,
                            label: 'QQ',
                            value: 'qq',
                        }
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

            },
            methods: {
                search() {
                    if (!this.params.type) {
                        this.$message.error("请选择类型！")
                        return false
                    }
                    if (!this.params.keywords) {
                        this.$message.error("请输入查询条件!")
                        return false
                    }
                    dcimSearch(this.params).then(res => {
                        if (res.data.status === 200) {
                            // ip搜索
                            if (this.params.type === 'ip') {
                                if (res.data.data.user && res.data.data.user.length === 0) {
                                    this.resultText = "无符合条件的结果！"
                                    this.isResult = true
                                    this.isTimesOver = false
                                    this.isShowUser = false
                                    this.isShowTable = false
                                } else {
                                    // IP搜索有返回值
                                    this.userData = res.data.data.user
                                    this.isResult = false
                                    this.isTimesOver = false
                                    this.isShowUser = true
                                    this.isShowTable = false
                                }
                                console.log(res.data.data.user);
                            } else {
                                // 手机号或邮箱 或 qq搜索
                                if (res.data.data.host && res.data.data.host.length === 0) {
                                    // 手机号或邮箱搜索 无返回值
                                    this.resultText = "无符合条件的结果！"
                                    this.isResult = true
                                    this.isTimesOver = false
                                    this.isShowUser = false
                                    this.isShowTable = false
                                } else {
                                    // 邮箱、手机号 搜索有返回值
                                    this.data = res.data.data.host
                                    this.showData = this.data.slice(0, this.params.limit)
                                    this.total = this.data.length
                                    this.isResult = false
                                    this.isTimesOver = false
                                    this.isShowUser = false
                                    this.isShowTable = true
                                }
                            }
                        }
                    }).catch((error) => {
                        if (error.data.status === 400) {
                            this.isResult = false
                            this.isTimesOver = true
                            this.isShowUser = false
                            this.isShowTable = false
                            this.resultText = error.data.msg
                        } else {
                            this.isResult = false
                            this.isTimesOver = false
                            this.isShowUser = false
                            this.isShowTable = false
                            this.$message.error(error.data.msg)
                        }
                    })
                },
                // 底部分页 页面跳转事件
                changePage(e) {
                    this.params.page = e.current
                    this.params.limit = e.pageSize
                    this.showData = this.data.slice((e.current - 1) * e.pageSize, e.current * e.pageSize)
                },
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
