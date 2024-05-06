// 这是vue注册全局 指令的地方
// 自动聚焦的指令
Vue.directive("focus", {
  inserted: function (el) {
    el.focus();
  },
});
// 判断是否安装了某插件的指令 用法 v-plugin="插件名" 例如 v-plugin="whmcs" v-plugin="'whmcs-bridge'"  用于显示/隐藏某些元素
Vue.directive("plugin", {
  inserted(el, binding) {
    const addonsDom = document.querySelector("#addons_js");
    let addonsArr = [];
    let arr = [];
    if (addonsDom) {
      addonsArr = JSON.parse(addonsDom.getAttribute("addons_js")) || []; // 插件列表
      // 判断是否安装了某插件
      arr = addonsArr.filter((item) => item.name === binding.value);
      if (arr.length === 0) {
        // 未安装 移除该元素 以及子元素所有的DOM
        el.parentNode.removeChild(el);
      }
    } else {
      el.parentNode.removeChild(el);
    }
  },
});
// 复制指令  用法 v-copy="复制的内容" 例如 v-copy="123456789" 用于复制内容到剪切板 不用写点击事件
Vue.directive("copy", {
  bind(el, { value }) {
    el.$value = value;
    el.handler = () => {
      el.style.position = "relative";
      if (!el.$value) {
        return;
      }
      // 动态创建 textarea 标签
      const textarea = document.createElement("textarea");
      // 将该 textarea 设为 readonly 防止 iOS 下自动唤起键盘，同时将 textarea 移出可视区域
      textarea.readOnly = "readonly";
      textarea.style.position = "absolute";
      textarea.style.top = "0px";
      textarea.style.left = "-9999px";
      textarea.style.zIndex = "-9999";
      // 将要 copy 的值赋给 textarea 标签的 value 属性
      textarea.value = el.$value;
      // 将 textarea 插入到 el 中
      el.appendChild(textarea);
      // 兼容IOS 没有 select() 方法
      if (textarea.createTextRange) {
        textarea.select(); // 选中值并复制
      } else {
        textarea.setSelectionRange(0, el.$value.length);
        textarea.focus();
      }
      const result = document.execCommand("Copy");
      if (result) {
        Vue.prototype.$message.success(lang.pay_text17);
      }
      el.removeChild(textarea);
    };
    el.addEventListener("click", el.handler); // 绑定点击事件
  },
  // 当传进来的值更新的时候触发
  componentUpdated(el, { value }) {
    el.$value = value;
  },
  // 指令与元素解绑的时候，移除事件绑定
  unbind(el) {
    el.removeEventListener("click", el.handler);
  },
});

/* 转换时间 ==> 12 Oct, 2023 14:47 传入的是秒级 */
Vue.directive("time", (el, binding) => {
  if (!binding.value) {
    el.innerHTML = "--";
    return;
  }
  const timestamp = binding.value;
  const date = new Date(timestamp * 1000);
  const curLang = localStorage.getItem("lang") || "en-us";
  let formattedDate = "";
  const year = date.getFullYear();
  let month = "";
  const day = String(date.getDate()).padStart(2, "0");
  const hour = String(date.getHours()).padStart(2, "0");
  const minute = String(date.getMinutes()).padStart(2, "0");
  if (curLang === "zh-cn" || curLang === "zh-hk") {
    month = String(date.getMonth() + 1).padStart(2, "0");
    formattedDate = `${year}-${month}-${day} ${hour}:${minute}`;
  } else {
    month = new Intl.DateTimeFormat("en-US", { month: "short" }).format(date);
    formattedDate = `${day} ${month}, ${year} <span style="color: #878A99;">${hour}:${minute}</span>`;
  }
  el.innerHTML = formattedDate;
});
