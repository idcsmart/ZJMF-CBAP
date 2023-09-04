(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('notice-sms')[0]
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
          configVisble: false,
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
              width: 500,
              ellipsis: true,
            },
            {
              colKey: 'author',
              title: lang.author,
              width: 200,
              ellipsis: true
            },
            {
              colKey: 'sms_type',
              title: lang.support_direction,
              width: 100,
              ellipsis: true
            },
            {
              colKey: 'version',
              title: lang.version,
              width: 120,
              ellipsis: true,
              className: 'version'
            },
            {
              colKey: 'status',
              title: lang.isOpen,
              width: 80,
              ellipsis: true
            },
            {
              colKey: 'op',
              title: lang.manage,
              width: 140,
              ellipsis: true
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
          },
          loading: false,
          country: [],
          delId: '',
          curStatus: 1,
          statusTip: '',
          addTip: '',
          roleList: [],
          installTip: '',
          configTip: '',
          configData: [],
          urlPath: url,
          name: '', // 插件标识
          type: '', // 安装/卸载
          module: 'sms', // 当前模块
          upVisible: false,
          curName: '',
          upLoading: false
        }
      },
      computed: {
        enableTitle () {
          return (status) => {
            if (status === 1) {
              return lang.disable
            } else if (status === 0) {
              return lang.enable
            }
          }
        },
        installTitle () {
          return (status) => {
            if (status === 3) {
              return lang.install
            } else {
              return lang.uninstall
            }
          }
        }
      },
      methods: {
        jump (row) {
          location.href = `notice_sms_template.htm?name=${row.name}`
        },
        getMore () {
          // window.open('https://market.idcsmart.com/shop/#/home-page')
          setToken().then(res => {
            if (res.data.status == 200) {
              let url = res.data.market_url
              let getqyinfo = url.split('?')[1]
              let getqys = new URLSearchParams('?' + getqyinfo)
              const from = getqys.get('from')
              const token = getqys.get('token')

              window.open(`https://my.idcsmart.com/shop/index.html?from=${from}&token=${token}`)
            }
          })
        },
        // 获取列表
        async getSmsList () {
          try {
            this.loading = true
            const params = {
              module: this.module
            }
            const res = await getMoudle(params)
            this.loading = false
            this.data = res.data.data.list
            this.total = res.data.data.count
            this.getNewVersion()
          } catch (error) {
            this.loading = false
          }
        },
        /* 升级 start */
        // 获取最新版本
        async getNewVersion () {
          try {
            const res = await getActiveVersion()
            const temp = res.data.data.list.filter(item => item.type === this.module)
            const arr = temp.reduce((all, cur) => {
              all.push(cur.uuid)
              return all
            }, [])
            if (arr.length > 0) {
              this.data = this.data.map(item => {
                item.isUpdate = false
                if (arr.includes(item.name)) {
                  const cur = temp.filter(el => el.uuid === item.name)[0]
                  item.isUpdate = this.checkVersion(cur?.old_version, cur?.version)
                }
                return item
              })
            }
          } catch (error) {

          }
        },
        /**
       *
       * @param {string} nowStr 当前版本
       * @param {string} lastStr 最新版本
       */
        // 对比版本，是否显示升级
        checkVersion (nowStr, lastStr) {
          const nowArr = nowStr.split('.')
          const lastArr = lastStr.split('.')
          let hasUpdate = false
          const nowLength = nowArr.length
          const lastLength = lastArr.length

          const length = Math.min(nowLength, lastLength)
          for (let i = 0; i < length; i++) {
            if (lastArr[i] - nowArr[i] > 0) {
              hasUpdate = true
            }
          }
          if (!hasUpdate && lastLength - nowLength > 0) {
            hasUpdate = true
          }
          return hasUpdate
        },
        updatePlugin (row) {
          this.upVisible = true
          this.curName = row.name
        },
        // 提交升级
        async sureUpgrade () {
          try {
            this.upLoading = true
            const res = await upgradePlugin({
              module: this.module,
              name: this.curName
            })
            this.$message.success(res.data.msg)
            this.upVisible = false
            this.upLoading = false
            this.getSmsList()
          } catch (error) {
            this.upLoading = false
            this.upVisible = false
            this.$message.error(error.data.msg)
          }
        },
        /* 升级 end */
        // 切换分页
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getSmsList()
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
          this.$refs.userDialog.reset()
        },
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
            this.getSmsList()
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
          this.installTip = this.type === 'install' ? lang.sureInstall : lang.sureUninstall
        },
        async sureDel () {
          try {
            const params = {
              module: this.module,
              name: this.name
            }
            const res = await deleteMoudle(this.type, params)
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getSmsList()
          } catch (error) {
            this.delVisible = false
            this.$message.error(error.data.msg)
          }
        },
        cancelDel () {
          this.delVisible = false
        },

      },
      created () {
        this.getSmsList()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
