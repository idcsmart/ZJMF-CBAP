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
          tab: "template_bottom_nav.htm",
          tabList: [],
          id: "",
          backUrl: `${str}configuration_theme.htm?name=web_switch`,
          name: "bottom_bar_nav",
          groupName: "bottom_bar_group",
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
              title: lang.temp_belong_group,
            },
            {
              colKey: "second",
              ellipsis: true,
              title: lang.temp_nav_name,
            },
            {
              colKey: "url",
              ellipsis: true,
              title: lang.temp_jump_url,
            },
            {
              colKey: "show",
              ellipsis: true,
              title: lang.tem_show,
              width: 200,
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
            url: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_jump_url}`,
                type: "error",
              },
              {
                pattern:
                  /^(((ht|f)tps?):\/\/)?([^!@#$%^&*?.\s-]([^!@#$%^&*?.\s]{0,63}[^!@#$%^&*?.\s])?\.)+[a-z]{2,6}\/?/,
                message: lang.tem_tip1,
                type: "warning",
              },
            ],
            group_id: [
              {
                required: true,
                message: `${lang.tem_select}${lang.temp_belong_group}`,
                type: "error",
              },
            ],
          },
          formData: {
            id: "",
            group_id: "",
            name: "",
            url: "",
            show: 0,
          },
          expandedRowKeys: [],
          firstNavs: [],
          temp_parent_id: "",
          // 分组相关
          deleteType: "", // name | group
          groupList: [],
          groupVisble: false,
          groupLoading: false,
          groupColumns: [
            {
              colKey: "id",
              ellipsis: true,
              title: "ID",
            },
            {
              colKey: "name",
              ellipsis: true,
              title: lang.temp_group_name,
            },
            {
              title: lang.tem_opt,
              colKey: "op",
              width: 120,
            },
          ],
          delDialog: false,
          upgradeDialog: false,
          themeInfo: {},
        };
      },
      created() {
        this.theme = getQuery().theme || "";
        this.getTabList();
        this.getThemeInfo();
        this.getList();
        this.getGroupList();
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
        /* 分组 */
        editGroup(row) {
          this.curId = row.id;
          this.optType = "update";
          this.formData.id = row.id;
          this.formData.name = row.name;
        },
        async handleGroup() {
          try {
            this.optType = "add";
            this.formData.name = "";
            this.$refs.groupDialog && this.$refs.groupDialog.reset();
            this.groupVisble = true;
            this.optTitle = lang.temp_group_manage;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        async getGroupList() {
          try {
            this.groupLoading = true;
            const res = await getControllerList(this.groupName, {
              theme: this.theme,
            });
            this.groupList = res.data.data.list;
            this.groupLoading = false;
          } catch (error) {
            this.groupLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        deleteGroup(row) {
          this.curId = row.id;
          this.delVisible = true;
          this.deleteType = "group";
        },
        async submitGroup({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const params = {
                id: this.formData.id,
                name: this.formData.name,
              };
              if (this.optType === "add") {
                delete params.id;
              }
              params.theme = this.theme;
              const res = await addAndUpdateController(
                this.groupName,
                this.optType,
                params
              );
              this.$message.success(res.data.msg);
              this.getGroupList();
              this.getList();
              this.optType = "add";
              this.formData.name = "";
              this.$refs.groupDialog && this.$refs.groupDialog.reset();
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
        /* 分组 end */
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
            let name = "bottom_bar_group";
            const params = {
              id: [],
              theme: this.theme,
            };
            // 一级拖拽
            if (!current.group_id) {
              params.id = newData
                .filter((item) => !item.group_id)
                .map((item) => item.id);
            }
            // 二级拖拽
            if (current.group_id) {
              name = "bottom_bar_nav";
              params.group_id = current.group_id;
              params.id = newData
                .filter((item) => item.group_id === current.group_id)
                .map((item) => item.id);
              this.expandedRowKeys = [current.group_id];
            }
            const res = await changeBaseOrder(name, params);
            this.$message.success(res.data.msg);
            this.getList();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        beforeDragSort({ current, target }) {
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
          this.formData.group_id = "";
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
          if (row.group_id) {
            this.deleteType = "name";
          } else {
            this.deleteType = "group";
          }
        },
        async sureDelete() {
          try {
            let name = "";
            if (this.deleteType === "name") {
              name = this.name;
            } else if (this.deleteType === "group") {
              name = this.groupName;
            }
            this.delLoading = true;
            const res = await delController(name, { id: this.curId });
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.delLoading = false;
            if (name === this.name) {
              this.getList();
            } else if (name === this.groupName) {
              this.optType = "add";
              this.formData.name = "";
              this.$refs.comDialog && this.$refs.comDialog.reset();
              this.getGroupList();
              this.getList();
            }
          } catch (error) {
            this.delVisible = false;
            this.delLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        async getList() {
          try {
            this.loading = true;
            const res = await getControllerList(this.name, {
              theme: this.theme,
            });
            this.data = res.data.data.list.map((item) => {
              item.key = item.id;
              item.children.forEach((el) => {
                el.key = `s-${el.id}`;
              });
              return item;
            });
            this.total = res.data.data.count;
            this.loading = false;
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
