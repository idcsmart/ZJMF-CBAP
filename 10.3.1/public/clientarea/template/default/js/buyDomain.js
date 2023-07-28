(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('template')[0]
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                asideMenu,
                topMenu,
                payDialog,
            },
            created() {
                this.checkList = JSON.parse(sessionStorage.getItem('buyDomainPosition')) || []
                this.getCommonData()
                this.getCarList()
                this.getCountry()
                this.getDomainSet()
            },
            mounted() {
            },
            updated() {
                // 关闭loading
                document.getElementById('mainLoading').style.display = 'none';
                document.getElementsByClassName('template')[0].style.display = 'block'
            },
            destroyed() {

            },
            data() {
                return {
                    commonData: {},
                    carList: [],
                    suffixList: [],
                    checkList: [],
                    isCarLoading: false,
                    temLoding: false,
                    autoRenew: false,
                    autoUpload: false,
                    isAgree: false,
                    templateArr: [],
                    templateParams: {
                        keywords: '',
                        type: ''
                    },
                    templateId: '',
                    isShowTemp: false,
                    infoDetails: {},
                    curId: '',
                    rePhoneData: {
                        countryCode: 86,
                        phone: ''
                    },
                    countryList: [],
                    order_id: '',
                    subLoading: false,
                    domainConfig: {}
                }
            },
            filters: {
                formateTime(time) {
                    if (time && time !== 0) {
                        return formateDate(time * 1000)
                    } else {
                        return "--"
                    }
                }
            },
            computed: {
                // 计算购物车选中的商品的总价
                totalMoneyCalc() {
                    let total = 0
                    this.carList.forEach((item) => {
                        total += this.priceCalc(item) * 1000
                    })
                    return (total / 1000).toFixed(2)
                },
                calcCountry() {
                    return name => {
                        return this.countryList.filter(item => item.iso === name)[0]?.name_zh
                    }
                }
            },
            methods: {
                getCountry() {
                    getCountry().then((res) => {
                        if (res.data.status === 200) {
                            this.countryList = res.data.data.list
                            this.rePhoneData.countryCode = 86
                        }
                    })
                },
                // 获取域名设置
                getDomainSet() {
                    domainSetting().then((res) => {
                        this.domainConfig = res.data.data
                    })
                },
                // 支付成功回调
                paySuccess(e) {
                    sessionStorage.removeItem('buyDomainPosition')
                    location.href = `/orderDetail.htm?id=${this.order_id}`
                },
                // 取消支付回调
                payCancel(e) {
                    sessionStorage.removeItem('buyDomainPosition')
                    location.href = `/orderDetail.htm?id=${this.order_id}`
                },
                lookItem(row) { // 查看详情
                    this.curId = row.id
                    this.isShowTemp = true
                    this.getInfoDetails()
                },
                openUrl(url) {
                    window.open(url)
                },
                // 提交订单
                submitOrder() {
                    if (this.templateId === '') {
                        this.$message.warning(lang.template_text89)
                        return
                    }
                    if (!this.isAgree) {
                        this.$message.warning(lang.template_text90)
                        return
                    }
                    this.subLoading = true
                    const params = {
                        positions: this.checkList,
                        customfield: {
                            auto_renew: this.autoRenew ? 1 : 0,
                            lock_status: this.autoUpload ? 1 : 0,
                            c_sysid: this.templateId
                        }
                    }
                    cartCheckout(params).then((res) => {
                        if (res.data.status === 200) {
                            this.order_id = res.data.data.order_id
                            this.$refs.payDialog.showPayDialog(res.data.data.order_id)
                            this.subLoading = false
                        }
                    }).catch((err) => {
                        this.subLoading = false
                        this.$message.error(err.data.msg)
                    })
                },
                async getInfoDetails() {
                    try {
                        const res = await templateDetails(this.curId)
                        this.infoDetails = res.data.data.info_template
                    } catch (error) {
                        this.$message.error(error.data.msg)
                    }
                },
                // 获取模板列表
                getTemplateList() {
                    this.temLoding = true
                    const params = {
                        domain: this.carList.map((item) => { return item.config_options.domain }),
                        ...this.templateParams
                    }
                    templateSupport(params).then((res) => {
                        this.templateArr = res.data.data.list
                        this.templateId = this.templateArr[0]?.id || ''
                    }).finally(() => {
                        this.temLoding = false
                    })
                },

                goBack() {
                    window.history.go(-1)
                },
                goCreatTem() {
                    location.href = '/creatTemplate.htm'
                },
                // 价格计算
                priceCalc(item) {
                    if (item.priceArr.length === 0) {
                        return 0
                    }
                    const price = item.priceArr.find((priceItem) => {
                        return priceItem.buyyear === item.selectYear
                    })
                    return price.buyprice
                },
                // 购物车列表
                getCarList() {
                    this.isCarLoading = true
                    cartList().then((res) => {
                        const arr = res.data.data.list.map((item, index) => {
                            return {
                                ...item,
                                positions: index,
                                selectYear: item.config_options.year,
                                showPrice: 0,
                                priceArr: [],
                                priceLoading: true
                            }
                        }).filter((item) => {
                            // 过滤掉非域名商品和指定位置的商品
                            return item.customfield.is_domain === 1 && this.checkList.includes(item.positions)
                        })
                        if (arr.length === 0) {
                            location.href = '/goodsList.htm'
                        }
                        // 拉取价格
                        this.carList = arr
                        this.getTemplateList()
                        this.isCarLoading = false
                        arr.forEach((item) => {
                            domainPrice({ name: item.config_options.domain }).then((res) => {
                                item.priceArr = res.data.data || []
                                item.showPrice = res.data.data[0].buyprice || 0
                            }).catch((err) => {

                            }).finally(() => {
                                item.priceLoading = false
                            })
                        })
                    })
                },
                // 去登录
                goLogin() {
                    location.href = '/login.htm'
                },
                // 修改购物车
                changeCart(val, item) {
                    const params = {
                        position: item.positions,
                        product_id: item.product_id,
                        qty: 1,
                        config_options: {
                            domain: item.config_options.domain,
                            year: val
                        },
                        customfield: {
                            is_domain: 1  // 是否域名商品
                        }
                    }
                    updateCart(params).then((res) => {
                        if (res.data.status === 200) {
                            this.getCarList()
                        }
                    }).catch((err) => {
                        this.$message.error(err.data.msg)
                    })

                },

                // 删除购物车
                async deleteCart(item) {
                    this.isCarLoading = true
                    const params = {
                        position: item.positions
                    }
                    const res = await deleteCart(params)
                    if (res.data.status === 200) {
                        this.isCarLoading = false
                        // 删除 checkList 中的数据
                        const index = this.checkList.findIndex((checkItem) => {
                            return checkItem === item.positions
                        })
                        this.checkList.splice(index, 1)
                        sessionStorage.setItem('buyDomainPosition', JSON.stringify(this.checkList))
                        this.getCarList()
                    }
                },

                // 获取通用配置
                getCommonData() {
                    this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
                    document.title = this.commonData.website_name + '-' + lang.template_text91
                },

            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
