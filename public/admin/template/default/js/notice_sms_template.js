(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('notice-sms-template')[0]
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
              width: 80
            },
            {
              colKey: 'template_id',
              title: lang.template + 'ID',
              width: 100
            },
            {
              colKey: 'type',
              title: lang.type,
              width: 100
            },
            {
              colKey: 'title',
              title: lang.template + lang.title,
              width: 250
            },
            {
              colKey: 'content',
              title: lang.template + lang.content
            },
            {
              colKey: 'status',
              title: window.lang.status,
              width: 100
            },
            {
              colKey: 'op',
              title: lang.manage,
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
          pageSizeOptions: [20, 50, 100],
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
            name: '',
            id: '',
            phone_code: 86,
            phone: ''
          },
          rules: {
            template_id: [ 
              { pattern: /^[A-Za-z0-9]{0,100}$/, message: lang.verify15 + '，' + lang.verify3 + 100, type: 'warning'}
            ],
            phone: [{ required: true, message: lang.input + lang.phone, type: 'error' }],
            title: [
              { required: true, message: lang.input + lang.title, type: 'error' },
              { validator: val => val.length <= 50, message: lang.verify3 + 50, type: 'warning'}
            ],
            content: [
              { required: true, message: lang.input + lang.content, type: 'error' },
              { validator: val => val.length <= 255, message: lang.verify3 + 255, type: 'warning'}
            ],
            notes: [
              { validator: val => val.length <= 1000, message: lang.verify3 + 1000, type: 'warning'}
            ],
            status: [{ required: true, message: lang.select+lang.template+lang.status, type: 'error' }]
          },
          country: [],
          loading: false,
          delId: '',
          curStatus: 1,
          statusTip: '',
          addTip: '',
          installTip: '',
          optType: '',
          name: '', // 插件标识
          type: '', // 安装/卸载
          module: 'sms', // 当前模块
          isChina: true // 是否国内用于短信测试
        }
      },
      created () {
        this.formData.name = this.name = location.href.split('?')[1].split('=')[1]
        this.getSmsList()
        this.getCountry()
        this.getSmsTemplateStatus()
      },
      methods: {
        // 获取短信接口状态
        async getSmsTemplateStatus () {
          const res = await getSmsTemplateStatus(this.formData.name)
          if (res.data.status === 200) {
            this.getSmsList()
          }
        },
        // 测试接口
        testHandler (row) { 
          this.isChina = row.type === 0 ? true : false
          this.testForm.name = row.sms_name
          this.testForm.id = row.id
          this.statusVisble = true
        },
        async testSubmit () {
          try {
            const res = await testSmsTemplate(this.testForm)
            this.$message.success(res.data.msg)
            this.statusVisble = false
          } catch (error) {
            this.$message.error(error.data.msg)
            this.statusVisble = false
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
          location.href = 'notice_sms.html'
        },
        // 获取列表
        async getSmsList () {
          try {
            this.loading = true
            const res = await getSmsTemplate(this.name)
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
          this.getSmsList()
        },
        clearKey () {
          this.params.keywords = ''
          this.seacrh()
        },
        seacrh () {
          this.getSmsList()
        },
        close () {
          this.visible = false
          this.formData.type = '0'
          this.formData.template_id = ''
          this.formData.status = ''
          this.$nextTick(() => {
            this.$refs.createTemp && this.$refs.createTemp.clearValidate()
            this.$refs.createTemp && this.$refs.createTemp.reset()
          })
        },
        // 创建模板
        createTemplate () {
          this.visible = true
          this.formData.type = '0'
          this.optType = 'create'
          this.addTip = window.lang.create_template
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const res = await createTemplate(this.optType, this.formData)
              this.$message.success(res.data.msg)
              this.getSmsList()
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
        async updateHandler (row) {
          try {
            if (row.status === 1) {
              return
            }
            this.optType = 'edit'
            this.addTip = window.lang.edit_template
            const params = {
              name: this.name,
              id: row.id
            }
            const res = await getSmsTemplateDetail(params)
            const temp = res.data.data
            this.formData.id = row.id
            this.formData.type = String(temp.type)
            this.formData.status = String(temp.status)
            this.formData.template_id = temp.template_id
            this.formData.title = temp.title
            this.formData.content = temp.content
            this.formData.notes = temp.notes
            this.visible = true
          } catch (error) {
          }
        },
        // 删除
        deleteHandler (row) {
          this.delVisible = true
          this.delId = row.id
        },
        async sureDel () {
          try {
            const params = {
              name: this.name,
              id: this.delId
            }
            const res = await deleteSmsTemplate(params)
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getSmsList()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
