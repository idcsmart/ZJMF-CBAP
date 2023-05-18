(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementById('content')
        Vue.prototype.lang = window.lang
        Vue.prototype.moment = window.moment
        new Vue({
            components: {
                topMenu,
                asideMenu,
            },
            created() {
                this.getCommontData()
                this.getMarketIndex()
            },
            data() {
                return {
                    commontData: {},
                    appData: {},
                    banner: [],
                    loading: false,
                    isCard: false,
                }
            },
            filters: {

            },
            methods: {
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
                getMarketIndex() {
                    this.loading = true
                    marketIndex().then(res => {
                        this.loading = false
                        if (res.data.status == 200) {
                            this.appData = res.data.data.app
                            this.banner = res.data.data.banner
                            this.isCard = this.banner.length >= 3
                        }
                    }).catch(error => {
                        this.loading = false
                    })
                },
                // 应用点击
                itemClick(id, clientId) {
                    location.href = `shop_detail.html?id=${id}&clientId=${clientId}`
                },
                // 查看全部
                toSeeAll(type) {
                    location.href = `shop_app.html?appType=${type}`
                }
            },

        }).$mount(template)

        const mainLoading = document.getElementById('mainLoading')
        setTimeout(() => {
            mainLoading && (mainLoading.style.display = 'none')
        }, 200)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
