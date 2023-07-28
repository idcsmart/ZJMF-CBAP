(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('addon')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data() {
        return {
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          statusVisble: false,
          hover: true,
          urlPath: url,
          columns: [
            {
              colKey: 'id',
              title: 'ID',
              width: 65,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'title',
              title: lang.plug_name,
              width: 500,
              ellipsis: true
            },
            {
              colKey: 'author',
              title: lang.author,
              width: 500,
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
              title: lang.status,
              width: 120
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 100
            },
          ],
          hideSortTips: true,
          params: {
            keywords: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
          rules: {
            username: [{ required: true, message: lang.input + lang.name, type: 'error' }]
          },
          loading: false,
          country: [],
          delId: '',
          curStatus: 1,
          statusTip: '',
          maxHeight: '',
          curName: '',
          installTip: '',
          authList: JSON.parse(JSON.stringify(localStorage.getItem('backAuth'))),
          module: 'addon', // 当前模块
          upVisible: false,
          curName: '',
          upLoading: false,
          pluginUpgrade: false,
          syncVisible: false,
          syncPluginList: [],
          btnLoading: false,
          pluginColumns: [
            {
              colKey: 'name',
              title: `${lang.plugin}${lang.nickname}`,
              ellipsis: true,
            },
            {
              colKey: 'version',
              title: `${lang.plugin}${lang.version}`,
              ellipsis: true
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 50
            }
          ]
        }
      },
      computed: {
        enableTitle() {
          return (status) => {
            if (status === 1) {
              return lang.disable
            } else if (status === 0) {
              return lang.enable
            }
          }
        },
        installTitle() {
          return (status) => {
            if (status === 3) {
              return lang.install
            } else {
              return lang.uninstall
            }
          }
        }
      },
      mounted() {
        this.maxHeight = document.getElementById('content').clientHeight - 180
        let timer = null
        window.onresize = () => {
          if (timer) {
            return
          }
          timer = setTimeout(() => {
            this.maxHeight = document.getElementById('content').clientHeight - 180
            clearTimeout(timer)
            timer = null
          }, 300)
        }
      },
      created() {
        // 权限相关
        if (!this.authList.includes('PluginController::pluginList')) {
          return this.$message.error(lang.tip17 + ',' + lang.tip18)
        }
        this.getAddonList();
        document.title = lang.plugin_list + '-' + localStorage.getItem('back_website_name');
      },
      methods: {
        /* 同步插件 */
        async getSystem () {
          try {
            this.btnLoading = true
            const res = await getSysyemVersion()
            if (res.data.data.license) { // 存在授权码
              this.syncPlugin()
            } else {
              this.btnLoading = false
              this.toMarket()
            }
          } catch (error) {
            this.btnLoading = false
            this.$message.error(error.data.msg)
          }
        },
        async syncPlugin () {
          try {
            this.btnLoading = true
            const res = await syncPlugins()
            this.syncPluginList = res.data.data.list
            this.syncVisible = true
            this.btnLoading = false
          } catch (error) {
            this.btnLoading = false
          }
        },
        async handlerDownload (row) {
          try {
            const res = await downloadPlugin(row.id)
            this.$message.success(res.data.msg)
            this.syncPlugin()
            setTimeout(() => {
              this.getAddonList()
            }, 0)
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },

        /* 同步插件 end */
        // 获取列表
        async getAddonList() {
          try {
            this.loading = true
            const res = await getAddon(this.params)
            this.loading = false
            this.data = res.data.data.list
            this.total = res.data.data.count
            // 获取最新版本
            this.getNewVersion()
          } catch (error) {
            this.loading = false
            this.$message.error(error.data.msg)
          }
        },
        /* 升级 start */
        // 获取最新版本
        async getNewVersion(refresh = false) {
          try {
            const res = await getActiveVersion()
            this.pluginUpgrade = res.data.data.upgrade === 1
            sessionStorage.setItem('pluginUpgrade', this.pluginUpgrade)
            if (refresh) {
              // 刷新页面
              window.location.reload()
            }
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
        checkVersion(nowStr, lastStr) {
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
        updatePlugin(row) {
          this.upVisible = true
          this.curName = row.name
        },
        // 提交升级
        async sureUpgrade() {
          try {
            this.upLoading = true
            const res = await upgradePlugin({
              module: this.module,
              name: this.curName
            })
            this.$message.success(res.data.msg)
            this.upVisible = false
            this.upLoading = false
            this.getAddonList()
            this.getNewVersion(true)
          } catch (error) {
            this.upLoading = false
            this.upVisible = false
            this.$message.error(error.data.msg)
          }
        },
        /* 升级 end */
        // 切换分页
        changePage(e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.params.keywords = ''
          this.getAddonList()
        },
        // 排序
        sortChange(val) {
          if (!val) {
            this.params.orderby = 'id'
            this.params.sort = 'desc'
          } else {
            this.params.orderby = val.sortBy
            this.params.sort = val.descending ? 'desc' : 'asc'
          }
          this.getAddonList()
        },
        clearKey() {
          this.params.keywords = ''
          this.seacrh()
        },
        seacrh() {
          this.params.page = 1
          this.getAddonList()
        },

        close() {
          this.visible = false
          this.$refs.userDialog.reset()
        },
        // 查看用户详情
        // handleClickDetail (row) {
        //   location.href = `client_detail.htm?id=${row.id}`
        // },
        // 停用/启用
        changeStatus(row) {
          this.delId = row.id
          this.curStatus = row.status
          this.curName = row.name
          this.statusTip = this.curStatus ? lang.sureDisable : lang.sure_Open
          this.statusVisble = true
        },
        async sureChange() {
          try {
            let tempStatus = this.curStatus === 1 ? 0 : 1
            const res = await changeAddonStatus({ name: this.curName, status: tempStatus })
            this.$message.success(res.data.msg)
            this.statusVisble = false
            // this.getAddonList()
            // 获取导航
            const menus = await getMenus()
            localStorage.setItem('backMenus', JSON.stringify(menus.data.data.menu))
            window.location.reload()
          } catch (error) {
            console.log(error)
          }
        },
        closeDialog() {
          this.statusVisble = false
        },

        // 卸载/安装
        installHandler(row) {
          this.delVisible = true
          this.name = row.name
          this.type = row.status === 3 ? 'install' : 'uninstall'
          this.installTip = this.type === 'install' ? lang.sureInstall : lang.sureUninstall
        },
        async sureDel() {
          try {
            const res = await deleteMoudle(this.type, this.name)
            this.$message.success(res.data.msg)
            this.delVisible = false
            // this.getAddonList()
            // 获取导航
            const menus = await getMenus()
            localStorage.setItem('backMenus', JSON.stringify(menus.data.data.menu))
            window.location.reload()
          } catch (error) {
            console.log(error)
            this.$message.error(error.data.msg)
          }
        },
        cancelDel() {
          this.delVisible = false
        },
        toMarket() {
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
        }
      }
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
