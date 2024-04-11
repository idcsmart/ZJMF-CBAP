(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('withdrawal_create')[0]
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    new Vue({
      components: {
        comConfig,
      },
      data () {
        return {
          formData: {
            source: '',
            method: [], // 提现方式bank银行卡alipay支付宝
            process: '',
            min: '',
            max: '',
            cycle: '', // 提现周期day每天week每周month每月
            cycle_limit: '',
            withdraw_fee_type: 'fixed',
            withdraw_fee: '',
            percent: '',
            percent_min: ''
          },
          loading: false,
          ways: [
            { label: lang.bank, value: 'bank' },
            { label: lang.alipay, value: 'alipay' },
          ],
          process: [
            { label: lang.Artificial, value: 'artificial' },
            { label: lang.auto, value: 'auto' },
          ],
          cycleList: [
            { label: lang.cycle_day, value: 'day' },
            { label: lang.cycle_week, value: 'week' },
            { label: lang.cycle_month, value: 'month' },
          ],
          withdraw_fee: [
            { label: lang.fixed, value: 'fixed' },
            { label: lang.percent, value: 'percent' },
          ],
          rules: {
            source: [{ required: true, message: lang.select + lang.withdrawal_source, type: 'error' }],
            method: [{ required: true, message: lang.select + lang.withdrawal_way, type: 'error' }],
            process: [{ required: true, message: lang.select + lang.withdrawal_process, type: 'error' }],
            cycle: [{ required: true, message: lang.select + lang.withdrawal_cycle_limit, type: 'error' }],
            withdraw_fee_type: [{ required: true, message: lang.select + lang.withdraw_fee_type, type: 'error' }],
            cycle_limit: [
              {
                pattern: /^\d+$/, message: lang.verify7, type: 'warning'
              },
            ],
            withdraw_fee: [
              {
                pattern: /^\d+(\.\d{0,2})?$/, message: lang.verify17, type: 'warning'
              },
              {
                validator: (val) => val >= 0, message: lang.verify17, type: 'warning'
              }
            ],
            percent: [
              {
                pattern: /^\d+(\.\d{0,2})?$/, message: lang.verify17, type: 'warning'
              },
              {
                validator: (val) => val >= 0, message: lang.verify17, type: 'warning'
              }
            ],
            percent_min: [
              {
                pattern: /^\d+(\.\d{0,2})?$/, message: lang.verify17, type: 'warning'
              },
              {
                validator: (val) => val >= 0, message: lang.verify17, type: 'warning'
              }
            ],
          },
          popupProps: {
            overlayInnerStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` })
          },
          sourceList: [] // 来源数据
        }
      },
      created () {
        this.getSourceList()
      },
      methods: {
        back () {
          location.href = `withdrawal_manage.htm`
        },
        // 获取提现来源
        async getSourceList () {
          try {
            const res = await getSource()
            this.sourceList = res.data.data.source
          } catch (error) {
          }
        },
        checkMin (val) {
          if (val > this.formData.recharge_max) {
            return { result: false, message: lang.currency_tip, type: 'warning' }
          }
          return { result: true }
        },
        checkMax (val) {
          if (val < this.formData.recharge_min) {
            return { result: false, message: lang.currency_tip, type: 'warning' }
          }
          return { result: true }
        },
        changeMoney () {
          this.$refs.formValidatorStatus.validate({
            fields: ['recharge_min', 'recharge_max']
          });
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            const params = JSON.parse(JSON.stringify(this.formData))
            if (params.withdraw_fee_type === 'fixed') {
              delete params.percent
              delete params.percent_min
            } else {
              delete params.withdraw_fee
            }
            try {
              this.loading = true
              const res = await createRules(params)
              this.$message.success(res.data.msg)
              this.loading = false
              setTimeout(() => {
                location.href = `withdrawal_manage.htm`
              }, 300)
            } catch (error) {
              this.$message.error(error.data.msg)
              this.loading = false
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
      },

    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
