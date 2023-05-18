(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('task')[0]
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
          hover: true,
          urlPath: url,
          columns: [
            {
              colKey: 'id',
              title: 'ID',
              width: 120,
              sortType: 'all',
              sorter: true
            },
            {
              colKey: 'description',
              title: lang.task_description,
              ellipsis: true,
              className: 'name-status'
            },
            {
              colKey: 'start_time',
              title: lang.start_time,
              width: 170
            },
            {
              colKey: 'finish_time',
              title: lang.end_time,
              width: 170
            },
            // {
            //   colKey: 'status',
            //   title: lang.task_status,
            //   width: 120,
            //   ellipsis: true
            // },
            {
              colKey: 'retry',
              title: lang.operation,
              width: 120
            }
          ],
          params: {
            keywords: '',
            status: '',
            page: 1,
            limit: 20,
            orderby: 'id',
            sort: 'desc'
          },
          id: '',
          total: 0,
          pageSizeOptions: [20, 50, 100],
          loading: false,
          formData: {
            status: '',
            keywords: ''
          },
          statusOpt: [
            { value: 'Wait', label: lang.Wait },
            { value: 'Exec', label: lang.Exec },
            { value: 'Finish', label: lang.Finish },
            { value: 'Failed', label: lang.failed },
          ],
          maxHeight: ''
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
      methods: {
        // 搜索
        reset () {
          this.formData.status = ''
          this.formData.keywords = ''
        },
        onSubmit () {
          Object.assign(this.params, this.formData)
          this.params.page = 1
          this.getTaskList()
        },
        // 重试
        async retryFun (id) {
          try {
            const res = await reloadTask(id)
            this.$message.success(res.data.msg)
            this.getTaskList()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        changePage (e) {
          this.params.page = e.current
          this.params.limit = e.pageSize
          this.getTaskList()
        },
        async getTaskList () {
          try {
            this.loading = true
            const res = await getTask(this.params)
            this.data = res.data.data.list
            this.total = res.data.data.count
            this.loading = false
          } catch (error) {
            this.$message.error(error.data.msg)
            this.loading = false
          }
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
          this.getTaskList()
        }
      },
      created () {
        this.getTaskList()
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);

