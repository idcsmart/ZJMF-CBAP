// css 样式依赖common.css
const asideMenu = {
    template: ` <el-aside width="160px">
    <div class="menu-list-top">
        <div class="menu-item" :class="item.id === menuActiveId ? 'menu-active':''" v-for="item in menu1" :key="item.id" @click="toPage(item)">
            <img :src="item.icon" class="item-img">
            <span class="item-text">{{item.name}}</span>
        </div>
    </div>
    <div class="menu-list-bottom">
        <div class="menu-item" :class="item.id === menuActiveId ? 'menu-active':''" v-for="item in menu2" :key="item.id" @click="toPage(item)">
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
                // { icon: `${url}/img/common/menu6.png`, text: lang.menu_6, url: "", id: 6 },
                // { icon: `${url}/img/common/menu7.png`, text: lang.menu_7, url: "", id: 7 },
                // { icon: `${url}/img/common/menu8.png`, text: lang.menu_8, url: "", id: 8 },
                // { icon: `${url}/img/common/menu9.png`, text: lang.menu_9, url: "", id: 9 },
                // { icon: `${url}/img/common/menu10.png`, text: lang.menu_10, url: "", id: 10 },
                // { icon: `${url}/img/common/menu1.png`, text: "阿里云首页", url: "./index.html", id: 11 },
            ],
        }
    },
    created() {
        const menu = JSON.parse(localStorage.getItem('frontMenus'))
        menu.map(item=>{
            if(item.icon){
                item.icon = `${url}img/common/${item.icon}.png`
            }
        })
        this.menu1 = menu
        console.log(this.menu1);
    },
    methods: {
        // 页面跳转
        toPage(e) {
            location.href = './' + e.url
        },
    },
    props: {
        menuActiveId: {
            type: Number,
            default: 1
        }
    },
}