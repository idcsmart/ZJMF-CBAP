(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('order-details')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = moment
    new Vue({
      data () {
        return {
          id: '',
          data: [],
          tableLayout: true,
          bordered: true,
          hover: true,
          total: 0,
          pageSizeOptions: [20, 50, 100],
          params: {
            order_id: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          loading: false,
          columns: [
            {
              colKey: 'transaction_number',
              title: lang.flow_number,
              ellipsis: true
            },
            {
              colKey: 'amount',
              title: lang.money,
              ellipsis: true
            },
            {
              colKey: 'gateway',
              title: lang.pay_way,
              width: 200,
              ellipsis: true
            },
            {
              colKey: 'create_time',
              title: lang.trade_time,
              width: 200,
              ellipsis: true
            },
          ],
        }
      },
      mounted () {
        this.getFlowList()
      },
      methods: {
        async getFlowList () {
          try {
            const res = await getClientOrder(this.params)
            this.data = res.data.data.list
            this.total = res.data.data.count
            this.loading = false
          } catch (error) {
            this.loading = false
            this.$message.error(res.data.msg)
          }
        },
        // 排序
        sortChange (val) {
          if (!val) {
            this.params.orderby = 'id'
            this.params.sort = 'desc'
          } else {
            this.params.orderby = val.sortBy
            this.params.sort = val.descending ? 'desc' : 'asc'
          }
          this.getFlowList()
        },
        // 分页
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getFlowList()
        },
      },
      created () {
        this.id = this.params.order_id = location.href.split('?')[1].split('=')[1];
        this.currency_prefix = JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥';
        document.title = lang.flow + '-' + localStorage.getItem('back_website_name');
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);