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
                // 获取通用信息
                this.getCommonData()
                // 获取工单统计
                this.getTicketStatistic()
                // 获取工单列表
                this.getTicketList()
                // 获取工单类型
                this.getTicketType()
                // 获取关联产品列表
                this.getHostList()
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
                    // 分页
                    params: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: 'id',
                        sort: 'desc',
                        keywords: '',
                    },
                    // 通用数据
                    commonData: {},
                    // 顶部展示
                    topData: [
                        {
                            id: 1,
                            text: lang.ticket_text1,
                            num: 0,
                            style: `background: url(${url}/img/ticket/top-bg-1.png) no-repeat;background-size:100% 100%;`
                        },
                        {
                            id: 2,
                            text: lang.ticket_text2,
                            num: 0,
                            style: `background: url(${url}/img/ticket/top-bg-2.png) no-repeat;background-size:100% 100%;`
                        },
                        {
                            id: 3,
                            text: lang.ticket_text3,
                            num: 0,
                            style: `background: url(${url}/img/ticket/top-bg-3.png) no-repeat;background-size:100% 100%;`
                        },
                        {
                            id: 4,
                            text: lang.ticket_text4,
                            num: 0,
                            style: `background: url(${url}/img/ticket/top-bg-4.png) no-repeat;background-size:100% 100%;`
                        },
                    ],
                    // 表格数据
                    dataList: [],
                    // 表格加载
                    tableLoading: false,
                    // 创建工单弹窗是否显示
                    isShowDialog: false,
                    // 创建工单弹窗 数据
                    formData: {
                        title: '',
                        ticket_type_id: '',
                        host_ids: [],
                        content: '',
                        attachment: []
                    },
                    // 表单错误信息显示
                    errText: '',
                    // 工单类别
                    ticketType: [],
                    // 关联产品列表
                    hostList: [],
                    createBtnLoading: false,
                    stats: {
                        Pending: {
                            name: "待接单",
                        },
                        Handling: {
                            name: "处理中",
                        },
                        Reply: {
                            name: "待回复"

                        },
                        Replied: {
                            name: "已回复"
                        },
                        Resolved: {
                            name: "已解决"
                        },
                        Closed: {
                            name: "已关闭"
                        }
                    },
                    fileList: []


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
                    this.getTicketList()
                },
                // 当前页改变
                currentChange(e) {
                    this.params.page = e
                    this.getTicketList()
                },
                inputChange() {
                    this.params.page = 1
                    this.getTicketList()
                },
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
                // 获取工单统计
                getTicketStatistic() {
                    ticketStatistic().then(res => {
                        if (res.data.status === 200) {
                            const data = res.data.data
                            this.topData[0].num = data.pending
                            this.topData[1].num = data.handling
                            this.topData[2].num = data.reply
                            this.topData[3].num = data.replied
                        }
                    })
                },
                // 获取工单列表
                getTicketList() {
                    this.tableLoading = true
                    ticketList(this.params).then(res => {
                        if (res.data.status === 200) {
                            this.dataList = res.data.data.list
                            this.params.total = res.data.data.count
                        }
                        this.tableLoading = false
                    })
                },
                // 展示创建弹窗 并初始化弹窗数据
                showCreateDialog() {
                    this.formData = {
                        title: '',
                        ticket_type_id: '',
                        host_ids: [],
                        content: '',
                        attachment: []
                    }
                    this.errText = ''
                    this.isShowDialog = true
                },
                // 验证表单调用接口执行 创建工单
                doCreateTicket() {
                    let isPass = true
                    this.errText = ''
                    const formData = this.formData
                    if (!formData.title) {
                        isPass = false
                        this.errText = "请输入工单标题"
                    }

                    if (!formData.ticket_type_id) {
                        isPass = false
                        this.errText = "请选择工单类型"
                    }
                    if (!formData.host_ids) {
                        isPass = false
                        this.errText = "请选择关联产品"
                    }
                    if (!formData.content) {
                        isPass = false
                        this.errText = "请输入问题描述"
                    }

                    if (isPass) {
                        // 调用创建工单接口
                        const params = formData
                        this.createBtnLoading = true
                        createTicket(params).then(res => {
                            if (res.data.status === 200) {
                                // 关闭弹窗
                                this.isShowDialog = false
                                // 刷新工单列表
                                this.getTicketList()
                                // 刷新工单统计
                                this.getTicketStatistic()
                            }
                            this.delFile()
                            this.createBtnLoading = false
                        }).catch(error => {
                            this.errText = error.data.msg
                            this.createBtnLoading = false
                        })
                    }

                },
                closeDialog() {
                    this.delFile()
                    this.isShowDialog = false
                },
                // 获取工单类型
                getTicketType() {
                    ticketType().then(res => {
                        if (res.data.status === 200) {
                            this.ticketType = res.data.data.list
                        }
                    })
                },
                // 获取产品列表
                getHostList() {
                    const params = {
                        keywords:'',
                        status:'',
                        page:1,
                        limit:1000,
                        orderby:"id",
                        sort:"desc"
                    }
                    hostAll(params).then(res => {
                        console.log(res);
                        if (res.data.status === 200) {
                            this.hostList = res.data.data.list
                        }
                    })
                },
                // 跳转工单详情
                itemReply(record) {
                    const id = record.id
                    location.href = `ticketDetails.html?id=${id}`
                },
                // 关闭工单
                itemClose(record) {
                    const id = record.id
                    const params = {
                        id
                    }
                    // 调用关闭工单接口 给出结果
                    closeTicket(params).then(res => {
                        if (res.data.status === 200) {
                            this.$message({
                                message: "关闭工单成功",
                                type: 'success'
                            });
                            this.getTicketList()
                            this.getTicketStatistic()
                        }
                    }).catch(err => {
                        this.$message.error(err.data.msg);
                    })
                },
                // 催单
                itemUrge(record) {
                    const id = record.id
                    const params = {
                        id
                    }
                    // 调用催单接口 给出结果
                    urgeTicket(params).then(res => {
                        if (res.data.status === 200) {
                            this.$message({
                                message: "催单成功",
                                type: 'success'
                            });
                        }
                    }).catch(err => {
                        this.$message.error(err.data.msg);
                    })
                },
                // 上传文件相关 
                handleSuccess(response, file, fileList) {
                    if (response.status === 200) {
                        this.formData.attachment.push(response.data.save_name)
                    }
                },
                beforeRemove(file, fileList) {
                    // 获取到删除的 save_name
                    let save_name = file.response.data.save_name
                    this.formData.attachment = this.formData.attachment.filter(item => {
                        return item != save_name
                    })
                },
                // 删除上传文件的文件
                delFile() {
                    let uploadFiles = this.$refs['fileupload'].uploadFiles
                    let length = uploadFiles.length
                    uploadFiles.splice(0, length)
                }

            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
