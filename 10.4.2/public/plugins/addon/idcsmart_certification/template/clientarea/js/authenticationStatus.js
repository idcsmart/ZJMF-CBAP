(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    new Vue({
      components: {
        asideMenu,
        topMenu,
      },
      created() {
        this.rzType = location.href.split("?")[1].split("=")[1]
          ? location.href.split("?")[1].split("=")[1]
          : "";
        this.getCommonData();
        this.getCertificationInfo();
      },
      mounted() {},
      updated() {
        // // 关闭loading
        document.getElementById("mainLoading").style.display = "none";
        document.getElementsByClassName("template")[0].style.display = "block";
      },
      destroyed() {},
      data() {
        return {
          certificationInfoObj: {
            person: {},
            company: {},
          },
          personStatus: null, // 个人认证状态
          companyStatus: null, // 企业认证状态
          userStatus: null, // 10 个人认证通过  15个人资料审核中  20企业审核通过  25企业资料审核中   50失败
          rzType: "", // 1 个人   2 企业
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
      },
      methods: {
        // 返回按钮
        backTicket() {
          location.href = "authentication_select.htm";
        },
        goAccount() {
          location.href = "/account.htm";
        },
        // 获取基础信息
        getCertificationInfo() {
          certificationInfo().then((res) => {
            this.certificationInfoObj = res.data.data;
            this.personStatus = res.data.data.person.status;
            this.companyStatus = res.data.data.company.status;
            if (this.companyStatus === 2 && this.rzType !== "3") {
              this.userStatus = 50;
              return;
            }
            if (this.rzType === "2") {
              if (this.companyStatus === 1) {
                // 企业认证成功
                this.userStatus = 20;
              } else if (this.companyStatus === 3 || this.companyStatus === 4) {
                // 企业待审核
                this.userStatus = 25;
              } else if (this.companyStatus === 2) {
                // 企业认证失败
                this.userStatus = 50;
              }
            } else if (this.rzType === "1" || this.rzType === "3") {
              if (this.personStatus === 1) {
                this.userStatus = 10; // 个人认证成功
              } else if (this.personStatus === 3 || this.personStatus === 4) {
                this.userStatus = 15; // 个人待审核
              } else if (this.personStatus === 2) {
                this.userStatus = 50; // 个人认证失败
              }
            }
          });
        },
        submitAgan() {
          if (this.personStatus === 1) {
            location.href = `authentication_status.htm?type=3`;
          } else {
            location.href = "authentication_select.htm";
          }
        },
        // 获取通用配置
        getCommonData() {
          getCommon().then((res) => {
            if (res.data.status === 200) {
              this.commonData = res.data.data;
              localStorage.setItem(
                "common_set_before",
                JSON.stringify(res.data.data)
              );
              document.title =
                this.commonData.website_name + "-" + lang.realname_text81;
            }
          });
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
