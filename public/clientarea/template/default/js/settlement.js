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
                const temp = this.getQuery(location.search)
                if (temp.arr) { // 购物车过来
                    this.selectGoodsList = temp.arr.split(',')
                    this.isFormShoppingCart = true
                }
                if (!temp.arr && sessionStorage.product_information) { // 配置页直接结算
                    const obj = JSON.parse(sessionStorage.product_information)
                    this.isFormShoppingCart = false
                    this.productObj = {
                        product_id: temp.id ? temp.id : obj.id ? obj.id : '',
                        config_options: obj.config_options,
                        qty: Number(obj.qty),
                        customfield: {}
                    }
                    productDetail(this.productObj.product_id).then((res) => {
                        this.productObj.name = res.data.data.product.name
                        this.showGoodsList.push(this.productObj)
                    })
                }
                this.getCartList()
                this.getCommonData()
                this.getPayLisy()
            },
            mounted() {
                // 关闭loading
                // document.getElementById('mainLoading').style.display = 'none';
                // document.getElementsByClassName('template')[0].style.display = 'block'
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
                    commonData: {}, // 公告接口数据
                    selectGoodsList: [], // 要结算的商品
                    productObj: {}, // 单独结算的商品对象
                    shoppingList: [], // 所有购物车列表
                    listLoading: false,
                    isFormShoppingCart: true, // 是否从购物车页面结算
                    showGoodsList: [], // 展示的列表
                    payTypeList: [], // 支付渠道数组
                    payType: '', // 选择的支付渠道
                    checked: false, // 勾选隐私协议
                    subBtnLoading: false // 提交按钮loading
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
                totalPrice() {
                    return this.showGoodsList.reduce((pre, cur) => {
                        return pre + (cur.unitPrice * cur.qty)
                    }, 0)
                },
            },
            methods: {
                // 获取购物车列表
                getCartList() {
                    this.listLoading = true
                    cartList().then((res) => {
                        const arr = []
                        this.shoppingList = res.data.data.list
                        if (this.isFormShoppingCart) {
                            this.selectGoodsList.forEach((item) => {
                                arr.push(this.shoppingList[item])
                            })
                            this.showGoodsList = [...arr]
                        } else {
                            arr.push(this.productObj)
                        }
                        this.listLoading = false
                        arr.forEach((item) => {
                            item.isLoading = true
                            configOption(item.product_id, item.config_options).then((ress) => {
                                item.info = ress.data.data
                                item.preview = ress.data.data.preview
                                item.price = Number(ress.data.data.price)
                                clientLevelAmount({ id: item.product_id, amount: item.price }).then(resss => {
                                    if (resss.data.status === 200) {
                                        item.isLoading = false
                                        item.unitPrice = (Number(item.price) * 1000 - Number(resss.data.data.discount) * 1000) / 1000 // 实际单价 = 原单价 - 优惠价格
                                        this.showGoodsList = [...arr]
                                    }
                                }).catch(error => {
                                    item.isLoading = false
                                    item.unitPrice = Number(item.price)
                                    this.showGoodsList = [...arr]
                                })
                            }).catch(() => {
                                item.isLoading = false
                                item.preview = []
                                this.showGoodsList = [...arr]
                            })
                        })

                    })
                },
                goPay() {
                    if (!this.checked) {
                        this.$message.warning("请先勾选协议后再提交订单")
                        return
                    }
                    this.subBtnLoading = true
                    if (this.isFormShoppingCart) {
                        cart_settle({ positions: this.selectGoodsList, customfield: {} }).then((res) => {
                            this.$refs.payDialog.showPayDialog(res.data.data.order_id, res.data.data.amount, this.payType)

                        }).catch((err) => {
                            this.$message.error(err.data.msg)
                        }).finally(() => {
                            this.subBtnLoading = false
                        })
                    } else {
                        product_settle({ product_id: this.productObj.product_id, config_options: this.productObj.config_options, customfield: this.productObj.customfield, qty: this.productObj.qty }).then((res) => {
                            this.$refs.payDialog.showPayDialog(res.data.data.order_id, 0, this.payType)
                        }).catch((err) => {
                            this.$message.error(err.data.msg)
                        }).finally(() => {
                            this.subBtnLoading = false
                        })
                    }

                },
                // 支付成功回调
                paySuccess(e) {
                    location.href = './finance.html'
                },
                // 取消支付回调
                payCancel(e) {
                    location.href = './finance.html'
                },
                getPayLisy() {
                    payLisy().then((res) => {
                        this.payTypeList = res.data.data.list
                        this.payType = res.data.data.list[0].name
                    })
                },
                // 解析url
                getQuery(url) {
                    const str = url.substr(url.indexOf('?') + 1)
                    const arr = str.split('&')
                    const res = {}
                    for (let i = 0; i < arr.length; i++) {
                        const item = arr[i].split('=')
                        res[item[0]] = item[1]
                    }
                    return res
                },
                goTermsServiceUrl() {
                    window.open(this.commonData.terms_service_url)
                },
                goTermsPrivacUrl() {
                    window.open(this.commonData.terms_privacy_url)
                },
                // 获取通用配置
                getCommonData() {
                    getCommon().then(res => {
                        if (res.data.status === 200) {
                            this.commonData = res.data.data
                            localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                            document.title = this.commonData.website_name + '-商品结算'
                        }
                    })
                },
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
