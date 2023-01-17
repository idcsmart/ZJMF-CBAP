(function (window, undefined) {
    var old_onload = window.onload;
    window.onload = function () {
        const content = document.getElementById('content')

        /* aside head */
        content && new Vue({
            data: {
                keyWords: "",
                asideMenuActiveId: localStorage.getItem('asideMenuActiveId')
            },
            computed: {

            },
            mounted() {

            },
            created() {

            },
            methods: {
                asideMenuSelect(e) {
                    localStorage.setItem('asideMenuActiveId', e)
                    console.log(e);
                    location.href = e
                }
            },
            watch: {

            }
        }).$mount(content)


        const mainLoading = document.getElementById('mainLoading')
        setTimeout(() => {
            mainLoading && (mainLoading.style.display = 'none')
        }, 200)

        typeof old_onload == 'function' && old_onload()
    };
})(window);
