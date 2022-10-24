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
            },
            created() {
                // 获取实例id
                this.id = location.href.split('?')[1].split('=')[1]
                this.getHostDetail()
                // this.id = 5294
                // 获取实例状态
                // this.getStatus()
                // 获取通用信息
                this.getCommonData()
            },

            data() {
                return {
                    commonData: {},
                    // 实例id
                    id: null,
                    // 产品id
                    product_id: 0,
                    // 实例状态
                    statusData: {},
                    // 开关机状态
                    powerStatus: 'on',
                    // 错误提示信息
                    errText: '',
                    // 救援模式弹窗数据
                    rescueData: {
                        type: "1",
                        password: ''
                    },
                    // 产品详情
                    hostData: {},
                    // 当前切换的升降级套餐
                    changeUpgradeData: {},
                    // 是否展示升降级弹窗
                    isShowUpgrade: false,
                    // 是否展示重置密码弹窗
                    isShowRePass: false,
                    // 重置密码弹窗数据
                    rePassData: {
                        password: '',
                        checked: false
                    },
                    // 实例详情
                    cloudData: {
                        package: {
                            description: ''
                        }
                    },
                    // 升降级价格
                    upPrice: 0,
                    // 升降级套餐列表
                    upgradeList: [],
                    // 升降级表单
                    upgradePackageId: 0,
                    // 是否展示救援模式弹窗
                    isShowRescue: false,
                    // 开关机确认按钮loading
                    loading1: false,
                    // 控制台按钮loading
                    loading2: false,
                    // 救援系统弹窗 提交按钮 loading
                    loading3: false,
                    // 升降级弹窗提交按钮 loading
                    loading4: false,
                    // 充值密码提交按钮 loading
                    loading5: false,

                    // 停用相关
                    // 是否显示停用弹窗
                    isShowRefund: false,
                    // 停用页面信息
                    refundPageData: {
                        host: {
                            create_time: 0,
                            first_payment_amount: 0
                        }
                    },
                    // 停用页面参数
                    refundParams: {
                        host_id: 0,
                        suspend_reason: null,
                        type: 'Expire'
                    },

                    // 控制显示开关机重启弹窗
                    isShowSure: false,
                    isShowQuit: false,
                    isRescue: false,
                    powerList: [
                        {
                            id: 1,
                            label: '开机',
                            value: "on"
                        },
                        {
                            id: 2,
                            label: '关机',
                            value: "off"
                        },
                        {
                            id: 3,
                            label: '重启',
                            value: "rebot"
                        },
                        {
                            id: 4,
                            label: '强制重启',
                            value: "hardRebot"
                        },
                        {
                            id: 5,
                            label: '强制关机',
                            value: "hardOff"
                        },
                    ]

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
                // 返回剩余到期时间
                formateDueDay(time) {
                    return Math.floor((time * 1000 - Date.now()) / (1000 * 60 * 60 * 24))
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
                // 获取实例状态
                getStatus() {
                    const params = {
                        id: this.id
                    }
                    cloudStatus(params).then(res => {
                        if (res.data.status === 200) {
                            this.statusData = res.data.data
                            if (this.statusData.status == 'off') {
                                this.powerStatus = 'off'
                            }
                        }
                    })
                },
                // 随机生成密码
                autoPass() {
                    let pass = randomCoding(1) + 0 + genEnCode(10, 1, 1, 0, 1, 0)
                    // 救援系统密码
                    this.rescueData.password = pass
                    // 重置密码
                    this.rePassData.password = pass
                },
                // 关闭救援模式弹窗
                rescueDgClose() {
                    this.isShowRescue = false
                },
                // 进行开关机
                toChangePower() {
                    this.loading1 = true
                    if (this.powerStatus == 'on') {
                        this.doPowerOn()
                    }
                    if (this.powerStatus == 'off') {
                        this.doPowerOff()
                    }
                    if (this.powerStatus == 'rebot') {
                        this.rebot()
                    }
                    if (this.powerStatus == 'hardRebot') {
                        this.doHardRebot()
                    }
                    if (this.powerStatus == 'hardOff') {
                        this.doHardOff()
                    }

                },
                // 开机
                doPowerOn() {
                    const params = {
                        id: this.id
                    }
                    powerOn(params).then(res => {
                        if (res.data.status === 200) {
                            this.$message.success("开机发起成功!")
                            this.$refs.cloudTop.getCloudStatus()
                            this.loading1 = false
                        }
                    }).catch(err => {
                        this.loading1 = false
                        this.$message.error(err.data.msg)
                    })
                },
                // 关机
                doPowerOff() {
                    const params = {
                        id: this.id
                    }
                    powerOff(params).then(res => {
                        if (res.data.status === 200) {
                            this.$message.success("关机发起成功!")
                            this.$refs.cloudTop.getCloudStatus()
                        }
                        this.loading1 = false
                    }).catch(err => {
                        this.$message.error(err.data.msg)
                        this.loading1 = false
                    })
                },
                // 强制关机
                doHardOff() {
                    const params = {
                        id: this.id
                    }
                    hardOff(params).then(res => {
                        if (res.data.status === 200) {
                            this.$message.success("强制关机发起成功!")
                            this.$refs.cloudTop.getCloudStatus()
                            this.loading1 = false
                        }
                    }).catch(err => {
                        this.loading1 = false
                        this.$message.error(err.data.msg)
                    })
                },
                // 强制重启
                doHardRebot() {
                    const params = {
                        id: this.id
                    }
                    hardReboot(params).then(res => {
                        if (res.data.status === 200) {
                            this.$message.success("强制重启发起成功!")
                            this.$refs.cloudTop.getCloudStatus()
                            this.loading1 = false
                        }
                    }).catch(err => {
                        this.loading1 = false
                        this.$message.error(err.data.msg)
                    })
                },
                // 重启
                rebot() {
                    const params = {
                        id: this.id
                    }
                    reboot(params).then(res => {
                        if (res.data.status === 200) {
                            this.$message.success("重启发起成功!")
                            this.$refs.cloudTop.getCloudStatus()
                            this.loading1 = false
                        }
                    }).catch(err => {
                        this.loading1 = false
                        this.$message.error(err.data.msg)
                    })
                },
                // 控制台点击
                getVncUrl() {
                    this.loading2 = true
                    const params = {
                        id: this.id
                    }
                    vncUrl(params).then(res => {
                        if (res.data.status === 200) {
                            window.open(res.data.data.url);
                        }
                        this.loading2 = false
                    }).catch(err => {
                        this.$message.error(err.data.msg)
                        this.loading2 = false
                    })
                },
                // 重置密码点击
                showRePass() {
                    this.errText = ''
                    this.rePassData = {
                        password: '',
                        checked: false
                    }
                    this.isShowRePass = true

                },
                // 关闭重置密码弹窗
                rePassDgClose() {
                    this.isShowRePass = false
                },
                // 重置密码提交
                rePassSub() {
                    const data = this.rePassData
                    let isPass = true
                    if (!data.password) {
                        isPass = false
                        this.errText = "请输入密码"
                        return false
                    }

                    if (!data.checked && this.powerStatus == 'off') {
                        isPass = false
                        this.errText = "请勾选同意强制关机"
                        return false
                    }

                    if (isPass) {
                        this.loading5 = true
                        this.errText = ''
                        const params = {
                            id: this.id,
                            password: data.password
                        }

                        if (this.powerStatus == 'off') {
                            const params1 = {
                                id: this.id
                            }
                            hardOff(params1).then(res => {
                                this.$refs.cloudTop.getCloudStatus()
                                resetPassword(params).then(res => {
                                    if (res.data.status === 200) {
                                        this.$message.success("重置密码成功")
                                        this.isShowRePass = false
                                    }
                                    this.loading5 = false
                                }).catch(error => {
                                    this.errText = error.data.msg
                                    this.loading5 = false
                                })
                            }).catch(error => {
                                this.$message.error(error.data.msg)
                            })
                        } else {
                            resetPassword(params).then(res => {
                                if (res.data.status === 200) {
                                    this.$message.success("重置密码成功")
                                    this.isShowRePass = false
                                }
                                this.loading5 = false
                            }).catch(error => {
                                this.errText = error.data.msg
                                this.loading5 = false
                            })
                        }
                    }

                },
                // 救援模式点击
                showRescueDialog() {
                    this.errText = ''
                    this.rescueData = {
                        type: "1",
                        password: ''
                    }
                    this.isShowRescue = true
                },
                // 关闭救援模式弹窗
                rescueDgClose() {
                    this.isShowRescue = false
                },
                // 救援模式提交按钮
                rescueSub() {
                    let isPass = true
                    if (!this.rescueData.type) {
                        isPass = false
                        this.errText = "请选择救援系统"
                        return false
                    }
                    if (!this.rescueData.password) {
                        isPass = false
                        this.errText = "请输入临时密码"
                        return false
                    }

                    if (isPass) {
                        this.errText = ''
                        this.loading3 = true
                        // 调用救援系统接口
                        const params = {
                            id: this.id,
                            type: this.rescueData.type,
                            password: this.rescueData.password
                        }
                        rescue(params).then(res => {
                            if (res.data.status === 200) {
                                this.$message.success("救援模式发起成功!")
                                this.$refs.cloudTop.getRemoteInfo()
                            }
                            this.isShowRescue = false
                            this.loading3 = false
                        }).catch(err => {
                            this.errText = err.data.msg
                            this.loading3 = false
                        })
                    }
                },
                // 重装系统点击
                showReinstall() {
                    this.$refs.cloudTop.showReinstall()
                },
                // 挂载ISO点击
                // 设置启动项目点击
                // 升降级点击
                showUpgrade() {
                    const params = {
                        product_id: this.product_id,
                        data_center_id: this.$refs.cloudTop.cloudData.data_center.id
                    }
                    // 获取实例详情
                    this.cloudData = this.$refs.cloudTop.cloudData
                    // 产品详情
                    this.hostData = this.$refs.cloudTop.hostData
                    // 获取升降级套餐列表
                    upgradePackage(params).then(res => {
                        if (res.data.status === 200) {
                            this.upgradeList = res.data.data.package

                            // 当前套餐的周期
                            let duration = this.cloudData.duration
                            // 当前产品套餐id
                            const packageId = this.cloudData.package.id
                            // 过滤升降级套餐中不支持该套餐周期的
                            this.upgradeList = this.upgradeList.filter(item => {
                                return (item[duration] != '') && (item.id != packageId)
                            })
                            // 默认获取 过滤后的第一个套餐
                            this.upgradePackageId = this.upgradeList[0].id
                            let money = this.upgradeList[0][duration]
                            switch (duration) {
                                case 'month_fee':
                                    duration = '月'
                                    break;
                                case 'quarter_fee':
                                    duration = '季'
                                    break;
                                case 'year_fee':
                                    duration = '年'
                                    break;
                                case 'two_year':
                                    duration = '两年'
                                    break;
                                case 'three_year':
                                    duration = '三年'
                                    break;
                                case 'onetime_fee':
                                    duration = '一次性'
                                    break;
                            }
                            this.changeUpgradeData = {
                                id: this.upgradeList[0].id,
                                money,
                                duration,
                                description: this.upgradeList[0].description
                            }
                            // 获取升降级价格
                            this.getUpgradePrice()
                        }
                    })
                    this.errText = ''
                    this.isShowUpgrade = true
                },
                // 升降级弹窗 套餐选择框变化
                upgradeSelectChange(e) {
                    this.upgradeList.map(item => {
                        if (item.id == e) {
                            // 获取当前套餐的周期
                            let duration = this.cloudData.duration
                            // 该周期新套餐的价格
                            let money = item[duration]

                            switch (duration) {
                                case 'month_fee':
                                    duration = '月'
                                    break;
                                case 'quarter_fee':
                                    duration = '季'
                                    break;
                                case 'year_fee':
                                    duration = '年'
                                    break;
                                case 'two_year':
                                    duration = '两年'
                                    break;
                                case 'three_year':
                                    duration = '三年'
                                    break;
                                case 'onetime_fee':
                                    duration = '一次性'
                                    break;
                            }
                            this.changeUpgradeData = {
                                id: item.id,
                                money,
                                duration,
                                description: item.description
                            }
                        }
                    })
                    this.getUpgradePrice()
                },
                // 关闭升降级弹窗
                upgradeDgClose() {
                    this.isShowUpgrade = false
                },
                // 获取升降级价格
                getUpgradePrice() {
                    const params = {
                        id: this.id,
                        package_id: this.upgradePackageId
                    }
                    upgradePackagePrice(params).then(res => {
                        if (res.data.status === 200) {
                            let price = res.data.data.price
                            if (price < 0) {
                                this.upPrice = 0
                                return
                            }
                            this.upPrice = res.data.data.price
                        }
                    })
                },
                // 升降级提交
                upgradeSub() {
                    const data = this.changeUpgradeData
                    let isPass = true
                    if (!data.id) {
                        this.errText = "请选择套餐"
                        isPass = false
                        return false
                    }
                    if (isPass) {
                        this.errText = ''
                        // 调生成升降级订单接口
                        const params = {
                            id: this.id,
                            package_id: data.id
                        }
                        upgradeOrder(params).then(res => {
                            if (res.data.status === 200) {
                                this.$message.success("生成升降级订单成功")
                                this.isShowUpgrade = false
                                const orderId = res.data.data.id
                                // 调支付弹窗
                            }

                        }).catch(err => {
                            this.errText = err.data.msg
                        })
                    }
                },
                // 删除实例点击
                showRefund() {
                    const params = {
                        host_id: this.id
                    }
                    refundMsg(params).then(res => {
                        if (res.data.status === 200) {
                            console.log(res);
                        }
                    })
                    // 获取停用页面信息
                    refundPage(params).then(res => {
                        if (res.data.status == 200) {
                            this.refundPageData = res.data.data
                            if (this.refundPageData.allow_refund === 0) {
                                this.$message.warning("不支持退款")
                            } else {
                                this.isShowRefund = true
                            }
                        }
                    })
                },
                // 关闭停用弹窗
                refundDgClose() {
                    this.isShowRefund = false
                },
                // 停用弹窗提交
                subRefund() {
                    const params = {
                        host_id: this.id,
                        suspend_reason: this.refundParams.suspend_reason,
                        type: this.refundParams.type
                    }
                    if (!params.suspend_reason) {
                        this.$message.error("请选择停用原因")
                        return false
                    }
                    if (!params.type) {
                        this.$message.error("请选择停用时间")
                        return false
                    }

                    refund(params).then(res => {
                        if (res.data.status == 200) {
                            this.$message.success("停用申请成功！")
                            this.isShowRefund = false
                            this.$refs.cloudTop.getRefundMsg()
                        }
                    }).catch(err => {
                        this.$message.error(err.data.msg)
                    })
                },
                // topcloud传回的实例状态
                getPowerStatus(e) {
                    if (e == 'on') {
                        this.powerList = [
                            {
                                id: 2,
                                label: '关机',
                                value: "off"
                            },
                            {
                                id: 5,
                                label: '强制关机',
                                value: "hardOff"
                            },
                            {
                                id: 3,
                                label: '重启',
                                value: "rebot"
                            },
                            {
                                id: 4,
                                label: '强制重启',
                                value: "hardRebot"
                            },
                        ]
                        this.powerStatus = 'off'
                    } else if (e == 'off') {
                        this.powerList = [
                            {
                                id: 1,
                                label: '开机',
                                value: "on"
                            },
                            {
                                id: 3,
                                label: '重启',
                                value: "rebot"
                            },
                            {
                                id: 4,
                                label: '强制重启',
                                value: "hardRebot"
                            },

                        ]
                        this.powerStatus = 'on'
                    } else {
                        this.powerList = [
                            {
                                id: 1,
                                label: '开机',
                                value: "on"
                            },
                            {
                                id: 2,
                                label: '关机',
                                value: "off"
                            },
                            {
                                id: 3,
                                label: '重启',
                                value: "rebot"
                            },
                            {
                                id: 4,
                                label: '强制重启',
                                value: "hardRebot"
                            },
                            {
                                id: 5,
                                label: '强制关机',
                                value: "hardOff"
                            },
                        ]
                    }


                },
                // 获取是否救援模式
                getRescueStatus(e) {
                    this.isRescue = e
                },
                // 显示退出救援模式确认框
                showQuitRescueDialog() {
                    this.isShowQuit = true
                },
                quitDgClose() {
                    this.isShowQuit = false
                },
                // 执行退出救援模式
                reQuitSub() {
                    const params = {
                        id: this.id
                    }
                    exitRescue(params).then(res => {
                        if (res.data.status === 200) {
                            this.$message.success(res.data.msg)
                            this.$refs.cloudTop.getRemoteInfo()
                            this.isShowQuit = false
                        }
                    }).catch(err => {
                        this.$message.error(err.data.msg)
                    })
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
