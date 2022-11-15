(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('template')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data () {
        return {
          message: 'template...',
          params: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          total: 100,
          pageSizeOptions: [20, 50, 100],
        }
      },
      methods: {
        // 切换分页
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.params.keywords = ''
        },
      },
      created () {

      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
