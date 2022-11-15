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
                // 获取通用信息
                this.getCommonData()
                // // 获取工单类别
                // this.getTicketType()
                // // 获取关联产品列表
                // this.getHostList()
                // 获取工单详情
                this.getDetails()
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
                    commonData: {},
                    id: null,
                    ticketData: {},
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
                    // 工单类别
                    ticketTypeList: [],
                    // 关联产品列表
                    hostList: [],
                    // 基本信息
                    baseMsg: {
                        title: '',
                        type: '',
                        hosts: '',
                        status: ''
                    },
                    replyData: {
                        id: null,
                        content: "",
                        attachment: []
                    },
                    sendBtnLoading: false,
                    fileList: [
                    ]
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
                            document.title = this.commonData.website_name + '-工单详情'
                        }
                    })
                },
                // 返回工单列表页面
                backTicket() {
                    location.href = 'ticket.html'
                },
                // 获取url中的id参数然后获取工单详情信息
                getDetails() {
                    let url = window.location.href
                    let getqyinfo = url.split('?')[1]
                    let getqys = new URLSearchParams('?' + getqyinfo)
                    let id = getqys.get('id')
                    this.id = id
                    const params = {
                        id
                    }
                    // 调用查看工单接口
                    ticketDetail(params).then(res => {
                        if (res.data.status === 200) {
                            this.ticketData = res.data.data.ticket
                            this.ticketData.replies = this.ticketData.replies.reverse()
                            // 工单类型
                            this.getTicketType()

                            // 当前状态
                            this.baseMsg.status = this.stats[this.ticketData.status].name
                            // 标题
                            this.baseMsg.title = this.ticketData.title
                            // 关联产品
                            this.getHostList()
                        }
                    })
                },
                // 获取工单类型
                getTicketType() {
                    ticketType().then(res => {
                        if (res.data.status === 200) {
                            this.ticketTypeList = res.data.data.list
                            this.ticketTypeList.map(item => {
                                if (item.id == this.ticketData.ticket_type_id) {
                                    this.baseMsg.type = item.name
                                }
                            })
                        }
                    })
                },
                // 获取产品列表
                getHostList() {
                    const params = {
                        keywords: '',
                        status: '',
                        page: 1,
                        limit: 1000,
                        orderby: "id",
                        sort: "desc"
                    }
                    hostAll(params).then(res => {
                        if (res.data.status === 200) {
                            this.hostList = res.data.data.list
                            let names = ""
                            this.ticketData.host_ids.forEach(element => {
                                this.hostList.forEach(item => {
                                    if (item.id == element) {
                                        names += item.product_name + ","
                                    }
                                })
                            });
                            names = names.slice(0, -1)
                            this.baseMsg.hosts = names
                        }
                    })
                },
                // 回复工单
                doReplyTicket() {
                    if (!this.replyData.content) {
                        this.$message.error("内容不能为空");
                        return false
                    }

                    const params = {
                        ...this.replyData,
                        id: this.id,
                    }
                    this.sendBtnLoading = true
                    replyTicket(params).then(res => {
                        if (res.data.status === 200) {
                            // 清空输入框
                            this.replyData.content = ""
                            this.replyData.attachment = []
                            // 清空上传框
                            let uploadFiles = this.$refs['fileupload'].uploadFiles
                            let length = uploadFiles.length
                            uploadFiles.splice(0, length)

                            // 重新拉取工单详情
                            this.getDetails()
                        }
                        this.sendBtnLoading = false
                    }).catch(err => {
                        this.sendBtnLoading = false
                        this.$message.error(err.data.msg);
                    })
                },
                // 聊天框滚动到底部
                toBottom() {
                    var div = document.getElementsByClassName('main-old-msg')[0];
                    div.scrollTop = div.scrollHeight;
                },
                // 上传文件相关 
                handleSuccess(response, file, fileList) {
                    if (response.status === 200) {
                        this.replyData.attachment.push(response.data.save_name)
                    }
                },
                beforeRemove(file, fileList) {
                    // 获取到删除的 save_name
                    let save_name = file.response.data.save_name
                    this.replyData.attachment = this.replyData.attachment.filter(item => {
                        return item != save_name
                    })
                },
                // 下载文件
                downFile(res, title) {
                    let url = res.lastIndexOf('/');
                    res = res.substring(url + 1, res.length);
                    const params = {
                        name: res
                    }
                    downloadFile(params).then(function (response) {
                        const blob = new Blob([response.data]);
                        const fileName = title;
                        const linkNode = document.createElement('a');
                        linkNode.download = fileName; //a标签的download属性规定下载文件的名称
                        linkNode.style.display = 'none';
                        linkNode.href = URL.createObjectURL(blob); //生成一个Blob URL
                        document.body.appendChild(linkNode);
                        linkNode.click(); //模拟在按钮上的一次鼠标单击
                        URL.revokeObjectURL(linkNode.href); // 释放URL 对象
                        document.body.removeChild(linkNode);
                    }).catch(error => {
                        this.$message.error(error.data.msg);
                    });
                },
                // 附件下载
                downloadfile(url) {
                    const downloadElement = document.createElement("a");
                    downloadElement.href = url;
                    downloadElement.download = url.split("^")[1]; // 下载后文件名
                    document.body.appendChild(downloadElement);
                    downloadElement.click(); // 点击下载
                },
            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
