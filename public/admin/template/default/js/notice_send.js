(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('notice-send')[0]
    Vue.prototype.lang = window.lang
    new Vue({
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
              title: lang.sms_interface
            },
            {
              colKey: 'sms_template',
              title: lang.sms_template
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
          formData: {},
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
          maxHeight: ''
        }
      },
      mounted () {
        this.maxHeight = document.getElementById('content').clientHeight - 200
        let timer = null
        window.onresize = () => {
          if (timer) {
            return
          }
          timer = setTimeout(() => {
            this.maxHeight = document.getElementById('content').clientHeight - 200
            clearTimeout(timer)
            timer = null
          }, 300)
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
              this.smsInterList.forEach(item => {
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
          location.href = `notice_email_template.html`
        },
        async save () {
          try {
            const params = JSON.parse(JSON.stringify(this.formData))
            for(const item in params){
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
            const res = await updateSend(params)
            this.$message.success(res.data.msg)
          } catch (error) {
            this.$message.error(error.data.msg)
          }
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
