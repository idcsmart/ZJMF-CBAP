(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('withdrawal_create')[0]
    Vue.prototype.lang = window.lang
    new Vue({
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
          id: '',
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
            overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` })
          },
          sourceList: [] // 来源数据
        }
      },
      created () {
        this.id = location.href.split('?')[1].split('=')[1]
        this.getSourceList()
        this.getRuleDetail()
      },
      methods: {
        // 获取规则详情
        async getRuleDetail () {
          try {
            const res = await ruleDetail(this.id)
            const temp = res.data.data.rule
            temp.min = Number(temp.min) || ''
            temp.max = Number(temp.max) || ''
            this.formData = temp
          } catch (error) {

          }
        },
        back () {
          location.href = `withdrawal_manage.html`
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
          if (val >= this.formData.max) {
            return { result: false, message: lang.rule_tip, type: 'warning' }
          }
          return { result: true }
        },
        checkMax (val) {
          if (val <= this.formData.min) {
            return { result: false, message: lang.rule_tip, type: 'warning' }
          }
          return { result: true }
        },
        changeMoney () {
          this.$refs.formValidatorStatus.validate({
            fields: ['min', 'max']
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
              const res = await updateRules(params)
              this.$message.success(res.data.msg)
              this.loading = false
              setTimeout(() => {
                location.href = `withdrawal_manage.html`
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
