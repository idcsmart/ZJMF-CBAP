(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('re-order-details')[0]
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
                    currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥',
                    maxHeight: '',
                    urlPath: url,
                    id: 0,
                    data: {
                        newDescription: []
                    },
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
                getDetails() {
                    const params = {
                        id: this.id
                    }
                    orderDetails(params).then(res => {
                        if (res.data.status == 200) {
                            const data = res.data.data.room_box
                            data.newDescription = this.analysisDescription(data.description)
                            this.data = data
                            console.log(this.data);
                        }
                    }).catch((error) => {
                        this.$message.error(error.data.msg)
                    })
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
                        this.productionForm.cycle_min = 0
                        this.productionForm.cycle_max = 0
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
                                if (res.data.stataus == 200) {
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
                                if (res.data.stataus == 200) {
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

                analysisDescription(description) {
                    if (!description) {
                        return []
                    }
                    const result = new Array()
                    const arr1 = description.split('\n')
                    arr1.forEach(item => {
                        const arr2 = item.split('=>')
                        const itemObj = {
                            name: "",
                            price: "",
                            weight: ""
                        }
                        arr2.forEach((val, index) => {
                            val = val.trim()
                            switch (index) {
                                case 1:
                                    itemObj.name = val
                                    break
                                case 2:
                                    itemObj.name += '-' + val
                                    break;
                                case 3:
                                    itemObj.price = this.currency_prefix + val
                                    break;
                                case 4:
                                    itemObj.weight = val ? val + "kg" : ""
                                    break;
                            }
                        })
                        result.push(itemObj)
                    });
                    return result
                }
            },
            created() {
                let url = window.location.href
                let getqyinfo = url.split('?')[1]
                let getqys = new URLSearchParams('?' + getqyinfo)
                this.id = getqys.get('id')

                this.getDetails()

            }
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
