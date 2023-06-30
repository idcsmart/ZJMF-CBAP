
// css 样式依赖common.css
ELEMENT.Dialog.props.closeOnClickModal.default = false
const topMenu = {
    template:
        `
        <div>
        <el-drawer :visible.sync="isShowMenu" direction="ltr" :before-close="handleClose" :with-header="false" size="3.8rem" custom-class="drawer-menu">
          <div class="drawer-menu-top">
            <img class="drawer-menu-logo" @click="goHome" :src="logo"></img>
          </div>
          <div class="drawer-menu-list-top">
          <el-menu class="menu-top" :default-active="menuActiveId" @select="handleSelect" background-color="#1E41C9" text-color="rgba(255, 255, 255, .6)" active-text-color="#FFF">
          <template v-for="item in menu1">
              <!-- 只有一级菜单 -->
              <el-menu-item v-if="!item.child || item.child?.length === 0" :key="item.id" :index="item.url" :id="item.url">
                      <i class="iconfont" :class="item.icon"></i>
                      <span class="aside-menu-text" slot="title">{{item.name}}</span>
              </el-menu-item>
              <!-- 有二级菜单 -->
              <el-submenu v-else :key="item.id" :index="item.icon" :id="item.url">
                  <template slot="title">
                      <i class="iconfont" :class="item.icon"></i>
                      <span class="aside-menu-text" slot="title">{{item.name}}</span>
                  </template>
                  <template v-for="child in item.child">
                      <el-menu-item  :index="child.url" :key="child.id">{{child.name}}</el-menu-item>
                  </template>
              </el-submenu>
          </template>
      </el-menu>
  
      <div class="line" v-if="hasSeparate"></div>
  
      <el-menu class="menu-top" :default-active="menuActiveId " @select="handleSelect" background-color="#1E41C9" text-color="rgba(255, 255, 255, .6)" active-text-color="#FFF">
          <template v-for="item in menu2">
              <!-- 只有一级菜单 -->
              <el-menu-item v-if="!item.child || item.child?.length === 0" :key="item.id" :index="item.url" :id="item.url">
                  <i class="iconfont" :class="item.icon"></i>
                  <span class="aside-menu-text" slot="title">{{item.name}}</span>
              </el-menu-item>
              <!-- 有二级菜单 -->
              <el-submenu v-else :key="item.id" :index="item.icon" :id="item.url">
                  <template slot="title">
                      <i class="iconfont" :class="item.icon"></i>
                      <span class="aside-menu-text" slot="title">{{item.name}}</span>
                  </template>
                  <template v-for="child in item.child">
                      <el-menu-item  :index="child.url" :key="child.id">{{child.name}}</el-menu-item>
                  </template>
              </el-submenu>
          </template>
      </el-menu>
  
          </div>
        </el-drawer>
      
        <el-header>
          <div class="header-left">
            <img src="${url}/img/common/menu.png" class="menu-img" @click="showMenu">
            <img v-if="isShowMore" src="${url}/img/common/search.png" class="left-img">
            <el-autocomplete v-if="isShowMore" v-model="topInput" :fetch-suggestions="querySearchAsync" placeholder="请输入内容" @select="handleSelect">
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
      
            <div class="header-right-item hg-24">
              <el-dropdown @command="changeLang" trigger="click" :disabled="commonData.lang_home_open * 1 ? false : true">
                <div class="el-dropdown-country">
                  <img :src="curSrc" alt="">
                  <i class="right-icon el-icon-arrow-down el-icon--right"></i>
                </div>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item v-for="item in commonData.lang_list" :key="item.display_lang" :command="item.display_lang">{{item.display_name}}</el-dropdown-item>
                </el-dropdown-menu>
              </el-dropdown>
            </div>
      
            <div class="header-right-item cloum-line-item" v-if="isGetData">
              <div class="cloum-line"></div>
            </div>
      
            <div class="header-right-item" v-show="unLogin && isGetData">
              <div class="un-login" @click="goLogin">
                <img src="${url}/img/common/login_icon.png">{{lang.topMenu_text1}}
              </div>
            </div>
      
            <div class="header-right-item" v-show="!unLogin && isGetData">
              <el-dropdown @command="handleCommand" trigger="click">
                <div class="el-dropdown-header">
                  <div class="right-item head-box" ref="headBoxRef" v-show="firstName">{{firstName}}</div>
                  <i class="right-icon el-icon-arrow-down el-icon--right"></i>
                </div>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item command="account">{{lang.topMenu_text2}}</el-dropdown-item>
                  <el-dropdown-item command="quit">{{lang.topMenu_text3}}</el-dropdown-item>
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
            menu2: [],
            menuActiveId: "",
            firstName: '',
            hasSeparate: false,
            produclData: [],
            selectValue: '',
            shoppingCarNum: 0,
            headBgcList: ['#3699FF', '#57C3EA', '#5CC2D7', '#EF8BA2', '#C1DB81', '#F1978C', '#F08968'],
            commonData: {
                lang_list: []
            },
            unLogin: true,
            isGetData: false
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
            this.$confirm(lang.topMenu_text4, lang.topMenu_text5, {
                confirmButtonText: lang.topMenu_text6,
                cancelButtonText: lang.topMenu_text7,
                type: 'warning'
            }).then(() => {
                //const res = await Axios.post('/logout')
                Axios.post('/logout').then(res => {
                    localStorage.removeItem("jwt")
                    setTimeout(() => {
                        location.href = '/login.htm'
                    }, 300)
                })
            }).catch(() => { })
        },
        goLogin() {
            location.href = '/login.htm'
        },
        goHome() {
            localStorage.frontMenusActiveId = "";
            location.href = "/home.htm";
        },
        // 获取购物车数量
        getCartList() {
            cartList().then((res) => {
                this.shoppingCarNum = res.data.data.list.length
            })
        },
        GetIndexData() {
            accountDetail().then((res) => {
                if (res.data.status == 200) {
                    this.firstName = res.data.data.account.username.substring(0, 1).toUpperCase()
                    this.unLogin = false
                    if (sessionStorage.headBgc) {
                        this.$refs.headBoxRef.style.background = sessionStorage.headBgc
                    } else {
                        const index = Math.round(Math.random() * (this.headBgcList.length - 1))
                        this.$refs.headBoxRef.style.background = this.headBgcList[index]
                        sessionStorage.headBgc = this.headBgcList[index]
                    }
                }
            }).finally(() => {
                this.isGetData = true
            })
        },
        goShoppingCar() {
            localStorage.frontMenusActiveId = ''
            location.href = '/shoppingCar.htm'
        },
        goAccountpage() {
            location.href = '/account.htm'
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
        handleSelect(key) {
            localStorage.setItem("frontMenusActiveId", key);
            // 跳转到对应路径
            location.href = "/" + key;
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
                let index = menu.findIndex((item) => item.name == "分隔符");
                if (index != -1) {
                    this.hasSeparate = true
                    this.menu1 = menu.slice(0, index);
                    this.menu2 = menu.slice(index + 1);
                } else {
                    this.hasSeparate = false
                    this.menu1 = menu;
                }
                this.setActiveMenu();
            } else {
                getMenu().then(res => {
                    if (res.data.status === 200) {
                        const menu = res.data.data.menu;
                        localStorage.setItem("frontMenus", JSON.stringify(menu));
                        let index = menu.findIndex((item) => item.name == "分隔符");
                        if (index != -1) {
                            this.hasSeparate = true
                            this.menu1 = menu.slice(0, index);
                            this.menu2 = menu.slice(index + 1);
                        } else {
                            this.hasSeparate = false
                            this.menu1 = menu;
                        }
                        this.setActiveMenu();
                    }
                })
            }
        },
        // 判断当前菜单激活
        setActiveMenu() {
            const url = location.href;
            let isTrue = true;
            this.menu1.forEach((item) => {
                // 当前url下存在和导航菜单对应的路径
                if (item.url && url.indexOf(item.url) != -1) {
                    this.menuActiveId = item.url;
                    isTrue = false;
                }
            });
            if (isTrue) {
                // 不存在对应的 读取本地存储的 导航id\
                this.menuActiveId = localStorage.getItem("frontMenusActiveId");
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
                if (!localStorage.lang) {
                    localStorage.setItem('lang', this.commonData.lang_home)
                }
            } catch (error) {
            }
        },
    },
}