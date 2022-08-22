// css 样式依赖common.css
const asideMenu = {
    template: ` <el-aside width="160px">
    <div class="menu-list-top">
        <div class="menu-item" :class="item.id === menuActiveId ? 'menu-active':''" v-for="item in menu1" :key="item.id" @click="toPage(item)">
            <img :src="item.icon" class="item-img">
            <span class="item-text">{{item.text}}</span>
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
                // { icon: `${url}/img/common/menu1.png`, text: lang.menu_1, url: "", id: 1 },
                // { icon: `${url}/img/common/menu2.png`, text: lang.menu_2, url: "./cloudList.html", id: 2 },
                { icon: `${url}/img/common/menu3.png`, text: lang.menu_3, url: "./finance.html", id: 3 },
                { icon: `${url}/img/common/menu4.png`, text: lang.menu_4, url: "./accountCommon.html", id: 4 },
                // { icon: `${url}/img/common/menu5.png`, text: lang.menu_5, url: "", id: 5 },
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