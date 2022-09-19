(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('client-detail')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    new Vue({
      data () {
        return {
          id: '', // 用户id
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          hover: true,
          diaTitle: '',
          logColumns: [
            {
              colKey: 'ip',
              title: 'IP' + lang.address,
            },
            {
              colKey: 'login_time',
              title: lang.login_time,
            }
          ],
          params: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
          loading: false,
          moneyLoading: false,
          statusVisble: false,
          title: '',
          delId: '',
          formData: {
            id: '',
            username: '',
            phone_code: '',
            phone: '',
            email: '',
            country: '',
            address: '',
            company: '',
            language: 'zh-cn',
            notes: '',
            password: ''
          },
          clientList: [], // 用户列表
          rules: {
            country: [
              {
                validator: val => val.length <= 100,
                message: lang.verify3 + 100, type: 'waring'
              }
            ],
            address: [
              {
                validator: val => val.length <= 255,
                message: lang.verify3 + 255, type: 'waring'
              }
            ],
            notes: [
              {
                validator: val => val.length <= 1000,
                message: lang.verify3 + 1000, type: 'waring'
              }
            ],
            password: [
              { pattern: /^[\w@!#$%^&*()+-_]{6,32}$/, message: lang.verify8 + '6~32' + '，' + lang.verify14, type: 'warning' }
            ],
          },
          visibleMoney: false,
          visibleLog: false,
          moneyData: { // 充值/扣费
            id: '',
            type: '', //  recharge充值 deduction扣费
            amount: '',
            notes: ''
          },
          moneyRules: {
            amount: [
              { required: true, message: lang.input + lang.money, type: 'error' },
              {
                pattern: /^\d+(\.\d{0,2})?$/, message: lang.verify5, type: 'warning'
              },
              {
                validator: val => val > 0, message: lang.verify5, type: 'warning'
              }
            ],
            notes: [{ required: true, message: lang.input + lang.content, type: 'error' }]
          },
          logCunt: 0,
          // 变更记录
          logData: [],
          columns: [
            {
              colKey: 'id',
              title: 'ID',
              width: 120
            },
            {
              colKey: 'amount',
              title: lang.change_money,
              width: 120
            },
            {
              colKey: 'type',
              title: lang.type,
              width: 120
            },
            {
              colKey: 'create_time',
              title: lang.change_time,
              width: 180
            },
            {
              colKey: 'notes',
              title: lang.notes,
              ellipsis: true,
              width: 200
            },
            {
              colKey: 'admin_name',
              title: lang.operator,
              width: 100
            }
          ],
          params: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          moneyPage: {
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          logSizeOptions: [20, 50, 100],
          statusTip: '',
          country: [],
          popupProps: {
            overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` })
          },
          currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix,
          clientTotal: 0,
          clinetParams: {
            page: 1,
            limit: 1000,
            orderby: 'id',
            sort: 'desc'
          },
          website_url: '',
          refundAmount: 0.00,
          authList: JSON.parse(JSON.stringify(localStorage.getItem('backAuth'))),
          // 充值相关开始
          // 是否显示充值弹窗
          visibleRecharge: false,
          // 充值弹窗数据
          rechargeData: {
            gateway: '',
            amount: ''
          },
          // 充值弹窗提交验证
          rechargeRules: {
            amount: [
              { required: true, message: lang.input + lang.money, type: 'error' },
              {
                pattern: /^\d+(\.\d{0,2})?$/, message: lang.verify5, type: 'warning'
              },
              {
                validator: val => val > 0, message: lang.verify5, type: 'warning'
              }
            ],
          },
          // 支付方式列表
          gatewayList: []
          // 充值相关结束
        }
      },
      computed: {
        inputLabel () {
          if (this.moneyData.type === 'recharge') {
            return this.currency_prefix
          } else {
            return '-' + this.currency_prefix
          }
        }
      },
      created () {
        localStorage.setItem('curValue', 2)
        const query = location.href.split('?')[1].split('&')
        this.moneyData.id = this.id = Number(this.getQuery(query[0]))
        this.langList = JSON.parse(localStorage.getItem('common_set')).lang_home
        this.getUserDetail()
        this.getCountry()
        // 获取用户列表
        this.getClintList()
        this.getSystemOption()
        // 获取支付方式列表
        this.getGatewayList()
        this.getRefundAmount()
      },
      methods: {
        // 获取退款
        async getRefundAmount () {
          try {
            const res = await getRefund(this.id)
            this.refundAmount = res.data.data.amount
          } catch (error) {
          }
        },
        // 获取后台配置的路径
        async getSystemOption () {
          try {
            const res = await getSystemOpt()
            this.website_url = res.data.data.website_url
          } catch (error) {
          }
        },
        // 以用户登录
        async loginByUser () {
          try {
            const res = await loginByUserId(this.id)
            localStorage.setItem('jwt', res.data.data.jwt)
            // 获取前台导航存入 locaStorage  frontMenus

            // const url = '/reactmember/#/'
            const url = this.website_url
            const newPage = window.open('', '_blank')
            newPage.location = url
          } catch (error) {
            console.log(error)
            this.$message.error(error.data.msg)
          }
        },
        changeUser (id) {
          this.id = id
          location.href = `client_detail.html?client_id=${this.id}`
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
        getQuery (val) {
          return val.split('=')[1]
        },
        // 删除用户
        deleteUser () {
          this.delVisible = true
        },
        async sureDelUser () {
          try {
            const res = await deleteClient(this.id)
            this.delVisible = false
            this.$message.success(res.data.msg)
            setTimeout(() => {
              location.href = 'client.html'
            }, 300)
          } catch (error) {
            this.delVisible = false
            this.$message.error(error.data.msg)
          }
        },
        // 启用/停用
        changeStatus () {
          this.statusVisble = true
          this.statusTip = this.data.status ? lang.sure_Close : lang.sure_Open
        },
        async sureChange () {
          try {
            const params = {
              status: this.data.status === 1 ? 0 : 1
            }
            const res = await changeOpen(this.id, params)
            this.statusVisble = false
            this.$message.success(res.data.msg)
            this.getUserDetail()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 充值/扣费
        changeMoney () {
          this.moneyData.type = 'recharge'
          this.moneyData.amount = ''
          this.moneyData.notes = ''
          this.visibleMoney = true
        },
        // 充值相关开始
        // 显示充值弹窗
        showRecharge () {
          // 初始化充值数据
          this.rechargeData.gateway = this.gatewayList[0].name
          this.rechargeData.amount = ''
          this.visibleRecharge = true
        },
        // 取消充值
        closeRechorge () {
          this.visibleRecharge = false
        },
        // 充值提交
        confirmRecharge ({ validateResult, firstError }) {
          if (validateResult === true) {
            // 调用充值接口
            const params = {
              client_id: this.id,
              amount: Number(this.rechargeData.amount),
              gateway: this.rechargeData.gateway
            }
            recharge(params).then(res => {
              if (res.data.status === 200) {
                this.$message.success(res.data.msg)
                // 关闭弹窗
                this.visibleRecharge = false
                // 刷新余额
                this.getUserDetail()
              }
            }).catch(error => {
              this.$message.error(error.data.msg)
            })
          } else {
            this.$message.warning(firstError);
          }
        },
        // 获取充值列表
        getGatewayList () {
          getPayList().then(res => {
            if (res.data.status === 200) {
              this.gatewayList = res.data.data.list
            }
          })
        },


        // 充值相关结束

        async confirmMoney ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const res = await updateClientDetail(this.id, this.moneyData)
              this.$message.success(res.data.msg)
              this.visibleMoney = false
              this.getUserDetail()
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        closeMoney () {
          this.visibleMoney = false
          this.moneyData.amount = ''
          this.moneyData.notes = ''
          this.$refs.moneyRef && this.$refs.moneyRef.clearValidate()
          this.$refs.moneyRef && this.$refs.moneyRef.reset()
        },
        // 变更记录
        changeLog () {
          this.visibleLog = true
          this.getChangeLog()
        },
        // 获取变更记录列表
        async getChangeLog () {
          try {
            this.moneyLoading = true
            const res = await getMoneyDetail(this.id, this.moneyPage)
            this.logData = res.data.data.list
            this.logCunt = res.data.data.count
            this.moneyLoading = false
          } catch (error) {
            this.moneyLoading = false
            this.$message.error(error.data.msg)
          }
        },
        closeLog () {
          this.visibleLog = false
        },
        // 提交修改用户信息
        updateUserInfo () {
          this.$refs.userInfo.validate().then(async res => {
            if (res !== true) {
              this.$message.error(res.name[0].message)
              return
            }
            // 验证通过
            try {
              const res = await updateClient(this.id, this.formData)
              this.$message.success(res.data.msg)
              this.getUserDetail()
              this.formData.password = ''
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          }).catch(err => {
            console.log(err)
          })
        },
        // 金额变更分页
        changePage (e) {
          this.moneyPage.page = e.current
          this.moneyPage.limit = e.pageSize
          this.getChangeLog()
        },
        // 获取用户详情
        async getUserDetail () {
          try {
            const res = await getClientDetail(this.id)
            const temp = res.data.data.client
            this.data = temp
            this.formData.username = temp.username
            this.formData.phone_code = temp.phone_code
            this.formData.phone = temp.phone
            this.formData.email = temp.email
            this.formData.country = temp.country
            this.formData.address = temp.address
            this.formData.company = temp.company
            this.formData.language = temp.language
            this.formData.notes = temp.notes
          } catch (error) {
          }
        },
        // 获取国家列表
        async getCountry () {
          try {
            const res = await getCountry()
            this.country = res.data.data.list
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },

      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
