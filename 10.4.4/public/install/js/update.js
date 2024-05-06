(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('update')[0]
        Vue.prototype.lang = window.lang
        Vue.prototype.moment = window.moment
        new Vue({
            data() {
                return {
                    activeId: 1,
                    isBegin: true,
                    isUp: true,
                    menu: [
                        {
                            id: 1,
                            icon: `${url}/img/iu/u-menu1.png`,
                            text: "更新须知"
                        },
                        {
                            id: 2,
                            icon: `${url}/img/iu/u-menu2.png`,
                            text: "覆盖文件"
                        },
                        {
                            id: 3,
                            icon: `${url}/img/iu/u-menu3.png`,
                            text: "数据库更新"
                        }, {
                            id: 4,
                            icon: `${url}/img/iu/u-menu4.png`,
                            text: "执行文件"
                        }, {
                            id: 5,
                            icon: `${url}/img/iu/u-menu5.png`,
                            text: "升级完成"
                        }
                    ],
                    versionData: JSON.parse(localStorage.getItem('systemData')),
                    contentData: JSON.parse(localStorage.getItem('updateContent')),
                    timer: null,
                    updateData: {
                        msg: '覆盖文件',
                        progress: '0.00%'
                    },
                    isUpNow: false,
                    loginUrl: ""
                }
            },
            computed: {

            },
            mounted() {

            },
            destroyed() {
                clearInterval(this.timer)
            },
            created() {
                // this.getContent()
                // this.getVersion()
                // this.doUpgrage()
                // 从localhost 获取更新须知 版本信息
            },
            methods: {
                begin() {
                    this.isUp = true
                    this.isUpNow = true
                    this.doUpgrage()
                },
                goDivBootm() {
                    const detailDom = document.querySelector('.down-text')
                    if (detailDom) {
                        detailDom.scrollTop = detailDom.scrollHeight
                    }
                },

                // 执行升级
                doUpgrage() {
                    this.activeId = 2
                    this.isUp = false
                    update().then(res => {
                        if (res.data.status === 200) {
                        }
                    }).catch(error => {
                        this.activeId = 0
                        this.isBegin = false
                        clearInterval(this.timer)
                        this.versionData.last_version_check = error.data.msg
                        // if (error.data.msg == '没有可用的安装包') {
                        //     this.activeId = 0
                        //     this.isBegin = false
                        //     clearInterval(this.timer)
                        //     this.versionData.last_version_check = "没有可升级的更新包，请前往后台检查更新"
                        // }
                        // if (error.data.msg == '没有可用的更新包') {
                        //     this.activeId = 0
                        //     this.isBegin = false
                        //     clearInterval(this.timer)
                        //     this.versionData.last_version_check = "没有可升级的更新包，请前往后台检查更新"
                        // }
                        // if (error.data.msg == '安装包MD5错误') {
                        //     this.activeId = 0
                        //     this.isBegin = false
                        //     clearInterval(this.timer)
                        //     this.versionData.last_version_check = "安装包MD5错误"
                        // }

                        // if (error.data.msg == '更新包MD5错误，请重新下载') {
                        //     this.activeId = 0
                        //     this.isBegin = false
                        //     clearInterval(this.timer)
                        //     this.versionData.last_version_check = "安装包MD5错误"
                        // }

                        this.$message.error(error.data.msg)
                    })
                    // 轮询升级进度
                    if (this.timer) {
                        clearInterval(this.timer)
                    }
                    this.timer = setInterval(() => {
                        progress().then(res => {
                            if (res.data.status === 200) {
                                this.updateData = res.data
                                console.log(this.updateData.msg);
                                console.log(this.updateData.progress);
                                this.goDivBootm()
                                if (this.updateData.progress == '70%') {
                                    console.log("70.00% active3");
                                    // 开始数据库更新
                                    this.activeId = 3
                                }
                                if (this.updateData.progress == '80%') {
                                    console.log("80.00% active4");
                                    // 开始执行文件
                                    this.activeId = 4
                                }

                                if (this.updateData.msg == '') {
                                    clearInterval(this.timer)
                                    this.activeId = 5
                                }


                                if (this.updateData.msg == '更新成功') {
                                    if (this.versionData.last_version_check) {
                                        return false
                                    }
                                    this.updateData = res.data.data
                                    clearInterval(this.timer)
                                    this.activeId = 5
                                }

                            }
                        }).catch((error) => {
                            this.activeId = 0
                            this.isBegin = false
                            clearInterval(this.timer)
                            this.versionData.last_version_check = error.data.msg
                            this.$message.error("安装失败！")
                        })
                    }, 2000)
                },
                // 立即升级 
                upNow() {
                    this.doUpgrage()
                    this.isUpNow = true
                },
                toAdmin() {
                    sessionStorage.removeItem('isCanUpdata')
                    location.href = this.updateData.url + '/login.htm'
                },
                goBack() {
                    // this.getVersion()
                    this.activeId = 1
                    this.isBegin = true
                }
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
