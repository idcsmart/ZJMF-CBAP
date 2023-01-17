(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementById('content')
        Vue.prototype.lang = window.lang
        Vue.prototype.moment = window.moment
        new Vue({
            components: {
                topMenu,
                asideMenu
            },
            created() {
                let url = window.location.href
                let getqyinfo = url.split('?')[1]
                let getqys = new URLSearchParams('?' + getqyinfo)
                this.id = getqys.get('id')
                this.getCommontData()
                this.getDeveloperDetail()

            },
            mounted() {
            },
            data() {
                return {
                    commontData: {},
                    id: '',
                    developerData: {},
                    app_type_list: [],
                    select_index: 0,
                    app_list: [],
                    appData: {}
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
                // 切换应用分类
                typeChange(item, index) {
                    this.select_index = index
                    this.app_list = this.appData[item.key]
                },
                // 应用点击
                itemClick(id) {
                    location.href = `shop_detail.html?id=${id}&clientId=${this.id}`
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
                getDeveloperDetail() {
                    developerDetail(this.id).then((res) => {
                        this.developerData = res.data.data.developer
                        this.appData = res.data.data.developer.app
                        const key = Object.keys(res.data.data.developer.app)
                        const val = Object.values(res.data.data.developer.app)
                        const obj = {
                            addon: '插件',
                            captcha: '验证码接口',
                            certification: '实名接口',
                            gateway: '支付接口',
                            mail: '邮件接口',
                            sms: '短信接口',
                            server: '模块',
                            template: '主题',
                            service: '服务'
                        }
                        this.app_type_list = key.map((item, index) => {
                            return {
                                key: item,
                                type_text: obj[item],
                                num: val[index].length
                            }
                        })
                        this.typeChange(this.app_type_list[0], 0)
                    })
                }
            }

        }).$mount(template)

        const mainLoading = document.getElementById('mainLoading')
        setTimeout(() => {
            mainLoading && (mainLoading.style.display = 'none')
        }, 200)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
