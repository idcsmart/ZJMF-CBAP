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
      async created() {
        this.getCommonData();
        await this.getWithdrawaList();
      },
      mounted() {},
      updated() {
        // // 关闭loading
        // document.getElementById('mainLoading').style.display = 'none';
        // document.getElementsByClassName('template')[0].style.display = 'block'
      },
      destroyed() {},
      data() {
        return {
          params: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },
          commonData: {},
          withdrawalArr: [],
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
        back() {
          history.back();
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

        // 获取通用配置
        getCommonData() {
          getCommon().then((res) => {
            if (res.data.status === 200) {
              this.commonData = res.data.data;
              localStorage.setItem(
                "common_set_before",
                JSON.stringify(res.data.data)
              );
              document.title =
                this.commonData.website_name + "-" + lang.finance_btn9;
            }
          });
        },
        async getWithdrawaList() {
          const res = await queryWithdrawIistAPI(this.params);
          if (res.status === 200) {
            const { data } = res.data;
            this.params.total = data.count;
            this.withdrawalArr = data.list.map((item) => {
              if (item.status === 0) {
                item.stateName = "warning";
                item.stateText = lang.finance_text97;
              }
              if (item.status === 1) {
                item.stateName = " ";
                item.stateText = lang.finance_text98;
              }
              if (item.status === 2) {
                item.stateName = "danger";
                item.stateText = lang.finance_text109;
              }
              if (item.status === 3) {
                item.stateName = "success";
                item.stateText = lang.finance_text100;
              }
              return item;
            });
            // console.log(arr);
            // this.withdrawalArr = data.list;
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
