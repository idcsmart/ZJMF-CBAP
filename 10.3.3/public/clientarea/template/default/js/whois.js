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
                this.getCommonData()
                this.getWhoisInfo()
            },
            mounted() {
            },
            updated() {
                // 关闭loading
                document.getElementById('mainLoading').style.display = 'none';
                document.getElementsByClassName('template')[0].style.display = 'block'
            },
            destroyed() {
            },
            data() {
                return {
                    commonData: {},
                    whoisInfo: {},
                    domainName: window.location.href.split("?")[1].split("=")[1]
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
                // 获取 whois 信息
                getWhoisInfo() {
                    let params = {
                        domain: this.domainName
                    }
                    domainWhois(params).then(res => {
                        if (res.data.status == 200) {
                            this.whoisInfo = res.data.data
                            console.log(this.whoisInfo);
                        }
                    }).catch(err => {
                        this.$message.error(err.data.msg)
                    })
                },

                // 获取通用配置
                getCommonData() {
                    this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
                    document.title = this.commonData.website_name + '-' + lang.template_text155
                },
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
