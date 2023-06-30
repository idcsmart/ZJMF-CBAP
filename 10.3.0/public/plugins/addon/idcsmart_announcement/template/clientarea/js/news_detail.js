(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('news_detail')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      components: {
        asideMenu,
        topMenu,
      },
      created () {
        this.id = location.href.split('?')[1].split('=')[1]
        this.getCommonData()
        this.getData()
      },
      mounted () {

      },
      updated () {
        // // 关闭loading
        document.getElementById('mainLoading').style.display = 'none';
        document.getElementsByClassName('news_detail')[0].style.display = 'block'
      },
      destroyed () {

      },
      data () {
        return {
          id: '',
          params: {
            page: 1,
            limit: 20,
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
          curTit: '',
          newDetail: '',
          baseUrl: url
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
      computed: {
        calStr () {
          return (str) => {
            const temp = str && str.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').
            replace(/&amp;lt;/g, '<').replace(/&amp;gt;/g, '>').replace(/ &amp;lt;/g, '<').replace(/&amp;gt; /g, '>')
            .replace(/&amp;gt; /g, '>').replace(/&amp;quot;/g, '"').replace(/&amp;amp;nbsp;/g, ' ').replace(/&amp;#039;/g, '\'').replace('<?php', '&lt;?php')
            return temp
          }
        }
      },
      methods: {
        back () {
          location.href = 'source.htm'
        },
        async getData () {
          try {
            const res = await getNewsDetail(this.id)
            if (res.data.status === 200) {
              this.newDetail = res.data.data.news
              this.params.total = res.data.data.count
              //   this.loading.close()
            }
          } catch (error) {
            this.loading = false
            // this.loading.close()
          }
        },
        // 下载文件
        async downFile (row) {
          try {
            const res = await downloadFile({ id: row.id })
            // const fileName = row.name + "." + row.filetype;
            const fileName = row.name;
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
          this.commonData = JSON.parse(localStorage.getItem('common_set_before'))
          document.title = this.commonData.website_name + `-${lang.news_detail}`

        }
      },

    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
