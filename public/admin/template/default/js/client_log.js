(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('log')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    new Vue({
      data () {
        return {
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          hover: true,
          columns: [
            {
              colKey: 'id',
              title: 'ID',
              width: 100,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'description',
              title: lang.detail,
              width: 700,
              ellipsis: true,
              className: 'log-description-width'
            },
            {
              colKey: 'create_time',
              title: lang.time,
              width: 200,
              ellipsis: true
            },
            {
              colKey: 'ip',
              title: 'IP' + lang.address,
              width: 100,
              ellipsis: true
            },
            {
              colKey: 'user_name',
              title: lang.operator,
              width: 100,
              ellipsis: true
            }
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
          maxHeight: '',
          clinetParams: {
            page: 1,
            limit: 1000,
            orderby: 'id',
            sort: 'desc'
          },
          clientList: [], // 用户列表
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
          location.href = `client_log.html?client_id=${this.id}`
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
            this.loading = true
            const res = await getLog(this.id, this.params)
            this.data = res.data.data.list
            this.total = res.data.data.count
            this.loading = false
          } catch (error) {
            this.loading = false
            this.$message.error(error.data.msg)
          }
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

