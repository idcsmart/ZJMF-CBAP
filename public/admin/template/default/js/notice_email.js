(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('notice-email')[0]
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
              width: 100,
              ellipsis: true
            },
            {
              colKey: 'title',
              title: lang.interface_name,
              width: 700,
              ellipsis: true,
            },
            {
              colKey: 'author',
              title: lang.author,
              width: 200,
              ellipsis: true
            },
            {
              colKey: 'version',
              title: lang.version,
              width: 80,
              ellipsis: true
            },
            {
              colKey: 'status',
              title: lang.status,
              width: 80,
              ellipsis: true
            },
            {
              colKey: 'op',
              title: lang.manage,
              width: 120
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
          formData: { // 添加用户
            username: '',
            email: '',
            phone_code: '',
            phone: '',
            password: '',
            repassword: ''
          },
          rules: {
            username: [{ required: true, message: window.lang.required, type: 'error' }],
            // phone: [{ required: true, message: window.lang.required, type: 'warning'}],
            // email: [
            //   { required: false, pattern: /^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{2,9})$/, trigger: 'blur' }
            // ],
            password: [{ required: true, message: window.lang.required, type: 'error' }],
            repassword: [
              { required: true, message: window.lang.required, type: 'error' },
              { validator: checkPwd2, trigger: 'blur' }
            ],
          },
          loading: false,
          country: [],
          delId: '',
          curStatus: 1,
          statusTip: '',
          addTip: '',
          roleList: [],
          configTip: '',
          configData: [],
          installTip: '',
          configVisble: false,
          name: '', // 插件标识
          type: '', // 安装/卸载
          module: 'mail' // 当前模块
        }
      },
      methods: {
        // 配置
        handleConfig (row) {
          this.configVisble = true
          this.name = row.name
          this.getConfig(row.id)
        },
        async getConfig (id) {
          try {
            const params = {
              module: this.module,
              name: this.name,
              id
            }
            const res = await getMoudleConfig(params)
            this.configData = res.data.data.plugin.config
            this.configTip = res.data.data.plugin.title
            this.configVisble = true
          } catch (error) {

          }
        },
        // 保存配置
        async onSubmit () {
          try {
            const params = {
              module: this.module,
              name: this.name,
              config: {}
            }
            for (const i in this.configData) {
              params.config[this.configData[i].field] = this.configData[i].value
            }
            const res = await saveMoudleConfig(params)
            this.$message.success(res.data.msg)
            this.configVisble = false
            this.getGatewayList()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        jump () {
          location.href = `notice_email_template.html`
        },
        getMore () {
          location.href = ''
        },
        // 获取列表
        async getEmailList () {
          try {
            this.loading = true
            const params = {
              module: this.module
            }
            const res = await getMoudle(params)
            this.loading = false
            this.data = res.data.data.list
            this.total = res.data.data.count
          } catch (error) {
            this.loading = false
          }
        },
        // 切换分页
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getEmailList()
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
          this.$refs.userDialog.reset()
        },
        sendManage () {
          location.href = '/notice_send.html'
        },
        // 停用/启用
        changeStatus (row) {
          this.name = row.name
          this.curStatus = row.status
          this.statusTip = this.curStatus ? window.lang.sureDisable : window.lang.sure_Open
          this.statusVisble = true
        },
        async sureChange () {
          try {
            let tempStatus = this.curStatus === 1 ? 0 : 1
            const params = {
              module: this.module,
              name: this.name,
              status: tempStatus
            }
            const res = await changeMoudle(params)
            this.$message.success(res.data.msg)
            this.statusVisble = false
            this.getEmailList()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        closeDialog () {
          this.statusVisble = false
        },

        // 卸载/安装
        installHandler (row) {
          this.delVisible = true
          this.name = row.name
          this.type = row.status === 3 ? 'install' : 'uninstall'
          this.installTip = this.type === 'install' ? window.lang.sureInstall : window.lang.sureUninstall
        },
        async sureDel () {
          try {
            const params = {
              type: this.type,
              module: this.module,
              name: this.name
            }
            const res = await deleteMoudle(params)
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getEmailList()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        cancelDel () {
          this.delVisible = false
        },

      },
      created () {
        this.getEmailList()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
