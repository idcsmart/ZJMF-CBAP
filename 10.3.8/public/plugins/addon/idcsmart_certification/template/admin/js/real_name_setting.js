(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('real_name_setting')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data() {
        return {
          formData: {
            certification_open: 0,
            certification_approval: 0,
            certification_notice: 0,
            certification_age_open: 0,
            certification_age: 18,
            certification_update_client_name: 0,
            certification_upload: 0,
            certification_update_client_phone: 0,
            certification_uncertified_cannot_buy_product: 0,
            certification_recharge: 0
          },
          loading: false
        }
      },
      methods: {
        changeAge(val) {
          if (val && !this.formData.certification_age) {
            this.formData.certification_age = 18
          }
        },
        async getSetting() {
          try {
            const res = await getRealSetting()
            this.formData = Object.assign({}, this.formData, res.data.data)
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.loading = true
              const res = await saveRealSetting(this.formData)
              this.$message.success(res.data.msg)
              this.getSetting()
              this.loading = false
            } catch (error) {
              this.loading = false
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult)
            this.$message.warning(firstError)
          }
        }
      },
      created() {
        this.getSetting()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  }
})(window)
