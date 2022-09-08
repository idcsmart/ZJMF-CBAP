(function (window, undefined) {
    var old_onload = window.onload;
    window.onload = function () {
        const finance = document.getElementById("finance");
        Vue.prototype.lang = window.lang;
        new Vue({
            components: {
                asideMenu,
                topMenu,
                pagination,
                payDialog,
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
            data() {
                return {
                    // 交易记录 是否展示订单详情
                    isDetail: false,
                    // 后台返回的支付html
                    payHtml: "",
                    // 轮询相关
                    timer: null,
                    balanceTimer: null,
                    time: 300000,
                    // 支付方式
                    gatewayList: [],
                    // 错误提示信息
                    errText: "",
                    // 待审核金额
                    unAmount: 0,
                    commonData: {},
                    // 货币前缀
                    currency_prefix: "",
                    // 货币后缀
                    currency_code: "",
                    // 用户余额
                    balance: "",
                    // 支付弹窗相关 开始
                    // 支付弹窗控制
                    isShowZf: false,
                    // 是否展示第三方支付
                    isShowPay: true,
                    zfData: {
                        // 订单id
                        orderId: 0,
                        // 订单金额
                        amount: 0,
                        checked: false,
                        // 支付方式
                        gateway: gatewayList.length > 0 ? gatewayList[0].name : ''
                    },

                    // 支付弹窗相关结束
                    // 是否显示提现弹窗
                    isShowTx: false,
                    // 是否显示充值弹窗
                    isShowCz: false,
                    // 充值弹窗表单数据
                    czData: {
                        amount: "",
                        gateway: '',
                    },
                    czDataOld: {
                        amount: "",
                        gateway: '',
                    },
                    // 提现弹窗表单数据
                    txData: {
                        method: "alipay",
                        account: "",
                        card_number: "",
                        name: "",
                        amount: ""
                    },
                    // 余额记录列表
                    balanceType: {
                        // Artificial: { text: "人工", color: "geekblue" },
                        Recharge: { text: "充值" },
                        Applied: { text: "扣费" },
                        // Overpayment: { text: "超付" },
                        // Underpayment: { text: "少付" },
                        Refund: { text: "退款" },
                        Withdraw: { text: "提现" }
                    },
                    loading1: false,
                    loading2: false,
                    loading3: false,
                    loading4: false,
                    dataList1: [],
                    dataList2: [],
                    dataList3: [],
                    dataList4: [],
                    timerId1: null,
                    timerId2: null,
                    timerId3: null,
                    params1: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: "id",
                        sort: "desc",
                        keywords: "",
                    },
                    params2: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: "id",
                        sort: "desc",
                        keywords: "",
                    },
                    params3: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: "id",
                        sort: "desc",
                        keywords: "",
                    },
                    activeIndex: "1",
                    // 订单类型
                    tipslist1: [
                        {
                            color: "#0058FF",
                            name: "新订单",
                            value: "new",
                        },
                        {
                            color: "#3DD598",
                            name: "续费订单",
                            value: "renew",
                        },
                        {
                            color: "#F0142F ",
                            name: "升降级订单",
                            value: "upgrade",
                        },
                        {
                            color: "#F99600 ",
                            name: "人工订单",
                            value: "artificial",
                        },
                    ],
                    // 订单详情 产品状态
                    status: {
                        Unpaid: "未付款",
                        Pending: "开通中",
                        Active: "使用中",
                        Suspended: "暂停",
                        Deleted: "删除",
                        Failed: "开通失败"
                    },
                    // 提现规则
                    ruleData: {

                    },
                    // 提现方式
                    txway: {

                    },
                    payLoading: false,
                    isShowimg: true,
                    payLoading1: false,
                    isShowimg1: true
                };
            },
            created() {
                // 订单记录列表
                this.getorderList();
                this.getCommon();
                this.getAccount();
                this.getGateway();
                this.getUnAmount();
                this.getWithdrawRule()
            },
            watch: {},
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
                //获取订单列表
                getorderList() {
                    this.loading1 = true
                    orderList(this.params1).then((res) => {
                        if (res.data.status === 200) {
                            this.params1.total = res.data.data.count;
                            let list = res.data.data.list;

                            list.map(item => {
                                let product_name = ""
                                // 商品名称 含两个以上的 只取前两个拼接然后拼接上商品名称的个数
                                if (item.product_names.length > 2) {
                                    product_name = item.product_names[0] + "、" + item.product_names[1] + " " + '等' + item.product_names.length + '个商品'
                                } else {
                                    item.product_names.map(n => {
                                        product_name += n + "、"
                                    })
                                    product_name = product_name.slice(0, -1)
                                }
                                item.product_name = product_name

                                // 判断有无子数据
                                if (item.order_item_count > 1) {
                                    // item.children = []
                                    item.hasChildren = true
                                }
                            })

                            this.dataList1 = list;
                            console.log(this.dataList1);
                        }
                        this.loading1 = false
                    });
                },
                // 获取交易记录列表
                getTransactionList() {
                    this.loading2 = true
                    transactionList(this.params2).then(res => {
                        if (res.data.status === 200) {
                            let list = res.data.data.list
                            if (list) {
                                list.map(item => {
                                    if (item.order_id == 0) {
                                        item.order_id = "--"
                                    }
                                })
                            }
                            this.dataList2 = list
                            this.params2.total = res.data.data.count
                        }
                        this.loading2 = false
                    })
                },
                // 获取余额记录列表
                getCreditList() {
                    this.loading3 = true
                    creditList(this.params3).then(res => {
                        if (res.data.status === 200) {
                            let list = res.data.data.list
                            // 过滤人工订单 不显示
                            list = list.filter(item => {
                                return item.type !== "Artificial"
                            })
                            console.log(list);
                            this.dataList3 = list
                            this.params3.total = res.data.data.count
                        }
                        this.loading3 = false
                    })
                },
                // 获取订单详情
                getOrderDetailsList(id) {
                    this.loading4 = true
                    orderDetails(id).then(res => {
                        if (res.data.status === 200) {
                            let data = res.data.data.order
                            let item = data.items
                            let product_name = ""
                            if (item) {
                                item.map(n => {
                                    if (n.product_name) {
                                        product_name += n.product_name + "、"
                                    }
                                })
                            }
                            data.product_name = product_name.slice(0, -1)
                            let order = []
                            order.push(data)
                            this.dataList4 = order
                        }
                        this.loading4 = false
                    })
                },
                // 获取支付方式列表
                getGateway() {
                    gatewayList().then(res => {
                        if (res.data.status === 200) {
                            this.gatewayList = res.data.data.list
                            console.log(this.gatewayList);
                        }
                    })
                },
                // tab点击事件
                handleClick() {
                    if (this.activeIndex == 1) {
                        // 订单记录
                        this.getorderList()
                    }
                    if (this.activeIndex == 2) {
                        // 交易记录
                        this.getTransactionList()
                    }
                    if (this.activeIndex == 3) {
                        // 余额记录
                        this.getCreditList(3)
                    }
                },
                sizeChange1(e) {
                    this.params1.limit = e;
                    this.getorderList();
                },
                sizeChange2(e) {
                    this.params2.limit = e;
                    this.getTransactionList();
                },
                sizeChange3(e) {
                    this.params3.limit = e;
                    this.getCreditList();
                },

                currentChange1(e) {
                    this.params1.page = e;
                    this.getorderList();
                },
                currentChange2(e) {
                    this.params2.page = e;
                    this.getTransactionList();
                },
                currentChange3(e) {
                    this.params3.page = e;
                    this.getCreditList();
                },
                // 订单记录 搜索框事件
                inputChange1() {
                    // if (this.timerId1) {
                    //     clearTimeout(this.timerId1)
                    // }
                    // this.timerId1 = setTimeout(() => {
                    this.params1.page = 1
                    this.getorderList()
                    // }, 500)
                },
                // 交易记录 搜索框事件
                inputChange2() {
                    // if (this.timerId2) {
                    //     clearTimeout(this.timerId2)
                    // }
                    // this.timerId2 = setTimeout(() => {
                    this.params2.page = 1
                    this.getTransactionList()
                    // }, 500)
                },
                // 余额记录 搜索框事件
                inputChange3() {
                    // if (this.timerId3) {
                    //     clearTimeout(this.timerId3)
                    // }
                    // this.timerId3 = setTimeout(() => {
                    this.params3.page = 1
                    this.getCreditList()
                    // }, 500)
                },
                // 获取通用配置
                getCommon() {
                    common().then(res => {
                        if (res.data.status === 200) {
                            this.commonData = res.data.data
                            document.title = this.commonData.website_name + '-财务信息'
                            this.currency_prefix = res.data.data.currency_prefix
                            this.currency_code = res.data.data.currency_code
                        }
                    })
                },
                // 获取账户详情
                getAccount() {
                    account().then(res => {
                        if (res.data.status === 200) {
                            this.balance = res.data.data.account.credit
                        }
                    })
                },
                // 获取待审核金额
                getUnAmount() {
                    unAmount().then(res => {
                        if (res.data.status === 200) {
                            this.unAmount = res.data.data.amount
                        }
                    })
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
                // 显示提现 dialog
                showTx() {
                    // 初始化提现弹窗数据
                    this.txData = {
                        method: "alipay",
                        account: "",
                        card_number: "",
                        name: "",
                        amount: ""
                    }
                    // 清空错误信息
                    this.errText = ""
                    this.isShowTx = true
                },
                // 申请提现 提交
                doCredit() {
                    let isPass = true
                    const data = this.txData
                    if (data.method === "alipay") {   // 支付宝 提现
                        if (!data.account) {
                            isPass = false
                            this.errText = "请输入支付宝账号"
                        }
                    }

                    if (data.method === "bank") { // 银行卡 提现
                        if (!data.card_number) {
                            isPass = false
                            this.errText = "请输入银行卡号"
                        }
                        if (!data.name) {
                            isPass = false
                            this.errText = "请输入银行卡持有人姓名"
                        }
                    }

                    if (!data.amount) {
                        isPass = false
                        this.errText = "请输入提现金额"
                    }

                    if (isPass) {
                        // 清空错误信息
                        this.errText = ""
                        const params = {
                            source: "credit",
                            method: data.method,
                            amount: Number(data.amount),
                            card_number: data.card_number,
                            name: data.name,
                            account: data.account
                        }
                        withdraw(params).then(res => {
                            if (res.data.status === 200) {
                                this.$message.success("提现申请成功");
                                this.isShowTx = false
                                // 重新拉取余额记录
                                this.getCreditList()
                                // 重新拉取当前余额
                                this.getAccount();
                                // 重新拉取待退款金额
                                this.getUnAmount();
                            }
                        }).catch(error => {
                            this.errText = error.data.msg
                        })
                    }

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
                        this.errText = "请输入充值金额"
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
                            this.getCreditList()
                            this.getorderList()
                            this.getAccount()
                            this.getUnAmount()
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
                // 订单记录订单详情
                load(tree, treeNode, resolve) {
                    // 获取订单详情
                    // console.log(tree);
                    const id = tree.id
                    orderDetails(id).then(res => {
                        if (res.data.status === 200) {
                            let resData = res.data.data.order.items
                            resolve(resData)
                        }
                    })
                    // this.getOrderDetailsList(id)
                },
                // 交易记录订单详情
                rowClick(orderId) {
                    this.isDetail = true
                    // const id = row.order_id
                    this.getOrderDetailsList(orderId)
                },
                // 支付弹窗相关
                // 点击去支付
                showPayDialog(row) {
                    if (this.timer) {  // 清除定时器
                        clearInterval(this.timer)
                    }
                    this.errText = ""
                    // 默认不使用余额
                    this.zfData.checked = false
                    // 重置支付倒计时5分钟
                    this.time = 300000
                    // 获取余额
                    this.getAccount()
                    // 获取订单金额 和 订到id
                    this.zfData.orderId = Number(row.id)
                    this.zfData.amount = Number(row.amount)
                    // 展示支付 dialog
                    this.isShowZf = true
                    // 默认拉取第一种支付方式
                    this.zfData.gateway = this.gatewayList[0].name
                    this.zfSelectChange()
                    // 轮询支付
                    this.pollingStatus(this.zfData.orderId)
                },
                // 支付方式切换
                zfSelectChange() {
                    this.payLoading = true
                    this.isShowimg = true
                    const balance = Number(this.balance)
                    const money = Number(this.zfData.amount)
                    // 余额大于等于支付金额 且 勾选了使用余额
                    if ((balance >= money) && this.zfData.checked) {
                        return false
                    }
                    // 获取第三方支付
                    const params = {
                        gateway: this.zfData.gateway,
                        id: this.zfData.orderId
                    }
                    pay(params).then(res => {
                        this.errText = ""
                        this.payLoading = false
                        this.payHtml = res.data.data.html
                    }).catch(error => {
                        this.isShowimg = false
                        this.payLoading = false
                        this.errText = error.data.msg
                    })
                },
                // 使用余额
                useBalance() {
                    this.getAccount()
                    if (this.balanceTimer) {
                        clearTimeout(this.balanceTimer)
                    }
                    this.balanceTimer = setTimeout(() => {
                        creditPay({
                            id: this.zfData.orderId,
                            use: this.zfData.checked ? 1 : 0
                        }).then(res => {
                            // 新的订单id
                            const tempId = res.data.data.id;
                            this.zfData.orderId = tempId
                            // 获取新订单的详情
                            orderDetails(tempId).then(result => {
                                const orderRes = result.data.data.order;
                                if (this.zfData.checked) {    //使用余额
                                    if (Number(this.balance) >= Number(orderRes.amount)) {
                                        this.errText = ""
                                        this.isShowPay = false
                                    } else {
                                        // 账户余额小于 订单金额 重新拉取第三方支付并显示
                                        this.isShowPay = true
                                        this.zfSelectChange();
                                    }
                                } else { // 取消使用余额
                                    if (Number(this.balance) >= Number(orderRes.amount)) {
                                        this.errText = ""
                                        this.isShowPay = true
                                    } else {
                                        // 账户余额小于 订单金额 重新拉取第三方支付并显示
                                        this.isShowPay = true
                                        this.zfSelectChange();
                                    }
                                }
                            })
                        }).catch(error => {
                            this.errText = error.data.msg
                        })
                    }, 500)



                },
                // 获取提现规则
                getWithdrawRule() {
                    const params = {
                        source: "credit"
                    }
                    withdrawRule(params).then(res => {
                        if (res.data.status === 200) {
                            this.ruleData = res.data.data.rule
                        }
                    })
                },
                // 充值关闭
                zfClose() {
                    this.isShowZf = false
                    this.isShowPay = true
                    clearInterval(this.timer)
                    this.time = 300000
                    if (this.zfData.checked) {  // 如果勾选了使用余额
                        this.zfData.checked = false
                        // 取消使用余额
                        const params = {
                            id: this.zfData.orderId,
                            use: 0
                        }
                        creditPay(params).then(res => { }).catch(error => { })
                    }
                },
                // 确认使用余额支付
                handleOk() {
                    console.log("确认使用余额支付");
                    const params = {
                        gateway: this.zfData.gateway,
                        id: this.zfData.orderId
                    }
                    pay(params).then(res => { }).catch(error => { })
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
        }).$mount(finance);
        typeof old_onload == "function" && old_onload();
    };
})(window);
