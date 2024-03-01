(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("server")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        let checkPwd2 = (val) => {
          if (val !== this.formData.password) {
            return {
              result: false,
              message: window.lang.password_tip,
              type: "error",
            };
          }
          return { result: true };
        };
        return {
          data: [],
          submitLoading: false,
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          statusVisble: false,
          hover: true,
          columns: [
            {
              colKey: "id",
              title: "ID",
              width: 110,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "name",
              title: lang.interface_name,
              className: "name-status",
              width: 200,
              ellipsis: true,
            },
            {
              colKey: "module",
              title: lang.module_type,
              width: 150,
              ellipsis: true,
            },
            {
              colKey: "server_group_name",
              title: lang.belong_group,
              width: 150,
              ellipsis: true,
            },
            {
              colKey: "host_num",
              title: lang.active_num,
              align: "center",
              width: 110,
            },
            {
              colKey: "status",
              title: lang.open_status,
              width: 110,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 100,
            },
          ],
          hideSortTips: true,
          params: {
            keywords: "",
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
            status: "",
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
          formData: {
            // 添加接口
            name: "",
            module: "",
            url: "",
            username: "",
            password: "",
            hash: "",
            status: 0,
          },
          options: [
            {
              value: 1,
              label: lang.enable,
            },
            {
              value: 0,
              label: lang.disable,
            },
          ],
          rules: {
            name: [
              {
                required: true,
                message: lang.input + lang.interface_name,
                type: "error",
              },
              {
                validator: (val) => val.length <= 50,
                message: lang.verify3 + 50,
                type: "warning",
              },
            ],
            module: [
              {
                required: true,
                message: lang.select + lang.template_type,
                type: "error",
              },
            ],
            url: [
              { required: true, message: lang.tip7, type: "error" },
              {
                pattern:
                  /(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?/,
                type: "warning",
              },
            ],
            username: [
              {
                validator: (val) => val.length <= 100,
                message: lang.verify3 + 100,
                type: "warning",
              },
            ],
            password: [
              {
                validator: (val) => val.length <= 100,
                message: lang.verify3 + 100,
                type: "warning",
              },
              // { pattern: /^[\w@!#$%^&*()+-_]{0,100}$/, message: lang.verify8 + '0~100，' + lang.verify14}
            ],
          },
          loading: false,
          country: [],
          delId: "",
          title: "",
          typeList: [],
          maxHeight: "",
          type: "", // create update
          adminStatus: [
            { value: 0, label: lang.deactivate },
            { value: 1, label: lang.enable },
          ],
        };
      },
      computed: {
        calcName() {
          return (module) => {
            const temp = this.typeList.filter((item) => item.name === module);
            return temp[0]?.display_name;
          };
        },
      },
      mounted() {
        this.maxHeight = document.getElementById("content").clientHeight - 170;
        let timer = null;
        window.onresize = () => {
          if (timer) {
            return;
          }
          timer = setTimeout(() => {
            this.maxHeight =
              document.getElementById("content").clientHeight - 170;
            clearTimeout(timer);
            timer = null;
          }, 300);
        };
      },
      created() {
        this.getInterfaceList();
        this.getTypeList();
      },
      methods: {
        toChild() {
          location.href = "child_server.htm";
        },
        // 获取单个状态
        async getSingleStatus(row) {
          try {
            row.linkStatus = null;
            const res = await getInterfaceStatus(row.id);
            this.data.forEach((item, index) => {
              if (item.id === row.id) {
                item.linkStatus = res.data.status;
                item.fail_reason = res;
              }
            });
          } catch (error) {
            this.data.forEach((item, index) => {
              if (item.id === row.id) {
                item.linkStatus = error.data?.status;
                item.fail_reason = error.data?.msg;
              }
            });
          }
        },
        async getTypeList() {
          try {
            const res = await getInterfaceType(this.params);
            this.loading = false;
            this.typeList = res.data.data.list;
          } catch (error) {}
        },
        // 获取列表
        async getInterfaceList() {
          try {
            this.loading = true;
            const res = await getInterface(this.params);
            const temp = res.data.data;
            temp.list.forEach((item) => {
              item.linkStatus = null;
              item.fail_reason = "";
              this.getSingleStatus(item);
            });
            this.data = temp.list;
            this.total = temp.count;
            this.loading = false;
          } catch (error) {
            this.loading = false;
          }
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getInterfaceList();
        },
        // 排序
        sortChange(val) {
          if (!val) {
            return;
          }
          this.params.orderby = val.sortBy;
          this.params.sort = val.descending ? "desc" : "asc";
          this.getInterfaceList();
        },
        clearKey() {
          this.params.keywords = "";
          this.seacrh();
        },
        seacrh() {
          this.params.page = 1;
          this.getInterfaceList();
        },
        changeStatus(status) {
          this.formData.status = Number(status);
        },
        // 添加接口
        addUser() {
          this.type = "create";
          this.formData.name = "";
          this.formData.module = "";
          this.formData.url = "";
          this.formData.username = "";
          this.formData.password = "";
          this.formData.hash = "";
          this.formData.status = 0;
          this.title = lang.create_interface;
          this.visible = true;
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const res = await addAndUpdateInterface(this.type, this.formData);
              this.$message.success(res.data.msg);
              this.getInterfaceList();
              this.visible = false;
              this.submitLoading = false;
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        close() {
          this.visible = false;
          this.$refs.userDialog.clearValidate();
        },
        // 编辑
        updateHandler(row) {
          this.title = window.lang.edit_interface;
          this.type = "update";
          this.visible = true;
          Object.assign(this.formData, row);
        },
        // 删除接口
        deleteUser(row) {
          this.delVisible = true;
          this.delId = row.id;
        },
        async sureDel() {
          try {
            this.submitLoading = true;
            const res = await deleteInterface(this.delId);
            this.$message.success(res.data.msg);
            this.params.page =
              this.data.length > 1 ? this.params.page : this.params.page - 1;
            this.delVisible = false;
            this.getInterfaceList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.delVisible = false;
            this.$message.error(error.data.msg);
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
