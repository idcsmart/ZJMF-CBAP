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
        this.id = temp.id ? temp.id : "";
        this.initData();
      },
      mounted() {},
      data() {
        return {
          id: "",
          goods_token: "",
          goods_url: "",
          commonData: {},
          iframeUrl: "",
        };
      },
      filters: {
        formateTime(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000);
          } else {
            return "--";
          }
        },
        filterMoney(money) {
          if (isNaN(money)) {
            return "0.00";
          } else {
            const temp = `${money}`.split(".");
            return parseInt(temp[0]).toLocaleString() + "." + (temp[1] || "00");
          }
        },
      },

      methods: {
        // 解析url
        getQuery(url) {
          const str = url.substr(url.indexOf("?") + 1);
          const arr = str.split("&");
          const res = {};
          for (let i = 0; i < arr.length; i++) {
            const item = arr[i].split("=");
            res[item[0]] = item[1];
          }
          return res;
        },
        // 初始化请求数据
        async initData() {
          // 获取产品列表地址
          await goodsList({ id: this.id }).then((res) => {
            this.goods_url = encodeURIComponent(res.data.data.url);
          });
          // 获取token
          await goodsToken().then((res) => {
            this.goods_token = encodeURIComponent(res.data.data.token);
          });
          this.iframeUrl = `https://console.vcp.baidu.com/api/loginvcp/login/securitytoken?redirectUrl=${this.goods_url}&signinSecurityToken=${this.goods_token}`;
        },
      },
      destroyed() {},
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
