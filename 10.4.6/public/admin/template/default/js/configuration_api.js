(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("configuration-system")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    new Vue({
      components: {
        comConfig,
        comChooseUser,
      },
      created() {
        document.title =
          lang.user_api_text1 + "-" + localStorage.getItem("back_website_name");
        this.getActivePlugin();
        this.getApiConfig();
      },
      computed: {
        showDetails() {
          const clientAuth = [
            "auth_system_configuration_system_configuration_user_api_management",
          ];
          return clientAuth.some((item) => this.$checkPermission(item));
        },
        filterColor() {
          return (level) => {
            if (level) {
              return this.levelList.filter(
                (item) => item.id * 1 === level[0]?.value * 1
              )[0]?.background_color;
            }
          };
        },
        filterName() {
          return (level) => {
            if (level) {
              return (
                this.levelList.filter(
                  (item) => item.id * 1 === level[0]?.value * 1
                )[0]?.name || ""
              );
            }
          };
        },
      },
      data() {
        return {
          hasController: true,
          data: [],
          tableLayout: false,
          addLoading: false,
          bordered: true,
          visible: false,
          delVisible: false,
          loading: false,
          hover: true,
          isCanUpdata: sessionStorage.isCanUpdata === "true",
          levelList: [],
          client_id: "",
          statusVisble: false,
          submitLoading: false,
          delData: {},
          delId: null,
          params: {
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          columns: [
            {
              colKey: "id",
              title: "ID",
              width: 125,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "username",
              title: lang.name,
              width: 120,
              ellipsis: true,
            },
            {
              colKey: "phone",
              title: lang.contact,
              width: 120,
              ellipsis: true,
            },
            {
              colKey: "e-mail",
              title: lang.email,
              width: 120,
              ellipsis: true,
            },
            {
              colKey: "host_active_num",
              title: lang.host_active_product_num,
              width: 140,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 90,
              // 右对齐
              fixed: "right",
            },
          ],
          configData: {
            client_create_api: 0,
            client_create_api_type: 0,
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
        };
      },

      methods: {
        changeSwitch() {
          this.updateApiConfig();
        },
        typeChange(val) {
          this.updateApiConfig();
        },
        changeUser(id) {
          this.client_id = id;
        },
        // 获取配置
        getApiConfig() {
          apiConfig().then((res) => {
            this.configData.client_create_api = Number(
              res.data.data.client_create_api
            );
            this.configData.client_create_api_type = Number(
              res.data.data.client_create_api_type
            );
            this.configData.client_create_api === 1 &&
              this.configData.client_create_api_type !== 0 &&
              this.getClientList();
          });
        },
        addUser() {
          if (!this.client_id) {
            return;
          }
          this.addApiUser(this.client_id);
          this.client_id = "";
        },
        clickDel(row) {
          this.delData = row;
          this.statusVisble = true;
        },
        changeStatus() {
          // if (this.delData.status == 1) {
          //   this.delApiUser(this.delData.id);
          // } else {
          //   this.addApiUser(this.delData.id);
          // }
          this.delApiUser(this.delData.id);
        },
        // 添加
        addApiUser(id) {
          this.addLoading = true;
          apiUserAdd(id)
            .then((res) => {
              this.$message.success(res.data.msg);
              this.getClientList();
              this.addLoading = false;
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
              this.addLoading = false;
            });
        },
        // 移除
        delApiUser(id) {
          this.submitLoading = true;
          apiUserDelete(id)
            .then((res) => {
              this.submitLoading = false;
              this.$message.success(res.data.msg);
              this.statusVisble = false;
              this.getClientList();
            })
            .catch((err) => {
              this.submitLoading = false;
              this.$message.error(err.data.msg);
            });
        },
        // 修改获取配置
        updateApiConfig() {
          apiConfigPut(this.configData)
            .then((res) => {
              this.$message.success(res.data.msg);
              this.getApiConfig();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
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
          this.getClientList();
        },

        // 获取列表
        async getClientList() {
          try {
            this.loading = true;
            const res = await apiUserList(this.params);
            this.loading = false;
            this.data = res.data.data.list;
            this.total = res.data.data.count;
          } catch (error) {
            this.loading = false;
            console.log(error.data && error.data.msg);
          }
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getClientList();
        },
        goDetail(id) {
          location.href = `client_detail.htm?client_id=${id}`;
        },
        async getActivePlugin() {
          const res = await getActiveAddon();
          this.hasController = (res.data.data.list || [])
            .map((item) => item.name)
            .includes("TemplateController");
          if (
            (res.data.data.list || [])
              .map((item) => item.name)
              .includes("IdcsmartCertification")
          ) {
            this.columns.splice(2, 0, {
              colKey: "certification",
              title: lang.user_text20,
              width: 150,
            });
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
