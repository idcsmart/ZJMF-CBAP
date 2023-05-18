(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('real_name_approval')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    Vue.use(VueViewer.default)
    const host = location.origin
    const fir = location.pathname.split('/')[1]
    const str = `${host}/${fir}/`
    new Vue({
      data () {
        return {
          data: [],
          tableLayout: true,
          bordered: true,
          visible: false,
          delVisible: false,
          statusVisble: false,
          hover: true,
          virtualScroll: false,
          columns: [
            {
              colKey: 'id',
              title: 'ID',
              width: 90,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'username',
              title: lang.proposer,
              ellipsis: true,
              width: 200
            },
            {
              colKey: 'real_name',
              title: lang.real_name,
              ellipsis: true,
              width: 200
            },
            {
              colKey: 'type',
              title: lang.auth_type,
              width: 300,
              ellipsis: true
            },
            {
              colKey: 'status',
              title: lang.status,
              width: 200,
              ellipsis: true
            },
            {
              colKey: 'create_time',
              title: lang.order_post_time,
              width: 150,
              ellipsis: true
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 120,
              ellipsis: true
            },
          ],
          hideSortTips: true,
          params: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
          formData: { // 驳回审核
            reason: ''
          },
          rules: {
            reason: [
              { required: true, message: lang.input + lang.dismiss_the_reason, type: 'error' },
              { validator: val => val.length <= 100, message: lang.verify3 + 100, type: 'warning' }
            ]
          },
          loading: false,
          country: [],
          delId: '',
          curStatus: 1,
          statusTip: '',
          addTip: '',
          langList: [],
          roleTotal: 0,
          roleList: [],
          optType: 'pass',
          curId: '',
          roleParams: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          popupProps: {
            overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` })
          },
          maxHeight: '',
          currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥',
          // 详情
          payTit: lang.detail,
          detailVisible: false,
          realDetai: {},
          imgVisble: false,
          bigShow: [],
          num: 0,
          sNum: 10,
          prentNum: 100
        }
      },
      mounted () {
        this.maxHeight = document.getElementById('content').clientHeight - 220
        let timer = null
        window.onresize = () => {
          if (timer) {
            return
          }
          timer = setTimeout(() => {
            this.maxHeight = document.getElementById('content').clientHeight - 220
            clearTimeout(timer)
            timer = null
          }, 300)
        }
      },
      methods: {
        lookImg (url) {
          this.bigShow = []
          this.bigShow.push(url)
          this.$viewerApi({
            images: this.bigShow,
            options: {
              initialViewIndex: 0,
            },
          })
        },
        jumpUser (row) {
          location.href = str + `client_detail.html?client_id=${row.client_id}`
        },
        checkPwd (val) {
          if (val !== this.formData.password) {
            return { result: false, message: window.lang.password_tip, type: 'error' };
          }
          return { result: true }
        },
        rotation () {
          this.num += 1
        },
        // 获取列表
        async getList () {
          try {
            this.loading = true
            const res = await getRealName(this.params)
            this.loading = false
            this.data = res.data.data.list
            this.total = res.data.data.count
          } catch (error) {
            this.loading = false
          }
        },
        // 切换分页
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getList()
        },
        // 排序
        sortChange (val) {
          if (val === undefined) {
            this.params.orderby = 'id'
            this.params.sort = 'desc'
          } else {
            this.params.orderby = val.sortBy
            this.params.sort = val.descending ? 'desc' : 'asc'
          }
          this.getList()
        },
        // 切换状态
        clearKey () {
          this.params.keywords = ''
          this.seacrh()
        },
        seacrh () {
          this.params.page = 1
          this.getList()
        },
        close () {
          this.visible = false
          this.$nextTick(() => {
            this.$refs.userDialog && this.$refs.userDialog.reset()
          })
        },
        // 驳回审核
        rejectHandler (row) {
          this.statusVisble = true
          this.optType = 'reject'
          this.delId = row.id
          this.statusTip = lang.sure + lang.reject + '?'
        },
        // 审核通过
        passHandler (row) {
          this.delId = row.id
          this.optType = 'pass'
          this.statusTip = lang.sure + lang.pass + '?'
          this.statusVisble = true
        },
        async sureChange () {
          try {
            const res = await changeRealStatus(this.optType, this.delId)
            this.$message.success(res.data.msg)
            this.statusVisble = false
            this.getList()
          } catch (error) {
            console.log(error)
            this.$message.error(error.data.msg)
            this.statusVisble = false
          }
        },
        closeDialog () {
          this.statusVisble = false
        },
        // 获取详情
        async getDetail (row) {
          try {
            const res = await getRealNameDetail(row.id)
            const temp = { ...res.data.data.log, type: row.type }
            temp.fontUrl = temp.img[0]
            temp.backUrl = temp.img[1]
            temp.slicense = temp.img[2]
            this.realDetai = temp
            this.detailVisible = true
          } catch (error) {

          }
        },
        // 确认已付款/修改流水号
        confirmRemittance (row) {
          this.payForm = JSON.parse(JSON.stringify(row))
          this.payTit = row.status === 3 ? lang.update + lang.flow_number : lang.confirm_remittance
          this.payVisible = true
        },
        onSubmitPay ({ validateResult, firstError }) {
          if (validateResult === true) {
            if (this.payForm.status !== 3) {
              this.surePay()
            } else {
              this.changeTransaction()
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        // 确认付款
        async surePay () {
          try {
            const { id, transaction_number } = this.payForm
            const params = {
              id, transaction_number
            }
            this.btnLoading = true
            const res = await submitPay(params)
            this.$message.success(res.data.msg)
            this.getList()
            this.payVisible = false
            this.btnLoading = false
          } catch (error) {
            this.btnLoading = false
            this.$message.error(error.data.msg)
          }
        },
        // 修改流水
        async changeTransaction () {
          try {
            const { id, transaction_number } = this.payForm
            const params = {
              id, transaction_number
            }
            this.btnLoading = true
            const res = await updateTransaction(params)
            this.$message.success(res.data.msg)
            this.getList()
            this.payVisible = false
            this.btnLoading = false
          } catch (error) {
            this.btnLoading = false
            this.$message.error(error.data.msg)
          }
        },
        closePay () {
          this.detailVisible = false
        },
        // 复制账号
        copyHandler (id) {
          const name = document.getElementById(id)
          name.select()
          document.execCommand("Copy")
          this.$message.success(lang.copy + lang.success)
        },
      },
      created () {
        this.getList()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
