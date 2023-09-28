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
      data() {
        return {
          urlPath: url,
          baseUrl: str,
          id: '',
          client_id: '',
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          hover: true,
          isLoading: false,
          diaTitle: '',
          isAgent: false,
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
            status: '',
            customfield: {},
            upstream_host_id: null
          },
          upData: {},
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
          cycleObj: {
            free: lang.free,
            onetime: lang.onetime,
            recurring_prepayment: lang.recurring_prepayment,
            recurring_postpaid: lang.recurring_postpaid
          },
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
          /* 新增下拉优化插件 */
          hasProPlugin: false,
          selectWay: '',
          visibleTree: false,
          productName: '',
          firstGroup: [],
          secondGroup: [],
          fir_pro: [], // 一级+商品
          second_pro: [], // 二级+商品
          fir_second_pro: [], // 一级+二级+商品
          isClick: false,
          clickExpand: [],
          hostFieldList: [],
          hostArr: [],
          tempHostId: null,
          // 手动资源
          hasResources: false,
          curResourcesId: '',
          resourceDialog: false,
          resourceList: [],
          resourceType: '', // allot free
          resourceTitle: '',
          resourceVisible: false,
          resourceId: '',
          resourcesColumns: [
            { colKey: 'dedicated_ip', title: 'IP', width: '120' },
            { colKey: 'power_status', title: lang.status },
            { colKey: 'configuration', title: lang.config, width: '200', ellipsis: true },
            { colKey: 'notes', title: lang.notes },
            // { colKey: 'ipmi', title: 'IPMI' },
            // { colKey: 'ipmi_auth', title: lang.ipmi_auth },
            { colKey: 'supplier', title: lang.manual_text10 },
            { colKey: 'cost', title: lang.manual_text11 },
            { colKey: 'user', title: lang.manual_text12, width: '200', ellipsis: true },
            { colKey: 'due_time', title: lang.due_time, width: '180' },
            { colKey: 'opt', title: lang.operation, width: '80' }
          ]
        }
      },
      watch: {
        'formData.type': {
          immediate: true,
          handler(val) {
            this.curList = val === 'server' ? this.serverList : this.serverGroupList
          }
        },
        serverList() {
          this.done = this.serverList.length === this.total
        },
        curId: {
          handler(val) {
            this.curRenew = this.renewList[val - 1]
          }
        }
      },
      created() {
        const query = this.getUrlParams()
        this.client_id = query.client_id
        this.formData.id = this.id = query.id
        this.langList = JSON.parse(localStorage.getItem('common_set')).lang_home
        this.getClintList()
        this.getProDetail()
        this.getproModule()
        this.getUpHostDetail()
        this.getPlugin()
        const navList = JSON.parse(localStorage.getItem('backMenus'))
        let tempArr = navList.reduce((all, cur) => {
          cur.child && all.push(...cur.child)
          return all
        }, [])
        const curValue = tempArr.filter(item => item.url === 'client.htm')[0]?.id
        localStorage.setItem('curValue', curValue)

        this.getBtns()
        this.getUserDetail()
        this.getHostList()
      },
      computed: {
        calcStatus() {
          return status => {
            switch (status) {
              case 'on':
                return lang.manual_text14
              case 'off':
                return lang.manual_text15
              case 'error':
                return lang.manual_text16
              default:
                return '--'
            }

          }
        },
        disabled() {
          return this.formData.due_time === '' && this.formData.billing_cycle === 'onetime'
        },
        calcShow() {
          return (data) => {
            return `#${data.id}-` + (data.username ? data.username : (data.phone ? data.phone : data.email)) + (data.company ? `(${data.company})` : '')
          }
        },
        isExist() {
          return !this.clientList.find(item => item.id === this.clientDetail.id)
        },
        /* 04-12 */
        calcProduct() {
          switch (this.selectWay) {
            case 'first_group':
              return this.fir_pro;
            case 'second_group':
              return this.second_pro;
            case 'first_second_group':
              return this.fir_second_pro;
          }
        },
        calcName() {
          return (id) => {
            return this.proList.filter(item => item.id === id)[0]?.name
          }
        },
        calcExpand() {
          const arr = this.proList.filter(item => item.id === this.formData.product_id)
          return ['f' + arr[0]?.product_group_id_first, 's' + arr[0]?.product_group_id_second]
        }
      },
      mounted() {
        document.addEventListener('click', () => {
          this.visibleTree = false
        })
        this.$nextTick(() => {
          document.getElementById(`myPopup`).onclick = () => {
            event.stopPropagation()
          }
        })
      },
      methods: {
        /* 手动资源 */
        async getResourcesList() {
          try {
            const res = await getManualResource({ page: 1, limit: 1000 })
            this.resourceList = res.data.data.list
            this.resourceList.forEach(item => {
               getResourceStatus({id: item.id}).then(result => {
                  item.power_status = result.data.data.status
               }).catch(err => {
                  item.power_status = err.data.data.status
               })
            })
          } catch (error) {

          }
        },
        handlerDistribute() {
          this.resourceDialog = true
        },
        optItem(row, type) {
          this.resourceId = row.id
          this.resourceType = type
          if (type === 'allot') {
            this.resourceTitle = lang.sure_allot_resource
          } else {
            this.resourceTitle = lang.sure_free_resource
          }
          this.resourceVisible = true
        },
        async handlerResource() {
          try {
            const res = await changeResource(this.resourceType, {
              id: this.resourceId,
              host_id: this.id
            })
            this.$message.success(res.data.msg)
            this.resourceVisible = false
            this.getResourcesList()
            this.getHostField()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        /* 手动资源 end */
        goBack() {
          const url = sessionStorage.currentHostUrl || ''
          sessionStorage.removeItem('currentHostUrl')
          if (url) {
            location.href = url
          } else {
            window.history.back()
          }
        },
        goClient() {
          sessionStorage.removeItem('hostListParams')
          sessionStorage.removeItem('currentHostUrl')
          location.href = 'client.htm'
        },
        changePro() {
          this.id = this.formData.id
          location.href = `host_detail.htm?client_id=${this.client_id}&id=${this.id}`
          // this.getProDetail()
          // this.getproModule()
          // this.getUpHostDetail()
        },
        async getHostList() {
          try {
            const res = await getClientPro(this.id, { page: 1, limit: 9999, client_id: this.client_id })
            this.hostArr = res.data.data.list
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        /* 产品内页模块输入框输出 */
        getHostField() {
          hostField(this.id).then((res) => {
            this.hostFieldList = res.data.data || []
            this.hasResources = this.hostFieldList[0]?.field.reduce((all, cur) => {
              all.push(cur.key)
              return all
            }, []).includes('manual_resource')
            if (this.hasResources) {
              this.curResourcesId = this.hostFieldList[0]?.field.filter(item => item.key === 'manual_resource')[0]?.value
              this.getResourcesList()
            }
          })
        },

        /* 新增下拉优化插件 */
        // 获取一级分组
        async getFirPro() {
          try {
            const res = await getFirstGroup()
            this.firstGroup = res.data.data.list.map(item => {
              item.key = 'f' + item.id
              return item
            })
            return this.firstGroup
          } catch (error) {
          }
        },
        // 获取二级分组
        async getSecPro() {
          try {
            const res = await getSecondGroup()
            this.secondGroup = res.data.data.list.map(item => {
              item.key = 's' + item.id
              return item
            })
            return this.secondGroup
          } catch (error) {
          }
        },
        // 初始化
        init() {
          try {
            this.loading = true
            // 获取商品，一级，二级分组
            Promise.all([this.getProList(), this.getFirPro(), this.getSecPro()]).then(res => {
              // 一级+商品
              if (this.selectWay === 'first_group') {
                this.fir_pro = this.firstGroup.map(item => {
                  item.children = []
                  item.children.push(...this.proList.filter(el => el.product_group_id_first === item.id))
                  return item
                }).filter(item => item.children.length > 0)
              } else if (this.selectWay === 'second_group') {
                // 二级+商品
                this.second_pro = this.secondGroup.map(item => {
                  item.children = []
                  item.children.push(...this.proList.filter(el => el.product_group_id_second === item.id))
                  return item
                }).filter(item => item.children.length > 0)
              } else if (this.selectWay === 'first_second_group') {
                // 一二级+商品
                const fArr = res[1].map(item => {
                  let secondArr = []
                  res[2].forEach(sItem => {
                    if (sItem.parent_id === item.id) {
                      secondArr.push(sItem)
                    }
                  })
                  item.children = secondArr
                  return item
                })
                setTimeout(() => {
                  this.fir_second_pro = fArr.map(item => {
                    item.children.map(ele => {
                      let temp = []
                      res[0].forEach(e => {
                        if (e.product_group_id_second === ele.id) {
                          temp.push(e)
                        }
                      })
                      ele.children = temp
                      return ele
                    })
                    return item
                  })
                }, 0)
              }
            })
          } catch (error) {
            console.log('@@@@', error)
            this.loading = false
          }
        },
        async getConfig() {
          try {
            const res = await getSelectConfig()
            this.selectWay = res.data.data.config
          } catch (error) {
          }
        },
        focusHandler() {
          this.visibleTree = true
          this.isClick = false
        },
        // 商品选择
        onClick(e) {
          if (!e.node.data.children) {
            const pName = e.node.data.name
            const pId = e.node.data.id
            this.productName = pName
            this.formData.product_id = pId
            this.visibleTree = false
          } else {
            this.isClick = true
            // if (Array.from(this.clickExpand).toString() == [e.node.value].toString()) {
            //   this.clickExpand = []
            // } else {
            //   if (this.selectWay === 'first_second_group' && Array.from(this.clickExpand).length < 2) {
            //     this.clickExpand.push(e.node.value)
            //   } else {
            //     this.clickExpand = [e.node.value]
            //   }

            // }
          }
        },
        getLabel(createElement, node) {
          const label = node.data.name
          const { data } = node
          data.label = label
          return label
        },
        /* 新增下拉优化插件 end*/
        changeUser(id) {
          this.id = id
          location.href = `client_host.htm?client_id=${this.client_id}`
        },
        async getUpHostDetail() {
          try {
            const res = await upHostDetail(this.id)
            this.upData = res.data.data.host.host
          } catch (error) {
            console.log(error.data.msg)
          }
        },
        async getClintList() {
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
        remoteMethod(key) {
          this.clinetParams.keywords = key
          this.getClintList()
        },
        filterMethod(search, option) {
          return option
        },
        // 获取用户详情
        async getUserDetail() {
          try {
            const res = await getClientDetail(this.client_id);
            this.clientDetail = res.data.data.client;
          } catch (error) { }
        },
        /* 1-31 */
        async getBtns() {
          try {
            const res = await getMoudleBtns({
              id: this.id
            })
            this.optBtns = res.data.data.button
          } catch (error) {

          }
        },
        /* 1-7 start */
        handlerMoudle(type) {
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
        confirmModule() {
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
        async createHandler() {
          try {
            this.moduleLoading = true
            const res = await createModule({
              id: this.id
            })
            this.$message.success(res.data.msg)
            this.getProDetail()
            this.getUpHostDetail()
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
        async unsuspendHandler() {
          try {
            this.moduleLoading = true
            const res = await unsuspendModule({
              id: this.id
            })
            this.$message.success(res.data.msg)
            this.getProDetail()
            this.getUpHostDetail()
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
        async deleteHandler() {
          try {
            this.moduleLoading = true
            const res = await delModule({
              id: this.id
            })
            this.$message.success(res.data.msg)
            this.getProDetail()
            this.getBtns()
            this.getUpHostDetail()

            this.moduleLoading = false
            this.moduleVisible = false
          } catch (error) {
            this.moduleLoading = false
            this.moduleVisible = false
            this.$message.error(error.data.msg)
          }
        },
        // 暂停
        handlerSuspend() {
          this.suspendForm.suspend_type = 'overdue'
          this.suspendForm.suspend_reason = ''
          this.suspendVisible = true
        },
        // 提交停用
        async onSubmit() {
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
            this.getUpHostDetail()

            this.moduleLoading = false
            this.suspendVisible = false
          } catch (error) {
            this.moduleLoading = false
            this.$message.error(error.data.msg)
          }
        },
        /* 1-7 end */
        async getPlugin() {
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
            this.hasProPlugin = temp.includes("ProductDropDownSelect");
            if (this.hasProPlugin) {
              this.getConfig()
            } else {
              this.selectWay = 'default'
            }
          } catch (error) { }
        },
        // 获取优惠码使用记录
        async getPromoList() {
          try {
            const res = await proPromoRecord({ id: this.id })
            this.promoList = res.data.list
          } catch (error) {
            console.log(error)
          }
        },
        jumpOrder(row) {
          location.href = str + `order.htm?order_id=${row.order_id}`
        },
        /* 跳转到订单/工单 */
        jumpToOrder() {
          location.href = str + `client_order.htm?id=${this.client_id}&host_id=${this.id}`
        },
        jumpToTicket() {
          location.href = str + `plugin/idcsmart_ticket/client_ticket.htm?id=${this.client_id}&host_id=${this.id}`
        },
        /* 续费 */
        renewDialog() {
          this.getRenewPage()
        },
        // 获取续费页面
        async getRenewPage() {
          try {
            const res = await getSingleRenew(this.formData.id)
            this.renewList = res.data.data.host.map((item, index) => {
              item.id = index + 1
              return item
            })
            if (this.renewList.length === 0) {
              return this.$message.warning(lang.renew_tip)
            }
            this.renewVisible = true
            this.curRenew = this.renewList[0]
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 向左移动
        subIndex() {
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
        addIndex() {
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
        checkCur(item) {
          this.curId = item.id
        },
        async submitRenew() {
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
            this.getUpHostDetail()

          } catch (error) {
            this.submitLoading = false
            this.$message.error(error.data.msg)
          }
        },
        back() {
          this.delVisible = true
        },
        // 删除
        deltePro(row) {
          this.delVisible = true
        },
        async onConfirm() {
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

        getUrlParams() {
          const url = window.location.href;
          // 判断是否有参数
          if (url.indexOf('?') === -1) {
            return {}
          }
          const params = url.split('?')[1];
          const paramsArr = params.split('&');
          const paramsObj = {};
          paramsArr.forEach((item) => {
            const key = item.split('=')[0];
            const value = item.split('=')[1];
            paramsObj[key] = value;
          })
          return paramsObj;
        },
        getQuery(val) {
          return val.split('=')[1]
        },
        checkTime(val) {
          if (moment(val).unix() > moment(this.formData.due_time).unix()) {
            return { result: false, message: lang.verify6, type: 'error' }
          }
          return { result: true }
        },
        checkTime1(val) {
          if (moment(val).unix() < moment(this.formData.active_time).unix()) {
            return { result: false, message: lang.verify6, type: 'error' }
          }
          return { result: true }
        },
        changeActive() {
          this.$refs.userInfo.validate({
            fields: ['active_time', 'due_time']
          });
        },
        async getproModule() {
          try {
            const res = await getproModule(this.id)
            this.isShowModule = res.data.data.content ? true : false
            this.$nextTick(() => {
              $('.config-box .content').html(res.data.data.content)
            })
          } catch (error) {
          }
        },
        async getProList() {
          try {
            const res = await getProList()
            const temp = res.data.data.list.map(item => {
              item.key = 't' + item.id
              return item
            })
            // 处理老财务迁移过后的数据：产品id不在产品列表中
            let hasPro = temp.some(item => this.formData.product_id === item.id)
            if (!hasPro) {
              temp.unshift({
                id: this.formData.product_id,
                name: this.formData.product_name
              })
            }
            this.proList = temp
            return this.proList
          } catch (error) {
          }
        },
        changeType(type) {
          this.formData.type = type
          this.formData.rel_id = ''
        },
        // 修改
        updateUserInfo() {
          this.$refs.userInfo.validate().then(async res => {
            if (res !== true) {
              this.$message.error(res.name[0].message)
              return
            }
            // 验证通过
            try {
              this.isLoading = true
              const params = { ...this.formData }
              params.due_time = (params.due_time === '') ? 0 : params.due_time
              params.active_time = (params.active_time === '') ? 0 : params.active_time
              if (params.active_time === 0) {
                params.active_time = moment(params.active_time * 1000).format('YYYY-MM-DD HH:mm:ss')
              }
              if (params.due_time === 0) {
                params.due_time = moment(params.due_time * 1000).format('YYYY-MM-DD HH:mm:ss')
              }
              // 修改前台
              const obj = {}
              const tempArr = JSON.parse(JSON.stringify(this.hostFieldList)).reduce((all, cur) => {
                all.push(...cur.field)
                return all
              }, [])
              tempArr.forEach((item) => {
                obj[item.key] = item.value
              })
              params.customfield.module_admin_field = obj
              // 修改前台结束
              const res = await updateProduct(this.id, params)
              this.$message.success(res.data.msg)
              this.getProDetail()
              this.getUpHostDetail()
              this.isLoading = false
            } catch (error) {
              this.isLoading = false
              this.$message.error(error.data.msg)
            }
          }).catch(err => {
            console.log(err)
          })
        },
        // 获取用户详情
        async getProDetail() {
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
            this.isAgent = res.data.data?.host.agent === 1
            Object.assign(this.formData, temp)
            this.formData.active_time = temp.active_time ? moment(temp.active_time * 1000).format('YYYY-MM-DD HH:mm:ss') : ''
            this.formData.due_time = temp.due_time ? moment(temp.due_time * 1000).format('YYYY-MM-DD HH:mm:ss') : ''
            this.formData.server_id = temp.server_id === 0 ? '' : temp.server_id
            this.tempCycle = temp.billing_cycle
            this.tempHostId = temp.upstream_host_id
            this.$forceUpdate()
            this.getHostField()
            this.status = res.data.data.status.map((item, index) => {
              return { value: item, label: lang[item] }
            })
            this.curStatus = this.formData.status
            document.title = lang.user_list + '-' + temp.product_name + '-' + localStorage.getItem('back_website_name')
            this.init()
          } catch (error) {
            console.log(error);
          }
        },
        // 续费
        async renew() {
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
