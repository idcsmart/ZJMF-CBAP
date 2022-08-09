(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('addon')[0]
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
              width: 80,
              ellipsis: true
            },
            {
              colKey: 'status',
              title: lang.status,
              width: 120
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 100,
              fixed: 'right'
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
          installTip: ''
        }
      },
      mounted () {
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
      created () {
        this.getAddonList()
      },
      methods: {
        // 获取列表
        async getAddonList () {
          try {
            this.loading = true
            const res = await getAddon(this.params)
            this.loading = false
            this.data = res.data.data.list
            this.total = res.data.data.count
          } catch (error) {
            this.loading = false
            this.$message.error(error.data.msg)
          }
        },
        // 切换分页
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.params.keywords = ''
          this.getAddonList()
        },
        // 排序
        sortChange (val) {
          if (!val) {
            this.params.orderby = 'id'
            this.params.sort = 'desc'
          } else {
            this.params.orderby = val.sortBy
            this.params.sort = val.descending ? 'desc' : 'asc'
          }
          this.getAddonList()
        },
        clearKey () {
          this.params.keywords = ''
          this.seacrh()
        },
        seacrh () {
          this.params.page = 1
          this.getAddonList()
        },

        close () {
          this.visible = false
          this.$refs.userDialog.reset()
        },
        // 查看用户详情
        // handleClickDetail (row) {
        //   location.href = `client_detail.html?id=${row.id}`
        // },
        // 停用/启用
        changeStatus (row) {
          this.delId = row.id
          this.curStatus = row.status
          this.curName = row.name
          this.statusTip = this.curStatus ? lang.sureDisable : lang.sure_Open
          this.statusVisble = true
        },
        async sureChange () {
          try {
            let tempStatus = this.curStatus === 1 ? 0 : 1
            const res = await changeAddonStatus({ name: this.curName, status: tempStatus })
            this.$message.success(res.data.msg)
            this.statusVisble = false
            this.getAddonList()
          } catch (error) {
            console.log(error)
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
            const res = await deleteMoudle(this.type, this.name)
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getAddonList()
          } catch (error) {
              console.log(error)
            this.$message.error(error.data)
          }
        },
        cancelDel () {
          this.delVisible = false
        },
      }
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
