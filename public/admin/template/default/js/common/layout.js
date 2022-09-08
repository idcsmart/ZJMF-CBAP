(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    // 全局搜索
    function globalSearch(keywords) {
      return Axios.get(`/global_search?keywords=${keywords}`)
    }
    const aside = document.getElementById('aside')
    const footer = document.getElementById('footer')
    Vue.prototype.lang = window.lang
    if (!localStorage.getItem('backJwt')) {
      const host = location.host
      const fir = location.pathname.split('/')[1]
      const str = `${host}/${fir}/`
      location.href = 'http://' + str + '/login.html'
    }
    const MODE_OPTIONS = [
      { type: 'light', text: window.lang.theme_light, src: `${url}/img/assets-setting-light.svg` },
      { type: 'dark', text: window.lang.theme_dark, src: `${url}/img/assets-setting-dark.svg` }
    ]
    const COLOR_OPTIONS = ['default', 'cyan', 'green', 'yellow', 'orange', 'red', 'pink', 'purple']
    /* aside */
    aside && new Vue({
      data: {
        collapsed: false,
        isSearchFocus: false,
        searchData: '',
        /* 系统设置 */
        visible: false,
        formData: {
          mode: localStorage.getItem('theme-mode') || 'light',
          brandTheme: localStorage.getItem('theme-color') || 'default'
        },
        MODE_OPTIONS,
        COLOR_OPTIONS,
        colorList: {
          DEFAULT: {
            '@brand-color': '#0052D9'
          },
          CYAN: {
            '@brand-color': '#0594FA'
          },
          GREEN: {
            '@brand-color': '#00A870'
          },
          ORANGE: {
            '@brand-color': '#ED7B2F'
          },
          RED: {
            '@brand-color': '#E34D59'
          },
          PINK: {
            '@brand-color': '#ED49B4'
          },
          PURPLE: {
            '@brand-color': '#834EC2'
          },
          YELLOW: {
            '@brand-color': '#EBB105'
          },
        },
        curSrc: localStorage.getItem('country_imgUrl') || `${url}/img/CN.png`,
        langList: [],
        expanded: [],
        curValue: Number(localStorage.getItem('curValue')) || 2,
        iconList: ['user', 'view-module', 'cart', 'setting', 'folder-open', 'precise-monitor', 'control-platform'],
        navList: [],
        global: null,
        loadingSearch: false,
        noData: false,
        isShow: false,
        userName: localStorage.getItem('userName') || '-',
        // 修改密码弹窗
        editPassVisible: false,
        editPassFormData: {
          password: '',
          repassword: ''
        },
      },
      computed: {
        logUrl() {
          if (this.collapsed) {
            return `${url}/img/small-logo.png`
          } else {
            return `${url}/img/logo.png`
          }
        }
      },
      mounted() {
        const auth = JSON.parse(localStorage.getItem('backMenus'))
        this.navList = JSON.parse(localStorage.getItem('backMenus'))
        this.navList.forEach(item => {
          item.child && item.child.forEach(el => {
            if (el.id === this.curValue) {
              this.expanded = []
              this.expanded.push(item.id)
            }
          })
        })
        this.langList = JSON.parse(localStorage.getItem('common_set')).lang_admin
      },
      created() {
        this.getSystemConfig()

      },
      methods: {
        async getSystemConfig() {
          try {
            const res = await Axios.get('/configuration/system')
            document.title = res.data.data.website_name
          } catch (error) {
            console.log(error)
          }
        },
        getAuth(auth) {
          return auth.map(item => {
            item.child = item.child.filter(el => el.url)
            return item
          })
        },
        jumpHandler(e) {
          localStorage.setItem('curValue', e.id)
          const host = location.host
          const fir = location.pathname.split('/')[1]
          const str = `${host}/${fir}/`
          location.href = 'http://' + str + e.url || (e.child && str + e.child[0].url)
        },
        changeCollapsed() {
          this.collapsed = !this.collapsed
        },
        changeSearch(e) {
          this.isSearchFocus = e
          this.isShow = true
          this.noData = false
          this.globalSearchList()
        },
        // 全局搜索
        async globalSearchList() {
          try {
            this.loadingSearch = true
            const res = await globalSearch(this.isSearchFocus)
            this.global = res.data.data
            if (this.global.clients.length === 0 &&
              this.global.products.length === 0 &&
              this.global.hosts.length === 0) {
              this.noData = true
            }
            this.loadingSearch = false
          } catch (error) {
            console.log(error)
            this.loadingSearch = false
          }
        },
        changeSearchFocus(value) {
          if (!value) {
            this.searchData = ''
            setTimeout(() => {
              this.isShow = false
            }, 300)
          }
          this.isSearchFocus = value
        },
        // 个人中心
        handleNav() {

        },
        // 退出登录
        async handleLogout() {
          try {
            const res = await Axios.post('/logout')
            this.$message.success(res.data.msg)
            localStorage.removeItem('backJwt')
            setTimeout(() => {
              const host = location.host
              const fir = location.pathname.split('/')[1]
              const str = `${host}/${fir}/`
              location.href = 'http://' + str + 'login.html'
            }, 300)
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 语言切换
        changeLang(e) {
          const index = this.langList.findIndex(item => item.display_lang === e.value)
          if (localStorage.getItem('lang') !== e.value || !localStorage.getItem('lang')) {
            if (localStorage.getItem('lang')) {
              window.location.reload()
            }
            localStorage.setItem('country_imgUrl', this.langList[index].display_img)
            localStorage.setItem('lang', e.value)
          }
        },
        // 颜色配置
        toUnderline(name) {
          return name.replace(/([A-Z])/g, '_$1').toUpperCase();
        },
        getBrandColor(type, colorList) {
          const name = /^#[A-F\d]{6}$/i.test(type) ? type : this.toUnderline(type);
          return colorList[name || 'DEFAULT'];
        },
        /* 页面配置 */
        toggleSettingPanel() {
          this.visible = true
        },
        handleClick() {
          this.visible = true
        },
        getModeIcon(mode) {
          if (mode === 'light') {
            return SettingLightIcon
          }
          if (mode === 'dark') {
            return SettingDarkIcon
          }
          return SettingAutoIcon
        },
        // 主题
        onPopupVisibleChange(visible, context) {
          if (!visible && context.trigger === 'document') this.isColoPickerDisplay = visible
        },

        // 修改密码相关
        // 关闭修改密码弹窗
        editPassClose() {
          this.editPassVisible = false
          this.editPassFormData = {
            password: '',
            repassword: ''
          }
        },
        // 修改密码提交
        onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            const params = {
              password: this.editPassFormData.password,
              repassword: this.editPassFormData.repassword
            }
            editPass(params).then(res => {
              if (res.data.status === 200) {
                this.editPassClose()
                this.$message.success(res.data.msg)
                this.handleLogout()
              }
            }).catch(error => {
              this.$message.error(error.data.msg)
            })
            console.log(this.editPassFormData);
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        // 确认密码检查
        checkPwd(val) {
          if (val !== this.editPassFormData.password) {
            return { result: false, message: window.lang.password_tip, type: 'error' };
          }
          return { result: true }
        },
      },
      watch: {
        'formData.mode'() {
          if (this.formData.mode === 'auto') {
            document.documentElement.setAttribute('theme-mode', '')
          } else {
            document.documentElement.setAttribute('theme-mode', this.formData.mode)
          }
          localStorage.setItem('theme-mode', this.formData.mode)
        },
        'formData.brandTheme'() {
          document.documentElement.setAttribute('theme-color', this.formData.brandTheme);
          localStorage.setItem('theme-color', this.formData.brandTheme)
        }
      }
    }).$mount(aside)

    /* footer */
    footer && new Vue({
      data() {
        return {}
      }
    }).$mount(footer)

    var loading = document.getElementById('loading')
    setTimeout(() => {
      loading && (loading.style.display = 'none')
    }, 200)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
