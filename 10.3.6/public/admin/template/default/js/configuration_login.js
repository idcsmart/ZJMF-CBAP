(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('configuration-login')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data () {
        return {
          formData: {
            register_email: '',
            register_phone: '',
            login_phone_verify: '',
            home_login_check_ip: '',
            admin_login_check_ip: '',
            code_client_email_register: false,
            code_client_phone_register:false
          },
          isCanUpdata: sessionStorage.isCanUpdata === 'true',
        }
      },
      methods: {
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = JSON.parse(JSON.stringify(this.formData))
              params.code_client_email_register = params.code_client_email_register ? 1 : 0
              params.code_client_phone_register = params.code_client_phone_register ? 1 : 0
              const res = await updateLoginOpt(params)
              this.$message.success(res.data.msg)
              this.getSetting()
            } catch (error) {
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        async getSetting () {
          try {
            const res = await getLoginOpt()
            this.formData.register_email = String(res.data.data.register_email)
            this.formData.register_phone = String(res.data.data.register_phone)
            this.formData.login_phone_verify = String(res.data.data.login_phone_verify)
            this.formData.home_login_check_ip = String(res.data.data.home_login_check_ip)
            this.formData.admin_login_check_ip = String(res.data.data.admin_login_check_ip)
            this.formData.code_client_email_register = res.data.data.code_client_email_register === 1
            this.formData.code_client_phone_register = res.data.data.code_client_phone_register === 1
          } catch (error) {
          }
        }
      },
      created () {
        this.getSetting()
        document.title = lang.login_setting + '-' + localStorage.getItem('back_website_name')
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
