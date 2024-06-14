(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("supplier_product")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    new Vue({
      components: {
        comConfig
      },
      data() {
        return {
          // 分页相关
          params: {
            keywords: "",
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
            supplier_id: "",
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
          // 表格相关
          data: [],
          columns: [
            {
              colKey: "id",
              title: "ID",
              width: 120,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "product_name",
              title: lang.product_name,
              ellipsis: true,
              className: "product-name",
              width: 350,
            },
            {
              colKey: "client_id",
              title: lang.user + "(" + lang.contact+ ")",
              width: 250,
              ellipsis: true,
            },
            {
              colKey: "name",
              title: lang.host_name,
              width: 280,
              ellipsis: true,
            },
            {
              colKey: "renew_amount",
              title: `${lang.money_cycle}`,
              width: 166,
              ellipsis: true,
            },
            {
              colKey: "due_time",
              title: lang.due_time,
              width: 170,
              sortType: "all",
              sorter: true,
            },
          ],
          currency_prefix:
            JSON.parse(localStorage.getItem("common_set")).currency_prefix ||
            "¥",
          currency_suffix: JSON.parse(localStorage.getItem("common_set"))
            .currency_suffix,
          tableLayout: false,
          hover: true,
          loading: false,
          supplier_id: "",
          statusText: {
            Unpaid: lang.Unpaid,
            Pending: lang.Pending,
            Active: lang.Active,
            Suspended: lang.Suspended,
            Deleted: lang.Deleted,
            Failed: lang.Failed,
          },
        };
      },
      filters: {
        filterMoney(money) {
          if (isNaN(money)) {
            return "0.00";
          } else {
            const temp = `${money}`.split(".");
            return parseInt(temp[0]).toLocaleString() + "." + (temp[1] || "00");
          }
        },
      },
      mounted() {},
      created() {
        const temp = this.getQuery(location.search);
        temp.id && (this.supplier_id = temp.id);
        this.getOrderList();
      },
      methods: {
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
        // 搜索框 搜索
        search() {
          this.params.page = 1;
          // 重新拉取申请列表
          this.getOrderList();
        },
        // 清空搜索框
        clearKey() {
          this.params.keywords = "";
          this.params.page = 1;
          // 重新拉取申请列表
          this.getOrderList();
        },
        // 底部分页 页面跳转事件
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getOrderList();
        },
        // 排序
        sortChange(val) {
          if (!val) {
            this.params.orderby = "id";
            this.params.sort = "desc";
          } else {
            this.params.orderby = val.sortBy;
            this.params.sort = val.descending ? "desc" : "asc";
          }
          this.getOrderList();
        },
        // 获取申请列表
        async getOrderList() {
          this.loading = true;
          this.params.supplier_id = this.supplier_id;
          const res = await upstreamHost(this.params);
          this.data = res.data.data.list;
          this.total = res.data.data.count;
          this.loading = false;
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
