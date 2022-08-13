(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('host')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = moment
    new Vue({
      data () {
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
              colKey: 'client_id',
              title: lang.user + '(' + lang.company + ')',
              width: 250
            },
            {
              colKey: 'product_name',
              title: lang.product_name,
              width: 250,
              ellipsis: true
            },
            {
              colKey: 'name',
              title: lang.host_name,
              width: 166,
              ellipsis: true
            },
            {
              colKey: 'first_payment_amount',
              title: lang.money_cycle,
              width: 166,
              ellipsis: true
            },
            {
              colKey: 'active_time',
              title: lang.open_time,
              width: 180,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'due_time',
              title: lang.due_time,
              width: 180,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'status',
              title: lang.status,
              width: 100,
              ellipsis: true
            },
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
            sort: 'desc'
          },
          id: '',
          total: 0,
          pageSizeOptions: [20, 50, 100],
          loading: false,
          title: '',
          delId: '',
          maxHeight: ''
        }
      },
      mounted () {
        this.maxHeight = document.getElementById('content').clientHeight - 170
        let timer = null
        window.onresize = () => {
          if (timer) {
            return
          }
          timer = setTimeout(() => {
            this.maxHeight = document.getElementById('content').clientHeight - 170
            clearTimeout(timer)
            timer = null
          }, 300)
        }
      },
      methods: {
        // 搜索
        clearKey () {
          this.params.keywords = ''
          this.seacrh()
        },
        seacrh () {
          this.params.page = 1
          this.getClientList()
        },
        // 分页
        changePage (e) {
          this.params.keywords = ''
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getClientList()
        },
        async getClientList () {
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
        sortChange (val) {
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
        deltePro (row) {
          this.delVisible = true
          this.delId = row.id
        },
        async onConfirm () {
          try {
            const res = await deletePro(this.delId)
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getClientList()
          } catch (error) {
            this.delVisible = false
            this.$message.error(error.data.msg)
          }
        }
      },
      created () {
        this.getClientList()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);

