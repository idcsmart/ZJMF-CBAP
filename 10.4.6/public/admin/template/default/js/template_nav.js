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
          tab: "template_nav.htm",
          id: "",
          backUrl: `${str}configuration_theme.htm?name=web_switch`,
          name: "web_nav",
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          delLoading: false,
          hover: true,
          loading: false,
          popupProps: {
            overlayStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
              "max-height": "362px",
            }),
          },
          columns: [
            {
              colKey: "drag",
              width: 50,
            },
            {
              colKey: "name",
              ellipsis: true,
              title: lang.temp_first_nav,
            },
            {
              colKey: "second",
              ellipsis: true,
              title: lang.temp_second_nav,
            },
            {
              colKey: "show",
              ellipsis: true,
              title: lang.tem_show,
            },
            {
              title: lang.tem_opt,
              colKey: "op",
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
                message: `${lang.tem_input}${lang.temp_nav_name}`,
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
            parent_id: "",
            name: "",
            description: "",
            file_address: "",
            icon: [],
            show: 0,
          },
          expandedRowKeys: [],
          tabList: [],
          uploadUrl: str + "v1/upload",
          // uploadUrl: 'https://kfc.idcsmart.com/admin/v1/upload',
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          firstNavs: [],
          temp_parent_id: "",
          delDialog: false,
          upgradeDialog: false,
          themeInfo: {},
        };
      },
      computed: {},
      created() {
        this.theme = getQuery().theme || "";
        const navList = JSON.parse(localStorage.getItem("backMenus"));
        let tempArr = navList.reduce((all, cur) => {
          cur.child && all.push(...cur.child);
          return all;
        }, []);
        const curValue = tempArr.filter(
          (item) => item.url === "template_nav.htm"
        )[0]?.id;
        localStorage.setItem("curValue", curValue);
        this.getTabList();
        this.getThemeInfo();
        this.getList();
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
        handleFail() {},
        formatImgResponse(res) {
          if (res.status === 200) {
            return { url: res.data.image_url };
          } else {
            return this.$message.error(res.msg);
          }
        },
        deleteTabLogo() {
          this.formData.icon = [];
        },
        async onDragSort({ current, target, newData }) {
          try {
            let name = "first_web_nav";
            const params = {
              id: [],
              theme: this.theme,
            };
            // 一级拖拽
            if (!current.parent_id) {
              params.id = newData
                .filter((item) => !item.parent_id)
                .map((item) => item.id);
            }
            // 二级拖拽
            if (current.parent_id) {
              name = "second_web_nav";
              params.parent_id = current.parent_id;
              params.id = newData
                .filter((item) => item.parent_id === current.parent_id)
                .map((item) => item.id);
              this.expandedRowKeys = [current.parent_id];
            }
            const res = await changeBaseOrder(name, params);
            this.$message.success(res.data.msg);
            this.getList();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        beforeDragSort({ current, target }) {
          // 首页不能拖动
          if (current.id === 1 || target.id === 1) {
            return false;
          }
          // 不同层级不能拖动
          if (current.parent_id) {
            if (current.parent_id !== target.parent_id) {
              this.$message.error(lang.tem_tip9);
              return false;
            }
          } else {
            if (target.parent_id) {
              this.$message.error(lang.tem_tip9);
              return false;
            }
          }
          return true;
        },
        async changeStatus(val, row) {
          try {
            const res = await changeBaseStatus(this.name, {
              id: row.id,
              show: val,
            });
            this.$message.success(res.data.msg);
            this.getList();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        handleAdd() {
          this.optTitle = `${lang.tem_add}${lang.temp_nav}`;
          this.visible = true;
          this.optType = "add";
          this.formData.id = "";
          this.formData.parent_id = "";
          this.formData.file_address = "";
          this.formData.description = "";
          this.formData.icon = [];
          this.$refs.comDialog && this.$refs.comDialog.reset();
        },
        editHandler(row) {
          this.formData = JSON.parse(JSON.stringify(row));
          this.optTitle = `${lang.tem_edit}${
            row.parent_id ? lang.temp_second_nav : lang.temp_first_nav
          }`;
          this.visible = true;
          this.optType = "update";
          this.temp_parent_id = row.parent_id;
          this.formData.icon = [];
          if (row.icon) {
            const origin = "https://kfc.idcsmart.com";
            let url = "";
            if (row.icon.indexOf(origin) === -1) {
              url = `${origin}/${row.icon}`;
            } else {
              url = row.icon;
            }
            this.formData.icon.push({
              url,
            });
          }
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const params = JSON.parse(JSON.stringify(this.formData));
              if (this.optType === "add") {
                delete params.id;
              }
              params.theme = this.theme;
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
            const res = await getControllerList(this.name, {
              theme: this.theme,
            });
            this.data = res.data.data.list;
            this.total = res.data.data.count;
            this.loading = false;
            this.firstNavs = this.data
              .map((item) => {
                return {
                  id: item.id,
                  name: item.name,
                };
              })
              .filter((item) => item.id !== 1);
            // this.$nextTick(() => {
            //  this.$refs.navTable.expandAll();
            // });
          } catch (error) {
            this.loading = false;
            this.$message.error(error.data.msg);
          }
        },
        changeExpand(node) {
          this.expandedRowKeys = node;
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
