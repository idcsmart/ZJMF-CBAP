// 验证码通过
function captchaCheckSuccsss(bol, captcha, token) {
    if (bol) {
        // 验证码验证通过
        getData(captcha, token)
    }
};
// 取消验证码验证
function captchaCheckCancel() {
    captchaCancel()
};
const loginDialog = {
    template:
        `
            <div>
                <!-- 验证码 -->
                <captcha-dialog :is-show-captcha="isShowCaptcha" ref="captcha"></captcha-dialog>
                <el-dialog width="16.95rem" custom-class='login-dialog' :visible.sync="visible" :show-close=true>
                    <div class="login-left">
                        <div class="login-text">
                            <div class="login-text-title">{{lang.login}}</div>
                            <div class="login-text-regist" v-if="commonData.register_email == 1 || commonData.register_phone == 1">
                                {{lang.login_no_account}}<a @click="toRegist">{{lang.login_regist_text}}</a>
                            </div>
                        </div>
                        <div class="login-form">
                            <div class="login-top">
                                <div v-show="isPassOrCode" class="login-email" :class="isEmailOrPhone? 'active':null" @click="isEmailOrPhone = true">{{lang.login_email}}
                                </div>
                                <div class="login-phone" :class="!isEmailOrPhone? 'active':null" @click="isEmailOrPhone = false">{{lang.login_phone}}
                                </div>
                            </div>
                            <div class="form-main">
                                <div class="form-item">
                                    <el-input v-if="isEmailOrPhone" v-model="formData.email" :placeholder="lang.login_placeholder_pre + lang.login_email"></el-input>
                                    <el-input v-else class="input-with-select select-input" v-model="formData.phone" :placeholder="lang.login_placeholder_pre + lang.login_phone">
                                        <el-select filterable slot="prepend" v-model="formData.countryCode">
                                            <el-option v-for="item in countryList" :key="item.name" :value="item.phone_code" :label="item.name_zh + '+' + item.phone_code"></el-option>
                                        </el-select>
                                    </el-input>
                                </div>
                                <div v-if="isPassOrCode" class="form-item">
                                    <el-input :placeholder="lang.login_pass" v-model="formData.password" type="password">
                                    </el-input>
                                </div>
                                <div v-else class="form-item code-item">
                                    <!-- 邮箱验证码 -->
                                    <el-input v-if="isEmailOrPhone" v-model="formData.emailCode" :placeholder="lang.email_code">
                                    </el-input>
                                    <count-down-button ref="emailCodebtn" @click.native="sendEmailCode" v-if="isEmailOrPhone" my-class="code-btn"></count-down-button>
                                    <!-- <el-button v-if="isEmailOrPhone" class="code-btn" type="primary">获取验证码</el-button> -->

                                    <!-- 手机验证码 -->
                                    <el-input v-if="!isEmailOrPhone" v-model="formData.phoneCode" :placeholder="lang.login_phone_code">
                                    </el-input>
                                    <count-down-button ref="phoneCodebtn" @click.native="sendPhoneCode" v-if="!isEmailOrPhone" my-class="code-btn"></count-down-button>
                                    <!-- <el-button v-if="!isEmailOrPhone" class="code-btn" type="primary">获取验证码</el-button> -->
                                </div>
                                <div class="form-item rember-item">
                                    <el-checkbox v-model="formData.isRemember">{{lang.login_remember}}</el-checkbox>
                                    <a @click="toForget">{{lang.login_forget}}</a>
                                </div>
                                <div class="read-item" v-if="errorText.length !== 0">
                                    <el-alert :title="errorText" type="error" show-icon :closable="false">
                                    </el-alert>
                                </div>
                                <div class="form-item">
                                    <el-button type="primary" class="login-btn" @click="doLogin">{{lang.login}}</el-button>
                                </div>
                                <div class="form-item read-item">
                                    <el-checkbox v-model="checked">
                                    {{lang.login_read}}<a @click="toService">{{lang.read_service}}</a>{{lang.read_and}}<a @click="toPrivacy">{{lang.read_privacy}}</a>
                                    </el-checkbox>
                                </div>

                                <div class="form-item line-item" v-if="commonData.login_phone_verify == 1">
                                    <el-divider><span class="text">or</span></el-divider>
                                </div>
                                <div class="form-item" v-if="commonData.login_phone_verify == 1">
                                    <el-button v-if="isPassOrCode" :disabled="commonData.login_phone_verify == 0" @click="isPassOrCode = false;isEmailOrPhone = false" class="type-btn">{{lang.login_code_login}}
                                    </el-button>
                                    <el-button v-else @click="isPassOrCode = true" class="type-btn">
                                        {{lang.login_pass_login}}
                                    </el-button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="login-right">
                        <img src="${url}/img/common/login_back.png" class="login-back-img">
                    </div>
                </el-dialog>
            </div>


        `,
    data() {
        return {
            visible: true,
            // 登录是否需要验证
            isCaptcha: false,
            isShowCaptcha: false, //登录是否显示验证码弹窗
            checked: getCookie("checked") == "1" ? true : false,
            isEmailOrPhone: getCookie("isEmailOrPhone") == "1" ? true : false,  // true:电子邮件 false:手机号
            isPassOrCode: getCookie("isPassOrCode") == "1" ? true : false, // true:密码登录 false:验证码登录
            errorText: "",
            formData: {
                email: getCookie("email") ? getCookie("email") : null,
                phone: getCookie("phone") ? getCookie("phone") : null,
                password: getCookie("password") ? getCookie("password") : null,
                phoneCode: "",
                emailCode: "",
                isRemember: getCookie("isRemember") == "1" ? true : false,
                countryCode: 86
            },
            token: "",
            captcha: '',
            countryList: [],
            commonData: {}
        }
    },
    components: {
        countDownButton,
        captchaDialog
    },
    props: {
        isShowLogin: {
            default: false,
            type: Boolean,
        }
    },
    created() {
        this.getCommonData()
        this.getCountryList()
    },
    mounted() {
        window.captchaCancel = this.captchaCancel
        window.getData = this.getData
        window.showLoginDialog = this.showLoginDialog
    },
    methods: {
        // 获取通用配置
        getCommonData() {
            this.commonData = JSON.parse(localStorage.getItem('common_set_before'))
            if (this.commonData.login_phone_verify == 0) {
                this.isPassOrCode = true
            }
            if (this.commonData.captcha_client_login == 1) {
                this.isCaptcha = true
            }
        },
        showLoginDialog() {
            this.visible = true
        },
        toRegist() {
            // location.href = 'regist.html'
            console.log("显示注册弹窗");
        },
        toForget() {
            // location.href = 'forget.html'
            console.log("显示忘记密码弹窗");
        },


        // 验证码验证成功后的回调
        getData(captchaCode, token) {
            this.isCaptcha = false
            this.token = token
            this.captcha = captchaCode
            this.isShowCaptcha = false
            // 判断是否密码登录 是执行登录
            // 否则判断发送手机验证码还是邮箱验证码
            if (this.isPassOrCode) {
                this.doLogin()
            } else {
                if (this.isEmailOrPhone) {
                    // 发送邮箱验证码
                    this.sendEmailCode()
                } else {
                    // 发送手机验证码
                    this.sendPhoneCode()
                }
            }

        },
        // 登录
        doLogin() {
            let isPass = true;
            const form = { ...this.formData };
            console.log(form);
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

                // 邮件 密码登录 验证
                if (this.isPassOrCode) { // 密码登录
                    if (!form.password) {
                        isPass = false
                        this.errorText = "请输入密码"
                    }
                } else {
                    // 邮件 验证码登录 验证
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

                // 手机号 密码登录 验证
                if (this.isPassOrCode) { // 密码登录
                    if (!form.password) {
                        isPass = false
                        this.errorText = "请输入密码"
                    }
                } else {
                    // 手机 验证码登录 验证
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
            }

            // 勾选协议
            if (!this.checked) {
                isPass = false
                this.errorText = "请勾选服务协议书！"
            }

            if (isPass && this.isCaptcha) {
                this.isShowCaptcha = true
                this.$refs.captcha.doGetCaptcha()
                return
            }

            // 验证通过
            if (isPass) {
                this.errorText = ""
                let code = "";
                if (!this.isPassOrCode) {
                    if (this.isEmailOrPhone) {
                        code = form.emailCode
                    } else {
                        code = form.phoneCode
                    }
                }
                const params = {
                    type: this.isPassOrCode ? "password" : "code",
                    account: this.isEmailOrPhone ? form.email : form.phone,
                    phone_code: form.countryCode.toString(),
                    code,
                    password: this.isPassOrCode ? this.encrypt(form.password) : "",
                    remember_password: form.isRemember ? "1" : "0",
                    captcha: this.captcha,
                    token: this.token
                }

                //调用登录接口
                logIn(params).then(res => {
                    if (res.data.status === 200) {
                        this.$message.success(res.data.msg);
                        // 存入 jwt
                        localStorage.setItem("jwt", res.data.data.jwt);
                        if (form.isRemember) {// 记住密码
                            console.log(form);
                            if (this.isEmailOrPhone) {
                                console.log("email");
                                setCookie("email", form.email, 30)
                            } else {
                                console.log("phone");
                                setCookie("phone", form.phone, 30)
                            }
                            setCookie("password", form.password, 30)
                            setCookie("isRemember", form.isRemember ? "1" : "0")
                            setCookie("checked", this.checked ? "1" : "0")

                            // 保存登录方式
                            setCookie("isEmailOrPhone", this.isEmailOrPhone ? "1" : "0")
                            setCookie("isPassOrCode", this.isPassOrCode ? "1" : "0")

                        } else {
                            // 未勾选记住密码
                            delCookie("email")
                            delCookie("phone")
                            delCookie("password")
                            delCookie("isRemember")
                            delCookie("checked")
                        }

                        location.reload();
                    }
                }).catch(err => {
                    if (err.data.msg === "请输入图形验证码" || err.data.msg === "图形验证码错误") {
                        this.isShowCaptcha = true
                        this.$refs.captcha.doGetCaptcha()
                    } else {
                        this.errorText = err.data.msg
                        // this.$message.error(err.data.msg);
                    }

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
                    action: "login",
                    email: form.email,
                    token: this.token,
                    captcha: this.captcha
                }
                emailCode(params).then(res => {
                    if (res.data.status === 200) {
                        // 执行倒计时
                        this.$refs.emailCodebtn.countDown()
                    }
                }).catch(err => {
                    if (err.data.msg === "请输入图形验证码" || err.data.msg === "图形验证码错误") {
                        this.isShowCaptcha = true
                        this.$refs.captcha.doGetCaptcha()
                    } else {
                        this.errorText = err.data.msg
                        // this.$message.error(err.data.msg);
                    }
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
                    action: "login",
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
                }).catch(err => {
                    if (err.data.msg == "请输入图形验证码" || err.data.msg == "图形验证码错误") {
                        this.isShowCaptcha = true
                        this.$refs.captcha.doGetCaptcha()
                    } else {
                        this.errorText = err.data.msg
                        // this.$message.error(err.data.msg);
                    }
                })
            }
        },
        toService() {
            const url = this.commonData.terms_service_url
            window.open(url);
        },
        toPrivacy() {
            const url = this.commonData.terms_privacy_url
            window.open(url);
        },
        // 验证码 关闭
        captchaCancel() {
            this.isShowCaptcha = false
        }
    },
}
