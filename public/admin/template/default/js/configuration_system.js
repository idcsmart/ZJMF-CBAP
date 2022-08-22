(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('configuration-system')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data() {
        return {
          formData: {
            lang_admin: '',
            lang_home_open: '',
            lang_home: '',
            maintenance_mode: '',
            maintenance_mode_message: '',
            website_name: '',
            website_url: '',
            terms_service_url: ''
          },
          adminArr: JSON.parse(localStorage.getItem('common_set')).lang_admin,
          homeArr: JSON.parse(localStorage.getItem('common_set')).lang_home,
          rules: {
            website_name: [
              { required: true, message: lang.input + lang.site_name, type: 'error' },
              { validator: val => val.length <= 255, message: lang.verify3 + 255, type: 'warning' }
            ],
            website_url: [
              { required: true, message: lang.input + lang.domain, type: 'error' },
              { validator: val => val.length <= 255, message: lang.verify3 + 255, type: 'warning' },
            ],
            terms_service_url: [
              { required: true, message: lang.input + lang.service_address, type: 'error' },
              { validator: val => val.length <= 255, message: lang.verify3 + 255, type: 'warning' }
            ],
            maintenance_mode_message: [
              { required: true, message: lang.input + lang.maintenance_mode_info, type: 'error' },
            ]
          }
        }
      },
      methods: {
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const res = await updateSystemOpt(this.formData)
              this.$message.success(res.data.msg)
              this.getSetting()
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        async getSetting() {
          try {
            const res = await getSystemOpt()
            Object.assign(this.formData, res.data.data)
            this.formData.maintenance_mode = String(res.data.data.maintenance_mode)
            this.formData.lang_home_open = String(res.data.data.lang_home_open)
          } catch (error) {
          }
        }
      },
      created() {
        this.getSetting()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
