(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('file_download')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      components: {
        asideMenu,
        topMenu,
        pagination,
      },
      created () {
        this.getCommonData()
        this.getFileFolder()
      },
      mounted () {

      },
      updated () {
        // // 关闭loading
        document.getElementById('mainLoading').style.display = 'none';
        document.getElementsByClassName('file_download')[0].style.display = 'block'
      },
      destroyed () {

      },
      data () {
        return {
          params: {
            page: 1,
            limit: 10,
            pageSizes: [10, 20, 50],
            total: 0,
            orderby: 'id',
            sort: 'desc',
            keywords: '',
          },
          commonData: {},
          folder: [],
          folderNum: 0,
          curId: '',
          tableData: [],
          loading: false,
          curTit: ''
        }
      },
      filters: {
        formateTime (time) {
          if (time && time !== 0) {
            return formateDate(time * 1000)
          } else {
            return "--"
          }
        },
        formateByte (size) {
          if (size < 1024 * 1024) {
            return (size / 1024).toFixed(2) + 'KB'
          } else {
            return (size / (1024 * 1024).toFixed(2)) + 'MB'
          }
        }
      },
      methods: {
        // 获取文件夹
        async getFileFolder () {
          try {
            const res = await getFileFolder()
            if (res.data.status === 200) {
              this.folder = res.data.data.list
              this.curId = res.data.data.list[0].id
              this.folderNum = this.folder.reduce((all, cur) => {
                all += cur.file_num
                return all
              }, 0)
              this.curTit = res.data.data.list[0].name
              this.getData()
            }
          } catch (error) {
            console.log(error)
          }
        },
        // 选择文件夹
        changeFolder (item) {
          this.curId = item.id
          this.curTit = item.name
          this.params.page = 1
          this.getData()
        },
        async getData () {
          try {
            const params = {
              addon_idcsmart_file_folder_id: this.curId,
              ...this.params
            }
            this.loading = true
            delete params.pageSizes
            delete params.total
            const res = await getFileList(params)
            if (res.data.status === 200) {
              this.tableData = res.data.data.list
              this.params.total = res.data.data.count
              this.loading = false
            }
          } catch (error) {
            this.loading = false
            console.log(error)
          }
        },
        // 下载文件
        async downFile (item) {
          try {
            const res = await downloadFile({ id: item.id })
            const fileName = item.name;
            const _res = res.data;
            const blob = new Blob([_res]);
            const downloadElement = document.createElement("a");
            const href = window.URL.createObjectURL(blob); // 创建下载的链接
            downloadElement.href = href;
            downloadElement.download = decodeURI(fileName); // 下载后文件名
            document.body.appendChild(downloadElement);
            downloadElement.click(); // 点击下载
            document.body.removeChild(downloadElement); // 下载完成移除元素
            window.URL.revokeObjectURL(href); // 释放掉blob对象
          } catch (error) {

          }
        },
        // 搜索
        inputChange () {
          this.params.page = 1
          this.getData()
        },
        // 每页展示数改变
        sizeChange (e) {
          this.params.limit = e
          this.params.page = 1
          // 获取列表
          this.getData()
        },
        // 当前页改变
        currentChange (e) {
          this.params.page = e
          this.getData()
        },

        // 获取通用配置
        getCommonData () {
          getCommon().then(res => {
            if (res.data.status === 200) {
              this.commonData = res.data.data
              localStorage.setItem('common_set_before', JSON.stringify(res.data.data))
              document.title = this.commonData.website_name + `-${lang.file_download}`
            }
          })
        }
      },

    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
