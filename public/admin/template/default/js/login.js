function captchaCheckSuccsss (bol, captcha, token, login) {
  vm.captchaBol = bol
  vm.formData.captcha = captcha
  vm.formData.token = token
  vm.direct_login = login
}
(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const login = document.getElementById('login')
    Vue.prototype.lang = window.lang
    if (localStorage.getItem('backJwt')) {
      const host = location.host
      const fir = location.pathname.split('/')[1]
      const str = `${host}/${fir}/`
      location.href = 'http://' + str
      return
    }
    const vm = new Vue({
      data () {
        return {
          check: false,
          type: 'password',
          loading: false,
          formData: {
            name: localStorage.getItem('name') || '',
            password: localStorage.getItem('password') || '',
            remember_password: 0,
            token: localStorage.getItem('backToken') || '',
            captcha: localStorage.getItem('backCaptcha') || ''
          },
          captcha: '',
          rules: {
            name: [{ required: true, message: lang.input + lang.acount, type: 'error' }],
            password: [{ required: true, message: lang.input + lang.password, type: 'error' }],
            captcha: [{ required: true, message: lang.captcha, type: 'error' }]
          },
          captcha_admin_login: 0, // 登录是否需要验证码
          website_name: "",
          direct_login: false // 是否验证通过直接登录
        }
      },
      created () {
        this.getLoginInfo()
        if (this.formData.name) {
          this.check = true
        }
        if (!localStorage.getItem('lang')) {
          localStorage.setItem('lang', 'zh-cn')
        }
      },
      watch: {
        captcha_admin_login (val) {
          if (val == 1) {
            this.getCaptcha()
          }
        },
        direct_login (bol) {
          if (bol) {
            this.submitLogin()
          }
        }
      },
      methods: {
        async getCaptcha () {
          try {
            const res = await getCaptcha()
            const temp = res.data.data.html
            $('#admin-captcha').html(temp)
            // this.formData.token = temp.token
            // this.captcha = temp.captcha
          } catch (error) {
          }
        },
        async getLoginInfo () {
          try {
            const res = await getLoginInfo()
            this.captcha_admin_login = res.data.data.captcha_admin_login
            localStorage.setItem('back_website_name', res.data.data.website_name)
            document.title = lang.login + '-' + res.data.data.website_name
          } catch (error) {
          }
        },
        // 发起登录
        async submitLogin () {
          try {
            this.loading = true
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
            const authTemp = auth.data.data.rule.map(item => {
              item = item.split('\\')[3]
              return item
            })
            localStorage.setItem('backAuth', JSON.stringify(authTemp))
            this.$message.success(res.data.msg)
            // 获取导航
            const menus = await getMenus()
            localStorage.setItem('backMenus', JSON.stringify(menus.data.data.menu))
            this.loading = false
            localStorage.setItem('curValue', 0)
            location.href = 'index.html'
          } catch (error) {
            (this.captcha_admin_login == 1) && this.getCaptcha()
            this.$message.error(error.data.msg)
            this.loading = false
          }
        },
        // 提交按钮
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            // 开启验证码的时候
            if (this.captcha_admin_login === '1') {
              if (!this.captchaBol) {
                return this.$message.warning(lang.input + lang.correct_code);
              }
            }
            this.submitLogin()
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
    }).$mount(login);
    window.vm = vm
    typeof old_onload == 'function' && old_onload()

  };
})(window);
