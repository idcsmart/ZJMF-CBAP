(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('log-notice-email')[0]
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
              width: 120,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'subject',
              title: lang.title,
              width: 500,
              ellipsis: true
            },
            {
              colKey: 'to',
              title: lang.email,
              width: 500,
              ellipsis: true
            },
            {
              colKey: 'create_time',
              title: lang.time,
              width: 200,
              ellipsis: true
            },
            {
              colKey: 'user_name',
              title: lang.receiver,
              width: 200,
              ellipsis: true
            }
          ],
          params: {
            client_id: '',
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
          messageVisable: false,
          messagePop: '',
          emailTitle: ''
        }
      },
      created () {
        const query = location.href.split('?')[1].split('&')
        this.id = this.params.client_id = Number(this.getQuery(query[0]))
        this.getClientList()
      },
      mounted () {
        this.maxHeight = document.getElementById('content').clientHeight - 220
        let timer = null
        window.onresize = () => {
          if (timer) {
            return
          }
          timer = setTimeout(() => {
            this.maxHeight = document.getElementById('content').clientHeight - 220
            clearTimeout(timer)
            timer = null
          }, 300)
        }
      },
      methods: {
        getQuery (val) {
          return val.split('=')[1]
        },
        jump () {
          location.href = `client_notice_sms.html?id=${this.params.client_id}`
        },
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getClientList()
        },
        async getClientList () {
          try {
            this.loading = true
            const res = await getEmailLog(this.params)
            this.data = res.data.data.list
            this.total = res.data.data.count
            this.loading = false
          } catch (error) {
            this.$message.error(error.data.msg)
            this.loading = false
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
        clearKey () {
          this.params.keywords = ''
          this.seacrh()
        },
        seacrh () {
          this.getClientList()
        },
        // 显示邮件详情
        showMessage (row) {
          this.messageVisable = true
          this.emailTitle = row.subject
          this.messagePop = row.message
        }
      }
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);

