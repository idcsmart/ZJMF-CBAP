(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("upstream_order")[0];
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
            supplier_id: "",
            keywords: "",
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          id: "",
          total: 0,
          rootRul: url,
          supplier_id: "",
          delete_host: false, // 是否删除产品:0否1是
          delVisible: false,
          delId: null,
          fullLoading: false,
          father_client_id: "",
          optType: "",
          // 变更价格
          formData: {
            id: "",
            amount: "",
            description: "",
          },
          pageSizeOptions: [20, 50, 100],
          // 表格相关
          data: [],
          columns: [
            {
              colKey: "id",
              title: "ID",
              width: 100,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "client_name",
              title: lang.user + "(" + lang.contact+ ")",
              width: 250,
              ellipsis: true,
            },
            {
              colKey: "icon",
              width: 16,
              className: "icon-width",
            },
            {
              colKey: "product_names",
              title: lang.product_name,
              width: 200,
              ellipsis: true,
            },
            {
              colKey: "amount",
              title: lang.money,
              ellipsis: true,
              width: 150,
            },
            {
              colKey: "profit",
              title: lang.upstream_text73,
              ellipsis: true,
              width: 150,
            },
            {
              colKey: "gateway",
              title: lang.pay_way,
              width: 170,
              ellipsis: true,
            },
            {
              colKey: "create_time",
              title: lang.order_time,
              width: 170,
              ellipsis: true,
            },
            {
              colKey: "status",
              title: lang.status,
              width: 120,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 120,
            },
          ],
          rules: {
            amount: [
              {
                required: true,
                message: lang.input + lang.money,
                type: "error",
              },
              {
                pattern: /^-?\d+(\.\d{0,2})?$/,
                message: lang.verify10,
                type: "warning",
              },
              {
                validator: (val) => val * 1 !== 0,
                message: lang.verify10,
                type: "warning",
              },
            ],
            description: [
              {
                required: true,
                message: lang.input + lang.description,
                type: "error",
              },
              {
                validator: (val) => val.length <= 1000,
                message: lang.verify3 + 1000,
                type: "warning",
              },
            ],
          },
          currency_prefix:
            JSON.parse(localStorage.getItem("common_set")).currency_prefix ||
            "¥",
          currency_suffix: JSON.parse(localStorage.getItem("common_set"))
            .currency_suffix,
          money: {},
          tableLayout: false,
          hover: true,
          loading: false,
          priceModel: false,
          curInfo: {},
          maxHeight: "",
          submitLoading: false,
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
      created() {
        this.getOrderList();
        this.getSellInfo();
      },
      methods: {
        // 解析url
        getQuery(url) {
          const str = url.substr(url.indexOf("?") + 1);
          const arr = str.split("&");
          const res = {};
          for (let i = 0; i < arr.length; i++) {
            const item = arr[i].split("=");
            res[item[0]] = item[1];
          }
          return res;
        },
        // 删除订单
        delteOrder(row) {
          this.delId = row.id;
          this.delVisible = true;
          this.delete_host = false;
        },
        // 立即代理
        goAgentList() {
          location.href = "agentList.htm";
        },
        async onConfirm() {
          try {
            const params = {
              id: this.delId,
              delete_host: this.delete_host ? 1 : 0,
            };
            this.submitLoading = true;
            await delOrderDetail(params);
            this.$message.success(window.lang.del_success);
            this.delVisible = false;
            this.params.page =
              this.data.length > 1 ? this.params.page : this.params.page - 1;
            this.getOrderList();
            this.getSellInfo();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        sureDel() {
          delSupplier(this.delId).then((res) => {
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.getOrderList();
            this.getSellInfo();
          });
        },
        itemClick(row) {
          row.isExpand = row.isExpand ? false : true;
          const rowData = this.$refs.table.getData(row.id);
          this.$refs.table.toggleExpandData(rowData);
          if (row.list?.length > 0) {
            return;
          }
          this.father_client_id = row.client_id;
          this.handelGetOrderDetail(this.optType === "sub" ? row.pId : row.id);
        },
        // 订单详情
        async handelGetOrderDetail(id) {
          try {
            const res = await getOrderDetail(id);
            res.data.data.order.items.forEach((item) => {
              item.pId = res.data.data.order.id;
              this.$refs.table.appendTo(id, item);
            });
          } catch (error) {}
        },
        lookDetail(row) {
          location.href = `order_details.htm?id=${row.id}`;
        },
        // 自定义图标
        treeExpandAndFoldIconRender(h, { type }) {},
        // 排序
        sortChange(val) {
          if (!val) {
            this.params.orderby = "id";
            this.params.sort = "desc";
          } else {
            this.params.orderby = val.sortBy;
            this.params.sort = val.descending ? "desc" : "asc";
          }
          this.getOrderList();
        },
        // 搜索框 搜索
        search() {
          this.params.page = 1;
          this.getOrderList();
        },
        // 清空搜索框
        clearKey() {
          this.params.keywords = "";
          this.params.page = 1;
          this.getOrderList();
        },
        // 底部分页 页面跳转事件
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getOrderList();
        },
        // 调整价格
        updatePrice(row, type) {
          this.optType = type;
          this.formData.id = row.id;
          this.formData.amount = "";
          this.formData.description = "";
          this.$refs.update_price && this.$refs.update_price.clearValidate();
          this.priceModel = true;
          this.curInfo = row;
          if (type === "sub") {
            this.formData = { ...row };
          }
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            if (this.optType === "order") {
              this.changeOrderPrice();
            } else {
              this.changeSubPrice();
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        // 修改订单价格
        async changeOrderPrice() {
          try {
            this.submitLoading = true;
            await updateOrder(this.formData);
            this.$message.success(lang.modify_success);
            this.priceModel = false;
            this.getOrderList();
            this.getSellInfo();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 修改子项人工价格
        async changeSubPrice() {
          try {
            this.submitLoading = true;
            await updateArtificialOrder(this.formData);
            this.$message.success(lang.modify_success);
            this.priceModel = false;
            this.getOrderList();
            this.getSellInfo();
            this.optType = "";
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 获取订单列表
        async getOrderList() {
          try {
            this.loading = true;
            this.fullLoading = true;
            const res = await orderList(this.params);
            this.data = res.data.data.list;
            this.total = res.data.data.count;
            this.data.forEach((item) => {
              item.list = [];
              item.isExpand = false;
            });
            this.loading = false;
            // if (JSON.stringify(this.curInfo) !== "{}") {
            //   //修改子项打开对应的订单下拉
            //   this.itemClick(this.curInfo);
            // } else {
            // }
          } catch (error) {
            this.loading = false;
          }
        },

        // 获取销售信息
        getSellInfo() {
          sellInfo({ supplier_id: this.supplier_id }).then((res) => {
            this.money = res.data.data;
          });
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
