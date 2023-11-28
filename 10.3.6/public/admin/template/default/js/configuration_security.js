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
            captcha_plugin: '',
            code_client_email_register: false
            // captcha_width: '',
            // captcha_height: '',
            // captcha_length: '',

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
          codeUrl: '',
          captchaList: [],
          popupProps: {
            overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` }),
          },
        }
      },
      methods: {
        getCode () {
          // 验证是否规范再提交获取
          this.$refs.formValidatorStatus.validate().then(res => {
            if (res === true) {
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
              const params = JSON.parse(JSON.stringify(this.formData))
              params.captcha_client_register = Number(params.captcha_client_register)
              params.captcha_client_login = Number(params.captcha_client_login)
              params.captcha_client_login_error = Number(params.captcha_client_login_error)
              params.captcha_admin_login = Number(params.captcha_admin_login)
              params.code_client_email_register = params.code_client_email_register ? 1 : 0
              const res = await updateSafeOpt(params)
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
        // 获取安全设置
        async getSetting () {
          try {
            const res = await getSafeOpt()
            const temp = res.data.data
            Object.assign(this.formData, temp)
            this.formData.captcha_client_register = Boolean(temp.captcha_client_register * 1)
            this.formData.captcha_client_login = Boolean(temp.captcha_client_login * 1)
            this.formData.captcha_client_login_error = String(temp.captcha_client_login_error * 1)
            this.formData.captcha_admin_login = Boolean(temp.captcha_admin_login * 1)
            this.formData.code_client_email_register = Boolean(temp.code_client_email_register * 1)
            this.formData.captcha_plugin = temp.captcha_plugin === 0 ? '' : temp.captcha_plugin
            // this.getPreviewCode()
          } catch (error) {
            console.log(error)
          }
        },
        // 获取验证码列表
        async getCaptcha () {
          try {
            const res = await getCaptchaList({
              page: 1,
              limit: 1000
            })
            this.captchaList = res.data.data.list
            const temp = this.captchaList.filter(item => item.name === this.formData.captcha_plugin)
            if (temp.length === 0) {
              this.formData.captcha_plugin = ''
            }
          } catch (error) {
          }
        }
      },
      created () {
        this.getSetting()
        this.getCaptcha()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
