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
                localStorage.frontMenusActiveId = "";
                this.getCommonData()
                this.getGateway();
            },
            mounted() {
                const addons = document.querySelector('#addons_js')
                this.addons_js_arr = JSON.parse(addons.getAttribute('addons_js'))
                this.initData()
            },
            updated() {
                // // 关闭loading
                // document.getElementById('mainLoading').style.display = 'none';
                // document.getElementsByClassName('template')[0].style.display = 'block'
            },
            destroyed() {

            },
            data() {
                return {
                    addons_js_arr: [], // 插件数组
                    commonData: {},
                    showRight: false,
                    account: {}, // 个人信息
                    certificationObj: {},// 认证信息
                    percentage: 0,
                    productListLoading: true,
                    nameLoading: false,
                    infoSecLoading: false,
                    productList: [],// 产品列表
                    ticketList: [], // 工单列表
                    homeNewList: [], // 新闻列表
                    // 支付方式
                    gatewayList: [],
                    headBgcList: ['#3699FF', '#57C3EA', '#5CC2D7', '#EF8BA2', '#C1DB81', '#F1978C', '#F08968'],
                    // 轮询相关
                    timer: null,
                    time: 300000,
                    // 后台返回的支付html
                    payHtml: "",
                    // 错误提示信息
                    errText: "",
                    // 是否显示充值弹窗
                    isShowCz: false,
                    payLoading1: false,
                    isShowimg1: true,
                    // 充值弹窗表单数据
                    czData: {
                        amount: "",
                        gateway: '',
                    },
                    czDataOld: {
                        amount: "",
                        gateway: '',
                    },
                    isOpen: true,
                    promoterData: {},
                    openVisible: false,

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
                formareDay(time) {
                    if (time && time !== 0) {
                        const dataTime = formateDate(time * 1000)
                        return dataTime.split(' ')[0].split('-')[1] + '-' + dataTime.split(' ')[0].split('-')[2]
                    } else {
                        return "--"
                    }
                }
            },
            methods: {
                toReferral(val) {
                    location.href = `/plugin/${val}/recommend.html`
                },
                handelAttestation(val) {
                    location.href = `/plugin/${val}/authentication_select.html`
                },
                goWorkPage(val) {
                    location.href = `/plugin/${val}/ticket.html`
                },
                goNoticePage(val) {
                    location.href = `/plugin/${val}/source.html`
                },
                goNoticeDetail(val, id) {
                    location.href = `/plugin/${val}/news_detail.html?id=${id}`
                },
                goGoodsList() {
                    location.href = `/goodsList.html`
                },
                goProductPage(id) {
                    location.href = `/productdetail.html?id=${id}`
                },
                initData() {
                    const arr = this.addons_js_arr.map((item) => {
                        return item.name
                    })
                    if (arr.includes('IdcsmartCertification')) {
                        certificationInfo().then((res) => {
                            this.certificationObj = res.data.data
                        })
                    }
                    if (arr.includes('IdcsmartTicket')) {
                        ticket_list({ page: 1, limit: 3 }).then((res) => {
                            this.ticketList = res.data.data.list
                            this.ticketList.forEach((item) => {
                                switch (item.status) {
                                    case 'Pending':
                                        item.statusText = '待接受'
                                        break;
                                    case 'Handling':
                                        item.statusText = '处理中'
                                        break;
                                    case 'Reply':
                                        item.statusText = '待回复'
                                        break;
                                    case 'Replied':
                                        item.statusText = '已回复'
                                        break;
                                    case 'Resolved':
                                        item.statusText = '已解决'
                                        break;
                                    case 'Closed':
                                        item.statusText = '已关闭'
                                        break;
                                    default: ''
                                        item.statusText = '--'
                                        break;
                                }
                            })

                        })
                    }
                    if (arr.includes('IdcsmartNews')) {
                        newsList({ page: 1, limit: 3 }).then((res) => {
                            this.homeNewList = res.data.data.list.slice(0, 3)
                        })
                    }
                    if (arr.includes('IdcsmartRecommend')) {
                        this.showRight = true
                        this.getPromoterInfo();
                    }
                    this.nameLoading = true
                    indexData().then((res) => {
                        this.account = res.data.data.account
                        this.account.firstName = res.data.data.account.username.substring(0, 1)
                        this.percentage = Number(this.account.this_month_consume) / Number(this.account.consume) * 100
                        if (sessionStorage.headBgc) {
                            this.$refs.headBoxRef.style.background = sessionStorage.headBgc
                        } else {
                            const index = Math.round(Math.random() * this.headBgcList.length)
                            this.$refs.headBoxRef.style.background = this.headBgcList[index]
                            sessionStorage.headBgc = this.headBgcList[index]
                        }
                        this.nameLoading = false
                    })

                    indexHost({ page: 1 }).then((res) => {
                        this.productListLoading = false
                        this.productList = res.data.data.list
                        const data = new Date().getTime() * 0.001
                        this.productList.forEach((item) => {
                            if ((item.due_time - data) / (60 * 60 * 24) <= 10) {
                                item.isOverdue = true
                            } else {
                                item.isOverdue = false
                            }
                        })
                    }).catch(() => {
                        this.productListLoading = false
                    })

                    // promoter_statistic().then((res) => {
                    //     console.log(res);
                    // })
                },
                // 获取支付方式列表
                getGateway() {
                    gatewayList().then(res => {
                        if (res.data.status === 200) {
                            this.gatewayList = res.data.data.list
                        }
                    })
                },
                goUser() {
                    location.href = `account.html`
                },
                // 返回两位小数
                oninput(value) {
                    let str = value;
                    let len1 = str.substr(0, 1);
                    let len2 = str.substr(1, 1);
                    //如果第一位是0，第二位不是点，就用数字把点替换掉
                    if (str.length > 1 && len1 == 0 && len2 != ".") {
                        str = str.substr(1, 1);
                    }
                    //第一位不能是.
                    if (len1 == ".") {
                        str = "";
                    }
                    if (len1 == "+") {
                        str = "";
                    }
                    if (len1 == "-") {
                        str = "";
                    }
                    //限制只能输入一个小数点
                    if (str.indexOf(".") != -1) {
                        let str_ = str.substr(str.indexOf(".") + 1);
                        if (str_.indexOf(".") != -1) {
                            str = str.substr(0, str.indexOf(".") + str_.indexOf(".") + 1);
                        }
                    }
                    //正则替换
                    str = str.replace(/[^\d^\.]+/g, ""); // 保留数字和小数点
                    str = str.replace(/^\D*([0-9]\d*\.?\d{0,2})?.*$/, "$1"); // 小数点后只能输 2 位
                    return str;

                },
                // 显示充值 dialog
                showCz() {
                    // 初始化弹窗数据
                    this.czData = {
                        amount: "",
                        gateway: this.gatewayList[0] ? this.gatewayList[0].name : "",
                    }
                    this.czDataOld = {
                        amount: "",
                        gateway: ""
                    }
                    this.errText = ""
                    this.isShowCz = true
                    this.payLoading1 = false
                    this.payHtml = ""
                },
                // 充值金额变化时触发
                czInputChange() {
                    let data = this.czData
                    let isPass = true
                    if (!data.gateway) {
                        this.errText = "请选择充值方式"
                        isPass = false
                    }
                    if (!data.amount) {
                        this.errText = "请输入充值金额"
                        isPass = false
                    }

                    if (this.czData.amount == this.czDataOld.amount && this.czData.gateway == this.czDataOld.gateway) {
                        isPass = false
                    }

                    if (isPass) {
                        this.errText = ""
                        // 调用充值接口
                        const params = {
                            amount: Number(data.amount),
                            gateway: data.gateway
                        }
                        this.doRecharge(params)
                    }
                },
                // 充值方式变化时触发
                czSelectChange() {
                    let data = this.czData
                    let isPass = true
                    if (!data.gateway) {
                        this.errText = "请选择充值方式"
                        isPass = false
                    }
                    if (!data.amount) {

                        isPass = false
                    }
                    if (isPass) {
                        this.errText = ""
                        // 调用充值接口
                        const params = {
                            amount: Number(data.amount),
                            gateway: data.gateway
                        }
                        this.doRecharge(params)
                    }
                },
                // 充值dialog 关闭
                czClose() {
                    this.isShowCz = false
                    clearInterval(this.timer)
                    this.time = 300000
                },
                // 充值
                doRecharge(params) {
                    this.payLoading1 = true
                    this.isShowimg1 = true
                    this.czDataOld = { ...this.czData }
                    recharge(params).then(res => {
                        if (res.data.status === 200) {
                            const orderId = res.data.data.id
                            const gateway = params.gateway
                            // 调用支付接口
                            pay({ id: orderId, gateway }).then(res => {
                                this.payLoading1 = false
                                this.isShowimg1 = true
                                this.errText = ""
                                if (res.data.status === 200) {
                                    this.payHtml = res.data.data.html
                                    console.log(this.payHtml);
                                    // 轮询支付状态
                                    this.pollingStatus(orderId)
                                }
                            }).catch(error => {
                                this.payLoading1 = false
                                this.isShowimg1 = false
                                this.errText = error.data.msg

                            })
                        }
                    }).catch(error => {
                        // 显示错误信息
                        this.errText = error.data.msg
                        // 关闭loading
                        this.payLoading1 = false
                        // 第三方支付
                        this.payHtml = ""
                    })
                },
                // 轮循支付状态
                pollingStatus(id) {
                    if (this.timer) {
                        clearInterval(this.timer)
                    }
                    this.timer = setInterval(async () => {
                        const res = await getPayStatus(id);
                        this.time = this.time - 2000
                        if (res.data.code === "Paid") {
                            this.$message.success(res.data.msg)
                            clearInterval(this.timer);
                            this.time = 300000
                            this.isShowCz = false
                            this.isShowZf = false
                            indexData().then((res) => {
                                this.account = res.data.data.account
                                this.account.firstName = res.data.data.account.username.substring(0, 1)
                                this.percentage = (Number(this.account.this_month_consume) / Number(this.account.consume)) * 100
                            })
                            return false
                        }
                        if (this.time === 0) {
                            clearInterval(this.timer);
                            // 关闭充值 dialog
                            this.isShowCz = false
                            this.isShowZf = false
                            this.$message.error("支付超时")
                        }
                    }, 2000)
                },
                // 获取通用配置
                getCommonData() {
                    this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
                    document.title = this.commonData.website_name + '-首页'

                },
                // 获取推广者基础信息
                getPromoterInfo() {
                    promoterInfo().then(res => {
                        if (res.data.status == 200) {
                            this.promoterData = res.data.data.promoter
                            if (JSON.stringify(this.promoterData) == '{}') {
                                this.isOpen = false
                            } else {
                                this.isOpen = true
                            }
                        }
                    })
                },
                // 开启推介计划
                openReferral() {
                    openRecommend().then(res => {
                        if (res.data.status == 200) {
                            this.$message.success(res.data.msg)
                            this.getPromoterInfo()
                            this.openVisible = false
                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                    })
                },
                // 复制
                copyUrl(text) {
                    if (navigator.clipboard && window.isSecureContext) {
                        // navigator clipboard 向剪贴板写文本
                        this.$message.success('复制成功')
                        return navigator.clipboard.writeText(text)
                    } else {
                        // 创建text area
                        const textArea = document.createElement('textarea')
                        textArea.value = text
                        // 使text area不在viewport，同时设置不可见
                        document.body.appendChild(textArea)
                        // textArea.focus()
                        textArea.select()
                        this.$message.success('复制成功')
                        return new Promise((res, rej) => {
                            // 执行复制命令并移除文本框
                            document.execCommand('copy') ? res() : rej()
                            textArea.remove()
                        })
                    }
                },
            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
