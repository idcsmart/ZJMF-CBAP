(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('withdrawal')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
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
          virtualScroll: false,
          sourceModal: false,
          columns: [
            {
              colKey: 'id',
              title: lang.order_index,
              width: 90,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'source',
              title: lang.withdrawal_source,
              width: 500,
              ellipsis: true
            },
            {
              colKey: 'admin',
              title: lang.order_poster,
              width: 150,
              ellipsis: true
            },
            {
              colKey: 'create_time',
              title: lang.time_application,
              width: 300,
              ellipsis: true
            },
            {
              colKey: 'status',
              title: lang.open_close,
              width: 110,
              ellipsis: true
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 80,
              ellipsis: true
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
          formData: { // 驳回审核
            reason: ''
          },
          rules: {
            reason: [
              { required: true, message: lang.input + lang.dismiss_the_reason, type: 'error' },
              { validator: val => val.length <= 100, message: lang.verify3 + 100, type: 'warning' }
            ]
          },
          loading: false,
          country: [],
          delId: '',
          curStatus: 1,
          statusTip: '',
          addTip: '',
          langList: [],
          roleTotal: 0,
          roleList: [],
          optType: 'create',
          curId: '',
          roleParams: {
            page: 1,
            limit: 20
          },
          popupProps: {
            overlayStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` })
          },
          maxHeight: '',
          // 原始插件列表数据
          pluginList: [],
          // 左侧数据源
          originList: [],
          checkList: [],
          selectedRowKeys: [],
          checkId: [],
          // 已选数据来源
          sourceList: [],
          // 本地搜索的数据源
          searchList: [],
          originColumns: [
            {
              colKey: 'row-select',
              type: 'multiple',
              className: 'demo-multiple-select-cell',
              width: 30
            },
            { colKey: 'id', title: 'ID', width: 50 },
            { colKey: 'title', title: lang.nickname, ellipsis: true },
            { colKey: 'author', title: lang.author, ellipsis: true, width: 120 },
            { colKey: 'version', title: lang.version, width: 50 }
          ],
          checkColumns: [
            { colKey: 'id', title: 'ID', width: 50 },
            { colKey: 'title', title: lang.nickname, ellipsis: true, width: 150 },
            { colKey: 'author', title: lang.author, ellipsis: true, width: 120 },
            { colKey: 'version', title: lang.version, width: 50 },
            { colKey: 'op', width: 40, className: 'delItem', align: 'right' },
          ],
          pluginKey: ''
        }
      },
      mounted () {
        this.maxHeight = document.getElementById('content').clientHeight - 220
        let timer = null
        window.onresize = () => {
          if (timer) {
            return
          }
          timer = setTimeout(() => {
            this.maxHeight = document.getElementById('content').clientHeight - 220
            clearTimeout(timer)
            timer = null
          }, 300)
        }
      },
      methods: {
        checkPwd (val) {
          if (val !== this.formData.password) {
            return { result: false, message: window.lang.password_tip, type: 'error' };
          }
          return { result: true }
        },
        // 获取插件列表
        async getPlugin () {
          try {
            const res = await getAddon()
            const temp = res.data.data.list
            this.searchList = this.pluginList = this.originList = temp.filter(item => item.status !== 3)
          } catch (error) {

          }
        },
        // 获取已添加的数据来源
        async getSourceList () {
          try {
            const res = await getSource()
            this.sourceList = res.data.data.source
          } catch (error) {

          }
        },
        rehandleSelectChange (value, { selectedRowData }) {
          this.checkId = value
          this.selectedRowKeys = selectedRowData;
        },
        // 移动数据
        transferData () {
          if (this.originList.length === 0) {
            return
          }
          this.checkList = this.checkList.concat(this.selectedRowKeys)
          const arr = this.checkList.reduce((all, cur) => {
            all.push(cur.id)
            return all
          }, [])
          this.searchList = this.originList = this.pluginList.filter(item => !arr.includes(item.id))
        },
        // 移除数据
        delItem (id) {
          this.checkList = this.checkList.filter(item => item.id !== id)
          const temp = this.pluginList.filter(item => item.id === id)
          this.searchList = this.originList = this.originList.concat(temp).sort((a, b) => {
            return a.id - b.id
          })
          this.checkId = []
        },
        // 提交来源
        async sureSubmit () {
          try {
            const source = this.checkList.reduce((all, cur) => {
              all.push(cur.name)
              return all
            }, [])
            const res = await submitSource({ source })
            this.$message.success(res.data.msg)
            this.sourceModal = false
            this.getSourceList()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 获取列表
        async getList () {
          try {
            this.loading = true
            const res = await getWithdrawalRules(this.params)
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
          this.getList()
        },
        // 跳转
        jump () {
          location.href = `withdrawal_create.html`
        },
        // 编辑规则详情
        editHandler (row) {
          location.href = `withdrawal_update.html?id=${row.id}`
        },
        // 删除规则
        deleteHandler (row) {
          this.delId = row.id
          this.delVisible = true
        },
        // 删除流水
        async sureDelUser () {
          try {
            const res = await deleteRules(this.delId)
            this.$message.success(res.data.msg)
            this.delVisible = false
            this.getList()
          } catch (error) {
            this.$message.error(error)
          }
        },
        // 规则来源管理
        sourceHandel () {
          this.sourceModal = true
          this.checkId = []
          this.selectedRowKeys = []
          // 过滤掉已经选择的数据来源
          const temp = this.sourceList.reduce((all, cur) => {
            all.push(cur.name)
            return all
          }, [])
          this.originList = this.pluginList.filter(item => !temp.includes(item.name))
          this.checkList = this.pluginList.filter(item => temp.includes(item.name))
        },
        // 排序
        sortChange (val) {
          if (val === undefined) {
            this.params.orderby = 'id'
            this.params.sort = 'desc'
          } else {
            this.params.orderby = val.sortBy
            this.params.sort = val.descending ? 'desc' : 'asc'
          }
          this.getList()
        },
        // 切换状态
        clearKey () {
          this.params.keywords = ''
          this.seacrh()
        },
        seacrh () {
          this.params.page = 1
          this.getList()
        },
        close () {
          this.visible = false
          this.$nextTick(() => {
            this.$refs.userDialog && this.$refs.userDialog.reset()
          })
        },
        // 本地搜索插件
        seacrhPlugin () {
          this.originList = this.searchList.filter(item=> item.title.indexOf(this.pluginKey) !== -1)
        },
        clearPluginKey () {
          this.pluginKey = ''
          this.originList = this.searchList
        },

        // 开启/关闭
        async changeStatus (row) {
          try {
            const params = {
              id: row.id,
              status: row.status
            }
            const res = await changeRuleStatus(params)
            this.$message.success(res.data.msg)
            this.getList()
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        // 驳回审核
        rejectHandler (row) {
          this.visible = true
          this.delId = row.id
          this.addTip = lang.approved_reject
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = {
                id: this.delId,
                status: 2,
                reason: this.formData.reason
              }
              console.log(params)
              const res = await changeStatus(params)
              this.$message.success(res.data.msg)
              this.getList()
              this.visible = false
              this.$refs.userDialog.reset()
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult);
            this.$message.warning(firstError);
          }
        },
        // 审核通过
        passHandler (row) {
          this.delId = row.id
          this.statusTip = lang.sure + lang.approved + '?'
          this.statusVisble = true
        },
        async sureChange () {
          try {
            const params = {
              id: this.delId,
              status: 1
            }
            const res = await changeStatus(params)
            this.$message.success(res.data.msg)
            this.statusVisble = false
            this.getList()
          } catch (error) {
            this.$message.error(error.data.msg)
            this.statusVisble = false
          }
        },
        closeDialog () {
          this.statusVisble = false
        }
      },
      created () {
        this.getList()
        this.getPlugin()
        this.getSourceList()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
