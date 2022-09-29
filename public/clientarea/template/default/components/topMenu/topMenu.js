
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
                <div class="right-item" @click="logOut()" >
                    <img src="${url}/img/common/exit.png">
                </div>
                <div class="right-item item-cur">
                    <el-dropdown @command="changeLang">
                        <img :src="curSrc" alt="">
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item command="zh-cn">{{lang.chinese}}</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>
                <div v-if="isShowMore" class="right-item item-bell">
                    <img src="${url}/img/common/bell.png">
                    <span class="bell-num">2</span>
                </div>
                <div v-if="isShowMore" class="right-item">
                    <img src="${url}/img/common/cart.png">
                </div>

                <span class="buy-product-btn" @click="toOrder">订购</span>
                <el-select class="product-select" v-model="selectValue" placeholder="请选择商品">
                    <el-option v-for="item in produclData" :key="item.id" :value="item.id" :label="item.name"></el-option>
                </el-select>
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
            produclData: [],
            selectValue: ''
        }
    },
    props: {
        isShowMore: {
            type: Boolean,
            default: false
        }
    },
    created() {
        this.doGetMenu()
        this.setActiveMenu()
        // 获取商品列表
        this.getProductList()
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
                        location.href = 'login.html'
                    }, 300)
                })
            }).catch(() => { })
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
        // 获取商品列表
        getProductList() {
            const params = {
                limit: 1000
            }
            productList(params).then(res => {
                if (res.data.status === 200) {
                    this.produclData = res.data.data.list
                }
            })
        },
        // 单项商品点击
        itemClick(item) {
            console.log(item);
        },
        toOrder() {
            console.log(this.selectValue);
            const id = this.selectValue
            this.produclData.map(item => {
                if (item.id == id) {
                    if (item.module == 'common_cloud' || item.module1 == 'common_cloud') {
                        location.href = `/order.html?id=${item.id}`
                    }

                    if (item.module1 == 'idcsmart_common' || item.module == 'idcsmart_common') {
                        location.href = `/common_product.html?id=${item.id}`
                    }
                }
            })
        }
    },
}