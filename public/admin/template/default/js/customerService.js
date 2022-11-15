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
                message: "弹窗源码为必填项",
                type: "error",
                trigger: "blur",
              },
              { required: true, message: "弹窗源码为必填项", type: "error" },
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
                this.$message.success("提交成功");
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
