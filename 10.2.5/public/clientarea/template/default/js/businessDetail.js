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
      async created() {
        this.getCommonData()
        let obj = this.getUrlParams()
        if (obj) {
          this.authorizeId = obj.id
          await this.getAuthorizeInfo()
          await this.getAppTable()
        }
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
          params1: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 0,
            orderby: 'id',
            sort: 'desc',
            keywords: '',
          },
          params2: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 0,
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
          authorizeId: '',
          authorizeInfo: {},
          commonData: {},
          orderStatusList: [],
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
          activeName: '1',
          loading1: false,
          loading2: false,
          loading3: false,
          appTable: [],
          serveTable: [],
          fileTable: [],
          AllFile: [],
          fileCount: '',
          fileId: '',
          curId: '',
          curTit: '',
        }
      },
      filters: {
        formateTime(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000)
          } else {
            return "--"
          }
        },
        formateByte(size) {
          if (size < 1024 * 1024) {
            return (size / 1024).toFixed(2) + 'KB'
          } else {
            return (size / (1024 * 1024).toFixed(2)) + 'MB'
          }
        }
      },
      methods: {
        getUrlParams() {
          var urlObj = {};
          if (!window.location.search) {
            return false;
          }
          var urlParams = window.location.search.substring(1);
          var urlArr = urlParams.split("&");
          for (var i = 0; i < urlArr.length; i++) {
            var urlArrItem = urlArr[i].split("=");
            urlObj[urlArrItem[0]] = urlArrItem[1];
          }
          if (Object.keys(urlObj).length > 0) {
            return urlObj;
          } else {
            return false;
          }
        },
        // 下载文件
        async downFile(item) {
          try {
            const res = await downloadFile({ id: item.id })
            const fileName = item.name;
            const _res = res.data;
            const blob = new Blob([_res]);
            const downloadElement = document.createElement("a");
            const href = window.URL.createObjectURL(blob); // 创建下载的链接
            downloadElement.href = href;
            downloadElement.download = decodeURI(fileName); // 下载后文件名
            document.body.appendChild(downloadElement);
            downloadElement.click(); // 点击下载
            document.body.removeChild(downloadElement); // 下载完成移除元素
            window.URL.revokeObjectURL(href); // 释放掉blob对象
          } catch (error) {

          }
        },
        // 每页展示数改变
        sizeChange1(e) {
          this.params1.limit = e
          this.params1.page = 1
          this.getAppTable()
          // 获取列表
        },
        currentChange1(e) {
          this.params2.page = e
          this.getAppTable()
        },
        sizeChange2(e) {
          this.params2.limit = e
          this.params2.page = 1
          this.getServeTable()
        },
        currentChange2(e) {
          this.params2.page = e
          this.getServeTable()
        },
        sizeChange3(e) {
          this.params3.limit = e
          this.params3.page = 1
        },
        currentChange3(e) {
          this.params3.page = e
        },

        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
          document.title = this.commonData.website_name + '-模板页面'
        },
        goBack() {
          history.back()
        },
        handleClick(e) {
          switch (e.name) {
            case '1':
              // 应用列表
              this.getAppTable()
              break;
            case '2':
              // 服务管理
              this.getServeTable()
              break;
            case '3':
              // 文件
              this.getFileFolder()
              break;
            default:
          }
        },

        async getAuthorizeInfo() {
          const res = await queryAuthorizeDetailApi({ id: this.authorizeId })
          if (res.status == 200) {
            const { data } = res.data
            this.authorizeInfo = data.authorize
          }
        },
        // 续费
        goShop_client() {
          window.open(`/shop/shop_client.html?activeName=1`)
        },
        // 支付成功回调
        paySuccess(e) {
          this.getAppTable()
        },
        // 取消支付回调
        payCancel(e) {
          // location.reload();
        },
        // 去支付
        handelPay(row) {
          this.$refs.payDialog.showPayDialog(row.order_id)
        },
        // 去支付
        goPay(row) {
          this.$refs.payDialog.showPayDialog(row.order_id)
        },
        // 再次购买
        goShop(row) {
          window.open(`/shop/shop_detail.html?id=${row.product_id}&clientId=${row.client_id}`)

        },
        goShopDetail() {
          window.open(`/shop/shop_client.html?activeName=2`)
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
        async getAppTable() {
          this.loading1 = true
          const res = await queryMyAppApi({ ...this.params1, host_id: this.authorizeInfo.id })
          if (res.status == 200) {
            const { data } = res.data
            const m = this.commonData.currency_prefix
            this.params1.total = data.count
            this.appTable = data.list.map(item => {
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
                str += item.pay_type == amount + '/年付'
              } else if (item.pay_type == 'free') {
                str = '免费'
              } else {
                str = '--'
              }
              let obj = this.orderStatusList.find(
                (stateItem) => item.status == stateItem.value
              );
              return { ...item, str, ...obj }
            })
          }
          this.loading1 = false
        },
        async getServeTable() {
          this.loading2 = true
          const res = await queryMyServeApi({ ...this.params2, host_id: this.authorizeInfo.id })
          if (res.status == 200) {
            const { data } = res.data
            this.params2.total = data.count
            const m = this.commonData.currency_prefix
            this.serveTable = data.list.map(item => {
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
                str += item.pay_type == amount + '/年付'
              } else if (item.pay_type == 'free') {
                str = '免费'
              } else {
                str = '--'
              }
              let obj = this.orderStatusList.find(
                (stateItem) => item.status == stateItem.value
              );
              return { ...item, str, ...obj }
            })
          }
          this.loading2 = false
        },

        // 获取文件夹
        async getFileFolder() {
          try {
            const res = await queryAllFileApi()
            if (res.data.status === 200) {
              this.AllFile = res.data.data.list
              this.curId = res.data.data.list[0].id
              this.fileCount = this.AllFile.reduce((all, cur) => {
                all += cur.file_num
                return all
              }, 0)
              this.curTit = res.data.data.list[0].name
              this.getData()
            }
          } catch (error) {
            console.log(error)
          }
        },
        getAllFiles() {
          this.curId = ''
          this.curTit = '全部'
          this.params3.page = 1
          this.getData()
        },
        // 选择文件夹
        changeFolder(item) {
          this.curId = item.id
          this.curTit = item.name
          this.params3.page = 1
          this.getData()
        },
        async getData() {
          try {
            const params = {
              addon_idcsmart_file_folder_id: this.curId,
              ...this.params3
            }
            this.loading3 = true
            delete params.pageSizes
            delete params.total
            const res = await getFileList(params)
            if (res.data.status === 200) {
              this.fileTable = res.data.data.list
              this.params3.total = res.data.data.count
              this.loading3 = false
            }
          } catch (error) {
            this.loading3 = false
            console.log(error)
          }
        },
      },

    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
