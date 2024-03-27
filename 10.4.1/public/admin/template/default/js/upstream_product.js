(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("upstream_product")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    new Vue({
      components: {
        comConfig,
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
            billing_cycle: "",
            status: "",
            start_time: "",
            end_time: "",
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
          money: {},
          tableLayout: false,
          hover: true,
          loading: false,
          range: [],
          productStatus: [
            { value: "Unpaid", label: lang.Unpaid },
            { value: "Pending", label: lang.Pending },
            { value: "Active", label: lang.opened_notice },
            { value: "Suspended", label: lang.Suspended },
            { value: "Deleted", label: lang.Deleted },
            { value: "Failed", label: lang.Failed },
            { value: "Cancelled", label: lang.Cancelled },
          ],
          allIp: [],
          ipLoading: false,
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
      created() {
        const temp = this.getQuery(location.search);
        temp.id && (this.supplier_id = temp.id);
        this.getOrderList();
      },
      methods: {
        async showIp(id) {
          try {
            this.allIp = [];
            const res = await getAllIp({ id });
            const { assign_ip, dedicate_ip } = res.data.data;
            const temp = assign_ip.split(",");
            temp.unshift(dedicate_ip);
            this.allIp = temp;
            this.ipLoading = true;
          } catch (error) {
            this.ipLoading = false;
            this.$message.error(error.data.msg);
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
        // 搜索框 搜索
        seacrh() {
          this.params.page = 1;
          if (this.range.length > 0) {
            this.params.start_time =
              new Date(this.range[0].replace(/-/g, "/")).getTime() / 1000 || "";
            this.params.end_time =
              (new Date(this.range[1].replace(/-/g, "/")).getTime() +
                24 * 3600 * 1000) /
                1000 || "";
          } else {
            this.params.start_time = "";
            this.params.end_time = "";
          }
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
