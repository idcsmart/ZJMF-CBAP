(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          message: "template...",
          pagination: {
            total: 0,
            pageSize: 20,
            pageSizeOptions: [20, 50, 100],
          },
          tableHeight: 0,
          listData: [],
          columns: [
            {
              colKey: "product_name",
              title: lang.product_name,
              // 对齐方式
              align: "left",
              ellipsis: true,
            },
            // {
            //   colKey: 'config_option',
            //   title: lang.product_configuration,
            //   // 对齐方式
            //   align: 'left',
            //   ellipsis: true,
            // },
            {
              width: 200,
              colKey: "type",
              title: "op-type",
              cell: "type",
              ellipsis: true,
            },
            {
              width: 120,
              colKey: "op",
              title: "op-column",
              cell: "op",
              ellipsis: true,
            },
          ], //商品表格
          page: {
            page: 1,
            limit: 20,
            keywords: "",
          }, //分页

          customChecked: false, //自定义开关
          endVisible: false, //停用原因弹框
          reasonColumns: [
            {
              colKey: "index",
              title: "index-column",
              cell: "index",
              width: 120,
              // 对齐方式
              align: "left",
              ellipsis: true,
            },
            {
              colKey: "content",
              title: "content-column",
              cell: "content",
              width: 600,
              ellipsis: true,
            },
            {
              colKey: "admin_name",
              title: lang.order_poster,
              // 对齐方式
              align: "left",
              ellipsis: true,
            },
            {
              colKey: "create_time",
              title: lang.create_time,
              // 对齐方式
              align: "left",
              ellipsis: true,
            },
            {
              colKey: "op",
              title: "op-column",
              cell: "op",
              width: 150,
              ellipsis: true,
            },
          ],
          selectReason: [],
          reasonTable: [], //停用list
          isSubmit: false,
          delVisible: false,
          submitLoading: false,
          curId: ""
        };
      },
      methods: {
        // 时间格式转换
        formatDate(date) {
          const str1 = [
            date.getFullYear(),
            this.formatDateAdd0(date.getMonth() + 1),
            this.formatDateAdd0(date.getDate()),
          ].join("/");
          const str2 = [
            this.formatDateAdd0(date.getHours()),
            this.formatDateAdd0(date.getMinutes()),
            this.formatDateAdd0(date.getSeconds()),
          ].join(":");
          return str1 + " " + str2;
        },
        formatDateAdd0(m) {
          return m < 10 ? "0" + m : m;
        },
        // 切换分页
        onPageChange(pageInfo) {
          this.page = {
            page: pageInfo.current,
            limit: pageInfo.pageSize,
            keywords: this.page.keywords,
          };
          this.pagination.pageSize = pageInfo.pageSize;
          this.getList();
        },
        // 输入框-查询
        Search() {
          this.page.page = 1;
          this.getList();
        },
        // 输入框-清空
        Clear() {
          this.page.page = 1;
          this.page.keywords = "";
          this.getList();
        },
        //获取商品列表list
        async getList() {
          let list = await getRefundList(this.page);
          this.listData = list.data.data.list;
          this.pagination.total = list.data.data.count;
        },

        //删除商品
        deleteRow (data) {
          this.curId = data.row.id
          this.delVisible = true
        },
        async sureDelUser() {
          try {
            this.submitLoading = true;
            const res = await deleteRefund(this.curId);
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.submitLoading = false;
            this.getList()
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },

        //关闭弹框
        btn_close() {
          let judge = false;
          this.reasonTable.forEach((item) => {
            if (item.inputJudge) {
              judge = true;
            }
          });

          if (judge) {
            this.msg = this.$message.info({
              content: lang.order_type_verify3,
              duration: 3000,
              // 层级控制：非当前场景自由控制开关的关键代码，仅用于测试 API 是否运行正常
              zIndex: 1001,
              // 挂载元素控制：非当前场景自由控制开关的关键代码，仅用于测试 API 是否运行正常
              attach: "#t-message-toggle",
            });
          } else {
            this.endVisible = false;
          }
        },

        //获取停用原因
        async getCutom() {
          let obj = await getReasonCustom();
          this.customChecked = obj.data.data.reason_custom == 1 ? true : false;
        },

        //选择
        async changeCustom(val) {
          await setReasonCustom({
            reason_custom: val ? 1 : 0,
          });
          this.getCutom();
        },

        //打开停用原因弹框
        openEndDialog() {
          this.endVisible = true;
          this.getReasonList();
          this.getCutom();
          // this.getRefundReason();
        },

        //前往新增编辑界面
        to_add(data) {
          if (data) {
            location.href = `add_refund_product.htm?id=${data.row.id}`;
          } else {
            location.href = "add_refund_product.htm";
          }
        },
        //获取停用列表
        async getReasonList() {
          let list = await reasonList();
          list.data.data.list.forEach((item) => {
            item["inputJudge"] = false;
            item.create_time = Number(item.create_time) * 1000;
            item.create_time = this.formatDate(new Date(item.create_time));
          });
          this.selectReason = JSON.parse(JSON.stringify(list.data.data.list));
          this.reasonTable = list.data.data.list;
        },

        //新增停用原因
        btn_reasonOpen(index) {
          let judge = false;
          this.reasonTable.forEach((item) => {
            if (item.inputJudge) {
              judge = true;
            }
          });

          if (judge) {
            this.msg = this.$message.info({
              content: lang.order_type_verify3,
              duration: 3000,
              // 层级控制：非当前场景自由控制开关的关键代码，仅用于测试 API 是否运行正常
              zIndex: 1001,
              // 挂载元素控制：非当前场景自由控制开关的关键代码，仅用于测试 API 是否运行正常
              attach: "#t-message-toggle",
            });
          } else {
            this.reasonTable.push({
              content: "",
              inputJudge: true,
            });
          }
        },

        //编辑或者新增停用原因
        async btn_reasonSave(data) {
          if (!data.content) {
            this.$message.info({
              content: lang.end_null_message,
              duration: 3000,
              theme: "warning",
              // 层级控制：非当前场景自由控制开关的关键代码，仅用于测试 API 是否运行正常
              zIndex: 1001,
              // 挂载元素控制：非当前场景自由控制开关的关键代码，仅用于测试 API 是否运行正常
              attach: "#t-message-toggle",
            });
            return;
          }
          if (this.isSubmit) {
            return;
          }
          this.isSubmit = true;
          if (data.id) {
            //编辑
            await putReason(data);
            this.msg = this.$message.info({
              content: lang.modify_success,
              duration: 3000,
              // 层级控制：非当前场景自由控制开关的关键代码，仅用于测试 API 是否运行正常
              zIndex: 1001,
              // 挂载元素控制：非当前场景自由控制开关的关键代码，仅用于测试 API 是否运行正常
              attach: "#t-message-toggle",
            });
            setTimeout(() => {
              this.isSubmit = false;
            }, 1000);
          } else {
            //新增
            await addReason(data);
            this.msg = this.$message.info({
              content: lang.add_success,
              duration: 3000,
              // 层级控制：非当前场景自由控制开关的关键代码，仅用于测试 API 是否运行正常
              zIndex: 1001,
              // 挂载元素控制：非当前场景自由控制开关的关键代码，仅用于测试 API 是否运行正常
              attach: "#t-message-toggle",
            });
            setTimeout(() => {
              this.isSubmit = false;
            }, 1000);
          }
          this.getReasonList();
        },

        //删除停用原因
        async btn_deleteReason(data, index) {
          let _that = this;
          if (data.id) {
            let mydialog = this.$dialog({
              theme: "warning",
              header: `${lang.sureDelete}`,
              className: "t-dialog-new-class1 t-dialog-new-class2",
              style: "color: rgba(0, 0, 0, 0.6)",
              confirmBtn: lang.sure,
              cancelBtn: lang.cancel,
              onConfirm: ({ e }) => {
                deleteReason(data.id).then((res) => {
                  _that.getReasonList();
                  this.$message.info({
                    content: lang.del_success,
                    duration: 3000,
                    // 层级控制：非当前场景自由控制开关的关键代码，仅用于测试 API 是否运行正常
                    zIndex: 1001,
                    // 挂载元素控制：非当前场景自由控制开关的关键代码，仅用于测试 API 是否运行正常
                    attach: "#t-message-toggle",
                  });
                  mydialog.hide();
                });
              },
            });
          }
        },
        btn_deleteReasons(data) {
          if (data.row.id) {
            data.row.inputJudge = false;
          } else {
            this.reasonTable.splice(data.rowIndex, 1);
          }
        },

        //编辑停用原因
        btn_editEnd(data) {
          let judge = false;
          this.reasonTable.forEach((item) => {
            if (item.inputJudge) {
              judge = true;
            }
          });

          if (judge) {
            this.msg = this.$message.info({
              content: lang.order_type_verify3,
              duration: 3000,
              // 层级控制：非当前场景自由控制开关的关键代码，仅用于测试 API 是否运行正常
              zIndex: 1001,
              // 挂载元素控制：非当前场景自由控制开关的关键代码，仅用于测试 API 是否运行正常
              attach: "#t-message-toggle",
            });
          } else {
            data.row.inputJudge = true;
          }
        },

        //获取选择理由
        async getRefundReason() {
          // let res= await getRefund();
          //
        },
      },
      created() {
        const domHeight = template.scrollHeight;
        this.tableHeight = domHeight - 250;
        this.getList();
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
