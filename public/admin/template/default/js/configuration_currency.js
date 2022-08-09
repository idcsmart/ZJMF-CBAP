(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('configuration-currency')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data () {
        return {
          formData: {
            currency_code: '',
            currency_prefix: '',
            recharge_open: 0,
            recharge_min: ''
          },
          rules: {
            currency_code: [{ required: true, message: lang.input + lang.currency_code, type: 'error' }],
            currency_prefix: [{ required: true, message: lang.input + lang.currency_prefix, type: 'error' }],
            recharge_min: [
              { required: true, message: lang.input + lang.recharge_min, type: 'error' },
              {
                pattern: /^\d+(\.\d{0,2})?$/, message: lang.verify4, type: 'warning'
              },
              {
                validator: (val) => val > 0, message: lang.verify4, type: 'warning'
              }
            ],
          }
        }
      },
      methods: {
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const res = await updateCurrencyOpt(this.formData)
              this.$message.success(res.data.msg)
              this.getSetting()
              this.getCommonSetting()
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
            const res = await getCurrencyOpt()
            const temp = res.data.data
            Object.assign(this.formData, temp)
          } catch (error) {

          }
        },
        async getCommonSetting () {
          try {
            const res = await Axios.get('/common')
            localStorage.setItem('common_set', JSON.stringify(res.data.data))
          } catch (error) {
          }
        },
      },
      created () {
        this.getSetting()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
