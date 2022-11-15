(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('template')[0]
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                asideMenu,
                topMenu,
            },
            created() {
                this.rzType = location.href.split('?')[1].split('=')[1]
                this.getCommonData()
            },
            mounted() {
                this.getCertificationAuth()
            },
            updated() {
                // 关闭loading
                document.getElementById('mainLoading').style.display = 'none';
                document.getElementsByClassName('template')[0].style.display = 'block'
            },
            destroyed() {
                clearInterval(this.timer1)
                this.timer1 = null
            },
            data() {
                return {
                    commonData: {},
                    timer1: null,
                    contentBox: null,
                    rzType: '' // 1 个人   2 企业
                }
            },
            filters: {
                formateTime(time) {
                    if (time && time !== 0) {
                        return formateDate(time * 1000)
                    } else {
                        return "--"
                    }
                }
            },
            methods: {
                // 返回按钮
                backTicket() {
                    location.href = '/account.html'
                },
                goSelect() {
                    location.href = 'authentication_select.html'
                },
                // 获取基础信息
                getCertificationInfo() {
                    certificationInfo().then((res) => {
                        this.certificationInfoObj = res.data.data
                    })
                },
                // 获取状态
                grtCertificationStatus() {
                    certificationStatus().then((res) => {
                        if (res.data.status === 400) {
                            clearInterval(this.timer1)
                            this.timer1 = null
                            location.href = 'authentication_select.html'
                        }
                        if (res.data.status === 200) {
                            if (!(res.data.data.code == 2 && res.data.data.refresh == 0)) {
                                console.log('需要清除');
                                clearInterval(this.timer1)
                                this.timer1 = null
                                location.href = `authentication_status.html?type=${this.rzType}`
                            }
                        }

                    })
                },
                getCertificationAuth() {
                    certificationAuth().then((res) => {
                        if (res.data.status === 400) {
                            if (res.data.data.code === 10000) {
                                location.href = 'authentication_select.html'
                            } else if (res.data.data.code === 10001) {
                                location.href = `authentication_status.html?type=${this.rzType}`
                            }
                        } else if (res.data.status === 200) {
                            this.contentBox = res.data.data.html
                            $("#third-box").html(this.contentBox)
                            setTimeout((() => {
                                this.timer1 = setInterval(() => {
                                    this.grtCertificationStatus()
                                }, 1000)
                            }), 2000)
                        }
                    })
                },


                // 获取通用配置
                getCommonData() {
                    getCommon().then(res => {
                        if (res.data.status === 200) {
                            this.commonData = res.data.data
                            localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                            document.title = this.commonData.website_name + '-实名认证'
                        }
                    })
                }
            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
