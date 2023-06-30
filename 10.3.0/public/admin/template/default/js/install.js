(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const template = document.getElementsByClassName('installAndUpdate')[0]
        Vue.prototype.lang = window.lang
        Vue.prototype.moment = window.moment
        new Vue({
            data() {
                return {
                    maxHeight: 0,
                    activeId: 3,
                    loading: false,
                    menu: [
                        {
                            id: 1,
                            icon: `/img/iu/iu-welcome.png`,
                            text: lang.install_text1
                        },
                        {
                            id: 2,
                            icon: `/img/iu/iu-common.png`,
                            text: lang.install_text2
                        },
                        {
                            id: 3,
                            icon: `/img/iu/iu-db.png`,
                            text: lang.install_text3
                        }, {
                            id: 4,
                            icon: `/img/iu/iu-config.png`,
                            text: lang.install_text4
                        }, {
                            id: 5,
                            icon: `/img/iu/iu-finish.png`,
                            text: lang.install_text5
                        }
                    ],
                    baseData: {
                        "envs": [],
                        "modules": [],
                        "error": 0
                    },
                    columns: [
                        {
                            minWidth: 200,
                            colKey: 'name',
                            title: lang.install_text6,
                            ellipsis: true
                        },
                        {
                            width: 150,
                            colKey: 'suggest',
                            title: lang.install_text7,
                            ellipsis: true
                        },
                        {
                            width: 150,
                            colKey: 'current',
                            title: lang.install_text8,
                            ellipsis: true
                        },
                        {
                            width: 150,
                            colKey: 'status',
                            title: '',
                            ellipsis: true
                        },
                    ],
                    dbData: {
                        hostname: '127.0.0.1',
                        dbname: '',
                        username: '',
                        password: '',
                        hostport: ''
                    },
                    dbRules: {
                        hostname: [
                            { required: true, message: lang.install_text9, type: 'error' },
                            {
                                validator: (val) => val.length <= 50,
                                message: lang.install_text10,
                                type: 'error',
                            },
                        ],
                        dbname: [
                            { required: true, message: lang.install_text11, type: 'error' },
                            {
                                validator: (val) => val.length <= 50,
                                message: lang.install_text10,
                                type: 'error',
                            },
                        ],
                        username: [
                            { required: true, message: lang.install_text12, type: 'error' },
                            {
                                validator: (val) => val.length <= 50,
                                message: lang.install_text10,
                                type: 'error',
                            },
                        ],
                        password: [
                            { required: true, message: lang.install_text13, type: 'error' },
                            {
                                validator: (val) => val.length <= 50,
                                message: lang.install_text10,
                                type: 'error',
                            },
                        ],
                        hostport: [
                            { required: true, message: lang.install_text14, type: 'error' },
                            {
                                validator: (val) => val.length <= 50,
                                message: lang.install_text10,
                                type: 'error',
                            },
                        ],
                    },
                    configData: {
                        sitename: "",
                        username: "",
                        password: "",
                        email: ""

                    },
                    configRules: {
                        sitename: [
                            { required: true, message: lang.install_text15, type: 'error' },
                        ],
                        username: [
                            { required: true, message: lang.install_text16, type: 'error' },
                        ],
                        password: [
                            { required: true, message: lang.install_text17, type: 'error' },
                        ],
                        email: [
                            { required: true, message: lang.install_text18, type: 'error' },
                        ],
                    },
                    finalData: {
                        admin_url: "",
                        admin_name: "",
                        admin_pass: ""
                    }
                }
            },
            computed: {

            },
            mounted() {

            },
            created() {

            },
            methods: {
                // 欢迎页 立即开始
                bengin() {
                    // 检测环境
                    this.doStep1()
                },
                // 环境检查
                doStep1() {
                    step_1().then(res => {
                        if (res.data.status === 200) {
                            this.activeId = 2
                            if (res.data.data) {
                                this.baseData = res.data.data
                                this.baseData.envs.map(item => {
                                    this.baseData.modules.push(item)
                                })
                            }
                        }
                    }).catch((error) => {
                        this.activeId = 0
                        this.$message.error(error.data.msg)
                    })
                },
                // 数据库检查提交
                dbSubmit({ validateResult, firstError }) {
                    if (validateResult === true) {
                        const params = { ...this.dbData }
                        step_2(params).then(res => {
                            if (res.data.status === 200) {
                                this.activeId = 4
                            }
                        }).catch(error => {
                            this.$message.error(error.data.msg)
                        })
                    } else {
                        this.$message.warning(firstError);
                    }
                },
                // 查看教程
                toLearn() {

                },
                // 配置信息 提交
                configSubmit({ validateResult, firstError }) {
                    if (validateResult === true) {
                        const params = { ...this.configData }
                        step_3(params).then(res => {
                            if (res.data.status === 200) {
                                // this.activeId = 4
                                step_4().then(res => {
                                    if (res.data.status === 200) {
                                        step_5().then(res => {
                                            if (res.data.status === 200) {
                                                step_6().then(res => {
                                                    if (res.data.status === 200) {
                                                        this.activeId = 5
                                                        step_7().then(res => {
                                                            if (res.data.status === 200) {
                                                                this.finalData = res.data.data
                                                            }
                                                        }).catch(error => {
                                                            this.$message.error(error.data.msg)
                                                        })
                                                    }
                                                }).catch(error => {
                                                    this.$message.error(error.data.msg)
                                                })
                                            }
                                        }).catch(error => {
                                            this.$message.error(error.data.msg)
                                        })
                                    }
                                }).catch(error => {
                                    this.$message.error(error.data.msg)
                                })

                            }
                        }).catch(error => {
                            this.$message.error(error.data.msg)
                        })
                    } else {
                        this.$message.warning(firstError);
                    }
                },
                toBack() {
                    // console.log(this.finalData.url);
                    location.href = this.finalData.admin_url
                },
                // 自动生成密码
                autoPass() {
                    this.configData.password = this.genEnCode(5, 1, 1, 1, 1, 0)
                    console.log(this.configData);
                },
                /**
                * 生成密码字符串
                * 33~47：!~/
                * 48~57：0~9
                * 58~64：:~@
                * 65~90：A~Z
                * 91~96：[~`
                * 97~122：a~z
                * 123~127：{~
                * @param length 长度  生成的长度是length + 2
                * @param hasNum 是否包含数字 1-包含 0-不包含
                * @param hasChar 是否包含字母 1-包含 0-不包含
                * @param hasSymbol 是否包含其他符号 1-包含 0-不包含
                * @param caseSense 是否大小写敏感 1-敏感 0-不敏感
                * @param lowerCase 是否只需要小写，只有当hasChar为0且caseSense为1时起作用 1-全部小写 0-全部大写
                */
                genEnCode(length, hasNum, hasChar, hasSymbol, caseSense, lowerCase) {
                    var m = ''
                    if (hasNum === 0 && hasChar === 0 && hasSymbol === 0) return m
                    for (var i = length; i >= 0; i--) {
                        var num = Math.floor((Math.random() * 94) + 33)
                        if (
                            (
                                (hasNum === 0) && ((num >= 48) && (num <= 57))
                            ) || (
                                (hasChar === 0) && ((
                                    (num >= 65) && (num <= 90)
                                ) || (
                                        (num >= 97) && (num <= 122)
                                    ))
                            ) || (
                                (hasSymbol === 0) && ((
                                    (num >= 33) && (num <= 47)
                                ) || (
                                        (num >= 58) && (num <= 64)
                                    ) || (
                                        (num >= 91) && (num <= 96)
                                    ) || (
                                        (num >= 123) && (num <= 127)
                                    ))
                            )
                        ) {
                            i++
                            continue
                        }
                        m += String.fromCharCode(num)
                    }
                    if (caseSense === '0') {
                        m = (lowerCase === '0') ? m.toUpperCase() : m.toLowerCase()
                    }
                    return m
                }
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
