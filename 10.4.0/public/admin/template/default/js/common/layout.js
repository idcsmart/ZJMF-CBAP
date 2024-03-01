(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const isEn = localStorage.getItem("backLang") === "en-us" ? true : false;
    if (isEn) {
      document.getElementById("layout").className = "isEn";
    } else {
      document.getElementById("layout").className = "";
    }
    // 全局搜索
    function globalSearch(keywords) {
      return Axios.get(`/global_search?keywords=${keywords}`);
    }
    TDesign.Dialog.options.props.closeOnOverlayClick.default = false;
    TDesign.Dialog.options.props.placement.default = "center";
    const aside = document.getElementById("aside");
    const footer = document.getElementById("footer");
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    Vue.prototype.lang = window.lang;
    // const linkTag = document.querySelector('link[rel="icon"]')
    // linkTag.href = localStorage.getItem('tab_logo')
    if (!localStorage.getItem("backJwt")) {
      const host = location.origin;
      const fir = location.pathname.split("/")[1];
      const str = `${host}/${fir}/`;
      location.href = str + "/login.htm";
    }
    const MODE_OPTIONS = [
      {
        type: "light",
        text: window.lang.theme_light,
        src: `${url}/img/assets-setting-light.svg`,
      },
      {
        type: "dark",
        text: window.lang.theme_dark,
        src: `${url}/img/assets-setting-dark.svg`,
      },
    ];
    const COLOR_OPTIONS = [
      "default",
      "cyan",
      "green",
      "yellow",
      "orange",
      "red",
      "pink",
      "purple",
    ];
    /* aside */
    aside &&
      new Vue({
        data: {
          baseUrl: str,
          collapsed: false,
          isSearchFocus: false,
          searchData: "",
          /* 系统设置 */
          visible: false,
          formData: {
            mode: localStorage.getItem("theme-mode") || "light",
            brandTheme: localStorage.getItem("theme-color") || "default",
          },
          MODE_OPTIONS,
          COLOR_OPTIONS,
          colorList: {
            DEFAULT: {
              "@brand-color": "#0052D9",
            },
            CYAN: {
              "@brand-color": "#0594FA",
            },
            GREEN: {
              "@brand-color": "#00A870",
            },
            ORANGE: {
              "@brand-color": "#ED7B2F",
            },
            RED: {
              "@brand-color": "#E34D59",
            },
            PINK: {
              "@brand-color": "#ED49B4",
            },
            PURPLE: {
              "@brand-color": "#834EC2",
            },
            YELLOW: {
              "@brand-color": "#EBB105",
            },
          },
          curSrc: localStorage.getItem("country_imgUrl") || `${url}/img/CN.png`,
          langList: [],
          expanded: [],
          curValue: Number(localStorage.getItem("curValue")),
          iconList: [
            "user",
            "view-module",
            "cart",
            "setting",
            "folder-open",
            "precise-monitor",
            "control-platform",
          ],
          navList: [],
          audio_tip: null,
          global: null,
          loadingSearch: false,
          noData: false,
          timer: null,
          isShow: false,
          userName: localStorage.getItem("userName") || "-",
          // 修改密码弹窗
          editPassVisible: false,
          editPassFormData: {
            password: "",
            repassword: "",
          },
          isCanUpdata: false,
          pluginUpgrade: false,
          setting_parent_id: null,
          plugin_parent_id: null,
        },
        computed: {
          logUrl() {
            if (this.collapsed) {
              return `${url}/img/small-logo.png`;
            } else {
              return `${url}/img/logo.png`;
            }
          },
        },
        mounted() {
          this.navList = JSON.parse(localStorage.getItem("backMenus"));
          this.navList.forEach((item) => {
            item.child &&
              item.child.forEach((el) => {
                if (el.id === this.curValue) {
                  this.expanded = [];

                  this.expanded.push(item.id);
                }
                if (el.url === "configuration_system.htm") {
                  // 如果是系统设置 找到他的parent_id
                  this.setting_parent_id = el.parent_id;
                }
                if (el.url === "plugin.htm") {
                  // 如果是插件列表 找到他的parent_id
                  this.plugin_parent_id = el.parent_id;
                }
              });
          });
          this.langList = JSON.parse(
            localStorage.getItem("common_set")
          ).lang_admin;
          // 其他区域关闭全局搜索
          document.onclick = () => {
            this.isShow = false;
          };
          this.$nextTick(() => {
            document.getElementById(`search-content`) &&
              (document.getElementById(`search-content`).onclick = () => {
                event.stopPropagation();
              });
            document.getElementById(`global-input`) &&
              (document.getElementById(`global-input`).onclick = () => {
                event.stopPropagation();
              });
          });
        },
        created() {
          // this.getSystemConfig()
          this.setWebTitle();
          this.getVersion();
          this.getNewVersion();
        },
        methods: {
          // 获取系统版本信息
          async getVersion() {
            try {
              if (
                sessionStorage.isCanUpdata === "true" ||
                sessionStorage.isCanUpdata === "false"
              ) {
                // 字符串转布尔值
                this.isCanUpdata = sessionStorage.isCanUpdata === "true";
              } else {
                const res = await version();
                const systemData = res.data.data;
                this.isCanUpdata = this.checkVersion(
                  systemData.version,
                  systemData.last_version
                );
              }
              sessionStorage.setItem("isCanUpdata", this.isCanUpdata);
            } catch (error) {}
          },
          // 获取插件版本信息
          async getNewVersion() {
            try {
              if (
                sessionStorage.pluginUpgrade === "true" ||
                sessionStorage.pluginUpgrade === "false"
              ) {
                // 字符串转布尔值
                this.pluginUpgrade = sessionStorage.pluginUpgrade === "true";
              } else {
                const res = await getActiveVersion();
                this.pluginUpgrade = res.data.data.upgrade === 1;
              }
              sessionStorage.setItem("pluginUpgrade", this.pluginUpgrade);
            } catch (error) {}
          },
          /**
           *
           * @param {string} nowStr 当前版本
           * @param {string} lastStr 最新版本
           */
          checkVersion(nowStr, lastStr) {
            const nowArr = nowStr.split(".");
            const lastArr = lastStr.split(".");
            let hasUpdate = false;
            const nowLength = nowArr.length;
            const lastLength = lastArr.length;

            const length = Math.min(nowLength, lastLength);
            for (let i = 0; i < length; i++) {
              if (lastArr[i] - nowArr[i] > 0) {
                hasUpdate = true;
              }
            }
            if (!hasUpdate && lastLength - nowLength > 0) {
              hasUpdate = true;
            }
            return hasUpdate;
          },
          setWebTitle() {
            const urlArr = location.pathname.split("/");
            const url =
              urlArr.length > 3
                ? urlArr.slice(2).join("/")
                : urlArr[urlArr.length - 1];
            const website_name = localStorage.getItem("back_website_name");
            const menu = JSON.parse(localStorage.getItem("backMenus"));
            let isSetTitle = false;
            menu.forEach((fir) => {
              const temp = fir.child || [];
              if (temp.length > 0) {
                let menu_id = "";
                if (
                  location.pathname.includes("client_") &&
                  !location.pathname.includes("idcsmart_client_") &&
                  !location.pathname.includes("client_care") &&
                  !location.pathname.includes("client_custom_field")
                ) {
                  menu_id = temp.filter((e) => e.url === "client.htm")[0]?.id;
                } else if (location.pathname.includes("supplier_")) {
                  menu_id = temp.filter((e) => e.url === "supplier_list.htm")[0]
                    ?.id;
                } else {
                  // menu_id = temp.filter(e => location.pathname.includes(e.url))[0]?.id
                }
                if (menu_id) {
                  localStorage.setItem("curValue", menu_id);
                  this.curValue = menu_id;
                }
                temp.forEach((sec) => {
                  if (sec.url === url) {
                    document.title =
                      (url === "index.htm" ? lang.home : sec.name) +
                      "-" +
                      website_name;
                    localStorage.lastTitle =
                      (url === "index.htm" ? lang.home : sec.name) +
                      "-" +
                      website_name;
                    isSetTitle = true;
                  } else if (
                    url.includes("plugin") &&
                    sec.url.split("/")[1] === url.split("/")[1] &&
                    !isSetTitle
                  ) {
                    // document.title = (url === 'index.htm' ? lang.home : sec.name) + '-' + website_name
                    if (localStorage.getItem("curValue") * 1 === sec.id * 1) {
                      document.title = sec.name + "-" + website_name;
                    }
                    localStorage.lastTitle =
                      (url === "index.htm" ? lang.home : sec.name) +
                      "-" +
                      website_name;
                  }
                });
              } else {
                if (fir.url === url) {
                  document.title =
                    (url === "index.htm" ? lang.home : fir.name) +
                    "-" +
                    website_name;
                } else if (
                  url.includes("plugin") &&
                  fir.url.split("/")[1] === url.split("/")[1]
                ) {
                  document.title =
                    (url === "index.htm" ? lang.home : fir.name) +
                    "-" +
                    website_name;
                } else {
                  document.title = localStorage.lastTitle || website_name;
                }
              }
            });
            /* 首页 */
            if (
              location.origin +
                "/" +
                location.pathname.split("/")[1] +
                "/index.htm" ===
              location.href
            ) {
              document.title = lang.homepage + "-" + website_name;
            }
          },
          async getSystemConfig() {
            try {
              const res = await getCommon();
              document.title = res.data.data.website_name;
              localStorage.lastTitle = res.data.data.website_name;
            } catch (error) {
              console.log(error);
            }
          },
          getAuth(auth) {
            return auth.map((item) => {
              item.child = item.child.filter((el) => el.url);
              return item;
            });
          },
          jumpHandler(e) {
            localStorage.setItem("curValue", e.id);
            if (
              e.url.includes("idcsmart_ticket") ||
              e.url.includes("idcsmart_ticket_internal") ||
              (e.child && str + e.child[0].url.includes("idcsmart_ticket")) ||
              (e.child &&
                str + e.child[0].url.includes("idcsmart_ticket_internal"))
            ) {
              this.audio_tip = new Audio(
                "/admin/template/default/media/tip.wav"
              );
              this.audio_tip.play();
              setTimeout(() => {
                this.audio_tip.pause();
                this.audio_tip = null;
                location.href =
                  str + e.url || (e.child && str + e.child[0].url);
              }, 2);
            } else {
              location.href = str + e.url || (e.child && str + e.child[0].url);
            }
          },
          changeCollapsed() {
            this.collapsed = !this.collapsed;
          },
          goIndex() {
            localStorage.setItem("curValue", 0);
            location.href = str + "index.htm";
          },
          changeSearch(e) {
            this.isSearchFocus = e;
            this.isShow = true;
            this.noData = false;
            if (this.timer) {
              clearTimeout(this.timer);
              this.timer = null;
            }
            this.timer = setTimeout(() => {
              this.globalSearchList();
            }, 500);
          },
          // 全局搜索
          async globalSearchList() {
            try {
              this.loadingSearch = true;
              const res = await globalSearch(this.isSearchFocus);
              this.global = res.data.data;
              if (
                this.global.clients.length === 0 &&
                this.global.products.length === 0 &&
                this.global.hosts.length === 0
              ) {
                this.noData = true;
              }
              this.loadingSearch = false;
            } catch (error) {
              console.log(error);
              this.loadingSearch = false;
            }
          },
          changeSearchFocus(value) {
            if (value) {
              if (this.global) {
                this.isShow = true;
              }
            }
            this.isSearchFocus = value;
          },
          // 个人中心
          handleNav() {},
          // 退出登录
          async handleLogout() {
            try {
              const res = await Axios.post("/logout");
              this.$message.success(res.data.msg);
              localStorage.removeItem("backJwt");
              setTimeout(() => {
                const host = location.origin;
                const fir = location.pathname.split("/")[1];
                const str = `${host}/${fir}/`;
                location.href = str + "login.htm";
              }, 300);
            } catch (error) {
              this.$message.error(error.data.msg);
            }
          },
          // 语言切换
          async changeLang(e) {
            try {
              const index = this.langList.findIndex(
                (item) => item.display_lang === e.value
              );
              if (
                localStorage.getItem("backLang") !== e.value ||
                !localStorage.getItem("backLang")
              ) {
                localStorage.setItem(
                  "country_imgUrl",
                  this.langList[index].display_img
                );
                localStorage.setItem("backLang", e.value);
              }
              // 更新系统设置里面的后台语言
              // 先获取后更改
              const res = await getSystemOpt();
              const params = res.data.data;
              params.lang_admin = e.value;
              await updateSystemOpt(params);
              // 获取导航
              const menus = await getMenus();
              const menulist = menus.data.data.menu;
              localStorage.setItem("backMenus", JSON.stringify(menulist));
              window.location.reload();
            } catch (error) {
              console.log(error);
            }
          },
          // 颜色配置
          toUnderline(name) {
            return name.replace(/([A-Z])/g, "_$1").toUpperCase();
          },
          getBrandColor(type, colorList) {
            const name = /^#[A-F\d]{6}$/i.test(type)
              ? type
              : this.toUnderline(type);
            return colorList[name || "DEFAULT"];
          },
          /* 页面配置 */
          toggleSettingPanel() {
            this.visible = true;
          },
          handleClick() {
            this.visible = true;
          },
          getModeIcon(mode) {
            if (mode === "light") {
              return SettingLightIcon;
            }
            if (mode === "dark") {
              return SettingDarkIcon;
            }
            return SettingAutoIcon;
          },
          // 主题
          onPopupVisibleChange(visible, context) {
            if (!visible && context.trigger === "document")
              this.isColoPickerDisplay = visible;
          },

          // 修改密码相关
          // 关闭修改密码弹窗
          editPassClose() {
            this.editPassVisible = false;
            this.editPassFormData = {
              password: "",
              repassword: "",
            };
          },
          // 修改密码提交
          onSubmit({ validateResult, firstError }) {
            if (validateResult === true) {
              const params = {
                password: this.editPassFormData.password,
                repassword: this.editPassFormData.repassword,
              };
              editPass(params)
                .then((res) => {
                  if (res.data.status === 200) {
                    this.editPassClose();
                    this.$message.success(res.data.msg);
                    this.handleLogout();
                  }
                })
                .catch((error) => {
                  this.$message.error(error.data.msg);
                });
              console.log(this.editPassFormData);
            } else {
              console.log("Errors: ", validateResult);
              this.$message.warning(firstError);
            }
          },
          // 确认密码检查
          checkPwd(val) {
            if (val !== this.editPassFormData.password) {
              return {
                result: false,
                message: window.lang.password_tip,
                type: "error",
              };
            }
            return { result: true };
          },
        },
        watch: {
          "formData.mode"() {
            if (this.formData.mode === "auto") {
              document.documentElement.setAttribute("theme-mode", "");
            } else {
              document.documentElement.setAttribute(
                "theme-mode",
                this.formData.mode
              );
            }
            localStorage.setItem("theme-mode", this.formData.mode);
          },
          "formData.brandTheme"() {
            document.documentElement.setAttribute(
              "theme-color",
              this.formData.brandTheme
            );
            localStorage.setItem("theme-color", this.formData.brandTheme);
          },
        },
      }).$mount(aside);

    /* footer */
    footer &&
      new Vue({
        data() {
          return {};
        },
      }).$mount(footer);

    var loading = document.getElementById("loading");
    setTimeout(() => {
      loading && (loading.style.display = "none");
    }, 200);
    typeof old_onload == "function" && old_onload();
  };
})(window);

const mixin = {
  data() {
    return {
      addonArr: [], // 已激活的插件
    };
  },
  methods: {
    async getAddonList() {
      try {
        const res = await getAddon();
        this.addonArr = res.data.data.list.map((item) => item.name);
      } catch (error) {}
    },
  },
  created() {
    this.getAddonList();
  },
};
