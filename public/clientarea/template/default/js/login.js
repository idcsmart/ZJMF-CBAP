(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const login = document.getElementById('login')
    Vue.prototype.lang = window.lang
    new Vue({
      components: {
        countDownButton,
        captchaDialog
      },
      data() {
        return {
          isShowCaptcha: false, //登录是否需要验证码
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
      created() {
        this.getCountryList();
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
          this.doLogin()
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
                this.doGetMenu()
                location.href = 'finance.html'
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
            }).catch(error => {
              this.errorText = error.data.msg
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
            }).catch(error => {
              this.errorText = error.data.msg
              // this.$message.error(error.data.msg);
            })
          }
        },
        toRegist() {
          location.href = 'regist.html'
        },
        toForget() {
          location.href = 'forget.html'
        },
        // 获取通用配置
        async getCommonSetting() {
          try {
            const res = await getCommon()
            console.log(res);
            this.commonData = res.data.data
            if (this.commonData.login_phone_verify == 0) {
              this.isPassOrCode = true
            }
            document.title = this.commonData.website_name + '-登录'
            localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
          } catch (error) {

          }
        },
        // 获取前台导航
        doGetMenu() {
          getMenu().then(res => {
            console.log(res);
            if (res.data.status === 200) {
              localStorage.setItem('frontMenus', JSON.stringify(res.data.data.menu))
            }
          })
        },
        toRead() {
          const url = this.commonData.terms_service_url
          location.href = url
        }
      }
    }).$mount(login)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
