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
                let url = window.location.href
                let getqyinfo = url.split('?')[1]
                let getqys = new URLSearchParams('?' + getqyinfo)
                this.params.keyWords = getqys.get('keyWords')

                this.getCommontData()
                this.getAppList()
            },
            data() {
                return {
                    commontData: {},
                    params: {
                        keyWords: ""
                    },
                    loading: false,
                    dataList: {},
                    activeType: "addon"
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
                goBack() {
                    history.back()
                },
                getAppList() {
                    this.loading = true
                    marketIndex(this.params).then(res => {
                        this.loading = false
                        if (res.data.status == 200) {
                            this.dataList = res.data.data.app
                        }
                    }).catch((error) => {
                        this.loading = false
                    })
                },
                // 应用点击
                itemClick(id, clientId) {
                    location.href = `shop_detail.html?id=${id}&clientId=${clientId}`
                },
                appTypeChange(type) {
                    this.activeType = type
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
