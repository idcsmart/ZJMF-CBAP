(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("configuration-debug")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          isCanUpdata: sessionStorage.isCanUpdata === "true",
          formData: {
            debug_model: 0,
            debug_model_auth: "",
            debug_model_expire_time: "",
          },
          countdownText: "",
          duration: "",
          submitLoading: false,
        };
      },
      methods: {
        // 复制
        copyHandler(id) {
          this.submitLoading = true;
          const name = document.getElementById(id);
          name.select();
          document.execCommand("Copy");
          this.submitLoading = false;
          this.$message.success(lang.copy + lang.success);
        },
        async changeSwitch(e) {
          try {
            const res = await updateDebugConfig({
              debug_model: e,
            });
            this.$message.success(res.data.msg);
            this.getSetting();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        async getSetting() {
          try {
            const res = await getDebugConfig();
            this.formData = res.data.data;
            if (!this.formData.debug_model_expire_time) {
              return;
            }
            const expirationDate = moment.unix(
              this.formData.debug_model_expire_time
            );
            const currentTime = moment();
            const duration = moment.duration(expirationDate.diff(currentTime));
            this.duration = duration * 1;
            const hours = Math.floor(duration.asHours());
            const minutes = Math.floor(duration.asMinutes()) % 60;
            const seconds = Math.floor(duration.asSeconds()) % 60;
            if (hours > 0) {
              return (this.countdownText = `${hours}${lang.hour}`);
            }
            if (minutes > 0) {
              this.countdownText = `${minutes}${lang.debug_minutes}`;
            } else {
              this.countdownText = `${seconds}${lang.seconds}`;
            }
          } catch (error) {}
        },
      },
      created() {
        this.getSetting();
        document.title =
          lang.debug_setting + "-" + localStorage.getItem("back_website_name");
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
