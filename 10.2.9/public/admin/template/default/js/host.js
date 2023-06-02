(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('host')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = moment
    new Vue({
      data() {
        return {
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          hover: true,
          currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥',
          columns: [
            {
              colKey: 'id',
              title: 'ID',
              width: 120,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'product_name',
              title: lang.product_name,
              ellipsis: true,
              className: 'product-name',
              width: 300
            },
            {
              colKey: 'client_id',
              title: lang.user + '(' + lang.company + ')',
              width: 250,
              ellipsis: true
            },
            {
              colKey: 'name',
              title: lang.host_name,
              width: 280,
              ellipsis: true
            },
            {
              colKey: 'renew_amount',
              title: `${lang.money_cycle}`,
              width: 166,
              ellipsis: true
            },
            // {
            //   colKey: 'active_time',
            //   title: lang.open_time,
            //   width: 170,
            //   sortType: 'all',
            //   sorter: true
            // },
            {
              colKey: 'due_time',
              title: lang.due_time,
              width: 170,
              sortType: 'all',
              sorter: true
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
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc',
            billing_cycle: '',
            status: '',
            start_time: '',
            end_time: ''
          },
          id: '',
          total: 0,
          pageSizeOptions: [20, 50, 100],
          loading: false,
          title: '',
          delId: '',
          /* 2023-04-11 */
          range: [],
          productStatus: [
            { value: 'Unpaid', label: lang.Unpaid },
            { value: 'Pending', label: lang.Pending },
            { value: 'Active', label: lang.opened_notice },
            { value: 'Suspended', label: lang.Suspended },
            { value: 'Deleted', label: lang.Deleted },
            { value: 'Failed', label: lang.Failed },
            { value: 'Cancelled', label: lang.Cancelled },
          ],
        }
      },
      methods: {
        goHostDetail(row) {
          sessionStorage.currentHostUrl = window.location.href
          sessionStorage.hostListParams = JSON.stringify(this.params)
          location.href = `host_detail.htm?client_id=${row.client_id}&id=${row.id}`
        },
        // 搜索
        clearKey() {
          this.params.keywords = ''
          this.seacrh()
        },
        seacrh() {
          this.params.page = 1
          if (this.range.length > 0) {
            this.params.start_time = new Date(this.range[0].replace(/-/g, '/')).getTime() / 1000 || ''
            this.params.end_time = (new Date(this.range[1].replace(/-/g, '/')).getTime() + 24 * 3600 * 1000) / 1000 || ''
          } else {
            this.params.start_time = ''
            this.params.end_time = ''
          }
          this.getClientList()
        },
        // 分页
        changePage(e) {
          //   this.params.keywords = ''
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getClientList()
        },
        async getClientList() {
          try {
            this.loading = true
            const res = await getClientPro('', this.params)
            this.data = res.data.data.list
            this.total = res.data.data.count
            this.loading = false
          } catch (error) {
            this.loading = false
            this.$message.error(error.data.msg)
          }
        },
        // 排序
        sortChange(val) {
          if (!val) {
            this.params.orderby = 'id'
            this.params.sort = 'desc'
          } else {
            this.params.orderby = val.sortBy
            this.params.sort = val.descending ? 'desc' : 'asc'
          }
          this.getClientList()
        },
        // 删除
        deltePro(row) {
          this.delVisible = true
          this.delId = row.id
        },
        async onConfirm() {
          try {
            const res = await deletePro(this.delId)
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getClientList()
          } catch (error) {
            this.delVisible = false
            this.$message.error(error.data.msg)
          }
        },
        // 秒级时间戳转xxxx-xx-xx
        initDate(time) {
          const timestamp = time * 1000; // 时间戳
          const date = new Date(timestamp);
          const year = date.getFullYear();
          const month = date.getMonth() + 1; // 月份从 0 开始，所以要加 1
          const day = date.getDate();
          const formattedDate = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
          return formattedDate
        },
      },
      created() {
        if (sessionStorage.hostListParams) {
          this.params = Object.assign(this.params, JSON.parse(sessionStorage.hostListParams))
          if (this.params.start_time && this.params.end_time) {
            this.range = [this.initDate(this.params.start_time), this.initDate(this.params.end_time)]
          }
        }
        sessionStorage.removeItem('hostListParams')
        sessionStorage.removeItem('currentHostUrl')
        this.getClientList()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);

