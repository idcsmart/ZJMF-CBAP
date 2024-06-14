// 验证码通过
function captchaCheckSuccsss(bol, captcha, token) {
  if (bol) {
    // 验证码验证通过
    getData(captcha, token);
  }
}
// 取消验证码验证
function captchaCheckCancel() {
  captchaCancel();
}

(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const login = document.getElementById("login");
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        countDownButton,
        captchaDialog,
        safeConfirm,
      },
      data() {
        return {
          client_operate_password: "",
          // 登录是否需要验证
          isCaptcha: false,
          isShowCaptcha: false, //登录是否显示验证码弹窗
          checked: getCookie("checked") == "1" ? true : false,
          isEmailOrPhone: getCookie("isEmailOrPhone") == "1" ? true : false, // true:电子邮件 false:手机号
          isPassOrCode: getCookie("isPassOrCode") == "1" ? true : false, // true:密码登录 false:验证码登录
          errorText: "",
          formData: {
            email: getCookie("email") ? getCookie("email") : null,
            phone: getCookie("phone") ? getCookie("phone") : null,
            password: getCookie("password") ? getCookie("password") : null,
            phoneCode: "",
            emailCode: "",
            //  isRemember: getCookie("isRemember") == "1" ? true : false,
            isRemember: false,
            countryCode: 86,
          },
          token: "",
          loopTimer: null,
          captcha: "",
          countryList: [],
          codeAction: "",
          commonData: {
            lang_list: [],
          },
          loginLoading: false,
          seletcLang: "",
          curSrc: `/upload/common/country/${lang_obj.countryImg}.png`,
        };
      },
      // 计算钩子
      computed: {},
      created() {
        if (localStorage.getItem("jwt")) {
          location.href = "home.htm";
          return;
        }
        this.seletcLang = getBrowserLanguage();
        this.getCountryList();
        this.getCommonSetting();
      },
      mounted() {
        window.captchaCancel = this.captchaCancel;
        window.getData = this.getData;
        // 关闭loading
        document.getElementById("mainLoading").style.display = "none";
        document.getElementsByClassName("template")[0].style.display = "block";
      },

      watch: {},
      methods: {
        hadelSafeConfirm(val) {
          this[val]();
        },
        // 验证码验证成功后的回调
        getData(captchaCode, token) {
          this.isCaptcha = false;
          this.token = token;
          this.captcha = captchaCode;
          this.isShowCaptcha = false;
          if (this.codeAction === "login") {
            this.doLogin();
          } else if (this.codeAction === "emailCode") {
            this.sendEmailCode();
          } else if (this.codeAction === "phoneCode") {
            this.sendPhoneCode();
          }
        },
        goHelpUrl(url) {
          window.open(this.commonData[url]);
        },

        // 语言切换
        changeLang(e) {
          sessionStorage.setItem("brow_lang", e);
          // 刷新页面
          window.location.reload();
        },
        // 登录
        doLogin() {
          let isPass = true;
          const form = { ...this.formData };
          // 邮件登录验证
          if (this.isEmailOrPhone) {
            if (!form.email) {
              isPass = false;
              this.errorText = lang.login_text1;
            } else if (
              form.email.search(
                /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/
              ) === -1
            ) {
              isPass = false;
              this.errorText = lang.login_text2;
            }

            // 邮件 密码登录 验证
            if (this.isPassOrCode) {
              // 密码登录
              if (!form.password) {
                isPass = false;
                this.errorText = lang.login_text3;
              }
            } else {
              // 邮件 验证码登录 验证
              if (!form.emailCode) {
                isPass = false;
                this.errorText = lang.login_text4;
              } else {
                if (form.emailCode.length !== 6) {
                  isPass = false;
                  this.errorText = lang.login_text5;
                }
              }
            }
          }

          // 手机号码登录 验证
          if (!this.isEmailOrPhone) {
            if (!form.phone) {
              isPass = false;
              this.errorText = lang.login_text6;
            } else {
              // 设置正则表达式的手机号码格式 规则 ^起点 $终点 1第一位数是必为1  [3-9]第二位数可取3-9的数字  \d{9} 匹配9位数字
              const reg = /^\d+$/;
              if (this.formData.countryCode === 86) {
                if (!reg.test(form.phone)) {
                  isPass = false;
                  this.errorText = lang.login_text7;
                }
              }
            }

            // 手机号 密码登录 验证
            if (this.isPassOrCode) {
              // 密码登录
              if (!form.password) {
                isPass = false;
                this.errorText = lang.login_text3;
              }
            } else {
              // 手机 验证码登录 验证
              if (!form.phoneCode) {
                isPass = false;
                this.errorText = lang.account_tips45;
              } else {
                if (form.phoneCode.length !== 6) {
                  isPass = false;
                  this.errorText = lang.account_tips46;
                }
              }
            }
          }

          // 勾选协议
          if (!this.checked) {
            isPass = false;
            this.errorText = lang.account_tips51;
          }

          if (isPass && this.isCaptcha) {
            this.loginLoading = true;
            this.isShowCaptcha = true;
            this.codeAction = "login";
            this.$refs.captcha.doGetCaptcha();
            this.loginLoading = false;
            return;
          }

          // 验证通过
          if (isPass) {
            this.loginLoading = true;
            this.errorText = "";
            let code = "";
            if (!this.isPassOrCode) {
              if (this.isEmailOrPhone) {
                code = form.emailCode;
              } else {
                code = form.phoneCode;
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
              token: this.token,
              client_operate_password: this.client_operate_password,
            };

            //调用登录接口
            logIn(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(res.data.msg);
                  // 存入 jwt
                  localStorage.setItem("jwt", res.data.data.jwt);
                  localStorage.setItem("lang", this.seletcLang);
                  if (form.isRemember) {
                    // 记住密码
                    if (this.isEmailOrPhone) {
                      setCookie("email", form.email, 30);
                    } else {
                      setCookie("phone", form.phone, 30);
                    }
                    setCookie("password", form.password, 30);
                    setCookie("isRemember", form.isRemember ? "1" : "0");
                    setCookie("checked", this.checked ? "1" : "0");

                    // 保存登录方式
                    setCookie(
                      "isEmailOrPhone",
                      this.isEmailOrPhone ? "1" : "0"
                    );
                    setCookie("isPassOrCode", this.isPassOrCode ? "1" : "0");
                  } else {
                    // 未勾选记住密码
                    delCookie("email");
                    delCookie("phone");
                    delCookie("password");
                    delCookie("isRemember");
                    delCookie("checked");
                  }
                  this.loginLoading = false;
                  getMenu().then((ress) => {
                    if (ress.data.status === 200) {
                      localStorage.setItem(
                        "frontMenus",
                        JSON.stringify(ress.data.data.menu)
                      );
                      const goPage = sessionStorage.redirectUrl || "/home.htm";
                      sessionStorage.redirectUrl &&
                        sessionStorage.removeItem("redirectUrl");
                      location.href = goPage;
                    }
                  });
                }
              })
              .catch((err) => {
                this.loginLoading = false;
                this.client_operate_password = "";
                if (
                  err.data.msg === "请输入图形验证码" ||
                  err.data.msg === "图形验证码错误"
                ) {
                  this.isCaptcha = true;
                  this.isShowCaptcha = true;
                  this.codeAction = "login";
                  this.$refs.captcha.doGetCaptcha();
                } else if (
                  err.data.data &&
                  err.data.data?.ip_exception_verify &&
                  err.data.data.ip_exception_verify.includes("operate_password")
                ) {
                  this.$refs.safeRef.openDialog("doLogin");
                  this.$message.error(lang.account_tips_text7);
                } else {
                  this.getCommonSetting(
                    this.isEmailOrPhone ? form.email : form.phone
                  );
                  this.errorText = err.data.msg;
                }
              });
          }
        },
        // 获取国家列表
        getCountryList() {
          getCountry({}).then((res) => {
            if (res.data.status === 200) {
              this.countryList = res.data.data.list;
            }
          });
        },
        // 加密方法
        encrypt(str) {
          const key = CryptoJS.enc.Utf8.parse("idcsmart.finance");
          const iv = CryptoJS.enc.Utf8.parse("9311019310287172");
          return CryptoJS.AES.encrypt(str, key, {
            mode: CryptoJS.mode.CBC,
            padding: CryptoJS.pad.Pkcs7,
            iv: iv,
          }).toString();
        },
        // 发送邮箱验证码
        sendEmailCode() {
          let isPass = true;
          const form = this.formData;
          if (!form.email) {
            isPass = false;
            this.errorText = lang.login_text1;
          } else if (
            form.email.search(
              /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/
            ) === -1
          ) {
            isPass = false;
            this.errorText = lang.login_text2;
          }
          if (isPass) {
            this.errorText = "";
            const params = {
              action: "login",
              email: form.email,
              token: this.token,
              captcha: this.captcha,
            };
            emailCode(params)
              .then((res) => {
                if (res.data.status === 200) {
                  // 执行倒计时
                  this.$refs.emailCodebtn.countDown();
                }
              })
              .catch((err) => {
                if (
                  err.data.msg === "请输入图形验证码" ||
                  err.data.msg === "图形验证码错误"
                ) {
                  this.isShowCaptcha = true;
                  this.codeAction = "emailCode";
                  this.$refs.captcha.doGetCaptcha();
                } else {
                  this.errorText = err.data.msg;
                  // this.$message.error(err.data.msg);
                  this.token = "";
                  this.captcha = "";
                }
              });
          }
        },
        // 发送手机短信
        sendPhoneCode() {
          let isPass = true;
          const form = this.formData;
          if (!form.phone) {
            isPass = false;
            this.errorText = lang.login_text6;
          } else {
            // 设置正则表达式的手机号码格式 规则 ^起点 $终点 1第一位数是必为1  [3-9]第二位数可取3-9的数字  \d{9} 匹配9位数字
            if (this.formData.countryCode === 86) {
              const reg = /^\d+$/;
              if (!reg.test(form.phone)) {
                isPass = false;
                this.errorText = lang.login_text7;
              }
            }
          }
          if (isPass) {
            this.errorText = "";
            const params = {
              action: "login",
              phone_code: form.countryCode,
              phone: form.phone,
              token: this.token,
              captcha: this.captcha,
            };
            phoneCode(params)
              .then((res) => {
                if (res.data.status === 200) {
                  // 执行倒计时
                  this.$refs.phoneCodebtn.countDown();
                }
              })
              .catch((err) => {
                if (
                  err.data.msg == "请输入图形验证码" ||
                  err.data.msg == "图形验证码错误"
                ) {
                  this.isShowCaptcha = true;
                  this.codeAction = "phoneCode";
                  this.$refs.captcha.doGetCaptcha();
                } else {
                  this.errorText = err.data.msg;
                  this.token = "";
                  this.captcha = "";
                  // this.$message.error(err.data.msg);
                }
              });
          }
        },
        toRegist() {
          location.href = "regist.htm";
        },
        toForget() {
          location.href = "forget.htm";
        },
        oauthLogin(item) {
          // 勾选协议
          if (!this.checked) {
            this.errorText = lang.account_tips51;
            return;
          }
          oauthUrl(item.name).then((res) => {
            const openWindow = window.open(
              res.data.data.url,
              "oauth",
              "width=800,height=800"
            );
            clearInterval(this.loopTimer);
            this.loopTimer = null;
            this.loopTimer = setInterval(() => {
              if (openWindow.closed) {
                clearInterval(this.loopTimer);
                this.loopTimer = null;
                this.getOauthToken();
              }
            }, 300);
          });
        },
        getOauthToken() {
          oauthToken().then((res) => {
            if (res.data.data && (res.data.data.jwt || res.data.data.url)) {
              if (res.data.data.jwt) {
                // 存入 jwt
                localStorage.setItem("jwt", res.data.data.jwt);
                getMenu().then((ress) => {
                  if (ress.data.status === 200) {
                    localStorage.setItem(
                      "frontMenus",
                      JSON.stringify(ress.data.data.menu)
                    );
                    const goPage = sessionStorage.redirectUrl || "/home.htm";
                    sessionStorage.redirectUrl &&
                      sessionStorage.removeItem("redirectUrl");
                    location.href = goPage;
                  }
                });
              } else {
                location.href = res.data.data.url;
              }
            }
          });
        },
        // 获取通用配置
        async getCommonSetting(account) {
          try {
            const res = await getCommon({ account });
            this.commonData = res.data.data;
            if (this.commonData.login_phone_verify == 0) {
              this.isPassOrCode = true;
            }
            if (
              this.commonData.captcha_client_login == 1 &&
              (this.commonData.captcha_client_login_error == 0 ||
                (this.commonData.captcha_client_login_error == 1 &&
                  this.commonData.captcha_client_login_error_3_times == 1))
            ) {
              this.isCaptcha = true;
            }
            document.title =
              this.commonData.website_name + "-" + lang.login_text8;
            localStorage.setItem(
              "common_set_before",
              JSON.stringify(res.data.data)
            );
            localStorage.setItem("lang", this.commonData.lang_home);
          } catch (error) {}
        },
        // 获取前台导航
        doGetMenu() {
          getMenu().then((res) => {
            if (res.data.status === 200) {
              localStorage.setItem(
                "frontMenus",
                JSON.stringify(res.data.data.menu)
              );
            }
          });
        },
        toService() {
          const url = this.commonData.terms_service_url;
          window.open(url);
        },
        toPrivacy() {
          const url = this.commonData.terms_privacy_url;
          window.open(url);
        },
        // 验证码 关闭
        captchaCancel() {
          this.isShowCaptcha = false;
        },
      },
    }).$mount(login);
    typeof old_onload == "function" && old_onload();
  };
})(window);
