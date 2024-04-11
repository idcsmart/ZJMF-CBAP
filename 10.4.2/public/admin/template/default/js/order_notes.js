(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("order-details")[0];
    Vue.prototype.lang = window.lang;
    const host = location.host;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          id: "",
          loading: false,
          baseUrl: str,
          rootRul: url,
          hasCostPlugin: false,
          formData: {
            id: "",
            notes: "",
            is_recycle: 0
          },
          rules: {
            notes: [
              {
                required: true,
                message: `${lang.input}${lang.notes}`,
                type: "error",
              },
              {
                validator: (val) => val.length <= 1000,
                message: lang.verify3 + 1000,
                type: "waring",
              },
            ],
          },
        };
      },
      mounted() {
        this.getOrderDetail();
      },
      methods: {
        async getAddonList() {
          try {
            const res = await getAddon();
            if (
              res.data.data.list.filter((item) => item.name === "CostPay")
                .length > 0
            ) {
              this.hasCostPlugin = true;
            }
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        goOrder() {
          sessionStorage.removeItem("orderListParams");
          sessionStorage.removeItem("currentOrderUrl");
          location.href = "order.htm";
        },
        goBack() {
          const url = sessionStorage.currentOrderUrl || "";
          sessionStorage.removeItem("currentOrderUrl");
          if (url) {
            location.href = url;
          } else {
            window.history.back();
          }
        },
        async getOrderDetail() {
          try {
            const res = await getOrderDetails({ id: this.id });
            this.formData.notes = res.data.data.order.notes;
            this.formData.is_recycle = res.data.data.order.is_recycle;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.loading = true;
              const res = await changeOrderNotes(this.formData);
              this.$message.success(res.data.msg);
              this.loading = false;
            } catch (error) {
              this.loading = false;
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
      },
      created() {
        this.getAddonList();
        this.id = this.formData.id = location.href.split("?")[1].split("=")[1];
        document.title =
          lang.notes + "-" + localStorage.getItem("back_website_name");
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
