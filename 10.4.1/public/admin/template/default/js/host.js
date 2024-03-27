(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("host")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = moment;
    new Vue({
      components: {
        comConfig,
        comTreeSelect
      },
      data () {
        return {
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          hover: true,
          currency_prefix:
            JSON.parse(localStorage.getItem("common_set")).currency_prefix ||
            "¥",
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
              width: 300,
            },
            {
              colKey: "client_id",
              title: lang.user + "(" + lang.contact + ")",
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
            // {
            //   colKey: 'active_time',
            //   title: lang.open_time,
            //   width: 170,
            //   sortType: 'all',
            //   sorter: true
            // },
            {
              colKey: "due_time",
              title: lang.due_time,
              width: 170,
              sortType: "all",
              sorter: true,
            },
            // {
            //   colKey: 'status',
            //   title: lang.status,
            //   width: 100,
            //   ellipsis: true
            // },
            // {
            //   colKey: 'op',
            //   title: lang.operation,
            //   width: 100,
            // },
          ],
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
            tab: "using",
            username: "",
            server_id: "",
            product_id: ""
          },
          id: "",
          total: 0,
          pageSizeOptions: [20, 50, 100],
          loading: false,
          title: "",
          delId: "",
          /* 2023-04-11 */
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
          expiring_count: 0,
          searchType: "",
          typeOption: [
            { value: "", label: lang.auth_all },
            { value: "host_id", label: "ID" },
            { value: "product_id", label: lang.product_name },
            { value: "name", label: lang.products_token },
            // { value: "username", label: lang.username },
            { value: "phone", label: lang.phone },
            { value: "email", label: lang.email },
          ],
          isAdvance: false,
          serverList: []
        };
      },
      computed: {
        calcCycle () {
          return (cycle) => {
            return isNaN(Number(cycle)) ? cycle : `${cycle}${lang.year}`;
          };
        },
        calcStatus () {
          return arr => {
            if (this.params.tab === 'using') {
              return [
                { value: "Pending", label: lang.Pending },
                { value: "Active", label: lang.opened_notice },
              ];
            } else if (this.params.tab === 'overdue') {
              return [
                { value: "Active", label: lang.opened_notice },
                { value: "Suspended", label: lang.Suspended },
                { value: "Failed", label: lang.Failed },
              ];
            } else {
              return arr;
            }
          };
        }
      },
      methods: {
        changeType () {
          this.params.keywords = "";
        },
        choosePro (id) {
          this.params.product_id = id;
        },
        async getServerLisrt () {
          try {
            const res = await getInterface({
              page: 1,
              limit: 999
            });
            this.serverList = res.data.data.list.sort((a, b) => {
              return a.id - b.id;
            });
          } catch (error) {

          }
        },
        changeAdvance () {
          this.isAdvance = !this.isAdvance;
        },
        changeHostTab (e) {
          this.params.page = 1;
          this.params.keywords = "";
          this.params.status = "";
          this.getClientList();
        },
        async showIp (id) {
          try {
            this.allIp = [];
            const res = await getAllIp({ id });
            const { assign_ip, dedicate_ip } = res.data.data;
            const temp = assign_ip.split(',');
            temp.unshift(dedicate_ip);
            this.allIp = temp;
            this.ipLoading = true;
          } catch (error) {
            this.ipLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        getQuery (name) {
          const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
          const r = window.location.search.substr(1).match(reg);
          if (r != null) return decodeURI(r[2]);
          return null;
        },
        goHostDetail (row) {
          sessionStorage.currentHostUrl = window.location.href;
          sessionStorage.hostListParams = JSON.stringify(this.params);
          location.href = `host_detail.htm?client_id=${row.client_id}&id=${row.id}`;
        },
        // 搜索
        clearKey () {
          this.params.keywords = "";
          this.seacrh();
        },
        seacrh () {
          this.params.page = 1;
          if (!this.isAdvance) {
            this.params.billing_cycle = "";
            this.params.username = "";
            this.params.status = "";
            this.params.server_id = "";
            this.range = [];
          }
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

          this.getClientList();
        },
        // 分页
        changePage (e) {
          //   this.params.keywords = ''
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getClientList();
        },
        async getClientList () {
          try {
            this.loading = true;
            const params = JSON.parse(JSON.stringify(this.params));
            if (this.searchType && this.searchType !== 'product_id') {
              params[this.searchType] = params.keywords;
              params.keywords = "";
            }
            const res = await getClientPro("", params);
            this.data = res.data.data.list;
            this.total = res.data.data.count;
            this.expiring_count = res.data.data.expiring_count;
            this.loading = false;
          } catch (error) {
            this.loading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 排序
        sortChange (val) {
          if (!val) {
            this.params.orderby = "id";
            this.params.sort = "desc";
          } else {
            this.params.orderby = val.sortBy;
            this.params.sort = val.descending ? "desc" : "asc";
          }
          this.getClientList();
        },

        // 秒级时间戳转xxxx-xx-xx
        initDate (time) {
          const timestamp = time * 1000; // 时间戳
          const date = new Date(timestamp);
          const year = date.getFullYear();
          const month = date.getMonth() + 1; // 月份从 0 开始，所以要加 1
          const day = date.getDate();
          const formattedDate = `${year}-${month
            .toString()
            .padStart(2, "0")}-${day.toString().padStart(2, "0")}`;
          return formattedDate;
        },
      },
      created () {
        if (sessionStorage.hostListParams) {
          this.params = Object.assign(
            this.params,
            JSON.parse(sessionStorage.hostListParams)
          );
          if (this.params.start_time && this.params.end_time) {
            this.range = [
              this.initDate(this.params.start_time),
              this.initDate(this.params.end_time),
            ];
          }
        }
        sessionStorage.removeItem("hostListParams");
        sessionStorage.removeItem("currentHostUrl");
        this.getServerLisrt();
        /* 全局搜索 */
        const searchType = this.getQuery("type") || "";
        const keywords = this.getQuery('keywords') || "";
        if (searchType) {
          this.params.tab = "";
        }
        if (searchType === "status") {
          this.params.status = keywords;
        } else if (searchType === "username") {
          this.params.username = keywords;
        } else if (searchType === "product_id") {
          this.params.product_id = keywords * 1 || "";
        } else if (searchType === "server_id") {
          this.params.server_id = keywords * 1 || "";
        } else if (searchType === "billing_cycle") {
          this.params.billing_cycle = keywords;
        } else if (searchType === "due_time") {
          this.range.push(keywords, keywords);
          this.params.start_time =
            new Date(this.range[0].replace(/-/g, "/")).getTime() / 1000 || "";
          this.params.end_time =
            (new Date(this.range[1].replace(/-/g, "/")).getTime() +
              24 * 3600 * 1000) /
            1000 || "";
        } else {
          this.params.keywords = keywords;
        }
        if (searchType === "due_time" || searchType === "status" || searchType === "billing_cycle"
          || searchType === "username" || searchType === "server_id") {
          this.searchType = "";
          this.isAdvance = true;
        } else {
          this.searchType = searchType;
        }
        /* 全局搜索 end */
        this.getClientList();
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
