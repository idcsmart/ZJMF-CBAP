(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('promo-code')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    const host = location.host
    const fir = location.pathname.split('/')[1]
    const str = `${host}/${fir}/`
    new Vue({
      data () {
        return {
          baseURL: url,
          curLevelId: '',
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          statusVisble: false,
          hover: true,
          currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥',
          columns: [
            {
              colKey: 'code',
              title: lang.promo_code,
              className: 'code-item',
              width: 120
            },
            {
              colKey: 'type',
              title: lang.coupon_code_type,
              width: 200,
              ellipsis: true
            },
            {
              colKey: 'value',
              title: lang.coupon_num,
              width: 200,
              ellipsis: true
            },
            {
              colKey: 'use',
              title: lang.use_used,
              width: 200,
              className: 'used'
            },
            {
              colKey: 'start_time',
              title: lang.start_time,
              width: 140
            },
            {
              colKey: 'end_time',
              title: lang.close_time,
              width: 140
            },
            {
              colKey: 'status',
              title: lang.current_state,
              width: 120
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 100
            },
          ],
          params: {
            keywords: '',
            type: '',
            status: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
          loading: false,
          maxHeight: '',
          levelList: [],
          statusVisble: false,
          recordTit: '',
          statusTip: '',
          /* 使用记录 */
          recordDialog: false,
          recordLoading: false,
          recordList: [],
          recordPage: [5, 10],
          recordColumns: [
            {
              colKey: 'username',
              title: lang.order_username,
              className: 'code-item'
            },
            {
              colKey: 'order_id',
              title: lang.order_number
            },
            {
              colKey: 'create_time',
              title: lang.use_time
            },
            {
              colKey: 'amount',
              title: lang.amount_of_money
            },
            {
              colKey: 'discount',
              title: lang.discount_amount,
              width: 100
            },
          ],
          recordTotal: 0,
          recordParams: {
            id: '',
            page: 1,
            limit: 5
          },
          // 切换状态
          statusParams: {

          },
          typeList: [
            {
              value: 'percent',
              label: lang.percent
            },
            {
              value: 'fixed_amount',
              label: lang.fixed_amount
            },
            {
              value: 'replace_price',
              label: lang.replace_price
            },
            {
              value: 'free',
              label: lang.free
            }
          ],
          statusList: [
            {
              value: 'Suspended',
              label: lang.promo_suspended
            },
            {
              value: 'Active',
              label: lang.promo_active
            },
            {
              value: 'Expiration',
              label: lang.promo_expiration
            },
            {
              value: 'Pending',
              label: lang.promo_pending
            }
          ]
        }
      },
      mounted () {
        this.maxHeight = document.getElementById('content').clientHeight - 180
        let timer = null
        window.onresize = () => {
          if (timer) {
            return
          }
          timer = setTimeout(() => {
            this.maxHeight = document.getElementById('content').clientHeight - 180
            clearTimeout(timer)
            timer = null
          }, 300)
        }
      },
      created () {
        this.getPromoList()
      },
      methods: {
        goDetail ({ row, e }) {
          const name = e.target.className
          if (name === 'stop' || name.baseVal?.indexOf('stop')) {
            return
          }
          location.href = `create_promo_code.html?id=${row.id}`
        },
        // 复制账号
        copyCode (id) {
          const name = document.getElementById(id)
          name.select()
          document.execCommand("Copy")
          this.$message.success(lang.copy + lang.success)
        },
        addPromo () {
          location.href = 'create_promo_code.html'
        },
        updatePromo (row) {
          location.href = `create_promo_code.html?id=${row.id}`
        },
        // 切换状态
        changeStatus (row) {
          this.statusParams = JSON.parse(JSON.stringify(row))
          this.statusVisble = true
          this.statusTip = lang.sure + (row.status === 'Suspended' ? lang.enable : lang.deactivate) + '?'
        },
        async sureChange () {
          try {
            const params = {
              id: this.statusParams.id,
              status: this.statusParams.status === 'Suspended' ? 1 : 0
            }
            const res = await changePromoStatus(params)
            this.$message.success(res.data.msg)
            this.getPromoList()
            this.statusVisble = false
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        jumpUser (row) {
          location.href = 'http://' + str + `client_detail.html?client_id=${row.client_id}`
        },
        jumpOrder (row) {
          location.href = 'http://' + str + `order.html?order_id=${row.order_id}`
        },
        // 使用记录
        getRecord (row) {
          this.recordParams.id = row.id
          this.recordParams.page = 1
          this.recordParams.limit = 5
          this.recordDialog = true
          this.recordTit = lang.use_record + '-' + row.code
          this.getRecordList()
        },
        changeRecord (e) {
          this.recordParams.page = e.current
          this.recordParams.limit = e.pageSize
          this.getRecordList()
        },
        async getRecordList () {
          try {
            this.recordLoading = true
            const res = await usePromoRecord(this.recordParams)
            this.recordTotal = res.data.data.count
            this.recordList = res.data.data.list
            this.recordLoading = false
          } catch (error) {
            this.recordLoading = false
          }
        },
        // 获取列表
        async getPromoList () {
          try {
            this.loading = true
            const res = await getPromo(this.params, this.curLevelId)
            this.loading = false
            this.data = res.data.data.list
            this.total = res.data.data.count
          } catch (error) {
            this.loading = false
            this.$message.error(error.data.msg)
          }
        },
        // 切换分页
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.params.keywords = ''
          this.getPromoList()
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
          this.getPromoList()
        },
        clearKey () {
          this.params.keywords = ''
          this.seacrh()
        },
        seacrh () {
          this.params.page = 1
          this.getPromoList()
        },
      }
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);