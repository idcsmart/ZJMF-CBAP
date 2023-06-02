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
                this.getCommonData()
                this.initData()
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
                    select_first_obj: {}, // 选中的一级分类对象 
                    select_second_obj: {}, // 选中的一级分类对象 
                    first_group_list: [], // 一级分类数组
                    second_group_list: [], // 二级分类数组
                    commonData: {},
                    scrollDisabled: false,
                    secondLoading: false, // 二级分类加载
                    goodSLoading: true,
                    goodsParmas: {
                        keywords: '', // 关键字,搜索范围:商品ID,商品名,描述
                        id: '', // 二级分组ID
                        page: 1, // 页数
                        limit: 12 // 每页条数
                    },
                    goodsList: [], // 商品列表数组

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
            methods: {
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
                            this.goodsParmas.id = this.select_second_obj.id
                            this.getProductGoodList()
                        }
                    })
                },
                // 获取商品列表
                getProductGoodList() {
                    this.goodSLoading = true
                    productGoods(this.goodsParmas).then((res) => {
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
                            this.goodsParmas.page = 1
                            this.goodsParmas.id = this.second_group_list[0].id
                            this.secondLoading = false
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
                    this.select_second_obj = {}
                    this.getProductGroup_second(val)
                    this.goodsList = []
                },
                // 点击二级分类
                selectSecondType(val) {
                    this.select_second_obj.id = val
                    this.goodsParmas.id = val
                    this.goodsList = []
                    this.goodsParmas.page = 1
                    this.getProductGoodList()
                },
                // 获取通用配置
                getCommonData() {
                    this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
                    document.title = this.commonData.website_name + '-商城'
                },
                // 点击购买
                goOrder(item) {
                    // 新窗口打开
                    window.open(`goods.htm?id=${item.id}&name=${item.name}`)
                },
                // 滚动计算
                scrollBottom() {
                    const scrollTop = document.documentElement.scrollTop || document.body.scrollTop
                    const clientHeight = document.documentElement.clientHeight
                    const scrollHeight = document.documentElement.scrollHeight
                    if (scrollTop + clientHeight >= scrollHeight) {
                        if (this.scrollDisabled) {
                            console.log('不需要加载啦');
                        } else {
                            console.log('还需加载');
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
