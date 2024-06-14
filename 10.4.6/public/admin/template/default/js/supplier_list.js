(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("supplier_list")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    new Vue({
      components: {
        comConfig,
      },
      data () {
        return {
          // 分页相关
          submitLoading: false,
          params: {
            keywords: "",
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          total: 0,
          editId: null,
          delId: null,
          pageSizeOptions: [20, 50, 100],
          // 表格相关
          data: [],
          delVisible: false,
          formData: {
            type: "",
            auto_update_rate: 0
          },
          configData: [
            {
              title: lang.upstream_text52,
              type: "text",
              name: "name",
            },
            {
              title: lang.upstream_text53,
              type: "select",
              name: "type",
              options: {
                default: lang.upstream_text54,
                whmcs: "whmcs",
                finance: lang.upstream_text55,
              },
            },
            {
              title: lang.upstream_text56,
              type: "text",
              name: "url",
              tip: lang.upstream_text57,
            },
            {
              title: lang.upstream_text58,
              type: "text",
              name: "username",
              tip: lang.upstream_text59,
            },
            {
              title: lang.upstream_text60,
              type: "text",
              name: "token",
              tip: lang.upstream_text61,
            },
            {
              title: lang.upstream_text62,
              type: "textarea",
              name: "secret",
              tip: lang.upstream_text63,
            },
            {
              title: lang.upstream_text64,
              type: "text",
              name: "contact",
            },
            {
              title: lang.upstream_text65,
              type: "textarea",
              name: "notes",
            },
          ],
          rules: {
            name: [{ required: true, message: lang.required, type: "error" }],
            url: [{ required: true, message: lang.required, type: "error" }],
            username: [
              { required: true, message: lang.required, type: "error" },
            ],
            token: [{ required: true, message: lang.required, type: "error" }],
            type: [{ required: true, message: lang.required, type: "error" }],
            secret: [{ required: true, message: lang.required, type: "error" }],
            rate: [{ required: true, message: lang.required, type: "error" }],
          },
          typeObj: {
            default: lang.upstream_text54,
            whmcs: "whmcs",
            finance: lang.upstream_text55,
          },
          columns: [
            {
              title: "ID",
              width: "100",
              align: "let",
              colKey: "id",
              sortType: "all",
              sorter: true,
            },
            {
              title: lang.upstream_text66,
              width: "150",
              colKey: "name",
              cell: "name",
              ellipsis: true,
            },
            {
              title: lang.upstream_text67,
              width: "150",
              colKey: "type",
              cell: "type",
            },
            {
              title: lang.upstream_text68,
              colKey: "url",
              ellipsis: true,
            },
            {
              title: lang.upstream_text76,
              colKey: "currency_name",
              width: "150",
              ellipsis: true,
            },
            {
              title: 'title-slot-name',
              colKey: "rate",
              width: "150",
              ellipsis: true,
            },
            {
              title: lang.upstream_text78,
              colKey: "auto_update_rate",
              width: "150",
              ellipsis: true,
              className: "update_rate"
            },
            {
              title: lang.upstream_text69 + "/" + lang.upstream_text70,
              cell: "num",
              width: "200",
              ellipsis: true,
            },
            {
              title: lang.upstream_text71,
              cell: "status",
              width: "100",
              ellipsis: true,
            },
            {
              title: lang.upstream_text72,
              width: "120",
              cell: "op",
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
          configVisble: false,
          /* 汇率 */
          rateVisible: false
        };
      },
      filters: {
        filterMoney (money) {
          if (isNaN(money)) {
            return "0.00";
          } else {
            const temp = `${money}`.split(".");
            return parseInt(temp[0]).toLocaleString() + "." + (temp[1] || "00");
          }
        },
      },
      computed: {},
      mounted () { },
      created () {
        // 获取提现列表
        this.getSupplierList();
      },
      methods: {
        /* 汇率 */
        changeRate (val) {
          if (val < 0) {
            this.formData.rate = 1;
          }
        },
        async changeStatus (bol, row) {
          try {
            const res = await updateRate({
              id: row.id,
              auto_update_rate: bol,
              rate: row.rate
            });
            this.$message.success(res.data.msg);
            this.getSupplierList();
            this.submitLoading = false;
            this.rateVisible = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        editRate (row) {
          this.formData = JSON.parse(JSON.stringify(row));
          this.rateVisible = true;
        },
        submitRate ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              this.changeStatus(this.formData.auto_update_rate, this.formData);
            } catch (error) {
              this.$message.error(error.data.msg);
            }
          }
        },
        /* 汇率 end */
        // 格式化配置里面的options
        computedOptions (options) {
          const arr = [];
          Object.keys(options).map((item) => {
            arr.push({ value: item, label: options[item] });
          });
          return arr;
        },
        // 编辑/添加
        onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            this.submitLoading = true;
            if (this.editId !== null) {
              editSupplier(this.editId, this.formData)
                .then((res) => {
                  this.$message.success(res.data.msg);
                  this.configVisble = false;
                  this.getSupplierList();
                })
                .catch((err) => {
                  this.$message.error(err.data.msg);
                }).finally(() => {
                  this.submitLoading = false;
                });
            } else {
              addSupplier(this.formData)
                .then((res) => {
                  this.$message.success(res.data.msg);
                  this.configVisble = false;
                  this.getSupplierList();
                })
                .catch((err) => {
                  this.$message.error(err.data.msg);
                }).finally(() => {
                  this.submitLoading = false;
                });
            }
          }
        },
        sortChange (val) {
          if (!val) {
            this.params.orderby = "id";
            this.params.sort = "desc";
          } else {
            this.params.orderby = val.sortBy;
            this.params.sort = val.descending ? "desc" : "asc";
          }
          this.getSupplierList();
        },
        handelEdit (row) {
          this.editId = row.id;
          this.getSupplierDrtail(row.id);
        },
        getSupplierDrtail (id) {
          supplierDrtail(id).then((res) => {
            this.formData = res.data.data.supplier;
            this.configVisble = true;
          });
        },
        goDetail (id) {
          location.href = `supplier_order.htm?id=${id}`;
        },
        diaClose () {
          this.editId = null;
          this.$refs.userDialog && this.$refs.userDialog.reset();
          this.formData = {
            auto_update_rate: 0
          };
        },
        // 添加供应商
        addSupplier () {
          this.editId = null;
          this.configVisble = true;
        },
        handelDel (id) {
          this.delId = id;
          this.delVisible = true;
        },
        sureDel () {
          this.submitLoading = true;
          delSupplier(this.delId)
            .then((res) => {
              this.$message.success(res.data.msg);
              this.delVisible = false;
              this.getSupplierList();
            })
            .catch((error) => {
              this.$message.error(error.data.msg);
            }).finally(() => {
              this.submitLoading = false;
            });
        },
        //
        // 搜索框 搜索
        search () {
          this.params.page = 1;
          // 重新拉取申请列表
          this.getSupplierList();
        },
        // 清空搜索框
        clearKey () {
          this.params.keywords = "";
          this.params.page = 1;
          // 重新拉取申请列表
          this.getSupplierList();
        },
        // 底部分页 页面跳转事件
        changePage (e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getSupplierList();
        },
        // 获取申请列表
        async getSupplierList () {
          this.loading = true;
          const res = await supplierList(this.params);
          this.total = res.data.data.count;
          this.data = res.data.data.list.map((item) => {
            item.status = false;
            item.resgen = "";
            return item;
          });
          this.getSupplierStatus();
          this.loading = false;
        },
        // 检查供应商接口连接状态
        getSupplierStatus () {
          this.data.forEach((item) => {
            supplierStatus(item.id)
              .then(() => {
                item.status = true;
              })
              .catch((err) => {
                item.resgen = err.data.msg;
              });
          });
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
