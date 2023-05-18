(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('common-config')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      components: {
        asideMenu,
        topMenu,
        payDialog
      },
      created () {
        this.id = location.href.split('?')[1].split('=')[1]
        this.getCommonData()
        this.getConfig()
        this.getCountryList()
      },
      mounted () {

      },
      updated () {
        // // 关闭loading
        document.getElementById('mainLoading').style.display = 'none';
        document.getElementsByClassName('template')[0].style.display = 'block'
      },
      destroyed () {

      },
      computed: {
        calStr () {
          const temp = this.basicInfo.order_page_description?.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&/g, '&').replace(/"/g, '"').replace(/'/g, "'");
          return temp
        },
        calcDes () {
          return (val) => {
            const temp = val.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&/g, '&').replace(/"/g, '"').replace(/'/g, "'");
            return temp
          }
        },
        calcSwitch () {
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
        calcCountry () {
          return (val) => {
            return this.countryList.filter(item => val === item.iso)[0]?.name_zh
          }
        },
        calcCity () {
          return (id) => {
            return this.filterCountry[id].filter(item => item[0]?.country === this.curCountry[id])[0]
          }
        }
      },
      data () {
        return {
          id: '',
          submitLoading: false,
          commonData: {},
          // 订单数据
          orderData: {
            qty: 1,
            // 是否勾选阅读
            isRead: false,
            // 付款周期
            duration: '',
          },
          // 右侧展示区域
          showInfo: [],
          totalPrice: 0.00, // 总价
          timerId: null, // 订单id
          basicInfo: {}, // 基础信息
          configoptions: [], // 配置项
          custom_cycles: [], // 自定义周期
          curCycle: 0,
          cycle: '',
          onetime: '',
          pay_type: '',
          // 提交数据
          configForm: { // 自定义配置项

          },
          // 国家列表
          countryList: [],
          // 处理过后的国家列表
          filterCountry: {},
          curCountry: {} // 当前国家，根据配置id存入对应的初始索引
        }
      },
      filters: {
        formateTime (time) {
          if (time && time !== 0) {
            return formateDate(time * 1000)
          } else {
            return "--"
          }
        }
      },
      methods: {
        async getCountryList () {
          try {
            const res = await getCountry()
            this.countryList = res.data.data.list
          } catch (error) {
          }
        },
        async getConfig () {
          try {
            const res = await getCommonDetail(this.id)
            const temp = res.data.data
            this.basicInfo = temp.common_product
            this.configoptions = temp.configoptions.filter(item => item.subs.length)
            this.custom_cycles = temp.custom_cycles
            this.pay_type = temp.common_product.pay_type
            this.onetime = temp.cycles.onetime === '-1.00' ? '0.00' : temp.cycles.onetime
            // 初始化自定义配置参数
            const obj = this.configoptions.reduce((all, cur) => {
              all[cur.id] = (
                cur.option_type === 'multi_select' ||
                cur.option_type === 'quantity' ||
                cur.option_type === 'quantity_range'
              ) ? [cur.option_type === 'multi_select' ? cur.subs[0].id : cur.subs[0].qty_min] : cur.subs[0].id
              // 区域的时候保存国家
              if (cur.option_type === 'area') {
                this.filterCountry[cur.id] = this.toTree(cur.subs)
                this.$set(this.curCountry, cur.id, 0)
              }
              return all
            }, {})
            this.configForm = obj
            if (this.pay_type === 'onetime') {
              this.cycle = 'onetime'
            } else if (this.pay_type === 'free') {
              this.cycle = 'free'
            } else {
              this.cycle = temp.custom_cycles[0].id
            }
            this.changeConfig()
          } catch (error) {

          }
        },
        // 数组转树
        toTree (data) {
          var temp = Object.values(data.reduce((res, item) => {
            res[item.country] ? res[item.country].push(item) : res[item.country] = [item]
            return res
          }, {}))
          return temp
        },
        // 切换配置选项
        changeItem () {
          this.changeConfig()
        },
        // 更改配置计算价格
        async changeConfig () {
          try {
            const temp = this.formatData()
            const params = {
              id: this.id,
              config_options: {
                configoption: temp,
                cycle: this.cycle
              },
              qty: this.orderData.qty
            }
            const res = await calcPrice(params)
            this.totalPrice = res.data.data.price
            let str = res.data.data.content.split('\n')
            str = str.reduce((all, cur) => {
              all.push({
                name: cur.split('=>')[0],
                value: cur.split('=>')[1],
              })
              return all
            }, [])
            this.showInfo = str
          } catch (error) {
            console.log(error)
          }
        },
        // 切换国家
        changeCountry (id, index) {
          this.$set(this.curCountry, id, index)
          this.configForm[id] = this.filterCountry[id][index][0]?.id
          this.changeConfig()
        },
        // 切换城市
        changeCity (el, id) {
          this.configForm[id] = el.id
          this.changeConfig()
        },
        // 切换单击选择
        changeClick (id, el) {
          this.configForm[id] = el.id
          this.changeConfig()
        },
        // 切换数量
        changeNum (val, id) {
          this.configForm[id] = [val * 1]
          this.changeConfig()
        },
        // 切换周期
        changeCycle (item, index) {
          this.cycle = item.id
          this.curCycle = index
          this.changeConfig()
        },
        // 商品购买数量减少
        delQty () {
          if (this.orderData.qty > 1) {
            this.orderData.qty--
          }
        },
        // 商品购买数量增加
        addQty () {
          this.orderData.qty++
        },

        formatData () {
          // 处理数量类型的转为数组
          const temp = JSON.parse(JSON.stringify(this.configForm))
          Object.keys(temp).forEach(el => {
            const arr = this.configoptions.filter(item => item.id * 1 === el * 1)
            if (arr[0].option_type === 'quantity' || arr[0].option_type === 'quantity_range' || arr[0].option_type === 'multi_select') {
              if (typeof (temp[el]) !== 'object') {
                temp[el] = [temp[el]]
              }
            }
          })
          return temp
        },
        // 立即购买
        async addCart () {
          if (!this.orderData.isRead) {
            this.$message.error("请先阅读并勾选协议")
            return false
          }
          const temp = this.formatData()
          const params = {
            product_id: this.id,
            config_options: {
              configoption: temp,
              cycle: this.cycle
            },
            qty: this.orderData.qty
          }
          try {
            this.submitLoading = true
            const res = await settle(params)
            if (res.data.status === 200) {
              const orderId = res.data.data.order_id
              const amount = (this.totalPrice * this.orderData.qty)
              this.$refs.payDialog.showPayDialog(orderId, amount)
            }
          } catch (error) {
            this.$message({
              message: error.data.msg,
              type: 'warning'
            });
            this.submitLoading = false
          }
        },
        // 支付成功回调
        paySuccess (e) {
          this.submitLoading = false
          location.href = 'common_product_list.htm'
        },
        // 取消支付回调
        payCancel (e) {
          this.submitLoading = false
          location.href = 'finance.htm'
        },
        // 获取通用配置
        getCommonData () {
          this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
          document.title = this.commonData.website_name + '-订购'
        }
      },

    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
