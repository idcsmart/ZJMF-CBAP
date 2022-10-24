(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('template')[0]
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                asideMenu,
                topMenu,
            },
            created() {
                this.getCommonData()
                this.getCartList()
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
                    timer1: null,
                    listLoding: false,
                    commonData: {},
                    searchVal: '',
                    checkedCities: [],
                    checkAll: false,
                    visible: false,
                    showList: [],
                    shoppingList: []
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
                    return this.checkedCities.reduce((pre, cur) => {
                        return pre + (cur.unitPrice * cur.qty)
                    }, 0)
                },

            },
            methods: {
                // 获取购物车列表
                getCartList() {
                    this.listLoding = true
                    cartList().then((res) => {
                        this.shoppingList = res.data.data.list
                        this.shoppingList.forEach((item) => {
                            item.isLoading = true
                        })
                        this.listLoding = false
                        this.showList = [...this.shoppingList]
                        for (let index = 0; index < this.shoppingList.length; index++) {
                            const item = this.shoppingList[index]
                            item.position = index
                            if (item.stock_control === 1 && item.qty > item.stock_qty) {
                                item.isShowTips = true
                                item.qty = item.stock_qty
                            } else {
                                item.isShowTips = false
                            }
                            configOption(item.product_id, item.config_options).then((ress) => {
                                item.info = ress.data.data
                                item.preview = ress.data.data.preview
                                item.price = Number(ress.data.data.price)
                                clientLevelAmount({ id: item.product_id, amount: item.price }).then(resss => {
                                    if (resss.data.status === 200) {
                                        item.unitPrice = (Number(item.price) * 1000 - Number(resss.data.data.discount) * 1000) / 1000 // 实际单价 = 原单价 - 优惠价格
                                        item.isLoading = false
                                        this.showList = [...this.shoppingList]
                                        if (this.shoppingList.length === 1) {
                                            this.checkedCities = [...this.shoppingList]
                                            this.checkAll = true
                                        }
                                    }
                                }).catch(error => {
                                    item.unitPrice = Number(item.price)
                                    item.isLoading = false
                                    this.showList = [...this.shoppingList]
                                })
                            }).catch(() => {
                                item.preview = []
                                item.invalid = true
                                item.isLoading = false
                                this.showList = [...this.shoppingList]
                            })
                        }

                    }).catch(() => {
                        this.listLoding = false
                    })
                },
                subtotal(unitprice, discount, qty) {
                    return ((unitprice * 1000 - discount * 1000) / 1000) * qty
                },
                // 搜索
                searchValChange(value) {
                    this.checkedCities = []
                    this.checkAll = false
                    if (value !== '') {
                        const arr = []
                        this.shoppingList.forEach((item, index) => {
                            if (this.shoppingList[index].name.includes(value)) {
                                arr.push(item)
                            }
                        })
                        this.showList = arr
                    } else {
                        this.showList = [...this.shoppingList]
                    }

                },
                // 点击全选按钮
                handleCheckAllChange(val) {
                    const arr = this.showList.filter((item) => {
                        return item.info
                    })
                    this.checkedCities = val ? arr : [];
                },
                // 编辑商品数量
                handelEditGoodsNum(index, num) {
                    return editGoodsNum(index, num)
                },
                // 编辑商品
                goGoods(item) {
                    if (item.info) {
                        const obj = {
                            config_options: item.config_options, // 配置信息
                            position: item.position, // 修改接口要用的位置信息
                            qty: item.qty   // 商品数量
                        }
                        sessionStorage.setItem('product_information', JSON.stringify(obj))
                    }
                    location.href = `goods.html?id=${item.product_id}`
                },
                // 监听购物车选择数量变化
                handleCheckedCitiesChange(value) {
                    this.checkAll = value.length === this.showList.length;
                },
                // 删除商品函数 
                deleteGoodsList(arr, isRefsh) {
                    deleteGoods(arr).then((res) => {
                        if (res.data.status === 200) {
                            this.$message.success(res.data.msg)
                            isRefsh && this.getCartList()
                            this.$refs.topMenu.getCartList()
                        }
                    }).catch((err) => {
                        err.data.msg && this.$message.error(err.data.msg)
                    }).finally(() => {

                    })

                },
                // 点击删除按钮
                handelDeleteGoods(item, index) {
                    // 调用删除接口
                    this.deleteGoodsList([item.position])
                    // 删除列表中对应的商品
                    this.showList.splice(index, 1)
                    this.shoppingList.splice(this.shoppingList.indexOf(item), 1)
                    this.checkedCities.splice(this.checkedCities.indexOf(item), 1)
                },
                // 删除选中的商品
                deleteCheckGoods() {
                    if (this.checkedCities.length === 0) {
                        this.$message.warning('请先选择您要删除的商品')
                        return
                    } else {
                        const arr = []
                        this.checkedCities.forEach((item) => {
                            arr.push(item.position)
                        })
                        this.deleteGoodsList(arr, true)
                        this.checkedCities = []
                    }
                },
                // 商品数量增加减少
                handleChange(n, o, item, index) {
                    if (item.stock_control === 1 && n >= item.stock_qty) {
                        this.$message.error('商品库存不足！')
                    }
                    // 节个流
                    if (this.timer1) {
                        clearTimeout(this.timer1)
                        this.timer1 = null
                    }
                    this.timer1 = setTimeout(() => {
                        this.handelEditGoodsNum(index, n).then((ress) => {
                        }).catch((err) => {
                            err.data.msg && this.$message.error(err.data.msg)
                        }).finally(() => {
                            clearTimeout(this.timer1)
                            this.timer1 = null
                            // this.getCartList()
                        })

                    }, 500)

                },
                // 结算
                goSettle() {
                    if (this.checkedCities.length === 0) {
                        this.$message.warning('请先选择您要购买的商品')
                        return
                    }
                    const arr = []
                    this.shoppingList.forEach((item, index) => {
                        this.checkedCities.forEach((items) => {
                            if (items.position == item.position) {
                                arr.push(index)
                            }
                        })
                    })
                    location.href = `./settlement.html?arr=${arr.toString()}`
                },
                // 获取通用配置
                getCommonData() {
                    getCommon().then(res => {
                        if (res.data.status === 200) {
                            this.commonData = res.data.data
                            localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                            document.title = this.commonData.website_name + '-商城'
                        }
                    })
                },
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
