const template = document.getElementsByClassName('common_config')[0]
Vue.prototype.lang = window.lang
Vue.prototype.moment = window.moment;
new Vue({
  data() {
    return {
      // 模块相关
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
      sonConfigArr: [],
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
  },
  computed: {
    disabled() {
      return this.sonHost.due_time === '' && this.sonHost.billing_cycle === 'onetime'
    },
    disabled2() {
      return this.authInfo.due_time === '' && this.authInfo.billing_cycle === 'onetime'
    },
  },
  methods: {
    getQuery(val) {
      return val.split('=')[1]
    },

    // 获取模块接口
    async getproModule() {
      try {
        const res = await getproModule(this.id)
        // this.$nextTick(() => {
        //   $('.config-box .content').html(res.data.data.content)
        // })
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
    // 获取模块配置
    async getProductInfo() {
      try {
        const res = await getProInfo({
          id: this.id
        })
        this.authInfo.authorize_id = res.data.data.authorize_id
        this.authInfo.domain = res.data.data.domain
        this.authInfo.ip = res.data.data.ip
        this.authInfo.license = res.data.data.license
        this.authInfo.service_due_time = res.data.data.service_due_time
        this.service_due_time = res.data.data.service_due_time * 1000
        this.configArr = res.data.data.config_option
        const obj = this.configArr.reduce((all, cur) => {
          this.$set(this.configRules, String(cur.id), [
            { required: true, message: cur.option_type === 'select' ? lang.select : lang.input, type: 'error' },
          ])
          all[cur.id] = cur.value
          return all
        }, {})
        this.configForm = obj

        // 有子商品
        if (res.data.data.son_host.id) {
          this.sonHost = res.data.data.son_host
          this.sonHost.active_time = res.data.data.son_host.active_time
          this.active_time = res.data.data.son_host.active_time * 1000
          this.sonHost.due_time = res.data.data.son_host.due_time
          this.due_time = res.data.data.son_host.due_time * 1000
          this.sonHost = res.data.data.son_host
          this.sonData.authorize_id = res.data.data.son.authorize_id
          this.sonData.domain = res.data.data.son.domain
          this.sonData.ip = res.data.data.son.ip
          this.sonData.license = res.data.data.son.license
          this.sonData.service_due_time = res.data.data.son.service_due_time
          this.sonConfigArr = res.data.data.son.config_option
          const sonObj = this.sonConfigArr.reduce((all, cur) => {
            this.$set(this.sonConfigRules, String(cur.id), [
              { required: true, message: cur.option_type === 'select' ? lang.select : lang.input, type: 'error' },
            ])
            all[cur.id] = cur.value
            return all
          }, {})

          this.sonConfigForm = sonObj
        }
      } catch (error) {
        console.log(error);
      }
    },
    // 提交
    async submitConfig({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          this.isLoading = true
          const temp = Object.keys(this.configForm).reduce((all, cur) => {
            all.push({
              id: cur * 1,
              value: this.configForm[cur]
            })
            return all
          }, [])
          const son_temp = Object.keys(this.sonConfigForm).reduce((all, cur) => {
            all.push({
              id: cur * 1,
              value: this.sonConfigForm[cur]
            })
            return all
          }, [])
          const params = { ...this.authInfo, id: this.id, config_option: temp, son: { ...this.sonHost, ...this.sonData, id: this.sonHost.id, config_option: son_temp } }
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