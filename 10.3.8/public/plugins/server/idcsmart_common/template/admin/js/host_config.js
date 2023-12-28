const template = document.getElementsByClassName('common_config')[0]
Vue.prototype.lang = window.lang
Vue.prototype.moment = window.moment;
new Vue({
  data() {
    return {
      // 模块相关
      host: location.origin,
      id: '',
      client_id: '',
      configArr: [],
      isLoading: false,
      configForm: {},
      configRules: {

      },
      sonConfigRules: {},
      sonConfigForm: {},
      service_due_time: '',
      active_time: '',
      due_time: '',
      authInfo: {
        authorize_id: '',
        domain: '',
        ip: '',
        license: '',
        service_due_time: ''
      },
      sonData: {
        authorize_id: '',
        domain: '',
        ip: '',
        license: '',
        service_due_time: ''
      },
      // 国家列表
      countryList: [],
      // 处理过后的国家列表
      filterCountry: [],
      sonConfigArr: [],
      curCountry: [],
      cycleList: [
        { value: 'free', label: lang.free },
        { value: 'onetime', label: lang.onetime },
        { value: 'recurring_prepayment', label: lang.recurring_prepayment },
        { value: 'recurring_postpaid', label: lang.recurring_postpaid },
      ],
      popupProps: {
        overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` }),
      },

      sonHost: {
        first_payment_amount: '',
        renew_amount: '',
        billing_cycle: '',
        billing_cycle_name: '',
        active_time: '',
        due_time: ''
      }
    }
  },
  created() {
    const query = location.href.split('?')[1].split('&')
    this.client_id = this.getQuery(query[0])
    this.id = this.getQuery(query[1])
    this.getproModule()
    this.getCountryList()
  },
  computed: {
    disabled() {
      return this.sonHost.due_time === '' && this.sonHost.billing_cycle === 'onetime'
    },
    calcCountry() {
      return (val) => {
        return this.countryList.filter(item => val === item.iso)[0]?.name_zh
      }
    },
    calcIcon() {
      return this.host + '/upload/common/country/' + this.countryList.filter(item => item.id === this.filterCountry[item.id]) + '.png'
    },
    calcSwitch() {
      return (item, type) => {
        if (type) {
          const arr = item.subs.filter(item => item.option_name === '是')
          return arr[0]?.id
        } else {
          const arr = item.subs.filter(item => item.option_name === '否')
          return arr[0]?.id
        }
      }
    },
  },
  methods: {
    async getCountryList() {
      try {
        const res = await getCountry()
        this.countryList = res.data.data.list
      } catch (error) {
      }
    },
    getQuery(val) {
      return val.split('=')[1]
    },

    // 获取模块接口
    async getproModule() {
      try {
        const res = await getproModule(this.id)
        if (res.data.data.content) {
          this.getProductInfo()
        }
      } catch (error) {
      }
    },
    changeActive(value, context) {
      this.authInfo.service_due_time = context.dayjsValue.valueOf() / 1000
    },
    changeActive2(value, context) {
      this.sonHost.active_time = context.dayjsValue.valueOf() / 1000
    },
    changeActive3(value, context) {
      this.sonHost.due_time = context.dayjsValue.valueOf() / 1000
    },
    formatData() {
      // 处理数量类型的转为数组
      const temp = JSON.parse(JSON.stringify(this.configForm))
      Object.keys(temp).forEach(el => {
        const arr = this.configArr.filter(item => item.id * 1 === el * 1)
        if (arr.length !== 0) {
          if (arr[0].option_type === 'quantity' || arr[0].option_type === 'quantity_range' || arr[0].option_type === 'multi_select') {
            if (typeof (temp[el]) !== 'object') {
              temp[el] = [temp[el]]
            }
          }
        }
      })
      return temp
    },
    // 数组转树
    toTree(data) {
      const arr = data.reduce((res, item) => {
        res[item.country] ? res[item.country].push(item) : res[item.country] = [item]
        return res
      }, {})
      const temp1 = Object.values(arr)
      const temp2 = Object.keys(arr)
      const countryArr = []
      temp2.forEach((item, index) => {
        const obj = {
          option_name: this.calcCountry(item),
          id: index + 'f',
          children: temp1[index]
        }
        countryArr.push(obj)
      })
      return countryArr
    },
    // 获取模块配置
    async getProductInfo() {
      try {
        const res = await getProInfo({
          id: this.id
        })
        this.configArr = res.data.data.upgrade_configoptions
        const nowComfig = res.data.data.configoptions
        // 初始化自定义配置参数
        const obj = this.configArr.reduce((all, cur) => {
          for (let index = 0; index < nowComfig.length; index++) {
            const item = nowComfig[index]
            if (item.id === cur.id) {
              // 多选为数组
              if (item.option_type === 'multi_select') {
                if (!all[cur.id]) {
                  all[cur.id] = [item.configoption_sub_id]
                } else {
                  all[cur.id].push(item.configoption_sub_id)
                }
              } else if (item.option_type === 'quantity_range' || item.option_type === 'quantity') {
                all[cur.id] = item.qty
              } else if (item.configoption_sub_id !== 0) { // 处理开关
                cur.subs.forEach((items) => {
                  if (items.id === item.configoption_sub_id) {
                    all[cur.id] = items.id
                  }
                })
              } else { // 赋默认值
                all[cur.id] = item.qty
              }
              // 区域的时候保存国家
              if (cur.option_type === 'area') {
                cur.subs.forEach((sitem) => {

                })
                this.filterCountry = this.toTree(cur.subs)
              }
            }

          }
          return all
        }, {})
        this.configForm = obj
        console.log(this.configForm);
      } catch (error) {
        console.log(error);
      }
    },
    // 提交
    async submitConfig({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          this.isLoading = true
          const temp = this.formatData()
          console.log(temp);
          const params = { id: this.id, config_option: temp }
          const res = await saveProInfo(params)
          this.$message.success(res.data.msg)
          this.getProductInfo()
          this.isLoading = false
        } catch (error) {
          this.isLoading = false
          this.$message.error(error.data.msg)
        }
      } else {
        console.log('Errors: ', validateResult);
        this.$message.warning(firstError);
      }
    },
  },
}).$mount(template)