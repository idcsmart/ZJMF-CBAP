// css 样式依赖common.css
const topMenu = {
    template: ` 
    <el-header height="80px" id="header">
        <img id="logo" src="./img/common/logo.png" alt="" @click="toHome">
        
        <div class="header-right">
            <span class="regist-btn" v-if="!firstName" @click="showLogin">
                登录
            </span>
            <div class="user" v-else>{{firstName}}</div>
            <el-select class="authorizeSelect" v-model="domain" @change="domainChange">
                <el-option v-for="(item,index) in authorizeData" :key="index" :value="item" :label="item.domain"></el-option>
            </el-select>
        </div>
    </el-header>
`,

    data() {
        return {
            authorizeData: [],
            domain: "",
            isLogin: false,
            firstName: ""
        };
    },
    mounted() {

    },
    created() {
        this.getAuthorize()
        this.getAccount()
    },
    methods: {
        toHome() {
            location.href = 'index.html'
        },
        getAuthorize() {
            authorize().then(res => {
                if (res.data.status == 200) {
                    this.authorizeData = res.data.data.list
                    if (this.authorizeData[0]) {
                        this.domainChange(this.authorizeData[0])
                        this.domain = this.authorizeData[0]
                    }
                }
            })
        },
        domainChange(e) {
            this.$emit('authorizechange', e)
        },
        // 账户详情
        getAccount() {
            accountDetail().then(res => {
                if(res.data.status == 200){
                    this.firstName = res.data.data.account.username.substring(0, 1).toUpperCase()
                }
            })
        },
        showLogin(){
            localStorage.setItem("isMarketLogin","0")
        }
    },
};
