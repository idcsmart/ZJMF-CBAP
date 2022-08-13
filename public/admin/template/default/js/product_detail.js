(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('product-detail')[0]
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
          params: {
            keywords: '',
            page: 1,
            limit: 15,
            orderby: 'id',
            sort: 'desc'
          },
          total: 0,
          loading: false,
          moneyLoading: false,
          statusVisble: false,
          title: '',
          delId: '',
          formData: {
            id: '',
            name: '',
            product_group_id: '',
            description: '',
            hidden: 1, // 1 隐藏，0 显示
            stock_control: 0, // 库存控制(1:启用)默认0
            qty: 0,
            creating_notice_sms: 0, // 1开启 0关闭
            creating_notice_sms_api: 0,
            creating_notice_sms_api_template: 0,
            creating_notice_mail: 0,
            creating_notice_mail_api: 0,
            creating_notice_mail_template: 0,
            
            created_notice_sms: 0,
            created_notice_sms_api: 0,
            created_notice_sms_api_template: '',
            created_notice_mail: 0,
            created_notice_mail_api: 0,
            created_notice_mail_template: 0,
            pay_type: 'recurring_prepayment',
            upgrade: []
          },
          checkOptions: [
            {
              value: 1,
              label: lang.open,
            },
            {
              value: 0,
              label: lang.close,
            },
          ],
          rules: {
            qty: [
              { pattern: /^([1-9]\d{0,8}|0)$/, message: lang.verify13 + '0~999999999',type: 'warning' }
            ],
            description: [
              { validator: val=> val.length <= 1000, message: lang.verify3 + 1000, type: 'warning' }
            ]
          },
          visibleMoney: false,
          visibleLog: false,
          moneyData: { // 充值/扣费
            id: '',
            type: '', //  recharge充值 deduction扣费
            amount: '',
            notes: ''
          },
          // 变更记录
          logData: [],
          logCunt: 0,
          tableLayout: false,
          bordered: true,
          hover: true,
          secondGroup: [],
          popupProps: {
            overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` }),
          },
          currency_prefix: JSON.parse(localStorage.getItem('common_set')).currency_prefix,
          smsInterList: [],
          emailInterList: [],
          smsInterTemp: [], // 开通中短信模板
          smsInterTemp1: [], // 已开通短信模板
          emailInterTemp: [],
          payType: [
            {
              value: 'free',
              label: lang.free
            },
            {
              value: 'onetime',
              label: lang.onetime
            },
            {
              value: 'recurring_prepayment',
              label: lang.recurring_prepayment
            },
            {
              value: 'recurring_postpaid',
              label: lang.recurring_postpaid
            }
          ],
          relationList: [],
          smsTempList: {},
          creatingName: '',
          createdName: '',
        }
      },
      created () {
        this.id = location.href.split('?')[1].split('=')[1]
        this.langList = JSON.parse(localStorage.getItem('common_set')).lang_home
        this.getUserDetail()
        this.getSecondGroup()
        this.getEmail()
        this.getEmailTemp()
      },
      watch: {
        'formData.id' (val) {
          val && this.getRelationList()
        }
      },
      computed: {
        labelTip () {
          return this.moneyData.type === 'recharge' ? this.currency_prefix : `-${this.currency_prefix}`
        },
      },
      methods: {
        changeSmsInterface(e){
          const name = this.smsInterList.filter(item=>item.id === e)[0]?.name
          this.creatingName = name
        },
        async getRelationList () {
          try {
            const res = await getProduct()
            this.relationList = res.data.data.list.filter(item=>item.id !== this.id * 1)
          } catch (error) {
          }
        },
        async getEmail () {
          try {
            const res = await getEmailInterface()
            this.emailInterList = res.data.data.list
          } catch (error) {
          }
        },
        async getSmsTemp (name) {// 根据name获取短信模板
          try {
            const res = await getSmsTemplate(name)
            this.$set(this.smsTempList, name, res.data.data.list)
          } catch (error) {
          }
        },
        async getEmailTemp () {
          try {
            const res = await getEmailTemplate()
            this.emailInterTemp = res.data.data.list
          } catch (error) {
          }
        },
        changeCreating (id) {
          const name = this.smsInterList.find(item => item.id === id).name
          this.creatingName = name
        },
        changeCreated (id) {
          const name = this.smsInterList.find(item => item.id === id).name
          this.createdName = name
        },
        // 获取商品二级分组
        async getSecondGroup () {
          try {
            const res = await getSecondGroup()
            this.secondGroup = res.data.data.list
          } catch (error) {
          }
        },
        // 删除用户
        deleteUser () {
          this.delVisible = true
        },
        async sureDelUser () {
          try {
            const res = await deleteClient(this.id)
            this.delVisible = false
            location.href = '/client.html'
          } catch (error) {
            this.delVisible = false
          }
        },
        // 恢复
        changeStatus () {
          this.statusVisble = true
        },
        async sureChange () {
          try {
            const res = await changeOpen(this.id, { status: 1 })
            this.statusVisble = false
            this.$message.success(res.data.msg)
            this.getUserDetail()
          } catch (error) {
            this.statusVisble = false
          }
        },

        // 提交修改用户信息
        updateUserInfo () {
          this.$refs.userInfo.validate().then(async res => {
            // 验证通过
            try {
              const params = {...this.formData}
              delete params.auto_setup
              delete params.type
              delete params.rel_id
              params.creating_notice_sms_api = params.creating_notice_sms_api * 1
              params.creating_notice_sms_api_template = params.creating_notice_sms_api_template * 1
              params.creating_notice_mail_template = params.creating_notice_mail_template * 1
              params.created_notice_sms_api = params.created_notice_sms_api * 1
              params.created_notice_sms_api_template = params.created_notice_sms_api_template * 1
              params.created_notice_mail_template = params.created_notice_mail_template * 1
              const res = await editProduct(params)
              this.$message.success(res.data.msg)
              this.getUserDetail()
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          }).catch(err => {
            console.log(err)
          })
        },
        // 获取用户详情
        async getUserDetail () {
          try {
            const res = await getProductDetail(this.id)
            const temp = res.data.data.product
            this.formData = temp
            this.formData.creating_notice_sms_api = temp.creating_notice_sms_api || ''
            this.formData.creating_notice_sms_api_template = temp.creating_notice_sms_api_template || '' 
            this.formData.creating_notice_mail_api = temp.creating_notice_mail_api || '' 
            this.formData.creating_notice_mail_template = temp.creating_notice_mail_template ||''

            this.formData.created_notice_sms_api = temp.created_notice_sms_api || ''
            this.formData.created_notice_sms_api_template = temp.created_notice_sms_api_template|| ''
            this.formData.created_notice_mail_api = temp.created_notice_mail_api|| ''
            this.formData.created_notice_mail_template = temp.created_notice_mail_template || '' 
            const result = await getSmsInterface()
            const temp1 = result.data.data.list
            this.smsInterList = temp1
            temp1.forEach(item => {
              this.getSmsTemp(item.name)
            })
            this.creatingName = temp1.filter(item => item.id === temp.creating_notice_sms_api)[0]?.name
            this.createdName = temp1.filter(item => item.id == temp.created_notice_sms_api)[0]?.name
          } catch (error) {
            console.log(error)
          }
        },
        back () {
          location.href = 'product.html'
        }
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
