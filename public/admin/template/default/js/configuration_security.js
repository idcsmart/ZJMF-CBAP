(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('configuration-security')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data () {
        return {
          formData: {
            captcha_client_register: false,
            captcha_client_login: false,
            captcha_client_login_error: false,
            captcha_admin_login: false,
            captcha_width: '',
            captcha_height: '',
            captcha_length: '',
            code_client_email_register: 0
          },
          rules: {
            lang_admin: [{ required: true }],
            captcha_width: [
              { required: true, message: lang.input + lang.image_width, type: 'error' },
              { pattern: /^(2\d{2}|3\d{2}|400)$/, message: lang.input + '200-400' + lang.verify2, type: 'warning' }
            ],
            captcha_length: [
              { required: true, message: lang.input + lang.image_num, type: 'error' },
              { pattern: /^([4-6]{1})$/, message: lang.input + '4-6' + lang.verify2, type: 'warning' }
            ],
            captcha_height: [
              { required: true, message: lang.input + lang.image_heigt },
              { pattern: /^([5-9]\d{1}|100)$/, message: lang.input + '50-100' + lang.verify2, type: 'warning' }
            ],
          },
          codeUrl: ''
        }
      },
      methods: {
        getCode(){
          // 验证是否规范再提交获取
          this.$refs.formValidatorStatus.validate().then(res=>{
            if (res === true){
             this.getPreviewCode()
            }
          })
        },
        // 图形验证码预览
        async getPreviewCode () {
          try {
            const { captcha_width, captcha_height, captcha_length } = this.formData
            const res = await previewCode({ captcha_width, captcha_height, captcha_length })
            this.codeUrl = res.data.data.captcha
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.captcha_client_register = Number(this.captcha_client_register)
              this.captcha_client_login = Number(this.captcha_client_login)
              this.captcha_client_login_error = Number(this.captcha_client_login_error)
              this.captcha_admin_login = Number(this.captcha_admin_login)
              const res = await updateSafeOpt(this.formData)
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
        async getSetting () {
          try {
            const res = await getSafeOpt()
            const temp = res.data.data
            Object.assign(this.formData, temp)
            this.formData.captcha_client_register = Boolean(temp.captcha_client_register)
            this.formData.captcha_client_login = Boolean(temp.captcha_client_login)
            this.formData.captcha_client_login_error = String(temp.captcha_client_login_error)
            this.formData.captcha_admin_login = Boolean(temp.captcha_admin_login)
            this.getPreviewCode()
          } catch (error) {

          }
        }
      },
      created () {
        this.getSetting()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
