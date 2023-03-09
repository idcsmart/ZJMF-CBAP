(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('feedback')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    new Vue({
      data () {
        return {
          hover: true,
          tableLayout: false,
          delVisible: false,
          loading: false,
          list: [],
          typeColumns: [
            {
              colKey: 'matter',
              title: lang.matter,
              ellipsis: true
            },
            {
              colKey: 'contact',
              title: lang.contact_user,
              ellipsis: true,
            },
            {
              colKey: 'username',
              title: lang.order_client,
              ellipsis: true,
            },
            {
              colKey: 'phone',
              title: lang.contact_phone,
              ellipsis: true,
            },
            {
              colKey: 'company',
              title: `${lang.company}${lang.nickname}`,
              ellipsis: true,
            },
            {
              colKey: 'email',
              title: lang.contact_email,
              ellipsis: true,
            },
          ],
          params: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
        }
      },
      computed: {
      },
      methods: {
        // 获取反馈
        async getConsultList () {
          try {
            this.loading = true
            const res = await getConsult(this.params)
            this.list = res.data.data.list
            this.total = res.data.data.count
            this.loading = false
          } catch (error) {
            this.loading = false
            this.$message.error(error.data.msg)
          }
        },
        // 切换分页
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getConsultList()
        }
      },
      created () {
        this.getConsultList()
        document.title = lang.guidance + '-' + localStorage.getItem('back_website_name')
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);