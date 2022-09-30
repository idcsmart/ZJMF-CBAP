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
                // 获取实例id
                this.id = location.href.split('?')[1].split('=')[1]
                this.getHostDetail()
                this.getCommonData()
                // 快照列表
                this.getSnapshotList()
                // 备份列表
                this.getBackupList()
                // 获取其他配置
                this.getConfigData()
                // 获取该实例的磁盘
                this.getDiskList()
            },
            data() {
                return {
                    commonData: {},
                    // 实例id
                    id: null,
                    hostData: {},
                    // 产品id
                    product_id: 0,
                    // 备份列表数据
                    dataList1: [],
                    // 快照列表数据
                    dataList2: [],
                    // 备份table loading
                    loading1: false,
                    // 快照table loading
                    loading2: false,
                    params1: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: 'id',
                        sort: 'desc',
                        keywords: '',
                    },
                    params2: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: 'id',
                        sort: 'desc',
                        keywords: '',
                    },
                    // 实例磁盘列表
                    diskList: [],
                    // 是否显示开启备份弹窗
                    isShowOpenBs: false,
                    // 是否显示开启快照弹窗
                    isShowsnap: false,
                    // 其他配置信息
                    configData: {},
                    // 获取快照/备份升降级价格 参数 生成快照/备份数量升降级订单参数
                    bsData: {
                        id: 0,
                        type: '',
                        backNum: 0,
                        snapNum: 0,
                        money: 0,
                        duration: '月'
                    },
                    // 创建备份/生成快照
                    // 是否显示弹窗
                    isShwoCreateBs: false,
                    // 弹窗表单数据
                    createBsData: {
                        id: 0,
                        name: '',
                        disk_id: 0
                    },
                    // true 标记为备份  false 标记为快照
                    isBs: true,
                    errText: '',
                    cgbsLoading: false,
                    // 删除显示数据
                    delData: {
                        delId: 0,
                        // 实例名称
                        cloud_name: '',
                        // 创建时间
                        time: '',
                        // 快照名称
                        name: "",
                    },
                    // 还原显示数据
                    restoreData: {
                        restoreId: 0,
                        // 实例名称
                        cloud_name: '',
                        // 创建时间
                        time: '',
                    },
                    isShowhyBs: false,
                    // 还原弹窗 提交按钮
                    loading3: false,
                    // 是否显示删除快照弹窗
                    isShowDelBs: false,
                    loading4: false,
                    // 实例详情 
                    cloudDetail: {},
                }
            },
            watch: {
                bsData: {
                    handler(newValue, oldValue) {
                        // 开启备份/快照的价格
                        this.getBsPrice()
                    },
                    deep: true
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
                    getCommon().then(res => {
                        if (res.data.status === 200) {
                            this.commonData = res.data.data
                            localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                        }
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
                        }
                    })
                },
                // 备份列表
                getBackupList() {
                    this.loading1 = true
                    const params = {
                        id: this.id,
                        ...this.params1
                    }
                    backupList(params).then(res => {

                        if (res.data.status === 200) {
                            this.dataList1 = res.data.data.list
                            this.params1.total = res.data.data.count
                        }
                        this.loading1 = false
                    }).catch(err => {
                        this.loading1 = true
                    })
                },
                // 快照列表
                getSnapshotList() {
                    this.loading2 = true
                    const params = {
                        id: this.id,
                        ...this.params2
                    }
                    snapshotList(params).then(res => {
                        if (res.data.status === 200) {
                            this.dataList2 = res.data.data.list
                            this.params2.total = res.data.data.count
                        }
                        this.loading2 = false
                    }).catch(err => {
                        this.loading2 = false
                    })
                },
                // 获取实例磁盘
                // 获取磁盘列表
                getDiskList() {
                    const params = {
                        id: this.id
                    }
                    diskList(params).then(res => {
                        this.diskList = res.data.list
                    }).catch(err => {
                    })
                },
                // 关闭 开启备份/快照弹窗
                bsopenDgClose() {
                    this.isShowOpenBs = false
                },
                // 获取开启备份/快照的价格
                getBsPrice() {
                    const params = {
                        id: this.id,
                        type: this.isBs ? 'backup' : 'snap',
                        num: this.isBs ? this.bsData.backNum : this.bsData.snapNum
                    }
                    backupConfig(params).then(res => {
                        if (res.data.status === 200) {
                            this.bsData.money = res.data.data.price
                        }
                    })
                },
                // 关闭 开启快照弹窗
                snapDgClose() {
                    this.isShowsnap = false
                },
                // 开启备份/快照 弹窗
                openBs(type) {
                    if (type == 'back') {
                        this.isBs = true
                    } else {
                        this.isBs = false
                    }
                    this.bsData.backNum = this.configData.backup_option[0] ? this.configData.backup_option[0].num : ''

                    this.bsData.snapNum = this.configData.snap_option[0] ? this.configData.backup_option[0].num : ''
                    this.isShowOpenBs = true
                },
                // 开启备份、弹窗提交
                bsopenSub() {
                    const params = {
                        id: this.id,
                        type: this.isBs ? 'backup' : 'snap',
                        num: this.isBs ? this.bsData.backNum : this.bsData.snapNum
                    }
                    backupOrder(params).then(res => {
                        if (res.data.status === 200) {
                            const orderId = res.data.data.id
                            const amount = this.bsData.money
                            this.isShowOpenBs = false
                            this.$refs.payDialog.showPayDialog(orderId, amount)
                        }
                    }).catch(err => {
                        this.$message.error(err.data.msg)
                    })
                },
                // 创建备份/生成快照弹窗 关闭
                bsCgClose() {
                    this.isShwoCreateBs = false
                },
                // 展示创建备份、快照弹窗
                showCreateBs(type) {
                    if (type == 'back') {
                        this.isBs = true
                    } else {
                        this.isBs = false
                    }
                    this.errText = ''
                    this.createBsData = {
                        id: this.id,
                        name: '',
                        disk_id: this.diskList[0] ? this.diskList[0].id : ''
                    }
                    this.isShwoCreateBs = true
                },
                // 创建备份、快照弹窗提交
                subCgBs() {
                    const data = this.createBsData
                    let isPass = true
                    if (!data.name) {
                        isPass = false
                        this.errText = "请输入名称"
                        return false
                    }
                    if (!data.disk_id) {
                        isPass = false
                        this.errText = "请选择磁盘"
                        return false
                    }
                    if (isPass) {
                        this.errText = ''
                        const params = {
                            ...this.createBsData
                        }
                        this.cgbsLoading = true
                        if (this.isBs) {
                            // 调用创建备份接口
                            createBackup(params).then(res => {
                                if (res.data.status === 200) {
                                    this.$message.success("创建备份成功")
                                    this.isShwoCreateBs = false
                                    this.getBackupList()
                                }
                                this.cgbsLoading = false
                            }).catch(err => {
                                this.errText = err.data.msg
                                this.cgbsLoading = false
                            })
                        } else {
                            // 调用创建磁盘接口
                            createSnapshot(params).then(res => {
                                if (res.data.status === 200) {
                                    this.$message.success("创建快照成功")
                                    this.isShwoCreateBs = false
                                    this.getSnapshotList()
                                }
                                this.cgbsLoading = false
                            }).catch(err => {
                                this.errText = err.data.msg
                                this.cgbsLoading = false
                            })
                        }
                    }
                },
                // 还原快照、备份 弹窗显示
                showhyBs(type, item) {
                    if (type == 'back') {
                        this.isBs = true
                    } else {
                        this.isBs = false
                    }
                    this.restoreData.restoreId = item.id
                    this.restoreData.time = item.create_time
                    this.restoreData.cloud_name = this.$refs.cloudTop.hostData.name
                    this.isShowhyBs = true
                },
                // 还原快照、备份 弹窗关闭
                bshyClose() {
                    this.isShowhyBs = false
                },
                // 还原备份、快照 提交
                subhyBs() {
                    this.loading3 = true
                    if (this.isBs) {
                        // 调用还原备份
                        const params = {
                            id: this.id,
                            backup_id: this.restoreData.restoreId
                        }
                        restoreBackup(params).then(res => {
                            if (res.data.status === 200) {
                                this.$message.success(res.data.msg)
                                this.isShowhyBs = false
                            }
                            this.loading3 = false
                        }).catch(err => {
                            this.$message.error(err.data.msg)
                            this.loading3 = false
                        })
                    } else {
                        // 调用还原快照
                        const params = {
                            id: this.id,
                            snapshot_id: this.restoreData.restoreId
                        }
                        restoreSnapshot(params).then(res => {
                            if (res.data.status === 200) {
                                this.$message.success(res.data.msg)
                                this.isShowhyBs = false
                            }
                            this.loading3 = false
                        }).catch(err => {
                            this.$message.error(err.data.msg)
                            this.loading3 = false
                        })
                    }

                },
                // 
                // 删除备份、快照弹窗显示
                showDelBs(type, item) {
                    if (type == 'back') {
                        this.isBs = true
                    } else {
                        this.isBs = false
                    }
                    this.delData.delId = item.id
                    this.delData.time = item.create_time
                    this.delData.name = item.name
                    this.delData.cloud_name = this.$refs.cloudTop.hostData.name
                    this.isShowDelBs = true
                },
                // 关闭 删除备份、快照弹窗显示
                delBsClose() {
                    this.isShowDelBs = false
                },
                // 删除备份、快照弹窗 提交
                subDelBs() {
                    this.loading4 = true
                    if (this.isBs) {
                        // 调用删除备份
                        const params = {
                            id: this.id,
                            backup_id: this.delData.delId
                        }
                        delBackup(params).then(res => {
                            if (res.data.status === 200) {
                                this.$message.success(res.data.msg)
                                this.isShowDelBs = false
                                this.getBackupList()
                            }
                            this.loading4 = false
                        }).catch(err => {
                            this.$message.error(err.data.msg)
                            this.loading4 = false
                        })
                    } else {
                        // 调用删除快照
                        const params = {
                            id: this.id,
                            snapshot_id: this.delData.delId
                        }
                        delSnapshot(params).then(res => {
                            if (res.data.status === 200) {
                                this.$message.success(res.data.msg)
                                this.isShowDelBs = false
                                this.getSnapshotList()
                            }
                            this.loading4 = false
                        }).catch(err => {
                            this.$message.error(err.data.msg)
                            this.loading4 = false
                        })
                    }
                },
                // 支付成功回调
                paySuccess(e) {
                    this.getConfigData()
                    this.getBackupList()
                    this.getSnapshotList()
                    this.$refs.cloudTop.getCloudDetail()
                    console.log(e);
                },
                // 取消支付回调
                payCancel(e) {
                    console.log(e);
                },
                // 获取实例详情
                getCloudDetail(e) {
                    this.cloudDetail = e
                    console.log(this.cloudDetail);
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
