(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('template')[0]
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                asideMenu,
                topMenu,
                cloudTop,
            },
            created() {
                // 获取产品id
                this.id = location.href.split('?')[1].split('=')[1]
            },
            mounted() {
            },
            updated() {

            },
            destroyed() {

            },
            data() {
                return {
                    commonData: {},
                    id: null
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

            },
            methods: {

            },

        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
