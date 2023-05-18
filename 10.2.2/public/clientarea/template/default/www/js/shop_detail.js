(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementById('content')
        Vue.prototype.lang = window.lang
        Vue.prototype.moment = window.moment
        new Vue({
            components: {
                topMenu,
                asideMenu
            },
            created() {

                let url = window.location.href
                let getqyinfo = url.split('?')[1]
                let getqys = new URLSearchParams('?' + getqyinfo)
                this.id = getqys.get('id')
                this.evaluationParams.id = getqys.get('id')
                this.clientId = getqys.get('clientId')
                this.getCommontData()
                this.getDetail()
                this.getDeveloper()
            },
            mounted() {
                this.e_list_dom = document.querySelector('.evaluation-list')
                this.e_list_dom.addEventListener('scroll', this.scrollBottom)
            },
            destroyed() {
                this.e_list_dom.removeEventListener('scroll', this.scrollBottom)
            },
            data() {
                return {
                    commontData: {},
                    id: null,
                    e_list_dom: null,
                    clientId: null,
                    loading: false,
                    evaluation_list: [],
                    evaluation_score: 0,
                    evaluation_finash: false,
                    evaluationParams: {
                        id: '',
                        page: 1,
                        limit: 10,
                        orderby: 'create_time',
                        sort: 'desc',
                    },
                    appData: {
                        developer: {
                        },
                        images: []
                    },
                    developerData: {},
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
                    activeName: "1",
                    pay_type: 'onetime',
                    dialogVisible: false,
                    // 商品评论开始
                    evaluationLoading: false,

                    // 商品投诉开始
                    complaintsVisible: false,
                    complaintsForm: {
                        id: "",
                        content: "",
                        attachment: []
                    },
                    complaintsRules: {
                        content: [
                            { required: true, message: '请输入投诉内容', trigger: 'blur' },
                        ]
                    },
                    complaintsLoading: false,
                    imgVisible: false,
                    dialogImageUrl: "",
                    fileList: [],
                }
            },
            filters: {
                formateTime(time) {
                    if (time && time !== 0) {
                        var date = new Date(time * 1000);
                        Y = date.getFullYear() + '/';
                        M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '/';
                        D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' ';
                        return (Y + M + D);
                    } else {
                        return "--"
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
                dialogOpen() {
                    this.pay_type = 'onetime'
                },
                handleClickBuy() {
                    if (this.appData.pay_type === 0) {
                        this.dialogVisible = true
                    } else {
                        location.href = `buy_goods.html?id=${this.id}&type=free`
                    }
                },
                goBuyPage() {
                    this.dialogVisible = false
                    location.href = `buy_goods.html?id=${this.id}&type=${this.pay_type}`
                },

                handelPayType(type) {
                    this.pay_type = type
                },
                getDetail() {
                    this.loading = true
                    const params = {
                        id: this.id
                    }
                    appDetails(params).then(res => {
                        this.loading = false
                        if (res.data.status == 200) {
                            this.appData = res.data.data.app
                        }
                    }).catch(error => {
                        this.loading = false
                    })
                },
                async getEvaluationList() {
                    this.evaluationLoading = true
                    await evaluationList(this.evaluationParams).then((res) => {
                        this.evaluation_score = res.data.score
                        if (res.data.list.length === 0) {
                            this.evaluation_finash = true
                            return
                        }
                        this.evaluation_list = this.evaluation_list.concat(res.data.list)
                        this.evaluationParams.page++

                    }).catch((err) => {
                        this.evaluationLoading = false
                        this.evaluation_finash = true
                        this.$message.error(err.data.msg)
                    })
                    this.evaluationLoading = false
                },
                getDeveloper() {
                    if (this.clientId) {
                        const params = {
                            id: this.clientId
                        }
                        developer(params).then(res => {
                            if (res.data.status == 200) {
                                this.developerData = res.data.data.developer
                            }
                        })
                    }

                },
                // 滚动计算
                scrollBottom() {
                    const scrollTop = this.e_list_dom.scrollTop
                    const clientHeight = this.e_list_dom.clientHeight
                    const scrollHeight = this.e_list_dom.scrollHeight
                    if (scrollTop + clientHeight >= scrollHeight) {
                        !this.evaluation_finash && this.getEvaluationList()
                    }
                },
                goBack() {
                    history.back()
                },
                goBusunessDetail() {
                    location.href = `business_detail.html?id=${this.developerData.id}`
                },
                handleClick() {
                    const n = this.activeName
                    if (n == 2) {
                        this.evaluation_finash = false
                        this.evaluationParams.page = 1
                        this.evaluation_list = []
                        this.getEvaluationList()
                    }
                },

                // 显示投诉弹窗
                showComplaints() {
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

                    this.$refs['complaintsform'].validate((valid) => {
                        if (valid) {
                            this.complaintsLoading = true
                            const params = { ...this.complaintsForm, id: this.id }
                            productComplaint(params).then(res => {
                                if (res.data.status == 200) {
                                    this.$message.success(res.data.msg)
                                    this.complaintsVisible = false
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
                        this.$message.error('上传图片大小不能超过 5MB!');
                    }
                    return isLt5M;
                    // return isJPG && isLt5M;
                },
                beforeRemove(file, fileList) {
                    // 获取到删除的 save_name
                    let save_name = file.response.data.save_name
                    // 删除
                    this.complaintsForm.attachment = this.complaintsForm.attachment.filter(item => {
                        return item != save_name
                    })


                },
                handleSuccess(response, file, fileList) {
                    // console.log(response);
                    if (response.status != 200) {
                        this.$message.error(response.msg)
                        // 清空上传框
                        let uploadFiles = this.$refs['fileupload'].uploadFiles
                        let length = uploadFiles.length
                        uploadFiles.splice(length - 1, length)


                    } else {
                        this.complaintsForm.attachment.push('http://kfc.idcsmart.com/upload/common/default/' + response.data.save_name)
                    }
                },
            }

        }).$mount(template)

        const mainLoading = document.getElementById('mainLoading')
        setTimeout(() => {
            mainLoading && (mainLoading.style.display = 'none')
        }, 200)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
