(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('log-notice-email')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    new Vue({
      components: {
        comConfig,
      },
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
              colKey: 'subject',
              title: lang.title,
              ellipsis: true
            },
            {
              colKey: 'to',
              title: lang.email,
              ellipsis: true
            },
            {
              colKey: 'create_time',
              title: lang.time,
              width: 150,
              ellipsis: true
            },
            {
              colKey: 'user_name',
              title: lang.receiver,
              width: 150,
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
          messageVisable: false,
          messagePop: '',
          emailTitle: ''
        }
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
        jump () {
          location.href = 'log_notice_sms.htm'
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
          this.search()
        },
        search () {
          this.getClientList()
        },
        // 显示邮件详情
        showMessage (row) {
          this.messageVisable = true
          this.emailTitle = row.subject
          this.messagePop = row.message
        }
      },
      created () {
        this.getClientList();
        document.title = lang.email_notice + '-' + localStorage.getItem('back_website_name');
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
