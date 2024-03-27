(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("agent-list")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          // 分页相关
          params: {
            keywords: "",
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          submitLoading: false,
          total: 0,
          pageSizeOptions: [20, 50, 100],
          // 表格相关
          data: [],
          columns: [
            {
              title: lang.upstream_text28,
              colKey: "name",
              width: 300,
              ellipsis: true,
            },
            {
              title: "CPU",
              cell: "cpu",
              width: 100,
              ellipsis: true,
            },
            {
              title: lang.upstream_text29,
              cell: "memory",
              width: 100,
              ellipsis: true,
            },
            {
              title: lang.upstream_text30,
              cell: "disk",
              width: 100,
              ellipsis: true,
            },
            {
              title: lang.upstream_text31,
              cell: "bandwidt",
              width: 100,
              ellipsis: true,
            },
            {
              title: lang.upstream_text32,
              cell: "flow",
              width: 120,
              ellipsis: true,
            },
            {
              title: lang.upstream_text33,
              cell: "price",
              width: 180,
            },
            {
              title: lang.upstream_text34,
              colKey: "description",
              width: 300,
              ellipsis: true,
            },
            {
              title: lang.upstream_text35,
              cell: "op",
              width: 120,
            },
          ],
          currency_prefix:
            JSON.parse(localStorage.getItem("common_set")).currency_prefix ||
            "¥",
          currency_suffix: JSON.parse(localStorage.getItem("common_set"))
            .currency_suffix,
          tableLayout: false,
          hover: true,
          loading: false,
          productModel: false,
          firstGroup: [],
          secondGroup: [],
          curObj: {},
          tempSecondGroup: [],
          methodOption: [
            {
              id: "agent",
              name: lang.upstream_text36,
            },
            {
              id: "client",
              name: lang.upstream_text37,
            },
          ],
          productData: {
            // 新建分组
            id: "",
            token: "",
            secret: "",
            name: "",
            username: "",
            firstId: "",
            profit_percent: "",
            auto_setup: 0,
            description: "",
            certification: 0,
            certification_method: "",
            product_group_id: "",
          },
          productRules: {
            username: [
              {
                required: true,
                message: lang.input + lang.upstream_text10,
                type: "error",
              },
            ],
            token: [
              {
                required: true,
                message: lang.input + lang.upstream_text14,
                type: "error",
              },
            ],
            secret: [
              {
                required: true,
                message: lang.input + lang.upstream_text16,
                type: "error",
              },
            ],
            name: [
              {
                required: true,
                message: lang.input + lang.upstream_text28,
                type: "error",
              },
            ],
            profit_percent: [
              {
                required: true,
                message: lang.input + lang.upstream_text18,
                type: "error",
              },
              {
                pattern:
                  /^([1-9]\d*(\.\d{1,2})?|([0](\.([0][1-9]|[1-9]\d{0,1}))))$/,
                message: lang.upstream_text39,
                type: "error",
              },
            ],
            certification_method: [
              {
                required: true,
                message: lang.select + lang.upstream_text38,
                type: "error",
              },
            ],
            firstId: [
              {
                required: true,
                message: lang.select + lang.first_group,
                type: "error",
              },
            ],
            product_group_id: [
              {
                required: true,
                message: lang.select + lang.second_group,
                type: "error",
              },
            ],
          },
        };
      },
      filters: {
        filterMoney(money) {
          if (isNaN(money)) {
            return "0.00";
          } else {
            const temp = `${money}`.split(".");
            return parseInt(temp[0]).toLocaleString() + "." + (temp[1] || "00");
          }
        },
      },
      mounted() {},
      created() {
        this.getOrderList();
        this.getFirPro();
        this.getSecPro();
      },
      methods: {
        closeProduct() {
          this.productModel = false;
          this.$refs.productForm.reset();
        },
        changeFirId(val) {
          this.tempSecondGroup = this.secondGroup.filter(
            (item) => item.parent_id === val
          );
          this.productData.product_group_id = "";
        },
        editGoods(row) {
          this.curObj = row;
          this.productData.token = row.supplier.token;
          this.productData.secret = row.supplier.secret;
          this.productData.username = row.supplier.username;
          this.productData.id = row.id;
          this.productModel = true;
        },
        // 获取一级分组
        async getFirPro() {
          try {
            const res = await getFirstGroup();
            this.firstGroup = res.data.data.list;
          } catch (error) {}
        },
        // 获取二级分组
        async getSecPro() {
          try {
            const res = await getSecondGroup();
            this.secondGroup = res.data.data.list;
          } catch (error) {}
        },
        // 搜索框 搜索
        seacrh() {
          this.params.page = 1;
          // 重新拉取申请列表
          this.getOrderList();
        },
        async submitProduct({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = { ...this.productData };
              delete params.firstId;
              this.submitLoading = true;
              const res = await recomProduct(params);
              this.$message.success(res.data.msg);
              this.productModel = false;
              this.$refs.productForm.reset();
              this.getOrderList();
              this.submitLoading = false;
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        // 清空搜索框
        clearKey() {
          this.params.keywords = "";
          this.params.page = 1;
          // 重新拉取申请列表
          this.getOrderList();
        },
        // 底部分页 页面跳转事件
        changePage() {
          this.getOrderList();
        },
        // 获取申请列表
        async getOrderList() {
          this.loading = true;
          const res = await recomProList(this.params);
          this.data = res.data.data.list;
          this.total = res.data.data.count;
          this.loading = false;
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
