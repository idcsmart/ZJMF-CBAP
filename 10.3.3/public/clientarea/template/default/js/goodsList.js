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
                window.addEventListener('scroll', this.scrollBottom)
                // 监听 页面点击事件 用于关闭 isShowSuffixBox
                document.addEventListener('click', (e) => {
                    console.log(e.target.className);
                    const classNameArr = ['suffix-list', 'suffix-box', 'suffix-item', 'suffix-item suffix-active', 'el-icon-arrow-down select-btn']
                    if (!classNameArr.includes(e.target.className)) {
                        this.isShowSuffixBox = false
                    }
                })
                this.getCommonData()
                this.initData()
                this.getCarList()
                sessionStorage.removeItem("product_information")
            },
            mounted() {
            },
            updated() {
                // 关闭loading
                document.getElementById('mainLoading').style.display = 'none';
                document.getElementsByClassName('template')[0].style.display = 'block'
            },
            destroyed() {
                window.removeEventListener('scroll', this.scrollBottom)
            },
            data() {
                return {
                    isShowView: false,
                    searchValue: '', // 搜索内容
                    searchLoading: false,
                    select_first_obj: {
                        id: '', // 一级分组ID
                        type: '' // 一级分组类型
                    }, // 选中的一级分类对象 
                    select_second_obj: {
                        id: '', // 二级分组ID
                        type: '' // 二级分组类型
                    }, // 选中的一级分类对象 
                    first_group_list: [], // 一级分类数组
                    second_group_list: [], // 二级分类数组
                    commonData: {},
                    scrollDisabled: false,
                    secondLoading: false, // 二级分类加载
                    goodSLoading: false,
                    goodsParmas: {
                        keywords: '', // 关键字,搜索范围:商品ID,商品名,描述
                        id: '', // 二级分组ID
                        page: 1, // 页数
                        limit: 12 // 每页条数
                    },
                    goodsList: [], // 商品列表数组
                    regType: '1',
                    domainInput: '',
                    selectSuffix: '',
                    isAllCheck: false,
                    suffixList: [],
                    domainList: [],
                    isSearching: false,
                    isShowSuffixBox: false,
                    carList: [],
                    checkList: [],
                    product_id: '',
                    isCarLoading: false,
                    isIndeterminate: false,
                    isBatchIndeterminate: false,
                    isBatchAllCheck: false,
                    batchLoading: false,
                    addAllLoading: false,
                    textarea2: '',
                    batchCheckGroup: [],
                    availList: [],
                    unavailList: [],
                    faillList: [],
                    activeNames: [],
                    domainConfig: {},
                    isShowUpload: false,
                    fileName: '',
                    fileContent: '',
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
                // 是否选中的分类为域名
                isDomain() {
                    return this.select_second_obj.type === 'domain'
                },
                // 计算购物车选中的商品的总价
                totalMoneyCalc() {
                    let total = 0
                    this.carList.forEach((item) => {
                        if (this.checkList.includes(item.positions)) {
                            total += this.priceCalc(item) * 1000
                        }
                    })
                    return total / 1000
                },
                // 是否登录
                isLogin() {
                    return window.localStorage.jwt ? true : false
                }
            },
            methods: {
                goLogin() {
                    sessionStorage.redirectUrl = '/goodsList.htm'
                    location.href = '/login.htm'
                },
                // 选择文件
                selectFile() {
                    document.getElementById('upFile').click()
                    // 监听文件上传
                    document.getElementById('upFile').addEventListener('change', (e) => {
                        // 获取文件
                        const file = e.target.files[0]
                        // 判断文件类型
                        if (file.type !== 'text/plain') {
                            this.$message.warning(lang.template_text137)
                            return
                        }
                        // 读取文件名
                        this.fileName = file.name
                        // 读取文件
                        const reader = new FileReader()
                        // 判断txt文件编码格式
                        reader.readAsText(file, 'utf-8')
                        reader.onload = e => {
                            const txtString = e.target.result
                            // utf-8 的 中文编码 正则表达式
                            const patrn = /[\u4E00-\u9FA5]/gi;
                            // 检测当前文本是否含有中文（如果没有，则当乱码处理）
                            // 两个格式的英文编码一样，所以纯英文文件也当成乱码再处理一次
                            if (!patrn.exec(txtString)) {
                                let reader_gb2312 = new FileReader()
                                // 再拿一次纯文本，这一次拿到的文本一定不会乱码
                                reader_gb2312.readAsText(file, 'gb2312')
                                reader_gb2312.onload = e2 => {
                                    this.fileContent = e2.target.result
                                }
                            } else {
                                this.fileContent = txtString
                            }
                        }
                    })
                },
                confirmUpload() {
                    // 解析输入框中的换行 把换行替换成\n 传字符串
                    let params = this.fileContent.replace(/[\r\n]/g, ',').split(',').filter((item) => {
                        return item !== ''
                    })
                    const maxLimt = this.domainConfig.number_limit || 500
                    if (params.length > maxLimt) {
                        // 截取前500个
                        params = params.slice(0, maxLimt)
                    }
                    this.textarea2 = params.join('\n')
                    this.cancelUpload()
                },
                cancelUpload() {
                    document.getElementById('upFile').value = ''
                    document.getElementById('upFile').removeEventListener('change', () => { })
                    this.fileName = ''
                    this.fileContent = ''
                    this.isShowUpload = false
                },
                // 获取域名设置
                getDomainSet() {
                    domainSetting().then((res) => {
                        this.domainConfig = res.data.data
                        this.selectSuffix = res.data.data.default_search_domain || ''
                    })
                },
                // 是否已经加入购物车
                isAddCart(item) {
                    const isHave = this.carList.find((cartItem) => {
                        return cartItem.config_options.domain === item.name
                    })
                    return isHave
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
                goBuyDomain() {
                    if (this.checkList.length === 0) {
                        this.$message.warning(lang.template_text138)
                        return
                    }
                    if (!this.isLogin) {
                        this.$message.warning(lang.template_text139)
                        sessionStorage.redirectUrl = '/goodsList.htm'
                        this.goLogin()
                        return
                    }
                    sessionStorage.setItem("buyDomainPosition", JSON.stringify(this.checkList))
                    location.href = '/buyDomain.htm'
                },
                // 批量查询域名
                batchSearchDomain() {
                    if (!this.textarea2) {
                        this.$message.warning(lang.template_text140)
                        return
                    }
                    // 解析输入框中的换行 把换行替换成\n 传字符串 
                    const params = this.textarea2.replace(/[\r\n]/g, ',').split(',').filter((item) => {
                        return item !== ''
                    }).join(',')
                    const maxLimt = this.domainConfig.number_limit || 500
                    if (params.split(',').length - 1 >= maxLimt) {
                        this.$message.warning(`${lang.template_text141}${maxLimt}${lang.template_text142}`)
                        return
                    }
                    this.batchLoading = true
                    domainBatch({ domains: params }).then((res) => {
                        this.availList = res.data.data.avail.map((item) => {
                            item.priceArr = []
                            item.showPrice = 0
                            item.priceLoading = true
                            return item
                        })
                        // 查询可注册的域名价格
                        this.availList.forEach((item) => {
                            domainPrice({ name: item.name }).then((res) => {
                                item.priceArr = res.data.data || []
                                item.showPrice = res.data.data[0].buyprice || 0
                            }).finally(() => {
                                item.priceLoading = false
                            })
                        })
                        this.unavailList = res.data.data.unavail
                        this.faillList = res.data.data.fail
                    }).catch((err) => {

                    }).finally(() => {
                        this.batchLoading = false
                    })
                },
                handleBatchChange(val) {
                    let checkedCount = val.length;
                    this.isBatchAllCheck = checkedCount === this.availList.length;
                    this.isBatchIndeterminate = checkedCount > 0 && checkedCount < this.availList.length;
                },
                handleBatchCheckAllChange(val) {
                    this.batchCheckGroup = val ? this.availList.map((item) => { return item.name }) : [];
                    this.isBatchIndeterminate = false;
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
                                priceArr: [],
                                showPrice: 0,
                                priceLoading: true
                            }
                        }).filter((item) => {
                            return item.customfield.is_domain === 1
                        })
                        // 拉取价格
                        this.carList = arr
                        this.isCarLoading = false
                        this.carList.forEach((item) => {
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
                handleCheckAllChange(val) {
                    this.checkList = val ? this.carList.map((item) => { return item.positions }) : [];
                    this.isIndeterminate = false;
                },
                handleCheckedCitiesChange(value) {
                    let checkedCount = value.length;
                    this.isAllCheck = checkedCount === this.carList.length;
                    this.isIndeterminate = checkedCount > 0 && checkedCount < this.carList.length;
                },
                // 加入购物车
                addCart(item) {
                    if (this.isAddCart(item)) {
                        return
                    }
                    this.isCarLoading = true
                    const params = {
                        product_id: this.product_id,
                        config_options: {
                            domain: item.name,
                            year: 1

                        },
                        qty: 1,
                        customfield: {
                            is_domain: 1  // 是否域名商品
                        }
                    }
                    addToCart(params).then((res) => {
                        if (res.data.status === 200) {
                            this.getCarList()
                        }
                    }).catch((err) => {
                        this.$message.error(err.data.msg)
                    }).finally(() => {
                        this.isCarLoading = false
                    })

                },
                // 批量加入购物车
                addAllCart() {
                    // 判断是否有选中的域名
                    if (this.batchCheckGroup.length === 0) {
                        this.$message.warning(lang.template_text138)
                        return
                    }
                    if (this.addAllLoading) {
                        return
                    }
                    this.addAllLoading = true
                    // 筛选出选中的域名
                    const arr = this.availList.filter((item) => {
                        return this.batchCheckGroup.includes(item.name)
                    })
                    // 循环调用加入购物车接口
                    const productsArr = []
                    arr.forEach((item) => {
                        const params = {
                            product_id: this.product_id,
                            config_options: {
                                domain: item.name,
                                year: 1

                            },
                            qty: 1,
                            customfield: {
                                is_domain: 1  // 是否域名商品
                            }
                        }
                        productsArr.push(params)
                    })
                    addToCart({ products: productsArr }).then((res) => {
                        if (res.data.status === 200) {
                            this.getCarList()
                        }
                    }).catch((err) => {
                        this.$message.error(err.data.msg)
                    }).finally(() => {
                        this.addAllLoading = false
                    })
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
                            // this.getCarList()
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
                        this.getCarList()
                    }
                },
                // 批量删除购物车
                async deleteClearCart() {
                    if (this.carList.length === 0) {
                        return
                    }
                    this.isCarLoading = true
                    const params = {
                        positions: this.carList.map((item) => { return item.positions })
                    }
                    const res = await deleteCartBatch(params)
                    if (res.data.status === 200) {
                        this.isCarLoading = false
                        this.getCarList()
                    }
                },
                // 获取后缀
                getSuffix() {
                    domainSuffix().then((res) => {
                        this.suffixList = res.data.data
                    })
                },
                // 选择后缀
                handelSelectSuffix(item) {
                    this.selectSuffix = item
                },
                // 域名查询
                handelDomainSearch() {
                    if (!this.domainInput) {
                        this.$message.warning(lang.template_text140)
                        return
                    }
                    this.isShowSuffixBox = false
                    this.isSearching = true
                    this.domainList = []
                    domainSearch({ domain: this.domainInput, suffix: this.selectSuffix }).then((res) => {
                        if (res.data.status === 200) {
                            this.domainList = res.data.data.map((item) => {
                                item.priceArr = []
                                item.showPrice = 0
                                item.priceLoading = true
                                return item
                            })
                            this.isSearching = false
                            this.domainList.forEach((item) => {
                                if (item.avail === 1) {
                                    domainPrice({ name: item.name }).then((res) => {
                                        item.priceArr = res.data.data || []
                                        item.showPrice = res.data.data[0].buyprice || 0
                                    }).finally(() => {
                                        item.priceLoading = false
                                    })
                                }
                            })
                            // 判断当前选中的域名后缀是否支持中文
                            const isChinese = this.suffixList.find((item) => {
                                return item.suffix === this.selectSuffix
                            }).allow_zh === 0
                            // 判断输入的是否是中文
                            const chineseReg = /[\u4e00-\u9fa5]/g
                            if (isChinese && chineseReg.test(this.domainInput)) {
                                this.domainList.unshift({
                                    // 添加一条不支持中文的提示
                                    name: this.domainInput + this.selectSuffix,
                                    avail: -2,
                                    description: lang.template_text143
                                })
                            }
                        }
                    }).catch((err) => {
                        this.isSearching = false
                    })
                },
                // 获取域名价格
                getDomainPrice() {

                },
                goWhois(item) {
                    window.open(`whois.htm?domain=${item.name}`)
                },
                getRule(arr) {
                    let isHave = this.showFun(arr, 'CartController::index')
                    if (isHave) {
                        this.isShowView = true
                    }
                    if (!this.isShowView) {
                        // 没有权限
                        location.href = "/noPermissions.htm"
                    }
                },
                showFun(arr, str) {
                    if (typeof arr == "string") {
                        return true;
                    } else {
                        let isShow = "";
                        isShow = arr.find((item) => {
                            let isHave = item.includes(str);
                            if (isHave) {
                                return isHave;
                            }
                        });
                        return isShow;
                    }
                },
                // 获取一级分类
                getProductGroup_first() {
                    productGroupFirst().then((res) => {
                        this.first_group_list = res.data.data.list
                    })
                },
                // 搜索
                searchGoods() {
                    this.searchLoading = true
                    this.goodsParmas.keywords = this.searchValue
                    this.goodsParmas.id = ''
                    this.goodsParmas.page = 1
                    this.goodsList = []
                    this.getProductGoodList()
                },
                // 获取二级分类
                getProductGroup_second(id) {
                    productGroupSecond(id).then((res) => {
                        this.secondLoading = false
                        this.second_group_list = res.data.data.list
                        if (res.data.data.list[0]) {
                            this.select_second_obj.id = res.data.data.list[0].id
                            this.select_second_obj.type = res.data.data.list[0].type
                            this.goodsParmas.id = this.select_second_obj.id
                            if (this.select_second_obj.type === 'domain') {
                                this.getSuffix()
                                this.getDomainSet()
                            }
                            this.getProductGoodList()
                        }
                    })
                },
                // 获取商品列表
                getProductGoodList() {
                    this.goodSLoading = true
                    productGoods(this.goodsParmas).then((res) => {
                        if (this.select_second_obj.type === 'domain' && res.data.data.list[0]) {
                            this.product_id = res.data.data.list[0].id
                        }
                        this.searchLoading = false
                        this.goodsList = this.goodsList.concat(res.data.data.list)
                        this.goodSLoading = false
                        if (res.data.data.list.length >= this.goodsParmas.limit) {
                            this.scrollDisabled = false
                        } else {
                            this.scrollDisabled = true
                        }
                    })
                },
                // 初始化
                async initData() {
                    // 获取一级分类
                    await productGroupFirst().then((res) => {
                        this.first_group_list = res.data.data.list
                    })
                    if (this.first_group_list[0]) {
                        this.select_first_obj.id = this.first_group_list[0].id
                        this.secondLoading = true
                        // 获取二级分类
                        await productGroupSecond(this.first_group_list[0].id).then((ress) => {
                            this.second_group_list = ress.data.data.list
                        })
                        if (this.second_group_list[0]) {
                            this.select_second_obj.id = this.second_group_list[0].id
                            this.select_second_obj.type = this.second_group_list[0].type
                            this.goodsParmas.page = 1
                            this.goodsParmas.id = this.second_group_list[0].id
                            this.secondLoading = false
                            if (this.select_second_obj.type === 'domain') {
                                this.getSuffix()
                                this.getDomainSet()
                            }
                            this.getProductGoodList()
                        } else {
                            this.goodSLoading = false
                            this.goodsList = []
                        }
                    } else {
                        this.goodSLoading = false
                        this.goodsList = []
                    }
                },
                // 点击一级分类
                selectFirstType(val) {
                    this.select_first_obj.id = val
                    this.secondLoading = true
                    this.goodsParmas.page = 1
                    this.second_group_list = []
                    this.select_second_obj = {
                        id: '',
                        type: ''
                    }
                    this.getProductGroup_second(val)
                    this.goodsList = []
                },
                // 点击二级分类
                selectSecondType(val) {
                    this.select_second_obj.id = val
                    this.select_second_obj.type = this.second_group_list.find((item) => { return item.id === val }).type
                    this.goodsParmas.id = val
                    this.goodsList = []
                    this.goodsParmas.page = 1
                    if (this.select_second_obj.type === 'domain') {
                        this.getSuffix()
                        this.getDomainSet()
                    }
                    this.getProductGoodList()
                },
                // 获取通用配置
                getCommonData() {
                    this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
                    document.title = this.commonData.website_name + '-' + lang.common_cloud_text301
                },
                // 点击购买
                goOrder(item) {
                    // 新窗口打开
                    window.open(`goods.htm?id=${item.id}&name=${item.name}`)
                },
                // 滚动计算
                scrollBottom() {
                    if (this.select_second_obj.type === 'domain') {
                        return
                    }
                    const scrollTop = document.documentElement.scrollTop || document.body.scrollTop
                    const clientHeight = document.documentElement.clientHeight
                    const scrollHeight = document.documentElement.scrollHeight
                    if (scrollTop + clientHeight >= scrollHeight) {
                        if (this.scrollDisabled) {
                        } else {
                            this.goodsParmas.page++
                            this.getProductGoodList()
                        }
                    }
                },
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
