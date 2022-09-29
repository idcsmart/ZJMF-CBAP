(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('host-detail')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    new Vue({
      data () {
        return {
          id: '',
          client_id: '',
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          hover: true,
          diaTitle: '',
          serverParams: {
            page: 1,
            limit: 20
          },
          serverGroupParams: {
            page: 1,
            limit: 20
          },
          total: 0,
          groupTotal: 0,
          loading: false,
          moneyLoading: false,
          statusVisble: false,
          title: '',
          delId: '',
          formData: {
            id: '',
            product_id: '',
            server_id: '',
            name: '',
            notes: '',
            first_payment_amount: '',
            renew_amount: '',
            billing_cycle: '',
            active_time: '',
            due_time: '',
            status: ''
          },
          status: [],
          rules: {
            name: [
              { validator: val => val.length <= 100, message: lang.verify3 + 100 }
            ],
            notes: [
              { validator: val => val.length <= 1000, message: lang.verify3 + 1000 }
            ],
            first_payment_amount: [
              { required: true, message: lang.input + lang.buy_amount, type: 'error' },
              {
                pattern: /^\d+(\.\d{0,2})?$/, message: lang.verify12, type: 'warning'
              },
              {
                validator: val => val >= 0, message: lang.verify12, type: 'warning'
              }
            ],
            renew_amount: [
              { required: true, message: lang.input + lang.renew_amount, type: 'error' },
              {
                pattern: /^\d+(\.\d{0,2})?$/, message: lang.verify12, type: 'warning'
              },
              {
                validator: val => val >= 0, message: lang.verify12, type: 'warning'
              }
            ]
          },
          // 变更记录
          logData: [],
          logCunt: 0,
          tableLayout: false,
          bordered: true,
          hover: true,
          statusTip: '',
          proList: [],
          currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix,
          serverList: [],
          cycleList: [
            { value: 'free', label: lang.free },
            { value: 'onetime', label: lang.onetime },
            { value: 'recurring_prepayment', label: lang.recurring_prepayment },
            { value: 'recurring_postpaid', label: lang.recurring_postpaid },
          ],
          done: false,
          popupProps: {
            overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` }),
          },
          config: '',
          // 续费相关
          renewVisible: false,
          renewList: [],
          curId: 1,
          renewTotal: '',
          pay: false,
          submitLoading: false,
          showId: [1, 2, 3],
          curRenew: {},
          curStatus: ''
        }
      },
      watch: {
        'formData.type': {
          immediate: true,
          handler (val) {
            this.curList = val === 'server' ? this.serverList : this.serverGroupList
          }
        },
        serverList () {
          this.done = this.serverList.length === this.total
        },
        curId: {
          handler (val) {
            this.curRenew = this.renewList[val - 1]
          }
        }
      },
      created () {
        const query = location.href.split('?')[1].split('&')
        this.client_id = this.getQuery(query[0])
        this.formData.id = this.id = this.getQuery(query[1])
        this.langList = JSON.parse(localStorage.getItem('common_set')).lang_home
        this.getProductDetail()
        this.getProList()
        this.getproModule()
        localStorage.setItem('curValue', 2)
      },
      computed: {
        disabled () {
          return this.formData.due_time === '' && this.formData.billing_cycle === 'onetime'
        }
      },
      methods: {
        /* 续费 */
        renewDialog () {
          this.renewVisible = true
          this.getRenewPage()
        },
        // 获取续费页面
        async getRenewPage () {
          try {
            const res = await getSingleRenew(this.formData.id)
            this.renewList = res.data.data.host.map((item, index) => {
              item.id = index + 1
              return item
            })
            this.curRenew = this.renewList[0]
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 向左移动
        subIndex () {
          let num = this.curId
          if (num > 1) {
            num -= 1
            this.curId -= 1
          }
          if (this.showId[0] > 1) {
            let newIds = this.showId
            newIds[0] = newIds[0] - 1
            newIds[1] = newIds[1] - 1
            newIds[2] = newIds[2] - 1
            this.showId = newIds
          }
        },
        // 向右移动
        addIndex () {
          let num = this.curId
          if (num < this.renewList.length) {
            num += 1
            this.curId = num++
          }
          if (this.showId[2] < this.renewList.length) {
            let newIds = this.showId
            newIds[0] = newIds[0] + 1
            newIds[1] = newIds[1] + 1
            newIds[2] = newIds[2] + 1
            this.showId = newIds
          }
        },
        checkCur (item) {
          this.curId = item.id
        },
        async submitRenew () {
          try {
            this.submitLoading = true
            const temp = JSON.parse(JSON.stringify(this.curRenew))
            delete temp.id
            const params = {
              id: this.formData.id,
              pay: this.pay,
              ...temp
            }
            const res = await postSingleRenew(params)
            this.$message.success(res.data.msg)
            this.submitLoading = false
            this.renewVisible = false
            this.getProductDetail()
          } catch (error) {
            this.submitLoading = false
            this.$message.error(error.data.msg)
          }
        },
        back () {
          this.delVisible = true
        },
        // 删除
        deltePro (row) {
          this.delVisible = true
        },
        async onConfirm () {
          try {
            const res = await deletePro(this.id)
            this.$message.success(res.data.msg)
            this.delVisible = false
            setTimeout(() => {
              window.location = document.referrer
            }, 300)
          } catch (error) {
            this.delVisible = false
            this.$message.error(error.data.msg)
          }
        },
        getQuery (val) {
          return val.split('=')[1]
        },
        checkTime (val) {
          if (moment(val).unix() > moment(this.formData.due_time).unix()) {
            return { result: false, message: lang.verify6, type: 'error' }
          }
          return { result: true }
        },
        checkTime1 (val) {
          if (moment(val).unix() < moment(this.formData.active_time).unix()) {
            return { result: false, message: lang.verify6, type: 'error' }
          }
          return { result: true }
        },
        changeActive () {
          this.$refs.userInfo.validate({
            fields: ['active_time', 'due_time']
          });
        },
        async getproModule () {
          try {
            const res = await getproModule(this.id)
            this.config = res.data.data.content
          } catch (error) {
          }
        },
        async getProList () {
          try {
            const res = await getProList()
            this.proList = res.data.data.list
          } catch (error) {
          }
        },
        changeType (type) {
          this.formData.type = type
          this.formData.rel_id = ''
        },
        // 修改
        updateUserInfo () {
          this.$refs.userInfo.validate().then(async res => {
            if (res !== true) {
              this.$message.error(res.name[0].message)
              return
            }
            // 验证通过
            try {
              const params = { ...this.formData }
              params.due_time = (params.due_time === '') ? 0 : params.due_time
              params.active_time = (params.active_time === '') ? 0 : params.active_time
              if (params.active_time === 0) {
                params.active_time = moment(params.active_time * 1000).format('YYYY-MM-DD HH:mm:ss')
              }
              if (params.due_time === 0) {
                params.due_time = moment(params.due_time * 1000).format('YYYY-MM-DD HH:mm:ss')
              }
              const res = await updateProduct(this.id, params)
              this.$message.success(res.data.msg)
              this.getProductDetail()
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          }).catch(err => {
            console.log(err)
          })
        },
        // 获取用户详情
        async getProductDetail () {
          try {
            let inter = await getInterface(this.serverParams)
            this.serverList = inter.data.data.list
            this.total = inter.data.data.count
            if (this.total > 20) {
              this.serverParams.limit = this.total
              inter = await getInterface(this.serverParams)
              this.serverList = inter.data.data.list
            }
            const res = await getProductDetail(this.id)
            const temp = res.data.data.host
            Object.assign(this.formData, temp)
            this.formData.active_time = temp.active_time ? moment(temp.active_time * 1000).format('YYYY-MM-DD HH:mm:ss') : ''
            this.formData.due_time = temp.due_time ? moment(temp.due_time * 1000).format('YYYY-MM-DD HH:mm:ss') : ''
            this.formData.server_id = temp.server_id === 0 ? '' : temp.server_id
            this.$forceUpdate()
            this.status = res.data.data.status.map((item, index) => {
              return { value: item, label: lang[item] }
            })
            this.curStatus = this.formData.status
          } catch (error) {
          }
        },
        // 续费
        async renew () {
          try {
            const res = await getSingleRenew(this.id)
            console.log(res)
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        }
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);