(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('configuration-system')[0]
    Vue.prototype.lang = window.lang
    const host = location.host
    const fir = location.pathname.split('/')[1]
    const str = `${host}/${fir}/`
    new Vue({
      data () {
        return {
          formData: {
            lang_admin: '',
            lang_home_open: '',
            lang_home: '',
            maintenance_mode: '',
            maintenance_mode_message: '',
            website_name: '',
            website_url: '',
            terms_service_url: '',
            terms_privacy_url: '',
            system_logo: []
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
            terms_privacy_url: [
              { required: true, message: lang.input + lang.privacy_clause_address, type: 'error' },
              { validator: val => val.length <= 255, message: lang.verify3 + 255, type: 'warning' }
            ],
            maintenance_mode_message: [
              { required: true, message: lang.input + lang.maintenance_mode_info, type: 'error' },
            ],
            system_logo: [
              { required: true, message: lang.upload + lang.member_center + 'LOGO', type: 'error' }
            ],
          },
          // ??????????????????
          uploadUrl: 'http://' + str + 'v1/upload',
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          // ??????????????????
          systemData: {},
          // ????????????
          updateContent: {},
          isDown: false,
          updateData: {
            progress: '0.00%'
          },
          isShowProgress: false,
          timer: null
        }
      },
      methods: {
        //??????????????????
        onSuccess (file) {
        },
        //????????????
        handleFail ({ file }) {
          this.$message.error(`?????? ${file.name} ????????????`);
        },
        //??????????????????
        beforeUploadfile (e) {
        },
        formatImgResponse (res) {
          if (res.status === 200) {
            return { url: res.data.image_url }
          } else {
            return this.$message.error(res.msg)
          }
        },
        deleteLogo () {
          this.formData.system_logo = []
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const temp = JSON.parse(JSON.stringify(this.formData))
              temp.system_logo = temp.system_logo[0].url
              const res = await updateSystemOpt(temp)
              this.$message.success(res.data.msg)
              localStorage.setItem('back_website_name', temp.website_name)
              this.getSetting()
              document.title = lang.system_setting + '-' + temp.website_name
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
            const res = await getSystemOpt()
            Object.assign(this.formData, res.data.data)
            this.formData.maintenance_mode = String(res.data.data.maintenance_mode)
            this.formData.lang_home_open = String(res.data.data.lang_home_open)
            this.formData.system_logo = []
            if (res.data.data.system_logo) {
              this.formData.system_logo.push({
                url: res.data.data.system_logo
              })
            }
          } catch (error) {
          }
        },
        // ??????????????????
        async getVersion () {
          try {
            const res = await version()
            this.systemData = res.data.data
            if (this.systemData.is_download == 1) {
              this.isDown = true
            }
            localStorage.setItem('systemData', JSON.stringify(this.systemData))
          } catch (error) {

          }
        },
        // ??????????????????
        getUpContent () {
          upContent().then(res => {
            if (res.data.status == 200) {
              this.updateContent = res.data.data
              localStorage.setItem('updateContent', JSON.stringify(this.updateContent))
            }
          })
        },
        // ?????????????????????
        toUpdate () {
          location.href = '/upgrade/update.html'
          // location.href = 'update.html'
        },
        // ????????????
        beginDown () {
          if (this.systemData.last_version == this.systemData.version) {
            this.$message.warning("??????????????????????????????????????????????????????")
            return false
          }

          this.isShowProgress = true
          upDown().then(res => {

            if (res.data.status === 200) {

            }
          }).catch((error) => {
            this.$message.warning(error.data.msg)
          })

          // ??????????????????
          if (this.timer) {
            clearInterval(timer)
          }
          this.timer = setInterval(() => {
            upProgress().then(res => {
              if (res.data.status === 200) {
                this.updateData = res.data.data
                if (this.updateData.progress == '100.00%') {
                  clearInterval(this.timer)
                  this.isShowProgress = false
                  this.isDown = true
                }
              }
            }).catch(error => {
              console.log(error.data.data);
              if (error.data.data == '?????????????????????????????????') {
                this.isShowProgress = false
                clearInterval(this.timer)
              }
            })
          }, 2000)
        }
      },
      created () {
        this.getSetting()
        this.getVersion()
        this.getUpContent()
        document.title = lang.theme_setting + '-' + localStorage.getItem('back_website_name')
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
