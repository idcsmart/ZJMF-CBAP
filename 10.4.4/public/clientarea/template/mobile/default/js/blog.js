(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        asideMenu,
        topMenu,
      },
      created() {
        const temp = this.getQuery(location.search);
        if (temp.title) {
          document.title = temp.title;
        }
        this.initData();
      },
      mounted() {},
      data() {
        return {
          goods_token: "",
          goods_url: "",
          commonData: {},
          iframeUrl: "",
        };
      },

      methods: {
        // 解析url
        getQuery() {
          const url = window.location.href;
          // 判断是否有参数
          if (url.indexOf("?") === -1) {
            return {};
          }
          const params = url.split("?")[1];
          const paramsArr = params.split("&");
          const paramsObj = {};
          paramsArr.forEach((item) => {
            const key = item.split("=")[0];
            const value = item.split("=")[1];
            // 解析中文
            paramsObj[key] = decodeURI(value);
          });
          return paramsObj;
        },
        // 初始化请求数据
        async initData() {
          this.iframeUrl = `https://blog.0xyun.com/`;
        },
      },
      destroyed() {},
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
