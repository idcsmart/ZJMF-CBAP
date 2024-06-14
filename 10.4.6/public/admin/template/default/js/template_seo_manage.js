(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          theme: "",
          tab: "template_seo_manage.htm",
          id: "",
          tabList: [],
          backUrl: `${str}configuration_theme.htm?name=web_switch`,
          name: "seo",
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          delLoading: false,
          hover: true,
          loading: false,
          total: 0,
          pageSizeOptions: [20, 50, 100],
          params: {
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          popupProps: {
            overlayStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
              "max-height": "362px",
            }),
          },
          columns: [
            {
              colKey: "title",
              title: lang.tem_seo_tit,
              width: 200,
              ellipsis: true,
            },
            {
              colKey: "page_address",
              title: lang.tem_seo_url,
              ellipsis: true,
              width: 200,
            },
            {
              colKey: "keywords",
              title: lang.tem_seo_keyword,
              ellipsis: true,
              width: 200,
            },
            {
              colKey: "description",
              title: lang.tem_description,
              ellipsis: true,
              width: 200,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 100,
            },
          ],
          curId: "",
          optTitle: lang.tem_add,
          optType: "",
          submitLoading: false,
          rules: {
            title: [
              {
                required: true,
                message: `${lang.tem_input}${lang.tem_page_tit}`,
                type: "error",
              },
            ],
            page_address: [
              {
                required: true,
                message: `${lang.tem_input}${lang.tem_seo_url}`,
                type: "error",
              },
              {
                pattern:
                  /^(((ht|f)tps?):\/\/)?([^!@#$%^&*?.\s-]([^!@#$%^&*?.\s]{0,63}[^!@#$%^&*?.\s])?\.)+[a-z]{2,6}\/?/,
                message: lang.tem_tip1,
                type: "warning",
              },
            ],
            keywords: [
              {
                required: true,
                message: `${lang.tem_input}${lang.tem_seo_keyword}`,
                type: "error",
              },
            ],
            description: [
              {
                required: true,
                message: `${lang.tem_input}${lang.tem_description}`,
                type: "error",
              },
            ],
          },
          formData: {
            id: "",
            title: "",
            page_address: "",
            keywords: "",
            description: "",
          },
          delDialog: false,
          upgradeDialog: false,
          themeInfo: {},
        };
      },
      computed: {},
      created() {
        this.theme = getQuery().theme || "";
        this.getThemeInfo();
        this.getList();
        this.getTabList();
      },
      methods: {
        sureUpgrade() {
          this.submitLoading = true;
          upgradeTheme({ theme: this.theme })
            .then((res) => {
              this.submitLoading = false;
              this.upgradeDialog = false;
              this.$message.success(res.data.msg);
              window.location.reload();
            })
            .catch((err) => {
              this.submitLoading = false;
              this.$message.error(err.data.msg);
            });
        },
        sureDel() {
          this.delLoading = true;
          uninstallTheme(this.theme)
            .then((res) => {
              this.$message.success(res.data.msg);
              this.delLoading = false;
              this.delDialog = false;
              location.href = this.backUrl;
            })
            .catch((err) => {
              this.delLoading = false;
              this.$message.error(err.data.msg);
            });
        },
        handleUpgrade() {
          this.upgradeDialog = true;
        },
        getThemeInfo() {
          getThemeLatestVersion(this.theme).then((res) => {
            this.themeInfo = res.data.data;
          });
        },
        handleDelete() {
          this.delDialog = true;
        },
        getTabList() {
          getTemplateControllerTab({ theme: this.theme }).then((res) => {
            this.tabList = res.data.data.list;
          });
        },
        changeTab(val) {
          const curPath = location.pathname
            .split("/")
            .find((item) => item.indexOf("htm") !== -1);
          if (val === curPath) {
            return;
          }
          location.href = val + `?theme=${this.theme}`;
        },
        handleAdd() {
          this.optTitle = lang.tem_add;
          this.visible = true;
          this.optType = "add";
          this.$refs.comDialog && this.$refs.comDialog.reset();
        },
        editHandler(row) {
          this.formData = JSON.parse(JSON.stringify(row));
          this.optTitle = lang.tem_edit;
          this.visible = true;
          this.optType = "update";
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const params = JSON.parse(JSON.stringify(this.formData));
              if (this.optType === "add") {
                delete params.id;
              }
              const res = await addAndUpdateController(
                this.name,
                this.optType,
                params
              );
              this.$message.success(res.data.msg);
              this.getList();
              this.visible = false;
              this.submitLoading = false;
            } catch (error) {
              console.log("error", error);
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        deleteHandler(row) {
          this.curId = row.id;
          this.delVisible = true;
        },
        async sureDelete() {
          try {
            this.delLoading = true;
            const res = await delController(this.name, { id: this.curId });
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.delLoading = false;
            this.getList();
          } catch (error) {
            this.delVisible = false;
            this.delLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        async getList() {
          try {
            this.loading = true;
            const res = await getControllerList(this.name, this.params);
            this.data = res.data.data.list;
            this.total = res.data.data.count;
            this.loading = false;
          } catch (error) {
            this.loading = false;
            this.$message.error(error.data.msg);
          }
        },
        sortChange(val) {
          if (!val) {
            this.params.orderby = "id";
            this.params.sort = "desc";
          } else {
            this.params.orderby = val.sortBy;
            this.params.sort = val.descending ? "desc" : "asc";
          }
          this.getList();
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.params.keywords = "";
          this.getList();
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
