(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('re-config')[0]
        Vue.prototype.lang = window.lang
        Vue.prototype.moment = window.moment
        new Vue({
            data() {
                const smallValidator = (val => {
                    let isPass = true
                    let errText = ""
                    if (this.smallData && this.smallData.length > 0) {
                        this.smallData.forEach(item => {
                            if (item.isEdit) {
                                isPass = false
                                errText = "请先确认或取消修改/新增"
                            }
                        })
                    } else {
                        isPass = false
                        errText = "请至少添加一条小型房箱预期交付周期数据"
                    }

                    if (isPass) {
                        return { result: true };
                    } else {
                        return { result: false, message: errText, type: 'error' };
                    }
                })
                const mediumValidator = (val => {
                    let isPass = true
                    let errText = ""
                    if (this.mediumData && this.mediumData.length > 0) {
                        this.mediumData.forEach(item => {
                            if (item.isEdit) {
                                isPass = false
                                errText = "请先确认或取消修改/新增"
                            }
                        })
                    } else {
                        isPass = false
                        errText = "请至少添加一条中型房箱预期交付周期数据"
                    }

                    if (isPass) {
                        return { result: true };
                    } else {
                        return { result: false, message: errText, type: 'error' };
                    }
                })

                const bigValidator = (val => {
                    let isPass = true
                    let errText = ""
                    if (this.bigData && this.bigData.length > 0) {
                        this.bigData.forEach(item => {
                            if (item.isEdit) {
                                isPass = false
                                errText = "请先确认或取消修改/新增"
                            }
                        })
                    } else {
                        isPass = false
                        errText = "请至少添加一条大型房箱预期交付周期数据"
                    }

                    if (isPass) {
                        return { result: true };
                    } else {
                        return { result: false, message: errText, type: 'error' };
                    }
                })

                return {
                    currency_suffix: JSON.parse(localStorage.getItem('common_set')).currency_suffix || '元',
                    maxHeight: '',
                    urlPath: url,
                    formData: {},
                    rules: {
                        length: [
                            { required: true, message: "请输入房箱识别码长度" },
                            // { validator: lengthValidator, trigger: 'blur' }
                        ],
                        small: [
                            { validator: smallValidator, trigger: 'blur' }
                        ],
                        medium: [
                            { validator: mediumValidator, trigger: 'blur' }
                        ],
                        big: [
                            { validator: bigValidator, trigger: 'blur' }
                        ],
                        purchase: [
                            { required: true, message: "请输入协议" },
                        ]
                    },
                    data: {},
                    purchase: "",
                    length: 0,
                    smallData: [],
                    mediumData: [],
                    bigData: [],
                    smallId: 1,
                    mediumId: 1,
                    bigId: 1,
                    columns: [
                        {
                            colKey: "id",
                            title: "ID",
                            width: 125,
                        },
                        {
                            colKey: "order",
                            title: "订单数",
                        },
                        {
                            colKey: "cycle",
                            title: "周期",
                        },
                        {
                            colKey: "operation",
                            title: "操作",
                            width: 125
                        },
                    ],
                    promotion_time_min: "",
                    promotion_time_max: "",
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
                // 获取设置
                doGetConfig() {
                    getConfig().then(res => {
                        if (res.data.status == 200) {
                            const data = res.data.data

                            this.smallData = []
                            this.mediumData = []
                            this.bigData = []
                            data.list.forEach(item => {
                                item.isEdit = false
                                if (item.type == 'small') {
                                    this.smallData.push(item)
                                }
                                if (item.type == 'medium') {
                                    this.mediumData.push(item)
                                }
                                if (item.type == 'big') {
                                    this.bigData.push(item)
                                }
                            })

                            this.formData = data
                            this.promotion_time_min = data.promotion_time_min * 1000
                            this.promotion_time_max = data.promotion_time_max * 1000
                        }
                    })
                },
                // 保存设置
                doSaveConfig() {
                    const params = {

                    }
                    saveConfig(params).then(res => {
                        if (res.data.status == 200) {
                            this.$message.success(res.data.msg)
                        }
                    }).catch((error) => {
                        this.$message.error(error.data.msg)
                    })
                },
                addCycle(type) {
                    if (type == 'small') {
                        const item = {
                            id: this.smallId,
                            type: "samll",
                            isEdit: true,
                            order_min: '',
                            order_max: '',
                            cycle_min: '',
                            cycle_max: '',
                        }
                        this.smallData.push(item)
                        this.smallId += 1
                    }
                    if (type == "medium") {
                        const item = {
                            id: this.mediumId,
                            type: "medium",
                            isEdit: true,
                            order_min: '',
                            order_max: '',
                            cycle_min: '',
                            cycle_max: '',
                        }
                        this.mediumData.push(item)
                        this.mediumId += 1
                    }
                    if (type == "big") {
                        const item = {
                            id: this.bigId,
                            type: "big",
                            isEdit: true,
                            order_min: '',
                            order_max: '',
                            cycle_min: '',
                            cycle_max: '',
                        }
                        this.bigData.push(item)
                        this.bigId += 1
                    }
                },
                edit(type, row) {
                    if (type == "small") {
                        this.smallData.map(item => {
                            if (item.id == row.id) {
                                item.isEdit = true
                                item.order_min_edit = Number(item.order_min)
                                item.order_max_edit = Number(item.order_max)
                                item.cycle_min_edit = Number(item.cycle_min)
                                item.cycle_max_edit = Number(item.cycle_max)
                            }
                        })
                    }
                    if (type == "medium") {
                        this.mediumData.map(item => {
                            if (item.id == row.id) {
                                item.isEdit = true
                                item.order_min_edit = Number(item.order_min)
                                item.order_max_edit = Number(item.order_max)
                                item.cycle_min_edit = Number(item.cycle_min)
                                item.cycle_max_edit = Number(item.cycle_max)
                            }
                        })
                    }
                    if (type == "big") {
                        this.bigData.map(item => {
                            if (item.id == row.id) {
                                item.isEdit = true
                                item.order_min_edit = Number(item.order_min)
                                item.order_max_edit = Number(item.order_max)
                                item.cycle_min_edit = Number(item.cycle_min)
                                item.cycle_max_edit = Number(item.cycle_max)
                            }
                        })
                    }
                },
                del(type, row) {
                    if (type == "small") {
                        this.smallData = this.smallData.filter(item => {
                            return item.id != row.id
                        })
                    }
                    if (type == "medium") {
                        this.mediumData = this.mediumData.filter(item => {
                            return item.id != row.id
                        })
                    }
                    if (type == "big") {
                        this.bigData = this.bigData.filter(item => {
                            return item.id != row.id
                        })
                    }
                },
                save(type, row) {
                    let isPass = true
                    let errText = ""
                    if (!row.order_min_edit) {
                        isPass = false
                        errText = "请输入最小订单数"
                    }
                    if (!row.order_max_edit) {
                        isPass = false
                        errText = "请输入最大订单数"
                    }
                    if (!row.cycle_min_edit) {
                        isPass = false
                        errText = "请输入最小周期数"
                    }
                    if (!row.cycle_max_edit) {
                        isPass = false
                        errText = "请输入最大周期数"
                    }

                    if (row.order_min_edit && row.order_max_edit && (Number(row.order_max_edit) < Number(row.order_min_edit))) {
                        isPass = false
                        errText = "最大订单数不能小于最小订单数"
                    }
                    if (row.cycle_min_edit && row.cycle_max_edit && (Number(row.cycle_max_edit) < Number(row.cycle_min_edit))) {
                        isPass = false
                        errText = "最大周期数不能小于最小周期数"
                    }

                    if (isPass) {
                        if (type == "small") {
                            this.smallData.map(item => {
                                if (item.id == row.id) {
                                    item.order_min = item.order_min_edit
                                    item.order_max = item.order_max_edit
                                    item.cycle_min = item.cycle_min_edit
                                    item.cycle_max = item.cycle_max_edit
                                    item.isEdit = false
                                }
                            })
                        }
                        if (type == "medium") {
                            this.mediumData.map(item => {
                                if (item.id == row.id) {
                                    item.order_min = item.order_min_edit
                                    item.order_max = item.order_max_edit
                                    item.cycle_min = item.cycle_min_edit
                                    item.cycle_max = item.cycle_max_edit
                                    item.isEdit = false
                                }
                            })
                        }
                        if (type == "big") {
                            this.bigData.map(item => {
                                if (item.id == row.id) {
                                    item.order_min = item.order_min_edit
                                    item.order_max = item.order_max_edit
                                    item.cycle_min = item.cycle_min_edit
                                    item.cycle_max = item.cycle_max_edit
                                    item.isEdit = false
                                }
                            })
                        }
                    } else {
                        this.$message.warning(errText)
                    }
                },
                cancel(type, row) {
                    let isNew = false
                    // 判断是否是新加的数据
                    if (!row.order_min || !row.order_max || !row.cycle_min || !row.cycle_max) {
                        isNew = true
                    }
                    if (type == "small") {
                        if (isNew) {
                            this.smallData = this.smallData.filter(item => {
                                return item.id != row.id
                            })
                        } else {
                            this.smallData.map(item => {
                                if (item.id == row.id) {
                                    item.isEdit = false
                                }
                            })
                        }
                    }
                    if (type == "medium") {
                        if (isNew) {
                            this.mediumData = this.mediumData.filter(item => {
                                return item.id != row.id
                            })
                        } else {
                            this.mediumData.map(item => {
                                if (item.id == row.id) {
                                    item.isEdit = false
                                }
                            })
                        }
                    }
                    if (type == "big") {
                        if (isNew) {
                            this.bigData = this.bigData.filter(item => {
                                return item.id != row.id
                            })
                        } else {
                            this.bigData.map(item => {
                                if (item.id == row.id) {
                                    item.isEdit = false
                                }
                            })
                        }
                    }
                },
                submit({ validateResult, firstError }) {
                    if (validateResult === true) {
                        const cycle = [...this.smallData, ...this.mediumData, ...this.bigData]
                        console.log(cycle);

                        const params = {
                            purchase: this.formData.purchase,
                            length: this.formData.length,
                            downpayment: this.formData.downpayment,
                            cycle,
                            promotion_time_min: this.formData.promotion_time_min,
                            promotion_time_max: this.formData.promotion_time_max,
                            promotion_copywritint: this.formData.promotion_copywritint,
                            promotion_amount: this.formData.promotion_amount,
                        }
                        saveConfig(params).then(res => {
                            if (res.data.status == 200) {
                                this.doGetConfig()
                                this.$message.success(res.data.msg)
                            }
                        }).catch(error => {
                            this.$message.error(error.data.msg)
                        })
                    } else {
                        this.$message.warning(firstError);
                    }
                },
                onReset() {
                    console.log("重置为初始值");
                },
                minHandleChange(value, context) {
                    this.formData.promotion_time_min = context.dayjsValue.valueOf() / 1000
                },
                maxHandleChange(value, context) {
                    this.formData.promotion_time_max = context.dayjsValue.valueOf() / 1000
                },
            },
            created() {
                this.doGetConfig()
            }
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
