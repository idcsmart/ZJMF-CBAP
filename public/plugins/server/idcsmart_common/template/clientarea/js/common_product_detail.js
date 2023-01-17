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
    // // 关闭loading
    document.getElementById('mainLoading').style.display = 'none';
    document.getElementsByClassName('common_product_detail')[0].style.display = 'block'
  },
  destroyed() {

  },
  data() {
    return {
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
        free: '免费',
        onetime: '一次性',
        recurring_prepayment: '周期先付',
        recurring_postpaid: '周期后付',
      },
      countryList: [],
      host: {}, // 基础信息
      configoptions: [], // 配置 
      status: {
        Unpaid: { text: "未付款", color: "#F64E60", bgColor: "#FFE2E5" },
        Pending: { text: "开通中", color: "#3699FF", bgColor: "#E1F0FF" },
        Active: { text: "正常", color: "#1BC5BD", bgColor: "#C9F7F5" },
        Suspended: { text: "已暂停", color: "#F0142F", bgColor: "#FFE2E5" },
        Deleted: { text: "已删除", color: "#9696A3", bgColor: "#F2F2F7" },
        Failed: { text: "开通失败", color: "#FFA800", bgColor: "#FFF4DE" }
      },
      // 停用状态
      refundStatus: {
        Pending: "待审核",
        Suspending: "待停用",
        Suspend: "停用中",
        Suspended: "已停用",
        Refund: "已退款",
        Reject: "审核驳回",
        Cancelled: "已取消"
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
        original_price: 0,// 原价
        base_price: 0,
        totalPrice: 0 // 现价
      },
      /* 备注 */
      isShowNotesDialog: false,
      hostData: {},
      notesValue: '',
      promo_code: [],
      loading: false,
      // 自动续费
      isShowPayMsg: 0,
      autoTitle: '',
      dialogVisible: false
    }
  },
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
    filterCountry() {
      return (country) => {
        const name = this.countryList.filter(item => item.iso === country)
        return name[0]?.name_zh
      }
    }
  },
  methods: {
    changeAutoStatus(e) {
      console.log(e)
      this.dialogVisible = true
      this.autoTitle = this.isShowPayMsg ? '请确认您将为以下实例关闭自动续费' : '请确认您将为以下实例开启自动续费'
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
        console.log(this.refundInfo)
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
        console.log(this.refundDialog.config_option.data[0].option)
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
            return this.$message.error('请输入停用原因')
          }
        } else {
          if (this.refundForm.arr.length === 0) {
            return this.$message.error('请选择停用原因')
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
        this.$message.success('申请成功！')
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
        this.$message.success('请求取消停用成功!')
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
        this.configoptions = temp
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
      this.configoptions[index].show = bol
    },
    hideTip(index) {
      this.configoptions[index].show = false
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
      document.title = this.commonData.website_name + '-通用产品'
    },
    // 使用优惠码
    async getDiscount(data) {
      this.customfield.promo_code = data[1]
      this.isUseDiscountCode = true
      this.renewParams.code_discount = Number(data[0])
      const price = this.renewParams.base_price
      const discountParams = { id: this.product_id, amount: price }
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
      this.renewParams.totalPrice = ((price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.code_discount * 1000) / 1000) > 0 ? ((price * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000).toFixed(2) : 0
    },
    removeDiscountCode() {
      this.isUseDiscountCode = false
      this.customfield.promo_code = ''
      this.renewParams.code_discount = 0
      const price = this.renewParams.original_price
      this.renewParams.totalPrice = ((price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000) > 0 ? ((price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000).toFixed(2) : 0
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
          this.renewParams.totalPrice = this.renewPageData[0].price > 0 ? Number(this.renewPageData[0].price) : 0
          let price = this.renewPageData[0].price

          // 开启了优惠码插件
          if (this.isShowPromo && this.customfield.promo_code) {
            // 更新优惠码
            await applyPromoCode({ // 开启了优惠券
              scene: 'renew',
              product_id: this.id,
              amount: this.renewPageData[0].base_price,
              billing_cycle_time: this.renewParams.duration,
              promo_code: this.customfield.promo_code,
            }).then((resss) => {
              price = this.renewPageData[0].base_price
              this.isUseDiscountCode = true
              this.renewParams.code_discount = Number(resss.data.data.discount)
            }).catch((err) => {
              this.$message.error(err.data.msg)
              this.removeDiscountCode()
            })
          }
          const discountParams = { id: this.product_id, amount: price }
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
          this.renewParams.totalPrice = ((price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.code_discount * 1000) / 1000) > 0 ? ((price * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000).toFixed(2) : 0
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
      this.renewParams.totalPrice = ((this.renewParams.original_price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.code_discount * 1000) / 1000) > 0 ? ((this.renewParams.original_price * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000).toFixed(2) : 0
    },
    // 续费移除代金券
    reRemoveCashCode() {
      this.$refs.cashRef.closePopver()
      this.cashObj = {}
      this.renewParams.cash_discount = 0
      this.customfield.voucher_get_id = ''
      this.renewParams.totalPrice = ((this.renewParams.original_price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.code_discount * 1000) / 1000) > 0 ? ((this.renewParams.original_price * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000).toFixed(2) : 0
    },
    // 续费弹窗关闭
    renewDgClose() {
      this.isShowRenew = false
      this.removeDiscountCode()
      this.reRemoveCashCode()
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
        // 更新优惠码
        await applyPromoCode({ // 开启了优惠券
          scene: 'renew',
          product_id: this.id,
          amount: item.base_price,
          billing_cycle_time: this.renewParams.duration,
          promo_code: this.customfield.promo_code,
        }).then((resss) => {
          price = item.base_price
          this.isUseDiscountCode = true
          this.renewParams.code_discount = Number(resss.data.data.discount)
        }).catch((err) => {
          this.$message.error(err.data.msg)
          this.removeDiscountCode()
        })
      }
      const discountParams = { id: this.product_id, amount: price }
      // 开启了等级折扣插件
      if (this.isShowLevel) {
        // 获取等级抵扣价格
        await clientLevelAmount(discountParams).then(res2 => {
          if (res2.data.status === 200) {
            this.renewParams.clDiscount = Number(ress.data.data.discount) // 客户等级优惠金额
          }
        }).catch(error => {
          this.renewParams.clDiscount = 0
        })
      }

      this.renewParams.totalPrice = ((price * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.code_discount * 1000) / 1000) > 0 ? ((price * 1000 - this.renewParams.cash_discount * 1000 - this.renewParams.clDiscount * 1000 - this.renewParams.code_discount * 1000) / 1000).toFixed(2) : 0
      this.renewLoading = false
    },

    // 支付成功回调
    paySuccess(e) {
      this.getDetail()
      console.log(e);
    },
    // 取消支付回调
    payCancel(e) {
      console.log(e);
    }
  },

}).$mount(template)