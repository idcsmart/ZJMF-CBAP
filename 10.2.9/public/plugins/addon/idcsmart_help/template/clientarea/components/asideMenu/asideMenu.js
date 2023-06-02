// css 样式依赖common.css
const asideMenu = {
    template: ` <el-aside width="160px">
    <img class="ali-logo" :src="logo"></img>
    <div class="menu-list-top">
        <div class="menu-item" :class="item.id == menuActiveId ? 'menu-active':''" v-for="item in menu1" :key="item.id" @click="toPage(item)">
            <img :src="item.icon" class="item-img">
            <span class="item-text">{{item.name}}</span>
        </div>
    </div>
    <div class="menu-list-bottom">
        <div class="menu-item" :class="item.id == menuActiveId ? 'menu-active':''" v-for="item in menu2" :key="item.id" @click="toPage(item)">
            <img :src="item.icon" class="item-img">
            <span class="item-text">{{item.text}}</span>
        </div>
    </div>
</el-aside>`,
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
    methods: {
        // 页面跳转
        toPage(e) {
            // 获取 当前点击导航的id 存入本地
            const id = e.id
            localStorage.setItem('frontMenusActiveId', id)
            // 跳转到对应路径
            location.href = './' + e.url
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
                    })
                    this.menu1 = menu
                    this.setActiveMenu()
                }
            })

        },
    },
}