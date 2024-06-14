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
          tabList: [],
          theme: "",
          tab: "template_index_banner.htm",
          id: "",
          backUrl: `${str}configuration_theme.htm?name=web_switch`,
          name: "index_banner",
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          submitLoading: false,
          hover: true,
          loading: false,
          // 图片上传相关
          uploadUrl: str + "v1/upload",
          // uploadUrl: 'https://kfc.idcsmart.com/admin/v1/upload',
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          // banner
          bannerColumns: [
            {
              colKey: "drag",
              width: 30,
              className: "drag-icon",
            },
            { colKey: "img", title: lang.tem_banner, width: "300" },
            {
              colKey: "url",
              title: lang.tem_jump_link,
              width: "200",
              ellipsis: true,
            },
            {
              colKey: "time",
              title: lang.tem_time_range,
              width: "230",
              ellipsis: true,
            },
            {
              colKey: "show",
              title: lang.tem_show,
              width: "100",
              ellipsis: true,
            },
            {
              colKey: "notes",
              title: lang.tem_notes,
              width: "150",
              ellipsis: true,
            },
            { colKey: "op", title: lang.tem_opt, width: "120" },
          ],
          tempBanner: [],
          editFile: [],
          editItem: {
            id: "",
            url: "",
            img: [],
            show: false,
            notes: "",
            edit: false,
            timeRange: [],
          },
          delVisible: false,
          curId: "",
          optType: "",
          optTitle: "",
          delDialog: false,
          upgradeDialog: false,
          themeInfo: {},
        };
      },
      created() {
        this.theme = getQuery().theme || "";
        this.getTabList();
        this.getThemeInfo();
        this.getBannerList();
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
        addBanner() {
          this.tempBanner = this.tempBanner
            .filter((item) => item.id)
            .map((item) => {
              item.edit = false;
              return item;
            });
          this.tempBanner.push({
            url: "",
            img: "",
            start_time: "",
            end_time: "",
            show: 0,
            notes: "",
            edit: true,
            timeRange: [],
          });
          this.editItem = {
            id: "",
            url: "",
            img: [],
            show: 0,
            notes: "",
            edit: false,
            timeRange: [],
          };
          this.optType = "add";
        },
        handlerEdit(row) {
          this.tempBanner = this.tempBanner
            .filter((item) => item.id)
            .map((item) => {
              item.edit = false;
              return item;
            });
          row.edit = true;
          this.optType = "update";
          this.editItem = JSON.parse(JSON.stringify(row));
          this.editItem.img = [{ url: row.img }];
          this.editItem.timeRange = [
            moment.unix(row.start_time).format("YYYY/MM/DD"),
            moment.unix(row.end_time).format("YYYY/MM/DD"),
          ];
        },
        delteItem(row) {
          this.delVisible = true;
          this.curId = row.id;
        },
        async changeShow(e, row) {
          try {
            if (row.edit) {
              return false;
            }
            const res = await changeBaseStatus(this.name, {
              id: row.id,
              show: e,
            });
            this.$message.success(res.data.msg);
            this.getBannerList();
          } catch (error) {
            this.$message.error(error.data.msg);
            this.getBannerList();
          }
        },
        async sureDelete() {
          try {
            this.submitLoading = true;
            const res = await delController(this.name, {
              id: this.curId,
            });
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.getBannerList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        cancelItem(row, index) {
          if (!row.id) {
            this.tempBanner.splice(index, 1);
          }
          row.edit = false;
        },
        changeFile(file) {
          this.editItem.img = [
            {
              url: file.response.data.image_url,
            },
          ];
        },
        async saveItem(row, index) {
          try {
            const temp = JSON.parse(JSON.stringify(this.editItem));
            if (temp.img.length === 0) {
              return this.$message.error(`${lang.upload}${lang.picture}`);
            }
            if (temp.timeRange.length === 0) {
              return this.$message.error(`${lang.select}${lang.time}`);
            }
            if (!temp.url) {
              return this.$message.error(`${lang.input}${lang.feed_link}`);
            }
            const reg =
              /^(((ht|f)tps?):\/\/)?([^!@#$%^&*?.\s-]([^!@#$%^&*?.\s]{0,63}[^!@#$%^&*?.\s])?\.)+[a-z]{2,6}\/?/;
            if (temp.url && !reg.test(temp.url)) {
              return this.$message.error(`${lang.input}${lang.feed_tip}`);
            }
            temp.start_time = parseInt(
              new Date(temp.timeRange[0].replaceAll("-", "/")).getTime() / 1000
            );
            temp.end_time = parseInt(
              new Date(temp.timeRange[1].replaceAll("-", "/")).getTime() / 1000
            );
            if (temp.lastModified) {
              temp.img = temp.img[0]?.response.data.image_url;
            } else {
              temp.img = temp.img[0].url;
            }
            temp.edit = false;
            if (this.optType === "add") {
              delete temp.id;
            }
            temp.show = row.show;
            const res = await addAndUpdateController(
              this.name,
              this.optType,
              temp
            );
            this.$message.success(res.data.msg);
            this.getBannerList();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        onDrop() {},
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
            this.getBannerList();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        formatImgResponse(res) {
          if (res.status === 200) {
            return { url: res.data.image_url };
          } else {
            return this.$message.error(res.msg);
          }
        },
        getTabList() {
          getTemplateControllerTab({ theme: this.theme }).then((res) => {
            this.tabList = res.data.data.list;
          });
        },
        async getBannerList() {
          try {
            this.loading = true;
            const res = await getControllerList(this.name);
            this.tempBanner = res.data.data.list.map((item) => {
              item.edit = false;
              return item;
            });
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
