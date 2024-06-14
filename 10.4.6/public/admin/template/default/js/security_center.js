(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("configuration-login")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          // 修改密码弹窗
          editPassVisible: false,
          type: 1,
          loading: false,
          set_operate_password: false,
          editPassFormData: {
            password: "",
            repassword: "",
            origin_password: "",
          },
        };
      },
      created() {
        document.title =
          lang.setting_text24 + "-" + localStorage.getItem("back_website_name");
        this.getLoginInfo();
      },
      methods: {
        // type 1:修改登录密码 2:修改操作密码
        handelChangePass(type) {
          this.type = type;
          this.editPassVisible = true;
          setTimeout(() => {
            this.$refs.userDialog.reset();
          }, 0);
        },
        getLoginInfo() {
          getAdminInfo().then((res) => {
            this.set_operate_password = res.data.data.set_operate_password;
            if (!this.set_operate_password) {
              this.type = 2;
              this.editPassVisible = true;
            }
          });
        },
        // 修改密码相关
        // 关闭修改密码弹窗
        editPassClose() {
          this.editPassVisible = false;
          this.$refs.userDialog.reset();
        },
        // 修改密码提交
        onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            this.loading = true;
            const params =
              this.type === 1
                ? {
                    password: this.editPassFormData.password,
                    repassword: this.editPassFormData.repassword,
                    origin_password: this.editPassFormData.origin_password,
                  }
                : {
                    origin_operate_password:
                      this.editPassFormData.origin_password,
                    operate_password: this.editPassFormData.password,
                    re_operate_password: this.editPassFormData.repassword,
                  };
            const subApi = this.type === 1 ? editPass : changePassword;
            subApi(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.loading = false;
                  this.editPassClose();
                  this.getLoginInfo();
                  this.$message.success(res.data.msg);
                  this.type === 1 && this.handleLogout();
                }
              })
              .catch((error) => {
                this.loading = false;
                this.$message.error(error.data.msg);
              });
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        // 确认密码检查
        checkPwd(val) {
          if (val !== this.editPassFormData.password) {
            return {
              result: false,
              message: lang.setting_text29,
              type: "error",
            };
          }
          return { result: true };
        },
        // 退出登录
        async handleLogout() {
          try {
            const res = await Axios.post("/logout");
            this.$message.success(res.data.msg);
            localStorage.removeItem("backJwt");
            setTimeout(() => {
              const host = location.origin;
              const fir = location.pathname.split("/")[1];
              const str = `${host}/${fir}/`;
              location.href = str + "login.htm";
            }, 300);
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
