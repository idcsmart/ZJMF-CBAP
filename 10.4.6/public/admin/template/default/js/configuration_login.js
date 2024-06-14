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
          submitLoading: false,
          formData: {
            login_phone_verify: "",
            register_email: "",
            register_phone: "",
            code_client_email_register: "",
            home_login_check_ip: "",
            admin_login_check_ip: "",
            code_client_phone_register: "",
            limit_email_suffix: "",
            email_suffix: "",
            home_login_check_common_ip: "",
            home_login_ip_exception_verify: [],
            home_enforce_safe_method: [],
            admin_enforce_safe_method: [],
            admin_allow_remember_account: "",
          },
          homeVerifyList: [
            {
              label: lang.setting_text11,
              value: "operate_password",
            },
          ],
          homeSafeMethodList: [
            {
              label: lang.setting_text12,
              value: "phone",
            },
            {
              label: lang.setting_text13,
              value: "email",
            },
            {
              label: lang.setting_text14,
              value: "operate_password",
            },
            {
              label: lang.setting_text15,
              value: "certification",
            },
            {
              label: lang.setting_text16,
              value: "oauth",
            },
          ],
          adminMethodList: [
            {
              label: lang.setting_text14,
              value: "operate_password",
            },
          ],
          rules: {
            home_login_ip_exception_verify: [
              {
                required: false,
                message: lang.select + lang.setting_text9,
                type: "error",
              },
            ],
            admin_enforce_safe_method: [
              {
                required: false,
                message: lang.select + lang.setting_text19,
                type: "error",
              },
            ],
            home_enforce_safe_method: [
              {
                required: false,
                message: lang.select + lang.setting_text19,
                type: "error",
              },
            ],
          },
          isCanUpdata: sessionStorage.isCanUpdata === "true",
          hasController: true,
        };
      },
      methods: {
        async getActivePlugin() {
          const res = await getActiveAddon();
          this.hasController = (res.data.data.list || [])
            .map((item) => item.name)
            .includes("TemplateController");
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = JSON.parse(JSON.stringify(this.formData));
              this.submitLoading = true;
              const res = await updateLoginOpt(params);
              this.$message.success(res.data.msg);
              this.getSetting();
              this.submitLoading = false;
            } catch (error) {
              error.data?.msg && this.$message.error(error.data.msg);
              this.submitLoading = false;
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        async getSetting() {
          try {
            const res = await getLoginOpt();
            this.formData = res.data.data;
          } catch (error) {}
        },
      },
      created() {
        this.getActivePlugin();
        this.getSetting();
        document.title =
          lang.login_setting + "-" + localStorage.getItem("back_website_name");
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
