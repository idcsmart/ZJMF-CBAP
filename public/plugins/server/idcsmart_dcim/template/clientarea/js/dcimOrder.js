
const template = document.getElementsByClassName('template')[0]
Vue.prototype.lang = window.lang
new Vue({
    components: {
        asideMenu,
        topMenu,
        payDialog,
        discountCode
    },
    created() {
        this.getBackConfig()
        // this.id = location.href.split('?')[1].split('=')[1]
        this.getCommonData()
        // 获取数据中心数据
        this.getDataCenter()
        // 获取其它配置
        // this.getConfig()
        // 获取镜像数据
        this.getImage()
        // 获取sshkey数据
       // this.getSshKey()
        if (this.backConfig.password) {
            this.orderData.password = this.backConfig.password
        } else {
            this.autoPass()
        }
    },
    mounted() {
        this.addons_js_arr = JSON.parse(document.querySelector('#addons_js').getAttribute('addons_js')) // 插件列表
        const arr = this.addons_js_arr.map((item) => {
            return item.name
        })
        if (arr.includes('PromoCode')) {
            // 开启了优惠码插件
            this.isShowPromo = true
        }
        if (arr.includes('IdcsmartClientLevel')) {
            // 开启了等级优惠
            this.isShowLevel = true
        }
          // 获取sshkey数据
          arr.includes('IdcsmartSshKey') && this.getSshKey()
    },
    data() {
        return {
            name: "DCIM产品（套餐）",
            commonData: {},
            // 商品id
            id: 0,
            // 数据中心列表
            centerData: [],
            // 套餐数据
            packageData: [],
            // 分页展示的套餐数据
            packageDataPage: [],
            // 分页 参数
            packageDataParams: {
                page: 1,
                limit: 6,
                total: 1,
                pageTotal: 1
            },
            // 其它配置
            configData: {},
            // 镜像数据
            osData: [],
            // 是否额外磁盘
            isMoreDisk: false,
            // 是否开启备份功能
            isBack: false,
            // 是否开启快照功能
            isSnapshot: false,
            // 当前增加的磁盘最大id
            maxDiskId: 0,
            // 额外的磁盘数据
            moreDiskData: [

            ],
            // 额外的磁盘的价格
            moreDiskPrice: 0,
            // Shhkey列表
            sshKeyData: [],
            // 订单数据
            orderData: {
                // 数据中心id
                centerId: 0,
                country: '',
                city: '',
                // 备份id
                backId: '',
                // 快照id
                snapId: '',
                // 镜像分组id
                osGroupId: '',
                // 镜像分组名称
                osGroupName: '',
                // 镜像版本id
                osId: '',
                // 镜像版本
                osName: '',
                // 密码
                password: '',
                // sshkey
                key: '',
                // 当前选择的套餐的id
                packageId: '',
                // 商品数量
                qty: 1,
                // 付款周期
                duration: '',
            },
            // 是否勾选阅读
            isRead: false,
            // 镜像版本选择框数据
            osSelectData: [],
            // 镜像分组icon路径
            osIcon: '',
            // 使用密码还是 SSH Key pass:密码 key:SSH KEY
            isPassOrKey: 'pass',
            // 付款周期数据
            payCircleData: {
                name: ''
            },
            // 当前选择的备份的数量
            backNum: 0,
            // 当前选择的备份的价格
            backPrice: 0,
            // 当前选择的快照的数量
            snapNum: 0,
            // 当前选择的快照的价格
            snapPrice: 0,
            // 商品总价格
            totalPrice: 0,
            // 商品原总价
            original_price: 0,
            // 商品单价
            onePrice: 0,
            timerId: null,
            // 镜像价格
            osPrice: 0,
            // 套餐价格
            pagePrice: 0,
            // 套餐类型
            pageType: '',
            // 展示出来的周期数据
            showCircleData: [],

            // 优惠码相关
            // 输入框内容
            inputValue: '',
            codeVisible: false,
            // 使用的优惠码
            discountList: [],
            // 优惠码叠加总金额
            codePrice: 0,
            // 套餐价格 以及周期
            pageData: {
                num: 1
            },
            priceLoading: false,
            // 套餐所有周期价格
            durationPrice: [],
            priceData: {},
            cartDialog: false,
            priceId: 0,
            cartBtnLoading: false,
            buyBtnLoading: false,
            packageLoading: false,
            backConfig: {},
            position: 0,
            submitLoading: false,
            isNew: true,
            isPageIdNew: true,
            // 新增代金券
            billing_cycle_time: '', // 当前周期
            clDiscount: 0,      // 客户等级折扣金额
            addons_js_arr: [], // 插件数组
            code_discount: 0, // 优惠码折扣金额
            isShowPromo: false, // 是否开启优惠码
            isShowLevel: false, // 是否开启等级优惠
            isUseDiscountCode: false, // 是否使用优惠码
            customfield: {}, // 自定义字段
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
        // 选择套餐价格显示
        showFee(data, type) {
            console.log(type);
            let fee = ""
            // 有一次付清
            if (type == 'free') {
                fee = '免费/永久'
                return fee
            }
            if (data.onetime_fee && type == 'onetime') {
                if (data.onetime_fee == 0) {
                    fee = '免费/永久'
                } else {
                    fee = data.onetime_fee + '/永久'
                }

                return fee
            } else {// 无一次付清 显示最低的价格周期
                // 月
                if (data.month_fee) {
                    if (data.month_fee == 0) {
                        fee = '免费/月'
                    } else {
                        fee = parseFloat(data.month_fee).toFixed(2) + '/月'
                    }
                    return fee
                } else if (data.quarter_fee) {
                    if (data.quarter_fee == 0) {
                        fee = '免费/季度'
                    } else {
                        fee = parseFloat(data.quarter_fee).toFixed(2) + '/季度'
                    }
                    return fee
                } else if (data.year_fee) {
                    if (data.year_fee == 0) {
                        fee = '免费/年'
                    } else {
                        fee = parseFloat(data.year_fee).toFixed(2) + '/年'
                    }
                    return fee
                } else if (data.two_year) {
                    if (data.two_year == 0) {
                        fee = '免费/两年'
                    } else {
                        fee = parseFloat(data.two_year).toFixed(2) + '/两年'
                    }
                    return fee
                } else if (data.three_year) {
                    if (data.three_year == 0) {
                        fee = '免费/三年'
                    } else {
                        fee = parseFloat(data.three_year).toFixed(2) + '/三年'
                    }
                    return fee
                }
            }
        },
        showOneFee(price) {
            if (price == '0') {
                return '免费'
            } else {
                return price
            }
        },
        // 计算周期价格
        cyclePrice(price) {
            if (price == '免费') {
                return '免费'
            }
            console.log(this.pageData);
            if (this.pageData.durationName == '月') {
                return (price * 1).toFixed(2)
            }
            if (this.pageData.durationName == '季') {
                return (price * 3).toFixed(2)
            }
            if (this.pageData.durationName == '年') {
                return (price * 12).toFixed(2)
            }
            if (this.pageData.durationName == '两年') {
                return (price * 24).toFixed(2)
            }
            if (this.pageData.durationName == '三年') {
                return (price * 36).toFixed(2)
            }
        },
        filterMoney(money) {
            if (isNaN(money)) {
                return '0.00'
            } else {
                const temp = `${money}`.split('.')
                return parseInt(temp[0]).toLocaleString() + '.' + (temp[1] || '00')
            }
        }

    },
    watch: {
        // 计算额外磁盘的价格
        moreDiskData: {
            handler(newValue, oldValue) {
                // 计算价格
                let totalSize = 0
                newValue.map(item => {
                    totalSize += item.size
                })
                this.moreDiskPrice = totalSize / 10 * this.configData.price
                this.getConfigPrice()
            },
            deep: true
        },
        // 监听orderData 获取该配置下的价格
        orderData: {
            handler(newValue, oldValue) {
                this.getConfigPrice()
            },
            deep: true
        },
        isMoreDisk: {
            handler(newValue, oldValue) {
                this.getConfigPrice()
            },
        },

        isBack: {
            handler(newValue, oldValue) {
                this.getConfigPrice()
            },
        },
        isSnapshot: {
            handler(newValue, oldValue) {
                this.getConfigPrice()
            },
        },
        isPassOrKey: {
            handler(newValue, oldValue) {
                this.getConfigPrice()
            },
        },

        // 优惠码变化计算优惠码总价
        discountList: {
            handler(newValue, oldValue) {
                let total = 0
                newValue.forEach(item => {
                    total += Number(item.num)
                })
                if (total > this.totalPrice) {
                    total = this.totalPrice
                }
                this.codePrice = parseFloat(total).toFixed(2)
            },
            deep: true
        }

    },
    methods: {
        getBackConfig() {
            let url = window.location.href
            let getqyinfo = url.split('?')[1]
            let getqys = new URLSearchParams('?' + getqyinfo)
            let id = getqys.get('id')
            this.id = id
            this.name = getqys.get('name')

            let product_information = sessionStorage.getItem("product_information")

            // let config_options = getqys.get('config_options')
            if (product_information) {
                let config_options = JSON.parse(sessionStorage.getItem("product_information")).config_options
                this.position = JSON.parse(sessionStorage.getItem("product_information")).position
                this.orderData.qty = JSON.parse(sessionStorage.getItem("product_information")).qty
                this.backConfig = config_options
                this.customfield = JSON.parse(sessionStorage.getItem("product_information")).customfield
            }
        },
        // getId() {
        //     let url = window.location.href
        //     let getqyinfo = url.split('?')[1]
        //     let getqys = new URLSearchParams('?' + getqyinfo)
        //     let id = getqys.get('id')
        //     this.id = id

        //     let config_options = getqys.get('config_options')
        //     if (config_options) {
        //         config_options = JSON.parse(config_options)
        //     } else {
        //         console.log("空的");
        //     }
        // },
        // 获取通用配置
        getCommonData() {
            this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
            document.title = this.commonData.website_name + '-订购'

        },
        // 使用优惠码
        getDiscount(data) {
            this.customfield.promo_code = data[1]
            this.isUseDiscountCode = true
            this.getConfigPrice()
        },
        // 删除优惠码
        removeDiscountCode() {
            this.isUseDiscountCode = false
            this.customfield.promo_code = ''
            this.code_discount = 0
            this.getConfigPrice()
        },
        // 获取数据中心
        getDataCenter() {
            const params = {
                id: this.id
            }
            dataCenter(params).then(res => {
                if (res.data.status === 200) {
                    const list = res.data.data.list ? res.data.data.list : []
                    const data = []
                    if (list.length > 0) {
                        list.map(country => {
                            if (country.city) {
                                country.city.map(item => {
                                    let centerItem = {
                                        id: item.id,
                                        iso: country.iso,
                                        cityName: item.name,
                                        countryName: country.name_zh
                                    }
                                    data.push(centerItem)
                                })
                            }
                        })
                        this.centerData = data

                        // 判断回填信息里面是否有数据中心id
                        if (this.backConfig.data_center_id) {
                            this.orderData.centerId = this.backConfig.data_center_id
                        } else {
                            // 默认选取第一个数据中心
                            this.orderData.centerId = this.centerData[0].id
                            this.orderData.country = this.centerData[0].countryName
                            this.orderData.city = this.centerData[0].cityName
                        }

                    } else {
                        this.orderData.centerId = 0
                    }
                    // 拉取第一个数据中心的套餐
                    this.getOrderPackge()
                }
            })
        },
        // 数据中心切换
        centerChange(item) {
            this.orderData.centerId = item.id
            this.orderData.country = item.countryName
            this.orderData.city = item.cityName
            // 重新拉取套餐数据
            this.getOrderPackge()
        },
        // 获取套餐数据
        getOrderPackge() {
            this.packageLoading = true
            const params = {
                product_id: this.id,
                data_center_id: this.orderData.centerId
            }
            orderPackge(params).then(res => {
                if (res.data.status === 200) {
                    this.packageData = res.data.data.package
                    this.pageType = res.data.data.product.pay_type
                    // 获取到的套餐进行分页
                    this.packageDataParams.total = Math.ceil(this.packageData.length / 6)

                    // 判断回填信息中是否有套餐id
                    if (this.backConfig.package_id && this.isNew) {
                        // 获取该套餐id在套餐中的下标 计算所在页数
                        let index = this.packageData.findIndex(item => item.id == this.backConfig.package_id)
                        index = Math.ceil((index + 1) / 6)
                        this.pageChange(index)
                        this.isNew = false
                    } else {
                        this.pageChange(1)
                    }

                }
                this.packageLoading = false
            }).catch(error => {
                this.packageLoading = false
            })
        },
        // 套餐分页点击
        pageChange(cur) {
            this.packageDataParams.page = cur
            const data = this.packageDataParams
            let list = (data.page - 1) * data.limit
            this.packageDataPage = this.packageData.slice(list, list + data.limit)


            // 回填数据中是否包含套餐id
            if (this.backConfig.package_id && this.isPageIdNew) {
                this.orderData.packageId = this.backConfig.package_id

                this.packageDataPage.map(item => {
                    if (item.id == this.orderData.packageId) {
                        this.payCircleData = item
                    }
                })
                this.isPageIdNew = false
            } else {
                // 默认选中分页后的第一个套餐
                this.orderData.packageId = this.packageDataPage[0] ? this.packageDataPage[0].id : ''
                // 默认展示分页后的第一个套餐的周期
                this.payCircleData = this.packageDataPage[0]

            }
            this.filterPayCircleData()
        },
        // 套餐切换时
        packageItemClick(item) {
            this.orderData.packageId = item.id
            this.payCircleData = item
            this.filterPayCircleData()
        },
        // 套餐显示内容过滤
        filterPayCircleData() {
            // 展示出来的周期
            let showCircleData = []

            if (!JSON.stringify(this.payCircleData)) {
                this.showCircleData = showCircleData
                this.orderData.duration = showCircleData[0] ? showCircleData[0].duration : ''
                return false
            }
            // 免费
            if (this.pageType == 'free') {
                showCircleData.push({
                    duration: 'free',
                    money: '免费',
                    durationName: '永久',
                    num: 1
                })
            } else if (this.pageType == 'onetime') {
                showCircleData.push({
                    duration: 'onetime_fee',
                    money: this.payCircleData.onetime_fee == 0 ? '免费' : parseFloat(this.payCircleData.onetime_fee).toFixed(2),
                    durationName: '永久',
                    num: 1
                })
            } else {
                if (this.payCircleData.month_fee) {
                    showCircleData.push({
                        duration: 'month_fee',
                        money: this.payCircleData.month_fee == 0 ? '免费' : parseFloat(this.payCircleData.month_fee).toFixed(2),
                        durationName: '月',
                        num: 1
                    })
                }
                if (this.payCircleData.quarter_fee) {
                    showCircleData.push({
                        duration: 'quarter_fee',
                        money: this.payCircleData.quarter_fee == 0 ? '免费' : parseFloat(this.payCircleData.quarter_fee).toFixed(2),
                        durationName: '季',
                        num: 3
                    })
                }
                if (this.payCircleData.year_fee) {
                    showCircleData.push({
                        duration: 'year_fee',
                        money: this.payCircleData.year_fee == 0 ? '免费' : parseFloat(this.payCircleData.year_fee).toFixed(2),
                        durationName: '年',
                        num: 12
                    })
                }
                if (this.payCircleData.two_year) {
                    showCircleData.push({
                        duration: 'two_year',
                        money: this.payCircleData.two_year == 0 ? '免费' : parseFloat(this.payCircleData.two_year).toFixed(2),
                        durationName: '两年',
                        num: 24
                    })
                }
                if (this.payCircleData.three_year) {
                    showCircleData.push({
                        duration: 'three_year',
                        money: this.payCircleData.three_year == 0 ? '免费' : parseFloat(this.payCircleData.three_year).toFixed(2),
                        durationName: '三年',
                        num: 36
                    })
                }
            }
            this.showCircleData = showCircleData

            if (this.backConfig.duration) {
                this.orderData.duration = this.backConfig.duration
            } else {
                this.orderData.duration = showCircleData[0].duration
            }
            // console.log(showCircleData[0]);
            this.pageData = showCircleData[0]
        },
        // 获取其它配置
        getConfig() {
            const params = {
                product_id: this.id
            }
            config(params).then(res => {
                if (res.data.status === 200) {
                    this.configData = res.data.data
                    this.configData.disk_min_size = Number(this.configData.disk_min_size)
                    this.configData.disk_max_size = Number(this.configData.disk_max_size)

                    // 给备份选择框默认值
                    if (this.configData.backup_enable == 1) {
                        this.orderData.backId = this.configData.backup_option[0].id
                        this.backNum = this.configData.backup_option[0].num
                        this.backPrice = this.configData.backup_option[0].price
                    }
                    // 给快照选择框默认值
                    if (this.configData.snap_enable == 1) {
                        this.orderData.snapId = this.configData.snap_option[0].id
                        this.snapNum = this.configData.snap_option[0].num
                        this.snapPrice = this.configData.snap_option[0].price
                    }
                }
            })
        },
        // 获取镜像数据
        getImage() {
            const params = {
                id: this.id
            }
            image(params).then(res => {
                if (res.data.status === 200) {
                    this.osData = res.data.data.list
                    let ositem = {}
                    let osImage = []
                    // 有回显数据
                    if (this.backConfig.image_id) {
                        this.osData.map(image => {
                            image.image.map(item => {
                                if (item.id == this.backConfig.image_id) {
                                    ositem = item
                                    osImage = image
                                }
                            })
                        })
                        this.osSelectData = osImage.image
                        this.orderData.osGroupId = osImage.id
                        this.orderData.osGroupName = osImage.name
                        this.osIcon = "/plugins/server/common_cloud/view/img/" + osImage.name + '.png'
                        this.orderData.osId = ositem.id
                        this.orderData.osName = ositem.name
                        this.osPrice = ositem.price
                    } else {
                        this.osSelectData = this.osData[0].image
                        this.orderData.osGroupId = this.osData[0].id
                        this.orderData.osGroupName = this.osData[0].name
                        this.osIcon = "/plugins/server/common_cloud/view/img/" + this.osData[0].name + '.png'
                        this.orderData.osId = this.osData[0].image[0].id
                        this.orderData.osName = this.osData[0].image[0].name
                        this.osPrice = this.osData[0].image[0].price
                    }
                }
            })
        },
        // 镜像分组改变时
        osSelectGroupChange(e) {
            this.osData.map(item => {
                if (item.id == e) {
                    this.osSelectData = item.image
                    this.orderData.osId = null
                    this.orderData.osName = ''
                    this.orderData.osGroupName = item.name
                    this.osIcon = "/plugins/server/common_cloud/view/img/" + item.name + '.png'
                    this.orderData.osId = item.image[0].id
                    this.orderData.osName = item.image[0].name
                    this.osPrice = item.image[0].price
                }
            })
        },
        // 镜像版本改变时
        osSelectChange(e) {
            this.osSelectData.map(item => {
                if (item.id == e) {
                    this.orderData.osName = item.name
                    this.osPrice = item.price
                }
            })
        },
        // 获取SSH秘钥列表
        getSshKey() {
            const params = {
                page: 1,
                limit: 1000,
                orderby: "id",
                sort: "desc"
            }
            sshKey(params).then(res => {
                if (res.data.status === 200) {
                    this.sshKeyData = res.data.data.list
                    if (this.sshKeyData[0]) {
                        this.orderData.key = this.sshKeyData[0].id
                    }
                }
            })
        },
        // 跳转创建sshkey
        toCreateSshKey() {
            location.href = "security_ssh.html"
        },
        // 随机生成密码
        autoPass() {
            let pass = randomCoding(1) + 0 + genEnCode(9, 1, 1, 0, 1, 0)
            this.orderData.password = pass
        },

        // 商品购买数量减少
        delQty() {
            if (this.orderData.qty > 1) {
                this.orderData.qty--
            }
        },
        // 商品购买数量增加
        addQty() {
            this.orderData.qty++
        },
        // 增加额外磁盘
        addMoreDisk() {
            if (Number(this.configData.disk_max_num) < 1) {
                return false
            }
            if (this.moreDiskData.length < Number(this.configData.disk_max_num)) {
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
                    message: `最多只能新加${this.configData.disk_max_num}个磁盘`,
                    type: 'warning'
                });
            }
        },
        // 删除额外磁盘
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
                this.isMoreDisk = 0
            }
        },
        // 是否显示额外磁盘变化
        diskChange(e) {
            if (e) {
                if (this.moreDiskData.length == 0) {
                    this.addMoreDisk()
                }
            }
        },
        // 备份选择框改变时
        backSelectChange(e) {
            this.configData.backup_option.map(item => {
                if (item.id == e) {
                    this.backNum = item.num
                    this.backPrice = item.price
                }
            })
        },
        // 快照选择框改变时
        snapSelectChange(e) {
            this.configData.snap_option.map(item => {
                if (item.id == e) {
                    this.snapNum = item.num
                    this.snapPrice = item.price
                }
            })
        },
        // 周期选择
        feeItemClick(item) {
            console.log(item);
            this.orderData.duration = item.duration
            this.pageData = item
        },
        // 通过配置获取价格
        getConfigPrice() {
            if (this.timerId) {
                clearTimeout(this.timerId)
            }
            this.timerId = setTimeout(() => {
                this.priceLoading = true
                let data_disk = []

                if (this.isMoreDisk) {
                    this.moreDiskData.map(item => {
                        data_disk.push(item.size)
                    })
                }
                let params = {}
                if (this.isPassOrKey == 'pass') {
                    params = {
                        id: this.id,
                        config_options: {
                            data_center_id: this.orderData.centerId,
                            package_id: this.orderData.packageId,
                            image_id: this.orderData.osId,
                            duration: this.orderData.duration,
                            password: this.orderData.password,
                            data_disk,
                            backup_num_id: this.isBack ? this.orderData.backId : '',
                            snap_num_id: this.isSnapshot ? this.orderData.snapId : ''
                        }
                    }
                } else {
                    params = {
                        id: this.id,
                        config_options: {
                            data_center_id: this.orderData.centerId,
                            package_id: this.orderData.packageId,
                            image_id: this.orderData.osId,
                            duration: this.orderData.duration,
                            ssh_key_id: this.orderData.key,
                            data_disk,
                            backup_num_id: this.isBack ? this.orderData.backId : '',
                            snap_num_id: this.isSnapshot ? this.orderData.snapId : ''
                        }
                    }
                }
                // // 获取所有周期价格
                this.getDuration()
                // 修改配置计算价格

                this.priceId = this.priceId + 1
                const priceId = this.priceId
                configPrice(params).then(async (res) => {
                    if (res.data.status === 200 && this.priceId == priceId) {
                        let price = res.data.data.price * this.orderData.qty
                        this.billing_cycle_time = res.data.data.duration
                        price = price.toFixed(2)
                        this.onePrice = res.data.data.price
                        this.original_price = res.data.data.price * this.orderData.qty
                        this.discountList = []
                        this.priceData = res.data.data

                        const discountParams = {
                            id: this.id,
                            amount: price
                        }
                        // 开启了等级折扣插件
                        if (this.isShowLevel) {
                            // 获取等级抵扣价格
                            await clientLevelAmount(discountParams).then(res2 => {
                                if (res2.data.status === 200) {
                                    this.clDiscount = res2.data.data.discount  // 客户等级优惠金额
                                }
                            }).catch(error => {
                                this.clDiscount = 0
                            })
                        }
                        // 开启了优惠码插件
                        if (this.isShowPromo && this.customfield.promo_code) {
                            // 更新优惠码
                            await applyPromoCode({ // 开启了优惠券
                                scene: 'new',
                                product_id: this.id,
                                amount: this.onePrice,
                                billing_cycle_time: this.billing_cycle_time,
                                promo_code: this.customfield.promo_code,
                                qty: this.orderData.qty
                            }).then((resss) => {
                                this.isUseDiscountCode = true
                                this.code_discount = Number(resss.data.data.discount)
                            }).catch((err) => {
                                this.$message.error(err.data.msg)
                                this.removeDiscountCode()
                            })
                        }
                        this.totalPrice = ((price * 1000 - this.clDiscount * 1000 - this.code_discount * 1000) / 1000) > 0 ? ((price * 1000 - this.clDiscount * 1000 - this.code_discount * 1000) / 1000).toFixed(2) : 0
                        this.priceLoading = false
                    }
                }).catch(err => {
                    this.totalPrice = 0.00
                    this.onePrice = 0.00
                    this.priceLoading = false
                })
            }, 500)
        },
        // 添加购物车
        addCart() {
            // if (!this.isRead) {
            //     this.$message.error("请先阅读并勾选协议")
            //     return false
            // }
            this.cartBtnLoading = true
            let data_disk = []
            if (this.isMoreDisk) {
                this.moreDiskData.map(item => {
                    data_disk.push(item.size)
                })
            }
            const params = {
                product_id: this.id,
                config_options: {
                    data_center_id: this.orderData.centerId,
                    package_id: this.orderData.packageId,
                    image_id: this.orderData.osId,
                    duration: this.orderData.duration,
                    password: this.isPassOrKey == 'pass' ? this.orderData.password : this.orderData.key,
                    data_disk,
                    backup_num_id: this.isBack ? this.orderData.backId : '',
                    snap_num_id: this.isSnapshot ? this.orderData.snapId : ''
                },
                qty: this.orderData.qty,
                customfield: this.customfield // 自定义参数
            }
            cart(params).then(res => {
                if (res.data.status === 200) {
                    // location.href = './shoppingCar.html'
                    this.cartDialog = true
                    getCart().then(result => {
                        localStorage.setItem('cartNum', 'cartNum-' + result.data.data.list.length)
                    })

                }
                this.cartBtnLoading = false
            }).catch(error => {
                this.cartBtnLoading = false
                this.$message({
                    message: error.data.msg,
                    type: 'warning'
                });
            })


        },
        // 直接购买
        buyNow() {
            // if (!this.isRead) {
            //     this.$message.error("请先阅读并勾选协议")
            //     return false
            // }
            // 获取磁盘数组
            let data_disk = []
            if (this.isMoreDisk) {
                this.moreDiskData.forEach(item => {
                    data_disk.push(item.size)
                })
            }
            // 获取优惠码数组
            let codes = []
            this.discountList.forEach(item => {
                codes.push(item.name)
            })

            const params = {
                product_id: this.id,
                config_options: {
                    data_center_id: this.orderData.centerId,
                    package_id: this.orderData.packageId,
                    image_id: this.orderData.osId,
                    duration: this.orderData.duration,
                    password: this.isPassOrKey == 'pass' ? this.orderData.password : this.orderData.key,
                    data_disk,
                    backup_num_id: this.isBack ? this.orderData.backId : '',
                    snap_num_id: this.isSnapshot ? this.orderData.snapId : ''
                },
                customfield: this.customfield,
                qty: this.orderData.qty
            }

            // 直接传配置到结算页面
            sessionStorage.setItem('product_information', JSON.stringify(params))
            location.href = `settlement.html?id=${params.product_id}`
            // settle(params).then(res => {
            //     if (res.data.status === 200) {
            //         const orderId = res.data.data.order_id
            //         const amount = this.totalPrice
            //         const codePrice = this.codePrice
            //         this.$refs.payDialog.showPayDialog(orderId, amount - codePrice)
            //     }
            // }).catch(error => {
            //     this.$message({
            //         message: error.data.msg,
            //         type: 'warning'
            //     });
            // })
        },
        // 支付成功回调
        paySuccess(e) {
            console.log("成功", e);
            // 返回产品列表
            location.href = './cloudList.html'
        },
        // 取消支付回调
        payCancel(e) {
            // 返回
            console.log("取消", e);
            // 返回财务信息
            location.href = './finance.html'
        },
        // 优惠码相关
        checkCode() {
            if (!this.inputValue) {
                return false
            }
            if (this.discountList.find(item => item.name === this.inputValue)) {
                this.$message.warning("同一优惠码不能多次使用")
                return false
            }
            if (this.discountList.find(item => !item.overlay)) {
                this.$message.warning(`优惠码${item.name}不能与其它优惠码叠加使用`)
                return false
            }

            let data_disk = []
            if (this.isMoreDisk) {
                this.moreDiskData.map(item => {
                    data_disk.push(item.size)
                })
            }
            let cycles = []
            for (let i = 0; i < this.orderData.qty; i++) {
                cycles.push({
                    product_id: this.id,
                    amount: Number(this.totalPrice) / this.orderData.qty,
                    billing_cycle_time: this.orderData.duration,
                    config_options: {
                        data_center_id: this.orderData.centerId,
                        package_id: this.orderData.packageId,
                        image_id: this.orderData.osId,
                        duration: this.orderData.duration,
                        password: this.isPassOrKey == 'pass' ? this.orderData.password : this.orderData.key,
                        data_disk,
                        backup_num_id: this.isBack ? this.orderData.backId : '',
                        snap_num_id: this.isSnapshot ? this.orderData.snapId : ''
                    },
                })
            }
            const params = {
                promo_code: this.inputValue,
                scene: "New",
                total: Number(this.totalPrice),
                cycles
            }

            promoCode(params).then(res => {
                if (res.data.status === 200) {
                    const { overlay, discount } = res.data.data
                    // 如果之前有使用过优惠码，且当前优惠码不可以叠加使用
                    if (this.discountList.length > 0 && !overlay) {
                        this.$message.warning("当前优惠码不能与其它优惠码叠加使用")
                        return false
                    }

                    this.discountList.push({
                        name: this.inputValue,
                        num: discount,
                        overlay
                    })
                    this.codeVisible = false
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })

        },
        // 单项优惠码删除
        delCode(e) {
            this.discountList = this.discountList.filter(item => {
                return item.name != e
            })
        },
        toService() {
            window.open(this.commonData.terms_service_url);
        },
        toPrivacy() {
            window.open(this.commonData.terms_privacy_url);
        },
        // 获取商品折扣金额
        doClientLevelAmount() {
            const params = {
                id: this.id,
                amount: this.totalPrice
            }
            this.isShowLevel && clientLevelAmount(params).then(res => {
                if (res.data.status === 200) {
                    this.clDiscount = res.data.data.discount
                }
            }).catch(error => {
                this.clDiscount = 0
            })

        },
        // 获取套餐所有周期价格
        getDuration() {

            let data_disk = []
            if (this.isMoreDisk) {
                this.moreDiskData.forEach(item => {
                    data_disk.push(item.size)
                })
            }
            const params = {
                id: this.id,
                package_id: this.orderData.packageId,
                image_id: this.orderData.osId,
                data_disk,
                backup_num: this.isBack ? this.backNum : '',
                snap_num: this.isSnapshot ? this.snapNum : ''
            }

            duration(params).then(res => {
                if (res.data.status === 200) {
                    let data = res.data.data

                    if (this.pageType == 'onetime') {
                        console.log("onetime_fee");
                        data = data.filter(item => {
                            return item.duration == 'onetime_fee'
                        })
                    } else if (this.pageType == 'free') {
                        data = data.filter(item => {
                            return item.duration == 'free'
                        })
                    } else {
                        data = data.filter(item => {
                            return item.duration != 'onetime_fee'
                        })

                    }

                    this.durationPrice = data



                    this.durationPrice = data
                    if (this.backConfig.duration) {
                        this.pageData = this.backConfig.duration
                    } else {
                        this.pageData = this.durationPrice[0]
                    }
                }
            }).catch(error => {
                this.durationPrice = []
            })
        },
        // 前往购物车
        goToCart() {
            location.href = `shoppingCar.html`
            this.cartDialog = false
        },
        changeCart() {
            let data_disk = []
            if (this.isMoreDisk) {
                this.moreDiskData.map(item => {
                    data_disk.push(item.size)
                })
            }
            const params = {
                position: this.position,
                product_id: this.id,
                config_options: {
                    data_center_id: this.orderData.centerId,
                    package_id: this.orderData.packageId,
                    image_id: this.orderData.osId,
                    duration: this.orderData.duration,
                    password: this.isPassOrKey == 'pass' ? this.orderData.password : this.orderData.key,
                    data_disk,
                    backup_num_id: this.isBack ? this.orderData.backId : '',
                    snap_num_id: this.isSnapshot ? this.orderData.snapId : ''
                },
                qty: this.orderData.qty,
                customfield: this.customfield // 自定义参数
            }

            this.submitLoading = true
            cartPosition(params).then(res => {
                this.submitLoading = false
                location.href = `shoppingCar.html`
            }).catch(error => {
                this.submitLoading = false
                console.log('errore', error)
                this.$message.error(error.data.msg)
            })
        },
    },

}).$mount(template)

