(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementById("content");
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          id: "",
          params: {
            type: "product",
            relid: "",
          },
          pageSizeOptions: [20, 50, 100],
          total: 0,
          loading: false,
          list: [],
          submitLoading: false,
          columns: [
            {
              colKey: "drag", // 列拖拽排序必要参数
              title: lang.sort,
              cell: "drag",
              width: 90,
            },
            {
              colKey: "id",
              title: "ID",
              cell: "id",
              width: 90,
            },
            {
              width: 200,
              colKey: "field_name",
              title: lang.client_custom_label9,
              ellipsis: true,
            },
            {
              width: 120,
              colKey: "field_type",
              cell: "field_type",
              title: lang.client_custom_label10,
            },
            {
              width: 250,
              colKey: "description",
              title: lang.client_custom_label23,
              cell: "description",
              ellipsis: true,
            },
            {
              width: 200,
              colKey: "regexpr",
              title: lang.client_custom_label24,
              cell: "regexpr",
              ellipsis: true,
            },
            {
              minWidth: 400,
              colKey: "watch",
              title: lang.client_custom_label31,
              cell: "watch",
              ellipsis: true,
            },
            {
              colKey: "op",
              width: 100,
              title: lang.operation,
              cell: "op",
            },
          ],
          typeList: [
            {
              value: "text",
              label: lang.client_custom_label22,
            },
            {
              value: "dropdown",
              label: lang.client_custom_label4,
            },
            {
              value: "link",
              label: lang.client_custom_label17,
            },
            {
              value: "password",
              label: lang.client_custom_label18,
            },
            {
              value: "tickbox",
              label: lang.client_custom_label19,
            },
            {
              value: "textarea",
              label: lang.client_custom_label20,
            },
          ],
          pagination: {
            current: 1,
            pageSize: 10,
            total: 0,
            showJumper: true,
          },
          materialVisble: false,
          formData: {
            type: "product",
            relid: "",
            field_option: "",
            field_type: "",
            field_name: "",
            description: "",
            regexpr: "",
            is_required: false,
            show_order_page: true,
            show_order_detail: true,
            show_client_host_detail: true,
            show_admin_host_detail: true,
            show_client_host_list: true,
          },
          editId: null,
          rules: {
            field_name: [
              { required: true, message: lang.required, type: "error" },
            ],
            field_type: [
              { required: true, message: lang.required, type: "error" },
            ],
            field_option: [
              { required: true, message: lang.required, type: "error" },
            ],
          },
          pageView: {
            show_order_page: lang.client_custom_label26,
            show_order_detail: lang.client_custom_label27,
            show_client_host_detail: lang.client_custom_label28,
            show_admin_host_detail: lang.client_custom_label29,
            show_client_host_list: lang.client_custom_label34,
          },
          delVisible: false,
          delId: null,
        };
      },
      created() {
        this.id = location.href.split("?")[1].split("=")[1];
        this.params.relid = this.id;
        this.formData.relid = this.id;
        this.getlist();
        this.getUserDetail();
      },
      computed: {
        calcTrueToNumber(val) {
          return val === 1;
        },
      },
      mounted() {},
      methods: {
        async getUserDetail() {
          try {
            const res = await getProductDetail(this.id);
            const temp = res.data.data.product;
            document.title =
              lang.product_list +
              "-" +
              temp.name +
              "-" +
              localStorage.getItem("back_website_name");
          } catch (error) {
            console.log(error);
          }
        },
        calcWatch(row) {
          let str = "";
          // 遍历arr
          for (let key in this.pageView) {
            if (row[key] === 1) {
              str += this.pageView[key] + " / ";
            }
          }
          str = str.slice(0, -2);
          return str;
        },
        calcTypeText(row) {
          return this.typeList.filter(
            (item) => row.field_type === item.value
          )[0].label;
        },
        onChange(row) {
          const params = { id: row.id, status: row.status };
          editclientStatus(params).then((res) => {
            this.$message.success(res.data.msg);
            this.getlist();
          });
        },
        onDragSort({ targetIndex, newData }) {
          this.data = newData;
          dragSelfDefinedField({
            id: newData[targetIndex].id,
            prev_id: targetIndex === 0 ? 0 : newData[targetIndex - 1].id,
          }).then((res) => {
            this.$message.success(res.data.msg);
          });
        },

        handADDmaterial() {
          this.formData.is_required = false;
          this.formData.show_order_page = true;
          this.formData.show_order_detail = true;
          this.formData.show_client_host_detail = true;
          this.formData.show_admin_host_detail = true;
          this.formData.show_client_host_list = true;
          this.formData.field_option = "";
          this.formData.field_type = "";
          this.formData.field_name = "";
          this.formData.description = "";
          this.formData.regexpr = "";
          this.materialVisble = true;
        },
        onEnter() {},
        getlist() {
          this.loading = true;
          getSelfDefinedField(this.params)
            .then((res) => {
              this.list = res.data.data.list;
              this.total = res.data.data.count;
              this.loading = false;
            })
            .catch((err) => {
              this.loading = false;
              this.$message.error(err.data.msg);
            });
        },
        sureDelUser() {
          this.submitLoading = true;
          deleteSelfDefinedField(this.delId)
            .then((res) => {
              this.$message.success(res.data.msg);
              this.getlist();
              this.delId = null;
              this.delVisible = false;
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            })
            .finally(() => {
              this.submitLoading = false;
            });
        },
        edit(row) {
          this.editId = row.id;
          this.formData.is_required = row.is_required === 1;
          this.formData.show_order_page = row.show_order_page === 1;
          this.formData.show_order_detail = row.show_order_detail === 1;
          this.formData.show_client_host_detail =
            row.show_client_host_detail === 1;
          this.formData.show_admin_host_detail =
            row.show_admin_host_detail === 1;
          this.formData.show_client_host_list = row.show_client_host_list === 1;
          this.formData.field_option = row.field_option;
          this.formData.field_type = row.field_type;
          this.formData.field_name = row.field_name;
          this.formData.description = row.description;
          this.formData.regexpr = row.regexpr;
          this.materialVisble = true;
        },
        deletes(id) {
          this.delId = id;
          this.delVisible = true;
        },
        opTypeDia() {
          this.getTypeList();
          this.typeVisible = true;
        },
        materialDiaClose() {
          this.editId = null;
          this.$refs.userDialog.reset();
          this.materialVisble = false;
        },
        onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            const params = { ...this.formData };
            params.is_required = params.is_required ? 1 : 0;
            params.show_order_page = params.show_order_page ? 1 : 0;
            params.show_order_detail = params.show_order_detail ? 1 : 0;
            params.show_client_host_detail = params.show_client_host_detail
              ? 1
              : 0;
            params.show_admin_host_detail = params.show_admin_host_detail
              ? 1
              : 0;
            params.show_client_host_list = params.show_client_host_list ? 1 : 0;
            this.submitLoading = true;
            if (this.editId !== null) {
              updateSelfDefinedField({ id: this.editId, ...params })
                .then((res) => {
                  this.$message.success(res.data.msg);
                  this.materialVisble = false;
                  this.getlist();
                })
                .catch((err) => {
                  this.$message.error(err.data.msg);
                })
                .finally(() => {
                  this.submitLoading = false;
                });
            } else {
              addSelfDefinedField(params)
                .then((res) => {
                  this.$message.success(res.data.msg);
                  this.materialVisble = false;
                  this.getlist();
                })
                .catch((err) => {
                  this.$message.error(err.data.msg);
                })
                .finally(() => {
                  this.submitLoading = false;
                });
            }
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
