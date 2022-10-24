(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        location.href = 'home.html'
        const template = document.getElementById('account')
        Vue.prototype.lang = window.lang
        new Vue({
            created() {

                this.getCommon();
                this.getGateway();
                this.getAccount();
                this.inviteState();
            },
            mounted() {
                // 关闭loading
                // document.getElementById('mainLoading').style.display = 'none';
            },
            updated() {
                // 关闭loading
                document.getElementById('mainLoading').style.display = 'none';
                document.getElementsByClassName('template')[0].style.display = 'block'
            },
            components: {
                aliAsideMenu,
                topMenu,
            },
            data() {
                return {
                    email: "",
                    isShowText: false,
                    isShow: false,
                    btnLoading: false,
                    money: [
                        { id: 0, num: "500.00" },
                        { id: 1, num: "1000.00" },
                        { id: 2, num: "5000.00" },
                        { id: 3, num: "10000.00" },
                    ],
                    imgLoading: true,
                    activeId: 0,
                    commonData: {},
                    accountData: {},
                    errText: "",
                    isShowCz: false,
                    timer: null,
                    // 充值弹窗表单数据
                    czData: {
                        amount: "500.00",
                        gateway: '',
                    },
                    inputValue: null,
                    payHtml: "",
                    // 支付方式
                    gatewayList: [],
                    orderId: null,
                    aliInviteState: 0
                }
            },

            methods: {
                // 获取通用信息
                getCommon() {
                    common().then(res => {
                        if (res.data.status === 200) {
                            this.commonData = res.data.data
                            document.title = this.commonData.website_name + '-首页'
                        }
                    })
                },
                // 获取邀请状态
                inviteState() {
                    getInviteState().then(res => {
                        if (res.data.status == undefined) {
                            this.aliInviteState = 1
                        } else {
                            this.aliInviteState = res.data.status
                        }
                        if (res.data.status === 1) {
                            this.isShow = false
                        } else {
                            this.isShow = true
                        }
                        this.isShowText = true
                    })
                },
                // 获取账户信息
                getAccount() {
                    account().then(res => {
                        if (res.data.status === 200) {
                            console.log(res);
                            this.accountData = res.data.data.account
                            this.email = this.accountData.email
                        }
                    })
                },
                // 前往充值记录列表
                toPageList() {
                    location.href = 'aliAccount.html'
                },
                // 获取邀请
                gitInvite() {
                    if (!this.email) {
                        this.$message.error("请输入邮箱")
                        return false
                    } else {
                        if (this.email.search(
                            /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/
                        ) === -1) {
                            this.$message.error("请输入正确的邮箱")
                            return false
                        }
                    }

                    this.btnLoading = true
                    // 调用接口获取邀请
                    const params = {
                        email: this.email
                    }
                    invite(params).then(res => {
                        this.btnLoading = false
                        if (res.data.status === 200) {
                            this.email = ""
                            this.$message.success("获取邀请成功")
                            this.inviteState();
                            this.getAccount();
                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                    })
                },
                // 显示充值弹窗
                showCz() {
                    this.czData.gateway = this.gatewayList[0].name
                    // 调阿里充值 获取订单ID
                    this.doAlRecharge()
                },
                // 充值dialog 关闭
                czClose() {
                    clearInterval(this.timer)
                    this.time = 300000
                },
                // 获取支付方式列表
                getGateway() {
                    gatewayList().then(res => {
                        if (res.data.status === 200) {
                            this.gatewayList = res.data.data.list
                        }
                    })
                },
                doAlRecharge() {
                    this.imgLoading = true
                    const params = {
                        amount: this.czData.amount ? this.czData.amount : this.money[this.activeId].num,
                        gateway: this.czData.gateway
                    }
                    recharge(params).then(res => {
                        if (res.data.status === 200) {

                            // 获取订单id
                            this.orderId = res.data.data.id
                            this.isShowCz = true
                            // 调用支付接口
                            console.log(this.czData.gateway);

                            pay({ id: this.orderId, gateway: this.czData.gateway }).then(res => {
                                this.imgLoading = false
                                console.log(res);
                                const payDom = document.getElementsByClassName("pay-html")[0]
                                // payDom.innerHTML = 
                                $(".pay-html").html(res.data.data.html)
                                // console.log(payDom[0]);
                                // payDom[0].appendChild(res.data.data.html)
                                // payDom.replaceChild(res.data.data.html,payDom)
                                // console.log(payDom);
                                //   this.payHtml = res.data.data.html
                                // console.log(this.payHtml);
                                // 轮询支付状态
                                this.pollingStatus(this.orderId)
                            }).catch(error => {
                                this.$message.error(error.data.msg)
                            })

                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
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
                            this.$message.success("支付成功")
                            clearInterval(this.timer);
                            this.time = 300000
                            this.isShowCz = false
                            this.getCreditList()
                            this.getorderList()
                            return false
                        }
                        if (this.time === 0) {
                            clearInterval(this.timer);
                            // 关闭充值 dialog
                            this.isShowCz = false
                            this.$message.error("支付超时")
                        }
                    }, 2000)
                },
                // 充值方式变化时触发
                czSelectChange() {
                    let data = this.czData
                    // 调用充值接口
                    const params = {
                        amount: Number(data.amount),
                        gateway: data.gateway
                    }
                    this.doAlRecharge(params)

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

                }
            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
