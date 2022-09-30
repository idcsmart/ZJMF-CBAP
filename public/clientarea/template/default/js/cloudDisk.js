(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('template')[0]
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                asideMenu,
                topMenu,
                cloudTop,
                payDialog,
            },
            created() {
                // 获取通用配置
                this.getCommonData()
                // 获取产品id
                this.id = location.href.split('?')[1].split('=')[1]
                this.getHostDetail()
                // 获取实例磁盘
                this.getDiskList()
                // 获取其他配置
                this.getConfigData()
            },
            data() {
                return {
                    commonData: {},
                    // 实例id
                    id: null,
                    hostData:{},
                    // 产品id
                    product_id: 0,
                    // 实例磁盘列表
                    diskList: [],
                    // 订购磁盘弹窗相关
                    isShowDg: false,
                    // 订购磁盘弹窗 中 当前配置磁盘
                    oldDiskList: [],
                    // 订购磁盘参数
                    orderDiskData: {
                        id: 0,
                        remove_disk_id: [],
                        add_disk: []
                    },
                    // 新增磁盘数据
                    moreDiskData: [],
                    // 其他配置信息
                    configData: {},
                    // 新增磁盘的最大id
                    maxDiskId: 1,
                    // 磁盘总价格
                    moreDiskPrice: 0,
                    // 磁盘总容量
                    totalSize: 0,
                    loading: false,
                    // 是否显示扩容弹窗
                    isShowExpansion: false,
                    // 扩容磁盘参数
                    expanOrderData: {
                        id: 0,
                        resize_data_disk: []
                    },
                    // 扩容价格
                    expansionDiskPrice: 0,
                    // 订购/扩容标识
                    isOrderOrExpan: true,

                    orderTimer: null,
                    expanTimer: null,

                    // 订单id
                    orderId: 0,
                    // 订单金额
                    amount: 0,
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
            watch: {
                // 获取订购页磁盘的价格/扩容页磁盘的价格
                moreDiskData: {
                    handler(newValue, oldValue) {
                        if (this.isOrderOrExpan) {
                            // 获取订购磁盘 总价格
                            this.getOrderDiskPrice()
                        } else {
                            // 获取扩容磁盘弹窗 总价格
                        }
                    },
                    deep: true
                },
                oldDiskList: {
                    handler(newValue, oldValue) {
                        if (this.isOrderOrExpan) {
                            // 获取订购磁盘 总价格
                            this.getOrderDiskPrice()
                        } else {
                            // 获取扩容磁盘弹窗 总价格
                            this.getExpanDiskPrice()
                        }
                    },
                    deep: true
                }
            },
            methods: {
                // 获取通用配置
                getCommonData() {
                    getCommon().then(res => {
                        if (res.data.status === 200) {
                            this.commonData = res.data.data
                            localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                        }
                    })
                },
                // 获取磁盘列表
                getDiskList() {
                    this.loading = true
                    const params = {
                        id: this.id
                    }
                    diskList(params).then(res => {
                        this.diskList = res.data.list
                        this.diskList = this.diskList.filter(item => {
                            return item.resize
                        })
                        this.loading = false
                    }).catch(err => {
                        this.loading = false
                    })
                },
                // 获取其他配置
                getConfigData() {
                    const params = {
                        product_id: this.product_id
                    }
                    config(params).then(res => {
                        if (res.data.status === 200) {
                            this.configData = res.data.data
                            this.configData.disk_min_size = Number(this.configData.disk_min_size)
                            this.configData.disk_max_size = Number(this.configData.disk_max_size)
                        }
                    })
                },
                // 显示订购磁盘弹窗
                showDg() {
                    // 标记打开订购磁盘弹窗
                    this.isOrderOrExpan = true
                    this.oldDiskList = [...this.diskList]
                    this.oldDiskList = this.oldDiskList.filter(item => {
                        return item.resize
                    })
                    this.orderDiskData = {
                        id: 0,
                        remove_disk_id: [],
                        add_disk: []
                    }
                    this.moreDiskData = []
                    this.addMoreDisk()
                    this.isShowDg = true
                },
                // 关闭订购页面弹窗
                dgClose() {
                    this.isShowDg = false
                },
                // 删除当前的磁盘项
                delOldSize(id) {
                    this.oldDiskList = this.oldDiskList.filter(item => {
                        return item.id != id
                    })
                    this.orderDiskData.remove_disk_id.push(id)
                },
                // 删除新增的磁盘项
                delMoreDisk(id) {
                    let diskData = [...this.moreDiskData]
                    diskData = diskData.filter(item => {
                        return item.id != id
                    })
                    diskData.map((item, index) => {
                        item.index = index + 1
                    })
                    this.moreDiskData = diskData
                    if (this.moreDiskData.length == 0) {
                        this.isShowDg = false
                    }
                },
                // 新增磁盘项目
                addMoreDisk() {
                    // 最多存在的磁盘数目
                    const max = Number(this.configData.disk_max_num)
                    // 已有磁盘的数目
                    const oldNum = this.oldDiskList.length
                    // 已新增磁盘的数目
                    const newNum = this.moreDiskData.length

                    if ((newNum + oldNum) < max) {
                        // 当前的磁盘量 小于 规定最大的磁盘数量
                        this.maxDiskId += 1
                        const diskData = [...this.moreDiskData]
                        const itemData = {
                            id: this.maxDiskId,
                            size: this.configData.disk_min_size,
                            index: 0
                        }
                        diskData.push(itemData)
                        diskData.map((item, index) => {
                            item.index = index + 1
                        })
                        this.moreDiskData = diskData
                    } else {
                        this.$message({
                            message: `最多只能配置${this.configData.disk_max_num}个磁盘`,
                            type: 'warning'
                        });
                    }
                },
                // 计算订购磁盘页的价格
                getOrderDiskPrice() {
                    if (this.orderTimer) {
                        clearTimeout(this.orderTimer)
                    }
                    this.orderTimer = setTimeout(() => {
                        // 新增磁盘容量数组
                        let newSize = []
                        this.moreDiskData.map(item => {
                            newSize.push(item.size)
                        })
                        this.orderDiskData.add_disk = newSize

                        // 获取磁盘价格
                        const params = {
                            id: this.id,
                            remove_disk_id: this.orderDiskData.remove_disk_id,
                            add_disk: this.orderDiskData.add_disk
                        }
                        diskPrice(params).then(res => {
                            this.moreDiskPrice = res.data.data.price
                        }).catch(error => {

                        })
                    }, 500)
                },
                // 计算扩容磁盘页的价格
                getExpanDiskPrice() {
                    if (this.orderTimer) {
                        clearTimeout(this.orderTimer)
                    }
                    this.orderTimer = setTimeout(() => {
                        // 新增磁盘容量数组
                        let newSize = []
                        this.oldDiskList.map(item => {
                            newSize.push({
                                id: item.id,
                                size: item.size
                            })
                        })
                        this.expanOrderData.resize_data_disk = newSize

                        // 获取磁盘价格
                        const params = {
                            id: this.id,
                            resize_data_disk: this.expanOrderData.resize_data_disk
                        }
                        expanPrice(params).then(res => {
                            this.expansionDiskPrice = res.data.data.price
                        }).catch(err => {
                            this.expansionDiskPrice = 0.00
                        })
                    }, 500)
                },
                // 提交创建磁盘
                toCreateDisk() {
                    // 新增磁盘容量数组
                    let newSize = []
                    this.moreDiskData.map(item => {
                        newSize.push(item.size)
                    })
                    this.orderDiskData.add_disk = newSize

                    // 获取磁盘价格
                    const params = {
                        id: this.id,
                        remove_disk_id: this.orderDiskData.remove_disk_id,
                        add_disk: this.orderDiskData.add_disk
                    }

                    // 调用生成购买磁盘订单
                    diskOrder(params).then(res => {
                        console.log(res);
                        if (res.data.status === 200) {
                            this.orderId = res.data.data.id
                            this.amount = this.moreDiskPrice
                            this.isShowDg = false
                            this.$refs.payDialog.showPayDialog(this.orderId, this.amount)
                        }
                    }).catch(err => {
                        this.$message.error(err.data.msg)
                    })
                },
                // 显示扩容弹窗
                showExpansion() {
                    // 标记打开扩容弹窗
                    this.isOrderOrExpan = false
                    this.expansionDiskPrice = 0.00
                    this.oldDiskList = this.diskList.map(item => ({ ...item }))
                    // this.oldDiskList = [...this.diskList]

                    this.oldDiskList = this.oldDiskList.filter(item => {
                        item.minSize = item.size
                        return item.resize
                    })
                    this.isShowExpansion = true
                },
                // 关闭扩容弹窗
                krClose() {
                    this.isShowExpansion = false
                },
                // 提交扩容
                subExpansion() {
                    let newSize = []
                    this.oldDiskList.map(item => {
                        newSize.push({
                            id: item.id,
                            size: item.size
                        })
                    })
                    this.expanOrderData.resize_data_disk = newSize

                    // 获取磁盘价格
                    const params = {
                        id: this.id,
                        resize_data_disk: this.expanOrderData.resize_data_disk
                    }
                    // 调用扩容接口
                    diskExpanOrder(params).then(res => {
                        this.orderId = res.data.data.id
                        this.amount = this.expansionDiskPrice
                        this.isShowExpansion = false
                        this.$refs.payDialog.showPayDialog(this.orderId, this.amount)
                    }).catch(err => {
                        this.$message.error(err.data.msg)
                    })
                },
                // 支付成功回调
                paySuccess(e) {
                    // 重新拉取磁盘列表
                    this.getDiskList()
                    console.log(e);
                },
                // 取消支付回调
                payCancel(e) {
                    console.log(e);
                },
                // 获取产品详情
                getHostDetail() {
                    const params = {
                        id: this.id
                    }
                    hostDetail(params).then(res => {
                        if (res.data.status === 200) {
                            this.hostData = res.data.data.host
                            this.product_id = this.hostData.product_id

                        }
                    })
                },
            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
