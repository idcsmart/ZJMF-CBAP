(function () {
  let PermissionPlugin = {};
  // 获取所有权限表
  const permissionArr = JSON.parse(localStorage.getItem("backAuth")) || [];
  PermissionPlugin.install = function (Vue) {
    Vue.directive("permission", {
      inserted: function (el, binding, vnode) {
        if (binding.value && !checkPermission(binding.value)) {
          el.parentNode && el.parentNode.removeChild(el);
        }
      },
    });
    // 按钮级使用 v-permission="'xxx'"
    Vue.prototype.$checkPermission = function (value) {
      if (!value) return true;
      return permissionArr.includes(value);
    };
  };
  // 事件使用 this.$checkPermission('xxx')
  function checkPermission(value) {
    if (!value) return true;
    return permissionArr.includes(value);
  }

  if (typeof window !== "undefined" && window.Vue) {
    window.Vue.use(PermissionPlugin);
  }
})();
