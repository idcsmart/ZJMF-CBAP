(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('cron')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data () {
        return {
          formData: {
            cron_shell: '',
            cron_status: '',
            cron_due_day_distance_1: '',
            cron_due_day_distance_1_switch: 0,
            cron_due_day_distance_2: '',
            cron_due_day_distance_2_switch: 0,
            cron_due_day_distance_3: '',
            cron_due_day_distance_3_switch: 0,
            cron_due_day_already_suspend: '',
            cron_due_day_already_suspend_switch: 0,
            cron_due_day_already_terminate: '',
            cron_due_day_already_terminate_switch: 0
          },
          rules: {
            cron_due_day_distance_1: [
              { required: true, message: lang.input + lang.host_renewal_one, type: 'error' },
              {
                pattern: /^[0-9]\d*$/, message: lang.verify7, type: 'warning'
              }
            ],
            cron_due_day_distance_2: [
              { required: true, message: lang.input + lang.host_renewal_one, type: 'error' },
              {
                pattern: /^[0-9]\d*$/, message: lang.verify7, type: 'warning'
              }
            ],
            cron_due_day_distance_3: [
              { required: true, message: lang.input + lang.host_renewal_one, type: 'error' },
              {
                pattern: /^[0-9]\d*$/, message: lang.verify7, type: 'warning'
              }
            ],
            cron_due_day_already_suspend: [
              { required: true, message: lang.input + lang.host_renewal_one, type: 'error' },
              {
                pattern: /^[0-9]\d*$/, message: lang.verify7, type: 'warning'
              }
            ],
            cron_due_day_already_terminate: [
              { required: true, message: lang.input + lang.host_renewal_one, type: 'error' },
              {
                pattern: /^[0-9]\d*$/, message: lang.verify7, type: 'warning'
              }
            ]
          }
        }
      },
      methods: {
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = { ...this.formData }
              delete params.cron_shell
              delete params.cron_status
              const res = await updateTaskConfig(params)
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
        async getTaskConfig () {
          try {
            const res = await getTaskConfig()
            const temp = res.data.data
            Object.assign(this.formData, temp)
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        }
      },
      created () {
        this.getTaskConfig()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
