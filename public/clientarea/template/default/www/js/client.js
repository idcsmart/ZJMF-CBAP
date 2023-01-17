(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementById('content')
        Vue.prototype.lang = window.lang
        Vue.prototype.moment = window.moment
        new Vue({
            components: {
                topMenu,
                asideMenu,
                pagination,
                payDialog,
            },
            created() {
                let url = window.location.href
                let getqyinfo = url.split('?')[1]
                let getqys = new URLSearchParams('?' + getqyinfo)
                this.activeName = getqys.get('activeName') ? getqys.get('activeName') : '1'
                this.getCommontData()
                this.getData1()
                this.getAuthorize()
            },
            data() {
                return {
                    commontData: {},
                    activeName: "1",
                    // 用户授权列表
                    authorizeData: [],
                    // 当前用户授权信息
                    authorizeNowData: {},
                    type: {
                        addon: "插件",
                        captcha: "验证码接口",
                        certification: "实名接口",
                        gateway: "支付接口",
                        mail: "邮件接口",
                        sms: "短信接口",
                        server: "模块",
                        template: "主题",
                        service: "服务"
                    },
                    status: {
                        Unpaid: "待支付",
                        Paid: "已支付待确认收货",
                        Wait: "等待服务",
                        Inservice: "服务中",
                        Refunding: "退款中",
                        Refunded: "已退款",
                        Finish: "已完成",
                        Cancelled: "已取消"
                    },
                    // 我的应用开始
                    params1: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: 'id',
                        sort: 'desc',
                        keywords: '',
                        type: "",
                        host_id: "",
                    },
                    loading1: false,
                    dataList1: [],
                    loading1: false,
                    appOrderId: null,
                    // 地址填写弹窗
                    addressVisible: false,
                    // 后台地址
                    addressForm: {
                        host_id: null,
                        admin_address: ""
                    },
                    addressRules: {
                        admin_address: [
                            { required: true, message: '请输入后台地址', trigger: 'blur' },
                        ]
                    },
                    // 我的应用结束

                    // 我的服务开始
                    params2: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: 'id',
                        sort: 'desc',
                        keywords: '',
                        used: "",
                        host_id: "",
                    },
                    loading2: false,
                    dataList2: [],
                    loading2: false,
                    serviceOrderId: null,
                    // 我的服务结束

                    // 我的订单开始
                    params3: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: 'id',
                        sort: 'desc',
                        keywords: '',
                        type: "",
                        status: ""
                    },
                    loading3: false,
                    dataList3: [],
                    loading3: false,
                    payType: {
                        onetime: "一次性",
                        monthly: "月付",
                        quarterly: "季付",
                        semiannually: "半年付",
                        annually: "年付",
                        free: "免费"
                    },
                    orderId: null,
                    // 确认关闭弹窗
                    orderDelVisible: false,
                    orderDelLoading: false,
                    // 确认订单完成 弹窗
                    orderFinishVisible: false,
                    orderFinishLoading: false,
                    // 服务完成 弹窗
                    serviceFinishVisible: false,
                    serviceFinishLoading: false,
                    // 修改退款金额 弹窗
                    editRefundVisible: false,
                    editRefundLoading: false,
                    editRefundForm: {
                        id: null,
                        amount: null
                    },
                    editRefundRules: {
                        amount: [
                            { required: true, message: '请输入退款金额', trigger: 'blur' },
                        ]
                    },
                    // 申请退款
                    refundVisible: false,
                    refundLoading: false,
                    refundForm: {
                        id: "",
                        amount: "",
                        content: "",
                        attachment: [],
                    },
                    refundRules: {
                        amount: [
                            { required: true, message: '请输入退款金额', trigger: 'blur' },
                        ],
                        content: [
                            { required: true, message: '请输入退款理由', trigger: 'blur' },
                        ]
                    },
                    refundDetailData: [],
                    // true:申请退款 false:查看详情
                    isRefund: false,
                    refundMoney: "",
                    // 发表评价
                    appraiseVisible: false,
                    appraiseForm: {
                        id: "",
                        content: "",
                        score: 0
                    },
                    appraiseLoading: false,
                    appraiseRules: {
                        content: [
                            { required: true, message: '请输入评价内容', trigger: 'blur' },
                        ],
                        score: [
                            { required: true, message: '请进行打分评价', trigger: 'blur' },
                        ]
                    },
                    // 我的订单结束

                    // 投诉举报开始
                    params4: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: 'id',
                        sort: 'desc',
                        keywords: '',
                        status: ""
                    },
                    loading4: false,
                    dataList4: [],
                    loading4: false,
                    complaintsVisible: false,
                    complaintsForm: {
                        id: "",
                        content: "",
                        attachment: []
                    },
                    complaintsRules: {
                        id: [
                            { required: true, message: '请选择订单', trigger: 'blur' },
                        ],
                        content: [
                            { required: true, message: '请输入投诉内容', trigger: 'blur' },
                        ]
                    },
                    complaintsLoading: false,
                    imgVisible: false,
                    dialogImageUrl: "",
                    fileList: [],
                    orderSelectList: [],
                    // 投诉详情数据
                    complaintDetailData: {},
                    complaintDetailForm: {
                        id: null,
                    },
                    // 是否查看投诉 true:查看投诉 false:投诉订单
                    isComplaintView: false,
                    // 撤回二次确认弹窗
                    retractVisible: false,
                    // 投诉id
                    retractId: null,
                    retractLoading: false,
                    // 投诉举报结束

                    // 续费开始
                    // 显示续费弹窗
                    isShowRenew: false,
                    // 续费页面数据
                    renewData: [],
                    renewForm: {
                        id: null,
                        billing_cycle: "",
                        customfield: {},
                        totalPrice: 0
                    },
                    renewLoading: false,
                    renewActiveId: "",
                    // 续费结束
                }
            },
            filters: {
                formateTime(time) {
                    if (time && time !== 0) {
                        var date = new Date(time * 1000);
                        Y = date.getFullYear() + '/';
                        M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '/';
                        D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate());
                        return (Y + M + D);
                    } else {
                        return "--";
                    }
                },
                formateTime2(time) {
                    if (time && time !== 0) {
                        var date = new Date(time * 1000);
                        Y = date.getFullYear() + '-';
                        M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
                        D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' ';
                        h = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
                        m = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
                        return (Y + M + D + h + m);
                    } else {
                        return "--";
                    }
                },
                filterMoney(money) {
                    if (isNaN(money)) {
                        return '0.00'
                    } else {
                        const temp = `${money}`.split('.')
                        return parseInt(temp[0]).toLocaleString() + '.' + (temp[1] || '00')
                    }
                }
            },
            methods: {
                getCommontData() {
                    if (localStorage.getItem('common_set_before')) {
                        this.commontData = JSON.parse(localStorage.getItem('common_set_before'))
                    } else {
                        getCommon().then(res => {
                            if (res.data.status == 200) {
                                this.commontData = res.data.data
                                localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                            }
                        })
                    }
                },
                handleClick() {
                    const index = this.activeName
                    if (index == 1) {
                        this.getData1()
                    }
                    if (index == 2) {
                        this.getData2()
                    }
                    if (index == 3) {
                        this.getData3()
                    }
                    if (index == 4) {
                        this.getData4()
                    }
                },
                // 获取授权列表
                getAuthorize() {
                    authorize().then(res => {
                        if (res.data.status == 200) {
                            this.authorizeData = res.data.data.list
                        }
                    })
                },
                // 我的应用开始
                sizeChange1(e) {
                    this.params1.limit = e
                    this.params1.page = 1
                    // 获取列表
                    this.getData1()
                },
                // 当前页改变
                currentChange1(e) {
                    this.params1.page = e
                    this.getData1()
                },
                search1() {
                    this.params1.page = 1
                    // 获取列表
                    this.getData1()
                },
                getData1() {
                    this.loading1 = true
                    const params = {
                        ...this.params1
                    }
                    clientAppList(params).then(res => {
                        if (res.data.status == 200) {
                            this.dataList1 = res.data.data.list
                            this.params1.total = res.data.data.count
                        }
                        this.loading1 = false
                    }).catch(error => {
                        this.loading1 = false
                    })
                },
                // 去支付
                appPay(orderId) {
                    this.appOrderId = orderId
                    this.$refs.payDialog.showPayDialog(this.appOrderId, 0);
                },
                // 下载安装包
                downloadApp(id) {
                    const params = {
                        id
                    }
                    download(params).then(res => {
                        console.log(res);
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                    })
                },
                address() {
                    this.$refs['addressForm'].validate((valid) => {
                        if (valid) {
                            // 检测后台地址
                            const params = {
                                ...this.addressForm,
                                host_id: this.authorizeNowData.host_id
                            }
                            checkAddress(params).then(res => {
                                if (res.data.status == 200) {
                                    this.$message.success(res.data.msg)
                                    this.addressVisible = false
                                    // 调用安装接口

                                }
                            }).catch(error => {
                                this.$message.error(error.data.msg)
                            })
                        } else {
                            return false
                        }
                    })
                },
                // 我的应用结束


                // 我的服务开始
                sizeChange2(e) {
                    this.params2.limit = e
                    this.params2.page = 1
                    // 获取列表
                    this.getData2()
                },
                // 当前页改变
                currentChange2(e) {
                    this.params2.page = e
                    this.getData2()
                },
                search2() {
                    this.params2.page = 1
                    // 获取列表
                    this.getData2()
                },
                getData2() {
                    this.loading2 = true
                    const params = {
                        ...this.params2
                    }
                    clientServiceLiist(params).then(res => {
                        if (res.data.status == 200) {
                            this.dataList2 = res.data.data.list
                            this.params2.total = res.data.data.count
                        }
                        this.loading2 = false
                    }).catch(error => {
                        this.loading2 = false
                    })
                },
                servicePay(orderId) {
                    this.serviceOrderId = orderId
                    this.$refs.payDialog.showPayDialog(this.serviceOrderId, 0);
                },
                // 我的服务结束

                // 我的订单开始
                sizeChange3(e) {
                    this.params3.limit = e
                    this.params3.page = 1
                    // 获取列表
                    this.getData3()
                },
                // 当前页改变
                currentChange3(e) {
                    this.params3.page = e
                    this.getData3()
                },
                search3() {
                    this.params3.page = 1
                    // 获取列表
                    this.getData3()
                },
                getData3() {
                    this.loading3 = true
                    const params = {
                        ...this.params3
                    }
                    order(params).then(res => {
                        if (res.data.status == 200) {
                            this.dataList3 = res.data.data.list
                            this.params3.total = res.data.data.count
                        }
                        this.loading3 = false
                    }).catch((error) => {
                        this.loading3 = false
                    })
                },
                // 显示关闭订单弹窗
                showOrderDel(id) {
                    this.orderId = id
                    this.orderDelVisible = true
                },
                orderDelSub() {
                    this.orderDelLoading = true
                    const params = {
                        id: this.orderId
                    }
                    delOrder(params).then(res => {
                        this.orderDelLoading = false
                        if (res.data.status == 200) {
                            this.$message.success(res.data.msg)
                            this.getData3()
                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                        this.orderDelLoading = false
                    })
                },
                // 确认订单收货
                showOrderFinish(id) {
                    this.orderId = id
                    this.orderFinishVisible = true
                },
                orderFinishSub() {
                    this.orderFinishLoading = true
                    const params = {
                        id: this.orderId
                    }
                    finishOrder(params).then(res => {
                        if (res.data.status == 200) {
                            this.$message.success(res.data.msg)
                            this.getData3()
                            this.orderFinishLoading = false
                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                        this.orderFinishLoading = false
                    })
                },
                // 服务完成
                showServiceFinish(id) {
                    this.orderId = id
                    this.serviceFinishVisible = true
                },
                serviceFinishSub() {
                    this.serviceFinishLoading = true
                    const params = {
                        id: this.orderId
                    }
                    finishService(params).then(res => {
                        if (res.data.status == 200) {
                            this.$message.success(res.data.msg)
                            this.getData3()
                            this.serviceFinishLoading = false
                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                        this.serviceFinishLoading = false
                    })
                },
                // 修改退款金额
                showEditRefund(id, refund) {
                    this.orderId = id
                    this.editRefundForm = {
                        id,
                        amount: refund
                    }
                    this.editRefundVisible = true
                },
                // 修改退款金额提交
                editRefundSub() {
                    this.$refs['editRefundForm'].validate((valid) => {
                        if (valid) {
                            this.editRefundLoading = true
                            const params = {
                                ...this.editRefundForm
                            }
                            editRefund(params).then(res => {
                                this.editRefundLoading = false
                                this.editRefundVisible = false
                                this.$message.success(res.data.msg)
                                this.getData3()
                            }).catch(error => {
                                this.editRefundLoading = false
                                this.$message.error(error.data.msg)
                            })
                        } else {
                            return false;
                        }
                    });
                },
                // 申请退款
                showRefund(id) {
                    this.orderId = id
                    this.refundForm = {
                        id,
                        amount: "",
                        content: "",
                        attachment: []
                    }
                    this.isRefund = true
                    this.refundVisible = true
                },
                refundSub() {
                    if (this.isRefund) {
                        this.$refs['refundForm'].validate((valid) => {
                            if (valid) {
                                this.refundLoading = true
                                const params = {
                                    ...this.refundForm
                                }
                                doRefund(params).then(res => {
                                    if (res.data.status == 200) {
                                        this.refundLoading = false
                                        this.refundVisible = false
                                        this.$message.success(res.data.msg)
                                        this.getData3()
                                    }
                                }).catch(error => {
                                    this.refundLoading = false
                                    this.$message.error(error.data.msg)
                                })
                            } else {
                                return false
                            }
                        })
                    } else {
                        this.$refs['refundForm'].validate(valid => {
                            if (valid) {
                                this.refundLoading = true
                                const params = {
                                    ...this.refundForm
                                }
                                replyRefund(params).then(res => {
                                    this.refundLoading = false
                                    if (res.data.status == 200) {
                                        this.refundLoading = false
                                        this.refundVisible = false
                                        this.$message.success(res.data.msg)
                                        this.getData3()
                                    }
                                }).catch(error => {
                                    this.refundLoading = false
                                    this.$message.error(error.data.msg)
                                })
                            } else {
                                return false
                            }
                        })
                    }

                },
                refundClose() {
                    this.refundVisible = false
                    let uploadFiles = this.$refs['refundFileupload'].uploadFiles
                    let length = uploadFiles.length
                    uploadFiles.splice(0, length)
                    this.$refs['refundForm'].resetFields();
                },
                // 去支付
                showPayDialog(id) {
                    this.orderId = id
                    this.$refs.payDialog.showPayDialog(this.orderId, 0);
                },
                // 查看申请
                showRefundDetail(id, refund) {
                    this.refundMoney = refund
                    this.isRefund = false
                    this.orderId = id
                    this.refundForm = {
                        id,
                        amount: "",
                        content: "",
                        attachment: []
                    }
                    this.refundVisible = true
                    const params = {
                        id
                    }
                    refundDetails(params).then(res => {
                        if (res.data.status == 200) {
                            this.refundDetailData = res.data.data.list
                        }
                    })
                },
                // 发表评价
                showAppraise(id) {
                    this.orderId = id
                    this.appraiseForm = {
                        id,
                        content: "",
                        score: 0
                    }
                    this.appraiseVisible = true
                },
                appraiseSub() {
                    this.$refs['appraiseForm'].validate((valid) => {
                        if (valid) {
                            const params = {
                                ...this.appraiseForm
                            }
                            this.appraiseLoading = true
                            evaluation(params).then(res => {
                                this.appraiseLoading = false
                                if (res.data.status == 200) {
                                    this.$message.success(res.data.msg)
                                    this.appraiseVisible = false
                                    this.getData3()
                                }
                            }).catch(error => {
                                this.appraiseLoading = false
                                this.$message.error(error.data.msg)
                            })
                        } else {
                            return false
                        }
                    })

                },
                // 投诉
                orderComplaints(id) {
                    this.showComplaints(id)
                },

                // 我的订单结束
                // 投诉举报开始
                sizeChange4(e) {
                    this.params4.limit = e
                    this.params4.page = 1
                    // 获取列表
                    this.getData4()
                },
                // 当前页改变
                currentChange4(e) {
                    this.params4.page = e
                    this.getData4()
                },
                search4() {
                    this.params4.page = 1
                    // 获取列表
                    this.getData4()
                },
                getData4() {
                    this.loading4 = true
                    const params = {
                        ...this.params4
                    }
                    complaint(params).then(res => {
                        if (res.data.status == 200) {
                            this.dataList4 = res.data.data.list
                            this.params4.total = res.data.data.count
                        }
                        this.loading4 = false
                    }).catch((error) => {
                        this.loading4 = false
                    })
                },
                // 显示投诉弹窗
                showComplaints(id) {
                    this.isComplaintView = false

                    // 获取订单列表
                    const params = {
                        page: 1,
                        limit: 10000,
                        orderby: "id",
                        sort: "desc"
                    }
                    order(params).then(res => {
                        if (res.data.status == 200) {
                            this.orderSelectList = res.data.data.list
                            if (id) {
                                this.complaintsForm.id = id
                            }
                        }
                    })
                    this.complaintsVisible = true
                },
                // 查看投诉点击
                showComplaintDetail(id) {
                    this.retractId = id
                    this.isComplaintView = true
                    // 显示投诉详情弹窗
                    const params = {
                        id
                    }
                    complaintOrderDetail(params).then(res => {
                        if (res.data.status == 200) {
                            this.complaintDetailData = res.data.data.list
                        }
                    })
                    this.complaintsVisible = true
                },
                // 关闭投诉弹窗
                closeComplaints() {
                    this.complaintsVisible = false
                    let uploadFiles = this.$refs['fileupload'].uploadFiles
                    let length = uploadFiles.length
                    uploadFiles.splice(0, length)
                    this.$refs['complaintsform'].resetFields();

                },
                // 投诉提交
                subComplaints() {
                    if (this.isComplaintView) {
                        this.$refs['complaintsform'].validate((valid) => {
                            if (valid) {
                                this.complaintsLoading = true
                                const params = {
                                    ...this.complaintsForm,
                                    id: this.retractId
                                }
                                replyComplaint(params).then(res => {
                                    if (res.data.status == 200) {
                                        this.$message.success(res.data.msg)
                                        this.complaintsVisible = false
                                        this.getData4()
                                    }
                                    this.complaintsLoading = false
                                }).catch(error => {
                                    this.complaintsLoading = false
                                    this.$message.error(error.data.msg)
                                })
                            } else {
                                return false
                            }
                        })
                    } else {
                        this.$refs['complaintsform'].validate((valid) => {
                            if (valid) {
                                this.complaintsLoading = true
                                const params = { ...this.complaintsForm }
                                complaintOrder(params).then(res => {
                                    if (res.data.status == 200) {
                                        this.$message.success(res.data.msg)
                                        this.complaintsVisible = false
                                        this.getData4()
                                    }
                                    this.complaintsLoading = false
                                }).catch(error => {
                                    this.complaintsLoading = false
                                    this.$message.error(error.data.msg)
                                })
                            } else {
                                return false;
                            }
                        });
                    }

                },
                // 上传图片点击预览
                handlePictureCardPreview(file) {
                    this.dialogImageUrl = file.url;
                    this.imgVisible = true;
                },
                // 附件上传前
                beforeUpload(file) {
                    // const isJPG = file.type === 'image/jpeg';
                    const isLt5M = file.size / 1024 / 1024 < 5;

                    // if (!isJPG) {
                    //     this.$message.error('上传头像图片只能是 JPG 格式!');
                    // }
                    if (!isLt5M) {
                        this.$message.error('上传头像图片大小不能超过 5MB!');
                    }
                    return isLt5M;
                    // return isJPG && isLt5M;
                },
                beforeRemove(file, fileList) {
                    // 获取到删除的 save_name
                    let save_name = file.response.data.save_name
                    if (this.activeName == '3') {
                        // 删除
                        this.refundForm.attachment = this.refundForm.attachment.filter(item => {
                            return item != save_name
                        })
                    }
                    if (this.activeName == '4') {
                        // 删除
                        this.complaintsForm.attachment = this.complaintsForm.attachment.filter(item => {
                            return item != save_name
                        })
                    }

                },
                handleSuccess(response, file, fileList) {
                    // console.log(response);
                    if (response.status != 200) {
                        this.$message.error(response.msg)
                        if (this.activeName == 3) {
                            // 清空上传框
                            let uploadFiles = this.$refs['refundFileupload'].uploadFiles
                            let length = uploadFiles.length
                            uploadFiles.splice(length - 1, length)
                        }
                        if (this.activeName == 4) {
                            // 清空上传框
                            let uploadFiles = this.$refs['fileupload'].uploadFiles
                            let length = uploadFiles.length
                            uploadFiles.splice(length - 1, length)
                        }

                    } else {
                        if (this.activeName == '3') {
                            this.refundForm.attachment.push('http://kfc.idcsmart.com/upload/common/default/' + response.data.save_name)
                        }
                        if (this.activeName == '4') {
                            this.complaintsForm.attachment.push('http://kfc.idcsmart.com/upload/common/default/' + response.data.save_name)
                        }

                    }
                },
                // 撤销点击
                retract(id) {
                    // 确认撤销弹窗
                    this.retractId = id
                    this.retractVisible = true
                },
                // 撤回确认
                retractSub() {
                    this.retractLoading = true
                    const params = {
                        id: this.retractId
                    }
                    delComplaint(params).then(res => {
                        if (res.data.status == 200) {
                            this.$message.success(res.data.msg)
                            this.retractVisible = false
                            this.getData4()
                        }
                        this.retractLoading = false
                    }).catch(error => {
                        this.retractLoading = false
                        this.$message.error(error.data.msg)
                    })
                },
                imgClick(url) {
                    this.dialogImageUrl = url
                    this.imgVisible = true
                },
                // 投诉举报结束

                // 支付成功回调
                paySuccess(id) {
                    if (id == this.orderId) {
                        this.getData3()
                    }
                    if (id == this.serviceOrderId) {
                        this.getData2()
                    }
                    if (id == this.appOrderId) {
                        this.getData1()
                    }
                },
                // 支付取消回调
                payCancel(id) {
                    this.activeName = "3"
                    this.getData3()
                },




                // 续费开始
                renewDgClose() {
                    this.isShowRenew = false
                    console.log("关闭续费");
                },
                showRenew(id) {
                    const params = {
                        id
                    }
                    this.renewForm.id = id
                    // 获取续费信息
                    renewMsg(params).then(res => {
                        if (res.data.status == 200) {
                            this.renewData = res.data.data.host
                            this.renewForm.billing_cycle = this.renewData[0]?.id
                            this.renewForm.totalPrice = this.renewData[0]?.price
                            this.isShowRenew = true
                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                    })
                },
                // 续费提交
                renewSub() {
                    const params = {
                        id: this.renewForm.id,
                        billing_cycle: this.renewForm.billing_cycle,
                        customfield: {}
                    }
                    this.renewLoading = true
                    doRenew(params).then(res => {
                        this.renewLoading = false
                        if (res.data.status == 200) {
                            this.$message.success(res.data.msg)
                            this.isShowRenew = false
                            // 续费成功回调
                            this.renewSuccess()
                        }
                    }).catch(error => {
                        this.renewLoading = false
                        this.$message.error(error.data.msg)
                    })
                },
                renewItemChange(e) {
                    this.renewForm.billing_cycle = e.id
                    this.renewForm.totalPrice = e.price
                },
                // 续费成功回调
                renewSuccess() {
                    if (this.activeName == 1) {
                        this.getData1()
                    }
                    if (this.activeName == 2) {
                        this.getData2()
                    }
                },
                // 续费结束
                authorizeChange(e) {
                    this.authorizeNowData = e
                    console.log(e);
                },
            },

        }).$mount(template)

        const mainLoading = document.getElementById('mainLoading')
        setTimeout(() => {
            mainLoading && (mainLoading.style.display = 'none')
        }, 200)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
