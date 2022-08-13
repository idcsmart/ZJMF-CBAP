(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('log-notice-sms')[0]
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
              colKey: 'content',
              title: lang.content,
              ellipsis: true
            },
            {
              colKey: 'create_time',
              title: lang.time,
              width: 200
            },
            {
              colKey: 'user_name',
              title: lang.receiver,
              width: 150,
              ellipsis: true
            },
            {
              colKey: 'phone',
              title: lang.phone,
              width: 250,
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
      created () {
        const query = location.href.split('?')[1].split('&')
        this.id = this.params.client_id = Number(this.getQuery(query[0]))
        this.getNoticeSms()
        this.getClintList()
      },
      mounted () {
        this.maxHeight = document.getElementById('content').clientHeight - 240
        let timer = null
        window.onresize = () => {
          if (timer) {
            return
          }
          timer = setTimeout(() => {
            this.maxHeight = document.getElementById('content').clientHeight - 240
            clearTimeout(timer)
            timer = null
          }, 300)
        }
      },
      methods: {
        changeUser (id) {
          this.id = id
          location.href = `client_notice_sms.html?client_id=${this.id}`
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
            console.log(error)
          }
        },
        getQuery (val) {
          return val.split('=')[1]
        },
        jump () {
          location.href = `client_notice_email.html?id=${this.id}`
        },
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getNoticeSms()
        },
        async getNoticeSms () {
          try {
            this.loading = true
            const res = await getSmsLog(this.params)
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
          this.getNoticeSms()
        },
        clearKey () {
          this.params.keywords = ''
          this.seacrh()
        },
        seacrh () {
          this.getNoticeSms()
        },
      }
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);

