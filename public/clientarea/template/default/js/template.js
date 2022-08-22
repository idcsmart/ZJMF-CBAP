(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('template')[0]
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                asideMenu,
                topMenu,
                pagination,
                captchaDialog
            },
            mounted() {
                // 关闭loading
                document.getElementById('mainLoading').style.display = 'none';
            },
            data() {
                return {
                    isShowCaptcha: false, //是否显示验证码弹窗
                    params: {
                        page: 1,
                        limit: 20,
                        pageSizes: [20, 50, 100],
                        total: 200,
                        orderby: 'id',
                        sort: 'desc',
                        keywords: '',
                    },
                }
            },
            methods: {
                // 切换分页
                sizeChange(e) {
                    this.params.limit = e
                    console.log(this.params);
                },
                currentChange(e) {
                    this.params.page = e
                    console.log(this.params);
                },
                // 验证码弹窗 验证成功后返回的数据
                getData(e){
                    console.log(e);
                }
            },
            created() {

            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
