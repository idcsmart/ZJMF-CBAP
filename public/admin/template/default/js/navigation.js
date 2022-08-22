(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('navigation')[0]
        Vue.prototype.lang = window.lang
        Vue.prototype.moment = moment
        new Vue({
            data() {
                return {
                    // 菜单列表
                    menuList: [],
                    loading:false,
                    columns: [
                        {
                            // 列拖拽排序必要参数
                            colKey: 'drag',
                            width: 40
                        },
                        {
                            colKey:'name',
                            title:"名称"
                        }
                    ]

                }
            },
            mounted() {
                this.maxHeight = document.getElementById('content').clientHeight - 170
                let timer = null
                window.onresize = () => {
                    if (timer) {
                        return
                    }
                    timer = setTimeout(() => {
                        this.maxHeight = document.getElementById('content').clientHeight - 170
                        clearTimeout(timer)
                        timer = null
                    }, 300)
                }
            },
            methods: {
                // 获取前台导航
                getHomeMenu() {
                    homeMenu().then(res => {
                        if (res.data.status === 200) {
                            this.menuList = res.data.data.menu
                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                    })
                },
                // 获取后台导航
                getAdminMenu() {
                    adminMenu().then(res => {
                        if (res.data.status === 200) {
                            this.menuList = res.data.data.menu
                        }
                    }).catch(error => {
                        this.$message.error(error.data.msg)
                    })
                },
                // 前后台导航切换
                menuChange(value) {
                    console.log(value);
                    if (value == 1) {
                        // 获取后台导航
                        this.getAdminMenu()
                    }
                    if (value == 2) {
                        // 获取前台导航
                        this.getHomeMenu()
                    }
                },
                onDragSort(){
                    
                }

            },
            created() {
                // 默认拉取后台菜单
                this.getAdminMenu()
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);

