(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    new Vue({
      components: {
        asideMenu,
        topMenu,
      },
      created() {
        this.getCommonData();
        this.getHelpList();
        this.getIdInUrl();
      },
      mounted() {
        this.getUrlId();
      },
      updated() {
        document.getElementById("mainLoading").style.display = "none";
        document.getElementsByClassName("template")[0].style.display = "block";
      },
      destroyed() {},
      computed: {
        calStr() {
          return (str) => {
            const temp =
              str &&
              str
                .replace(/&lt;/g, "<")
                .replace(/&gt;/g, ">")
                .replace(/&quot;/g, '"')
                .replace(/&amp;lt;/g, "<")
                .replace(/&amp;gt;/g, ">")
                .replace(/ &amp;lt;/g, "<")
                .replace(/&amp;gt; /g, ">")
                .replace(/&amp;gt; /g, ">")
                .replace(/&amp;quot;/g, '"')
                .replace(/&amp;amp;nbsp;/g, " ")
                .replace(/&amp;#039;/g, "'")
                .replace("<?php", "&lt;?php");
            return temp;
          };
        },
      },
      data() {
        return {
          commonData: {},
          helpList: [],
          // 目录激活项
          activeId: "0",
          preId: "",
          detailData: {},
          helpId: 0,
          contentLoading: false,
          menuId: 1,
          activeIndex: "1",
          newsUrl: "",
          helpUrl: "",
          downloadUrl: "",
          params: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },
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
      },
      methods: {
        getUrlId() {
          this.newsUrl = `plugin/${this.$refs.news?.$attrs.id}/source.htm`;
          this.helpUrl = `plugin/${this.$refs.help?.$attrs.id}/source.htm`;
          this.downloadUrl = `plugin/${this.$refs.download?.$attrs.id}/source.htm`;
        },
        handleClick() {
          if (this.activeIndex == "2") {
            location.href = `/${this.newsUrl}`;
          }
          if (this.activeIndex == "3") {
            location.href = `/${this.downloadUrl}`;
          }
        },
        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          document.title = this.commonData.website_name + "-帮助中心";
        },
        // 去帮助中心首页
        toHelpIndex() {
          location.href = `source.htm`;
        },
        // 获取帮助文档列表
        getHelpList() {
          const params = {
            keywords: "",
          };
          helpList(params).then((res) => {
            if (res.data.status === 200) {
              this.helpList = res.data.data.list;
            }
          });
        },
        // 目录展开
        handleOpen(key, keyPath) {
          // console.log(key, keyPath);
        },
        // 目录关闭
        handleClose(key, keyPath) {
          // console.log(key, keyPath);
        },
        // 菜单项点击
        itemClick(id) {
          this.contentLoading = true;
          // 获取帮助文档详情
          const params = {
            id,
          };
          this.preId = this.activeId;
          this.activeId = id.toString();
          helpDetails(params).then((res) => {
            if (res.data.status === 200) {
              this.detailData = res.data.data.help;
            }
            this.contentLoading = false;
          });
        },
        // 附件下载
        downloadfile(item) {
          const url = item.url;
          const downloadElement = document.createElement("a");
          downloadElement.href = url;
          downloadElement.download = item.name; // 下载后文件名
          document.body.appendChild(downloadElement);
          downloadElement.click(); // 点击下载
        },
        getIdInUrl() {
          let url = window.location.href;
          let getqyinfo = url.split("?")[1];
          let getqys = new URLSearchParams("?" + getqyinfo);
          let id = getqys.get("id");
          this.activeId = id;
          if (id) {
            this.itemClick(id);
          }
        },
        // 解析url
        getQuery(url) {
          const str = url.substr(url.indexOf("?") + 1);
          const arr = str.split("&");
          const res = {};
          for (let i = 0; i < arr.length; i++) {
            const item = arr[i].split("=");
            res[item[0]] = item[1];
          }
          return res;
        },
        // 关键字搜索
        inputChange() {
          const params = {
            keywords: this.params.keywords,
          };
          helpList(params).then((res) => {
            if (res.data.status === 200) {
              const list = res.data.data.list;
              let isSearch = false;
              list.forEach((element) => {
                element.helps.forEach((item) => {
                  if (item.search) {
                    isSearch = true;
                    const id = item.id;
                    this.activeId = id.toString();
                    this.itemClick(id);
                  }
                });
              });
              if (!isSearch) {
                this.$message({
                  message: "查询结果为空！",
                  type: "warning",
                });
              }
            }
          });
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
