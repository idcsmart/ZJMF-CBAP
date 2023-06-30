

// css 样式依赖common.css
const asideMenu = {
    template: ` 
  <el-aside width="240px" id="aside">
    <div class="aside-top">
        <el-input class="aside-search" v-model="keyWords" @keyup.enter.native="searchApp">
            <i class="el-icon-search" slot="prefix"></i>
            <el-button class="aside-search-btn" type="text" slot="suffix" @click="searchApp">搜索</el-button>
        </el-input>
        <el-menu :default-active="asideMenuActiveId" background-color="#F7FAFC"
            active-text-color="#1C25FF" @select="asideMenuSelect">
            <el-menu-item index="1">
                <img src="./img/common/menu-icon.png" alt="">
                <span slot="title">应用</span>
            </el-menu-item>
            <el-menu-item index="2">
                <img src="./img/common/menu-icon.png" alt="">
                <span slot="title">主题</span>
            </el-menu-item>
            <el-menu-item index="3">
                <img src="./img/common/menu-icon.png" alt="">
                <span slot="title">服务</span>
            </el-menu-item>
            <el-menu-item index="4">
                <img src="./img/common/menu-icon.png" alt="">
                <span slot="title">我的</span>
            </el-menu-item>
        </el-menu>
    </div>
    
    <div class="aside-row-line"></div>

    <div class="app-type-list" v-show="asideMenuActiveId == '1'">
        <div class="title">类别</div>
        <ul class="app-type-ul">
            <li class="app-type-li" :class="activeType==item.value?'active':null" v-for="item in appType" :key="item.id" @click="typeClick(item.value)">{{item.label}}</li>
        </ul>
    </div>
    <div class="aside-row-line" v-show="asideMenuActiveId == '1'"></div>

    <div class="aside-bottom">
        <a :href="commontData.terms_privacy_url" target="_blank">隐私权政策</a>
        <a :href="commontData.terms_service_url" target="_blank">服务条款</a>
    </div>

    <login-dialog ref="loginDialog"></login-dialog>
  </el-aside>
`,

    data() {
        return {
            commontData: {},
            keyWords: "",
            asideMenuActiveId: localStorage.getItem('asideMenuActiveId'),
            appType: [
                {
                    id: 1,
                    value: "all",
                    label: "全部",
                },
                {
                    id: 2,
                    value: "addon",
                    label: "插件",
                },
                {
                    id: 3,
                    value: "captcha",
                    label: "验证码接口",
                },
                {
                    id: 4,
                    value: "certification",
                    label: "实名接口",
                },
                {
                    id: 5,
                    value: "gateway",
                    label: "支付接口",
                },
                {
                    id: 6,
                    value: "mail",
                    label: "邮件接口",
                },
                {
                    id: 7,
                    value: "sms",
                    label: "短信接口",
                },
            ],
            activeType: localStorage.getItem('activeType'),
        };
    },
    components: {
        loginDialog
    },
    created() {
        let url = window.location.href
        let getqyinfo = url.split('?')[1]
        let getqys = new URLSearchParams('?' + getqyinfo)
        this.keyWords = getqys.get('keyWords')?getqys.get('keyWords'):''

        this.getCommontData()
    },
    mounted() {
        var orignalSetItem = localStorage.setItem;
        localStorage.setItem = function (key, newValue) {
            // 要监听的key为isMarketLogin
            if (key === "isMarketLogin") {
                var setItemEvent = new Event("setItemEvent");
                setItemEvent.newValue = newValue;
                window.dispatchEvent(setItemEvent);
                orignalSetItem.apply(this, arguments);
            } else {
                orignalSetItem.apply(this, arguments);
            }
        }

        window.addEventListener("setItemEvent", (e) => {
            if (e.newValue == 0) {
                // 显示登录弹窗
                this.$refs['loginDialog'].showLoginDialog()
                // console.log("更新了", e.newValue, "显示登录弹窗")
            }
        });
    },
    beforeDestroy() {
        window.removeEventListener("setItemEvent")
    },
    methods: {
        asideMenuSelect(e) {
            localStorage.setItem('asideMenuActiveId', e)
            // 应用
            if (e == 1) {
                this.activeType = "all"
                localStorage.setItem('activeType', "all")
                location.href = 'shop_app.html'
            }
            // 主题
            if (e == 2) {
                location.href = "shop_app.html?appType=template"
            }
            // 服务
            if (e == 3) {
                location.href = "shop_app.html?appType=service"
            }
            // 我的
            if (e == 4) {
                location.href = 'shop_client.html'
            }
            e
        },
        getCommontData() {
            if (localStorage.getItem('common_set_before')) {
                this.commontData = JSON.parse(localStorage.getItem('common_set_before'))
            } else {
                getCommon().then(res => {
                    if (res.data.status == 200) {
                        this.commontData = res.data.data
                        localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                    }
                })
            }
        },
        // 应用类别点击
        typeClick(value) {
            this.activeType = value
            localStorage.setItem('activeType', value)
            if (this.activeType == 'all') {
                location.href = `shop_app.html`
            } else {
                location.href = `shop_app.html?appType=${value}`
            }
        },
        searchApp() {
            location.href = `shop_search.html?keyWords=${this.keyWords}`
        },
        showLogin(){
            
        }
    },
};
