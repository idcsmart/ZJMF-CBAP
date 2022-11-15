
// css 样式依赖common.css
const topMenu = {
    template:
        `
        <div>
            <el-drawer
            :visible.sync="isShowMenu"
            direction="ltr"
            :before-close="handleClose"
            :with-header="false"
            size="3.8rem"
            custom-class="drawer-menu"
            >
                <div class="drawer-menu-top">
                    <img class="drawer-menu-logo" :src="logo"></img>
                </div>
                <div class="drawer-menu-list-top">
                    <div class="drawer-menu-item" :class="item.id == menuActiveId ? 'drawer-menu-active':''" v-for="item in menu1" :key="item.id" @click="toPage(item)">
                        <img :src="item.icon" class="drawer-item-img">
                        <span class="drawer-item-text">{{item.name}}</span>
                    </div>
                </div>
            </el-drawer>

            <el-header>
            <div class="header-left">
                <img src="${url}/img/common/menu.png" class="menu-img" @click="showMenu">
                <img v-if="isShowMore" src="${url}/img/common/search.png" class="left-img">
                <el-autocomplete
                v-if="isShowMore"
                    v-model="topInput"
                    :fetch-suggestions="querySearchAsync"
                    placeholder="请输入内容"
                    @select="handleSelect"
                >
                <template slot-scope="{ item }">
                    <div class="search-value">{{ item.value }}</div>
                    <div class="search-name">{{ item.name }}</div>
                </template>
                </el-autocomplete>
            </div>
            <div class="header-right">
                
                <div class="header-right-item car-item">
                    <div v-if="isShowCart" class="right-item" @click="goShoppingCar">
                        <el-badge :value="shoppingCarNum" class="item" :hidden="shoppingCarNum === 0 ? true : false">
                            <img src="${url}/img/common/cart.png">
                        </el-badge>
                    </div>
                </div>

                <div class="header-right-item">
                    <el-dropdown @command="changeLang" trigger="click">
                        <div class="el-dropdown-country">
                            <img :src="curSrc" alt="">
                            <i class="right-icon el-icon-arrow-down el-icon--right"></i>
                        </div>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item v-for="item in commonData.lang_list" :key="item.display_flag" :command="item.display_lang">{{item.display_name}}</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>

                <div class="header-right-item cloum-line-item">
                    <div class="cloum-line"></div>
                </div>

                <div class="header-right-item">
                    <el-dropdown @command="handleCommand" trigger="click">
                        <div class="el-dropdown-header">
                            <div class="right-item head-box" ref="headBoxRef" v-show="firstName">{{firstName}}</div>
                            <i class="right-icon el-icon-arrow-down el-icon--right"></i>
                        </div>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item command="account">账户信息</el-dropdown-item>
                            <el-dropdown-item command="quit">退出登录</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>

            </div>
        </el-header>
        </div>

    `,
    data() {




        return {
            topInput: "",
            // curSrc: url+'/img/common/'+lang_obj.countryImg+'.png' ,
            curSrc: `/upload/common/country/${lang_obj.countryImg}.png`,
            isShowMenu: false,
            logo: `/upload/logo.png`,
            menu1: [],
            menuActiveId: 0,
            firstName: '',
            produclData: [],
            selectValue: '',
            shoppingCarNum: 0,
            headBgcList: ['#3699FF', '#57C3EA', '#5CC2D7', '#EF8BA2', '#C1DB81', '#F1978C', '#F08968'],
            commonData: {
                lang_list: []
            },
        }
    },
    props: {
        isShowMore: {
            type: Boolean,
            default: false
        },
        isShowCart: {
            type: Boolean,
            default: true
        },
        num: {
            type: Number,
            default: 0
        }
    },
    watch: {
        num(val) {
            if (val) {
                this.shoppingCarNum = val
            }
        }
    },
    created() {
        this.GetIndexData()
        this.doGetMenu()
        this.setActiveMenu()
        this.getCartList()
        this.getCommonSetting()
    },
    methods: {
        // 退出登录
        logOut() {
            this.$confirm('您将退出登录，是否继续', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                //const res = await Axios.post('/logout')
                Axios.post('/logout').then(res => {
                    localStorage.removeItem("jwt")
                    setTimeout(() => {
                        location.href = '/login.html'
                    }, 300)
                })
            }).catch(() => { })
        },
        // 获取购物车数量
        getCartList() {
            cartList().then((res) => {
                this.shoppingCarNum = res.data.data.list.length
            })
        },
        GetIndexData() {
            indexData().then((res) => {
                this.firstName = res.data.data.account.username.substring(0, 1).toUpperCase()
                if (sessionStorage.headBgc) {
                    this.$refs.headBoxRef.style.background = sessionStorage.headBgc
                } else {
                    const index = Math.round(Math.random() * this.headBgcList.length)
                    this.$refs.headBoxRef.style.background = this.headBgcList[index]
                    sessionStorage.headBgc = this.headBgcList[index]
                }
            })
        },
        goShoppingCar() {
            localStorage.frontMenusActiveId = ''
            location.href = '/shoppingCar.html'
        },
        goAccountpage() {
            location.href = '/account.html'
        },
        // 语言切换
        changeLang(e) {
            if (localStorage.getItem('lang') !== e || !localStorage.getItem('lang')) {
                if (localStorage.getItem('lang')) {
                    window.location.reload()
                }
                localStorage.setItem('lang', e)
            }
        },
        handleCommand(e) {
            if (e == 'account') {
                this.goAccountpage()
            }
            if (e == 'quit') {
                this.logOut()
            }
            console.log(e);
        },
        // 全局搜索
        querySearchAsync(queryString, cb) {
            if (queryString.length == 0) {
                return false
            }
            const params = {
                keywords: queryString
            }
            globalSearch(params).then(res => {
                if (res.data.status === 200) {
                    const data = res.data.data.hosts
                    const result = []
                    data.map(item => {
                        let value = item.product_name + '#/' + item.id
                        result.push(
                            {
                                id: item.id,
                                value,
                                name: item.name
                            }
                        )
                    })
                    cb(result)

                }
            })
        },
        handleSelect(e) {
            const id = e.id
            location.href = `cloudList.html?${id}`
        },
        showMenu() {
            this.isShowMenu = true
        },
        handleClose() {
            this.isShowMenu = false
        },
        // 获取前台导航
        doGetMenu() {
            // 判断本地缓存是否有 前台导航，没有则调用接口获取，有则直接使用
            if (JSON.parse(localStorage.getItem('frontMenus'))) {
                const menu = JSON.parse(localStorage.getItem('frontMenus'))
                menu.map(item => {
                    if (item.icon) {
                        item.icon = `${url}img/common/${item.icon}.png`
                    }
                })
                this.menu1 = menu
            } else {
                getMenu().then(res => {
                    if (res.data.status === 200) {
                        localStorage.setItem('frontMenus', JSON.stringify(res.data.data.menu))
                        const menu = JSON.parse(localStorage.getItem('frontMenus'))
                        menu.map(item => {
                            if (item.icon) {
                                item.icon = `${url}img/common/${item.icon}.png`
                            }
                        })
                        this.menu1 = menu
                    }
                })
            }
        },
        // 判断当前菜单激活
        setActiveMenu() {
            const url = location.href
            let isTrue = true
            this.menu1.forEach(item => {
                // 当前url下存在和导航菜单对应的路径
                if (url.indexOf(item.url) != -1) {
                    this.menuActiveId = item.id
                    isTrue = false
                }
            });
            if (isTrue) {
                // 不存在对应的 读取本地存储的 导航id\
                this.menuActiveId = localStorage.getItem('frontMenusActiveId')
            }
        },
        // 页面跳转
        toPage(e) {
            location.href = '/' + e.url
        },

        // 获取通用配置
        async getCommonSetting() {
            try {
                const res = await getCommon()
                this.commonData = res.data.data
                localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
            } catch (error) {
            }
        },
    },
}