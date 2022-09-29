// css 样式依赖common.css
const asideMenu = {
    template: ` <el-aside width="160px">
    <img class="ali-logo" :src="logo"></img>
    <div class="menu-list-top">
        <div class="menu-item father-item">
            <img src="${url}/img/common/menu2.png" class="item-img">
            <span class="item-text">产品列表</span>
        </div>
        <div class="child-menu">
            <span class="child-item-text" :class="menuActiveId ==1 ?'child-active':''" @click="tochild(1)">云服务器</span>
  
            <span class="child-item-text" :class="menuActiveId ==3 ?'child-active':''" @click="tochild(3)">通用产品</span>
        </div>

        <div class="menu-item" :class="item.id == menuActiveId ? 'menu-active':''" v-for="item in menu1" :key="item.id" @click="toPage(item)">
            <img :src="item.icon" class="item-img">
            <span class="item-text">{{item.name}}</span>
        </div>
    </div>
    <div class="line"></div>
    <div class="menu-list-bottom">
        <div class="menu-item" :class="item.id == menuActiveId ? 'menu-active':''" v-for="item in menu2" :key="item.id" @click="toPage(item)">
            <img :src="item.icon" class="item-img">
            <span class="item-text">{{item.name}}</span>
        </div>
    </div>
</el-aside>`,
    // 云服务器 当前
    // 物理服务器 dcim
    // 通用产品
    data() {
        return {
            activeId: 1,
            menu1: [

            ],
            menu2: [
            ],
            logo: `/upload/logo.png`,
            menuActiveId: 0
        }
    },
    created() {
        this.doGetMenu()
    },
    updated() {
        // // 关闭loading
        document.getElementById('mainLoading').style.display = 'none';
        document.getElementsByClassName('template')[0].style.display = 'block'
    },
    methods: {
        // 页面跳转
        toPage(e) {
            // 获取 当前点击导航的id 存入本地
            const id = e.id
            localStorage.setItem('frontMenusActiveId', id)
            // 跳转到对应路径
            location.href = '/' + e.url
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
                if (url.indexOf('cloudList') != -1) {
                    this.menuActiveId = 1
                    isTrue = false
                }
                if (url.indexOf('common_product_list') != -1) {
                    this.menuActiveId = 3
                    isTrue = false
                }
            });
            if (isTrue) {
                // 不存在对应的 读取本地存储的 导航id\
                this.menuActiveId = localStorage.getItem('frontMenusActiveId')
            }
        },
        // 获取前台导航
        doGetMenu() {
            getMenu().then(res => {
                if (res.data.status === 200) {
                    const menu = res.data.data.menu
                    localStorage.setItem('frontMenus', JSON.stringify(menu))
                    menu.map(item => {
                        if (item.icon) {
                            item.icon = `${url}img/common/${item.icon}.png`
                        }
                        // 判断是否插件导航
                        if (item.url.indexOf("plugin") != -1) {
                            this.menu2.push(item)
                        } else {
                            this.menu1.push(item)
                        }
                    })
                    // this.menu1 = menu
                    this.setActiveMenu()
                }
            })

        },
        tochild(id) {
            this.menuActiveId = id
            localStorage.setItem('frontMenusActiveId', id)
            if (id == 1) {
                location.href = "/cloudList.html"
                
            }
            if (id == 2) {

          
            }
            if (id == 3) {
                location.href = "/common_product_list.html"
            }
            
        }
    },
}