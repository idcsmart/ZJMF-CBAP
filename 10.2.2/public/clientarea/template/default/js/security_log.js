(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        asideMenu,
        topMenu,
        pagination,
      },
      created() {
        this.getCommonData();
      },
      mounted() {
        this.getLogList();
      },
      updated() {
        // // 关闭loading
        // document.getElementById('mainLoading').style.display = 'none';
        // document.getElementsByClassName('template')[0].style.display = 'block'
      },
      destroyed() {},
      data() {
        return {
          isShowAPI: false,
          isShowAPILog: false,
          params: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
            type: "api",
          },
          commonData: {},
          activeName: "3",
          loading: false,
          dataList: [],
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
        getRule(arr) {
          let isShow1 = this.showFun(arr, "ApiController::list");
          let isShow2 = this.showFun(arr, "LogController::list");
          if (isShow1) {
            this.isShowAPI = true;
            this.activeName = this.activeName;
          } else {
            this.activeName = "2";
          }
          if (isShow2) {
            this.isShowAPILog = true;
          }
          this.handleClick();
        },
        showFun(arr, str) {
          if (typeof arr == "string") {
            return true;
          } else {
            let isShow = "";
            isShow = arr.find((item) => {
              let isHave = item.includes(str);
              if (isHave) {
                return isHave;
              }
            });
            return isShow;
          }
        },
        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
        },
        // 每页展示数改变
        sizeChange(e) {
          this.params.limit = e;
          this.params.page = 1;
          // 获取列表
        },
        // 当前页改变
        currentChange(e) {
          this.params.page = e;
        },
        inputChange() {
          this.params.page = 1;
          this.getLogList();
        },
        handleClick(tap, event) {
          if (this.activeName == 1) {
            location.href = "security.html";
          }
          if (this.activeName == 2) {
            location.href = "security_ssh.html";
          }
          if (this.activeName == 4) {
            location.href = "security_group.html";
          }
        },

        getLogList() {
          this.loading = true;
          logList(this.params)
            .then((res) => {
              if (res.data.status === 200) {
                this.dataList = res.data.data.list;
                this.params.total = res.data.data.count;
              }
              this.loading = false;
            })
            .catch((err) => {
              this.loading = false;
            });
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
