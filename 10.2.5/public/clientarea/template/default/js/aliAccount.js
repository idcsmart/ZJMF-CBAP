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
                window.addEventListener('scroll', this.computeScroll)
                // 关闭loading
                // document.getElementById('mainLoading').style.display = 'none';
            },
            updated() {
                // 关闭loading
                document.getElementById('mainLoading').style.display = 'none';
                document.getElementsByClassName('template')[0].style.display = 'block'
            },
            destroyed() {
                window.removeEventListener('scroll', this.computeScroll);
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
                    commonData: {},
                    isShowBackTop: false,
                    scrollY: 0,
                    isEnd: false,
                    isShowMore: false
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
                // 监测滚动
                computeScroll() {
                    let sizeWidth = document.documentElement.clientWidth;  // 初始宽宽度
                    if (sizeWidth > 750) {
                        return false
                    }

                    const body = document.getElementById('ali-home')
                    // 获取距离顶部的距离
                    let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
                    // 获取窗口的高度
                    let browserHeight = window.outerHeight;
                    // 滚动条高度
                    const scrollHeight = body.scrollHeight;
                    let scroll = scrollTop - this.scrollY
                    this.scrollY = scrollTop
                    // 判断返回顶部按钮是否显示
                    if (scrollTop > browserHeight) {
                        if (scroll < 0) {
                            this.isShowBackTop = true
                        } else {
                            this.isShowBackTop = false
                        }
                    } else {
                        this.isShowBackTop = false
                    }

                    // 判断是否到达底部
                    if ((browserHeight + scrollTop) >= scrollHeight) {
                        // 判断是否加载数据

                        // 充值记录
                        // 判断是否最后一页
                        // 是：显示到底了
                        // 不是：则加载下一页 显示加载中
                        const params = this.params
                        // 计算总页数
                        let allPage = params.total % params.limit == 0 ? (params.total / params.limit) : (Math.floor(params.total / params.limit) + 1)

                        if (params.page == allPage) {
                            // 已经是最后一页了
                            this.isEnd = true
                        } else {
                            // 显示加载中
                            this.isShowMore = true
                            // 页数加一
                            this.params.page = this.params.page + 1
                            // 获取订单记录 push到列表中
                            // 关闭加载中

                            this.isShowMore = true
                            rechargelist(this.params).then(res => {
                                if (res.data.status === 200) {
                                    let list = res.data.data.list
                                    this.params.total = res.data.data.count
                                    list.map(item => {
                                        this.dataList1.push(item)
                                    })

                                }
                                this.isShowMore = false
                            })
                        }
                    } else {
                        this.isEnd = false
                        this.isShowMore = false
                    }
                },
                // 返回顶部
                goBackTop() {
                    document.documentElement.scrollTop = document.body.scrollTop = 0;
                },
            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
