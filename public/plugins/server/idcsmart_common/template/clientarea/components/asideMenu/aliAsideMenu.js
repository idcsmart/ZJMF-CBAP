// css 样式依赖common.css
const aliAsideMenu = {
    template: ` <el-aside width="160px">
    <img class="ali-logo" :src="logo"></img>
    <div class="menu-list-top">
        <div class="menu-item" :class="item.id === menuActiveId ? 'menu-active':''" v-for="item in menu1" :key="item.id" @click="toPage(item)">
            <img :src="item.icon" class="item-img">
            <span class="item-text">{{item.text}}</span>
        </div>
    </div>
</el-aside>`,
    data() {
        return {
            activeId: 1,
            menu1: [
                { icon: `${url}/img/common/menu1.png`, text: lang.menu_1, url: "./index.html", id: 1 },
                { icon: `${url}/img/common/menu2.png`, text: lang.menu_4, url: "./account.html", id: 2 },
            ],
            logo:`${url}/img/ali/logo.png`
        }
    },
    created() {
        console.log(location.href);

    },
    methods: {
        // 页面跳转
        toPage(e) {
            location.href = e.url
        },
    },
    props: {
        menuActiveId: {
            type: Number,
            default: 1
        }
    },
}