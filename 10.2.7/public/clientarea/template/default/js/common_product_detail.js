(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('common_product_detail')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      components: {
        asideMenu,
        topMenu,
        pagination,
        payDialog,
      },
      created() {
        this.id = location.href.split('?')[1].split('=')[1]
        this.getCommonData()
        this.getDetail()
        // 获取退款信息
        this.getRefundInfo()
      },
      mounted() {

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
          // 续费页面信息
          renewPageData: [],
          // 续费参数
          renewParams: {
            id: 0,
            billing_cycle: '',
            price: 0
          },
        }
      },
      filters: {
        formateTime(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000)
          } else {
            return "--"
          }
        }
      },
      methods: {
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
          try {
            const res = await getRefund(this.id)
            this.refundDialog = res.data.data
            console.log(this.refundDialog.config_option.data[0].option)
            if (!this.refundDialog.allow_refund) {
              this.noRefundVisible = true
              return false
            }
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
                return this.$message.error('请输入退款原因')
              }
            } else {
              if (this.refundForm.arr.length === 0) {
                return this.$message.error('请选择退款原因')
              }
            }
            const params = {
              host_id: this.id,
              type: this.refundForm.type,
              suspend_reason: this.refundDialog.reason_custom ? this.refundForm.str : this.refundForm.arr
            }
            const res = await submitRefund(params)
            this.$message.success('申请退款成功')
            this.refundVisible = false
            this.getRefundInfo()
          } catch (error) {
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
            this.configoptions = res.data.data.configoptions
          } catch (error) {

          }
        },
        back() {
          location.href = 'common_product_list.htm'
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

        // 显示续费弹窗
        showRenew() {
          // 获取续费页面信息
          const params = {
            id: this.id,
          }
          renewPage(params).then(res => {
            if (res.data.status === 200) {
              this.renewPageData = res.data.data.host

              this.renewActiveId = 0
              this.renewParams.billing_cycle = this.renewPageData[0].billing_cycle
              this.renewParams.price = this.renewPageData[0].price
              this.isShowRenew = true

            }
          }).catch(err => {
            this.$message.error(err.data.msg)
          })

        },
        // 续费弹窗关闭
        renewDgClose() {
          this.isShowRenew = false
        },
        // 续费提交
        subRenew() {
          const params = {
            id: this.id,
            billing_cycle: this.renewParams.billing_cycle,
            customfield: {
              promo_code: []
            }
          }
          renew(params).then(res => {
            if (res.data.status === 200) {
              this.isShowRenew = false
              this.renewOrderId = res.data.data.id
              const orderId = res.data.data.id
              const amount = this.renewParams.price
              this.$refs.payDialog.showPayDialog(orderId, amount)
            }
          })
        },
        // 续费周期点击
        renewItemChange(item, index) {
          console.log(item);
          this.renewActiveId = index
          this.renewParams.billing_cycle = item.billing_cycle
          this.renewParams.price = item.price
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
    typeof old_onload == 'function' && old_onload()
  };
})(window);
