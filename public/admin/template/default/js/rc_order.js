(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('re-order')[0]
        Vue.prototype.lang = window.lang
        Vue.prototype.moment = window.moment
        new Vue({
            data() {
                const cycleValidator = (val => {
                    if (!this.productionForm.cycle_min && !this.productionForm.cycle_max) {
                        return { result: false, message: "请输入周期", type: 'error' };
                    }
                    if (this.productionForm.cycle_max < this.productionForm.cycle_min) {
                        return { result: false, message: "最大周期不能小于最小周期", type: 'error' };
                    }
                    return { result: true };
                })
                return {
                    maxHeight: '',
                    urlPath: url,
                    currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥',
                    currency_suffix: JSON.parse(localStorage.getItem('common_set')).currency_suffix || '元',
                    staticsticsData: {},
                    clientData: [],
                    params: {
                        keywords: "",
                        client_id: "",
                        page: 1,
                        limit: 20,
                        orderby: "id",
                        sort: "desc"
                    },
                    total: 0,
                    clientParams: {
                        page: 1,
                        limit: 1000,
                        orderby: "id",
                        sort: "desc"
                    },
                    pageSizeOptions: [20, 50, 100],
                    loading: false,
                    options: [],
                    dataList: [],
                    dataLoading: false,
                    columns: [
                        {
                            colKey: "id",
                            title: "ID",
                            width: 125,
                        },
                        {
                            colKey: "product_name",
                            title: "商品信息",
                            ellipsis: true,
                        },
                        {
                            colKey: "code",
                            title: "房箱识别码",
                            ellipsis: true,
                        },
                        {
                            colKey: "buy_amount",
                            title: "购买价格",
                            ellipsis: true,
                        },
                        {
                            colKey: "username",
                            title: "购买用户",
                            ellipsis: true,
                        },
                        {
                            colKey: "create_time",
                            title: "下单时间",
                            ellipsis: true,
                        },
                        {
                            colKey: "cycle_min",
                            title: "预计交付周期",
                            ellipsis: true,
                        },
                        {
                            colKey: "distribution",
                            title: "配送信息",
                            width: 200,
                        },
                        {
                            colKey: "status",
                            title: "状态",
                            width: 200,
                            ellipsis: true,
                        },
                        {
                            colKey: "operation",
                            title: "操作"
                        }
                    ],
                    stataus: {
                        Unpaid: "待支付首付款",
                        Ordered: "已下单",
                        Production: "生产中",
                        FinalUnpaid: "待支付尾款",
                        Delivery: "待交付",
                        Delivered: "已交付",
                        Cancelled: "已取消",
                    },
                    visible: false,
                    header: "",
                    sureType: "",
                    orderId: null,
                    productionHead: "",
                    productionVisible: false,
                    productionType: "",
                    productionForm: {
                        id: null,
                        cycle_min: 0,
                        cycle_max: 0
                    },
                    productionRules: {
                        cycle: [
                            { validator: cycleValidator, trigger: 'blur' },
                        ]
                    },
                    deliveryVisible: false,
                    deliveryForm: {
                        id: null,
                        logistic: ""
                    },
                    deliveryRules: {
                        logistic: [
                            { required: true, message: "请输入物流信息" }
                        ]
                    }
                }
            },
            mounted() {
                this.maxHeight = document.getElementById('content').clientHeight - 150
                let timer = null
                window.onresize = () => {
                    if (timer) {
                        return
                    }
                    timer = setTimeout(() => {
                        this.maxHeight = document.getElementById('content').clientHeight - 150
                        clearTimeout(timer)
                        timer = null
                    }, 300)
                }
            },
            watch: {

            },
            methods: {
                // 获取统计数据
                getStaticstics() {
                    staticstics().then(res => {
                        if (res.data.status == 200) {
                            this.staticsticsData = res.data.data
                        }
                    })
                },
                // 切换分页
                changePage(e) {
                    this.params.page = e.current;
                    this.params.limit = e.pageSize;
                    this.getData()
                },
                remoteMethod(search) {
                    this.loading = true;
                    const params = {
                        ...this.clientParams,
                        keywords: search
                    }
                    clientList(params).then(res => {
                        this.loading = false;
                        if (res.data.status == 200) {
                            ;
                            const list = res.data.data.list
                            const options = []
                            list.map(item => {

                                options.push({
                                    value: item.id,
                                    label: item.username
                                })
                            })
                            this.options = options
                        }

                    }).catch(error => {
                        this.loading = false;
                    })
                },
                getClient() {
                    this.loading = true;
                    const params = {
                        ...this.clientParams
                    }
                    clientList(params).then(res => {
                        this.loading = false
                        if (res.data.status == 200) {
                            const list = res.data.data.list
                            const options = []
                            list.map(item => {
                                options.push({
                                    value: item.id,
                                    label: item.username
                                })
                            })
                            this.options = options
                        }
                    }).catch(error => {
                        this.loading = false
                    })
                },
                // 订单列表
                getData() {
                    this.dataLoading = true
                    orderList(this.params).then(res => {
                        this.dataLoading = false
                        if (res.data.status == 200) {
                            this.total = res.data.data.count
                            this.dataList = res.data.data.list
                        }
                    }).catch((error) => {
                        this.dataLoading = false
                    })
                },
                search() {
                    this.params.page = 1
                    this.getData()
                },
                copyMsg(text) {
                    if (navigator.clipboard && window.isSecureContext) {
                        // navigator clipboard 向剪贴板写文本
                        this.$message.success("复制成功");
                        return navigator.clipboard.writeText(text);
                    } else {
                        // 创建text area
                        const textArea = document.createElement("textarea");
                        textArea.value = text;
                        // 使text area不在viewport，同时设置不可见
                        document.body.appendChild(textArea);
                        textArea.focus();
                        textArea.select();
                        this.$message.success("复制成功");
                        return new Promise((res, rej) => {
                            // 执行复制命令并移除文本框
                            document.execCommand("copy") ? res() : rej();
                            textArea.remove();
                        });
                    }
                },
                sure() {
                    if (this.sureType == 'finish') {
                        this.doFinish()
                    }
                    if (this.sureType == "failPaid") {
                        this.doFailPaid()
                    }
                    if (this.sureType == 'paid') {
                        this.doPaid()
                    }
                    this.visible = false
                },
                showSure(type, id) {
                    this.sureType = type
                    this.orderId = id
                    if (type == 'finish') {
                        this.header = "确认生产完成"
                    }
                    if (type == 'failPaid') {
                        this.header = "确认已付尾款"
                    }
                    if (type == 'paid') {
                        this.header = "确认已支付"
                    }
                    this.visible = true
                },
                // 生产完成
                doFinish() {
                    const params = {
                        id: this.orderId
                    }
                    finish(params).then(res => {
                        if (res.data.status == 200) {
                            this.$message.success(res.data.msg)
                            this.getData()
                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                    })
                },
                // 已付尾款
                doFailPaid() {
                    const params = {
                        id: this.orderId
                    }
                    failPaid(params).then(res => {
                        if (res.data.status == 200) {
                            this.$message.success(res.data.msg)
                            this.getData()
                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                    })
                },
                // 已支付
                doPaid() {
                    const params = {
                        id: this.orderId
                    }
                    paid(params).then(res => {
                        if (res.data.status == 200) {
                            this.$message.success(res.data.msg)
                            this.getData()
                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                    })
                },
                showProduction(type, row) {
                    console.log(row);
                    this.orderId = row.id
                    this.productionForm.id = row.id
                    this.productionType = type
                    if (type == 'edit') {
                        this.productionHead = "修改预计周期"
                        this.productionForm.cycle_min = Number(row.cycle_min)
                        this.productionForm.cycle_max = Number(row.cycle_max)
                    } else {
                        this.productionHead = "开始生产"
                        this.productionForm.cycle_min = Number(row.cycle_min)
                        this.productionForm.cycle_max = Number(row.cycle_max)
                    }
                    this.productionVisible = true

                },
                productionSub({ validateResult, firstError }) {
                    if (validateResult === true) {
                        const params = {
                            ...this.productionForm
                        }
                        if (this.productionType == 'edit') {
                            // 修改周期
                            editCycle(params).then(res => {
                                if (res.data.status == 200) {
                                    this.$message.success(res.data.msg)
                                    this.getData()
                                    this.productionVisible = false
                                }
                            }).catch((error) => {
                                this.$message.error(error.data.msg)
                            })
                        } else {
                            // 开始生产
                            beginProduction(params).then(res => {
                                if (res.data.status == 200) {
                                    this.$message.success(res.data.msg)
                                    this.getData()
                                    this.productionVisible = false
                                }
                            }).catch((error) => {
                                this.$message.error(error.data.msg)
                            })
                        }
                    } else {
                        this.$message.warning(firstError);
                    }
                },
                showDelivery(id) {
                    this.deliveryForm.id = id
                    this.deliveryForm.logistic = ""
                    this.deliveryVisible = true
                },
                deliverySub({ validateResult, firstError }) {
                    if (validateResult === true) {
                        const params = {
                            ...this.deliveryForm
                        }
                        delivery(params).then(res => {
                            if (res.data.stataus == 200) {
                                this.$message.success(res.data.msg)
                                this.getData()
                                this.deliveryVisible = false
                            }
                        }).catch(error => {
                            this.$message.error(error.data.msg)
                        })
                    } else {
                        this.$message.warning(firstError);
                    }
                },
                rowClick(e) {
                    location.href = `rc_order_details.html?id=${e.row.id}`;
                },
                stopPop(event) {
                    event.stopPropagation()
                }
            },
            created() {
                this.getStaticstics()
                this.getClient()
                this.getData()
            }
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
