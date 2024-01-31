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

                // 获取工单详情
                this.getDetails()
            },
            mounted() {
                // this.initTemplate()
                this.initViewer()
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
                    // 工单类别
                    ticketTypeList: [],
                    // 关联产品列表
                    hostList: [],
                    jwt: `Bearer ${localStorage.jwt}`,
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
                    ],
                    visible: false,
                    delLoading: false,
                    isClose: false,
                    viewer: null,
                    preImg: "",
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
                    this.commonData = JSON.parse(localStorage.getItem('common_set_before'))
                    document.title = this.commonData.website_name + '-' + lang.ticket_label17
                },
                // 返回工单列表页面
                backTicket() {
                    location.href = 'ticket.htm'
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
                            const replies = res.data.data.ticket.replies
                            const arrEntities = { 'lt': '<', 'gt': '>', 'nbsp': ' ', 'amp': '&', 'quot': '"' };
                            this.ticketData.replies = replies.reverse().map((item) => {
                                item.content = filterXSS(item.content).replace(/&(lt|gt|nbsp|amp|quot);/ig, function (all, t) {
                                    return arrEntities[t];
                                });
                                item.content = item.content.replaceAll('http-equiv="refresh"', '')
                                return item
                            })
                            // this.$nextTick(() => {
                            //     this.toBottom()
                            // })
                            // 工单类型
                            this.getTicketType()
                            // 当前状态
                            this.baseMsg.status = this.ticketData.status
                            // 标题
                            this.baseMsg.title = '#' + this.ticketData.ticket_num + "-" + this.ticketData.title

                            this.baseMsg.create_time = this.ticketData.create_time
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
                            // let names = ""

                            let hosts = []
                            this.ticketData.host_ids.forEach(element => {
                                this.hostList.forEach(item => {
                                    if (item.id == element) {
                                        let hostitem = {
                                            id: item.id,
                                            label: item.product_name + " (" + item.name + ")"
                                        }

                                        hosts.push(hostitem)

                                        // names += item.product_name + " (" + item.name + ")" + ","
                                    }
                                })
                            });
                            // names = names.slice(0, -1)
                            this.baseMsg.hosts = hosts
                        }
                    })
                },
                // 回复工单
                doReplyTicket() {
                    if (this.sendBtnLoading) return
                    if (!this.replyData.content) {
                        this.$message.warning(lang.ticket_label18)
                        return
                    }

                    const params = {
                        ...this.replyData,
                        id: this.id,
                        // content: tinyMCE.activeEditor.getContent()
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

                            // tinyMCE.activeEditor.setContent("")

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
                    // console.log(response);
                    if (response.status != 200) {
                        this.$message.error(response.msg)
                        // 清空上传框
                        let uploadFiles = this.$refs['fileupload'].uploadFiles
                        let length = uploadFiles.length
                        uploadFiles.splice(length - 1, length)
                    } else {
                        this.replyData.attachment.push(response.data.save_name)
                    }
                },
                handleProgress(response) {
                    console.log("response", response);
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
                    const name = url
                    const type = name.substring(name.lastIndexOf(".") + 1)
                    if (['png', 'jpg', 'jepg', 'bmp', 'webp', 'PNG', 'JPG', 'JEPG', 'BMP', 'WEBP'].includes(type)) {
                        this.preImg = url
                        this.viewer.show()
                    } else {
                        const downloadElement = document.createElement("a");
                        downloadElement.href = url;
                        downloadElement.download = url.split("^")[1]; // 下载后文件名
                        document.body.appendChild(downloadElement);
                        downloadElement.click(); // 点击下载
                    }
                },
                showClose() {
                    this.visible = true
                },
                // 关闭工单
                doCloseTicket() {
                    const params = {
                        id: this.id
                    }
                    this.delLoading = true
                    closeTicket(params).then(res => {
                        if (res.data.status == 200) {
                            this.$message.success(res.data.msg)
                            this.visible = false
                            // 重新拉取工单详情
                            this.getDetails()
                        }
                        this.delLoading = false
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                        this.delLoading = false
                    })
                },
                // 载入富文本
                initTemplate() {
                    tinymce.init({
                        selector: '#tiny',
                        language_url: '/tinymce/langs/zh_CN.js',
                        language: 'zh_CN',
                        min_height: 400,
                        width: '100%',
                        plugins: 'link lists image code table colorpicker textcolor wordcount contextmenu fullpage',
                        toolbar:
                            'bold italic underline strikethrough | fontsizeselect | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote | undo redo | link unlink image fullpage code | removeformat',
                        images_upload_url: '/console/v1/upload',
                        convert_urls: false,
                        images_upload_handler: this.handlerAddImg
                    });
                },
                // 富文本图片上传
                handlerAddImg(blobInfo, success, failure) {
                    return new Promise((resolve, reject) => {
                        const formData = new FormData()
                        formData.append('file', blobInfo.blob())
                        axios.post('/console/v1/upload', formData, {
                            headers: {
                                Authorization: 'Bearer' + ' ' + localStorage.getItem('jwt')
                            }
                        }).then(res => {
                            const json = {}
                            if (res.status !== 200) {
                                failure('HTTP Error: ' + res.data.msg)
                                return
                            }
                            json.location = res.data.data?.image_url
                            if (!json || typeof json.location !== 'string') {
                                failure('Error:' + res.data.msg)
                                return
                            }
                            success(json.location)
                        })
                    })
                },
                toHost(id) {
                    location.href = "/productdetail.htm?id=" + id
                },
                hanldeImage(event) {
                    if (event.target.nodeName == 'IMG' || event.target.nodeName == 'img') {
                        const img = event.target.currentSrc
                        this.preImg = img
                        this.viewer.show()

                    }
                },
                initViewer() {
                    this.viewer = new Viewer(document.getElementById('viewer'), {
                        button: true,
                        inline: false,
                        zoomable: true,
                        title: true,
                        tooltip: true,
                        minZoomRatio: 0.5,
                        maxZoomRatio: 100,
                        movable: true,
                        interval: 2000,
                        navbar: true,
                        loading: true,
                    });
                }
            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
