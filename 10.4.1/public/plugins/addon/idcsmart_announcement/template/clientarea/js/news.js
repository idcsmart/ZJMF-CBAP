(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("news")[0];
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
        for (let i = 0; i < 4; i++) {
          this.defaultImg.push(
            `/plugins/addon/idcsmart_news/template/clientarea/img/news_0${
              i + 1
            }.png`
          );
        }
      },
      mounted() {
        this.getUrlId();
      },
      updated() {
        // // 关闭loading
        document.getElementById("mainLoading").style.display = "none";
        document.getElementsByClassName("news")[0].style.display = "block";
      },
      destroyed() {},
      data() {
        return {
          params: {
            page: 1,
            limit: 20,
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
          activeIndex: "2",
          newsUrl: "",
          helpUrl: "",
          downloadUrl: "",
          tableLoading: true,
          defaultImg: [],
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
            return size / (1024 * 1024).toFixed(2) + "MB";
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
          if (this.activeIndex == "3") {
            location.href = `/${this.downloadUrl}`;
          }
        },
        goDetail(id) {
          location.href = `news_detail.htm?id=${id}`;
        },
        // 获取文件夹
        async getFileFolder() {
          try {
            const res = await getNews();
            if (res.data.status === 200) {
              this.folder = res.data.data.list;
              this.curId = res.data.data.list[0].id;
              this.folderNum = this.folder.reduce((all, cur) => {
                all += cur.news_num;
                return all;
              }, 0);
              this.curTit = res.data.data.list[0].name;
              this.getData();
            }
          } catch (error) {
            console.log(error);
          }
        },
        getAllnews() {
          this.params.page = 1;
          this.curId = "";
          this.curTit = lang.news_text5;
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
          this.tableLoading = true;
          try {
            const params = {
              addon_idcsmart_news_type_id: this.curId,
              ...this.params,
            };
            // this.loading = this.$loading({
            //   text: 'Loading',
            //   spinner: 'el-icon-loading',
            //   fullscreen: false,
            //   background: 'rgba(255, 255, 255, 0.7)',
            //   target: document.querySelector('#news-list')
            // });
            delete params.pageSizes;
            delete params.total;
            const res = await getNewsList(params);
            if (res.data.status === 200) {
              this.tableData = res.data.data.list;
              this.params.total = res.data.data.count;
              //   this.loading.close()
            }
            this.tableLoading = false;
          } catch (error) {
            this.loading = false;
            this.tableLoading = false;
            // this.loading.close()
          }
        },
        // 下载文件
        async downFile(row) {
          try {
            const res = await downloadFile({ id: row.id });
            // const fileName = row.name + "." + row.filetype;
            const fileName = row.name;
            const _res = res.data;
            const blob = new Blob([_res]);
            const downloadElement = document.createElement("a");
            const href = window.URL.createObjectURL(blob); // 创建下载的链接
            downloadElement.href = href;
            downloadElement.download = decodeURI(fileName); // 下载后文件名
            document.body.appendChild(downloadElement);
            downloadElement.click(); // 点击下载
            document.body.removeChild(downloadElement); // 下载完成移除元素
            window.URL.revokeObjectURL(href); // 释放掉blob对象
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
          document.title = this.commonData.website_name + `-${lang.news_text1}`;
        },
      },

      // 监听滚动
      // 获取右侧分类距离顶部的距离
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
