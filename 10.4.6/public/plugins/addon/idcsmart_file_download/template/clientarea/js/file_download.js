(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("file_download")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    new Vue({
      components: {
        asideMenu,
        topMenu,
        pagination,
      },
      created() {
        this.getCommonData();
        this.getFileFolder();
      },
      mounted() {
        this.getUrlId();
      },
      updated() {
        // // 关闭loading
        document.getElementById("mainLoading").style.display = "none";
        document.getElementsByClassName("file_download")[0].style.display =
          "block";
      },
      destroyed() {},
      data() {
        return {
          params: {
            page: 1,
            limit: 10,
            pageSizes: [10, 20, 50],
            total: 0,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },
          commonData: {},
          folder: [],
          folderNum: 0,
          curId: "",
          tableData: [],
          loading: false,
          curTit: "",
          activeIndex: "3",
          newsUrl: "",
          helpUrl: "",
          downloadUrl: "",
          isDownLoading: false,
        };
      },
      filters: {
        formateTime(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000);
          } else {
            return "--";
          }
        },
        formateByte(size) {
          if (size < 1024 * 1024) {
            return (size / 1024).toFixed(2) + "KB";
          } else {
            return (size / (1024 * 1024)).toFixed(2) + "MB";
          }
        },
      },
      methods: {
        getUrlId() {
          this.newsUrl = `plugin/${this.$refs.news?.$attrs.id}/source.htm`;
          this.helpUrl = `plugin/${this.$refs.help?.$attrs.id}/source.htm`;
          this.downloadUrl = `plugin/${this.$refs.download?.$attrs.id}/source.htm`;
        },
        handleClick() {
          if (this.activeIndex == "1") {
            location.href = `/${this.helpUrl}`;
          }
          if (this.activeIndex == "2") {
            location.href = `/${this.newsUrl}`;
          }
        },
        // 获取文件夹
        async getFileFolder() {
          try {
            const res = await getFileFolder();
            if (res.data.status === 200) {
              this.folder = res.data.data.list;
              this.curId = res.data.data.list[0].id;
              this.folderNum = this.folder.reduce((all, cur) => {
                all += cur.file_num;
                return all;
              }, 0);
              this.curTit = res.data.data.list[0].name;
              this.getData();
            }
          } catch (error) {
            console.log(error);
          }
        },
        getAllFiles() {
          this.curId = "";
          this.curTit = "全部";
          this.params.page = 1;
          this.getData();
        },
        // 选择文件夹
        changeFolder(item) {
          this.curId = item.id;
          this.curTit = item.name;
          this.params.page = 1;
          this.getData();
        },
        async getData() {
          try {
            const params = {
              addon_idcsmart_file_folder_id: this.curId,
              ...this.params,
            };
            this.loading = true;
            delete params.pageSizes;
            delete params.total;
            const res = await getFileList(params);
            if (res.data.status === 200) {
              this.tableData = res.data.data.list;
              this.params.total = res.data.data.count;
              this.loading = false;
            }
          } catch (error) {
            this.loading = false;
            console.log(error);
          }
        },
        // 下载文件
        async downFile(item, index) {
          try {
            downloadFile(item.id)
              .then((res) => {
                window.open(res.data.data.url);
              })
              .catch((err) => {
                this.$message.error(err.data.msg);
              });
          } catch (error) {}
        },
        // 搜索
        inputChange() {
          this.params.page = 1;
          this.getData();
        },
        // 每页展示数改变
        sizeChange(e) {
          this.params.limit = e;
          this.params.page = 1;
          // 获取列表
          this.getData();
        },
        // 当前页改变
        currentChange(e) {
          this.params.page = e;
          this.getData();
        },

        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          document.title =
            this.commonData.website_name + `-${lang.file_download}`;
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
