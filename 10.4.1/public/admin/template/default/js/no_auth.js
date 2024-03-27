(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('template')[0]
    Vue.prototype.lang = window.lang
   new Vue({
      components: {
        comConfig,
      },
      data () {
        return {
          urlPath: url,
          msg: ''
        }
      },
      created () {
       this.msg = decodeURI(location.href.split('?')[1]?.split('=')[1])
      },
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
