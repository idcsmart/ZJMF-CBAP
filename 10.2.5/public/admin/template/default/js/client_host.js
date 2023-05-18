(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('host')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = moment
    const host = location.origin
    const fir = location.pathname.split('/')[1]
    const str = `${host}/${fir}`
    new Vue({
      data () {
        return {
          baseUrl: str,
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          hover: true,
          currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥',
          columns: [
            {
              colKey: 'row-select',
              type: 'multiple',
              className: 'demo-multiple-select-cell',
              checkProps: ({ row }) => ({ disabled: row.status !== 'Active' }),
              width: 30
            },
            {
              colKey: 'id',
              title: 'ID',
              width: 120,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'product_name',
              title: lang.product_name,
              width: 300,
              ellipsis: true
            },
            {
              colKey: 'name',
              title: lang.host_name,
              width: 300,
              ellipsis: true
            },
            {
              colKey: 'renew_amount',
              title: `${lang.money_cycle}`,
              width: 200,
              ellipsis: true
            },
            {
              colKey: 'active_time',
              title: lang.open_time,
              width: 180,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'due_time',
              title: lang.due_time,
              width: 180,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'status',
              title: lang.status,
              width: 100
            },
            // {
            //   colKey: 'op',
            //   title: lang.operation,
            //   width: 100
            // }
          ],
          params: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          id: '',
          clientList: [], // 用户列表
          total: 0,
          pageSizeOptions: [20, 50, 100],
          loading: false,
          title: '',
          delId: '',
          maxHeight: '',
          clinetParams: {
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          popupProps: {
            overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` })
          },
          /* 批量续费 */
          renewVisible: false,
          checkId: [],
          selectedRowKeys: [],
          renewColumns: [
            {
              colKey: 'id',
              title: 'ID',
              width: 60,
              sortType: 'all'
            },
            {
              colKey: 'product_name',
              title: lang.products_name,
              width: 300,
              ellipsis: true
            },
            {
              colKey: 'billing_cycles',
              title: lang.cycle,
              width: 120,
              ellipsis: true
            },
            {
              colKey: 'renew_amount',
              title: lang.money,
              width: 100,
              ellipsis: true
            },
          ],
          renewList: [],
          renewLoading: false,
          currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥',
          pay: false,
          submitLoading: false,
          hasPlugin: false,
          hasTicket: false, // 是否安装工单
          authList: JSON.parse(
            JSON.stringify(localStorage.getItem("backAuth"))
          ),
          clientDetail: {},
          searchLoading: false
        }
      },
      mounted () {
        this.maxHeight = document.getElementById('content').clientHeight - 260
        let timer = null
        window.onresize = () => {
          if (timer) {
            return
          }
          timer = setTimeout(() => {
            this.maxHeight = document.getElementById('content').clientHeight - 260
            clearTimeout(timer)
            timer = null
          }, 300)
        }
        this.getPlugin()
        document.title = lang.user_list + '-' + lang.product_info + '-' + localStorage.getItem('back_website_name')
      },
      computed: {
        renewTotal () {
          return this.renewList.reduce((all, cur) => {
            all += Number(cur.renew_amount)
            return all
          }, 0)
        },
        calcShow () {
          return (data) => {
            return `#${data.id}-` + (data.username ? data.username : (data.phone ? data.phone : data.email)) + (data.company ? `(${data.company})` : '')
          }
        },
        isExist () {
          return !this.clientList.find(item => item.id === this.clientDetail.id)
        }
      },
      methods: {
        // 远程搜素
        remoteMethod (key) {
          this.clinetParams.keywords = key
          this.getClintList()
        },
        filterMethod (search, option) {
          return option
        },
        // 获取用户详情
        async getUserDetail () {
          try {
            const res = await getClientDetail(this.id);
            this.clientDetail = res.data.data.client;
          } catch (error) { }
        },
        async getPlugin () {
          try {
            const res = await getAddon()
            const temp = res.data.data.list.reduce((all, cur) => {
              all.push(cur.name)
              return all
            }, [])
            this.hasPlugin = temp.includes('IdcsmartRenew')
            this.hasTicket = temp.includes("IdcsmartTicket")
          } catch (error) {

          }
        },
        /* 批量续费 */
        async batchRenew () {
          this.renewForm = []
          this.renewList = []
          if (this.checkId.length === 0) {
            return this.$message.error(lang.select)
          }
          this.renewVisible = true
          try {
            this.renewLoading = true
            const params = {
              client_id: this.id,
              ids: this.checkId
            }
            const res = await getRenewBatch(params)
            this.renewList = res.data.data.list.map(item => {
              item.curCycle = item.billing_cycles[0]?.billing_cycle
              item.renew_amount = item.billing_cycles.length > 0 ? item.billing_cycles[0].price : 0.00
              return item
            })
            this.renewLoading = false

          } catch (error) {
            this.renewLoading = false
          }
        },
        // 批量删除
        async batchDel () {
          if (this.checkId.length === 0) {
            return this.$message.error(lang.select)
          }
          this.delVisible = true

        },
        rehandleSelectChange (value, { selectedRowData }) {
          this.checkId = value
          this.selectedRowKeys = selectedRowData;
        },
        // 提交批量续费
        async submitRenew () {
          try {
            const params = {
              ids: [],
              client_id: this.id,
              billing_cycles: {},
              amount_custom: {},
              pay: this.pay
            }
            let temp = JSON.parse(JSON.stringify(this.renewList))
            temp = temp.filter(item => item.billing_cycles.length > 0)
            temp.forEach(item => {
              params.ids.push(item.id)
              params.billing_cycles[item.id] = item.curCycle
              params.amount_custom[item.id] = item.renew_amount
            });
            this.submitLoading = true
            const res = await postRenewBatch(params)
            this.$message.success(res.data.msg)
            this.submitLoading = false
            this.renewVisible = false
            this.getClientList()
          } catch (error) {
            this.submitLoading = false
            console.log(error)
            this.$message.error(error.data.msg)
          }
        },
        changeCycle (row) {
          row.renew_amount = row.billing_cycles.filter(item => item.billing_cycle === row.curCycle)[0].price
        },
        changeUser (id) {
          this.id = id
          location.href = `client_host.html?client_id=${this.id}`
        },
        async getClintList () {
          try {
            const res = await getClientList(this.clinetParams)
            this.clientList = res.data.data.list
            this.clientTotal = res.data.data.count
          } catch (error) {
            console.log(error.data.msg)
          }
        },
        cancelRenew () {
          this.selectedRowKeys = []
          this.checkId = []
        },
        /* -----批量续费end-------- */
        changeUser (id) {
          this.id = id
          location.href = `client_host.html?client_id=${this.id}`
        },
        async getClintList () {
          try {
            this.searchLoading = true
            const res = await getClientList(this.clinetParams)
            this.clientList = res.data.data.list
            this.clientTotal = res.data.data.count
            this.searchLoading = false
          } catch (error) {
            this.searchLoading = false
            console.log(error.data.msg)
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
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getClientList()
        },
        async getClientList () {
          try {
            const res = await getClientPro(this.id, this.params)
            this.data = res.data.data.list
            this.total = res.data.data.count
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        async onConfirm () {
          try {
            const res = await deleteHost({ id: this.checkId })
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getClientList()
          } catch (error) {
            this.$message.error(error.data.msg)
            this.delVisible = false
          }
        },
        deltePro (row) {
          this.delVisible = true
          this.delId = row.id
        }
      },
      created () {
        this.id = location.href.split('?')[1].split('=')[1] * 1
        this.getClientList()
        this.getClintList()
        this.getUserDetail()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);

