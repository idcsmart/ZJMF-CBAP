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
        payDialog,
      },
      created() {
        this.getCommonData()
        this.getAuthorizeList()
        this.getMyServeTable()
      },
      mounted() {

      },
      updated() {
        // // 关闭loading
        // document.getElementById('mainLoading').style.display = 'none';
        // document.getElementsByClassName('template')[0].style.display = 'block'
      },
      destroyed() {

      },
      data() {
        return {
          params: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: 'id',
            sort: 'desc',
            keywords: '',
            used: '',
            host_id: ''
          },
          commonData: {},
          data1: [],
          loading1: false,
          authorizeList: [],
          myServeTable: []
        }
      },
      filters: {
        formateTime(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000)
          } else {
            return "--"
          }
        }
      },
      methods: {

        // 每页展示数改变
        sizeChange(e) {
          this.params.limit = e
          this.params.page = 1
          // 获取列表
          this.getMyServeTable()
        },
        // 当前页改变
        currentChange(e) {
          this.params.page = e
          this.getMyServeTable()
        },

        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
          document.title = this.commonData.website_name + '-我的服务'
        },
        // 获取授权列表
        async getAuthorizeList() {
          const res = await queryAuthorizetApi()
          if (res.status == 200) {
            const { data } = res.data
            this.authorizeList = data.list
          }
        },
        // 获取我的服务列表
        async getMyServeTable() {
          this.loading1 = true
          const res = await queryMyServeApi(this.params)
          const m = this.commonData.currency_prefix
          if (res.status == 200) {
            const { data } = res.data
            this.data1 = data.list.map(item => {
              let str = ''
              if (item.pay_type == 'onetime') {
                str += m + item.amount + '/一次性'
              } else if (item.pay_type == 'monthly') {
                str += m + item.amount + '/月付'
              } else if (item.pay_type == 'quarterly') {
                str += m + item.amount + '/季付'
              } else if (item.pay_type == 'semiannually') {
                str += m + item.amount + '/半年付'
              } else if (item.pay_type == 'annually') {
                str += m + item.pay_type == amount + '/年付'
              } else if (item.pay_type == 'free') {
                str = '免费'
              } else {
                str = '--'
              }
              return { ...item, str }
            })
            this.params.total = data.count
          }
          this.loading1 = false
        },
        // 去支付
        goPay(row) {
          this.$refs.payDialog.showPayDialog(row.order_id)
        },
        // 再次购买
        goShop(row) {
          window.open(`/shop/shop_detail.html?id=${row.product_id}&clientId=${row.client_id}`)

        },
        goShop_client() {
          window.open(`/shop/shop_client.html?activeName=2`)
        },
        // 支付成功回调
        paySuccess(e) {
          location.reload();
        },
        // 取消支付回调
        payCancel(e) {
          // location.reload();
        },
      },

    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
