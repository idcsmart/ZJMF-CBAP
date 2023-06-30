(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('server')[0]
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
              width: 110,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'name',
              title: lang.interface_name,
              className: 'name-status',
              width: 300,
              ellipsis: true
            },
            {
              colKey: 'type',
              title: lang.server_module,
              width: 200,
              ellipsis: true
            },
            {
              colKey: 'group_name',
              title: lang.interface_group_name,
              width: 200,
              ellipsis: true
            },
            {
              colKey: 'ip_address',
              title: `IP${lang.address}`,
              width: 120,
              ellipsis: true
            },
            {
              colKey: 'pro_name',
              title: 'title-slot-name',
              align: 'center',
              width: 150
            },
            {
              colKey: 'status',
              title: lang.open_status,
              width: 110
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
            sort: 'desc',
            status: ''
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
          formData: { // 添加接口
            name: '',
            ip_address: '',
            type: '',
            max_accounts: '',
            hostname: '',
            gid: '',
            username: '',
            password: '',
            port: '',
            secure: 1,
            disabled: 0,
            hash: ''
          },
          options: [
            {
              value: 1,
              label: lang.enable,
            },
            {
              value: 0,
              label: lang.disable,
            },
          ],
          rules: {
            name: [
              { required: true, message: lang.input + lang.interface_name, type: 'error' },
              { validator: val => val.length <= 50, message: lang.verify3 + 50, type: 'warning' }
            ],
            type: [{ required: true, message: lang.select + lang.server_module, type: 'error' }],
            ip_address: [
              { required: true, message: lang.tip7, type: 'error' },
              // {
              //   pattern: /(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?/, type: 'warning'
              // }
              {
                pattern: /^((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.){3}(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])(?::(?:[0-9]|[1-9][0-9]{1,3}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5]))?$/, type: 'warning'
              }
            ],
            max_accounts: [
              { required: true, message: lang.input + lang.interface_capacity, type: 'error', },
              {
                pattern: /^([1-9][0-9]*)$/, message: lang.input + lang.verify16, type: 'warning'
              }
            ],
            username: [
              { validator: val => val.length <= 100, message: lang.verify3 + 100, type: 'warning' }
            ],
            password: [
              { validator: val => val.length <= 100, message: lang.verify3 + 100, type: 'warning' }
              // { pattern: /^[\w@!#$%^&*()+-_]{0,100}$/, message: lang.verify8 + '0~100，' + lang.verify14}
            ],
          },
          loading: false,
          country: [],
          delId: '',
          title: '',
          typeList: [],
          maxHeight: '',
          type: '', // create update
          adminStatus: [
            { value: 0, label: lang.deactivate },
            { value: 1, label: lang.enable },
          ],
          groupList: []
        }
      },
      computed: {
        calcName () {
          return (module) => {
            const temp = this.typeList.filter(item => item.name === module)
            return temp[0]?.display_name
          }
        }
      },
      mounted () {
        this.maxHeight = document.getElementById('content').clientHeight - 170
        let timer = null
        window.onresize = () => {
          if (timer) {
            return
          }
          timer = setTimeout(() => {
            this.maxHeight = document.getElementById('content').clientHeight - 170
            clearTimeout(timer)
            timer = null
          }, 300)
        }
      },
      created () {
        this.getInterfaceList()
        this.getTypeList()
        this.getGroupList()
        document.title = lang.child_module + '-' + localStorage.getItem('back_website_name')
      },
      methods: {
        async getGroupList () {
          try {
            const res = await getChildGroup()
            this.groupList = res.data.data.list
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        jumpStore () {
          window.open('https://my.idcsmart.com/shop/shop_app.html?appType=server', '_blank')
        },
        toChild () {
          location.href = 'child_server.html'
        },
        // 获取单个状态
        async getSingleStatus (row) {
          try {
            row.linkStatus = null
            const res = await getChildInterfaceStatus(row.id)
            this.data.forEach((item, index) => {
              if (item.id === row.id) {
                item.linkStatus = res.data.data.server_status
                item.fail_reason = res.data.data.msg
              }
            })
          } catch (error) {
            this.data.forEach((item, index) => {
              if (item.id === row.id) {
                item.linkStatus = error.data.data?.server_status
                item.fail_reason = error.data.data?.msg
              }
            })
          }
        },
        async getTypeList () {
          try {
            const res = await getChildInterfaceType(this.params)
            this.loading = false
            this.typeList = res.data.data.modules
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 获取列表
        async getInterfaceList () {
          try {
            this.loading = true
            const res = await getChildInterface(this.params)
            const temp = res.data.data
            temp.list.forEach(item => {
              item.linkStatus = null
              item.fail_reason = ''
              this.getSingleStatus(item)
            })
            this.data = temp.list
            this.total = temp.count
            this.loading = false
          } catch (error) {
            this.loading = false
          }
        },
        // 切换分页
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getInterfaceList()
        },
        // 排序
        sortChange (val) {
          if (!val) {
            return
          }
          this.params.orderby = val.sortBy
          this.params.sort = val.descending ? 'desc' : 'asc'
          this.getInterfaceList()
        },
        clearKey () {
          this.params.keywords = ''
          this.seacrh()
        },
        seacrh () {
          this.params.page = 1
          this.getInterfaceList()
        },
        changeStatus (status) {
          this.formData.status = Number(status)
        },
        // 添加接口
        addUser () {
          this.type = 'create'
          this.title = lang.create_interface
          this.visible = true
          this.$refs.form.reset()
          this.formData.secure = 1
          this.formData.disabled = 0
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const res = await addAndUpdateChildInterface(this.type, this.formData)
              this.$message.success(res.data.msg)
              this.getInterfaceList()
              this.visible = false
            } catch (error) {
              this.$message.error(error.data.msg)
            }
          } else {
            console.log('Errors: ', validateResult)
            this.$message.warning(firstError)
          }
        },
        close () {
          this.visible = false
          this.$refs.form.clearValidate()
        },
        // 编辑
        async updateHandler (row) {
          try {
            this.title = lang.edit_interface
            this.type = 'update'
            this.formData = JSON.parse(JSON.stringify(row))
            this.formData.type = this.formData.type.toLocaleLowerCase()
            this.visible = true
          } catch (error) {

          }
        },
        // 删除接口
        deleteUser (row) {
          this.delVisible = true
          this.delId = row.id
        },
        async sureDel () {
          try {
            const res = await deleteChildInterface(this.delId)
            this.$message.success(res.data.msg)
            this.params.page = this.data.length > 1 ? this.params.page : this.params.page - 1
            this.delVisible = false
            this.getInterfaceList()
          } catch (error) {
            this.delVisible = false
            this.$message.error(error.data.msg)
          }
        }
      }
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  }
})(window)
