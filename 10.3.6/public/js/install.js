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
                    activeId: 1,
                    // activeId: 3,
                    btnLoading: false,
                    menu: [
                        {
                            id: 1,
                            icon: `/img/iu/iu-welcome.svg`,
                            text: "欢迎"
                        },
                        {
                            id: 2,
                            icon: `/img/iu/iu-common.svg`,
                            text: "环境检查"
                        },
                        {
                            id: 3,
                            icon: `/img/iu/iu-db.svg`,
                            text: "配置数据库"
                        }, {
                            id: 4,
                            icon: `/img/iu/iu-config.svg`,
                            text: "配置信息"
                        }, {
                            id: 5,
                            icon: `/img/iu/iu-finish.svg`,
                            text: "安装完成"
                        }
                    ],
                    baseData: {
                        envs: [],
                        modules: [],
                        error: 0
                    },
                    columns: [
                        {
                            minWidth: 200,
                            colKey: 'name',
                            title: '名称',
                            ellipsis: true
                        },
                        {
                            width: 150,
                            colKey: 'suggest',
                            title: '建议',
                            ellipsis: true
                        },
                        {
                            width: 150,
                            colKey: 'current',
                            title: '当前',
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
                        username: "",
                        password: "",
                        hostport: "3306"
                    },
                    dbRules: {
                        hostname: [
                            { required: true, message: '请输入数据库地址 ', type: 'error' },
                        ],
                        dbname: [
                            { required: true, message: '请输入数据库名称', type: 'error' },
                        ],
                        username: [
                            { required: true, message: '请输入用户名', type: 'error' },
                        ],
                        password: [
                            { required: true, message: '请输入密码', type: 'error' },
                        ],
                        hostport: [
                            { required: true, message: '请输入数据库端口', type: 'error' },
                        ],
                    },
                    configData: {
                        sitename: "智简魔方V10业务管理系统",
                        username: "",
                        password: "",
                        email: "admin@domain.com",
                        license: "",
                    },
                    configRules: {
                        sitename: [
                            { required: true, message: '请输入站点标题', type: 'error' },
                        ],
                        username: [
                            { required: true, message: '请输入管理员用户名', type: 'error' },
                            {
                                validator: (val) => val.length >= 8 && val.length <= 50,
                                message: '请输入8到50位用户名',
                                type: 'error',
                            },
                            {
                                pattern: /^[a-z][a-z0-9]*$/, message: '用户名应小写字母开头且只含有数字和小写字母', type: 'error'
                            },
                        ],
                        password: [
                            { required: true, message: '请输入管理员密码', type: 'error' },
                            {
                                validator: (val) => val.length >= 8 && val.length <= 32,
                                message: '请输入8到32位密码',
                                type: 'error',
                            }
                        ],
                        email: [
                            { required: true, message: '请输入邮箱', type: 'error' },
                        ],
                    },
                    finalData: {
                        admin_url: "",
                        admin_name: "",
                        admin_pass: ""
                    },
                    reCheckLoading: false,
                    dbLoading: false,
                }
            },
            computed: {

            },
            mounted() {

            },
            watch: {
                activeId(newValue, oldValue) {
                    if (newValue != 0) {
                        sessionStorage.setItem('installActiveId', newValue)
                    } else {
                        sessionStorage.setItem('installActiveId', 1)
                    }
                }
            },
            created() {
                this.autoPass()
                this.autoName()

                this.activeId = sessionStorage.getItem('installActiveId') ? sessionStorage.getItem('installActiveId') : 1
                this.activeIdInit()
            },
            methods: {
                // 欢迎页 立即开始
                begin() {
                    // 检测环境
                    this.doStep1()
                },
                // 环境检查
                doStep1() {
                    this.reCheckLoading = true

                    step_1().then(res => {
                        this.reCheckLoading = false
                        if (res.data.status === 200) {
                            this.activeId = 2
                            if (res.data.data) {
                                this.baseData = res.data.data
                                this.baseData.envs.map(item => {
                                    this.baseData.modules.push(item)
                                })
                            }
                        }
                        installAxios.get(`	/console/v1/country`).then(res => {

                        }).catch(error => {
                            if (error.response.status == 404) {
                                this.baseData.modules.push(
                                    {
                                        status: 0,
                                        current: "未开启",
                                        name: "伪静态",
                                        suggest: "开启",
                                        worst: "开启"
                                    }
                                )
                            }
                        })

                    }).catch((error) => {
                        this.reCheckLoading = false
                        this.activeId = 0
                        this.$message.error(error.data.msg)
                        installAxios.get(`/console/v1/country`).then(res => {

                        }).catch(error => {
                            console.log("error", error);
                        })
                    })
                },
                // 数据库检查提交
                dbSubmit({ validateResult, firstError }) {
                    if (validateResult === true) {
                        this.dbLoading = true
                        const params = { ...this.dbData }
                        step_2(params).then(res => {
                            this.dbLoading = false
                            if (res.data.status === 200) {
                                this.activeId = 4
                            }
                            if (!res.data) {
                                this.$message.error("数据库连接失败！")
                            }
                        }).catch(error => {
                            this.dbLoading = false
                            this.$message.error(error.data.msg)
                        })
                    } else {
                        this.$message.warning(firstError);
                    }
                },
                toLearn() {
                    window.open('https://www.idcsmart.com/wiki_list/915.html', '_blank');
                },
                // 配置信息 提交
                configSubmit({ validateResult, firstError }) {
                    if (validateResult === true) {
                        this.btnLoading = true
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
                                                            this.btnLoading = false
                                                            if (res.data.status === 200) {
                                                                this.finalData = res.data.data
                                                            }
                                                        }).catch(error => {
                                                            this.btnLoading = false
                                                            this.$message.error(error.data.msg)
                                                        })
                                                    }
                                                }).catch(error => {
                                                    this.btnLoading = false
                                                    this.$message.error(error.data.msg)
                                                })
                                            }
                                        }).catch(error => {
                                            this.btnLoading = false
                                            this.$message.error(error.data.msg)
                                        })
                                    }
                                }).catch(error => {
                                    this.btnLoading = false
                                    this.$message.error(error.data.msg)
                                })

                            }
                        }).catch(error => {
                            this.btnLoading = false
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
                    this.configData.password = this.genEnCode(11, 1, 1, 0, 0, 1)
                },
                autoName() {
                    const first = this.randomCoding(1)
                    this.configData.username = first.toLowerCase() + this.genEnCode(6, 1, 1, 0, 0, 1)
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
                    if (hasNum == 0 && hasChar == 0 && hasSymbol == 0) return m
                    for (var i = length; i >= 0; i--) {
                        var num = Math.floor((Math.random() * 94) + 33)
                        if (
                            (
                                (hasNum == 0) && ((num >= 48) && (num <= 57))
                            ) || (
                                (hasChar == 0) && ((
                                    (num >= 65) && (num <= 90)
                                ) || (
                                        (num >= 97) && (num <= 122)
                                    ))
                            ) || (
                                (hasSymbol == 0) && ((
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
                    if (caseSense == '0') {
                        m = (lowerCase == '0') ? m.toUpperCase() : m.toLowerCase()
                    }
                    return m
                },
                randomCoding(n) {
                    //创建26个字母数组
                    var arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
                    var idvalue = '';
                    for (var i = 0; i < n; i++) {
                        idvalue += arr[Math.floor(Math.random() * 26)];
                    }
                    return idvalue;
                },
                activeIdInit() {
                    switch (Number(this.activeId)) {
                        case 1:
                            break;
                        case 2:
                            this.begin()
                            break;
                        case 3:
                            break;
                        case 4:
                            break;
                        case 5:
                            break;
                        default:
                            break;
                    }
                },
                // 复制
                copyText(text) {
                    if (navigator.clipboard && window.isSecureContext) {
                        // navigator clipboard 向剪贴板写文本
                        this.$message.success("复制成功");
                        return navigator.clipboard.writeText(text);
                    } else {
                        // 创建text area
                        const textArea = document.createElement("textarea");
                        textArea.value = text;
                        // 使text area不在viewport，同时设置不可见
                        document.body.appendChild(textArea);
                        textArea.focus();
                        textArea.select();
                        this.$message.success("复制成功");
                        return new Promise((res, rej) => {
                            // 执行复制命令并移除文本框
                            document.execCommand("copy") ? res() : rej();
                            textArea.remove();
                        });
                    }
                },
            },
        }).$mount(template)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
