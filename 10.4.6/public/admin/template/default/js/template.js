(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          message: "template...",
        };
      },
      created() {},
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
