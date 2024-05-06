(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementById('content')
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    new Vue({
      components: {

      },
      created() {
        this.init()
      },
      data() {
        return {
          detailData: "",
          contentLoading: false,
        }
      },
      filters: {
        formateTime(time) {
          if (time && time !== 0) {
            var date = new Date(time * 1000)
            Y = date.getFullYear() + '-'
            M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-'
            D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' '
            h = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':'
            m = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()
            return (Y + M + D + h + m)
          } else {
            return "--"
          }
        },
      },
      computed: {
        formatHtml() {
          return str => {
            return str && this.decodeHTML(str.replace(/amp;/g, ''))
          }
        },
        calStr () {
          return (str) => {
            const temp = str && str.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').
              replace(/&amp;lt;/g, '<').replace(/&amp;gt;/g, '>').replace(/ &amp;lt;/g, '<').replace(/&amp;gt; /g, '>')
              .replace(/&amp;gt; /g, '>').replace(/&amp;quot;/g, '"').replace(/&amp;amp;nbsp;/g, ' ').replace(/&amp;#039;/g, '\'').
              replace('<?php', '&lt;?php')
            return temp
          }
        }
      },
      methods: {
        decodeHTML(html) {
          var doc = new DOMParser().parseFromString(html, "text/html")
          return doc.documentElement.textContent
        },
        init() {
          const _url = window.location.href
          const _getqyinfo = _url.split('?')[1]
          const _getqys = new URLSearchParams('?' + _getqyinfo)
          const _id = _getqys.get('id')
          this.doGetHelpDetails(_id)
        },
        doGetHelpDetails(id) {
          this.contentLoading = true
          const params = {
            id
          }
          helpDetails(params).then(res => {
            this.contentLoading = false
            if (res.data.status == 200) {
              this.detailData = res.data.data.help
            }
          }).catch(error => {
            this.contentLoading = false
          })
        },
        // 附件下载
        downloadfile(url) {
          const downloadElement = document.createElement("a")
          downloadElement.href = url
          downloadElement.download = url.split("^")[1] // 下载后文件名
          document.body.appendChild(downloadElement)
          downloadElement.click() // 点击下载
        },
      },

    }).$mount(template)

    const mainLoading = document.getElementById('mainLoading')
    setTimeout(() => {
      mainLoading && (mainLoading.style.display = 'none')
    }, 200)
    typeof old_onload == 'function' && old_onload()
  }
})(window)
