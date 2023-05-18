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
        this.getmyAppTable()
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
            type: '',
            host_id: ''
          },
          commonData: {},
          typeList: [
            { value: 'addon', label: '插件' },
            { value: 'captcha', label: '验证码接口' },
            { value: 'certification', label: '实名接口' },
            { value: 'gateway', label: '支付接口' },
            { value: 'mail', label: '邮件接口' },
            { value: 'sms', label: '短信接口' },
            { value: 'server', label: '模块' },
            { value: 'template', label: '主题' },
          ],
          loading1: false,
          authorizeList: [],
          myAppTable: []
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
          this.getmyAppTable()
        },
        // 当前页改变
        currentChange(e) {
          this.params.page = e
          this.getmyAppTable()
        },

        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
          document.title = this.commonData.website_name + '-我的应用'
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
        async getmyAppTable() {
          this.loading1 = true
          const res = await queryMyAppApi(this.params)
          if (res.status == 200) {
            const { data } = res.data
            const m = this.commonData.currency_prefix
            this.myAppTable = data.list.map(item => {
              let str = m
              if (item.pay_type == 'onetime') {
                str += item.amount + '/一次性'
              } else if (item.pay_type == 'monthly') {
                str += item.amount + '/月付'
              } else if (item.pay_type == 'quarterly') {
                str += item.amount + '/季付'
              } else if (item.pay_type == 'semiannually') {
                str += item.amount + '/半年付'
              } else if (item.pay_type == 'annually') {
                str += item.pay_type == item.amount + '/年付'
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
        // 续费
        goShop_client() {
          window.open(`https://my.idcsmart.com/shop/shop_client.htm?activeName=1`)
        },
        // 支付成功回调
        paySuccess(e) {
          this.getmyAppTable()
        },
        // 取消支付回调
        payCancel(e) {
          // location.reload();
        },
        // 去支付
        handelPay(row) {
          this.$refs.payDialog.showPayDialog(row.order_id)
        },
        // 下载安装包
        async download(id) {
          try {
            const res = await downloadMyAppApi({ id })
            if (res.status == 200) {
              const fileName = res.headers['content-disposition'].split('filename=')[1].split('"')[1] //分割出文件名
              const blob = new Blob([res.data], {
                type: res.headers['content-type']
              })
              const downloadElement = document.createElement('a');
              const href = window.URL.createObjectURL(blob); //创建下载的链接
              downloadElement.href = href;
              downloadElement.download = fileName; //下载后文件名
              document.body.appendChild(downloadElement);
              downloadElement.click(); //点击下载
              document.body.removeChild(downloadElement); //下载完成移除元素
              window.URL.revokeObjectURL(href); //释放掉blob对象
            }
          } catch (err) {
            this.$message.error(err.data.msg)
          }
        },
      },

    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
