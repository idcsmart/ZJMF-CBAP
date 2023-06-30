(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        aliAsideMenu,
        asideMenu,
        topMenu,
        countDownButton,
        pagination,
        certificationDialog,
      },
      created() {
        this.getCommonData();
        // this.getAccount();
        this.getCountry();
      },
      mounted() {
        window.addEventListener("scroll", this.computeScroll);
        const addons = document.querySelector("#addons_js");
        this.addons_js_arr = JSON.parse(addons.getAttribute("addons_js"));
        this.getCertificationInfo();
      },
      updated() {
        // 关闭loading
        document.getElementById("mainLoading").style.display = "none";
        document.getElementsByClassName("template")[0].style.display = "block";
      },
      destroyed() {
        window.removeEventListener("scroll", this.computeScroll);
      },
      data() {
        return {
          showAccountController: false, // 是否展示概要
          showLogController: false, // 是否展示查看日志
          addons_js_arr: [],
          isShowCaptcha: false, //是否显示验证码弹窗
          tip_dialong_show: false,
          PermissionsList: [], // 权限列表
          activeIndex: "1",
          // 账户姓名
          userName: "",
          // 账户国家图片
          curSrc: "",
          // 获取的账户信息
          accountData: {},
          // 国家列表
          countryList: [],
          // 认证状态相关信息对象
          attestationStatusInfo: {
            iocnShow: false, // 认证信息是否显示
            iconUrl: null, // 图标
            text: "", // 文字信息
            status: 0, // 认证状态  0：未认证 10：仅个人认证通过  20：仅企业认证通过：30：个人企业均认证通过 40:失败
          },
          isShowPass: false,
          passData: {
            old_password: "",
            new_password: "",
            repassword: "",
          },
          imgShow: false,
          phoneData: {},
          rePhoneData: {
            countryCode: 86,
          },
          emailData: {},
          reEmailData: {},
          errText: "",
          isShowPhone: false,
          isShowRePhone: false,
          isShowEmail: false,
          isShowReEmail: false,
          isShowCodePass: false,
          isEmailOrPhone: true,
          // 图形验证码
          token: "",
          captcha: "",
          // 操作日志相关
          loading: false,
          dataList: [],
          params: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },
          timerId: null,
          // 忘记密码相关
          formData: {
            email: "",
            phone: "",
            password: "",
            repassword: "",
            phoneCode: "",
            emailCode: "",
            countryCode: 86,
          },
          errorText: "",
          commonData: {},
          isShowBackTop: false,
          scrollY: 0,
          isEnd: false,
          isShowMore: false,
        };
      },
      filters: {
        formateTime(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000);
          } else {
            return "--";
          }
        },
      },
      methods: {
        getRule(arr) {
          console.log(arr, "---->");
          let isShow1 = this.showFun(arr, "AccountController::index");
          let isShow2 = this.showFun(arr, "LogController::list");
          console.log(isShow1, isShow2);
          if (isShow1) {
            this.showAccountController = true;
          } else {
            this.activeIndex = "2";
            this.handleClick();
          }
          if (isShow2) {
            this.showLogController = true;
            if (isShow1) {
              this.handleClick();
            } else {
              this.activeIndex = "2";
              this.handleClick();
            }
          }
        },
        showFun(arr, str) {
          if (typeof arr == "string") {
            return true;
          } else {
            let isShow = "";
            isShow = arr.find((item) => {
              let isHave = item.includes(str);
              if (isHave) {
                return isHave;
              }
            });
            return isShow;
          }
        },
        // tab 切换
        handleClick() {
          if (this.activeIndex == 1) {
            this.getAccount();
          } else {
            this.getAccountList();
          }
        },
        sizeChange(e) {
          this.params.limit = e;
          this.params.page = 1;
          this.getAccountList();
        },
        currentChange(e) {
          this.params.page = e;
          this.getAccountList();
        },
        // 获取账户操作日志
        getAccountList() {
          // 表格加载
          this.loading = true;
          getLog({ ...this.params, type: "system" }).then((res) => {
            if (res.data.status === 200) {
              let list = res.data.data.list;

              this.dataList = list;
              this.params.total = res.data.data.count;
            }
            this.loading = false;
          });
        },
        // 搜索框
        inputChange() {
          this.params.page = 1;
          this.getAccountList();
        },
        // 打开验证码修改密码弹窗
        showCodePass() {
          this.formData = {
            email: "",
            phone: "",
            password: "",
            repassword: "",
            phoneCode: "",
            emailCode: "",
            countryCode: 86,
          };
          this.isShowPass = false;
          this.isShowCodePass = true;
        },
        // 关闭验证码修改密码弹窗
        quiteCodePass() {
          this.isShowCodePass = false;
        },
        // 获取账户详情
        getAccount() {
          account().then((res) => {
            this.imgShow = false;
            if (res.data.status === 200) {
              this.accountData = res.data.data.account;
              this.userName = res.data.data.account.username;
              this.commonData = JSON.parse(
                localStorage.getItem("common_set_before")
              );

              if (!this.accountData.language) {
                this.accountData.language = this.commonData.lang_home;
              }
              // 如果账户选择了国家
              // 掉接口查找国家的 iso 图片前缀 进行拼接
              if (this.accountData.country) {
                const params = {
                  keywords: this.accountData.country,
                };
                country(params).then((res) => {
                  if (res.data.status === 200) {
                    const iso = res.data.data.list[0].iso;
                    this.curSrc = `/upload/common/country/${iso}.png`;
                    this.imgShow = true;
                  }
                });
              }
            }
          });
        },
        // 获取认证状态信息
        getCertificationInfo() {
          const arr = this.addons_js_arr.map((item) => {
            return item.name;
          });
          if (arr.includes("IdcsmartCertification")) {
            certificationInfo().then((ress) => {
              this.attestationStatusInfo.iocnShow = false;
              if (ress.data.status === 200) {
                this.attestationStatusInfo.iocnShow = true;
                // 认证失败
                if (
                  !ress.data.data.is_certification ||
                  (ress.data.data.company.status !== 1 &&
                    ress.data.data.person.status !== 1)
                ) {
                  this.attestationStatusInfo.iconUrl = `${url}/img/account/unauthorized.png`;
                  this.attestationStatusInfo.text = window.lang.account_tips12;
                  if (
                    ress.data.data.company.status === 3 ||
                    ress.data.data.company.status === 4
                  ) {
                    // 企业资料审核中
                    this.attestationStatusInfo.status = 25;
                  } else if (
                    ress.data.data.person.status === 3 ||
                    ress.data.data.person.status === 4
                  ) {
                    // 个人资料审核中
                    this.attestationStatusInfo.status = 15;
                  } else if (
                    ress.data.data.company.status === 2 ||
                    ress.data.data.person.status === 2
                  ) {
                    if (ress.data.data.company.status === 2) {
                      // 企业认证失败
                      this.attestationStatusInfo.status = 40;
                    } else {
                      // 个人认证失败
                      this.attestationStatusInfo.status = 45;
                    }
                  } else {
                    this.attestationStatusInfo.status = 0; // 未认证
                  }
                  // this.tip_dialong_show = true
                  return;
                }
                // 企业认证成功
                if (ress.data.data.company.status === 1) {
                  this.attestationStatusInfo.iconUrl = `${url}/img/account/enterprise_certification.png`;
                  this.attestationStatusInfo.text = window.lang.account_tips13;
                  if (ress.data.data.person.status === 1) {
                    this.attestationStatusInfo.status = 30; // 个人成功
                  } else {
                    this.attestationStatusInfo.status = 20; // 个人未成功
                  }
                  return;
                }
                // 个人认证成功
                if (ress.data.data.person.status === 1) {
                  this.attestationStatusInfo.iconUrl = `${url}/img/account/personal_certification.png`;
                  this.attestationStatusInfo.text = window.lang.account_tips14;
                  if (ress.data.data.company.status === 1) {
                    this.attestationStatusInfo.status = 30;
                  } else {
                    this.attestationStatusInfo.status = 10;
                  }
                  return;
                }
              }
            });
          }
        },
        // 点击认证图标
        handelAttestation(val) {
          // 未认证或者都未通过时 跳转认证选择页面
          if (this.attestationStatusInfo.status === 0) {
            location.href = `plugin/${val}/authentication_select.htm`;
            return;
          }
          // 企业认证成功时 跳转企业认证成功页面
          if (this.attestationStatusInfo.status === 20 || this.attestationStatusInfo.status === 30
          ) {
            location.href = `plugin/${val}/authentication_status.htm?type=2`;
            return;
          }
          // 仅个人认证成功时 跳转个人认证成功页面
          if (this.attestationStatusInfo.status === 10) {
            location.href = `plugin/${val}/authentication_status.htm?type=1`;
            return;
          }
          // 企业审核中 跳转企业认证审核中页面
          if (this.attestationStatusInfo.status === 25) {
            location.href = `plugin/${val}/authentication_status.htm?type=2`;
            return;
          }
          // 个人审核中 跳转个人认证审核中页面
          if (this.attestationStatusInfo.status === 15) {
            location.href = `plugin/${val}/authentication_status.htm?type=1`;
            return;
          }
          // 有审核失败
          if (this.attestationStatusInfo.status === 40) {
            // 企业认证失败
            location.href = `plugin/${val}/authentication_status.htm?type=2`;
            return;
          }
          if (this.attestationStatusInfo.status === 45) {
            // 个人认证失败
            location.href = `plugin/${val}/authentication_status.htm?type=1`;
            return;
          }
        },
        // 获取国家列表
        getCountry() {
          country().then((res) => {
            if (res.data.status === 200) {
              this.countryList = res.data.data.list;
            }
          });
        },
        // 编辑基础资料
        saveAccount() {
          const data = this.accountData;
          const params = {
            username: data.username,
            company: data.company,
            country: data.country,
            address: data.address,
            language: data.language,
            notes: "",
          };
          updateAccount(params)
            .then((res) => {
              if (res.data.status === 200) {
                this.$message.success(res.data.msg);
                this.getAccount();
              }
            })
            .catch((error) => {
              this.$message.error(error.data.msg);
            });
        },
        // 展示修改密码弹框
        showPass() {
          this.isShowPass = true;
          let data = {
            old_password: "",
            new_password: "",
            repassword: "",
          };
          this.passData = data;
          this.errText = "";
        },
        // 展示修改手机弹框
        showPhone() {
          this.errText = "";
          if (this.accountData.phone) {
            // 有手机号
            // 展示验证手机
            this.phoneData = {};
            this.isShowPhone = true;
          } else {
            // 展示绑定手机
            this.rePhoneData = {
              countryCode: 86,
            };
            this.isShowRePhone = true;
          }
        },
        // 展示修改邮箱弹框
        showEmail() {
          this.errText = "";
          this.emailData = {};
          this.reEmailData = {};
          if (this.accountData.email) {
            // 有邮箱
            // 展示验证邮箱
            this.isShowEmail = true;
          } else {
            // 展示绑定邮箱
            this.isShowReEmail = true;
          }
        },
        // 确认修改密码
        doPassEdit() {
          let isPass = true;
          const data = this.passData;
          if (!data.old_password) {
            this.errText = lang.account_tips25;
            isPass = false;
          } else {
            if (data.old_password.length < 6 || data.old_password.length > 32) {
              this.errText = lang.account_tips26;
              isPass = false;
            }
          }

          if (!data.new_password) {
            this.errText = lang.account_tips27;
            isPass = false;
          } else {
            if (data.new_password.length < 6 || data.new_password.length > 32) {
              this.errText = lang.account_tips28;
              isPass = false;
            }
          }

          if (!data.repassword) {
            this.errText = lang.account_tips29;
            isPass = false;
          } else {
            if (data.repassword.length < 6 || data.repassword.length > 32) {
              this.errText = lang.account_tips30;
              isPass = false;
            }
            if (data.repassword !== data.new_password) {
              this.errText = lang.account_tips31;
              isPass = false;
            }
          }
          if (isPass) {
            this.errText = "";
            updatePassword(data)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(lang.account_tips32);
                  this.isShowPass = false;
                  location.href = "login.htm";
                  // 执行登录接口
                }
              })
              .catch((error) => {
                this.errText = error.data.msg;
                // this.$message.error(error.data.msg)
              });
          }
        },
        // 验证原手机号
        doPhoneEdit() {
          let isPass = true;
          if (!this.phoneData.code) {
            isPass = false;
            this.errText = lang.account_tips33;
          } else {
            if (this.phoneData.code.length !== 6) {
              isPass = false;
              this.errText = lang.account_tips34;
            }
          }
          if (isPass) {
            this.errText = "";
            const params = {
              code: this.phoneData.code,
            };
            verifiedPhone(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(lang.account_tips35);
                  this.isShowPhone = false;
                  this.isShowRePhone = true;
                }
              })
              .catch((error) => {
                this.errText = error.data.msg;
                // this.$message.error(error.data.msg)
              });
          }
        },
        // 修改手机号
        doRePhoneEdit() {
          let isPass = true;
          if (!this.rePhoneData.phone) {
            isPass = false;
            this.errText = lang.account_tips36;
          } else {
            if (this.rePhoneData.phone.length !== 11) {
              isPass = false;
              this.errText = lang.account_tips37;
            }
          }
          if (!this.rePhoneData.code) {
            isPass = false;
            this.errText = lang.account_tips33;
          } else {
            if (this.rePhoneData.code.length !== 6) {
              isPass = false;
              this.errText = lang.account_tips34;
            }
          }
          if (isPass) {
            this.errText = "";
            const params = {
              phone_code: this.rePhoneData.countryCode,
              phone: this.rePhoneData.phone,
              code: this.rePhoneData.code,
            };
            updatePhone(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(lang.account_tips38);
                  this.getAccount();
                  this.isShowRePhone = false;
                }
              })
              .catch((error) => {
                this.errText = error.data.msg;
                // this.$message.error(error.data.msg)
              });
          }
        },
        // 验证原邮箱
        doEmailEdit() {
          let isPass = true;
          if (!this.emailData.code) {
            isPass = false;
            this.errText = lang.account_tips33;
          } else {
            if (this.emailData.code.length !== 6) {
              isPass = false;
              this.errText = lang.account_tips34;
            }
          }
          if (isPass) {
            this.errText = "";
            const params = {
              code: this.emailData.code,
            };
            verifiedEmail(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(lang.account_tips39);
                  this.isShowEmail = false;
                  this.isShowReEmail = true;
                }
              })
              .catch((error) => {
                this.errText = error.data.msg;
                // this.$message.error(error.data.msg)
              });
          }
        },
        // 修改邮箱
        doReEmailEdit() {
          let isPass = true;
          if (!this.reEmailData.code) {
            isPass = false;
            this.errText = lang.account_tips33;
          } else {
            if (this.reEmailData.code.length !== 6) {
              isPass = false;
              this.errText = lang.account_tips34;
            }
          }

          if (!this.reEmailData.email) {
            isPass = false;
            this.errText = lang.account_tips19;
          }
          if (isPass) {
            this.errText = "";
            const params = {
              code: this.reEmailData.code,
              email: this.reEmailData.email,
            };
            updateEmail(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(lang.account_tips39);
                  this.isShowReEmail = false;
                  this.getAccount();
                }
              })
              .catch((error) => {
                this.errText = error.data.msg;
                // this.$message.error(error.data.msg)
              });
          }
        },
        doResetPass() {
          let isPass = true;
          const form = { ...this.formData };
          // 邮件登录验证
          if (this.isEmailOrPhone) {
            if (!form.email) {
              isPass = false;
              this.errorText = lang.ali_tips1;
            } else if (
              form.email.search(
                /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/
              ) === -1
            ) {
              isPass = false;
              this.errorText = lang.account_tips40;
            }

            if (!form.emailCode) {
              isPass = false;
              this.errorText = lang.account_tips41;
            } else {
              if (form.emailCode.length !== 6) {
                isPass = false;
                this.errorText = lang.account_tips42;
              }
            }
          }

          // 手机号码登录 验证
          if (!this.isEmailOrPhone) {
            if (!form.phone) {
              isPass = false;
              this.errorText = lang.account_tips43;
            } else {
              // 设置正则表达式的手机号码格式 规则 ^起点 $终点 1第一位数是必为1  [3-9]第二位数可取3-9的数字  \d{9} 匹配9位数字
              const reg = /^1[3-9]\d{9}$/;
              if (!reg.test(form.phone)) {
                isPass = false;
                this.errorText = lang.account_tips44;
              }
            }

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

          if (!form.password) {
            isPass = false;
            this.errorText = lang.account_tips47;
          } else if (form.password.length > 32 || form.password.length < 6) {
            isPass = false;
            this.errorText = lang.account_tips26;
          }
          if (!form.repassword) {
            isPass = false;
            this.errorText = lang.account_tips48;
          } else {
            if (form.password !== form.repassword) {
              isPass = false;
              this.errorText = lang.account_tips49;
            }
          }

          // 验证通过
          if (isPass) {
            this.errorText = "";
            let code = "";

            if (this.isEmailOrPhone) {
              code = form.emailCode;
            } else {
              code = form.phoneCode;
            }

            const params = {
              type: this.isEmailOrPhone ? "email" : "phone",
              account: this.isEmailOrPhone ? form.email : form.phone,
              phone_code: form.countryCode.toString(),
              code,
              password: form.password,
              re_password: form.repassword,
            };

            //调用注册接口
            forgetPass(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(res.data.msg);
                  // // 存入 jwt
                  // localStorage.setItem("jwt", res.data.data.jwt);
                  location.href = "login.htm";
                }
              })
              .catch((err) => {
                this.errorText = err.data.msg;
                // this.$message.error(err.data.msg);
              });
          }
        },
        // 发送手机验证码
        sendPhoneCode(type) {
          let isPass = true;
          if (type === "old") {
            const params = {
              action: "verify",
              phone_code: Number(this.accountData.phone_code),
              phone: this.accountData.phone,
              token: this.token,
              captcha: this.captcha,
            };
            phoneCode(params).then((res) => {
              if (res.data.status === 200) {
                this.errText = "";
                // 验证原手机 验证码按钮 执行倒计时
                this.$refs.phoneCodebtn.countDown();
              }
            }).catch((error) => {
              this.errText = error.data.msg;
            });
          }
          if (type === "new") {
            if (!this.rePhoneData.phone) {
              this.errText = lang.account_tips43;
              isPass = false;
            } else if (this.rePhoneData.phone.length !== 11) {
              this.errText = lang.account_tips37;
              isPass = false;
            }

            if (isPass) {
              this.errText = "";
              const params = {
                action: "update",
                phone_code: Number(this.rePhoneData.countryCode),
                phone: this.rePhoneData.phone,
                token: this.token,
                captcha: this.captcha,
              };
              phoneCode(params).then((res) => {
                if (res.data.status === 200) {
                  this.errText = "";
                  // 验证原手机 验证码按钮 执行倒计时
                  this.$refs.rePhoneCodebtn.countDown();
                }
              }).catch((error) => {
                this.errText = error.data.msg;
              });
            }
          }

          if (type === "code") {
            const form = this.formData;
            if (!form.phone) {
              isPass = false;
              this.errorText = lang.account_tips43;
            } else {
              // 设置正则表达式的手机号码格式 规则 ^起点 $终点 1第一位数是必为1  [3-9]第二位数可取3-9的数字  \d{9} 匹配9位数字
              const reg = /^1[3-9]\d{9}$/;
              if (!reg.test(form.phone)) {
                isPass = false;
                this.errorText = lang.account_tips44;
              }
            }

            if (isPass) {
              this.errorText = "";
              const params = {
                action: "password_reset",
                phone_code: form.countryCode,
                phone: form.phone,
                token: this.token,
                captcha: this.captcha,
              };
              phoneCode(params).then((res) => {
                if (res.data.status === 200) {
                  this.errText = "";
                  // 执行倒计时
                  this.$refs.codePhoneCodebtn.countDown();
                }
              }).catch((error) => {
                if (error.data.msg === "请输入图形验证码") {
                  this.isShowCaptcha = true;
                }
                this.$message.error(error.data.msg);
              });
            }
          }
        },
        // 发送邮箱验证码
        sendEmailCode(type) {
          let isPass = true;
          if (type === "old") {
            const params = {
              action: "verify",
              email: this.accountData.email,
              token: this.token,
              captcha: this.captcha,
            };
            emailCode(params).then((res) => {
              if (res.data.status === 200) {
                this.errText = "";
                // 验证原邮箱 验证码按钮 执行倒计时
                this.$refs.emailCodebtn.countDown();
              }
            }).catch((error) => {
              this.$message.error(error.data.msg);
            });
          }
          if (type === "new") {
            if (!this.reEmailData.email) {
              this.errText = lang.account_tips19;
              isPass = false;
            }
            if (isPass) {
              const params = {
                action: "update",
                email: this.reEmailData.email,
                token: this.token,
                captcha: this.captcha,
              };
              emailCode(params).then((res) => {
                if (res.data.status === 200) {
                  this.errText = "";
                  // 修改邮箱 验证码按钮 执行倒计时
                  this.$refs.reEmailCodebtn.countDown();
                }
              }).catch((error) => {
                this.errText = error.data.msg;
              });
            }
          }
          if (type === "code") {
            const form = this.formData;
            if (!form.email) {
              isPass = false;
              this.errorText = lang.ali_tips1;
            } else if (
              form.email.search(
                /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/
              ) === -1
            ) {
              isPass = false;
              this.errorText = lang.account_tips40;
            }
            if (isPass) {
              this.errorText = "";
              const params = {
                action: "password_reset",
                email: form.email,
                token: this.token,
                captcha: this.captcha,
              };
              emailCode(params).then((res) => {
                if (res.data.status === 200) {
                  this.errText = "";
                  // 执行倒计时
                  this.$refs.codeEmailCodebtn.countDown();
                }
              }).catch((error) => {
                if (error.data.msg === "请输入图形验证码") {
                  this.isShowCaptcha = true;
                }
                this.$message.error(error.data.msg);
              });
            }
          }
        },
        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
          document.title = this.commonData.website_name + "-" + lang.account_tips50;
        },
        // 监测滚动
        computeScroll() {
          return
          let sizeWidth = document.documentElement.clientWidth; // 初始宽宽度
          if (sizeWidth > 750) {
            return false;
          }

          const body = document.getElementById("account");
          // 获取距离顶部的距离
          let scrollTop =
            window.pageYOffset ||
            document.documentElement.scrollTop ||
            document.body.scrollTop;
          // 获取窗口的高度
          let browserHeight = window.outerHeight;
          // 滚动条高度
          const scrollHeight = body.scrollHeight;
          let scroll = scrollTop - this.scrollY;
          this.scrollY = scrollTop;
          // 判断返回顶部按钮是否显示
          if (scrollTop > browserHeight) {
            if (scroll < 0) {
              this.isShowBackTop = true;
            } else {
              this.isShowBackTop = false;
            }
          } else {
            this.isShowBackTop = false;
          }

          // 判断是否到达底部
          if (browserHeight + scrollTop >= scrollHeight) {
            // 判断是否加载数据

            // 订单记录
            // 判断是否最后一页
            // 是：显示到底了
            // 不是：则加载下一页 显示加载中
            const params = this.params;
            // 计算总页数
            let allPage =
              params.total % params.limit == 0
                ? params.total / params.limit
                : Math.floor(params.total / params.limit) + 1;

            if (params.page == allPage) {
              // 已经是最后一页了
              this.isEnd = true;
            } else {
              // 显示加载中
              this.isShowMore = true;
              // 页数加一
              this.params.page = this.params.page + 1;
              // 获取订单记录 push到列表中
              // 关闭加载中
              getLog({ ...this.params, type: "system" }).then((res) => {
                if (res.data.status === 200) {
                  let list = res.data.data.list;
                  list.map((item) => {
                    this.dataList.push(item);
                  });
                  this.params.total = res.data.data.count;
                }
                this.isShowMore = false;
              });
            }
          } else {
            this.isEnd = false;
            this.isShowMore = false;
          }
        },
        // 返回顶部
        goBackTop() {
          document.documentElement.scrollTop = document.body.scrollTop = 0;
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
