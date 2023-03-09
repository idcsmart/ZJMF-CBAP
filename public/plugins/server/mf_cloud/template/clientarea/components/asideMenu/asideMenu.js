// css 样式依赖common.css
const asideMenu = {
    template: ` <el-aside width="160px">
    <img class="ali-logo" :src="logo"></img>

    <el-menu class="menu-top" :default-active="menuActiveId" @select="handleSelect" background-color="#1E41C9" text-color="rgba(255, 255, 255, .6)" active-text-color="#FFF">
        <template v-for="item in menu1">
            <!-- 只有一级菜单 -->
            <el-menu-item v-if="!item.child" :key="item.id" :index="item.url" :id="item.url">
                    <img slot="title" :src="item.icon" class="item-img">
                    <span class="aside-menu-text" slot="title">{{item.name}}</span>
            </el-menu-item>
            <!-- 有二级菜单 -->
            <el-submenu v-else :key="item.id" :index="item.icon" :id="item.url">
                <template slot="title">
                    <img slot="title" :src="item.icon" class="item-img">
                    <span class="aside-menu-text" slot="title">{{item.name}}</span>
                </template>
                <template v-for="child in item.child">
                    <el-menu-item  :index="child.url" :key="child.id">{{child.name}}</el-menu-item>
                </template>
            </el-submenu>
        </template>
    </el-menu>

    <div class="line"></div>

    <el-menu class="menu-top" :default-active="menuActiveId" @select="handleSelect" background-color="#1E41C9" text-color="rgba(255, 255, 255, .6)" active-text-color="#FFF">
        <template v-for="item in menu2">
            <!-- 只有一级菜单 -->
            <el-menu-item v-if="!item.child" :key="item.id" :index="item.url" :id="item.url">
                <img slot="title" :src="item.icon" class="item-img">
                <span class="aside-menu-text" slot="title">{{item.name}}</span>
            </el-menu-item>
            <!-- 有二级菜单 -->
            <el-submenu v-else :key="item.id" :index="item.icon" :id="item.url">
                <template slot="title">
                    <img slot="title" :src="item.icon" class="item-img">
                    <span class="aside-menu-text" slot="title">{{item.name}}</span>
                </template>
                <template v-for="child in item.child">
                    <el-menu-item  :index="child.url" :key="child.id">{{child.name}}</el-menu-item>
                </template>
            </el-submenu>
        </template>
    </el-menu>



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
            menuActiveId: ''
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
        // toPage(e) {
        //     // 获取 当前点击导航的id 存入本地
        //     const id = e.id
        //     localStorage.setItem('frontMenusActiveId', id)
        //     // 跳转到对应路径
        //     location.href = '/' + e.url
        // },
        // 判断当前菜单激活
        setActiveMenu() {
            const url = location.href
            let isTrue = true
            this.menu1.forEach(item => {
                // 当前url下存在和导航菜单对应的路径
                if (item.url && url.indexOf(item.url) != -1) {
                    this.menuActiveId = item.url
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

        },
        handleSelect(key) {
            localStorage.setItem('frontMenusActiveId', key)
            // 跳转到对应路径
            location.href = '/' + key
        },
    },
}