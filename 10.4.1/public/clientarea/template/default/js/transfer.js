(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementById("content");
    Vue.prototype.lang = window.lang;
    new Vue({
      created() {
        const url = this.getQuery('target')
        this.jumpUrl = decodeURIComponent(url);
        this.init();
      },
      computed: {},
      data() {
        return {
          jumpUrl: "",
          logoUrl: "",
          website_name: ""
        };
      },
      methods: {
        init ()  {
          this.logoUrl = location.origin + '/upload/common/default/' + JSON.parse(localStorage.getItem('common_set'))?.system_logo
          this.website_name = JSON.parse(localStorage.getItem('common_set'))?.website_name
          document.title = `${lang.jump_tip}-${this.website_name}`
        },
        getQuery(name) {
          const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
          const r = window.location.search.substr(1).match(reg);
          if (r != null) return decodeURI(r[2]);
          return null;
        },
        jumpLink () {
          location.href = this.jumpUrl
        }
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
