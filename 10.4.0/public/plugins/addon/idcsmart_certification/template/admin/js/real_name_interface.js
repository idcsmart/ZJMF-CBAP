(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("real_name_interface")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          data: [],
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
              width: 120,
            },
            {
              colKey: "title",
              title: lang.interface_name,
              ellipsis: true,
              className: "status-icon",
            },
            {
              colKey: "certification_type",
              title: lang.type,
              width: 150,
              ellipsis: true,
            },
            {
              colKey: "author",
              title: lang.author,
              width: 150,
              ellipsis: true,
            },
            {
              colKey: "version",
              title: lang.version,
              width: 100,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 160,
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
          pageSizeOptions: [20, 50, 100],
          formData: {
            // 添加用户
            username: "",
            email: "",
            phone_code: "",
            phone: "",
            password: "",
            repassword: "",
          },
          rules: {
            username: [
              { required: true, message: lang.required, type: "error" },
            ],
            password: [
              { required: true, message: lang.required, type: "error" },
            ],
          },
          loading: false,
          upLoading: false,
          country: [],
          delId: "",
          curStatus: 1,
          statusTip: "",
          installTip: "",
          type: "",
          configVisble: false,
          configTip: "",
          configData: [],
          maxHeight: "",
          moduleName: "certification",
          baseUrl: url,
          upVisible: false,
          curName: "",
          submitLoading: false,
        };
      },
      methods: {
        updatePlugin(row) {
          this.upVisible = true;
          this.curName = row.name;
        },
        // 提交升级
        async sureUpgrade() {
          try {
            this.upLoading = true;
            const res = await upgradePlugin({
              module: this.module,
              name: this.curName,
            });
            this.$message.success(res.data.msg);
            this.upVisible = false;
            this.upLoading = false;
            this.getGatewayList();
          } catch (error) {
            this.upLoading = false;
            this.upVisible = false;
            this.$message.error(error.data.msg);
          }
        },
        // 获取列表
        async getGatewayList() {
          try {
            this.loading = true;
            const params = { ...this.params };
            params.module = this.moduleName;
            const res = await getMoudle(params);
            this.loading = false;
            this.data = res.data.data.list;
            this.total = res.data.data.count;
          } catch (error) {
            this.loading = false;
          }
        },
        addUser() {
          // window.open('https://market.idcsmart.com/shop')
          setToken().then((res) => {
            if (res.data.status == 200) {
              let url = res.data.market_url;
              let getqyinfo = url.split("?")[1];
              let getqys = new URLSearchParams("?" + getqyinfo);
              const from = getqys.get("from");
              const token = getqys.get("token");
              window.open(
                `https://my.idcsmart.com/shop/index.html?from=${from}&token=${token}`
              );
            }
          });
        },
        // 配置
        handleConfig(row) {
          this.configVisble = true;
          this.delId = row.name;
          this.getConfig(row.id);
        },
        async getConfig(id) {
          try {
            const params = {
              module: this.moduleName,
              name: this.delId,
              id,
            };
            const res = await getMoudleConfig(params);
            this.configData = res.data.data.plugin.config;
            this.configTip = res.data.data.plugin.title;
            this.configVisble = true;
          } catch (error) {}
        },
        // 保存配置
        async onSubmit() {
          try {
            const params = {
              module: this.moduleName,
              name: this.delId,
              config: {},
            };
            for (const i in this.configData) {
              params.config[this.configData[i].field] =
                this.configData[i].value;
            }
            this.submitLoading = true;
            const res = await saveMoudleConfig(params);
            this.$message.success(res.data.msg);
            this.configVisble = false;
            this.getGatewayList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getGatewayList();
        },
        // 排序
        sortChange (val) {
          if (val === undefined) {
            this.params.orderby = 'id'
            this.params.sort = 'desc'
          } else {
            this.params.orderby = val.sortBy
            this.params.sort = val.descending ? 'desc' : 'asc'
          }
          this.getGatewayList()
        },
        clearKey() {
          this.params.keywords = "";
          this.seacrh();
        },
        seacrh() {
          this.getGatewayList();
        },
        close() {
          this.visible = false;
          this.$refs.userDialog.reset();
        },

        // 停用/启用
        changeStatus(row) {
          this.delId = row.name;
          this.curStatus = row.status;
          this.statusTip = this.curStatus
            ? window.lang.sureDisable
            : window.lang.sure_Open;
          this.statusVisble = true;
        },
        async sureChange() {
          try {
            let tempStatus = this.curStatus === 1 ? 0 : 1;
            const params = {
              module: this.moduleName,
              name: this.delId,
              status: tempStatus,
            };
            this.submitLoading = true;
            const res = await changeMoudle(params);
            this.$message.success(res.data.msg);
            this.statusVisble = false;
            this.getGatewayList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(res.data.msg);
            this.statusVisble = false;
          }
        },
        closeDialog() {
          this.statusVisble = false;
        },
        // 删除
        deletePay(row) {
          this.delVisible = true;
          this.delId = row.name;
          this.type = row.status === 3 ? "install" : "uninstall";
          this.installTip =
            row.status === 3
              ? window.lang.sureInstall
              : window.lang.sureUninstall;
        },
        async sureDel() {
          try {
            const params = {
              module: this.moduleName,
              name: this.delId,
            };
            this.submitLoading = true;
            const res = await deleteMoudle(this.type, params);
            this.$message.success(res.data.msg);
            // this.params.page = this.data.length > 1 ? this.params.page : this.params.page - 1
            this.delVisible = false;
            this.getGatewayList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.delVisible = false;
            this.$message.error(error.data.msg);
          }
        },
        cancelDel() {
          this.delVisible = false;
        },
      },
      created() {
        this.getGatewayList();
      },
      computed: {
        // 格式化配置里面的options
        computedOptions() {
          return (options) => {
            const arr = [];
            Object.keys(options).map((item) => {
              arr.push({ value: item, label: options[item] });
            });
            return arr;
          };
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
