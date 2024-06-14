(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('server-group')[0]
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    new Vue({
      components: {
        comConfig,
      },
      data () {
        return {
          submitLoading: false,
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
              title: lang.group_name,
              ellipsis: true,
              width: 200
            },
            {
              colKey: 'pro_name',
              title: 'title-slot-name',
              align: 'center',
            },
            {
              colKey: 'mode',
              title: lang.distribution,
              ellipsis: true,
              width: 300
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 120,
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
          interfaceTotal: 0,
          pageSizeOptions: [20, 50, 100],
          formData: { // 添加接口
            id: '',
            name: '',
            mode: 1,
            server_ids: []
          },
          options: [
            {
              value: 1,
              label: lang.child_mode1,
              tip: lang.child_tip3
            },
            {
              value: 2,
              label: lang.child_mode2,
              tip: lang.child_tip4
            },
          ],
          rules: {
            name: [
              { required: true, message: lang.input + lang.group_name, type: 'error' },
              {
                validator: val => val.length <= 50, message: lang.verify3 + 50, type: 'warning'
              }
            ],
            server_id: [{ required: true, message: lang.select + lang.interface, type: 'error' }]
          },
          loading: false,
          country: [],
          delId: '',
          title: '',
          originList: [], // 原始数据
          interfaceList: [], // 接口下拉的选择
          createList: [], // 新建的时候筛选出没有分配的接口
          updateList: [], // 编辑的时候筛选没有分配和当前已分配的选项
          type: '', // create update
          interfaceParams: {
            page: 1,
            limit: 1000
          },
          tempArr: [],
          maxHeight: '',
          popupProps: {
            overlayInnerStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` }),
          }
        }
      },
      created () {
        this.getGroupList()
        this.getInterface()
        document.title = lang.child_module + '-' + localStorage.getItem('back_website_name')
      },
      methods: {
        async getInterface () {
          try {
            const res = await getChildInterface(this.interfaceParams)
            this.interfaceTotal = res.data.data.count
            res.data.data.list.forEach(item => {
              this.originList.push(item)
            })
            this.createList = this.originList.filter(item => {
              // 筛选出未被占用的接口
              return !item.group_name
            })
          } catch (error) {
          }
        },
        // 获取列表
        async getGroupList () {
          try {
            this.loading = true
            const res = await getChildGroup(this.params)
            this.loading = false
            this.data = res.data.data.list
            this.total = res.data.data.count
          } catch (error) {
            console.log('Eerror', error)
            this.loading = false
          }
        },
        // 切换分页
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getGroupList()
        },
        // 排序
        sortChange (val) {
          if (!val) {
            return
          }
          this.params.orderby = val.sortBy
          this.params.sort = val.descending ? 'desc' : 'asc'
          this.getGroupList()
        },
        clearKey () {
          this.params.keywords = ''
          this.search()
        },
        search () {
          this.params.page = 1
          this.getGroupList()
        },
        changeStatus (status) {
          this.formData.status = Number(status)
        },
        // 添加接口
        addUser () {
          this.type = 'create'
          this.title = lang.create_group
          this.createList = this.originList.filter(item => {
            // 筛选出未被占用的接口
            return !item.group_name
          })
          this.visible = true
          this.$refs.userDialog.reset()
          this.formData.mode = 1
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const temp = JSON.parse(JSON.stringify(this.formData))
              if (this.type === 'create') {
                delete temp.id
              }
              this.submitLoading = true
              const res = await addAndUpdateChildGroup(this.type, temp)
              this.$message.success(res.data.msg)
              this.interfaceTotal = 0
              this.interfaceParams.page = 1
              this.originList = []
              this.getGroupList()
              this.getInterface()
              this.visible = false
              this.submitLoading = false
            } catch (error) {
              this.submitLoading = false
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult)
            this.$message.warning(firstError)
          }
        },
        close () {
          this.visible = false
          this.$refs.userDialog && this.$refs.userDialog.reset()
        },
        // 编辑
        async updateHandler (row) {
          try {
            const res = await getChildGroupDetails(row.id)
            const temp = res.data.data
            this.title = lang.edit_group
            this.createList = temp.servers
            this.formData.id = row.id
            this.formData.server_ids = temp.select_servers
            this.formData.name = temp.server_group?.name
            this.formData.mode = row.mode
            this.type = 'update'
            this.visible = true
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 删除接口
        deleteUser (row) {
          this.delVisible = true
          this.delId = row.id
        },
        async sureDel () {
          try {
            this.submitLoading = true
            const res = await deleteChildGroup(this.delId)
            this.$message.success(res.data.msg)
            this.params.page = this.data.length > 1 ? this.params.page : this.params.page - 1
            this.delVisible = false
            this.getGroupList()
            this.getInterface()
            this.submitLoading = false
          } catch (error) {
            this.submitLoading = false
            this.delVisible = false
            this.$message.error(error.data.msg)
          }
        }
      }
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  }
})(window)
