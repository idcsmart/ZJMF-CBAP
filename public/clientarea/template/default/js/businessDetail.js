(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('template')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      components: {
        asideMenu,
        topMenu,
        pagination,
      },
      created () {
        this.getCommonData()
      },
      mounted () {

      },
      updated () {
        // // 关闭loading
        // document.getElementById('mainLoading').style.display = 'none';
        // document.getElementsByClassName('template')[0].style.display = 'block'
      },
      destroyed () {

      },
      data () {
        return {
          params1: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: 'id',
            sort: 'desc',
            keywords: '',
          },
          params2: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: 'id',
            sort: 'desc',
            keywords: '',
          },
          params3: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: 'id',
            sort: 'desc',
            keywords: '',
          },
          commonData: {},
          activeName: '1',
          loading1: false,
          loading2: false,
          loading3: false,
          data1: [],
          data2: [],
          data3: [],
        }
      },
      filters: {
        formateTime (time) {
          if (time && time !== 0) {
            return formateDate(time * 1000)
          } else {
            return "--"
          }
        }
      },
      methods: {

        // 每页展示数改变
        sizeChange1 (e) {
          this.params1.limit = e
          this.params1.page = 1
          // 获取列表
        },
        sizeChange2 (e) {
          this.params2.limit = e
          this.params2.page = 1
        },
        sizeChange3 (e) {
          this.params3.limit = e
          this.params3.page = 1
        },
        // 当前页改变
        currentChange1 (e) {
          this.params2.page = e
        },
        currentChange2 (e) {
          this.params2.page = e
        },
        currentChange3 (e) {
          this.params3.page = e
        },

        // 获取通用配置
        getCommonData () {
          this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
          document.title = this.commonData.website_name + '-模板页面'
        },
        goBack () {
          history.back()
        }
      },

    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
