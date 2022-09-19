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
        this.setActiveMenu()
    },
    methods: {
        // 页面跳转
        toPage(e) {
            location.href = './' + e.url
        },
        // 判断当前菜单激活
        setActiveMenu() {
            const url = location.href
            this.menu1.forEach(item => {
                if (url.indexOf(item.url) != -1) {
                    console.log(item.id);
                    this.menuActiveId = item.id
                }
            });
        },
        // 获取前台导航
        doGetMenu() {

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

        },
    },
}