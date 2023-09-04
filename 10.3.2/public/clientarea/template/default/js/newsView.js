(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementById('content')
    Vue.prototype.lang = window.lang
    Vue.prototype.moment = window.moment
    new Vue({
      created () {
        this.init()
      },
      computed: {
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
      data () {
        return {
          newDetail: {}
        }
      },
      filters: {
        formateTime (time) {
          if (time && time !== 0) {
            var date = new Date(time * 1000);
            Y = date.getFullYear() + '-';
            M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
            D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' ';
            h = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
            m = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
            return (Y + M + D + h + m);
          } else {
            return "--";
          }
        },
      },
      methods: {
        init () {
          this.newDetail = JSON.parse(sessionStorage.viewNewObjData)
          this.newDetail.create_time = new Date().getTime() / 1000
        },
      },

    }).$mount(template)

    const mainLoading = document.getElementById('mainLoading')
    setTimeout(() => {
      mainLoading && (mainLoading.style.display = 'none')
    }, 200)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
