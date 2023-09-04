(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('configuration-system')[0]
    Vue.prototype.lang = window.lang
    const host = location.origin
    const fir = location.pathname.split('/')[1]
    const str = `${host}/${fir}`
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
          // 图片上传相关
          uploadUrl: str + '/v1/upload',
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          // 系统版本信息
          systemData: {},
          // 更新信息
          updateContent: {},
          isDown: false,
          updateData: {
            progress: '0.00%'
          },
          isShowProgress: false,
          timer: null,
          isCanUpdata: sessionStorage.isCanUpdata === 'true',
        }
      },
      methods: {
        //文件上传成功
        onSuccess(file) {
        },
        //上传失败
        handleFail({ file }) {
          this.$message.error(`文件 ${file.name} ${lang.invoice_text23}`);
        },
        //上传文件之前
        beforeUploadfile(e) {
        },
        formatImgResponse(res) {
          if (res.status === 200) {
            return { url: res.data.image_url }
          } else {
            return this.$message.error(res.msg)
          }
        },
        deleteLogo() {
          this.formData.system_logo = []
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const temp = JSON.parse(JSON.stringify(this.formData))
              temp.system_logo = temp.system_logo[0].url
              const res = await updateSystemOpt(temp)
              this.$message.success(res.data.msg)
              localStorage.setItem('back_website_name', temp.website_name)
              localStorage.setItem('backLang', this.formData.lang_admin)
              // 修改 country_imgUrl
              let suffixImg = temp.lang_admin.split('-')[1].toUpperCase()
              if (suffixImg === 'HK' || suffixImg === 'TW') {
                suffixImg = 'CN'
              }
              localStorage.setItem('country_imgUrl', `/upload/common/country/${suffixImg}.png`)
              window.location.reload()
              document.title = lang.system_setting + '-' + temp.website_name
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
            this.formData.system_logo = []
            if (res.data.data.system_logo) {
              this.formData.system_logo.push({
                url: res.data.data.system_logo
              })
            }
          } catch (error) {
          }
        },
        // 获取版本信息
        async getVersion() {
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
        // 获取更新信息
        getUpContent() {
          upContent().then(res => {
            if (res.data.status == 200) {
              this.updateContent = res.data.data
              localStorage.setItem('updateContent', JSON.stringify(this.updateContent))
            }
          })
        },
        // 跳转到升级页面
        toUpdate() {
          location.href = '/upgrade/update.htm'
          // location.href = 'update.htm'
        },
        // 开始下载
        beginDown() {
          if (this.systemData.last_version == this.systemData.version) {
            this.$message.warning(lang.invoice_text27)
            return false
          }

          this.isShowProgress = true
          upDown().then(res => {

            if (res.data.status === 200) {

            }
          }).catch((error) => {
            this.$message.warning(error.data.msg)
          })

          // 轮询下载进度
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
              if (error.data.data == lang.invoice_text28) {
                this.isShowProgress = false
                clearInterval(this.timer)
              }
            })
          }, 2000)
        }
      },
      created() {
        this.getSetting()
        this.getVersion()
        this.getUpContent()
        document.title = lang.theme_setting + '-' + localStorage.getItem('back_website_name')
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
