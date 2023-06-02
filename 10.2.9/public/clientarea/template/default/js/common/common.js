(function () {
  // 动态计算根元素的fontsize
  let sizeWidth = document.documentElement.clientWidth;  // 初始宽宽度
  function setRootFontSize () {
    let rem, rootWidth;
    let rootHtml = document.documentElement;
    if (sizeWidth > rootHtml.clientWidth) {
      if ((sizeWidth > 750) && (rootHtml.clientWidth <= 750)) {
        window.location.reload();
      }
    } else {
      if ((sizeWidth <= 750) && (rootHtml.clientWidth > 750)) {
        window.location.reload();
      }
      // 大于750时 刷新页面
    }
    sizeWidth = rootHtml.clientWidth
    if (rootHtml.clientWidth > 750) {
      //限制展现页面的最小宽度
      rootWidth = rootHtml.clientWidth < 1200 ? 1200 : rootHtml.clientWidth > 1920 ? 1920 : rootHtml.clientWidth;
      // rootWidth = rootHtml.clientWidth;
      // 19.2 = 设计图尺寸宽 / 100（ 设计图的rem = 100 ）
      rem = rootWidth / 19.2;
      // 动态写入样式
      rootHtml.style.fontSize = `${rem}px`;
    } else {
      rootWidth = rootHtml.clientWidth
      rem = rootWidth / 7.5
      rootHtml.style.fontSize = `${rem}px`;
    }

  }
  setRootFontSize();
  window.addEventListener("resize", setRootFontSize, false);
})();


const mixin = {
  data () {
    return {
      addons_js_arr: [], // 已激活的插件
      isShowCashBtn: false,
      isShowCashDialog: false,
    }
  },
  methods: {
    applyCashback () {
      this.isShowCashDialog = true
    },
    showBtn (bol) {
      this.isShowCashBtn = bol
    },
    cancleDialog () {
      this.isShowCashDialog = false
    },
  },
  mounted () {
    this.addons_js_arr = []
    const addons = document.querySelector('#addons_js')
    this.addons_js_arr = JSON.parse(addons.getAttribute('addons_js')).map(item => item.name)
    // if (this.addons_js_arr.includes('OnlineService')) {
    //   queryCustomerServiceCode().then((res) => {
    //     const str = res.data.data.content
    //     let arr = str.split('</script>').map(item => {
    //       return item.replace(/[\r\n]/g, '').trim() + '</script>'
    //     }).filter(item => { return item.indexOf('</script>') > 0 })

    //     this.$nextTick(() => {
    //       const reg = /(<script src="(.*?)"><\/script>)|(<script>(.*?)<\/script>)/
    //       const newObj = arr.map(str => {
    //         // 创建 script 元素对象
    //         let script = document.createElement("script");
    //         // 将 script 元素对象赋值给 body 元素的子元素
    //         if (str.match(reg)[2]) {
    //           // 设置 src 属性
    //           let src = str.match(reg)[2]
    //           script.src = src
    //         } else if (str.match(reg)[4]) {
    //           let text = str.match(reg)[4]
    //           // script.textContent = text
    //         }
    //         document.querySelector("body").appendChild(script);
    //       })
    //     })
    //   }).catch(err => {
    //     console.log(err)
    //   })
    // }
  }
}