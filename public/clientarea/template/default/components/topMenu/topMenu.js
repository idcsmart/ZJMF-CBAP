
// css 样式依赖common.css
const topMenu = {
    template:
        `
    <el-header>
<div class="header-left">
    <img v-if="isShowMore" src="${url}/img/common/search.png" class="left-img">
    
    <el-autocomplete
    v-if="isShowMore"
        v-model="topInput"
        :fetch-suggestions="querySearchAsync"
        placeholder="请输入内容"
        @select="handleSelect"
    >
    
    <template slot-scope="{ item }">
        <div class="search-value">{{ item.value }}</div>
        <div class="search-name">{{ item.name }}</div>
    </template>

    </el-autocomplete>
</div>
<div class="header-right">
    <div class="right-item" @click="logOut()" >
        <img src="${url}/img/common/exit.png">
    </div>
    <div class="right-item item-cur">
        <el-dropdown @command="changeLang">
            <img :src="curSrc" alt="">
            <el-dropdown-menu slot="dropdown">
                <el-dropdown-item command="zh-cn">{{lang.chinese}}</el-dropdown-item>
            </el-dropdown-menu>
        </el-dropdown>
    </div>
    <div v-if="isShowMore" class="right-item item-bell">
        <img src="${url}/img/common/bell.png">
        <span class="bell-num">2</span>
    </div>
    <div v-if="isShowMore" class="right-item">
        <img src="${url}/img/common/cart.png">
    </div>
</div>
</el-header>
    `,
    data() {
        return {
            topInput: "",
            // curSrc: url+'/img/common/'+lang_obj.countryImg+'.png' ,
            curSrc: `/upload/common/country/${lang_obj.countryImg}.png`
        }
    },
    props: {
        isShowMore: {
            type: Boolean,
            default: false
        }
    },
    created() {

    },
    methods: {
        // 退出登录
        logOut() {
            this.$confirm('您将退出登录，是否继续', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                //const res = await Axios.post('/logout')
                Axios.post('/logout').then(res => {
                    localStorage.removeItem("jwt")
                    setTimeout(() => {
                        location.href = 'login.html'
                    }, 300)
                })
            }).catch(() => { })
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
        handleSelect(e) {
            const id = e.id
            location.href = `cloudList.html?${id}`
        }
    },
}