(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('host-detail')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    const host = location.origin
    const fir = location.pathname.split('/')[1]
    const str = `${host}/${fir}/`
    new Vue({
      data () {
        return {
          baseUrl: str,
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
          curStatus: '',
          promoList: [],
          recordColumns: [
            {
              colKey: 'create_time',
              title: lang.use_time
            },
            {
              colKey: 'scene',
              title: lang.use_cycle
            },
            {
              colKey: 'order_id',
              title: lang.order_number
            },
            {
              colKey: 'promo',
              title: lang.promo_code,
              width: 220
            },
          ],
          recordLoading: false,
          hasPlugin: false,
          tempCycle: '',
          /* 1-7 */
          moduleVisible: false,
          suspendVisible: false,
          optTilte: '',
          optType: '', // create unsuspend delete
          suspendType: [
            {
              value: 'overdue',
              label: lang.overdue
            },
            {
              value: 'overtraffic',
              label: lang.overtraffic
            },
            {
              value: 'certification_not_complete',
              label: lang.certification_not_complete
            },
            {
              value: 'other',
              label: lang.other
            }
          ],
          suspendForm: {
            suspend_type: 'overdue',
            suspend_reason: ''
          },
          moduleLoading: false,
          isShowModule: false,
          optBtns: [],
          clientDetail: {},
          searchLoading: false,
          clientList: [],
          clinetParams: {
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          hasTicket: false,
          authList: JSON.parse(
            JSON.stringify(localStorage.getItem("backAuth"))
          ),
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
        this.client_id = this.getQuery(query[0]) * 1
        this.formData.id = this.id = this.getQuery(query[1])
        this.langList = JSON.parse(localStorage.getItem('common_set')).lang_home
        this.getClintList()
        this.getProDetail()
        this.getproModule()
        this.getPlugin()

        const navList = JSON.parse(localStorage.getItem('backMenus'))
        let tempArr = navList.reduce((all, cur) => {
          cur.child && all.push(...cur.child)
          return all
        }, [])
        const curValue = tempArr.filter(item => item.url === 'client.html')[0]?.id
        localStorage.setItem('curValue', curValue)
        this.getBtns()
        this.getUserDetail()
      },
      computed: {
        disabled () {
          return this.formData.due_time === '' && this.formData.billing_cycle === 'onetime'
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
        changeUser (id) {
          this.id = id
          location.href = `client_host.html?client_id=${this.client_id}`
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
        }, // 远程搜素
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
            const res = await getClientDetail(this.client_id);
            this.clientDetail = res.data.data.client;
          } catch (error) { }
        },
        /* 1-31 */
        async getBtns () {
          try {
            const res = await getMoudleBtns({
              id: this.id
            })
            this.optBtns = res.data.data.button
          } catch (error) {

          }
        },
        /* 1-7 start */
        handlerMoudle (type) {
          this.optType = type
          switch (type) {
            case 'create':
              this.optTilte = lang.module_tip1;
              break;
            case 'unsuspend':
              this.optTilte = lang.module_tip2;
              break;
            case 'terminate':
              this.optTilte = lang.module_tip3;
              break;
            case 'suspend':
              this.optTilte = lang.module_tip4;
              this.handlerSuspend();
              break;
            case 'renew':
              this.renewDialog()
          }
          if (type !== 'renew' && type !== 'suspend') {
            this.moduleVisible = true
          }

        },
        confirmModule () {
          switch (this.optType) {
            case 'create':
              return this.createHandler();
            case 'unsuspend':
              return this.unsuspendHandler();
            case 'terminate':
              return this.deleteHandler()
          }
        },
        // 开通
        async createHandler () {
          try {
            this.moduleLoading = true
            const res = await createModule({
              id: this.id
            })
            this.$message.success(res.data.msg)
            this.getProDetail()
            this.getBtns()
            this.moduleLoading = false
            this.moduleVisible = false
          } catch (error) {
            this.moduleLoading = false
            this.moduleVisible = false
            this.$message.error(error.data.msg)
          }
        },
        // 取消停用
        async unsuspendHandler () {
          try {
            this.moduleLoading = true
            const res = await unsuspendModule({
              id: this.id
            })
            this.$message.success(res.data.msg)
            this.getProDetail()
            this.getBtns()
            this.moduleLoading = false
            this.moduleVisible = false
          } catch (error) {
            this.moduleLoading = false
            this.moduleVisible = false
            this.$message.error(error.data.msg)
          }
        },
        // 删除
        async deleteHandler () {
          try {
            this.moduleLoading = true
            const res = await delModule({
              id: this.id
            })
            this.$message.success(res.data.msg)
            this.getProDetail()
            this.getBtns()
            this.moduleLoading = false
            this.moduleVisible = false
          } catch (error) {
            this.moduleLoading = false
            this.moduleVisible = false
            this.$message.error(error.data.msg)
          }
        },
        // 暂停
        handlerSuspend () {
          this.suspendForm.suspend_type = 'overdue'
          this.suspendForm.suspend_reason = ''
          this.suspendVisible = true
        },
        // 提交停用
        async onSubmit () {
          try {
            this.moduleLoading = true
            const res = await suspendModule({
              id: this.id,
              suspend_type: this.suspendForm.suspend_type,
              suspend_reason: this.suspendForm.suspend_reason
            })
            this.$message.success(res.data.msg)
            this.getProDetail()
            this.getBtns()
            this.moduleLoading = false
            this.suspendVisible = false
          } catch (error) {
            this.moduleLoading = false
            this.$message.error(error.data.msg)
          }
        },
        /* 1-7 end */
        async getPlugin () {
          try {
            const res = await getAddon();
            const temp = res.data.data.list
              .reduce((all, cur) => {
                all.push(cur.name);
                return all;
              }, [])
            this.hasPlugin = temp.includes("PromoCode");
            this.hasTicket = temp.includes("IdcsmartTicket")
            this.hasPlugin && this.getPromoList()
          } catch (error) { }
        },
        // 获取优惠码使用记录
        async getPromoList () {
          try {
            const res = await proPromoRecord({ id: this.id })
            this.promoList = res.data.list
          } catch (error) {
            console.log(error)
          }
        },
        jumpOrder (row) {
          location.href = str + `order.html?order_id=${row.order_id}`
        },
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
            this.getProDetail()
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
            this.isShowModule = res.data.data.content ? true : false
            this.$nextTick(() => {
              $('.config-box .content').html(res.data.data.content)
            })
          } catch (error) {
          }
        },
        async getProList () {
          try {
            const res = await getProList()
            const temp = res.data.data.list
            // 产品id不在产品列表中
            let hasPro = temp.every(item => this.formData.product_id === item.id)
            if (!hasPro) {
              temp.unshift({
                id: this.formData.product_id,
                name: this.formData.product_name
              })
            }
            this.proList = temp
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
              this.getProDetail()
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          }).catch(err => {
            console.log(err)
          })
        },
        // 获取用户详情
        async getProDetail () {
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
            this.tempCycle = temp.billing_cycle
            this.$forceUpdate()
            this.status = res.data.data.status.map((item, index) => {
              return { value: item, label: lang[item] }
            })
            this.curStatus = this.formData.status
            document.title = lang.user_list + '-' + temp.product_name + '-' + localStorage.getItem('back_website_name')
            this.getProList()
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