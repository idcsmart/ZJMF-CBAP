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
                payDialog
            },
            created() {
                const url = window.location.href
                const getqyinfo = url.split('?')[1]
                const getqys = new URLSearchParams('?' + getqyinfo)
                this.id = getqys.get('id')
                this.type = getqys.get('type')
                this.getCommontData()
                this.getDetail()
            },
            mounted() {
            },
            data() {
                return {
                    commontData: {},
                    id: '',
                    type: '',
                    payLoading: false,
                    loading: false,
                    appData: {
                        developer: {}
                    },
                    customfield: {},
                    marketAuthorize: {}
                }
            },
            filters: {
                formateTime(time) {
                    if (time && time !== 0) {
                        var date = new Date(time * 1000);
                        Y = date.getFullYear() + '/';
                        M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '/';
                        D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' ';
                        return (Y + M + D);
                    } else {
                        return "--"
                    }
                }
            },
            methods: {
                // 跳转函数
                go(url, params) {
                    if (!params) {
                        location.href = url
                    } else {

                    }
                },
                goBack() {
                    history.back()
                },
                authorizechange(obj) {
                    this.marketAuthorize = obj
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
                handelPay() {
                    this.payLoading = true
                    buyApp({ id: this.id, host_id: this.marketAuthorize.id, pay_type: this.type, customfield: this.customfield }).then((res) => {
                        this.payLoading = false
                        this.$refs.payDialog.showPayDialog(res.data.data.order_id, res.data.data.amount)
                    }).catch((err) => {
                        this.payLoading = false
                        this.$message.error(err.data.msg)
                    })

                },
                getDetail() {
                    this.loading = true
                    const params = {
                        id: this.id
                    }
                    appDetails(params).then(res => {
                        this.loading = false
                        if (res.data.status == 200) {
                            this.appData = res.data.data.app
                        }
                    }).catch(error => {
                        this.loading = false
                    })
                },
                // 支付成功回调
                paySuccess(e) {
                    location.href = `shop_detail.html?id=${this.id}&clientId=${this.appData.client_id}`
                },
                // 取消支付回调
                payCancel(e) {
                    location.href = `shop_client.html?activeName=3`
                },
            }

        }).$mount(template)

        const mainLoading = document.getElementById('mainLoading')
        setTimeout(() => {
            mainLoading && (mainLoading.style.display = 'none')
        }, 200)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
