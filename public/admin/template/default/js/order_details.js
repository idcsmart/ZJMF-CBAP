(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('order-details')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    new Vue({
      data () {
        return {
          id: '',
          orderDetail: {},
          currency_prefix: '',
          type: '',
          visible: false,
          title: '',
          rules: {
            amount: [
              { required: true, message: lang.input + lang.money, type: 'error' },
              {
                pattern: /^-?\d+(\.\d{0,2})?$/, message: lang.verify4, type: 'warning'
              },
              {
                validator: val => val > 0, message: lang.verify4, type: 'warning'
              }
            ],
          },
          formData: {
            id: '',
            amount: ''
          },
          submitLoading: false,
          payList: [],
          gateway: '',
          payVisible: false,
          signForm: {
            amount: 0,
            credit: 0
          },
          use_credit: true,
          userInfo: {},
          columns: [
            {
              colKey: 'description',
              title: lang.description,
            },
            {
              colKey: 'amount',
              title: lang.money,
              ellipsis: true,
              width: 150
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 100
            }
          ],
          tableLayout: false,
          bordered: true,
          visible: false,
          hover: true,
          loading: false,
          delVisible: false,
          curId: '',
          visibleLog: false,
          logColumns: [
            {
              colKey: "id",
              title: "ID",
              width: 120,
            },
            {
              colKey: "amount",
              title: lang.change_money,
              width: 120,
            },
            {
              colKey: "type",
              title: lang.type,
              width: 120,
            },
            {
              colKey: "create_time",
              title: lang.change_time,
              width: 180,
            },
            {
              colKey: "notes",
              title: lang.notes,
              ellipsis: true,
              width: 200,
            },
            {
              colKey: "admin_name",
              title: lang.operator,
              width: 100,
            },
          ],
          moneyPage: {
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
            order_id: ''
          },
          logData: [],
          moneyLoading: false,
          logCunt: 0,
          pageSizeOptions: [20, 50, 100],
          userCredit: ''
        }
      },
      mounted () {
        this.getOrderDetail()
        this.getPayway()
      },
      methods: {
        // 变更记录
        changeLog () {
          this.visibleLog = true;
          this.getChangeLog();
        },
        closeLog () {
          this.visibleLog = false;
        },
        // 金额变更分页
        changePage (e) {
          this.moneyPage.page = e.current;
          this.moneyPage.limit = e.pageSize;
          this.getChangeLog();
        },
        // 获取变更记录列表
        async getChangeLog () {
          try {
            this.moneyLoading = true;
            const res = await getMoneyDetail(this.orderDetail.client_id, this.moneyPage);
            this.logData = res.data.data.list;
            this.logCunt = res.data.data.count;
            this.moneyLoading = false;
          } catch (error) {
            this.moneyLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 增加订单子项
        addSubItem () {
          this.orderDetail.items.push({
            id: this.id,
            description: '',
            amount: '',
            edit: 1
          })
        },
        saveFlow (row) {
          if (!row.description) {
            return this.$message.error(lang.input + lang.description)
          }
          if (!row.amount) {
            return this.$message.error(lang.input + lang.money)
          }
          if (row.id === this.id) { // 修改
            this.addItem(row)
          } else {
            this.editItem(row)
          }
        },
        async addItem (row) {
          try {
            const res = await updateOrder({
              id: this.id,
              amount: row.amount,
              description: row.description
            })
            this.$message.success(res.data.msg)
            this.getOrderDetail()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        async editItem (row) {
          try {
            const res = await updateArtificialOrder({
              id: row.id,
              amount: row.amount,
              description: row.description
            })
            this.$message.success(res.data.msg)
            this.getOrderDetail()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        delteFlow (row, ind) {
          if (row.id === this.id) {
            this.orderDetail.items.splice(ind, 1)
            return
          }
          this.curId = row.id
          this.delVisible = true
        },
        async sureDelUser () {
          try {
            this.submitLoading = true
            const res = await delArtificialOrder({
              id: this.curId
            })
            this.$message.success(res.data.msg)
            this.submitLoading = false
            this.delVisible = false
            this.getOrderDetail()
          } catch (error) {
            this.submitLoading = false
            this.$message.error(error.data.msg)
          }
        },
        // 获取用户信息
        async getUserInfo (id) {
          try {
            const res = await getClientDetail(id)
            this.userCredit = res.data.data.client.credit
          } catch (error) {

          }
        },
        async changePay (type) {
          try {
            const res = await changePayway({
              id: this.id,
              gateway: type
            })
            this.$message.success(res.data.msg)
            this.getOrderDetail()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 标记支付
        signPay () {
          this.payVisible = true
          this.delId = this.id
          this.signForm.amount = this.orderDetail.amount
          this.signForm.credit = this.orderDetail.amount_unpaid
        },
        async sureSign () {
          try {
            const params = {
              id: this.id,
              //  use_credit: this.use_credit ? 1 : 0
            }
            const res = await signPayOrder(params)
            this.$message.success(res.data.msg)
            this.getOrderDetail()
            this.payVisible = false
          } catch (error) {
            this.$message.error(error.data.msg)
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
        changeAdd (val) {
          if (val * 1 > this.orderDetail.apply_credit_amount * 1) {
            this.formData.amount = (this.orderDetail.apply_credit_amount * 1).toFixed(2)
          }
        },
        changeSub (val) {
          if (val * 1 > this.orderDetail.credit * 1) {
            this.formData.amount = (this.orderDetail.credit * 1).toFixed(2)
          }
        },
        async getOrderDetail () {
          try {
            const res = await getOrderDetails({ id: this.id })
            this.orderDetail = res.data.data.order
            this.gateway = this.orderDetail.gateway
            this.getUserInfo(this.orderDetail.client_id)
          } catch (error) {

          }
        },
        changeCredit (type) {
          this.type = type
          if (type === 'add') {
            this.title = `${lang.app}${lang.credit}`
            // 可应用余额存在且用户余额足够的时候
            if (this.orderDetail.apply_credit_amount) {
              this.formData.amount = (this.orderDetail.apply_credit_amount * 1 - this.userCredit * 1) >= 0
                ? (this.userCredit * 1).toFixed(2)
                : (this.orderDetail.apply_credit_amount * 1).toFixed(2)
            } else {
              this.formData.amount = (this.orderDetail.apply_credit_amount * 1).toFixed(2)
            }
          } else {
            // 扣除余额
            this.formData.amount = (this.orderDetail.credit * 1).toFixed(2)
            this.title = `${lang.deduct}${lang.credit}`
          }
          this.visible = true
        },
        close () {
          this.visible = false
        },
        onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            if (this.type === 'add') {
              this.addCredit()
            } else {
              this.subCredit()
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        async addCredit () {
          try {
            this.submitLoading = true
            const res = await orderApplyCredit(this.formData)
            this.$message.success(res.data.msg)
            this.submitLoading = false
            this.visible = false
            this.getOrderDetail()
          } catch (error) {
            this.submitLoading = false
            this.$message.error(error.data.msg)
          }
        },
        async subCredit () {
          try {
            this.submitLoading = true
            const res = await orderRemoveCredit(this.formData)
            this.$message.success(res.data.msg)
            this.submitLoading = false
            this.visible = false
            this.getOrderDetail()
          } catch (error) {
            this.submitLoading = false
            this.$message.error(error.data.msg)
          }
        },
      },

      created () {
        this.id = this.formData.id = this.moneyPage.order_id = location.href.split('?')[1].split('=')[1];
        this.currency_prefix = JSON.parse(localStorage.getItem('common_set')).currency_prefix || '¥';
        document.title = lang.create_order_detail + '-' + localStorage.getItem('back_website_name');
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);