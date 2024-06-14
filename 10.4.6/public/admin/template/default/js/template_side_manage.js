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
          tab: "template_side_manage.htm",
          id: "",
          backUrl: `${str}configuration_theme.htm?name=web_switch`,
          name: "side_floating_window",
          data: [],
          tabList: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          delLoading: false,
          hover: true,
          loading: false,
          columns: [
            {
              colKey: "drag",
              width: 30,
              className: "drag-icon",
            },
            {
              colKey: "id",
              title: lang.tem_num,
              ellipsis: true,
              width: 120,
            },
            {
              colKey: "name",
              title: lang.tem_name,
              ellipsis: true,
            },
            {
              colKey: "icon",
              title: lang.tem_icon,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 120,
            },
          ],
          curId: "",
          optTitle: lang.tem_add,
          optType: "",
          submitLoading: false,
          rules: {
            name: [
              {
                required: true,
                message: `${lang.tem_input}${lang.tem_quick_entry}`,
                type: "error",
              },
            ],
            icon: [
              { required: true, message: `${lang.tem_tip6}`, type: "error" },
            ],
            content: [
              {
                required: true,
                message: `${lang.tem_input}${lang.tem_hover_content}`,
                type: "error",
              },
            ],
          },
          formData: {
            id: "",
            name: "",
            icon: [],
            content: "",
          },
          uploadUrl: str + "v1/upload",
          // uploadUrl: 'https://kfc.idcsmart.com/admin/v1/upload',
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          delDialog: false,
          upgradeDialog: false,
          themeInfo: {},
        };
      },
      created() {
        this.theme = getQuery().theme || "";
        this.getList();
        this.getThemeInfo();
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
        changeTab(val) {
          const curPath = location.pathname
            .split("/")
            .find((item) => item.indexOf("htm") !== -1);
          if (val === curPath) {
            return;
          }
          location.href = val + `?theme=${this.theme}`;
        },
        formatImgResponse(res) {
          if (res.status === 200) {
            return { url: res.data.image_url };
          } else {
            return this.$message.error(res.msg);
          }
        },
        deleteTabLogo(e) {
          this.formData.icon = [];
          e.stopPropagation();
        },
        handleFail() {},
        async onDragSort(params) {
          try {
            this.tempBanner = params.currentData;
            const arr = this.tempBanner.reduce((all, cur) => {
              all.push(cur.id);
              return all;
            }, []);
            const res = await changeBaseOrder(this.name, {
              id: arr,
              theme: this.theme,
            });
            this.$message.success(res.data.msg);
            this.getList();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        handleAdd() {
          this.optTitle = `${lang.tem_add}${lang.tem_entry}`;
          this.visible = true;
          this.optType = "add";
          this.$refs.comDialog && this.$refs.comDialog.reset();
        },
        editHandler(row) {
          this.formData = JSON.parse(JSON.stringify(row));
          if (row.icon) {
            this.formData.icon = [];
            this.formData.icon.push({
              url: row.icon,
            });
          }
          this.optTitle = `${lang.tem_edit}${lang.tem_entry}`;
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
              params.icon = params.icon[0]?.url || "";
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
        getTabList() {
          getTemplateControllerTab({ theme: this.theme }).then((res) => {
            this.tabList = res.data.data.list;
          });
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
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
