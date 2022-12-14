(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('withdrawal')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    new Vue({
      data () {
        return {
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          statusVisble: false,
          hover: true,
          virtualScroll: false,
          columns: [
            {
              colKey: 'id',
              title: lang.order_index,
              width: 90,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'source',
              title: lang.withdrawal_source,
              width: 500,
              ellipsis: true
            },
            {
              colKey: 'amount',
              title: lang.requested_amount,
              width: 150,
              ellipsis: true
            },
            {
              colKey: 'withdraw_amount',
              title: lang.received_amount,
              width: 150,
              ellipsis: true
            },
            {
              colKey: 'username',
              title: lang.proposer,
              width: 200,
              ellipsis: true
            },
            {
              colKey: 'create_time',
              title: lang.time_application,
              width: 300,
              ellipsis: true
            },
            {
              colKey: 'status',
              title: lang.status,
              width: 110,
              ellipsis: true
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 100,
              ellipsis: true
            },
          ],
          hideSortTips: true,
          params: {
            status: '',
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
          formData: { // ????????????
            reason: '',
            custom: ''
          },
          rules: {
            reason: [
              { required: true, message: lang.select + lang.dismiss_the_reason, type: 'error' },
            ],
            custom: [
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
          optType: 'create',
          curId: '',
          roleParams: {
            page: 1,
            limit: 20
          },
          popupProps: {
            overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` })
          },
          maxHeight: '',
          // ??????????????????
          changeVisble: false,
          updateStatus: '',
          payVisible: false,
          payStatus: 'add',
          payTit: '',
          payForm: {
            transaction_number: ''
          },
          btnLoading: false,
          payRules: {
            transaction_number: [
              { required: true, message: lang.input + lang.flow_number, type: 'error' },
              { pattern: /^[A-Za-z0-9]+/, message: lang.verify9, type: 'warning' }
            ]
          },
          currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix || '??',
          reasons: [],
          baseUrl: url
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
      created () {
        this.getResons()
        this.getList()
      },
      methods: {
        async getResons () {
          try {
            const res = await getRejectReason()
            const temp = res.data.data.list
            temp.push({
              id: 0,
              reason: lang.custom_reason
            })
            this.reasons = temp
          } catch (error) {

          }
        },
        checkPwd (val) {
          if (val !== this.formData.password) {
            return { result: false, message: window.lang.password_tip, type: 'error' };
          }
          return { result: true }
        },
        // ????????????
        async getList () {
          try {
            this.loading = true
            const res = await getWithdrawal(this.params)
            this.loading = false
            this.data = res.data.data.list
            this.total = res.data.data.count
          } catch (error) {
            this.loading = false
          }
        },
        // ????????????
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getList()
        },
        // ??????
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
        // ????????????
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
        // ????????????
        rejectHandler (row) {
          this.formData.reason = ''
          this.formData.custom = ''
          this.visible = true
          this.delId = row.id
          this.addTip = lang.approved_reject
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = {
                id: this.delId,
                status: 2,
              }
              if (this.formData.reason === 0) {
                params.reason = this.formData.custom
              } else {
                params.reason = this.reasons.filter(item=> item.id === this.formData.reason)[0]?.reason
              }
              const res = await changeStatus(params)
              this.$message.success(res.data.msg)
              this.getList()
              this.visible = false
              this.$refs.userDialog.reset()
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        // ?????????????????????
        editStatus (row) {
          this.changeVisble = true
          this.delId = row.id
          this.updateStatus = row.status ? 0 : 1
        },
        closeChange () {
          this.changeVisble = false
        },
        async onSubmitChange () {
          try {
            const res = await changeWithdrawStatus({
              id: this.delId,
              status: this.updateStatus
            })
            this.$message.success(res.data.msg)
            this.changeVisble = false
            this.getList()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // ????????????
        passHandler (row) {
          this.delId = row.id
          this.statusTip = lang.sure + lang.approved + '?'
          this.statusVisble = true
        },
        async sureChange () {
          try {
            const params = {
              id: this.delId,
              status: 1
            }
            const res = await changeStatus(params)
            this.$message.success(res.data.msg)
            this.statusVisble = false
            this.getList()
          } catch (error) {
            this.$message.error(error.data.msg)
            this.statusVisble = false
          }
        },
        closeDialog () {
          this.statusVisble = false
        },
        // ???????????????/???????????????
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
        // ????????????
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
        // ????????????
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
          this.payVisible = false
        },
        // ????????????
        copyHandler (id) {
          const name = document.getElementById(id)
          name.select()
          document.execCommand("Copy")
          this.$message.success(lang.copy + lang.success)
        },
      },

    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
