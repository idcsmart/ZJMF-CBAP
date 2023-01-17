/* 用户信息-订单管理 */
(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('client-order')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    new Vue({
      data () {
        return {
          rootRul: url,
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          priceModel: false,
          payVisible: false,
          hover: true,
          currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥',
          columns: [
            {
              colKey: 'id',
              title: 'ID',
              width: 120,
              sortType: 'all',
              sorter: true
            },
            // {
            //   colKey: 'type',
            //   title: lang.type,
            //   width: 130
            // },
            {
              colKey: 'icon',
              width: 16,
              className: 'icon-width'
            },
            {
              colKey: 'product_names',
              title: lang.product_name,
              ellipsis: true,
              width: 250
            },
            {
              colKey: 'amount',
              title: lang.money_cycle,
              ellipsis: true,
              width: 150
            },
            {
              colKey: 'gateway',
              title: lang.pay_way,
              ellipsis: true,
              width: 170
            },
            {
              colKey: 'create_time',
              title: lang.order_time,
              width: 170
            },
            {
              colKey: 'status',
              title: lang.status,
              width: 120
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 100
            }
          ],
          params: {
            keywords: '',
            client_id: '', // 用户ID
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
          loading: false,
          title: '',
          delId: '',
          id: '',
          tempData: [],
          promiseArr: [],
          orderNum: 0,
          // 变更价格
          formData: {
            id: '',
            amount: '',
            description: ''
          },
          rules: {
            amount: [
              { required: true, message: lang.input + lang.money, type: 'error' },
              {
                pattern: /^-?\d+(\.\d{0,2})?$/, message: lang.verify10, type: 'warning'
              },
              {
                validator: val => val * 1 !== 0, message: lang.verify10, type: 'warning'
              }
            ],
            description: [
              { required: true, message: lang.input + lang.description, type: 'error' },
              {
                validator: val => val.length <= 1000, message: lang.verify3 + 1000, type: 'warning'
              }
            ],
          },
          delete_host: false, // 是否删除产品:0否1是
          signForm: {
            amount: 0,
            credit: 0
          },
          maxHeight: '',
          use_credit: true,
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
          curInfo: {},
          optType: '' // order,sub
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
        },
        document.title = lang.user_list + '-' + lang.order_manage + '-' + localStorage.getItem('back_website_name')
      },
      methods: {
        changeUser (id) {
          this.id = id
          location.href = `client_order.html?client_id=${this.id}`
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
        // 调整价格
        updatePrice (row, type) {
          this.optType = type
          this.formData.id = row.id
          this.formData.amount = ''
          this.formData.description = ''
          this.$refs.priceForm && this.$refs.priceForm.clearValidate()
          this.priceModel = true
          this.curInfo = row
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            if (this.optType === 'order') {
              this.changeOrderPrice()
            } else {
              this.changeSubPrice()
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        // 修改订单价格
        async changeOrderPrice () {
          try {
            await updateOrder(this.formData)
            this.$message.success(lang.modify_success)
            this.priceModel = false
            this.getClientList()
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        // 修改子项人工价格
        async changeSubPrice () {
          try {
            await updateArtificialOrder(this.formData)
            this.$message.success(lang.modify_success)
            this.priceModel = false
            this.getClientList()
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        closePrice () {
          this.priceModel = false
          this.$refs.priceForm.reset()
        },
        // 删除订单
        delteOrder (row) {
          this.delId = row.id
          this.delVisible = true
          this.delete_host = false
        },
        async onConfirm () {
          try {
            const params = {
              id: this.delId,
              delete_host: this.delete_host ? 1 : 0
            }
            await delOrderDetail(params)
            this.$message.success(window.lang.del_success)
            this.delVisible = false
            this.orderNum = 0
            this.params.page = this.data.length > 1 ? this.params.page : this.params.page - 1
            this.getClientList()
          } catch (error) {
            this.$message.error(error)
          }
        },
        // 标记支付
        signPay (row) {
          if (row.status === 'Paid') {
            return
          }
          this.payVisible = true
          this.delId = row.id
          this.signForm.amount = row.amount
          this.signForm.credit = row.client_credit
        },
        async sureSign () {
          try {
            const params = {
              id: this.delId,
              use_credit: this.use_credit ? 1 : 0
            }
            const res = await signPayOrder(params)
            this.$message.success(res.data.msg)
            this.orderNum = 0
            this.getClientList()
            this.payVisible = false
          } catch (error) {
            this.$message.error(error.data.msg)
            this.payVisible = false
          }
        },
        // 展开行
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getClientList()
        },
        // 获取订单列表
        async getClientList () {
          try {
            this.loading = true
            const res = await getOrder(this.params)
            this.data = res.data.data.list
            this.total = res.data.data.count
            this.data.forEach(item => {
              item.list = []
              item.isExpand = false
            })
            this.loading = false
            if (JSON.stringify(this.curInfo) !== '{}') { //修改子项打开对应的订单下拉
              this.itemClick(this.curInfo)
            } else {
            }
          } catch (error) {
            this.$message.error(error.data.msg)
            this.loading = false
          }
        },
        // id点击获取订单详情
        itemClick (row) {
          if (row.order_item_count < 2) {
            return
          }
          row.isExpand = row.isExpand ? false : true
          const rowData = this.$refs.table.getData(row.id);
          this.$refs.table.toggleExpandData(rowData);
          if (row.list?.length > 0) {
            return
          }
          this.getOrderDetail(this.optType === 'sub' ? row.pId : row.id)
        },
        // 订单详情
        async getOrderDetail (id) {
          try {
            const res = await getOrderDetail(id)
            res.data.data.order.items.forEach(item => {
              item.pId = res.data.data.order.id
              this.$refs.table.appendTo(id, item)
            })
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        }
      },
      created () {
        this.id = this.params.client_id = location.href.split('?')[1].split('=')[1] * 1
        this.getClientList()
        this.getClintList()
      }
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);

