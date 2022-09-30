(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('template')[0]
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                asideMenu,
                topMenu,
            },
            created() {
                this.getCommonData()
                this.getHelpIndex()
            },
            mounted() {

            },
            updated() {
                // // 关闭loading
                document.getElementById('mainLoading').style.display = 'none';
                document.getElementsByClassName('template')[0].style.display = 'block'
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
                    },
                    commonData: {},
                    helpIndexList: []
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
                // 每页展示数改变
                sizeChange(e) {
                    this.params.limit = e
                    this.params.page = 1
                    // 获取列表
                    this.getHelpIndex()
                },
                // 当前页改变
                currentChange(e) {
                    this.params.page = e
                    this.getHelpIndex()
                },
                // 关键字搜索
                inputChange() {
                    const params = {
                        keywords: this.params.keywords
                    }
                    helpList(params).then(res => {
                        if (res.data.status === 200) {
                            const list = res.data.data.list
                            let isSearch = false
                            list.forEach(element => {
                                element.helps.forEach(item => {
                                    if (item.search) {
                                        isSearch = true
                                        const id = item.id
                                        location.href = `helpTotal.html?id=${id}`
                                    }
                                })
                            });
                            if (!isSearch) {
                                this.$message({
                                    message: '查询结果为空！',
                                    type: 'warning'
                                });
                            }
                        }
                    })
                },
                // 获取通用配置
                getCommonData() {
                    getCommon().then(res => {
                        if (res.data.status === 200) {
                            this.commonData = res.data.data
                            localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                            document.title = this.commonData.website_name + '-帮助中心'
                        }
                    })
                },
                getHelpIndex() {
                    helpIndex(this.params).then(res => {
                        if (res.data.status === 200) {
                            let list = res.data.data.index
                            this.helpIndexList = list
                        }
                    })
                },
                // 去帮助中心汇总
                toHelpTotal() {
                    location.href = `helpTotal.html`
                },
                // 帮助详情
                toDetail(id) {
                    location.href = `helpTotal.html?id=${id}`
                }
            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
