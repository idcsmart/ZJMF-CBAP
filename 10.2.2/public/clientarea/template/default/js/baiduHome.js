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
            },
            mounted() {

            },
            updated() {
                // // 关闭loading
                // document.getElementById('mainLoading').style.display = 'none';
                // document.getElementsByClassName('template')[0].style.display = 'block'
            },
            destroyed() {

            },
            data() {
                return {
                    tableData: []

                }
            },
            filters: {
                formateTime(time) {
                    if (time && time !== 0) {
                        return formateDate(time * 1000)
                    } else {
                        return "--"
                    }
                },
                formareDay(time) {
                    if (time && time !== 0) {
                        const dataTime = formateDate(time * 1000)
                        return dataTime.split(' ')[0].split('-')[1] + '-' + dataTime.split(' ')[0].split('-')[2]
                    } else {
                        return "--"
                    }
                }
            },
            methods: {
                // 获取通用配置
                getCommonData() {
                    this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
                    document.title = this.commonData.website_name + '-首页'

                },

            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
