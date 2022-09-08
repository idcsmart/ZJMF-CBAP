(function (window, undefined) {
    var old_onload = window.onload
    window.onload = function () {
        const login = document.getElementById('regist')
        Vue.prototype.lang = window.lang
        new Vue({
            components: {
                countDownButton,
                captchaDialog
            },
            data() {
                return {
                    isShowCaptcha: false, //登录是否需要验证码
                    isEmailOrPhone: true,  // true:电子邮件 false:手机号
                    isPassOrCode: true,  // true:密码登录 false:验证码登录
                    errorText: "",
                    checked: false, // 是否勾选阅读并同意
                    formData: {
                        email: "",
                        phone: "",
                        username: "",
                        password: "",
                        repassword: "",
                        phoneCode: "",
                        emailCode: "",
                        countryCode: 86,
                    },
                    token: "",
                    captcha: '',
                    isCaptcha: 0, // 
                    countryList: [],
                    commonData: {},
                }
            },
            created() {
                this.getCountryList()
                this.getCommonSetting()
            },
            updated() {
                // 关闭loading
                document.getElementById('mainLoading').style.display = 'none';
                document.getElementsByClassName('template')[0].style.display = 'block'
            },
            watch: {

            },
            methods: {
                getData(e) {
                    console.log(e);
                    this.token = e.token
                    this.captcha = e.captchaCode
                    this.isShowCaptcha = false
                    this.doRegist()
                },
                async getCaptcha() {
                    try {
                        const res = await getCaptcha()
                        const temp = res.data.data
                        this.formData.token = temp.token
                        this.captcha = temp.captcha
                    } catch (error) {
                    }
                },
                // 获取通用配置
                async getCommonSetting() {
                    try {
                        const res = await getCommon()
                        console.log(res);
                        this.commonData = res.data.data

                        if (this.commonData.register_phone == 1) {
                            this.isEmailOrPhone = false
                        }

                        if (this.commonData.register_email == 1) {
                            this.isEmailOrPhone = true
                        }
                        document.title = this.commonData.website_name + '-忘记密码'
                        localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
                    } catch (error) {

                    }
                },
                // 前往协议
                toRead() {
                    const url = this.commonData.terms_service_url
                    location.href = url
                },
                // 注册
                doRegist() {
                    let isPass = true;
                    console.log(this.formData);
                    const form = { ...this.formData };
                    // 邮件登录验证
                    if (this.isEmailOrPhone) {
                        if (!form.email) {
                            isPass = false
                            this.errorText = "请输入邮箱"
                        } else if (
                            form.email.search(
                                /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/
                            ) === -1
                        ) {
                            isPass = false
                            this.errorText = "邮箱格式不正确"
                        }

                        if (this.commonData.code_client_email_register == 1) {
                            if (!form.emailCode) {
                                isPass = false
                                this.errorText = "请输入邮箱验证码"
                            } else {
                                if (form.emailCode.length !== 6) {
                                    isPass = false
                                    this.errorText = "邮箱验证码应为6位"
                                }
                            }
                        }


                    }

                    // 手机号码登录 验证
                    if (!this.isEmailOrPhone) {
                        if (!form.phone) {
                            isPass = false
                            this.errorText = "请输入手机号码"
                        } else {
                            // 设置正则表达式的手机号码格式 规则 ^起点 $终点 1第一位数是必为1  [3-9]第二位数可取3-9的数字  \d{9} 匹配9位数字
                            const reg = /^1[3-9]\d{9}$/;
                            if (!reg.test(form.phone)) {
                                isPass = false
                                this.errorText = "请输入正确的手机号"
                            }
                        }

                        if (!form.phoneCode) {
                            isPass = false
                            this.errorText = "请输入手机验证码"
                        } else {
                            if (form.phoneCode.length !== 6) {
                                isPass = false
                                this.errorText = "手机验证码应为6位"
                            }
                        }
                    }

                    if (!form.password) {
                        isPass = false
                        this.errorText = "请输入密码"
                    } else if (form.password.length > 32 || form.password.length < 6) {
                        isPass = false
                        this.errorText = "密码应该在6~32位"
                    }
                    if (!form.repassword) {
                        isPass = false
                        this.errorText = "请再次输入密码"
                    } else {
                        if (form.password !== form.repassword) {
                            isPass = false
                            this.errorText = "两次密码不一致"
                        }
                    }

                    if (!this.checked) {
                        isPass = false
                        this.errorText = "请勾选服务协议书！"
                    }


                    // 验证通过
                    if (isPass) {
                        this.errorText = ""
                        let code = "";
                        if (this.isEmailOrPhone) {
                            code = form.emailCode
                        } else {
                            code = form.phoneCode
                        }

                        const params = {
                            type: this.isEmailOrPhone ? "email" : "phone",
                            account: this.isEmailOrPhone ? form.email : form.phone,
                            phone_code: form.countryCode.toString(),
                            code,
                            username: "",
                            password: form.password,
                            re_password: form.repassword,
                        }

                        //调用注册接口
                        regist(params).then(res => {
                            if (res.data.status === 200) {
                                this.$message.success(res.data.msg);
                                // 存入 jwt
                                localStorage.setItem("jwt", res.data.data.jwt);
                                location.href = 'index.html'
                            }
                        }).catch(err => {
                            this.errorText = err.data.msg
                            // this.$message.error(err.data.msg);
                        })
                    }
                },
                // 获取国家列表
                getCountryList() {
                    getCountry({}).then(res => {
                        console.log(res);
                        if (res.data.status === 200) {
                            this.countryList = res.data.data.list
                        }
                    })
                },
                // 加密方法
                encrypt(str) {
                    const key = CryptoJS.enc.Utf8.parse("idcsmart.finance");
                    const iv = CryptoJS.enc.Utf8.parse("9311019310287172");
                    var encrypted = CryptoJS.AES.encrypt(str, key, {
                        mode: CryptoJS.mode.CBC,
                        padding: CryptoJS.pad.Pkcs7,
                        iv: iv,
                    }).toString();
                    return encrypted;
                },
                // 发送邮箱验证码
                sendEmailCode() {
                    let isPass = true
                    const form = this.formData
                    if (!form.email) {
                        isPass = false
                        this.errorText = "请输入邮箱"
                    } else if (
                        form.email.search(
                            /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/
                        ) === -1
                    ) {
                        isPass = false
                        this.errorText = "邮箱格式不正确"
                    }
                    if (isPass) {
                        this.errorText = ""
                        const params = {
                            action: "register",
                            email: form.email,
                            token: this.token,
                            captcha: this.captcha
                        }
                        emailCode(params).then(res => {
                            if (res.data.status === 200) {
                                // 执行倒计时
                                this.$refs.emailCodebtn.countDown()
                            }
                        }).catch(error => {
                            if (error.data.msg === "请输入图形验证码" || error.data.msg === "图形验证码错误") {
                                this.isShowCaptcha = true
                                this.$refs.captcha.doGetCaptcha()
                            } else {
                                this.errorText = err.data.msg
                            }

                            // this.$message.error(error.data.msg);
                        })
                    }
                },
                // 发送手机短信
                sendPhoneCode() {
                    let isPass = true
                    const form = this.formData
                    if (!form.phone) {
                        isPass = false
                        this.errorText = "请输入手机号码"
                    } else {
                        // 设置正则表达式的手机号码格式 规则 ^起点 $终点 1第一位数是必为1  [3-9]第二位数可取3-9的数字  \d{9} 匹配9位数字
                        const reg = /^1[3-9]\d{9}$/;
                        if (!reg.test(form.phone)) {
                            isPass = false
                            this.errorText = "请输入正确的手机号"
                        }
                    }

                    if (isPass) {
                        this.errorText = ""
                        const params = {
                            action: "register",
                            phone_code: form.countryCode,
                            phone: form.phone,
                            token: this.token,
                            captcha: this.captcha
                        }
                        phoneCode(params).then(res => {
                            if (res.data.status === 200) {
                                // 执行倒计时
                                this.$refs.phoneCodebtn.countDown()
                            }
                        }).catch(error => {
                            if (error.data.msg === "请输入图形验证码" || error.data.msg === "图形验证码错误") {
                                this.isShowCaptcha = true
                                this.$refs.captcha.doGetCaptcha()
                            } else {
                                this.errorText = error.data.msg
                            }

                        })
                    }
                },
                toLogin() {
                    location.href = 'login.html'
                }

            }
        }).$mount(login)
        typeof old_onload == 'function' && old_onload()
    };
})(window);
