(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("configuration-currency")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data () {
        return {
          submitLoading: false,
          formData: {
            currency_code: "",
            currency_prefix: "",
            currency_suffix: "",
            recharge_open: 0,
            recharge_min: "",
            recharge_max: "",
          },
          rules: {
            currency_code: [
              {
                required: true,
                message: lang.input + lang.currency_code,
                type: "error",
              },
            ],
            currency_prefix: [
              {
                required: true,
                message: lang.input + lang.currency_prefix,
                type: "error",
              },
            ],
            currency_suffix: [
              {
                required: true,
                message: lang.input + lang.currency_suffix,
                type: "error",
              },
            ],
            recharge_min: [
              {
                required: true,
                message: lang.input + lang.recharge_min,
                type: "error",
              },
              {
                pattern: /^\d+(\.\d{0,2})?$/,
                message: lang.verify4,
                type: "warning",
              },
              {
                validator: (val) => val > 0,
                message: lang.verify4,
                type: "warning",
              },
            ],
          },
        };
      },
      methods: {
        checkMin (val) {
          if (val > this.formData.recharge_max) {
            return {
              result: false,
              message: lang.currency_tip,
              type: "warning",
            };
          }
          return { result: true };
        },
        checkMax (val) {
          if (val < this.formData.recharge_min) {
            return {
              result: false,
              message: lang.currency_tip,
              type: "warning",
            };
          }
          return { result: true };
        },
        changeMoney () {
          this.$refs.formValidatorStatus.validate({
            fields: ["recharge_min", "recharge_max"],
          });
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const res = await updateCurrencyOpt(this.formData);
              this.$message.success(res.data.msg);
              this.getSetting();
              this.getCommonSetting();
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
        async getSetting () {
          try {
            const res = await getCurrencyOpt();
            const temp = res.data.data;
            Object.assign(this.formData, temp);
          } catch (error) { }
        },
        async getCommonSetting () {
          try {
            const res = await Axios.get("/common");
            localStorage.setItem("common_set", JSON.stringify(res.data.data));
          } catch (error) { }
        },
      },
      created () {
        this.getSetting();
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
