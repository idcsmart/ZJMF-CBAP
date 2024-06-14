(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("cron")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          formData: {
            cron_shell: "",
            cron_status: "",
            cron_day_start_time: 0,
            // cron_task_shell: '',
            // cron_task_status: '',
            // 开关配置
            cron_due_suspend_swhitch: 0,
            cron_due_suspend_day: 0,
            cron_due_unsuspend_swhitch: 0,
            cron_due_terminate_swhitch: 0,
            cron_due_terminate_day: 0,
            cron_due_renewal_first_swhitch: 0,
            cron_due_renewal_first_day: 0,
            cron_due_renewal_second_swhitch: 0,
            cron_due_renewal_second_day: 0,
            cron_overdue_first_swhitch: 0,
            cron_overdue_first_day: 0,
            cron_overdue_second_swhitch: 0,
            cron_overdue_second_day: 0,
            cron_overdue_third_swhitch: 0,
            cron_overdue_third_day: 0,
            cron_ticket_close_swhitch: 0,
            cron_ticket_close_day: 0,
            cron_aff_swhitch: 0,
            cron_order_overdue_swhitch: 0,
            cron_order_overdue_day: 0,
            cron_order_unpaid_delete_swhitch: 0,
            cron_order_unpaid_delete_day: 0,
          },
          timeArr: [],
          rules: {
            cron_due_renewal_first_day: [
              {
                pattern: /^[0-9]\d*$/,
                message: lang.verify7,
                type: "warning",
              },
            ],
            cron_due_day_distance_2: [
              {
                required: true,
                message: lang.input + lang.host_renewal_one,
                type: "error",
              },
              {
                pattern: /^[0-9]\d*$/,
                message: lang.verify7,
                type: "warning",
              },
            ],
            cron_due_day_distance_3: [
              {
                required: true,
                message: lang.input + lang.host_renewal_one,
                type: "error",
              },
              {
                pattern: /^[0-9]\d*$/,
                message: lang.verify7,
                type: "warning",
              },
            ],
            cron_due_day_already_suspend: [
              {
                required: true,
                message: lang.input + lang.host_renewal_one,
                type: "error",
              },
              {
                pattern: /^[0-9]\d*$/,
                message: lang.verify7,
                type: "warning",
              },
            ],
            cron_due_day_already_terminate: [
              {
                required: true,
                message: lang.input + lang.host_renewal_one,
                type: "error",
              },
              {
                pattern: /^[0-9]\d*$/,
                message: lang.verify7,
                type: "warning",
              },
            ],
          },
          submitLoading: false,
        };
      },
      methods: {
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = { ...this.formData };
              delete params.cron_shell;
              delete params.cron_status;
              // delete params.cron_task_shell
              // delete params.cron_task_status
              this.submitLoading = true;
              const res = await updateTaskConfig(params);
              this.$message.success(res.data.msg);
              this.getSetting();
              this.submitLoading = false;
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        async getSetting() {
          try {
            const res = await getTaskConfig();
            const temp = res.data.data;
            Object.assign(this.formData, temp);
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
      },
      created() {
        for (let i = 0; i < 24; i++) {
          this.timeArr.push({
            value: i,
            label: `${i}:00`,
          });
        }
        this.getSetting();
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
