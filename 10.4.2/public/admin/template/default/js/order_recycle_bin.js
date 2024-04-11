/* 用户信息-订单管理 */
(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("order-recyle")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    new Vue({
      components: {
        comConfig,
      },
      data () {
        return {
          id: "",
          rootRul: url,
          submitLoading: false,
          data: [],
          tableLayout: true,
          bordered: true,
          visible: false,
          delVisible: false,
          priceModel: false,
          hover: true,
          fullLoading: false,
          currency_prefix:
            JSON.parse(localStorage.getItem("common_set")).currency_prefix ||
            "¥",
          columns: [
            {
              colKey: "row-select",
              type: "multiple",
              width: 30,
            },
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
              colKey: "recycle_time",
              title: lang.recycle_time,
              width: 170,
              ellipsis: true,
            },
            {
              colKey: "will_delete_time",
              title: lang.order_del_time,
              width: 170,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 120,
            },
          ],
          params: {
            keywords: "",
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
            type: "",
            gateway: "",
            status: "",
            amount: "",
          },
          total: 0,
          father_client_id: "",
          pageSizeOptions: [20, 50, 100],
          loading: false,
          delId: "",
          expandIcon: true,
          delete_host: false, // 是否删除产品:0否1是
          // 变更价格
          formData: {
            id: "",
            amount: "",
            description: "",
          },
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
          orderNum: 0,
          signForm: {
            amount: 0,
            credit: 0,
          },
          payVisible: false,
          maxHeight: "",
          use_credit: true,
          curInfo: {},
          optType: "", // order,sub
          isAdvance: false,
          orderTypes: [
            { value: "new", label: lang.new },
            { value: "renew", label: lang.renew },
            { value: "upgrade", label: lang.upgrade },
            { value: "artificial", label: lang.artificial },
          ],
          payWays: [],
          range: [],
          delRange: [],
          /* 批量 */
          checkId: [],
          isBatch: false,
          deleteTit: "",
          hasCredit: false,
          /* 回收站 */
          recycleVisble: false,
          settingVisble: false,
          recycleForm: {
            order_recycle_bin: 0,
            order_recycle_bin_save_days: undefined
          },
          optTitle: "",
          optTit: "",
          optDes: "",
          recycleType: "", // clear restore delete
          curId: "",
        };
      },
      created () {
        this.params.keywords = location.href.split("?")[1]?.split("=")[1];
        if (sessionStorage.orderListParams) {
          this.params = Object.assign(
            this.params,
            JSON.parse(sessionStorage.orderListParams)
          );
        }
        sessionStorage.removeItem("orderListParams");
        if (this.params.start_time && this.params.end_time) {
          this.isAdvance = true;
          const start = new Date(this.params.start_time * 1000);
          const end = new Date(this.params.end_time * 1000);
          // 2024-01-01
          this.range = [
            `${start.getFullYear()}-${start.getMonth() + 1 < 10
              ? "0" + (start.getMonth() + 1)
              : start.getMonth() + 1
            }-${start.getDate() < 10 ? "0" + start.getDate() : start.getDate()
            }`,
            `${end.getFullYear()}-${end.getMonth() + 1 < 10
              ? "0" + (end.getMonth() + 1)
              : end.getMonth() + 1
            }-${end.getDate() < 10 ? "0" + end.getDate() : end.getDate()}`,
          ];
        }
        this.getClientList();
        this.getPayWay();
      },
      computed: {
        calcDeleteTime () {
          return time => {
            if (time === 0) {
              return '--';
            } else {
              const endTime = time * 1000;
              const nowTime = new Date();
              const timeDiff = Math.ceil((endTime - nowTime) / (1000 * 60 * 60 * 24));
              return `${timeDiff}${lang.recycle_tip15}`;
            }
          };
        }
      },
      methods: {
        /* 清空 | 恢复 | 删除 */
        handleRecyle (type, isBatch = false, row = {}) {
          if (isBatch && this.checkId.length === 0) {
            return this.$message.error(`${lang.select}${lang.order}`);
          }
          if (row.is_lock === 1 && type === 'delete') {
            return this.$message.error(lang.recycle_tip13);
          }
          this.curId = row.id;
          switch (type) {
            case 'clear':
              this.optTitle = lang.clear_recycle_bin;
              this.optTit = lang.recycle_tip5;
              this.optDes = lang.recycle_tip6;
              break;
            case 'restore':
              this.optTitle = lang.restore_orders;
              this.optTit = lang.recycle_tip7;
              this.optDes = lang.recycle_tip8;
              break;
            case 'delete':
              this.optTitle = lang.delete_orders;
              this.optTit = lang.recycle_tip9;
              this.optDes = lang.recycle_tip10;
              break;
            case 'lock':
              this.optTitle = lang.recycle_tip11;
              break;
            case 'unlock':
              this.optTitle = lang.recycle_tip12;
              break;

          }
          this.isBatch = isBatch;
          this.recycleType = type;
          if (type === 'lock' || type === 'unlock') {
            this.delVisible = true;
          } else {
            this.recycleVisble = true;
          }
        },
        submitRecyle () {
          switch (this.recycleType) {
            case 'clear':
              return this.handleClear();
            case 'restore':
              return this.handleRestore();
            case 'delete':
              return this.handleDelete();
          }
        },
        // 清空
        async handleClear () {
          try {
            this.submitLoading = true;
            const res = await clearRecycleList();
            this.$message.success(res.data.msg);
            this.recycleVisble = false;
            this.submitLoading = false;
            this.getClientList();
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 恢复
        async handleRestore () {
          try {
            const id = [];
            if (this.isBatch) {
              id.push(...this.checkId);
            } else {
              id.push(this.curId);
            }
            const params = {
              id
            };
            this.submitLoading = true;
            const res = await recoverRecycleList(params);
            this.$message.success(res.data.msg);
            this.recycleVisble = false;
            this.submitLoading = false;
            this.getClientList();
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 删除
        async handleDelete () {
          try {
            const id = [];
            if (this.isBatch) {
              id.push(...this.checkId);
            } else {
              id.push(this.curId);
            }
            const params = {
              id
            };
            this.submitLoading = true;
            const res = await delRecycleList(params);
            this.$message.success(res.data.msg);
            this.recycleVisble = false;
            this.submitLoading = false;
            this.getClientList();
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        /* 清空 | 恢复 | 删除 end */
        /* 锁定 | 解锁 */
        hanleStatus () {
          switch (this.recycleType) {
            case 'lock':
              return this.handleLock();
            case 'unlock':
              return this.handleUnlock();
          }
        },
        async handleLock () {
          try {
            const params = {
              id: this.checkId
            };
            this.submitLoading = true;
            const res = await lockRecycleList(params);
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.submitLoading = false;
            this.getClientList();
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        async handleUnlock () {
          try {
            const params = {
              id: this.checkId
            };
            this.submitLoading = true;
            const res = await unlockRecycleList(params);
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.submitLoading = false;
            this.getClientList();
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        backOrder () {
          location.href = 'order.htm';
        },
        handleConfig () {
          this.settingVisble = true;
          this.getRecycleSetting();
        },
        changeDays (e) {
          if (e < 0) {
            this.recycleForm.order_recycle_bin_save_days = 1;
          }
          if (e > 999) {
            this.recycleForm.order_recycle_bin_save_days = 999;
          }
        },
        async getRecycleSetting () {
          try {
            const res = await getRecycleConfig();
            const temp = res.data.data;
            temp.order_recycle_bin = Number(temp.order_recycle_bin);
            temp.order_recycle_bin_save_days = Number(temp.order_recycle_bin_save_days);
            this.recycleForm = temp;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        async submitConfig ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const res = await changeRecycleConfig(this.recycleForm);
              this.submitLoading = false;
              this.$message.success(res.data.msg);
              this.settingVisble = false;
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        async getAddonList () {
          try {
            const res = await getAddon();
            this.hasCredit =
              res.data.data.list.filter((item) => item.name === "CreditLimit")
                .length > 0;
            if (this.hasCredit) {
              this.payWays.unshift({
                name: "credit_limit",
                title: lang.credit_pay,
              });
            }
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        /* 批量操作 */
        batchDel () {
          this.renewForm = [];
          this.renewList = [];
          if (this.checkId.length === 0) {
            return this.$message.error(`${lang.select}${lang.order}`);
          }
          this.isBatch = true;
          this.delVisible = true;
          this.deleteTit = `${lang.batch_dele}${lang.order}`;
        },
        rehandleSelectChange (value, { selectedRowData }) {
          this.checkId = value;
          this.selectedRowKeys = selectedRowData;
        },
        /* 批量操作 end */
        changeAdvance () {
          this.isAdvance = !this.isAdvance;
          this.params.type = "";
          this.params.gateway = "";
          // this.params.status = ''
          this.params.amount = "";
          this.range = [];
        },
        async getPayWay () {
          try {
            const res = await getPayList();
            this.payWays = res.data.data.list;
            this.getAddonList();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        lookDetail (row) {
          sessionStorage.currentOrderUrl = window.location.href;
          sessionStorage.orderListParams = JSON.stringify(this.params);
          location.href = `order_details.htm?id=${row.id}`;
        },
        jumpPorduct (client_id, id) {
          location.href = `host_detail.htm?client_id=${client_id}&id=${id}`;
        },
        // 排序
        sortChange (val) {
          if (!val) {
            this.params.orderby = "id";
            this.params.sort = "desc";
          } else {
            this.params.orderby = val.sortBy;
            this.params.sort = val.descending ? "desc" : "asc";
          }
          this.getClientList();
        },
        clearKey () {
          this.params.keywords = "";
          this.seacrh();
        },
        seacrh () {
          this.params.page = 1;
          if (this.range.length > 0) {
            this.params.start_time =
              new Date(this.range[0].replace(/-/g, "/")).getTime() / 1000 || "";
            this.params.end_time =
              (new Date(this.range[1].replace(/-/g, "/")).getTime() +
                24 * 3600 * 1000) /
              1000 || "";
          } else {
            this.params.start_time = "";
            this.params.end_time = "";
          }
          if (this.delRange.length > 0) {
            this.params.start_recycle_time =
              new Date(this.delRange[0].replace(/-/g, "/")).getTime() / 1000 || "";
            this.params.end_recycle_time =
              (new Date(this.delRange[1].replace(/-/g, "/")).getTime() +
                24 * 3600 * 1000) /
              1000 || "";
          } else {
            this.params.start_recycle_time = "";
            this.params.end_recycle_time = "";
          }
          this.getClientList();
        },
        // 自定义图标
        treeExpandAndFoldIconRender (h, { type }) { },
        // 修改订单价格
        async changeOrderPrice () {
          try {
            this.submitLoading = true;
            await updateOrder(this.formData);
            this.$message.success(lang.modify_success);
            this.priceModel = false;
            this.getClientList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 修改子项人工价格
        async changeSubPrice () {
          try {
            this.submitLoading = true;
            await updateArtificialOrder(this.formData);
            this.$message.success(lang.modify_success);
            this.priceModel = false;
            this.getClientList();
            this.optType = "";
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 展开行
        changePage (e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.checkId = [];
          this.getClientList();
        },
        // 获取订单列表
        async getClientList () {
          try {
            this.loading = true;
            this.fullLoading = true;
            const res = await getRecycleList(this.params);
            this.data = res.data.data.list;
            this.total = res.data.data.count;
            this.data.forEach((item) => {
              item.list = [];
              item.isExpand = false;
            });
            this.loading = false;
            this.checkId = [];
          } catch (error) {
            this.loading = false;
          }
        },
        // 订单详情
        async getOrderDetail (id) {
          try {
            const res = await getOrderDetail(id);
            res.data.data.order.items.forEach((item) => {
              item.pId = res.data.data.order.id;
              this.$refs.table.appendTo(id, item);
            });
          } catch (error) { }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
