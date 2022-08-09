(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('admin')[0]
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
          virtualScroll: false,
          columns: [
            {
              colKey: 'id',
              title: 'ID',
              width: 90,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'name',
              title: lang.username,
              width: 300,
              ellipsis: true
            },
            {
              colKey: 'email',
              title: lang.email,
              width: 300,
              ellipsis: true
            },
            {
              colKey: 'nickname',
              title: lang.name,
              width: 300,
              ellipsis: true
            },
            {
              colKey: 'roles',
              title: lang.belong_group,
              width: 300,
              ellipsis: true
            },
            {
              colKey: 'status',
              title: lang.status,
              width: 110,
              ellipsis: true
            },
            {
              colKey: 'op',
              title: lang.operation,
              width: 170,
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
          formData: { // 添加用户
            name: '',
            password: '',
            repassword: '',
            email: '',
            nickname: '',
            role_id: ''
          },
          rules: {
            name: [
              { required: true, message: lang.input + lang.username, type: 'error' },
              { validator: val => val.length <= 50, message: lang.verify3 + 50, type: 'warning'}
            ],
            email: [
              { required: true, message: lang.input + lang.email, trigger: 'blur', type: 'error' },
              { pattern: /^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{1,9})$/, message: lang.email_tip, type: 'warning' }
            ],
            nickname: [
              { required: true, message: lang.input + lang.nickname, trigger: 'blur' },
              { validator: val => val.length <= 20, message: lang.verify3 + 20, type: 'warning'}
            ],
            role_id: [
              { required: true, message: lang.input + lang.group, trigger: 'blur' }
            ],
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
          maxHeight: ''
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
        // 获取列表
        async getAdminList () {
          try {
            this.loading = true
            const res = await getAdminList(this.params)
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
          this.getAdminList()
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
          this.getAdminList()
        },
        clearKey () {
          this.params.keywords = ''
          this.seacrh()
        },
        seacrh () {
          this.params.page = 1
          this.getAdminList()
        },
        close () {
          this.visible = false
          this.$nextTick(() => {
            this.$refs.userDialog && this.$refs.userDialog.reset()
          })
        },
        // 添加管理员
        addUser () {
          this.optType = 'create'
          this.visible = true
          this.addTip = window.lang.add + window.lang.admin
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = { ...this.formData }
              if (params.password === '') {
                delete params.password
              }
              if (params.repassword === '') {
                delete params.repassword
              }
              const res = await createAdmin(this.optType, params)
              this.$message.success(res.data.msg)
              this.getAdminList()
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
        // 编辑管理员
        updateAdmin (row) {
          this.optType = 'update'
          this.getAdminDetail(row.id)
          this.visible = true
          this.addTip = window.lang.update + window.lang.admin
          this.$refs.userDialog.reset()
        },
        async getAdminDetail (id) {
          try {
            const res = await getAdminDetail(id)
            Object.assign(this.formData, res.data.data.admin)
          } catch (error) {

          }
        },
        // 停用/启用
        changeStatus (row) {
          if (row.id === 1) {
            return
          }
          this.delId = row.id
          this.curStatus = row.status
          this.statusTip = this.curStatus ? window.lang.sureDisable : window.lang.sure_Open
          this.statusVisble = true
        },
        async sureChange () {
          try {
            let tempStatus = this.curStatus === 1 ? 0 : 1
            const params = {
              id: this.delId,
              status: tempStatus
            }
            const res = await changeAdminStatus(params)
            this.$message.success(res.data.msg)
            this.statusVisble = false
            this.getAdminList()
          } catch (error) {
            this.$message.error(error.data.msg)
            this.statusVisble = false
          }
        },
        closeDialog () {
          this.statusVisble = false
        },
        // 删除用户
        deleteUser (row) {
          if (row.id === 1) {
            return
          }
          this.delVisible = true
          this.delId = row.id
        },
        async sureDel () {
          try {
            const res = await deleteAdmin(this.delId)
            this.$message.success(res.data.msg)
            this.params.page = this.data.length > 1 ? this.params.page : this.params.page - 1
            this.delVisible = false
            this.getAdminList()
          } catch (error) {
            this.delVisible = false
            this.$message.error(error.data.msg)
          }
        },
        // 获取分组
        async getRoleList () {
          try {
            let res = await getAdminRole(this.roleParams)
            const temp = res.data.data
            this.roleTotal = temp.count
            this.roleList = res.data.data.list
            if (temp.count > 20) {
              this.roleParams.limit = this.roleTotal
              res = await getAdminRole(this.roleParams)
              this.roleList = res.data.data.list
            } else {
            }
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        }
      },
      created () {
        this.getAdminList()
        // 循环加载分组
        this.getRoleList()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
