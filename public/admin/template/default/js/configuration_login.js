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
            login_phone_verify: ''
          }
        }
      },
      methods: {
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const res = await updateLoginOpt(this.formData)
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
