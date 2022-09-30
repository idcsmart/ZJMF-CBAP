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
                this.getHelpList()
                this.getIdInUrl()
            },
            mounted() {

            },
            updated() {
                document.getElementById('mainLoading').style.display = 'none';
                document.getElementsByClassName('template')[0].style.display = 'block'
            },
            destroyed() {

            },
            data() {
                return {
                    commonData: {},
                    helpList: [],
                    // 目录激活项
                    activeId: "0",
                    detailData: {},
                    helpId: 0,
                    contentLoading: false
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
                // 获取通用配置
                getCommonData() {
                    getCommon().then(res => {
                        if (res.data.status === 200) {
                            this.commonData = res.data.data
                            localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                            document.title = this.commonData.website_name + '-帮助中心'
                        }
                    })
                },
                // 去帮助中心首页
                toHelpIndex() {
                    location.href = `help.html`
                },
                // 获取帮助文档列表
                getHelpList() {
                    const params = {
                        keywords: ""
                    }
                    helpList(params).then(res => {
                        if (res.data.status === 200) {
                            this.helpList = res.data.data.list
                        }
                    })
                },
                // 目录展开
                handleOpen(key, keyPath) {
                    console.log(key, keyPath);
                },
                // 目录关闭
                handleClose(key, keyPath) {
                    console.log(key, keyPath);
                },
                // 菜单项点击
                itemClick(id) {
                    this.contentLoading = true
                    // 获取帮助文档详情
                    const params = {
                        id
                    }
                    helpDetails(params).then(res => {
                        if (res.data.status === 200) {
                            this.detailData = res.data.data.help
                        }
                        this.contentLoading = false
                    })
                },
                // 附件下载
                downloadfile(url) {
                    const downloadElement = document.createElement("a");
                    downloadElement.href = url;
                    downloadElement.download = url.split("^")[1]; // 下载后文件名
                    document.body.appendChild(downloadElement);
                    downloadElement.click(); // 点击下载
                },
                getIdInUrl() {
                    let url = window.location.href
                    let getqyinfo = url.split('?')[1]
                    let getqys = new URLSearchParams('?' + getqyinfo)
                    let id = getqys.get('id')
                    this.activeId = id
                    if (id) {
                        this.itemClick(id)
                    }
                }
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
