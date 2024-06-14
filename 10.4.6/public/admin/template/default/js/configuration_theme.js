(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("configuration-theme")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          formData: {
            admin_theme: "",
            clientarea_theme: "",
            web_theme: "",
            clientarea_theme_mobile: "",
            web_switch: "0",
            clientarea_theme_mobile_switch: "0",
            cart_theme: "",
            cart_theme_mobile: "",
            first_navigation: "",
            second_navigation: "",
          },
          value: "admin_theme",
          clientarea_type: "pc",
          cart_type: "pc",
          isCanUpdata: sessionStorage.isCanUpdata === "true",
          admin_theme: [],
          clientarea_theme: [],
          web_theme_list: [],
          cart_theme_list: [],
          cart_theme_mobile_list: [],
          clientarea_theme_mobile_list: [],
          rules: {
            clientarea_theme: [
              {
                required: true,
                message: lang.input + lang.site_name,
                type: "error",
              },
              {
                validator: (val) => val.length <= 255,
                message: lang.verify3 + 255,
                type: "warning",
              },
            ],
          },
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
          submitLoading: false,
          hasController: true,
        };
      },
      methods: {
        getQuery(name) {
          const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
          const r = window.location.search.substr(1).match(reg);
          if (r != null) return decodeURI(r[2]);
          return null;
        },
        async getActivePlugin() {
          const res = await getActiveAddon();
          this.hasController = (res.data.data.list || [])
            .map((item) => item.name)
            .includes("TemplateController");
        },
        jumpController(item) {
          location.href = `${location.origin}/${
            location.pathname.split("/")[1]
          }/${item.url}?theme=${item.name}`;
        },
        chooseTheme(e) {
          this.formData.clientarea_theme = e.name;
        },
        chooseWebTheme(e) {
          this.formData.web_theme = e.name;
        },
        chooseMobileTheme(e) {
          this.formData.clientarea_theme_mobile = e.name;
        },
        cartTheme(e) {
          this.formData.cart_theme = e.name;
        },
        cartMobileTheme(e) {
          this.formData.cart_theme_mobile = e.name;
        },
        chooseAdmin(e) {
          this.formData.admin_theme = e.name;
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const res = await updateThemeConfig(this.formData);
              this.$message.success(res.data.msg);
              this.getTheme();
              this.submitLoading = false;
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        async getTheme() {
          try {
            const res = await getThemeConfig();
            const temp = res.data.data;
            this.formData.admin_theme = temp.admin_theme;
            this.formData.first_navigation = temp.first_navigation;
            this.formData.second_navigation = temp.second_navigation;
            this.formData.web_theme = temp.web_theme;
            this.formData.clientarea_theme = temp.clientarea_theme;
            this.formData.clientarea_theme_mobile =
              temp.clientarea_theme_mobile;
            this.formData.cart_theme = temp.cart_theme;
            this.admin_theme = temp.admin_theme_list;
            this.clientarea_theme = temp.clientarea_theme_list;
            this.clientarea_theme_mobile_list =
              temp.clientarea_theme_mobile_list;
            this.web_theme_list = temp.web_theme_list;
            this.cart_theme_list = temp.cart_theme_list;
            this.cart_theme_mobile_list = temp.cart_theme_mobile_list;
            this.formData.web_switch = temp.web_switch;
            this.formData.clientarea_theme_mobile_switch =
              temp.clientarea_theme_mobile_switch;
            this.formData.cart_theme_mobile =
              temp.clientarea_theme_mobile_switch == 0
                ? temp.cart_theme_mobile_list[0]?.name || ""
                : temp.cart_theme_mobile;
            this.formData.clientarea_theme_mobile =
              temp.clientarea_theme_mobile_switch == 0
                ? temp.clientarea_theme_mobile_list[0]?.name || ""
                : temp.clientarea_theme_mobile;
          } catch (error) {}
        },
      },
      created() {
        const queryName = this.getQuery("name");
        if (queryName) {
          this.value = queryName;
        }
        const navList = JSON.parse(localStorage.getItem("backMenus"));
        let tempArr = navList.reduce((all, cur) => {
          cur.child && all.push(...cur.child);
          return all;
        }, []);
        const curValue = tempArr.filter(
          (item) => item.url === "configuration_system.htm"
        )[0]?.id;
        localStorage.setItem("curValue", curValue);
        this.getTheme();
        this.getActivePlugin();
        document.title =
          lang.theme_setting + "-" + localStorage.getItem("back_website_name");
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
