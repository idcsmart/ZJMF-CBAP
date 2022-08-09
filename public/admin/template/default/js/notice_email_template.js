(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('notice-email-template')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data () {
        let checkPwd2 = (val => {
          if (val !== this.formData.password) {
            return { result: false, message: window.lang.password_tip, type: 'error' };
          }
          return { result: true };
        })
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
              colKey: 'id',
              title: 'ID',
              width: 150
            },
            {
              colKey: 'subject',
              title: '模板标题'
            },
            {
              colKey: 'op',
              title: '管理',
              width: 200
            },
          ],
          hideSortTips: true,
          params: {
            keywords: '',
            page: 1,
            limit: 15,
            orderby: 'id',
            sort: 'desc'
          },
          total: 0,
          pageSizeOptions: [5, 10, 15, 20, 50],
          formData: { // 创建模板
            id: '',
            name: '',
            template_id: '',
            type: '0',
            title: '',
            content: '',
            notes: '',
            status: ''
          },
          testForm: {
            name: 'Smtp',
            id: '',
            email: ''
          },
          rules: {
            email: [{ required: true, message: window.lang.input + window.lang.email, type: 'error' }],
            name: [{ required: true, message: window.lang.select + window.lang.email_interface, type: 'error' }]
          },
          country: [],
          loading: false,
          emailList: [],
          delId: '',
          curStatus: 1,
          statusTip: '',
          addTip: '',
          installTip: '',
          optType: '',
          name: '', // 插件标识
          type: '', // 安装/卸载
          module: 'sms' // 当前模块
        }
      },
      created () {
        this.getEmailList()
        this.getCountry()
        this.getEmailInterface()
      },
      methods: {
        // 获取邮件接口列表
        async getEmailInterface(){
          try {
            const params = {
              module: 'mail'
            }
            const res = await getMoudle(params)
            this.emailList = res.data.data.list
          } catch (error) {
            
          }
        },
        // 测试接口
        testHandler (row) {
          this.testForm.id = row.id
          this.statusVisble = true
        },
        async testSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const res = await testEmailTemplate(this.testForm)
              this.$message.success(res.data.msg)
              this.statusVisble = false
            } catch (error) {
              this.$message.error(error.data.msg)
              this.statusVisble = false
            }

          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        closeTest () {
          this.statusVisble = false
          this.testForm.phone = ''
        },
        // 获取国家列表
        async getCountry () {
          try {
            const res = await getCountry()
            this.country = res.data.data.list
          } catch (error) {
          }
        },
        back () {
          location.href = 'notice_email.html'
        },
        jump () {
          location.href = 'notice_email_template_create.html'
        },
        goSendManage () {

        },
        // 获取列表
        async getEmailList () {
          try {
            this.loading = true
            const res = await getEmailTemplate(this.name)
            this.loading = false
            this.data = res.data.data.list
            this.total = res.data.data.count
          } catch (error) {
            this.loading = false
          }
        },
        // 排序
        sortChange (val) {
          if (!val) {
            return
          }
          this.params.orderby = val.sortBy
          this.params.sort = val.descending ? 'desc' : 'asc'
          this.getEmailList()
        },
        clearKey () {
          this.params.keywords = ''
          this.seacrh()
        },
        seacrh () {
          this.getEmailList()
        },
        close () {
          this.visible = false
          this.$nextTick(() => {
            this.$refs.userDialog.clearValidate()
            this.$refs.userDialog && this.$refs.userDialog.reset()
          })
        },
        // 创建模板
        createTemplate () {
          this.visible = true
          this.optType = 'create'
          this.addTip = window.lang.create_template
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const res = await createTemplate(this.optType, this.formData)
              this.$message.success(res.data.msg)
              this.getEmailList()
              this.visible = false
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        // 编辑
        updateHandler (row) {
          location.href = `notice_email_template_update.html?id=${row.id}`
        },
        // 删除
        deleteHandler (row) {
          this.delVisible = true
          this.delId = row.id
        },
        async sureDel () {
          try {
            const res = await deleteEmailTemplate(this.delId)
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getEmailList()
          } catch (error) {
            this.$message.error(error.data.msg)
            this.delVisible = false
          }
        },
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
