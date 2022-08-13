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
              colKey: 'product_name',
              title: lang.product_name,
              width: 300,
              ellipsis: true
            },
            {
              colKey: 'name',
              title: lang.host_name,
              width: 300,
              ellipsis: true
            },
            {
              colKey: 'amount',
              title: lang.money_cycle,
              width: 200,
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
              width: 100
            },
            // {
            //   colKey: 'op',
            //   title: lang.operation,
            //   width: 100
            // }
          ],
          params: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          id: '',
          clientList: [], // 用户列表
          total: 0,
          pageSizeOptions: [20, 50, 100],
          loading: false,
          title: '',
          delId: '',
          maxHeight: '',
          clinetParams: {
            page: 1,
            limit: 1000,
            orderby: 'id',
            sort: 'desc'
          },
          popupProps: {
            overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` })
          },
        }
      },
      mounted () {
        this.maxHeight = document.getElementById('content').clientHeight - 200
        let timer = null
        window.onresize = () => {
          if (timer) {
            return
          }
          timer = setTimeout(() => {
            this.maxHeight = document.getElementById('content').clientHeight - 200
            clearTimeout(timer)
            timer = null
          }, 300)
        }
      },
      methods: {
        changeUser (id) {
          this.id = id
          location.href = `client_host.html?client_id=${this.id}`
        },
        async getClintList () {
          try {
            const res = await getClientList(this.clinetParams)
            this.clientList = res.data.data.list
            this.clientTotal = res.data.data.count
            if (this.clientList.length < this.clientTotal) {
              this.clinetParams.limit = this.clientTotal
              this.getClintList()
            }
          } catch (error) {
            console.log(error.data.msg)
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
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getClientList()
        },
        async getClientList () {
          try {
            const res = await getClientPro(this.id, this.params)
            this.data = res.data.data.list
            this.total = res.data.data.count
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        async onConfirm () {
          try {
            const res = await deletePro(this.delId)
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getClientList()
          } catch (error) {
            this.$message.error(error.data.msg)
            this.delVisible = false
          }
        },
        deltePro (row) {
          this.delVisible = true
          this.delId = row.id
        }
      },
      created () {
        this.id = location.href.split('?')[1].split('=')[1] * 1
        this.getClientList()
        this.getClintList()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);

