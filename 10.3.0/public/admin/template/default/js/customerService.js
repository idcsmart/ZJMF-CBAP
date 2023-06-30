(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("transaction")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      data() {
        return {
          message: "template...",
          formData: {
            content: "",
          },
          rules: {
            content: [
              {
                required: true,
                message: lang.invoice_text30,
                type: "error",
                trigger: "blur",
              },
              { required: true, message: lang.invoice_text30, type: "error" },
            ],
          },
        };
      },
      created() {},
      methods: {
        onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            onlineServiceAPI(this.formData).then((res) => {
              if (res.data.status == 200) {
                this.$message.success(lang.submit_success);
              }
            });
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
