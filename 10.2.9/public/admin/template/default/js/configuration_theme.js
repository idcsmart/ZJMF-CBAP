(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('configuration-theme')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data() {
        return {
          formData: {
            admin_theme: '',
            clientarea_theme: '',
            web_theme: ''
          },
          isCanUpdata: sessionStorage.isCanUpdata === 'true',
          admin_theme: [],
          clientarea_theme: [],
          web_theme_list: [],
          rules: {
            clientarea_theme: [
              { required: true, message: lang.input + lang.site_name, type: 'error' },
              { validator: val => val.length <= 255, message: lang.verify3 + 255, type: 'warning' }
            ],
          },
          popupProps: {
            overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` }),
          },
        }
      },
      methods: {
        chooseTheme(e) {
          this.formData.clientarea_theme = e.name
        },
        chooseWebTheme(e) {
          this.formData.web_theme = e.name
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const res = await updateThemeConfig(this.formData)
              this.$message.success(res.data.msg)
              this.getTheme()
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        async getTheme() {
          try {
            const res = await getThemeConfig()
            const temp = res.data.data
            this.formData.admin_theme = temp.admin_theme
            this.formData.web_theme = temp.web_theme
            this.formData.clientarea_theme = temp.clientarea_theme
            this.admin_theme = temp.admin_theme_list
            this.clientarea_theme = temp.clientarea_theme_list
            this.web_theme_list = temp.web_theme_list
          } catch (error) {
          }
        }
      },
      created() {
        this.getTheme()
        document.title = lang.theme_setting + '-' + localStorage.getItem('back_website_name')
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
