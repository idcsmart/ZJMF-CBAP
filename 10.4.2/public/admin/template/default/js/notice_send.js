(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('notice-send')[0]
    Vue.prototype.lang = window.lang
   new Vue({
      components: {
        comConfig,
      },
      data () {
        return {
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          statusVisble: false,
          hover: true,
          columns: [
            {
              colKey: 'name',
              title: lang.action_name,
              width: 250
            },
            {
              colKey: 'sms_global_name',
              title: lang.sms_global_name
            },
            {
              colKey: 'sms_global_template',
              title: lang.sms_global_template
            },
            {
              colKey: 'sms_name',
              title: lang.home_sms_interface
            },
            {
              colKey: 'sms_template',
              title: lang.home_sms_template
            },
            {
              colKey: 'sms_enable',
              title: lang.isOpen,
              width: 120
            },
            {
              colKey: 'email_name',
              title: lang.email_interface
            },
            {
              colKey: 'email_template',
              title: lang.email_temp
            },
            {
              colKey: 'email_enable',
              title: lang.isOpen,
              width: 120
            }
          ],
          hideSortTips: true,
          formData: {
            configuration: {
              send_sms: '',
              send_sms_global: '',
              send_email: ''
            }
          },
          loading: false,
          country: [],
          delId: '',
          curStatus: 1,
          statusTip: '',
          installTip: '',
          name: '', // 插件标识
          type: '', // 安装/卸载
          module: 'mail', // 当前模块
          smsList: [],  // 国内短信接口列表
          smsInterList: [],  // 国际短信接口列表
          emailList: [], // 邮件接口列表
          emailTemplateList: [], // 邮件模板列表
          interTempObj: {},
          tempObj: {},
          maxHeight: '',
          rules: {
            send_sms_global: [
              { required: true, message: lang.select + lang.sms_global_name },
            ],
            send_sms: [
              { required: true, message: lang.select + lang.home_sms_interface },
            ],
            send_email: [
              { required: true, message: lang.select + lang.email_interface },
            ],
          },
          canSend: true,
          submitLoading: false
        }
      },
      created () {
        // 发送管理列表
        this.getManageList()
        // 接口列表
        this.getSmsList()
        this.getEmailList()
        // 模板列表
        this.getEmailTemList()
      },
      methods: {
        // 切换短信接口清空短信模板
        changeInter (row) {
          // this.formData[row.name].sms_global_template = this.interTempObj[row.sms_global_name+'_interTemp'][0]?.id
          this.formData[row.name].sms_global_template = ''
        },
        changeHome (row) {
          // this.formData[row.name].sms_template = this.tempObj[row.sms_name+'_temp'][0]?.id || ''
          this.formData[row.name].sms_template = ''
        },
        // 根据短信name获取对应的模板
        async getSmsTemp (type, val) {
          try {
            const res = await getSmsTemplate(val)
            const temp = res.data.data.list.filter(item => {
              return item.type === 0
            })
            const temp1 = res.data.data.list.filter(item => {
              return item.type === 1
            })
            if (type === 1) {
              this.smsInterList.forEach(item => {
                if (item.name === val) {
                  this.$set(this.interTempObj, `${item.name}_interTemp`, temp1)
                  this.$forceUpdate()
                }
              })
            }
            if (type === 0) {
              this.smsList.forEach(item => {
                if (item.name === val) {
                  this.$set(this.tempObj, `${item.name}_temp`, temp)
                }
              })
            }
          } catch (error) {

          }
        },
        async getSmsList () {
          try {
            const res = await getSmsInterface()
            const temp = res.data.data.list
            // 分装到国际/国内，在根据所选的接口name获取对应接口下面的模板
            temp.forEach(item => {
              if (item.sms_type.indexOf(1) !== -1) {
                this.smsInterList.push(item)
                this.getSmsTemp(1, item.name)
              }
              if (item.sms_type.indexOf(0) !== -1) {
                this.smsList.push(item)
                this.getSmsTemp(0, item.name)
              }
            })
          } catch (error) {

          }
        },
        async getEmailList () {
          try {
            const res = await getEmailInterface()
            this.emailList = res.data.data.list
          } catch (error) {

          }
        },
        async getEmailTemList () {
          try {
            const res = await getEmailTemplate()
            this.emailTemplateList = res.data.data.list
          } catch (error) {

          }
        },
        jump (row) {
          location.href = `notice_email_template.htm`
        },
        async save () {
          this.$refs.sendForm.validate().then(async res => {
            if (res === true) {
              try {
                const params = JSON.parse(JSON.stringify(this.formData))
                for (const item in params) {
                  if (params[item].sms_template === '') {
                    params[item].sms_template = 0
                  }
                  if (params[item].email_template === '') {
                    params[item].email_template = 0
                  }
                  if (params[item].sms_global_template === '') {
                    params[item].sms_global_template = 0
                  }
                }
                this.canSend = true
                // 提交前验证，选择了接口的必填
                Object.keys(params).forEach(item => {
                  try {
                    if (params[item].sms_global_name && params[item].sms_global_template === 0) { // 选择了国际接口未选择模板
                      this.canSend = false
                      throw new Error(lang.select + lang.sms_global_template)
                    }
                    if (params[item].sms_name && params[item].sms_template === 0) { // 选择国内接口未选择模板
                      this.canSend = false
                      throw new Error(lang.select + lang.home_sms_template)
                    }
                    if (params[item].email_name && params[item].email_template === 0) { // 选择了邮件未选择模板
                      this.canSend = false
                      throw new Error(lang.select + lang.email_temp)
                    }
                  } catch (e) {
                    this.$message.error(e.message)
                  }
                })
                if (this.canSend) {
                  this.submitLoading = true
                  const res = await updateSend(params)
                  this.$message.success(res.data.msg)
                  this.submitLoading = false
                }
              } catch (error) {
                this.$message.error(error.data.msg)
                this.submitLoading = false
              }
            } else {
              this.$message.error(res[Object.keys(res)[0]][0].message)
            }
          })
        },
        back () {
          window.history.go(-1)
        },
        // 获取列表
        async getManageList () {
          try {
            this.loading = true
            const res = await getSendList()
            const temp = res.data.data.list
            this.data = temp
            this.loading = false
            // 动态渲染成响应式数据会很卡
            temp.forEach(item => {
              if (item.sms_template === 0) {
                item.sms_template = ''
              }
              if (item.email_template === 0) {
                item.email_template = ''
              }
              if (item.sms_global_template === 0) {
                item.sms_global_template = ''
              }
              //this.$set(this.formData, item.name, item)
              this.formData[item.name] = item
            })
            this.formData.configuration = res.data.data.configuration
          } catch (error) {
            this.loading = false
            console.log(error)
          }
        }
      }
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
