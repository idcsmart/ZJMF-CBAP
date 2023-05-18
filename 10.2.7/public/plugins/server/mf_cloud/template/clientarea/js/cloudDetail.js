const template = document.getElementById('product_detail_cloud')
Vue.prototype.lang = window.lang
new Vue({
    components: {
        asideMenu,
        topMenu,
        payDialog,
        pagination,
        discountCode,
        cashCoupon,
        cashBack
    },
    created() {
        // 获取产品id
        this.id = location.href.split('?')[1].split('=')[1]
        // 获取通用信息
        this.getCommonData()
        // 获取产品详情
        this.getHostDetail()
        // 获取实例详情
        this.getCloudDetail()
        // 获取实例状态
        this.getCloudStatus()
        // 获取cpu 使用信息
        this.getRealData()
        // 获取救援模式状态
        this.getRemoteInfo()
        // 获取该实例的磁盘
        this.doGetDiskList()
        this.getstarttime(1)
    },
    mixins: [mixin],
    mounted() {
        // this.getCpuList()
        // this.getBwList()
        // this.getDiskLIoList()
        // this.getMemoryList()
        this.addons_js_arr = JSON.parse(document.querySelector('#addons_js').getAttribute('addons_js')) // 插件列表
        const arr = this.addons_js_arr.map((item) => {
            return item.name
        })
        console.log(arr);
        if (arr.includes('PromoCode')) {
            // 开启了优惠码插件
            this.isShowPromo = true
            // 优惠码信息
            this.getPromoCode()
        }
        if (arr.includes('IdcsmartClientLevel')) {
            // 开启了等级优惠
            this.isShowLevel = true
        }
        if (arr.includes('IdcsmartVoucher')) {
            // 开启了代金券
            this.isShowCash = true
        }
        // 开启了插件才拉取接口
        // 退款相关
        arr.includes('IdcsmartRefund') && this.getRefundMsg()
        arr.includes('IdcsmartRenew') && this.getRenewStatus()
    },
    updated() {
        // // 关闭loading
        document.getElementById('mainLoading').style.display = 'none';
        document.getElementById('product_detail_cloud').style.display = 'block'
        // document.getElementsByClassName('product_detail_cloud')[0].style.display = 'block'
    },
    computed: {
        vpcIps() {
            if (
                this.vpc_ips.vpc2 !== undefined &&
                this.vpc_ips.vpc3 !== undefined &&
                this.vpc_ips.vpc4 !== undefined
            ) {
                const str =
                    this.vpc_ips.vpc1.value +
                    '.' +
                    this.vpc_ips.vpc2 +
                    '.' +
                    this.vpc_ips.vpc3 +
                    '.' +
                    this.vpc_ips.vpc4 +
                    '/' +
                    this.vpc_ips.vpc6.value
                return str
            } else {
                return ''
            }
        },
        calcCpu() {
            return this.params.cpu + lang.mf_cores
        },
        calcCpuList() { // 根据区域来判断计算可选cpu数据
            if (this.activeName1 === 'fast') {
                return
            }
            const temp = this.configLimitList.filter(item => item.type === 'data_center' && this.params.data_center_id === item.data_center_id) || []
            const cpu = temp.reduce((all, cur) => {
                all.push(...cur.cpu.split(','))
                return all
            }, [])
            return this.cpuList.filter(item => !cpu.includes(String(item.value)))
        },
        calaMemoryList() { // 计算可选内存，根据 cpu + 区域
            if (this.activeName1 === 'fast') {
                return
            }
            const temp = this.configLimitList.filter(item => item.cpu.split(',').includes(String(this.params.cpu)))
            if (temp.length === 0) { // 没有匹配到限制条件
                if (this.memoryList[0]?.type === 'radio') {
                    return this.memoryList
                } else {
                    this.memoryTip = this.createTip(this.memoryArr)
                    this.memMarks = this.createMarks(this.memoryArr) // data 原数据，目标marks
                    return this.memoryArr
                }
            }
            // 分两种情况，单选和范围，单选：memory 范围，min_memory，max_memory
            if (temp[0].memory) {
                const memory = Array.from(new Set(temp.reduce((all, cur) => {
                    all.push(...cur.memory.split(','))
                    return all
                }, [])))
                const filMem = this.memoryList.filter(item => !memory.includes(String(item.value)))
                return filMem
            } else {  // 范围
                let fArr = []
                temp.forEach(item => {
                    fArr.push(...this.createArr([item.min_memory, item.max_memory]))
                })
                fArr = Array.from(new Set(fArr))
                const filterArr = this.memoryArr.filter(item => !fArr.includes(item))
                this.memoryTip = this.createTip(filterArr)
                this.memMarks = this.createMarks(filterArr) // data 原数据，目标marks
                return filterArr.filter(item => !fArr.includes(item))
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
        },
        vpcIps: {
            handler(newVal) {
                this.ips = newVal
            },
            immediate: true,
            deep: true
        }
        // bsData: {
        //     handler(newValue, oldValue) {
        //         // 开启备份/快照的价格
        //         this.getBsPrice()
        //     },
        //     deep: true
        // }
    },
    data() {
        return {
            commonData: {
                currency_prefix: '',
                currency_suffix: ''
            },
            activeName: "2",
            configLimitList: [], // 限制规则
            configObj: {},
            backup_config: [],
            snap_config: [],
            // 实例id
            id: null,
            // 产品id
            product_id: 0,
            // 实例状态
            status: 'operating',
            // 实例状态描述
            statusText: '',
            cpu_realData: {},
            // 代金券对象
            cashObj: {},
            // 是否救援系统
            isRescue: false,
            // 是否开启代金券
            isShowCash: false,
            // 产品详情
            hostData: {
                billing_cycle_name: '',
                status: "Active",
                first_payment_amount: '',
                renew_amount: '',
            },
            // 实例详情
            cloudData: {
                data_center: {
                    iso: 'CN',
                },
                image: {
                    icon: ''
                },
                package: {
                    cpu: '',
                    memory: '',
                    out_bw: '',
                    system_disk_size: '',
                },
                system_disk: {},
                iconName: 'Windows'
            },
            // 是否显示支付信息
            isShowPayMsg: false,
            imgBaseUrl: '',
            // 是否显示添加备注弹窗
            isShowNotesDialog: false,
            // 备份输入框内容
            notesValue: '',
            // 显示重装系统弹窗
            isShowReinstallDialog: false,
            // 重装系统弹窗内容
            reinstallData: {
                image_id: null,
                password: null,
                ssh_key_id: null,
                port: null,
                osGroupId: null,
                osId: null,
                type: 'pass'
            },
            // 镜像数据
            osData: [],
            // 镜像版本选择框数据
            osSelectData: [],
            // 镜像图片地址
            osIcon: '',
            // Shhkey列表
            sshKeyData: [],
            // 错误提示信息
            errText: '',
            // 镜像是否需要付费
            isPayImg: false,
            payMoney: 0,
            // 镜像优惠价格
            payDiscount: 0,
            // 镜像优惠码价格
            payCodePrice: 0,
            onOffvisible: false,
            rebotVisibel: false,
            codeString: '',
            isShowIp: false,
            renewLoading: false,  // 续费计算折扣loading
            // 停用信息
            refundData: {

            },
            // 停用状态
            refundStatus: {
                Pending: "待审核",
                Suspending: "待停用",
                Suspend: "停用中",
                Suspended: "已停用",
                Refund: "已退款",
                Reject: "审核驳回",
                Cancelled: "已取消"
            },

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

            addons_js_arr: [], // 插件列表
            isShowPromo: false, // 是否开启优惠码
            isShowLevel: false, // 是否开启等级优惠
            // 续费
            // 显示续费弹窗
            isShowRenew: false, // 续费的总计loading
            renewBtnLoading: false, // 续费按钮的loading
            // 续费页面信息
            renewPageData: [],

            renewActiveId: '',
            renewOrderId: 0,
            isShowRefund: false,
            hostStatus: {
                Unpaid: { text: "未付款", color: "#F64E60", bgColor: "#FFE2E5" },
                Pending: { text: "开通中", color: "#3699FF", bgColor: "#E1F0FF" },
                Active: { text: "正常", color: "#1BC5BD", bgColor: "#C9F7F5" },
                Suspended: { text: "已暂停", color: "#F99600", bgColor: "#FFE2E5" },
                Deleted: { text: "已删除", color: "#9696A3", bgColor: "#F2F2F7" },
                Failed: { text: "开通中", color: "#FFA800", bgColor: "#FFF4DE" }
            },
            isRead: false,
            isShowPass: false,
            passHidenCode: "",
            rescueStatusData: {},

            // 管理开始
            // 开关机状态
            powerStatus: 'on',
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
            ],
            loading1: false,
            loading2: false,
            loading3: false,
            loading4: false,
            loading5: false,
            ipValueData: [],
            // 重置密码弹窗数据
            rePassData: {
                password: '',
                checked: false
            },
            // 是否展示重置密码弹窗
            isShowRePass: false,
            // 救援模式弹窗数据
            rescueData: {
                type: "1",
                password: ''
            },
            // 是否展示救援模式弹窗
            isShowRescue: false,
            // 是否展示退出救援模式弹窗
            isShowQuit: false,
            ipValue: null,

            /* 升降级相关*/
            // 升降级套餐列表
            upgradeList: [],
            // 升降级表单
            upgradePackageId: '',
            // 当前切换的升降级套餐
            changeUpgradeData: {},
            // 是否展示升降级弹窗
            isShowUpgrade: false,
            // 升降级参数
            upParams: {
                customfield: {
                    promo_code: '', // 优惠码
                    voucher_get_id: '', // 代金券码
                },
                duration: '', // 周期
                isUseDiscountCode: false, // 是否使用优惠码
                clDiscount: 0, // 用户等级折扣价
                code_discount: 0, // 优惠码折扣价
                cash_discount: 0, // 代金券折扣价
                original_price: 0,// 原价
                totalPrice: 0 // 现价
            },


            // 续费参数
            renewParams: {
                id: 0, //默认选中的续费id
                isUseDiscountCode: false, // 是否使用优惠码
                customfield: {
                    promo_code: '', // 优惠码
                    voucher_get_id: '', // 代金券码
                },
                duration: '', // 周期
                billing_cycle: '', // 周期时间
                clDiscount: 0, // 用户等级折扣价
                cash_discount: 0, // 代金券折扣价
                code_discount: 0, // 优惠码折扣价
                original_price: 0,// 原价
                base_price: 0,
                totalPrice: 0 // 现价
            },



            // 磁盘 开始
            diskLoading: false,
            isSubmitEngine: false,
            // 实例磁盘列表
            // 过滤后
            diskList: [],
            // 未过滤
            allDiskList: [],
            // 订购/扩容标识
            isOrderOrExpan: true,
            // 订购磁盘参数
            orderDiskData: {
                id: 0,
                remove_disk_id: [],
                add_disk: []
            },
            // 新增磁盘数据
            moreDiskData: [],
            // 订购磁盘弹窗相关
            isShowDg: false,
            // 其他配置信息
            configData: {},
            systemDiskList: [],
            dataDiskList: [],
            // 磁盘总价格
            moreDiskPrice: 0,
            // 磁盘优惠价格
            moreDiscountkDisPrice: 0,
            // 磁盘优惠码优惠价格
            moreCodePrice: 0,
            // 订购磁盘弹窗 中 当前配置磁盘
            oldDiskList: [],
            oldDiskList2: [],
            orderTimer: null,
            expanTimer: null,
            // 磁盘订单id
            diskOrderId: 0,
            // 订购/扩容标识
            isOrderOrExpan: true,
            // 是否显示扩容弹窗
            isShowExpansion: false,
            // 扩容磁盘参数
            expanOrderData: {
                id: 0,
                resize_data_disk: []
            },
            // 扩容价格
            expansionDiskPrice: 0,
            // 扩容折扣
            expansionDiscount: 0,
            // 扩容优惠码优惠
            expansionCodePrice: 0,
            // 网络开始
            netLoading: false,
            netDataList: [],
            netParams: {
                page: 1,
                limit: 20,
                pageSizes: [20, 50, 100],
                total: 200,
                orderby: 'id',
                sort: 'desc',
                keywords: '',
            },
            // 网络流量
            flowData: {},
            // 日志开始
            logDataList: [],
            logParams: {
                page: 1,
                limit: 20,
                pageSizes: [20, 50, 100],
                total: 200,
                orderby: 'id',
                sort: 'desc',
                keywords: '',
            },
            logLoading: false,

            // 备份与快照开始
            dataList1: [],
            // 备份列表数据
            dataList1: [],
            // 快照列表数据
            dataList2: [],
            backLoading: false,
            snapLoading: false,
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
            // true 标记为备份  false 标记为快照
            isBs: true,
            // 弹窗表单数据
            createBsData: {
                id: 0,
                name: '',
                disk_id: 0
            },
            // 实例磁盘列表
            // 是否显示弹窗
            isShwoCreateBs: false,
            cgbsLoading: false,
            isShowhyBs: false,
            safeDialogShow: false,
            // 还原显示数据
            restoreData: {
                restoreId: 0,
                // 实例名称
                cloud_name: '',
                // 创建时间
                time: '',
            },
            // 是否显示删除快照弹窗
            isShowDelBs: false,
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
            bsDataLoading: false,
            // 获取快照/备份升降级价格 参数 生成快照/备份数量升降级订单参数
            bsData: {
                id: 0,
                type: '',
                backNum: 0,
                snapNum: 0,
                money: 0,
                moneyDiscount: 0,
                codePrice: 0,
                duration: '月'
            },
            // 是否显示开启备份弹窗
            isShowOpenBs: false,
            // 快照备份订单id
            bsOrderId: 0,
            chartSelectValue: "1",
            // 统计图表开始
            echartLoading1: false,
            echartLoading2: false,
            echartLoading3: false,
            echartLoading4: false,
            isShowPowerChange: false,
            powerTitle: "",
            diskPriceLoading: false,
            ipPriceLoading: false,
            ipMoney: 0.00,
            ipDiscountkDisPrice: 0.00,
            ipCodePrice: 0.00,
            upgradePriceLoading: false,
            trueDiskLength: 0,
            isShowAutoRenew: false,
            vpcDataList: [],
            vpcLoading: false,
            vpcParams: {
                page: 1,
                limit: 20,
                pageSizes: [20, 50, 100],
                total: 200,
                orderby: 'id',
                sort: 'desc',
                keywords: '',
            },
            isShowengine: false,
            engineID: '',
            curEngineId: '',
            engineSearchLoading: false,
            productOptions: [],
            productParams: {
                page: 1,
                limit: 20,
                keywords: '',
                status: 'Active',
                orderby: 'id',
                sort: 'desc',
                data_center_id: ''
            },
            isShowAddVpc: false,
            plan_way: 0,
            vpc_ips: {
                vpc1: {
                    tips: lang.range1,
                    value: 10,
                    select: [10, 172, 192]
                },
                vpc2: 0,
                vpc3: 0,
                vpc3Tips: '',
                vpc4: 0,
                vpc4Tips: '',
                vpc6: {
                    value: 16,
                    select: [16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28]
                },
                min: 0,
                max: 255
            },
            vpcName: '',
            ips: '',
            safeOptions: [],
            safeID: '',
            upData: {
                cpuName: '',

            },

            cpuName: '',
            memoryName: '',
            bwName: '',
            flowName: '',
            defenseName: '',
            memoryList: [],
            cpuList: [],
            memoryArr: [], // 范围时内存数组
            activeName1: 'custom', // fast, custom
            memoryType: false,
            memoryTip: '',
            params: { // 配置参数
                data_center_id: '',
                cpu: '',
                memory: 1,
                image_id: 0,
                system_disk: {
                    size: '',
                    disk_type: ''
                },
                data_disk: [],
                backup_num: '',
                snap_num: '',
                line_id: '',
                bw: '',
                flow: '',
                peak_defence: '',
                ip_num: '',
                duration_id: '',
                network_type: 'normal',
                // 提交购买
                name: '', // 主机名
                ssh_key_id: '',
                /* 安全组 */
                security_group_id: '',
                security_group_protocol: [],
                password: '',
                re_password: '',
                vpc: { // 新建-系统分配的时候都不传
                    id: '', // 选择已有的vc
                    ips: '', // 自定义的时候
                },
                notes: ''
            },
            lineDetail: {}, // 线路详情：bill_type, flow, bw, defence , ip

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
    methods: {
        async getLineDetails() {
            try {
                // 获取线路详情，
                const res = await getLineDetail({ id: this.product_id, line_id: this.cloudData.line.id })
                this.lineDetail = res.data.data
                // 默认选择带宽
                if (this.lineDetail.bw) {
                    if (this.cloudData?.bw !== 0) { // 初次回填
                        this.params.bw = this.cloudData.bw * 1
                    } else {
                        this.params.bw = this.lineDetail.bw[0]?.value || this.lineDetail.bw[0]?.min_value
                    }
                    this.bwName = this.params.bw + 'M'
                    // 循环生成带宽可选数组
                    const fArr = []
                    this.lineDetail.bw.forEach(item => {
                        fArr.push(...this.createArr([item.min_value, item.max_value]))
                    })
                    this.bwArr = fArr
                    this.bwTip = this.createTip(fArr)
                    this.bwMarks = this.createMarks(this.bwArr)
                }
                // 默认选择流量
                if (this.lineDetail.flow) {
                    if (this.cloudData?.flow) { // 初次回填
                        this.params.flow = this.cloudData.flow * 1
                    } else {
                        this.params.flow = this.lineDetail.flow[0]?.value
                    }
                    this.flowName = this.params.flow > 0 ? (this.params.flow + 'G') : lang.mf_tip28
                }
                // 默认选择cpu 
                this.params.cpu = this.cloudData.cpu !== 0 ? this.cloudData.cpu : this.calcCpuList[0]?.value
                this.cpuName = this.cloudData.cpu !== 0 ? this.cloudData.cpu + lang.mf_cores : this.calcCpuList[0]?.value + lang.mf_cores
                // 默认选择内存
                if (this.memoryList[0].type === 'radio') {
                    this.params.memory = this.cloudData.memory !== 0 ? this.cloudData.memory * 1 : this.calaMemoryList[0]?.value * 1
                } else {
                    this.params.memory = this.cloudData.memory !== 0 ? this.cloudData.memory * 1 : this.calaMemoryList[0] * 1
                }
                this.memoryName = this.cloudData.memory !== 0 ? this.cloudData.memory + 'G' : this.calaMemoryList[0]?.value + 'G'
                // 默认选择防御
                this.params.peak_defence = this.cloudData.peak_defence !== 0 ? this.cloudData.peak_defence : ''
                this.defenseName = this.params.peak_defence + 'G'
                this.getCycleList()
            } catch (error) {
                console.log('####', error)
            }
        },
        changeCpu(e) { // 切换cpu，改变内存
            this.params.cpu = e.replace(lang.mf_cores, '')
            setTimeout(() => {
                this.params.memory = this.memoryList[0].type === 'radio' ? this.calaMemoryList[0]?.value : this.calaMemoryList[0]
                this.memoryName = this.params.memory + 'G'
                this.getCycleList()
            }, 0)
        },
        // 切换防御
        changeDefence(e) {
            this.params.peak_defence = e.replace('G', '')
            setTimeout(() => {
                this.getCycleList()
            }, 0)
        },
        changeBw(e) {
            this.params.bw = e.replace('M', '')
            // 计算价格
            setTimeout(() => {
                this.getCycleList()
            }, 0)
        },
        // 切换流量
        changeFlow(e) {
            if (e === lang.mf_tip28) {
                this.params.flow = 0
            } else {
                this.params.flow = e.replace('G', '') * 1
            }

            setTimeout(() => {
                this.getCycleList()
            }, 0)
        },
        // 切换内存
        changeMemory(e) {
            this.params.memory = e.replace('G', '')
            setTimeout(() => {
                this.getCycleList()
            }, 0)
        },
        createArr([m, n]) {// 生成数组
            let temp = []
            for (let i = m; i <= n; i++) {
                temp.push(i)
            }
            return temp
        },
        createTip(arr) { // 生成范围提示
            let tip = ''
            let num = []
            arr.forEach((item, index) => {
                if (arr[index + 1] - item > 1) {
                    num.push(index)
                }
            })
            if (num.length === 0) {
                tip = `${arr[0]}-${arr[arr.length - 1]}`
            } else {
                tip += `${arr[0]}-${arr[num[0]]},`
                num.forEach((item, ind) => {
                    tip += arr[item + 1] + '-' + (arr[num[ind + 1]] ? (arr[num[ind + 1]] + ',') : arr[arr.length - 1])
                })
            }
            return tip
        },
        changeBwNum(num) {
            if (!this.bwArr.includes(num)) {
                this.bwArr.forEach((item, index) => {
                    if (num > item && num < this.bwArr[index + 1]) {
                        this.params.bw = (num - item) > (this.bwArr[index + 1] - num) ? this.bwArr[index + 1] : item
                    }
                })
            }
            this.getCycleList()
        },
        createMarks(data) {
            const obj = {
                0: '',
                25: '',
                50: '',
                75: '',
                100: ''
            }
            const range = data[data.length - 1] - data[0]
            obj[0] = `${data[0]}`
            obj[25] = `${Math.ceil(range * 0.25)}`
            obj[50] = `${Math.ceil(range * 0.5)}`
            obj[75] = `${Math.ceil(range * 0.75)}`
            obj[100] = `${data[data.length - 1]}`
            return obj
        },
        changeMem(num) {
            if (!this.calaMemoryList.includes(num)) {
                this.calaMemoryList.forEach((item, index) => {
                    if (num > item && num < this.calaMemoryList[index + 1]) {
                        this.params.memory = (num - item) > (this.calaMemoryList[index + 1] - num) ? this.calaMemoryList[index + 1] : item
                    }
                })
            }
            this.getCycleList()
        },

        changeVpc4() {
            switch (this.vpc_ips.vpc6.value) {
                case 25:
                    this.vpc_ips.vpc4 = this.near([0, 128], this.vpc_ips.vpc4)
                    break
                case 26:
                    this.vpc_ips.vpc4 = this.near([0, 64, 128, 192], this.vpc_ips.vpc4)
                    break
                case 27:
                    this.vpc_ips.vpc4 = this.near(
                        [0, ...this.productArr(32, 224)],
                        this.vpc_ips.vpc4
                    )
                    break
                case 28:
                    this.vpc_ips.vpc4 = this.near(
                        [0, ...this.productArr(16, 240)],
                        this.vpc_ips.vpc4
                    )
                    break
            }
        },
        productArr(min, max, step) {
            const arr = []
            for (let i = min; i < max + 1; i = i + min) {
                arr.push(i)
            }
            return arr
        },
        near(arr, n) {
            arr.sort(function (a, b) {
                return Math.abs(a - n) - Math.abs(b - n)
            })
            return arr[0]
        },
        changeVpcMask(value) {
            switch (value) {
                case 16:
                    this.vpc_ips.vpc3 = 0
                    this.vpc_ips.vpc4 = 0
                    break
                case 17:
                    this.vpc_ips.vpc3 = this.near([0, 128], this.vpc_ips.vpc3)
                    this.vpc_ips.vpc3Tips = lang.range2
                    this.vpc_ips.vpc4 = 0
                    break
                case 18:
                    this.vpc_ips.vpc3 = this.near([0, 64, 128, 192], this.vpc_ips.vpc3)
                    this.vpc_ips.vpc3Tips = lang.range3
                    this.vpc_ips.vpc4 = 0
                    break
                case 19:
                    this.vpc_ips.vpc3 = this.near(
                        [0, ...this.productArr(32, 224)],
                        this.vpc_ips.vpc3
                    )
                    this.vpc_ips.vpc3Tips = lang.range4
                    this.vpc_ips.vpc4 = 0
                    break
                case 20:
                    this.vpc_ips.vpc3 = this.near(
                        [0, ...this.productArr(16, 240)],
                        this.vpc_ips.vpc3
                    )
                    this.vpc_ips.vpc3Tips = lang.range5
                    this.vpc_ips.vpc4 = 0
                    break
                case 21:
                    this.vpc_ips.vpc3 = this.near(
                        [0, ...this.productArr(8, 248)],
                        this.vpc_ips.vpc3
                    )
                    this.vpc_ips.vpc3Tips = lang.range6
                    this.vpc_ips.vpc4 = 0
                    break
                case 22:
                    this.vpc_ips.vpc3 = this.near(
                        [0, ...this.productArr(4, 252)],
                        this.vpc_ips.vpc3
                    )
                    this.vpc_ips.vpc3Tips = lang.range7
                    this.vpc_ips.vpc4 = 0
                    break
                case 23:
                    this.vpc_ips.vpc3 = this.near(
                        [0, ...this.productArr(2, 254)],
                        this.vpc_ips.vpc3
                    )
                    this.vpc_ips.vpc3Tips = lang.range8
                    this.vpc_ips.vpc4 = 0
                    break
                case 24:
                    this.vpc_ips.vpc3Tips = lang.range9
                    this.vpc_ips.vpc4 = 0
                    break
                case 25:
                    this.vpc_ips.vpc4 = this.near([0, 128], this.vpc_ips.vpc4)
                    this.vpc_ips.vpc4Tips = lang.range2
                    this.vpc_ips.vpc3Tips = lang.range1
                    break
                case 26:
                    this.vpc_ips.vpc4 = this.near([0, 64, 128, 192], this.vpc_ips.vpc4)
                    this.vpc_ips.vpc4Tips = lang.range3
                    this.vpc_ips.vpc3Tips = lang.range1
                    break
                case 27:
                    this.vpc_ips.vpc4 = this.near(
                        [0, ...this.productArr(32, 224)],
                        this.vpc_ips.vpc4
                    )
                    this.vpc_ips.vpc4Tips = lang.range4
                    this.vpc_ips.vpc3Tips = lang.range1
                    break
                case 28:
                    this.vpc_ips.vpc4 = this.near(
                        [0, ...this.productArr(16, 240)],
                        this.vpc_ips.vpc4
                    )
                    this.vpc_ips.vpc4Tips = lang.range12
                    this.vpc_ips.vpc3Tips = lang.range1
                    break
            }
        },
        /* vpc校验规则 */
        changeVpc3() {
            switch (this.vpc_ips.vpc6.value) {
                case 16:
                    this.vpc_ips.vpc3 = 0
                    break
                case 17:
                    this.vpc_ips.vpc3 = this.near([0, 128], this.vpc_ips.vpc3)
                    break
                case 18:
                    this.vpc_ips.vpc3 = this.near([0, 64, 128, 192], this.vpc_ips.vpc3)
                    break
                case 19:
                    this.vpc_ips.vpc3 = this.near(
                        [0, ...this.productArr(32, 224)],
                        this.vpc_ips.vpc3
                    )
                    break
                case 20:
                    this.vpc_ips.vpc3 = this.near(
                        [0, ...this.productArr(16, 240)],
                        this.vpc_ips.vpc3
                    )
                    break
                case 21:
                    this.vpc_ips.vpc3 = this.near(
                        [0, ...this.productArr(8, 248)],
                        this.vpc_ips.vpc3
                    )
                    break
                case 22:
                    this.vpc_ips.vpc3 = this.near(
                        [0, ...this.productArr(4, 252)],
                        this.vpc_ips.vpc3
                    )
                    break
                case 23:
                    this.vpc_ips.vpc3 = this.near(
                        [0, ...this.productArr(2, 254)],
                        this.vpc_ips.vpc3
                    )
                    break
            }
        },
        changeVpcIp() {
            switch (this.vpc_ips.vpc1.value) {
                case 10:
                    this.vpc_ips.vpc1.tips = lang.range1
                    this.vpc_ips.min = 0
                    this.vpc_ips.max = 255
                    break
                case 172:
                    this.vpc_ips.vpc1.tips = lang.range10
                    if (this.vpc_ips.vpc2 < 16 || this.vpc_ips.vpc2 > 31) {
                        this.vpc_ips.vpc2 = 16
                    }
                    this.vpc_ips.min = 16
                    this.vpc_ips.max = 31
                    break
                case 192:
                    this.vpc_ips.vpc1.tips = lang.range11
                    this.vpc_ips.vpc2 = 168
                    this.vpc_ips.min = 168
                    this.vpc_ips.max = 168
                    break
            }
        },
        // 跳转对应页面
        handleClick() {
            switch (this.activeName) {
                case '1':
                    this.chartSelectValue = "1"
                    this.getstarttime(1)
                    this.getCpuList()
                    this.getBwList()
                    this.getDiskLIoList()
                    this.getMemoryList()
                    break;
                case '2':

                    break;
                case '3':
                    this.doGetDiskList()
                    break;
                case '4':
                    this.chartSelectValue = "1"
                    this.getIpList()
                    this.doGetFlow()
                    this.getVpcNetwork()
                    this.getSafeList()
                    this.getstarttime(1)
                    this.getBwList()
                    break;
                case '5':
                    this.getBackupList()
                    this.getSnapshotList()
                    break;
                case '6':
                    this.getLogList()
                    break;
            }
        },
        // 获取通用配置
        getCommonData() {
            this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
            document.title = this.commonData.website_name + '-产品详情'

        },
        // 获取自动续费状态
        getRenewStatus() {
            const params = {
                id: this.id
            }
            renewStatus(params).then(res => {
                console.log("renew", res);
                if (res.data.status === 200) {
                    const status = res.data.data.status
                    this.isShowPayMsg = status == 1 ? true : false
                }
            })
        },
        autoRenewChange() {
            console.log(this.isShowPayMsg);
            this.isShowAutoRenew = true
        },
        autoRenewDgClose() {
            this.isShowPayMsg = !this.isShowPayMsg
            this.isShowAutoRenew = false
        },
        doAutoRenew() {
            const params = {
                id: this.id,
                status: this.isShowPayMsg ? 1 : 0
            }
            rennewAuto(params).then(res => {
                if (res.data.status === 200) {
                    this.$message.success('请求成功')
                    this.isShowAutoRenew = false
                    this.getRenewStatus()
                }
            }).catch(error => {
                this.$message.error(error.data.msg)
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
                    this.hostData.status_name = this.hostStatus[res.data.data.host.status].text

                    // 判断下次缴费时间是否在十天内
                    if (((this.hostData.due_time * 1000) - new Date().getTime()) / (24 * 60 * 60 * 1000) <= 10) {
                        this.isRead = true
                    }
                    this.product_id = this.hostData.product_id
                    // 获取镜像数据
                    this.getConfigData()
                    this.getImage()
                    // 获取其它配置
                }
            })
        },
        // 获取实例详情
        getCloudDetail() {
            const params = {
                id: this.id
            }
            cloudDetail(params).then(res => {
                if (res.data.status === 200) {
                    this.cloudData = res.data.data
                    this.productParams.data_center_id = res.data.data.data_center.id
                    this.$emit('getclouddetail', this.cloudData)
                }
            })
        },
        // 关闭备注弹窗
        notesDgClose() {
            this.isShowNotesDialog = false
        },
        // 显示 修改备注 弹窗
        doEditNotes() {
            this.isShowNotesDialog = true
            this.notesValue = this.hostData.notes
        },
        // 修改备注提交
        subNotes() {
            const params = {
                id: this.id,
                notes: this.notesValue
            }
            editNotes(params).then(res => {
                if (res.data.status === 200) {
                    // 重新拉取产品详情
                    this.getHostDetail()
                    this.$message({
                        message: '修改成功',
                        type: 'success'
                    });
                    this.isShowNotesDialog = false
                }
            }).catch(err => {
                this.$message.error(err.data.msg);
            })
        },
        // 返回产品列表页
        goBack() {
            window.history.back();
        },
        // 关闭重装系统弹窗
        reinstallDgClose() {
            this.isShowReinstallDialog = false
        },
        // 展示重装系统弹窗
        showReinstall() {
            this.errText = ''
            this.reinstallData.password = null
            this.reinstallData.key = null
            this.reinstallData.port = null
            this.isShowReinstallDialog = true
        },
        // 提交重装系统
        doReinstall() {
            let isPass = true
            const data = this.reinstallData

            if (!data.osId) {
                isPass = false
                this.errText = "请选择操作系统"
                return false
            }

            if (!data.port) {
                isPass = false
                this.errText = "请输入端口号"
            }

            if (data.type == 'pass') {
                if (!data.password) {
                    isPass = false
                    this.errText = "请输入密码"
                    return false
                }
            } else {
                if (!data.key) {
                    isPass = false
                    this.errText = "请选择SSHKey"
                    return false
                }
            }

            if (isPass) {
                this.errText = ""
                let params = {
                    id: this.id,
                    image_id: data.osId,
                    port: data.port
                }

                if (data.type == 'pass') {
                    params.password = data.password
                } else {
                    params.ssh_key_id = data.key
                }


                // 调用重装系统接口
                reinstall(params).then(res => {
                    if (res.data.status == 200) {
                        this.$message.success(res.data.msg)
                        this.isShowReinstallDialog = false
                        this.getCloudStatus()
                    }
                }).catch(err => {
                    this.errText = err.data.msg
                })
            }

        },
        // 检查产品是否购买过镜像
        doCheckImage() {
            const params = {
                id: this.id,
                image_id: this.reinstallData.osId
            }
            checkImage(params).then(async (res) => {
                if (res.data.status === 200) {
                    const p = Number(res.data.data.price)
                    this.isPayImg = p > 0 ? true : false
                    this.payMoney = p
                    if (this.isShowLevel) {
                        await clientLevelAmount({ id: this.product_id, amount: res.data.data.price }).then((ress) => {
                            this.payDiscount = Number(ress.data.data.discount)
                        }).catch(() => {
                            this.payDiscount = 0
                        })
                    }
                    // 开启了优惠码插件
                    if (this.isShowPromo) {
                        // 更新优惠码
                        await applyPromoCode({ // 开启了优惠券
                            scene: 'upgrade',
                            product_id: this.product_id,
                            amount: p,
                            billing_cycle_time: this.hostData.billing_cycle_time,
                            promo_code: '',
                            host_id: this.id
                        }).then((resss) => {
                            this.payCodePrice = Number(resss.data.data.discount)
                        }).catch((err) => {
                            this.$message.error(err.data.msg)
                            this.payCodePrice = 0
                        })
                    }
                    this.renewLoading = false
                    this.payMoney = (p * 1000 - this.payCodePrice * 1000 - this.payDiscount * 1000) / 1000 > 0 ? (p * 1000 - this.payCodePrice * 1000 - this.payDiscount * 1000) / 1000 : 0
                }
            })
        },
        // 购买镜像
        payImg() {
            const params = {
                id: this.id,
                image_id: this.reinstallData.osId
            }
            imageOrder(params).then(res => {
                if (res.data.status === 200) {
                    const orderId = res.data.data.id
                    const amount = this.payMoney
                    this.$refs.topPayDialog.showPayDialog(orderId, amount)
                }
            })
        },
        // 获取镜像数据
        getImage() {
            const params = {
                id: this.product_id
            }
            image(params).then(res => {
                if (res.data.status === 200) {
                    this.osData = res.data.data.list
                    this.osSelectData = this.osData[0].image
                    this.reinstallData.osGroupId = this.osData[0].id
                    this.osIcon = "/plugins/server/mf_cloud/view/img/" + this.osData[0].name + '.png'
                    this.reinstallData.osId = this.osData[0].image[0].id
                    this.doCheckImage()
                }
            })
        },
        // 镜像分组改变时
        osSelectGroupChange(e) {
            this.osData.map(item => {
                if (item.id == e) {
                    this.osSelectData = item.image
                    this.osIcon = "/plugins/server/mf_cloud/view/img/" + item.name + '.png'
                    this.reinstallData.osId = item.image[0].id
                    this.doCheckImage()
                }
            })
        },
        // 镜像版本改变时
        osSelectChange(e) {
            this.doCheckImage()
        },
        // 随机生成密码
        autoPass() {
            let pass = randomCoding(1) + 0 + genEnCode(9, 1, 1, 0, 1, 0)
            this.reinstallData.password = pass
            // 重置密码
            this.rePassData.password = pass
            // 救援系统密码
            this.rescueData.password = pass
        },
        // 随机生成port
        autoPort() {
            this.reinstallData.port = genEnCode(3, 1, 0, 0, 0, 0)
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
                    console.log(this.sshKeyData);
                }
            })
        },
        // 获取实例状态
        getCloudStatus() {
            const params = {
                id: this.id
            }
            cloudStatus(params).then(res => {
                if (res.status === 200) {
                    this.status = res.data.data.status
                    this.statusText = res.data.data.desc
                    if (this.status == 'operating') {
                        this.getCloudStatus()
                    } else {
                        this.$emit('getstatus', res.data.data.status)
                        let e = this.status
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
                    }
                }
            }).catch(err => {
                this.getCloudStatus()
            })
        },
        // 获取救援模式状态
        getRemoteInfo() {
            const params = {
                id: this.id
            }
            remoteInfo(params).then(res => {
                if (res.data.status === 200) {
                    this.rescueStatusData = res.data.data
                    const length = this.rescueStatusData.password.length >= 6 ? 6 : this.rescueStatusData.password.length
                    for (let i = 0; i < length; i++) {
                        this.passHidenCode += "*"
                    }
                    this.isRescue = (res.data.data.rescue == 1)
                    this.$emit('getrescuestatus', this.isRescue)
                }
            })
        },
        // 控制台点击
        doGetVncUrl() {
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
        getVncUrl() {
            this.loading2 = true
            this.doGetVncUrl()
        },
        // 开机
        doPowerOn() {
            this.onOffvisible = false
            const params = {
                id: this.id
            }
            powerOn(params).then(res => {
                if (res.data.status === 200) {
                    this.$message.success("开机发起成功!")
                    this.status = 'operating'
                    this.getCloudStatus()
                    this.loading1 = false
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
                this.loading1 = false
            })
        },
        // 关机
        doPowerOff() {
            this.onOffvisible = false
            const params = {
                id: this.id
            }
            powerOff(params).then(res => {
                if (res.data.status === 200) {
                    this.$message.success("关机发起成功!")
                    this.status = 'operating'
                    this.getCloudStatus()
                }
                this.loading1 = false
            }).catch(err => {
                this.$message.error(err.data.msg)
                this.loading1 = false
            })
        },
        // 重启
        doReboot() {
            this.rebotVisibel = false
            const params = {
                id: this.id
            }
            reboot(params).then(res => {
                if (res.data.status === 200) {
                    this.$message.success("重启发起成功!")
                    this.status = 'operating'
                    this.getCloudStatus()
                }
                this.loading1 = false
            }).catch(err => {
                this.$message.error(err.data.msg)
                this.loading1 = false
            })
        },
        // 强制重启
        doHardReboot() {
            const params = {
                id: this.id
            }
            hardReboot(params).then(res => {
                if (res.data.status === 200) {
                    this.$message.success("强制重启发起成功!")
                    this.status = 'operating'
                    this.getCloudStatus()
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
                    this.$message.success("强制重启发起成功!")
                    this.status = 'operating'
                    this.getCloudStatus()
                }
                this.loading1 = false
            }).catch(err => {
                this.$message.error(err.data.msg)
                this.loading1 = false
            })
        },
        // 获取产品停用信息
        getRefundMsg() {
            const params = {
                id: this.id
            }
            refundMsg(params).then(res => {
                if (res.data.status === 200) {
                    this.refundData = res.data.data.refund
                }
            }).catch(err => {
                this.refundData = null
            })
        },
        // 获取cup/内存使用信息
        getRealData() {
            realData(this.id).then((res) => {
                this.cpu_realData = res.data.data
            })
        },
        // 支付成功回调
        paySuccess(e) {
            if (e == this.renewOrderId) {
                // 刷新实例详情
                this.getHostDetail()
                return true
            }
            if (e == this.diskOrderId) {
                this.doGetDiskList()
            }
            if (e == this.bsOrderId) {
                this.getConfigData()
                this.getBackupList()
                this.getSnapshotList()
                this.getCloudDetail()
            }
            this.getIpList()
            this.getCloudDetail()
            this.doGetDiskList()
            this.getConfigData()
            this.getHostDetail()
            // 重新检查当前选择镜像是否购买
            this.doCheckImage()


        },
        // 取消支付回调
        payCancel(e) {
            console.log(e);
        },
        // 获取优惠码信息
        getPromoCode() {
            const params = {
                id: this.id
            }
            promoCode(params).then(res => {
                if (res.data.status === 200) {
                    let codes = res.data.data.promo_code
                    console.log(codes);
                    let code = ''
                    codes.map(item => {
                        code += item + ","
                    })
                    code = code.slice(0, -1)
                    this.codeString = code
                }
            })
        },
        // 升降级使用优惠码
        getUpDiscount(data) {
            this.upParams.customfield.promo_code = data[1]
            this.upParams.isUseDiscountCode = true
            this.upParams.code_discount = Number(data[0])
            this.getCycleList()
        },
        // 移除升降级优惠码
        removeUpDiscountCode() {
            this.upParams.isUseDiscountCode = false
            this.upParams.customfield.promo_code = ''
            this.upParams.code_discount = 0
            this.getCycleList()
        },
        // 升降级使用代金券
        upUseCash(val) {
            this.cashObj = val
            const price = val.price ? Number(val.price) : 0
            this.upParams.cash_discount = price
            this.upParams.customfield.voucher_get_id = val.id
            this.getCycleList()
        },

        // 升降级移除代金券
        upRemoveCashCode() {
            this.$refs.cashRef.closePopver()
            this.cashObj = {}
            this.upParams.cash_discount = 0
            this.upParams.customfield.voucher_get_id = ''
            this.upParams.totalPrice = ((this.upParams.original_price * 1000 - this.upParams.clDiscount * 1000 - this.upParams.cash_discount * 1000 - this.upParams.code_discount * 1000) / 1000) > 0 ? ((this.upParams.original_price * 1000 - this.upParams.cash_discount * 1000 - this.upParams.clDiscount * 1000 - this.upParams.code_discount * 1000) / 1000).toFixed(2) : 0
        },

        // 续费使用代金券
        reUseCash(val) {
            this.cashObj = val
            const price = val.price ? Number(val.price) : 0
            this.renewParams.cash_discount = price
            this.renewParams.customfield.voucher_get_id = val.id
            this.renewParams.totalPrice = ((this.renewParams.original_price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.code_discount * 1000) / 1000) > 0 ? ((this.renewParams.original_price * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000).toFixed(2) : 0
        },
        // 续费移除代金券
        reRemoveCashCode() {
            this.$refs.cashRef.closePopver()
            this.cashObj = {}
            this.renewParams.cash_discount = 0
            this.renewParams.customfield.voucher_get_id = ''
            this.renewParams.totalPrice = ((this.renewParams.original_price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.code_discount * 1000) / 1000) > 0 ? ((this.renewParams.original_price * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000).toFixed(2) : 0
        },
        // 续费使用优惠码
        async getRenewDiscount(data) {
            this.renewParams.customfield.promo_code = data[1]
            this.renewParams.isUseDiscountCode = true
            this.renewParams.code_discount = Number(data[0])
            const price = this.renewParams.base_price
            const discountParams = { id: this.product_id, amount: price }
            // 开启了等级折扣插件
            if (this.isShowLevel) {
                // 获取等级抵扣价格
                await clientLevelAmount(discountParams).then(res2 => {
                    if (res2.data.status === 200) {
                        this.renewParams.clDiscount = Number(res2.data.data.discount) // 客户等级优惠金额
                    }
                }).catch(error => {
                    this.renewParams.clDiscount = 0
                })
            }
            this.renewParams.totalPrice = ((price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.code_discount * 1000) / 1000) > 0 ? ((price * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000).toFixed(2) : 0
        },
        // 移除续费的优惠码
        removeRenewDiscountCode() {
            this.renewParams.isUseDiscountCode = false
            this.renewParams.customfield.promo_code = ''
            this.renewParams.code_discount = 0
            const price = this.renewParams.original_price
            this.renewParams.totalPrice = ((price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000) > 0 ? ((price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000).toFixed(2) : 0
        },

        // 显示续费弹窗
        showRenew() {
            if (this.renewBtnLoading) return
            this.renewBtnLoading = true
            // 获取续费页面信息
            const params = {
                id: this.id,
            }
            this.isShowRenew = true
            this.renewLoading = true
            renewPage(params).then(async (res) => {
                if (res.data.status === 200) {
                    this.renewBtnLoading = false
                    this.renewPageData = res.data.data.host
                    this.renewActiveId = this.renewPageData[0].id
                    this.renewParams.billing_cycle = this.renewPageData[0].billing_cycle
                    this.renewParams.duration = this.renewPageData[0].duration
                    this.renewParams.original_price = this.renewPageData[0].price
                    this.renewParams.base_price = this.renewPageData[0].base_price
                    let price = this.renewPageData[0].price
                    this.renewParams.totalPrice = price > 0 ? Number(price) : 0
                    // 开启了优惠码插件
                    if (this.isShowPromo && this.renewParams.customfield.promo_code) {
                        // 更新优惠码
                        await applyPromoCode({ // 开启了优惠券
                            scene: 'renew',
                            product_id: this.product_id,
                            amount: this.renewPageData[0].base_price,
                            billing_cycle_time: this.renewParams.duration,
                            promo_code: this.renewParams.customfield.promo_code,
                        }).then((resss) => {
                            price = this.renewPageData[0].base_price
                            this.renewParams.isUseDiscountCode = true
                            this.renewParams.code_discount = Number(resss.data.data.discount)
                        }).catch((err) => {
                            this.$message.error(err.data.msg)
                            this.removeRenewDiscountCode()
                        })
                    }
                    const discountParams = { id: this.product_id, amount: price }
                    // 开启了等级折扣插件
                    if (this.isShowLevel) {
                        // 获取等级抵扣价格
                        await clientLevelAmount(discountParams).then(res2 => {
                            if (res2.data.status === 200) {
                                this.renewParams.clDiscount = Number(res2.data.data.discount) // 客户等级优惠金额
                            }
                        }).catch(error => {
                            this.renewParams.clDiscount = 0
                            this.renewParams.totalPrice = price
                        })
                    }
                    this.renewParams.totalPrice = ((price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.code_discount * 1000) / 1000) > 0 ? ((price * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000).toFixed(2) : 0
                }
                this.renewLoading = false
            }).catch(err => {
                this.renewBtnLoading = false
                this.renewLoading = false
                this.$message.error(err.data.msg)
            })

        },
        // 续费弹窗关闭
        renewDgClose() {
            this.isShowRenew = false
            this.removeRenewDiscountCode()
            this.reRemoveCashCode()
        },
        // 续费提交
        subRenew() {
            const params = {
                id: this.id,
                billing_cycle: this.renewParams.billing_cycle,
                customfield: this.renewParams.customfield
            }
            renew(params).then(res => {
                if (res.data.status === 200) {
                    if (res.data.code == 'Paid') {
                        this.$message.success(res.data.msg)
                        this.getHostDetail()
                    } else {
                        this.isShowRenew = false
                        this.renewOrderId = res.data.data.id
                        const orderId = res.data.data.id
                        const amount = this.renewParams.totalPrice
                        this.$refs.topPayDialog.showPayDialog(orderId, amount)
                    }

                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },
        // 续费周期点击
        async renewItemChange(item) {
            this.reRemoveCashCode()
            this.renewLoading = true
            this.renewActiveId = item.id
            this.renewParams.duration = item.duration
            this.renewParams.billing_cycle = item.billing_cycle
            let price = item.price
            this.renewParams.original_price = item.price
            this.renewParams.base_price = item.base_price

            // 开启了优惠码插件
            if (this.isShowPromo && this.renewParams.isUseDiscountCode) {
                // 更新优惠码
                await applyPromoCode({ // 开启了优惠券
                    scene: 'renew',
                    product_id: this.product_id,
                    amount: item.base_price,
                    billing_cycle_time: this.renewParams.duration,
                    promo_code: this.renewParams.customfield.promo_code,
                }).then((resss) => {
                    price = item.base_price
                    this.renewParams.isUseDiscountCode = true
                    this.renewParams.code_discount = Number(resss.data.data.discount)
                }).catch((err) => {
                    this.$message.error(err.data.msg)
                    this.removeRenewDiscountCode()
                })
            }
            const discountParams = { id: this.product_id, amount: price }
            // 开启了等级折扣插件
            if (this.isShowLevel) {
                // 获取等级抵扣价格
                await clientLevelAmount(discountParams).then(res2 => {
                    if (res2.data.status === 200) {
                        this.renewParams.clDiscount = Number(res2.data.data.discount) // 客户等级优惠金额
                    }
                }).catch(error => {
                    this.renewParams.clDiscount = 0
                })
            }
            this.renewParams.totalPrice = ((price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.code_discount * 1000) / 1000) > 0 ? ((price * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000).toFixed(2) : 0
            this.renewLoading = false
        },
        // 升降级点击
        showUpgrade() {
            this.getLineDetails()
            this.isShowUpgrade = true
            this.$message({
                showClose: true,
                message: '请注意，若现有配置在折算后金额高于升降级所需支付的金额，结余金额不会退款！',
                type: 'warning',
                duration: 10000
            });
        },
        // 关闭升降级弹窗
        upgradeDgClose() {
            this.isShowUpgrade = false
            this.removeUpDiscountCode()
            this.reRemoveCashCode()
        },
        // 获取升降级价格
        getCycleList() {
            this.upgradePriceLoading = true
            const params = {
                id: this.id,
                cpu: this.params.cpu,
                memory: this.params.memory,
                bw: this.params.bw,
                flow: this.params.flow,
                peak_defence: this.params.peak_defence,
            }
            upgradePackagePrice(params).then(async (res) => {
                if (res.data.status == 200) {
                    let price = res.data.data.price // 当前产品的价格
                    if (price < 0) {
                        this.upParams.original_price = 0
                        this.upParams.totalPrice = 0
                        this.upgradePriceLoading = false
                        return
                    }
                    this.upParams.original_price = price
                    this.upParams.totalPrice = price
                    // 开启了等级优惠
                    if (this.isShowLevel) {
                        await clientLevelAmount({ id: this.product_id, amount: price }).then((ress) => {
                            this.upParams.clDiscount = Number(ress.data.data.discount)
                        }).catch(() => {
                            this.upParams.clDiscount = 0
                        })
                    }
                    // 开启了优惠码插件
                    if (this.isShowPromo) {
                        // 更新优惠码
                        await applyPromoCode({ // 开启了优惠券
                            scene: 'upgrade',
                            product_id: this.product_id,
                            amount: price,
                            billing_cycle_time: this.hostData.billing_cycle_time,
                            promo_code: this.upParams.customfield.promo_code,
                            host_id: this.id
                        }).then((resss) => {
                            this.upParams.isUseDiscountCode = true
                            this.upParams.code_discount = Number(resss.data.data.discount)
                        }).catch((err) => {
                            this.upParams.isUseDiscountCode = false
                            this.upParams.customfield.promo_code = ''
                            this.upParams.code_discount = 0
                            this.$message.error(err.data.msg)
                        })
                    }
                    this.upParams.totalPrice = ((price * 1000 - this.upParams.clDiscount * 1000 - this.upParams.cash_discount * 1000 - this.upParams.code_discount * 1000) / 1000) > 0 ? ((price * 1000 - this.upParams.cash_discount * 1000 - this.upParams.clDiscount * 1000 - this.upParams.code_discount * 1000) / 1000).toFixed(2) : 0
                    this.upgradePriceLoading = false
                } else {
                    this.upParams.original_price = 0
                    this.upParams.clDiscount = 0
                    this.upParams.isUseDiscountCode = false
                    this.upParams.customfield.promo_code = ''
                    this.upParams.code_discount = 0
                    this.upParams.totalPrice = 0
                    this.upgradePriceLoading = false
                }

            }).catch(error => {
                this.upParams.original_price = 0
                this.upParams.clDiscount = 0
                this.upParams.isUseDiscountCode = false
                this.upParams.customfield.promo_code = ''
                this.upParams.code_discount = 0
                this.upParams.totalPrice = 0
                this.upgradePriceLoading = false
            })
        },
        // 升降级提交
        upgradeSub() {
            const params = {
                id: this.id,
                cpu: this.params.cpu,
                memory: this.params.memory,
                bw: this.params.bw,
                flow: this.params.flow,
                peak_defence: this.params.peak_defence,
                customfield: this.upParams.customfield
            }
            upgradeOrder(params).then(res => {
                if (res.data.status === 200) {
                    this.$message.success("生成升降级订单成功")
                    this.isShowUpgrade = false
                    const orderId = res.data.data.id
                    // 调支付弹窗
                    this.$refs.topPayDialog.showPayDialog(orderId, 0)
                } else {
                    this.$message.error(err.data.msg)
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
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
            this.reRemoveCashCode()
            this.getCycleList()
        },

        // 取消停用
        quitRefund() {
            const params = {
                id: this.refundData.id
            }
            cancel(params).then(res => {
                if (res.data.status == 200) {
                    this.$message.success("取消停用成功")
                    this.getRefundMsg()
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },
        // 关闭停用
        refundDgClose() {

        },
        // 删除实例点击
        showRefund() {
            const params = {
                host_id: this.id
            }
            // refundMsg(params).then(res => {
            //     if (res.data.status === 200) {
            //         console.log(res);
            //     }
            // })
            // 获取停用页面信息
            refundPage(params).then(res => {
                if (res.data.status == 200) {
                    this.refundPageData = res.data.data
                    // if (this.refundPageData.allow_refund === 0) {
                    //     this.$message.warning("不支持退款")
                    // } else {
                    //     this.isShowRefund = true
                    // }
                    this.isShowRefund = true
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
                    this.$message.success("申请成功！")
                    this.isShowRefund = false
                    this.getRefundMsg()
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },
        // 管理开始
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
                this.doReboot()
            }
            if (this.powerStatus == 'hardRebot') {
                this.doHardReboot()

            }
            if (this.powerStatus == 'hardOff') {
                this.doHardOff()
            }
            this.isShowPowerChange = false

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

                    resetPassword(params).then(res => {
                        if (res.data.status === 200) {
                            this.$message.success("重置密码成功")
                            this.isShowRePass = false
                            this.getCloudStatus()
                        }
                        this.loading5 = false
                    }).catch(error => {
                        this.errText = error.data.msg
                        this.loading5 = false
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
                        this.getRemoteInfo()
                    }
                    this.isShowRescue = false
                    this.loading3 = false
                }).catch(err => {
                    this.errText = err.data.msg
                    this.loading3 = false
                })
            }
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
                    this.getRemoteInfo()
                    this.isShowQuit = false
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },

        // 获取磁盘列表
        doGetDiskList() {
            this.diskLoading = true
            const params = {
                id: this.id
            }
            getDiskList(params).then(res => {
                this.diskList = res.data.data.list || []
                this.allDiskList = res.data.data.list
                this.trueDiskLength = res.data.data.list.filter((item) => { return item.type2 !== 'system' }).length
                this.diskLoading = false
            }).catch(err => {
                this.diskLoading = false
            })
        },
        // 显示扩容弹窗
        showExpansion() {
            // 标记打开扩容弹窗
            this.isOrderOrExpan = false
            this.expansionDiskPrice = 0.00
            this.expansionDiscount = 0.00
            this.expansionCodePrice = 0.00
            this.oldDiskList = []
            this.diskList.forEach((item) => {
                if (item.type2 !== 'system') {
                    item.selectList = []
                    this.dataDiskList.forEach((items) => {
                        if (items.other_config.disk_type === item.type && (items.type === 'step' || items.type === 'total')) {
                            item.selectList.push(items)
                            item.max_value = items.max_value
                        }
                        if (items.other_config.disk_type === item.type && (items.type === 'radio')) {
                            if (items.value >= item.size) {
                                item.selectList.push(items)
                            }
                        }
                    })
                    item.min_value = item.size
                    this.oldDiskList.push(JSON.parse(JSON.stringify(item)))
                }
            })
            this.isShowExpansion = true
        },
        // 显示订购磁盘弹窗
        showDg() {
            // 标记打开订购磁盘弹窗
            this.isOrderOrExpan = true
            this.oldDiskList2 = this.diskList.map(item => ({ ...item }))
            this.orderDiskData = {
                id: 0,
                remove_disk_id: [],
                add_disk: []
            }
            this.moreDiskData = []
            this.addMoreDisk()
            this.isShowDg = true
        },
        addTypeChange(val, item) {
            item.size = item.selectList[0][item.type][0].value
        },
        changeType(val, item) {
            item.size = item.selectList[0][item.type].min_value
        },
        goSSHpage(id) {
            location.href = `/security_ssh.htm`
        },
        // 新增磁盘项目        
        addMoreDisk() {
            // 最多存在的磁盘数目
            const max = 16
            // 已有磁盘的数目
            const oldNum = this.oldDiskList2.filter((item) => { return item.type2 !== 'system' }).length
            // 已新增磁盘的数目
            const newNum = this.moreDiskData.length
            if ((newNum + oldNum) < max) {
                const diskData = [...this.moreDiskData]
                const itemData = {}
                let max_value = 0
                const obj = {
                    disk_typeList: [],
                }
                const arr = this.dataDiskList.map((item) => {
                    return JSON.parse(JSON.stringify(item))
                })
                arr.forEach((items) => {
                    if (arr[0].type === 'radio') {
                        if (items.max_value > max_value) {
                            max_value = items.max_value
                        }
                        obj.type = 'radio'
                        if (items.other_config.disk_type === '') {
                            items.other_config.disk_type = '默认'
                        }
                        if (!obj.disk_typeList.includes(items.other_config.disk_type)) {
                            const type = items.other_config.disk_type
                            obj.disk_typeList.push(type)
                            obj[type] = []
                        }
                        obj[items.other_config.disk_type].push({
                            label: items.value + 'G',
                            value: items.value
                        })
                    } else {
                        obj.type = 'input'
                        if (items.other_config.disk_type === '') {
                            items.other_config.disk_type = '默认'
                        }
                        if (!obj.disk_typeList.includes(items.other_config.disk_type)) {
                            const type = items.other_config.disk_type
                            obj.disk_typeList.push(type)
                            obj[type] = {
                                config: [],
                                min_value: 0,
                                max_value: 0,
                                tips: ''
                            }
                        }
                        obj[items.other_config.disk_type].config.push([items.min_value, items.max_value])
                    }
                })
                obj.disk_typeList.forEach((item) => {
                    const arr = []
                    const arr1 = []
                    obj[item].config.forEach((is) => {
                        arr.push(...this.createArr([is[0], is[1]]))
                        arr1.push(...is)
                    })
                    obj[item].min_value = Math.min.apply(Math, arr1);
                    obj[item].max_value = Math.max.apply(Math, arr1);
                    obj[item].tips = this.createTip(arr)
                })
                if (this.dataDiskList.length !== 0) {
                    itemData.size = this.dataDiskList[0].type === 'radio' ? this.dataDiskList[0].other_config.disk_type : obj[obj.disk_typeList[0]]?.min_value
                    itemData.disk_type = this.dataDiskList[0].other_config.data_disk_type
                    itemData.selectList = [obj]
                    itemData.min_value = this.dataDiskList[0].type === 'radio' ? 0 : obj[obj.disk_typeList[0]]?.min_value
                    itemData.max_value = this.dataDiskList[0].type === 'radio' ? 0 : obj[obj.disk_typeList[0]]?.max_value
                    itemData.type = this.dataDiskList[0].type === 'radio' ? obj.disk_typeList[0] : obj.disk_typeList[0]
                }
                diskData.push(itemData)
                diskData.map((item, index) => {
                    item.index = index + 1
                })
                this.moreDiskData = diskData
                this.handlerType(this.moreDiskData, 'data')
            } else {
                this.$message({
                    message: `最多只能配置16个磁盘`,
                    type: 'warning'
                });
            }
        },
        // 初始化处理系统盘，数据盘类型
        handlerType(data, type) {

        },
        // 获取其他配置
        getConfigData() {
            const params = {
                id: this.product_id
            }
            getOrderConfig(params).then(res => {
                if (res.data.status === 200) {
                    this.configData = res.data.data
                    this.systemDiskList = res.data.data.system_disk
                    this.dataDiskList = res.data.data.data_disk
                    this.memoryList = res.data.data.memory
                    this.cpuList = res.data.data.cpu
                    this.configLimitList = res.data.data.config_limit
                    this.configObj = res.data.data.config
                    this.backup_config = res.data.data.backup_config
                    this.snap_config = res.data.data.snap_config
                    if (res.data.data.memory.length > 0 && res.data.data.memory[0].type !== 'radio') { // 范围的时候生成默认范围数组
                        this.memoryArr = res.data.data.memory.reduce((all, cur) => {
                            all.push(...this.createArr([cur.min_value, cur.max_value]))
                            return all
                        }, [])
                    }
                    if (this.memoryList.length > 0) {
                        if (this.memoryList[0].type === 'radio') {
                            this.memoryType = true
                        } else {
                            this.memoryType = false
                        }
                    }
                }
            })
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
        delOldSize2(id) {
            this.oldDiskList2 = this.oldDiskList2.filter(item => {
                return item.id != id
            })
            this.orderDiskData.remove_disk_id.push(id)
        },
        // 删除新增的磁盘项
        delMoreDisk(id) {
            const diskData = this.moreDiskData.filter(item => {
                return item.index != id
            })
            this.moreDiskData = diskData.map((item, index) => {
                item.index = index + 1
                return item
            })
        },
        selectIpValue(val) {
            if (this.ipValue !== val) {
                this.ipValue = val
                this.getIpPrice()
            }
        },
        // 获取附加ip价格
        getIpPrice() {
            this.ipPriceLoading = true
            ipPrice({ id: this.id, ip_num: this.ipValue }).then(async (res) => {
                if (this.isShowLevel) {
                    await clientLevelAmount({ id: this.product_id, amount: res.data.data.price }).then((ress) => {
                        this.ipDiscountkDisPrice = Number(ress.data.data.discount)
                    }).catch(() => {
                        this.ipDiscountkDisPrice = 0
                    })
                }
                // 开启了优惠码插件
                if (this.isShowPromo) {
                    // 更新优惠码
                    await applyPromoCode({ // 开启了优惠券
                        scene: 'upgrade',
                        product_id: this.product_id,
                        amount: res.data.data.price,
                        billing_cycle_time: this.hostData.billing_cycle_time,
                        promo_code: '',
                        host_id: this.id
                    }).then((resss) => {
                        this.ipCodePrice = Number(resss.data.data.discount)
                    }).catch((err) => {
                        this.$message.error(err.data.msg)
                        this.ipCodePrice = 0
                    })
                }
                this.ipMoney = (res.data.data.price * 1000 - this.ipDiscountkDisPrice * 1000 - this.ipCodePrice * 1000) / 1000
                this.ipPriceLoading = false
            })
        },
        // 提交创建磁盘
        toCreateDisk() {
            // 新增磁盘容量数组
            let newSize = []
            this.moreDiskData.map(item => {
                newSize.push({
                    size: item.size,
                    type: item.type === '默认' ? '' : item.type
                })
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
                if (res.data.status === 200) {
                    const orderId = res.data.data.id
                    this.diskOrderId = orderId
                    const amount = this.moreDiskPrice
                    this.isShowDg = false
                    this.$refs.topPayDialog.showPayDialog(orderId, amount)
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },
        // 变化监听
        sliderChange(val, item) {
            const arr = []
            item.selectList.forEach((i) => {
                arr.push([i.min_value, i.max_value])
            })
            item.size = this.mapToRange(val, arr, item.min_value)
        },
        changeDataNum(val, item) { // 数据盘数量改变计算价格
            item.size = this.mapToRange(val, item.selectList[0][item.type].config, item.selectList[0][item.type].config[0],)
        },
        // 磁盘挂载
        handelMount(id) {
            this.$confirm('确认挂载磁盘？').then(() => {
                mount({ id: this.id, disk_id: id }).then((res) => {
                    this.$message.success(res.data.msg)
                    this.doGetDiskList()
                }).catch((err) => {
                    this.$message.error(err.data.msg)
                })
            }).catch(_ => { });
        },
        goSecurityPage() {
            location.href = '/security_group.htm'
        },
        getSafeList() {
            securityGroup({ page: 1, limit: 9999 }).then((res) => {
                this.safeOptions = res.data.data.list
            })
        },
        handelSafeOpen() {
            this.safeDialogShow = true
        },
        subAddSafe() {
            if (this.safeID === '') {
                this.$message.error('请选择安全组！')
                return
            }
            addSafe({ id: this.safeID, host_id: this.id }).then((res) => {
                this.$message.success(res.data.msg)
                this.safeDialogShow = false
                this.getCloudDetail()
            }).catch((err) => {
                this.$message.error(err.data.msg)
            })
        },
        // 磁盘卸载
        handelUnload(id) {
            this.$confirm('确认卸载磁盘？').then(() => {
                unmount({ id: this.id, disk_id: id }).then((res) => {
                    this.$message.success(res.data.msg)
                    this.doGetDiskList()
                }).catch((err) => {
                    this.$message.error(err.data.msg)
                })
            }).catch(_ => { });
        },
        mapToRange(value, rangeArray, deflute) {
            for (let i = 0; i < rangeArray.length; i++) {
                const range = rangeArray[i];
                if (value >= range[0] && value <= range[1]) {
                    return value;
                }
                if (value < range[0] && i === 0) {
                    return range[0];
                }
                if (value > range[1] && i === rangeArray.length - 1) {
                    return range[1];
                }
                if (value > range[1] && value < rangeArray[i + 1][0]) {
                    return range[1];
                }
                if (value < range[0] && value > rangeArray[i - 1][1]) {
                    return range[0];
                }
            }
            return deflute; // 如果没有找到最近的区间，则返回默认最小值
        },
        // 计算订购磁盘页的价格
        getOrderDiskPrice() {
            if (this.orderTimer) {
                clearTimeout(this.orderTimer)
            }
            this.orderTimer = setTimeout(() => {
                this.diskPriceLoading = true
                // 新增磁盘容量数组
                let newSize = []
                this.moreDiskData.map(item => {
                    newSize.push({
                        size: item.size,
                        type: item.type === '默认' ? '' : item.type
                    })
                })
                this.orderDiskData.add_disk = newSize

                // 获取磁盘价格
                const params = {
                    id: this.id,
                    remove_disk_id: this.orderDiskData.remove_disk_id,
                    add_disk: this.orderDiskData.add_disk
                }
                diskPrice(params).then(async (res) => {
                    const price = Number(res.data.data.price)
                    this.moreDiskPrice = price
                    if (this.isShowLevel) {
                        await clientLevelAmount({ id: this.product_id, amount: res.data.data.price }).then((ress) => {
                            this.moreDiscountkDisPrice = Number(ress.data.data.discount)
                        }).catch(() => {
                            this.moreDiscountkDisPrice = 0
                        })
                    }
                    // 开启了优惠码插件
                    if (this.isShowPromo) {
                        // 更新优惠码
                        await applyPromoCode({ // 开启了优惠券
                            scene: 'upgrade',
                            product_id: this.product_id,
                            amount: price,
                            billing_cycle_time: this.hostData.billing_cycle_time,
                            promo_code: '',
                            host_id: this.id
                        }).then((resss) => {
                            this.moreCodePrice = Number(resss.data.data.discount)
                        }).catch((err) => {
                            this.$message.error(err.data.msg)
                            this.moreCodePrice = 0
                        })
                    }
                    this.moreDiskPrice = (price * 1000 - this.moreDiscountkDisPrice * 1000 - this.moreCodePrice * 1000) / 1000 > 0 ? (price * 1000 - this.moreDiscountkDisPrice * 1000 - this.moreCodePrice * 1000) / 1000 : 0
                    this.diskPriceLoading = false
                }).catch(error => {
                    this.diskPriceLoading = false
                })
            }, 500)
        },
        // 计算扩容磁盘页的价格
        getExpanDiskPrice() {
            if (this.orderTimer) {
                clearTimeout(this.orderTimer)
            }
            this.orderTimer = setTimeout(() => {
                this.diskPriceLoading = true
                // 新增磁盘容量数组
                let newSize = []
                this.oldDiskList.forEach(item => {
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
                expanPrice(params).then(async (res) => {
                    const price = res.data.data.price
                    this.expansionDiskPrice = price
                    if (this.isShowLevel) {
                        await clientLevelAmount({ id: this.product_id, amount: res.data.data.price }).then((ress) => {
                            this.expansionDiscount = Number(ress.data.data.discount)
                        }).catch(() => {
                            this.expansionDiscount = 0
                        })
                    }
                    // 开启了优惠码插件
                    if (this.isShowPromo) {
                        // 更新优惠码
                        await applyPromoCode({ // 开启了优惠券
                            scene: 'upgrade',
                            product_id: this.product_id,
                            amount: price,
                            billing_cycle_time: this.hostData.billing_cycle_time,
                            promo_code: '',
                            host_id: this.id
                        }).then((resss) => {
                            this.expansionCodePrice = Number(resss.data.data.discount)
                        }).catch((err) => {
                            this.$message.error(err.data.msg)
                            this.expansionCodePrice = 0
                        })
                    }
                    this.expansionDiskPrice = (price * 1000 - this.moreDiscountkDisPrice * 1000 - this.expansionCodePrice * 1000) / 1000 > 0 ? (price * 1000 - this.moreDiscountkDisPrice * 1000 - this.expansionCodePrice * 1000) / 1000 : 0
                    this.diskPriceLoading = false
                }).catch(err => {
                    this.expansionDiskPrice = 0.00
                    this.diskPriceLoading = false
                })
            }, 500)
        },
        // 打开新增Ip弹窗
        showIpDia() {
            getLineConfig({ id: this.product_id, line_id: this.cloudData.line.id }).then((res) => {
                if (res.data.data.ip && res.data.data.ip.length > 0) {
                    this.ipValueData = res.data.data.ip.filter((item) => {
                        return item.value !== this.netDataList.length - 1
                    })
                    this.ipValue = this.ipValueData[0].value
                    this.getIpPrice()
                    this.isShowIp = true
                } else {
                    this.$message.error('暂不支持新增附加IP')
                }
            })
        },
        // 获取vpc网络列表
        getVpcNetwork() {
            this.vpcLoading = true
            vpcNetwork({ id: this.id, ...this.vpcParams }).then((res) => {
                this.vpcDataList = res.data.data.list
                this.vpcParams.total = res.data.data.count
                this.vpcLoading = false
            }).catch((err) => {
                this.vpcLoading = false
                this.$message.error(err.msg.data)
            })
        },
        handDelVpc(id) {
            this.$confirm('确认删除该VPC？').then(() => {
                delVpc({ id: this.id, vpc_network_id: id }).then((res) => {
                    this.$message.success(res.data.msg)
                    this.getVpcNetwork()
                }).catch((err) => {
                    this.$message.error(err.data.msg)
                })
            }).catch(_ => { });
        },
        handelAddVpc() {
            this.vpcName = 'VPC-' + this.generateRandomString(8)
            this.isShowAddVpc = true
        },
        // 随机生成字符串
        generateRandomString(length) {
            let result = '';
            const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            const charactersLength = characters.length;
            for (let i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        },
        subAddVpc() {
            addVpcNet({ id: this.id, name: this.vpcName, ips: this.plan_way === 1 ? this.ips : '' }).then((res) => {
                this.$message.success(res.data.msg)
                this.isShowAddVpc = false
                this.getVpcNetwork()
            }).catch((err) => {
                this.$message.error(err.data.msg)
            })
        },
        // 关闭扩容弹窗
        krClose() {
            this.isShowExpansion = false
        },
        // 关闭新增IP弹窗
        ipClose() {
            this.isShowIp = false
            this.ipValue = null
        },
        handelEngine(row) {
            this.curEngineId = row.id
            this.isShowengine = true
            this.remoteMethod('')
        },
        engineClose() {
            this.isShowengine = false
        },
        safeClose() {
            this.safeDialogShow = false
        },
        addVpcClose() {
            this.plan_way = 0
            this.isShowAddVpc = false
        },
        subAddEngine() {
            if (this.isSubmitEngine) {
                return
            }
            this.isSubmitEngine = true
            changeVpc({ id: this.engineID, vpc_network_id: this.curEngineId }).then((res) => {
                this.$message.success(res.data.msg)
                this.isShowengine = false
                this.isSubmitEngine = false
                this.getVpcNetwork()
            }).catch((err) => {
                this.isSubmitEngine = false
                this.$message.error(err.data.msg)
            })
        },
        remoteMethod(query) {
            this.engineID = ''
            this.engineSearchLoading = true;
            if (query !== '') {
                this.productParams.keywords = query
            } else {
                this.productParams.keywords = ''
            }
            cloudList(this.productParams).then((res => {
                this.productOptions = res.data.data.list
                this.engineSearchLoading = false;
            }))
        },
        // 提交新增IP
        subAddIp() {
            ipOrder({ id: this.id, ip_num: this.ipValue }).then((res) => {
                const orderId = res.data.data.id
                this.isShowIp = false
                this.$refs.topPayDialog.showPayDialog(orderId)
            }).catch((err) => {
                this.$message.error(err.data.msg)
            })
        },
        // 提交扩容
        subExpansion() {
            let newSize = []
            this.oldDiskList.forEach(item => {
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
                this.diskOrderId = res.data.data.id
                const amount = this.expansionDiskPrice
                this.isShowExpansion = false
                this.$refs.topPayDialog.showPayDialog(this.diskOrderId, amount)
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },
        // 网络开始
        // 获取ip列表
        getIpList() {
            const params = {
                id: this.id,
                ...this.netParams
            }
            this.netLoading = true
            ipList(params).then(res => {
                if (res.data.status === 200) {
                    this.netParams.total = res.data.data.count
                    this.netDataList = res.data.data.list
                }
                this.netLoading = false
            })
        },
        netSizeChange(e) {
            this.netParams.limit = e
            this.netParams.page = 1
            // 获取列表
            this.getIpList()
        },
        netCurrentChange(e) {
            this.netParams.page = e
            this.getIpList()
        },
        vpcSizeChange(e) {
            this.vpcParams.limit = e
            this.vpcParams.page = 1
            // 获取列表
            this.getVpcNetwork()
        },
        vpcCurrentChange(e) {
            this.vpcParams.page = e
            this.getVpcNetwork()
        },
        // 获取网络流量
        doGetFlow() {
            const params = {
                id: this.id
            }
            getFlow(params).then(res => {
                if (res.data.status === 200) {
                    this.flowData = res.data.data
                }
            })
        },
        // 日志开始
        logSizeChange(e) {
            this.logParams.limit = e
            this.logParams.page = 1
            // 获取列表
            this.getLogList()
        },
        logCurrentChange(e) {
            this.logParams.page = e
            this.getLogList()
        },
        getLogList() {
            this.logLoading = true
            const params = {
                ...this.logParams,
                id: this.id
            }
            getLog(params).then(res => {
                if (res.data.status === 200) {
                    this.logParams.total = res.data.data.count
                    this.logDataList = res.data.data.list
                }
                this.logLoading = false
            }).catch(error => {
                this.logLoading = false
            })
        },
        // 备份与快照 开始
        // 备份列表
        getBackupList() {
            this.backLoading = true
            const params = {
                id: this.id,
                ...this.params1
            }
            backupList(params).then(res => {

                if (res.data.status === 200) {
                    this.dataList1 = res.data.data.list
                    this.params1.total = res.data.data.count
                }
                this.backLoading = false
            }).catch(err => {
                this.backLoading = true
            })
        },
        // 快照列表
        getSnapshotList() {
            this.snapLoading = true
            const params = {
                id: this.id,
                ...this.params2
            }
            snapshotList(params).then(res => {
                if (res.data.status === 200) {
                    this.dataList2 = res.data.data.list
                    this.params2.total = res.data.data.count
                }
                this.snapLoading = false
            }).catch(err => {
                this.snapLoading = false
            })
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
        // 创建备份/生成快照弹窗 关闭
        bsCgClose() {
            this.isShwoCreateBs = false
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
        // 还原快照、备份 弹窗显示
        showhyBs(type, item) {
            if (type == 'back') {
                this.isBs = true
            } else {
                this.isBs = false
            }
            this.restoreData.restoreId = item.id
            this.restoreData.time = item.create_time
            this.restoreData.cloud_name = this.hostData.name
            this.isShowhyBs = true
        },
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
            this.delData.cloud_name = this.hostData.name
            this.isShowDelBs = true
        },
        // 开启备份/快照 弹窗
        openBs(type) {
            if (type == 'back') {
                this.isBs = true
            } else {
                this.isBs = false
            }
            this.bsData.backNum = this.backup_config[0] ? this.backup_config[0].num : ''
            this.bsData.snapNum = this.snap_config[0] ? this.snap_config[0].num : ''
            this.isShowOpenBs = true
            this.getBsPrice()
        },
        // 关闭 开启备份/快照弹窗
        bsopenDgClose() {
            this.isShowOpenBs = false
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
                    this.bsOrderId = orderId
                    const amount = this.bsData.money
                    this.isShowOpenBs = false
                    this.$refs.topPayDialog.showPayDialog(orderId, amount)
                }
            }).catch(err => {
                this.$message.error(err.data.msg)
            })
        },
        bsSelectChange() {
            this.getBsPrice()
        },
        // 获取开启备份/快照的价格
        getBsPrice() {
            this.bsDataLoading = true
            const params = {
                id: this.id,
                type: this.isBs ? 'backup' : 'snap',
                num: this.isBs ? this.bsData.backNum : this.bsData.snapNum
            }
            backupConfig(params).then(async (res) => {
                if (res.data.status === 200) {
                    const price = Number(res.data.data.price)
                    this.bsData.money = price
                    if (this.isShowLevel) {
                        clientLevelAmount({ id: this.product_id, amount: res.data.data.price }).then((ress) => {
                            this.bsData.moneyDiscount = Number(ress.data.data.discount)
                        }).catch(() => {
                            this.bsData.moneyDiscount = 0
                        })
                    }
                    // 开启了优惠码插件
                    if (this.isShowPromo) {
                        // 更新优惠码
                        await applyPromoCode({ // 开启了优惠券
                            scene: 'upgrade',
                            product_id: this.product_id,
                            amount: price,
                            billing_cycle_time: this.hostData.billing_cycle_time,
                            promo_code: '',
                            host_id: this.id
                        }).then((resss) => {
                            this.bsData.codePrice = Number(resss.data.data.discount)
                        }).catch((err) => {
                            this.$message.error(err.data.msg)
                            this.bsData.codePrice = 0
                        })
                    }
                    this.bsData.money = (price * 1000 - this.bsData.moneyDiscount * 1000 - this.bsData.codePrice * 1000) / 1000 > 0 ? (price * 1000 - this.bsData.moneyDiscount * 1000 - this.bsData.codePrice * 1000) / 1000 : 0
                    this.bsDataLoading = false
                }
            }).catch(error => {
                this.bsDataLoading = false

            })
        },
        // 统计图表开始
        // 获取cpu用量数据
        getCpuList() {
            this.echartLoading1 = true
            const params = {
                id: this.id,
                start_time: this.startTime,
                type: 'cpu'
            }
            chartList(params).then(res => {
                if (res.data.status === 200) {
                    const list = res.data.data.list
                    let x = []
                    let y = []
                    list.forEach(item => {
                        x.push(formateDate(item.time * 1000))
                        y.push((item.value).toFixed(2))
                    });

                    const cpuOption = {
                        title: {
                            text: 'CPU占用量',
                        },
                        tooltip: {
                            show: true,
                            trigger: "axis",
                        },
                        grid: {
                            left: '5%',
                            right: '4%',
                            bottom: '5%',
                            containLabel: true
                        },
                        xAxis: {
                            type: "category",
                            boundaryGap: false,
                            data: x,
                        },
                        yAxis: {
                            type: "value",
                        },
                        series: [
                            {
                                name: "占用量(%)",
                                data: y,
                                type: "line",
                                areaStyle: {},
                            },
                        ],
                    }

                    var CpuChart = echarts.init(document.getElementById('cpu-echart'));
                    CpuChart.setOption(cpuOption);
                }
                this.echartLoading1 = false
            }).catch(err => {
                this.echartLoading1 = false
            })
        },
        // 获取网络宽度
        getBwList() {
            this.echartLoading2 = true
            const params = {
                id: this.id,
                start_time: this.startTime,
                type: 'bw'
            }
            chartList(params).then(res => {
                if (res.data.status === 200) {
                    const list = res.data.data.list

                    let xAxis = []
                    let yAxis = []
                    let yAxis2 = []

                    list.forEach(item => {
                        xAxis.push(formateDate(item.time * 1000))
                        yAxis.push(item.in_bw.toFixed(2))
                        yAxis2.push(item.out_bw.toFixed(2));
                    });


                    const options = {
                        title: {
                            text: '网络宽带',
                        },
                        tooltip: {
                            show: true,
                            trigger: "axis",
                        },
                        grid: {
                            left: '5%',
                            right: '4%',
                            bottom: '5%',
                            containLabel: true
                        },
                        xAxis: {
                            type: "category",
                            boundaryGap: false,
                            data: xAxis,
                        },
                        yAxis: {
                            type: "value",
                        },
                        series: [
                            {
                                name: "进带宽(bps)",
                                data: yAxis,
                                type: "line",
                                areaStyle: {},
                            },
                            {
                                name: "出带宽(bps)",
                                data: yAxis2,
                                type: "line",
                                areaStyle: {},
                            },
                        ],
                    }



                    var bwChart = echarts.init(document.getElementById('bw-echart'));
                    var bw2Chart = echarts.init(document.getElementById('bw2-echart'));
                    bwChart.setOption(options);
                    bw2Chart.setOption(options);
                }
                this.echartLoading2 = false
            }).catch(err => {
                this.echartLoading2 = false
            })
        },
        // 获取磁盘IO
        getDiskLIoList() {
            this.echartLoading3 = true
            const params = {
                id: this.id,
                start_time: this.startTime,
                type: 'disk_io'
            }

            chartList(params).then(res => {
                if (res.data.status === 200) {
                    const list = res.data.data.list

                    let xAxis = []
                    let yAxis = []
                    let yAxis2 = []
                    let yAxis3 = []
                    let yAxis4 = []

                    list.forEach(item => {
                        xAxis.push(formateDate(item.time * 1000))
                        yAxis.push((item.read_bytes / 1024 / 1024).toFixed(2));
                        yAxis2.push(item.read_iops.toFixed(2));
                        yAxis3.push((item.write_bytes / 1024 / 1024).toFixed(2));
                        yAxis4.push(item.write_iops.toFixed(2));
                    });

                    const options = {
                        title: {
                            text: '磁盘IO',
                        },
                        tooltip: {
                            show: true,
                            trigger: "axis",
                        },
                        grid: {
                            left: '5%',
                            right: '4%',
                            bottom: '5%',
                            containLabel: true
                        },
                        xAxis: {
                            type: "category",
                            boundaryGap: false,
                            data: xAxis,
                        },
                        yAxis: {
                            // name: "单位（B/s）",
                            type: "value",
                        },
                        series: [
                            {
                                name: "读取速度(MB/s)",
                                data: yAxis,
                                type: "line",
                                areaStyle: {},
                            },
                            {
                                name: "读取IOPS",
                                data: yAxis2,
                                type: "line",
                                areaStyle: {},
                            },
                            {
                                name: "写入速度(MB/s)",
                                data: yAxis3,
                                type: "line",
                                areaStyle: {},
                            },
                            {
                                name: "写入IOPS",
                                data: yAxis4,
                                type: "line",
                                areaStyle: {},
                            },
                        ],
                    }



                    var diskIoChart = echarts.init(document.getElementById('disk-io-echart'));
                    diskIoChart.setOption(options);
                }
                this.echartLoading3 = false
            }).catch(err => {
                this.echartLoading3 = false
            })
        },
        // 获取内存用量
        getMemoryList() {
            this.echartLoading4 = true
            const params = {
                id: this.id,
                start_time: this.startTime,
                type: 'memory'
            }
            chartList(params).then(res => {
                if (res.data.status === 200) {
                    const list = res.data.data.list

                    let xAxis = []
                    let yAxis = []
                    let yAxis2 = []

                    list.forEach(item => {
                        xAxis.push(formateDate(item.time * 1000))
                        yAxis.push((item.total / 1024 / 1024 / 1024).toFixed(2));
                        yAxis2.push((item.used / 1024 / 1024 / 1024).toFixed(2));
                    });
                    const options = {
                        title: {
                            text: '内存用量',
                        },
                        tooltip: {
                            show: true,
                            trigger: "axis",
                        },
                        grid: {
                            left: '5%',
                            right: '4%',
                            bottom: '5%',
                            containLabel: true
                        },
                        xAxis: {
                            type: "category",
                            boundaryGap: false,
                            data: xAxis,
                        },
                        yAxis: {
                            type: "value",
                        },
                        series: [
                            {
                                name: "总内存(GB)",
                                data: yAxis,
                                type: "line",
                                areaStyle: {},
                            },
                            {
                                name: "内存使用量(GB)",
                                data: yAxis2,
                                type: "line",
                                areaStyle: {},
                            },
                        ],
                    }



                    var memoryChart = echarts.init(document.getElementById('memory-echart'));
                    memoryChart.setOption(options);
                }
                this.echartLoading4 = false
            }).catch(err => {
                this.echartLoading4 = false
            })
        },
        getstarttime(type) {
            // 1: 过去24小时 2：过去三天 3：过去七天
            let nowtime = parseInt(new Date().getTime() / 1000);
            if (type == 1) {
                this.startTime = nowtime - 24 * 60 * 60;
            } else if (type == 2) {
                this.startTime = nowtime - 24 * 60 * 60 * 3;
            } else if (type == 3) {
                this.startTime = nowtime - 24 * 60 * 60 * 7;
            }
        },
        // 时间选择框
        chartSelectChange(e) {
            // 计算开始时间
            this.getstarttime(e)

            // 重新拉取图表数据
            this.getCpuList()
            this.getBwList()
            this.getDiskLIoList()
            this.getMemoryList()
        },
        powerDgClose() {
            this.isShowPowerChange = false
        },
        // 显示电源操作确认弹窗
        showPowerDialog() {
            const type = this.powerStatus
            if (type == 'on') {
                this.powerTitle = "开启"
            }
            if (type == 'off') {
                this.powerTitle = "关闭"
            }
            if (type == 'rebot') {
                this.powerTitle = "重启"
            }
            if (type == 'hardOff') {
                this.powerTitle = "强制关机"
            }
            if (type == 'hardRebot') {
                this.powerTitle = "强制重启"
            }
            this.powerType = type
            this.isShowPowerChange = true
        },
    },

}).$mount(template)

