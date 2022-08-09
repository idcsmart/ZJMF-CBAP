(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const login = document.getElementById('login')
    Vue.prototype.lang = window.lang
    new Vue({
      data () {
        return {
          check: false,
          type: 'password',
          formData: {
            name: localStorage.getItem('name') || '',
            password: localStorage.getItem('password') || '',
            remember_password: 0,
            token: '',
            captcha: ''
          },
          captcha: '',
          rules: {
            name: [{ required: true, message: lang.input + lang.acount, type: 'error' }],
            password: [{ required: true, message: lang.input + lang.password, type: 'error' }],
            captcha: [{ required: true, message: lang.captcha, type: 'error' }]
          },
          captcha_admin_login: 0 // 登录是否需要验证码
        }
      },
      created () {
        this.getLoginInfo()
        if (this.formData.name) {
          this.check = true
        }
      },
      watch: {
        captcha_admin_login (val) {
          if (val == 1) {
            this.getCaptcha()
          }
        }
      },
      methods: {
        async getCaptcha () {
          try {
            const res = await getCaptcha()
            const temp = res.data.data
            this.formData.token = temp.token
            this.captcha = temp.captcha
          } catch (error) {
          }
        },
        async getLoginInfo () {
          try {
            const res = await getLoginInfo()
            this.captcha_admin_login = res.data.data.captcha_admin_login
          } catch (error) {
          }
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.formData.remember_password = this.check === true ? 1 : 0
              const params = { ...this.formData }
              if (!this.captcha_admin_login) {
                delete params.token
                delete params.captcha
              }
              const res = await logIn(params)
              localStorage.setItem('backJwt', res.data.data.jwt)
              // 记住账号
              if (this.formData.remember_password) {
                localStorage.setItem('name', this.formData.name)
                localStorage.setItem('password', this.formData.password)
              } else { // 未勾选记住
                localStorage.removeItem('name')
                localStorage.removeItem('password')
              }
              localStorage.setItem('userName', this.formData.name)
              await this.getCommonSetting()
              // 获取权限
               const auth = await getAuthRole()
               localStorage.setItem('auth', JSON.stringify(auth.data.data.list))
               this.$message.success(res.data.msg)
               localStorage.setItem('curValue', 2)
               location.href = 'client.html'
            } catch (error) {
              (this.captcha_admin_login == 1) && this.getCaptcha()
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        // 获取通用配置
        async getCommonSetting () {
          try {
            const res = await getCommon()
            localStorage.setItem('common_set', JSON.stringify(res.data.data))
          } catch (error) {
          }
        },
      }
    }).$mount(login)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
