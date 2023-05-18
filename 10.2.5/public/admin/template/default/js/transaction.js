/* 用户管理-交易流水 */
(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('transaction')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = moment
    new Vue({
      data () {
        return {
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          flowModel: false,
          orderVisible: false,
          hover: true,
          columns: [
            {
              colKey: 'id',
              title: 'ID',
              width: 125,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'amount',
              title: lang.money,
              width: 125,
              ellipsis: true
            },
            {
              colKey: 'gateway',
              title: lang.pay_way,
              width: 170,
              ellipsis: true
            },
            {
              colKey: 'client_name',
              title: lang.user + '(' + lang.company + ')',
              width: 240,
              ellipsis: true
            },
            // {
            //   colKey: 'hosts',
            //   title: lang.product_name,
            //   width: 180,
            //   ellipsis: true
            // },
            {
              colKey: 'transaction_number',
              title: lang.flow_number,
              ellipsis: true,
              width: 180
            },
            {
              colKey: 'order_id',
              title: lang.order + 'ID',
              ellipsis: true,
              width: 125,
            },
            {
              colKey: 'create_time',
              title: lang.trade_time,
              width: 170,
              ellipsis: true
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 100
            }
          ],
          orderColumns: [
            // {
            //   colKey: 'id',
            //   title: 'ID',
            //   width: 100,
            //   ellipsis: true
            // },
            {
              colKey: 'type',
              title: lang.type,
              width: 100,
              ellipsis: true
            },
            {
              colKey: 'product_names',
              title: lang.product_name,
              width: 180,
              ellipsis: true
            },
            // {
            //   colKey: 'create_time',
            //   title: lang.time,
            //   width: 180,
            //   ellipsis: true
            // },
            {
              colKey: 'host_name',
              title: lang.host_name,
              width: 180,
              ellipsis: true
            },
            {
              colKey: 'amount',
              title: lang.money_cycle,
              width: 130,
              ellipsis: true
            },
            {
              colKey: 'status',
              title: lang.status,
              width: 100
            },
            // {
            //   colKey: 'gateway',
            //   title: lang.pay_way,
            //   width: 120
            // }
          ],
          params: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc',
            gateway: '',
            amount: '',
            start_time: '',
            end_time: ''
          },
          id: '',
          total: 0,
          pageSizeOptions: [20, 50, 100],
          loading: false,
          detailLoading: false,
          delId: '',
          currency_prefix: '',
          // 新增流水表单
          formData: {
            amount: '',
            gateway: '',
            transaction_number: '',
            client_id: ''
          },
          searchLoading: false,
          client_name: '',
          client_id: '',
          addLoading: false,
          rules: {
            amount: [
              { required: true, message: lang.input + lang.money, type: 'error' },
              {
                pattern: /^-?\d+(\.\d{0,2})?$/, message: lang.verify10, type: 'warning'
              },
              {
                validator: val => val != 0, message: lang.verify10, type: 'warning'
              }
            ],
            gateway: [{ required: true, message: lang.select + lang.pay_way, type: 'error' }],
            transaction_number: [
              { pattern: /^[A-Za-z0-9]+$/, message: lang.verify9, type: 'warning' }
            ],
            client_id: [{ required: true, message: lang.select + lang.user, type: 'error' }]
          },
          payList: [],
          userList: [], // 用户列表
          userParams: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          orderDetail: [

          ],
          expandedRowKeys: [],
          isShow: false,
          maxHeight: '',
          optType: 'add',
          optTitle: '',
          /* 2023-04-11 */
          isAdvance: false,
          orderTypes: [
            { value: 'new', label: lang.new },
            { value: 'renew', label: lang.renew },
            { value: 'upgrade', label: lang.upgrade },
            { value: 'artificial', label: lang.artificial }
          ],
          payWays: [],
          range: []
        }
      },
      methods: {
        changeAdvance () {
          this.isAdvance = !this.isAdvance
          this.params.amount = ''
          this.range = []
        },
        async getPayWay () {
          try {
            const res = await getPayList()
            this.payWays = res.data.data.list
          } catch (error) {

          }
        },
        // 搜索
        clearSearch () {
          this.params.keywords = ''
          this.seacrh()
        },
        seacrh () {
          this.params.page = 1
          if (this.range.length > 0) {
            this.params.start_time = new Date(this.range[0].replace(/-/g, '/')).getTime() / 1000 || ''
            this.params.end_time = (new Date(this.range[1].replace(/-/g, '/')).getTime() + 24 * 3600 * 1000) / 1000 || ''
          } else {
            this.params.start_time = ''
            this.params.end_time = ''
          }
          this.getClientList()
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
        // 点击显示详情
        async rowClick (e) {
          try {
            this.orderDetail = []
            this.orderVisible = true
            //const order_id = e.row.order_id
            const order_id = e.order_id
            const res = await getOrderDetail(order_id)
            const temp = [], tempData = []
            res.data.data.order.items.forEach(item => {
              temp.push(item.product_name)
            })
            res.data.data.order['product_names'] = temp
            tempData.push(res.data.data.order)
            this.orderDetail = tempData
            this.$nextTick(() => {
              this.$refs.tableDialog.expandAll()
            })

          } catch (error) {

          }
        },
        rehandleExpandChange () {

        },
        treeExpandAndFoldIconRender () {
          return ''
        },
        // 分页
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getClientList()
        },
        // 获取流水数据
        async getClientList () {
          try {
            this.loading = true
            const res = await getClientOrder(this.params)
            this.data = res.data.data.list
            this.total = res.data.data.count
            this.loading = false
          } catch (error) {
            this.$message.error(res.data.msg)
            this.loading = false
          }
        },
        addFlow () {
          this.flowModel = true
          this.formData.amount = ''
          this.formData.gateway = this.payList[0].name
          this.formData.transaction_number = ''
          this.optTitle = lang.new_flow
          this.optType = 'add'
        },
        updateFlow (row) {
          this.flowModel = true
          this.optTitle = lang.update_flow
          this.optType = 'update'
          this.formData = JSON.parse(JSON.stringify(row))
          this.formData.gateway = this.payList.filter(item => item.title === row.gateway)[0].name
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.addLoading = true
              await addAndUpdateFlow(this.optType, this.formData).then(res => {
                this.$message.success(res.data.msg)
                this.addLoading = false
                this.flowModel = false
                this.getClientList()
              })
            } catch (error) {
              this.addLoading = false
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        // 获取支付方式
        async getPayway () {
          try {
            const res = await getPayList()
            this.payList = res.data.data.list
          } catch (error) {

          }
        },
        // 删除流水
        async sureDelUser () {
          try {
            const res = await deleteFlow(this.delId)
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getClientList()
          } catch (error) {
            this.$message.error(error)
          }
        },
        delteFlow (row) {
          this.delVisible = true
          this.delId = row.id
        },
        // 获取用户列表
        async getUserList () {
          try {
            this.searchLoading = true
            const { data: { data } } = await getClientList(this.userParams)
            this.userList = data.list
            this.searchLoading = false
          } catch (error) {
            this.searchLoading = false
          }
        },
        // 远程搜素
        remoteMethod (key) {
          this.userParams.keywords = key
          this.getUserList()
        },
        clearKey () {
          this.userParams.keywords = ''
          this.getUserList()
        }
      },
      created () {
        this.getUserList()
        this.getClientList()
        this.getPayway()
        this.currency_prefix = JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥'
      }
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);

