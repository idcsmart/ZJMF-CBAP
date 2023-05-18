(function (window, undefined) {
  var old_onload = window.onload
  window.onload = function () {
    const template = document.getElementsByClassName('template')[0]
    Vue.prototype.lang = window.lang
    new Vue({
      data() {
        return {
          urlPath: url
        }
      },
      created() {

      },
      methods: {
        goBack() {
          history.go(-1)
        },
      }
    }).$mount(template)
    typeof old_onload == 'function' && old_onload()
  };
})(window);
