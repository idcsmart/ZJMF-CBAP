(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("order-details")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = moment;
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
          data: [],
          baseUrl: str,
          rootRul: url,
          tableLayout: true,
          hasCostPlugin: false,
          bordered: true,
          hover: true,
          visible: false,
          delVisible: false,
          total: 0,
          pageSizeOptions: [20, 50, 100],
          params: {
            id: "",
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          loading: false,
          columns: [
            {
              colKey: "create_time",
              title: lang.refund_time,
              ellipsis: true,
            },
            {
              colKey: "amount",
              title: lang.money,
              ellipsis: true,
            },
            {
              colKey: "type",
              title: lang.refund_to,
              ellipsis: true,
            },
            {
              colKey: "admin_name",
              title: lang.operator,
              width: 200,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 50,
              ellipsis: true,
            },
          ],
          rules: {
            amount: [
              {
                required: true,
                message: lang.input + lang.Refund + lang.money,
                type: "error",
              },
              {
                pattern: /^-?\d+(\.\d{0,2})?$/,
                message: lang.verify4,
                type: "warning",
              },
              {
                validator: (val) => val > 0,
                message: lang.verify4,
                type: "warning",
              },
            ],
            gateway: [
              {
                required: true,
                message: lang.select + lang.gateway,
                type: "error",
              },
            ],
            type: [
              {
                required: true,
                message: lang.select + lang.type,
                type: "error",
              },
            ],
            transaction_number: [
              {
                required: true,
                message: lang.input + lang.flow_number,
                type: "error",
              },
              {
                pattern: /^[A-Za-z0-9]+$/,
                message: lang.verify9,
                type: "warning",
              },
            ],
            currency_prefix: "",
            client_id: [
              {
                required: true,
                message: lang.select + lang.user,
                type: "error",
              },
            ],
          },
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
          submitLoading: false,
          formData: {
            id: "",
            type: "", // credit transaction
            amount: "",
            gateway: "",
            transaction_number: "",
          },
          typeObj: {
            credit: lang.account_balance,
            transaction: lang.gateway,
            original: lang.original_pay,
          },
          payList: [],
          curId: "",
          orderDetail: {},
          isEn: localStorage.getItem("backLang") === "en-us" ? true : false,
        };
      },
      mounted() {
        this.getFlowList();
        this.getPayway();
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
        goBack() {
          const url = sessionStorage.currentOrderUrl || "";
          sessionStorage.removeItem("currentOrderUrl");
          if (url) {
            location.href = url;
          } else {
            window.history.back();
          }
        },
        goOrder() {
          sessionStorage.removeItem("orderListParams");
          sessionStorage.removeItem("currentOrderUrl");
          location.href = "order.htm";
        },
        async getOrderDetail() {
          try {
            const res = await getOrderDetails({ id: this.id });
            this.orderDetail = res.data.data.order;
          } catch (error) {}
        },
        initiateRefund() {
          this.visible = true;
          this.formData.type = "credit";
          this.formData.amount = this.orderDetail.refundable_amount;
          this.formData.gateway = "";
          this.formData.transaction_number = "";
        },
        close() {
          this.visible = false;
        },
        delteFlow(row) {
          this.curId = row.id;
          this.delVisible = true;
        },
        async sureDelUser() {
          try {
            this.submitLoading = true;
            const res = await delOrderRecord({
              id: this.curId,
            });
            this.$message.success(res.data.msg);
            this.submitLoading = false;
            this.delVisible = false;
            this.getFlowList();
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 获取支付方式
        async getPayway() {
          try {
            const res = await getPayList();
            this.payList = res.data.data.list;
          } catch (error) {}
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const res = await orderRefund(this.formData);
              this.$message.success(res.data.msg);
              this.submitLoading = false;
              this.visible = false;
              this.getFlowList();
              this.getOrderDetail();
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        async getFlowList() {
          try {
            const res = await getOrderRefundRecord(this.params);
            this.data = res.data.data.list;
            this.total = res.data.data.count;
            this.loading = false;
          } catch (error) {
            this.loading = false;
            this.$message.error(res.data.msg);
          }
        },
        // 排序
        sortChange(val) {
          if (!val) {
            this.params.orderby = "id";
            this.params.sort = "desc";
          } else {
            this.params.orderby = val.sortBy;
            this.params.sort = val.descending ? "desc" : "asc";
          }
          this.getFlowList();
        },
        // 分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getFlowList();
        },
      },
      created() {
        this.getAddonList();
        this.id =
          this.params.id =
          this.formData.id =
            location.href.split("?")[1].split("=")[1];
        this.currency_prefix =
          JSON.parse(localStorage.getItem("common_set")).currency_prefix || "¥";
        document.title =
          lang.refund_record + "-" + localStorage.getItem("back_website_name");
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
