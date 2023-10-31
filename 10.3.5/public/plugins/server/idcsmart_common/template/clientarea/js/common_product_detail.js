const template = document.getElementsByClassName('common_product_detail')[0]
Vue.prototype.lang = window.lang
new Vue({
  components: {
    asideMenu,
    topMenu,
    pagination,
    payDialog,
    cashCoupon,
    discountCode,
    cashBack
  },
  created() {
    this.id = location.href.split('?')[1].split('=')[1]
    this.getCommonData()
    this.getDetail()
    this.getComDetail()
    // 获取退款信息
    // this.getRefundInfo()
    this.getCountryList()
    // this.getRenewStatus()
    this.getRenewPrice()
  },
  mounted() {
    this.addons_js_arr = JSON.parse(document.querySelector('#addons_js').getAttribute('addons_js')) // 插件列表
    const arr = this.addons_js_arr.map((item) => {
      return item.name
    })
    if (arr.includes('PromoCode')) {
      // 开启了优惠码插件
      this.isShowPromo = true
      this.getPromo()
    }
    if (arr.includes('IdcsmartClientLevel')) {
      // 开启了等级优惠
      this.isShowLevel = true
    }
    if (arr.includes('IdcsmartVoucher')) {
      // 开启了代金券
      this.isShowCash = true
    }
    arr.includes('IdcsmartRefund') && this.getRefundInfo()
    arr.includes('IdcsmartRenew') && this.getRenewStatus()


  },
  updated() {
    // 关闭loading
    // document.getElementById('mainLoading').style.display = 'none';
    // document.getElementsByClassName('common_product_detail')[0].style.display = 'block'
  },
  destroyed() {

  },
  data() {
    return {
      initLoading: true,
      baseUrl: url,
      id: '',
      isShowCash: false,
      product_id: '',
      renewLoading: false,
      params: {
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 200,
        orderby: 'id',
        sort: 'desc',
        keywords: '',
      },
      commonData: {},
      // 代金券对象
      cashObj: {},
      payWay: {
        free: lang.free,
        onetime: lang.onetime,
        recurring_prepayment: lang.recurring_prepayment,
        recurring_postpaid: lang.recurring_postpaid,
      },
      countryList: [],
      host: {}, // 基础信息
      configoptions: [], // 配置 
      status: {
        Unpaid: { text: lang.common_cloud_text88, color: "#F64E60", bgColor: "#FFE2E5" },
        Pending: { text: lang.common_cloud_text89, color: "#3699FF", bgColor: "#E1F0FF" },
        Active: { text: lang.common_cloud_text90, color: "#1BC5BD", bgColor: "#C9F7F5" },
        Suspended: { text: lang.common_cloud_text91, color: "#F0142F", bgColor: "#FFE2E5" },
        Deleted: { text: lang.common_cloud_text92, color: "#9696A3", bgColor: "#F2F2F7" },
        Failed: { text: lang.common_cloud_text93, color: "#FFA800", bgColor: "#FFF4DE" }
      },
      // 停用状态
      refundStatus: {
        Pending: lang.common_cloud_text234,
        Suspending: lang.common_cloud_text235,
        Suspend: lang.common_cloud_text236,
        Suspended: lang.common_cloud_text237,
        Refund: lang.common_cloud_text238,
        Reject: lang.common_cloud_text239,
        Cancelled: lang.common_cloud_text240
      },
      /* 停用相关 */
      isStop: false,
      noRefundVisible: false,
      refundVisible: false,
      refundInfo: {}, //商品停用信息
      refundForm: {
        str: '',
        arr: [],
        type: 'Expire' // Expire, Immediate
      },
      refundMoney: '0.00',
      refundDialog: {},
      // 续费
      renewActiveId: '0',
      // 显示续费弹窗
      isShowRenew: false,
      customfield: {},
      // 续费页面信息
      renewPageData: [],
      addons_js_arr: [], // 插件列表
      isShowPromo: false, // 是否开启优惠码
      isShowLevel: false, // 是否开启等级优惠
      isUseDiscountCode: false, // 是否使用优惠码
      // 续费参数
      renewParams: {
        id: 0,
        duration: '', // 周期
        billing_cycle: '', // 周期时间
        clDiscount: 0, // 用户等级折扣价
        code_discount: 0, // 优惠码折扣价
        cash_discount: 0, // 代金券折扣价格
        original_price: 0,// 售卖价格
        base_price: 0, // 原价
        totalPrice: 0 // 应支付价格
      },
      /* 备注 */
      isShowNotesDialog: false,
      host: {},
      hostData: {},
      notesValue: '',
      promo_code: [],
      loading: false,
      // 自动续费
      isShowPayMsg: 0,
      autoTitle: '',
      dialogVisible: false,
      /* 升降级 */
      upgradeLoading: false,
      upLicenseDialogShow: false,
      selectUpIndex: 0,
      buy_id: '',
      upPriceLoading: false,
      licenseActive: '1',
      upData: {
        price: 0,
        clDiscount: 0,
        totalPrice: 0,
        code_discount: 0
      },
      isShowUp: true,
      upBtnLoading: false,
      upgradeHost: {},
      upgradeConfig: [],
      upgradeSon_host: [],
      upgradeList: [],
      basicInfo: {},
      configForm: {},
      upSon: [],
      curCycle: 0,
      curCountry: {},
      firstInfo: [],
      renewPriceList: [],
      filterCountry: {}
      // filterCountry: [],
      /* 升降级 end */
    }
  },
  mixins: [mixin],
  filters: {
    formateTime(time) {
      if (time && time !== 0) {
        return formateDate(time * 1000)
      } else {
        return "--"
      }
    },
    filterMoney(money) {
      if (isNaN(money)) {
        return '0.00'
      } else {
        const temp = `${money}`.split('.')
        return parseInt(temp[0]).toLocaleString() + '.' + (temp[1] || '00')
      }
    }
  },
  computed: {
    // filterCountry () {
    //   return (country) => {
    //     const name = this.countryList.filter(item => item.iso === country)
    //     return name[0]?.name_zh
    //   }
    // },
    calcSwitch() {
      return (item, type) => {
        if (type) {
          const arr = item.subs.filter(item => item.option_name === lang.com_config.yes)
          return arr[0]?.id
        } else {
          const arr = item.subs.filter(item => item.option_name === lang.com_config.no)
          return arr[0]?.id
        }
      }
    },
    calcCountry() {
      return (val) => {
        return this.countryList.filter(item => val === item.iso)[0]?.name_zh
      }
    },
    calcCity() {
      return (id) => {
        return this.filterCountry[id].filter(item => item[0]?.country === this.curCountry[id])[0]
      }
    },
    showRenewPrice() {
      let p = this.hostData.renew_amount
      this.renewPriceList.forEach(item => {
        if (item.billing_cycle === this.hostData.billing_cycle_name && this.hostData.renew_amount * 1 < item.price * 1) {
          p = item.price * 1
        }
      })
      return p
    }
  },
  watch: {
    renewParams: {
      handler() {
        let n = 0
        // l:当前周期的续费价格
        const l = this.hostData.renew_amount
        if (this.isShowPromo && this.customfield.promo_code) {
          // n: 算出来的价格
          n = (this.renewParams.base_price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000 > 0 ? (this.renewParams.base_price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000 : 0
        } else {
          //  n: 算出来的价格
          n = (this.renewParams.original_price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000 > 0 ? (this.renewParams.original_price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000 : 0
        }
        let t = n
        // 如果当前周期和选择的周期相同，则和当前周期对比价格
        if (this.hostData.billing_cycle_time === this.renewParams.duration || this.hostData.billing_cycle_name === this.renewParams.billing_cycle) {
          console.log(n > l);
          // 谁大取谁
          t = n
        }
        this.renewParams.totalPrice = t * 1000 - this.renewParams.cash_discount * 1000 > 0 ? ((t * 1000 - this.renewParams.cash_discount * 1000) / 1000).toFixed(2) : 0
      },
      immediate: true,
      deep: true
    }
  },
  methods: {
    /* 升降级 */
    handelUpLicense(val) {
      if (this.upgradeLoading) return
      if (val !== 'isUpApp') {
        this.buy_id = ''
        this.buy_host_id = ''
      }
      this.upgradeLoading = true
      this.licenseActive = '1'
      this.selectUpIndex = 0
      this.$message({
        showClose: true,
        message: lang.common_cloud_text54,
        type: 'warning',
        duration: 10000
      });
      this.handleTabClick({ name: '1' })
      this.curCycle = 0
    },
    handleTabClick(e) {
      this.selectUpIndex = 0
      const upApi = this.buy_id ? upAppPage : upgradePage
      const configApi = this.buy_id ? upgradeAppPage : upgradeConfigPage
      const id = this.buy_id ? this.buy_host_id : this.id
      if (e.name === '1') { // 产品升降级
        this.isShowUp = true
        upApi(id).then(res => {
          this.upgradeList = res.data.data.upgrade
          if (res.data.data.upgrade.length === 0) {
            this.isShowUp = false
            this.licenseActive = '2'
            this.handleTabClick({ name: '2' })
            return
          }
          this.upgradeHost = res.data.data.host
          this.upgradeConfig = res.data.data.configoptions
          this.upgradeSon_host = res.data.data.son_host
          this.upgradeLoading = false
          this.getConfig()
          this.upLicenseDialogShow = true
        }).catch((err) => {
          this.$message.warning(err.data && err.data.msg)
          this.upgradeLoading = false
        })
      } else { // 配置升降级
        configApi(id).then(res => {
          this.upgradeList = res.data.data.upgrade_configoptions
          this.upgradeHost = res.data.data.host
          this.upgradeConfig = res.data.data.configoptions
          this.upgradeSon_host = res.data.data.son_host
          this.upgradeLoading = false
          this.getConfig()
          this.upLicenseDialogShow = true
        }).catch((err) => {
          this.$message.warning(err.data && err.data.msg)
          this.upgradeLoading = false
        })
      }
    },
    // 更改授权数量拖动
    changeQuantity(val, i) {
      if (i.son_product_id > 0) {
        return
      }
      let num1 = val * 1
      let step = i.subs[0]?.qty_change || 1
      if (num1 % step !== 0) {
        num1 = parseInt(num1 / step) * step
      }
      this.configForm[i.id] = [num1]
      const fatherArr = this.configoptions.filter((item) => {
        if (item.son_product_id === 0 && (item.option_type === 'quantity_range' || item.option_type === 'quantity')) {
          return item
        }
      })
      let num = 0
      const fatherId = fatherArr.map((item) => {
        return item.id
      })
      fatherId.forEach((item) => {
        num = this.configForm[item][0] ? this.configForm[item][0] * 1 + num : this.configForm[item] * 1 + num
      })
      const arr = this.configoptions.filter(item => item.son_product_id > 0)
      const arr1 = arr.map((item) => {
        return item.id
      })

      arr1.forEach((item) => {
        this.configForm[item] = [num]
      })
      this.changeConfig()
    },
    qtyChangeNum(val, item) {
      let num1 = val * 1
      let step = item.subs[0]?.qty_change || 1
      if (num1 % step !== 0) {
        num1 = parseInt(num1 / step) * step
      }
      this.configForm[item.id] = [num1]
      const fatherArr = this.configoptions.filter((item) => {
        if (item.son_product_id === 0 && (item.option_type === 'quantity_range' || item.option_type === 'quantity')) {
          return item
        }
      })
      let num = 0
      const fatherId = fatherArr.map((item) => {
        return item.id
      })
      fatherId.forEach((item) => {
        num = this.configForm[item][0] ? this.configForm[item][0] * 1 + num : this.configForm[item] * 1 + num
      })
      const arr = this.configoptions.filter(item => item.son_product_id > 0)
      const arr1 = arr.map((item) => {
        return item.id
      })
      arr1.forEach((item) => {
        this.configForm[item] = [num]
      })
      setTimeout(() => {
        this.changeConfig()
      }, 300)
    },
    // 切换数量
    changeNum(val, item) {
      let num1 = val.target.value * 1
      let step = item.subs[0]?.qty_change || 1
      if (num1 % step !== 0) {
        num1 = parseInt(num1 / step) * step
      }
      this.configForm[item.id] = [num1]
      const fatherArr = this.configoptions.filter((item) => {
        if ((item.option_type === 'quantity_range' || item.option_type === 'quantity')) {
          return item
        }
      })
      let num = 0
      const fatherId = fatherArr.map((item) => {
        return item.id
      })
      fatherId.forEach((item) => {
        num = this.configForm[item][0] ? this.configForm[item][0] * 1 + num : this.configForm[item] * 1 + num
      })
      let arr = []
      this.upSon.forEach((item) => {
        arr = item.configoptions.filter((items) => {
          if (item.basicInfo.configoption_id > 0 && (items.option_type === 'quantity_range' || items.option_type === 'quantity')) {
            return item
          }
        })
      })
      const arr1 = arr.map((item) => {
        return item.id
      })
      arr1.forEach((item) => {
        this.sonConfigForm[0][item] = [num]
      })
      setTimeout(() => {
        if (this.upLicenseDialogShow) {
          this.changeConfig()
        } else {
          this.changeSonConfig()
        }
      }, 300)
    },
    // 切换子商品数量
    changeSonNum(val, item) {
      let num = val * 1
      let step = item.subs[0]?.qty_change || 1
      if (num % step !== 0) {
        num = parseInt(num / step) * step
      }
      this.sonConfigForm[item.id] = [num]
      setTimeout(() => {
        if (this.upLicenseDialogShow) {
          this.changeConfig()
        } else {
          this.changeSonConfig()
        }
      }, 300)
    },
    // 切换国家
    changeCountry(id, index) {
      this.$set(this.curCountry, id, index)
      this.configForm[id] = this.filterCountry[id][index][0]?.id
      this.changeConfig()
    },
    // 切换城市
    changeCity(el, id) {
      this.configForm[id] = el.id
      this.changeConfig()
    },
    // 切换单击选择
    changeClick(id, el) {
      this.configForm[id] = el.id
      if (this.upLicenseDialogShow) {
        this.changeConfig()
      } else {
        this.changeSonConfig()
      }
    },
    // 父商品数据输入
    fatherChange(val, i) {
      let inputNum = val * 1
      if (i.subs && i.subs[0]) {
        let step = i.subs[0]?.qty_change || 1
        if (inputNum % step !== 0) {
          inputNum = parseInt(inputNum / step) * step
        }
        this.configForm[i.id] = [inputNum]
      }
      const fatherArr = this.configoptions.filter((item) => {
        if ((item.option_type === 'quantity_range' || item.option_type === 'quantity')) {
          return item
        }
      })
      let num = 0
      const fatherId = fatherArr.map((item) => {
        return item.id
      })
      fatherId.forEach((item) => {
        num = this.configForm[item][0] ? this.configForm[item][0] * 1 + num : this.configForm[item] * 1 + num
      })
      let arr = []
      this.upSon.forEach((item) => {
        arr = item.configoptions.filter((items) => {
          if (item.basicInfo.configoption_id > 0 && (items.option_type === 'quantity_range' || items.option_type === 'quantity')) {
            return item
          }
        })
      })
      const arr1 = arr.map((item) => {
        return item.id
      })
      arr1.forEach((item) => {
        this.sonConfigForm[0][item] = [num]
      })
      this.changeConfig()
    },
    // 切换配置选项
    changeItem() {
      if (this.upLicenseDialogShow) {
        this.changeConfig()
      } else {
        this.changeSonConfig()
      }
    },
    async getConfig() {
      this.upSon = []
      this.buySonData = []
      this.sonCurCycle = []
      this.sonCountry = []
      this.sonConfigForm = []
      this.sonCycle = []
      this.sonCurCountry = []
      try {
        const tabVal = this.licenseActive
        if (tabVal === '1') {
          const temp = this.upgradeList[this.selectUpIndex * 1]
          this.basicInfo = temp.common_product
          this.configoptions = temp.configoptions.filter(item => item.subs.length)
          this.custom_cycles = temp.custom_cycles
          this.pay_type = temp.common_product?.pay_type
          this.onetime = temp.cycles?.onetime === '-1.00' ? '0.00' : temp.cycles.onetime
          // 初始化自定义配置参数
          const obj = this.configoptions.reduce((all, cur) => {
            all[cur.id] =
              (cur.option_type === 'multi_select'
                || cur.option_type === 'quantity'
                || cur.option_type === 'quantity_range')
                ? [cur.option_type === 'multi_select' ? cur.subs[0].id : cur.subs[0].qty_min]
                : cur.subs[0].id
            // 区域的时候保存国家
            if (cur.option_type === 'area') {
              this.filterCountry[cur.id] = this.toTree(cur.subs)
              this.$set(this.curCountry, cur.id, 0)
            }
            return all
          }, {})
          this.configForm = obj
          // 处理费用周期
          if (this.pay_type === 'onetime') {
            this.cycle = 'onetime'
          } else if (this.pay_type === 'free') {
            this.cycle = 'free'
          } else {
            this.cycle = temp.custom_cycles[0].id
          }
          /* 处理子商品 */
          this.originSon = temp.son
          this.originSon && temp.son.forEach((item, index) => {
            // 左侧展示的数据
            // 默认选中的周期
            this.sonCurCycle.push(0)
            this.upSon.push({
              open: true,
              basicInfo: item.common_product,
              configoptions: item.configoptions.filter(el => el.subs.length),
              custom_cycles: item.custom_cycles,
              pay_type: item.common_product.pay_type,
              onetime: item.cycles.onetime === '-1.00' ? '0.00' : item.cycles.onetime
            })
            // 初始化自定义配置参数
            const obj = item.configoptions.filter(el => el.subs.length).reduce((all, cur) => {
              all[cur.id] = (cur.option_type === 'multi_select' || cur.option_type === 'quantity' || cur.option_type === 'quantity_range') ? [cur.option_type === 'multi_select' ? cur.subs[0].id : cur.subs[0].qty_min] : cur.subs[0].id
              // 区域的时候保存国家
              if (cur.option_type === 'area') {
                this.sonCountry.push({ [cur.id]: this.toTree(cur.subs) })
                this.sonCurCountry.push({ [cur.id]: 0 })
              }
              return all
            }, {})
            this.sonConfigForm.push(obj)
            // 处理费用周期
            let sonC = ''
            if (item.common_product.pay_type === 'onetime') {
              sonC = 'onetime'
            } else if (item.common_product.pay_type === 'free') {
              sonC = 'free'
            } else {
              sonC = item.custom_cycles[0].id
            }
            this.sonCycle.push(sonC)
          })
        } else {
          const temp = JSON.parse(JSON.stringify(this.upgradeList))
          this.configoptions = temp
          // 初始化自定义配置参数
          const obj = this.configoptions.reduce((all, cur) => {
            if (cur.option_type === 'multi_select') {
              const mulArr = this.upgradeConfig.reduce((sum, c) => {
                if (c.id === cur.id) {
                  sum.push(c.configoption_sub_id)
                }
                return sum
              }, [])
              all[cur.id] = mulArr
            } else if (cur.option_type === 'quantity') {
              all[cur.id] = this.backfillId('quantity', cur.id)
            } else {
              all[cur.id] = cur.option_type === 'quantity_range' ? this.backfillId('quantity_range', cur.id) : this.backfillId('id', cur.id)
            }
            // 区域的时候保存国家
            if (cur.option_type === 'area') {
              this.filterCountry[cur.id] = this.toTree(cur.subs)
              const curItem = this.upgradeConfig.filter(item => item.id === cur.id)
              let index = this.filterCountry[cur.id].findIndex(item => item.reduce((sumC, cc) => {
                sumC.push(cc.id)
                return sumC
              }, []).includes(curItem[0]?.configoption_sub_id * 1)
              )
              this.$set(this.curCountry, cur.id, index)
            }
            return all
          }, {})
          this.backups = JSON.parse(JSON.stringify(obj))
          this.configForm = obj
        }
        this.changeConfig()
      } catch (error) {
        console.log('error', error)
      }
    },
    // 回填处理id
    backfillId(type, id) {
      const temp = this.upgradeConfig.filter(item => item.id === id)
      if (type === 'id') {
        return temp[0]?.configoption_sub_id
      } else if (type === 'quantity_range') {
        return [temp[0]?.qty]
      } else {
        return temp[0]?.qty
      }
    },
    // 数组转树
    toTree(data) {
      var temp = Object.values(data.reduce((res, item) => {
        res[item.country] ? res[item.country].push(item) : res[item.country] = [item]
        return res
      }, {}))
      return temp
    },
    goPay() {
      if (this.hostData.status === 'Unpaid') {
        this.$refs.payDialog.showPayDialog(this.hostData.order_id)
      }
    },
    // 切换周期
    changeCycle(item, index) {
      this.cycle = item.id
      this.curCycle = index

      if (this.basicInfo.pay_type === 'recurring_prepayment' || this.basicInfo.pay_type === 'recurring_postpaid') {
        this.upSon.forEach(el => {
          this.sonCycle = []
          this.sonCurCycle = []
          this.sonCycle.push(el.custom_cycles[index].id)
          this.sonCurCycle.push(index)
        })
      }
      this.changeConfig()
    },
    // 更改配置计算价格
    async changeConfig() {
      const tabVal = this.licenseActive
      this.upPriceLoading = true
      try {
        let res = {}
        const temp = this.formatData()
        const sonParams = []
        if (tabVal === '1') {
          // 配置子商品的参数
          this.upSon.forEach((item, index) => {
            sonParams.push({
              config_options: {
                configoption: this.upFormatSubData(this.sonConfigForm[index], index),
                cycle: this.sonCycle[index],
              },
              id: this.originSon[index].configoptions[0].product_id,
              qty: 1,
              buy: item.open
            })
          })
          const params = { configoption: temp, cycle: this.cycle, son: sonParams, product_id: this.upgradeList[this.selectUpIndex * 1]?.configoptions[0].product_id }
          res = this.buy_id ? await upAppPrice(this.buy_host_id, params) : await upgradePrice(this.id, params)
          this.upData.price = res.data.data.upgrade_price // 原单价
          // 重新计算周期显示
          const calculateParams = { config_options: { configoption: { ...temp }, son: sonParams, cycle: this.cycle, host_id: this.buy_id ? this.buy_host_id : this.id }, qty: 1, id: this.upgradeList[this.selectUpIndex * 1]?.configoptions[0].product_id, }
          const result = this.buy_id ? await buyCalculate(calculateParams) : await calculate(calculateParams)
          this.custom_cycles = result.data.data.custom_cycles
          this.onetime = result.data.data.cycles.onetime
          // 重新计算周期价格显示
          result.data.data.son || [].forEach((el, ind) => {
            this.$set(this.upSon[ind], 'custom_cycles', el.custom_cycles)
            this.$set(this.upSon[ind], 'onetime', el.cycles.onetime)
          })
        } else {
          const temp1 = this.formatData()
          const params = { configoption: temp1, buy: this.isBuyServe }
          res = this.buy_id ? await syncAppPrice(this.buy_host_id, params) : await syncUpgradePrice(this.id, params)
          this.upData.price = res.data.data.price          // 原单价
        }
        if (this.isShowLevel) {
          // 计算折扣金额
          const discount = await clientLevelAmount({
            id: tabVal === '1' ? this.upgradeList[this.selectUpIndex * 1]?.configoptions[0].product_id : this.product_id,
            amount: this.upData.price
          })
          this.upData.clDiscount = Number(discount.data.data.discount)
        }
        // 开启了优惠码插件
        if (this.isShowPromo) {
          // 更新优惠码
          await applyPromoCode({
            // 开启了优惠券
            scene: "upgrade",
            product_id: tabVal === '1' ? this.upgradeList[this.selectUpIndex * 1]?.configoptions[0].product_id : this.product_id,
            amount: this.upData.price,
            billing_cycle_time: this.host.billing_cycle_time,
            promo_code: '',
            host_id: this.id,
          }).then((resss) => {
            this.upData.code_discount = Number(
              resss.data.data.discount
            );
          }).catch((err) => {
            this.upData.code_discount = 0;
          });
        }
        this.upData.totalPrice = (this.upData.price * 1000 - this.upData.clDiscount * 1000 - this.upData.code_discount * 1000) / 1000
        this.upPriceLoading = false
      }
      catch (error) {
        console.log('error11111', error);
        this.upPriceLoading = false
        this.dataLoading = false
      }
    },
    formatData() {
      // 处理数量类型的转为数组
      const temp = JSON.parse(JSON.stringify(this.configForm))
      Object.keys(temp).forEach(el => {
        const arr = this.configoptions.filter(item => item.id * 1 === el * 1)
        if (arr.length !== 0) {
          if (arr[0].option_type === 'quantity'
            || arr[0].option_type === 'quantity_range'
            || arr[0].option_type === 'multi_select') {
            if (typeof (temp[el]) !== 'object') {
              temp[el] = [temp[el]]
            }
          }
        }
      })
      return temp
    },
    // 点击可升级授权
    selectUpItem(index) {
      this.selectUpIndex = index
      this.curCycle = 0
      this.getConfig()
    },
    // 提交升级
    handelUpConfirm() {
      if (this.upBtnLoading) return
      // this.upBtnLoading = true
      if (this.licenseActive === '1') {
        const temp = this.formatData()
        // 配置子商品的参数
        const sonParams = []
        this.upSon.forEach((item, index) => {
          sonParams.push({
            config_options: {
              configoption: this.upFormatSubData(this.sonConfigForm[index], index),
              cycle: this.sonCycle[index],
            },
            id: this.originSon[index].configoptions[0].product_id,
            qty: 1,
            buy: item.open
          })
        })
        // 配置子商品的参数
        const params = {
          id: this.id,
          product_id: this.upgradeList[this.selectUpIndex * 1]?.configoptions[0].product_id,
          config_options: {
            configoption: temp,
            cycle: this.cycle,
            son: sonParams
          },
          qty: 1,
          customfield: {}
        }
        const upHostApi = this.buy_id ? upAppHost : upgradeHost
        const id = this.buy_id ? this.buy_host_id : this.id
        upHostApi(id, params).then((res) => {
          this.$refs.payDialog.showPayDialog(res.data.data.id)
        }).catch((err) => {
          this.$message.error(err.data.msg)
        }).finally(() => {
          this.upBtnLoading = false
          this.upLicenseDialogShow = false
        })
      } else {
        // const obj = {}
        // this.upgradeConfig.forEach((item) => { // 原始数量对象
        //   if (item.option_type === 'quantity_range') {
        //     obj[item.id] = [item.qty]
        //   }
        //   if (item.option_type === 'quantity') {
        //     obj[item.id] = item.qty
        //   }
        // })

        // const obj = this.configoptions.reduce((all, cur) => {
        //   if (cur.option_type === 'multi_select') {
        //     const mulArr = this.upgradeConfig.reduce((sum, c) => {
        //       if (c.id === cur.id) {
        //         sum.push(c.configoption_sub_id)
        //       }
        //       return sum
        //     }, [])
        //     all[cur.id] = mulArr
        //   } else {
        //     all[cur.id] = (
        //       cur.option_type === 'quantity' ||
        //       cur.option_type === 'quantity_range'
        //     ) ? this.backfillId('num', cur.id) : this.backfillId('id', cur.id)
        //   }
        //   // 区域的时候保存国家
        //   if (cur.option_type === 'area') {
        //     this.filterCountry[cur.id] = this.toTree(cur.subs)
        //     const curItem = this.upgradeConfig.filter(item => item.id === cur.id)
        //     let index = this.filterCountry[cur.id].findIndex(item => item.reduce((sumC, cc) => {
        //       sumC.push(cc.id)
        //       return sumC
        //     }, []).includes(curItem[0]?.configoption_sub_id * 1)
        //     )
        //     this.$set(this.curCountry, cur.id, index)
        //   }
        //   return all
        // }, {})
        if (this.isEquivalent(this.backups, this.configForm)) {
          this.$message.error(lang.common_cloud_text241)
          this.upBtnLoading = false
          return
        }
        const temp1 = this.formatData()
        params = { configoption: temp1, buy: this.isBuyServe }
        const upConfigApi = this.buy_id ? upgradeAppHost : upgradeConfigHost
        const id = this.buy_id ? this.buy_host_id : this.id
        upConfigApi(id, params).then((res) => {
          this.$refs.payDialog.showPayDialog(res.data.data.id)
        }).catch((err) => {
          this.$message.error(err.data.msg)
        }).finally(() => {
          this.upBtnLoading = false
          this.upLicenseDialogShow = false
        })
      }

    },
    // 比较对象是否相等
    isEquivalent(a, b) { // a:已有配置  b:当前配置
      // 获取a和b对象的属性名数组
      const aProps = Object.getOwnPropertyNames(a);
      // 遍历对象的每个属性并进行比较
      for (let i = 0; i < aProps.length; i++) {
        const propName = aProps[i];
        // 如果属性值为对象，则递归调用该函数进行比较
        if (typeof a[propName] === 'object') {
          if (!this.isEquivalent(a[propName], b[propName])) {
            return false;
          }
        } else {
          if (b.hasOwnProperty(propName)) {
            // 否则，直接比较属性值
            if (a[propName] !== b[propName]) {
              return false;
            }
          }
        }

      }
      // 如果遍历完成则说明两个对象内容相同
      return true;
    },
    /* 升降级 end */
    changeAutoStatus(e) {
      this.dialogVisible = true
      this.autoTitle = this.isShowPayMsg ? lang.common_cloud_text242 : lang.common_cloud_text243
    },
    async changeAuto() {
      try {
        const params = {
          id: this.id,
          status: this.isShowPayMsg ? 0 : 1
        }
        const res = await rennewAuto(params)
        this.$message.success(res.data.msg)
        this.dialogVisible = false
        this.getRenewStatus()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    async getRenewStatus() {
      try {
        const res = await renewStatus({
          id: this.id
        })
        this.isShowPayMsg = res.data.data.status
      } catch (error) {

      }
    },
    async getPromo() {
      try {
        const res = await getPromoCode(this.id)
        this.promo_code = res.data.data.promo_code
      } catch (error) {

      }
    },
    /* 备注 */
    async getComDetail() {
      try {
        const res = await getCommonDetail(this.id)
        this.hostData = res.data.data.host
        this.product_id = res.data.data.host.product_id
      } catch (error) {

      }
    },
    // 显示 修改备注 弹窗
    doEditNotes() {
      this.isShowNotesDialog = true
      this.notesValue = this.hostData.notes
    },
    // 修改备注提交
    async subNotes() {
      const params = {
        id: this.id,
        notes: this.notesValue
      }
      try {
        const res = await changeNotes(params)
        this.$message.success(res.data.msg)
        this.isShowNotesDialog = false
        this.getComDetail()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    notesDgClose() {
      this.isShowNotesDialog = false
    },
    // 获取退款信息
    async getRefundInfo() {
      try {
        const res = await getRefundInfo(this.id)
        this.refundInfo = res.data.data.refund
      } catch (error) {
      }
    },
    /* 停用 */
    async stop_use() {
      this.refundForm.str = ''
      this.refundForm.arr = []
      this.refundForm.type = 'Expire'
      this.refundMoney = '0.00'
      try {
        const res = await getRefund(this.id)
        this.refundDialog = res.data.data
        // if (!this.refundDialog.allow_refund) {
        //   this.noRefundVisible = true
        //   return false
        // }
        this.refundVisible = true
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    changeReson(e) {
      this.refundMoney = (e === 'Immediate') ? this.refundDialog.host.amount : '0.00'
    },
    async submitRefund() {
      try {
        if (this.refundDialog.reason_custom) { // 自定义
          if (!this.refundForm.str) {
            return this.$message.error(lang.common_cloud_label44)
          }
        } else {
          if (this.refundForm.arr.length === 0) {
            return this.$message.error(lang.common_cloud_text58)
          }
        }
        const params = {
          host_id: this.id,
          type: this.refundForm.type,
          suspend_reason: this.refundDialog.reason_custom ? this.refundForm.str : this.refundForm.arr
        }
        this.loading = true
        const res = await submitRefund(params)
        this.loading = false
        this.$message.success(lang.common_cloud_text60)
        this.refundVisible = false
        this.getRefundInfo()
      } catch (error) {
        this.loading = false
        this.$message.error(error.data.msg)
      }
    },

    // 取消停用
    async cancelRefund() {
      try {
        const res = await cancelRefund({ id: this.refundInfo.id })
        this.$message.success(lang.common_cloud_text220)
        this.getRefundInfo()
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },

    async getCountryList() {
      try {
        const res = await getCountry()
        this.countryList = res.data.data.list
      } catch (error) {
      }
    },
    async getDetail() {
      try {
        const res = await getCommonListDetail(this.id)
        this.host = res.data.data.host
        const temp = res.data.data.configoptions.map(item => {
          item.show = false
          return item
        })
        this.firstInfo = temp
        setTimeout(() => {
          this.initLoading = false
        }, 300)
      } catch (error) {

      }
    },
    textRange(el) {
      const targetw = el.getBoundingClientRect().width
      const range = document.createRange()
      range.setStart(el, 0)
      range.setEnd(el, el.childNodes.length)
      const rangeWidth = range.getBoundingClientRect().width
      return rangeWidth > targetw
    },
    checkWidth(e, index) {
      const bol = this.textRange(e.target)
      this.firstInfo[index].show = bol
    },
    hideTip(index) {
      this.firstInfo[index].show = false
    },
    back() {
      window.history.back();
    },
    // 每页展示数改变
    sizeChange(e) {
      this.params.limit = e
      this.params.page = 1
      // 获取列表
    },
    // 当前页改变
    currentChange(e) {
      this.params.page = e

    },

    // 获取通用配置
    getCommonData() {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
      document.title = this.commonData.website_name + '-' + lang.common_cloud_text221
    },
    // 使用优惠码
    async getDiscount(data) {
      this.customfield.promo_code = data[1]
      this.isUseDiscountCode = true
      this.renewParams.code_discount = Number(data[0])
      const price = this.renewParams.base_price
      const discountParams = { id: this.product_id, amount: price }
      // // 开启了等级折扣插件
      if (this.isShowLevel) {
        // 获取等级抵扣价格
        await clientLevelAmount(discountParams).then(res2 => {
          if (res2.data.status === 200) {
            this.renewParams.clDiscount = Number(res2.data.data.discount) // 客户等级优惠金额
          }
        }).catch(error => {
          this.renewParams.clDiscount = 0
        })
      }
    },
    removeDiscountCode() {
      this.isUseDiscountCode = false
      this.customfield.promo_code = ''
      this.renewParams.code_discount = 0
      this.renewParams.clDiscount = 0
    },
    // 显示续费弹窗
    showRenew() {
      // 获取续费页面信息
      const params = {
        id: this.id,
      }
      this.isShowRenew = true
      this.renewLoading = true
      renewPage(params).then(async (res) => {
        if (res.data.status === 200) {
          this.renewPageData = res.data.data.host
          this.renewActiveId = 0
          this.renewParams.billing_cycle = this.renewPageData[0].billing_cycle
          this.renewParams.duration = this.renewPageData[0].duration
          this.renewParams.original_price = this.renewPageData[0].price
          this.renewParams.base_price = this.renewPageData[0].base_price
          this.renewParams.totalPrice = this.renewPageData[0].price
          this.renewLoading = false
        }
      }).catch(err => {
        this.renewLoading = false
        this.$message.error(err.data.msg)
      })

    },
    // 续费使用代金券
    reUseCash(val) {
      this.cashObj = val
      const price = val.price ? Number(val.price) : 0
      this.renewParams.cash_discount = price
      this.customfield.voucher_get_id = val.id
    },
    // 续费移除代金券
    reRemoveCashCode() {
      this.$refs.cashRef.closePopver()
      this.cashObj = {}
      this.renewParams.cash_discount = 0
      this.customfield.voucher_get_id = ''
    },
    // 续费弹窗关闭
    renewDgClose() {
      this.isShowRenew = false
      this.removeDiscountCode()
      this.reRemoveCashCode()
    },
    getRenewPrice() {
      renewPage({ id: this.id, }).then(async (res) => {
        if (res.data.status === 200) {
          this.renewPriceList = res.data.data.host
        }
      }).catch(err => {
        this.renewPriceList = []
      })
    },
    // 续费提交
    subRenew() {
      const params = {
        id: this.id,
        billing_cycle: this.renewParams.billing_cycle,
        customfield: this.customfield
      }
      this.loading = true
      renew(params).then(res => {
        if (res.data.status === 200) {
          if (res.data.code == 'Paid') {
            this.$message.success(res.data.msg)
            this.getDetail()
            this.loading = false
          }

          this.isShowRenew = false
          this.renewOrderId = res.data.data.id
          const orderId = res.data.data.id
          const amount = this.renewParams.price
          this.loading = false
          this.$refs.payDialog.showPayDialog(orderId, amount)
        }
      })
    },

    // 续费周期点击
    async renewItemChange(item, index) {
      this.reRemoveCashCode()
      this.renewLoading = true
      this.renewActiveId = index
      this.renewParams.duration = item.duration
      this.renewParams.billing_cycle = item.billing_cycle
      let price = item.price
      this.renewParams.original_price = item.price
      this.renewParams.base_price = item.base_price
      // 开启了优惠码插件
      if (this.isShowPromo && this.customfield.promo_code) {
        const discountParams = { id: this.product_id, amount: item.base_price }
        // 开启了等级折扣插件
        if (this.isShowLevel) {
          // 获取等级抵扣价格
          await clientLevelAmount(discountParams).then(res2 => {
            if (res2.data.status === 200) {
              this.renewParams.clDiscount = Number(res2.data.data.discount) // 客户等级优惠金额
            }
          }).catch(error => {
            this.renewParams.clDiscount = 0
          })
        }
        // 更新优惠码
        await applyPromoCode({ // 开启了优惠券
          scene: 'renew',
          product_id: this.product_id,
          amount: item.base_price,
          billing_cycle_time: this.renewParams.duration,
          promo_code: this.customfield.promo_code,
        }).then((resss) => {
          this.isUseDiscountCode = true
          this.renewParams.code_discount = Number(resss.data.data.discount)
        }).catch((err) => {
          this.$message.error(err.data.msg)
          this.removeDiscountCode()
        })

      }
      this.renewLoading = false
    },

    // 支付成功回调
    paySuccess(e) {
      this.getDetail()
      this.getComDetail()
      console.log(e);
    },
    // 取消支付回调
    payCancel(e) {
      console.log(e);
    }
  },

}).$mount(template)