(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("server-group")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
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
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          statusVisble: false,
          hover: true,
          submitLoading: false,
          columns: [
            {
              colKey: "id",
              title: "ID",
              width: 120,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "name",
              title: lang.group_name,
              width: 210,
              ellipsis: true,
            },
            {
              colKey: "server",
              title: lang.include_interface,
              ellipsis: true,
              width: 630,
            },
            {
              colKey: "create_time",
              title: lang.time,
              width: 180,
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
          },
          total: 0,
          interfaceTotal: 0,
          pageSizeOptions: [20, 50, 100],
          formData: {
            // 添加接口
            id: "",
            name: "",
            server_id: [],
          },
          options: [
            {
              value: 1,
              label: window.lang.enable,
            },
            {
              value: 0,
              label: window.lang.disable,
            },
          ],
          rules: {
            name: [
              {
                required: true,
                message: lang.input + lang.group_name,
                type: "error",
              },
              {
                validator: (val) => val.length <= 50,
                message: lang.verify3 + 50,
                type: "warning",
              },
            ],
            server_id: [
              {
                required: true,
                message: lang.select + lang.interface,
                type: "error",
              },
            ],
          },
          loading: false,
          country: [],
          delId: "",
          title: "",
          originList: [], // 原始数据
          interfaceList: [], // 接口下拉的选择
          createList: [], // 新建的时候筛选出没有分配的接口
          updateList: [], // 编辑的时候筛选没有分配和当前已分配的选项
          type: "", // create update
          interfaceParams: {
            page: 1,
            limit: 20,
          },
          tempArr: [],
          maxHeight: "",
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
        };
      },
      created() {
        this.getGroupList();
        this.getInterface();
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
      methods: {
        async getInterface() {
          try {
            const res = await getInterface(this.interfaceParams);
            this.interfaceTotal = res.data.data.count;
            if (res.data.data.list.length === 20) {
              this.interfaceParams.page++;
              this.getInterface();
            }
            res.data.data.list.forEach((item) => {
              this.originList.push(item);
            });
            this.createList = this.originList.filter((item) => {
              // 筛选出未被占用的接口
              return item.server_group_id === 0;
            });
          } catch (error) {}
        },
        // 获取列表
        async getGroupList() {
          try {
            this.loading = true;
            const res = await getGroup(this.params);
            this.loading = false;
            this.data = res.data.data.list;
            this.total = res.data.data.count;
          } catch (error) {
            this.loading = false;
          }
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getGroupList();
        },
        // 排序
        sortChange(val) {
          if (!val) {
            return;
          }
          this.params.orderby = val.sortBy;
          this.params.sort = val.descending ? "desc" : "asc";
          this.getGroupList();
        },
        clearKey() {
          this.params.keywords = "";
          this.seacrh();
        },
        seacrh() {
          this.params.page = 1;
          this.getGroupList();
        },
        changeStatus(status) {
          this.formData.status = Number(status);
        },
        // 添加接口
        addUser() {
          this.formData.id = "";
          this.formData.name = "";
          this.formData.server_id = [];
          this.type = "create";
          this.title = lang.create_group;
          this.interfaceList = this.createList;
          this.visible = true;
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const res = await addAndUpdateGroup(this.type, this.formData);
              this.$message.success(res.data.msg);
              this.interfaceTotal = 0;
              this.interfaceParams.page = 1;
              this.originList = [];
              this.getGroupList();
              this.getInterface();
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
          this.$refs.userDialog && this.$refs.userDialog.reset();
        },
        // 编辑
        updateHandler(row) {
          this.title = lang.edit_group;
          this.formData.id = row.id;
          this.type = "update";
          this.formData.server_id = [];
          this.interfaceList = [];
          this.updateList = [];
          this.visible = true;
          this.formData.name = row.name;
          row.server.forEach((item) => {
            this.formData.server_id.push(item.id);
          });
          const slectIds = [];
          row.server.forEach((el) => {
            slectIds.push(el.id);
          });
          // 筛选出组id等于0的 代表没有使用，还有已选择的 接口id
          const temp = this.originList.filter((item) => {
            return (
              item.server_group_id === 0 ||
              (item.server_group_id !== 0 && slectIds.includes(item.id))
            );
          });
          let obj = {};
          this.interfaceList = temp.reduce((arr, cur) => {
            obj[cur.id] ? "" : (obj[cur.id] = true && arr.push(cur));
            return arr;
          }, []);
        },
        // 删除接口
        deleteUser(row) {
          this.delVisible = true;
          this.delId = row.id;
        },
        async sureDel() {
          try {
            this.submitLoading = true;
            const res = await deleteGroup(this.delId);
            this.$message.success(res.data.msg);
            this.params.page =
              this.data.length > 1 ? this.params.page : this.params.page - 1;
            this.delVisible = false;
            this.getGroupList();
            this.getInterface();
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
