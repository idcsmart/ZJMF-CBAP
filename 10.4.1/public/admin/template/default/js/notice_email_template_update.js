(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName(
      "notice-email-template-create"
    )[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
        comTinymce,
      },
      data() {
        return {
          formData: {
            id: "",
            name: "",
            subject: "",
            message: "",
          },
          submitLoading: false,
          rules: {
            name: [
              {
                required: true,
                message: `${lang.input}${lang.nickname}`,
                type: "error",
              },
              {
                validator: (val) => val.length <= 100,
                message: `${lang.verify3}100`,
                type: "warning",
              },
            ],
            subject: [
              {
                required: true,
                message: `${lang.input}${lang.title}`,
                type: "error",
              },
              {
                validator: (val) => val.length <= 100,
                message: `${lang.verify3}100`,
                type: "warning",
              },
            ],
            message: [
              {
                required: true,
                message: `${lang.input}${lang.content}`,
                type: "error",
              },
            ],
          },
        };
      },
      created() {
        this.formData.id = location.href.split("?")[1].split("=")[1];
        this.getEmailDetail();
      },
      mounted() {
        // this.initTemplate()
        document.title =
          lang.email_notice +
          "-" +
          lang.template_manage +
          "-" +
          localStorage.getItem("back_website_name");
      },
      methods: {
        async getEmailDetail() {
          try {
            const res = await getEmailTemplateDetail(this.formData.id);
            Object.assign(this.formData, res.data.data.email_template);
            this.$nextTick(() => {
              this.$refs.comTinymce.setContent(this.formData.message);
              this.$refs.userDialog.clearValidate(['message'])
            })
          } catch (error) {
            console.log(error);
          }
        },
        setContent() {
          this.formData.message = this.$refs.comTinymce.getContent();
        },
        submit() {
          this.setContent();
          this.$refs.userDialog.validate().then(
            async (res) => {
              try {
                this.submitLoading = true;
                const res = await createEmailTemplate("update", this.formData);
                this.$message.success(res.data.msg);
                setTimeout(() => {
                  this.submitLoading = false;
                  location.href = "notice_email_template.htm";
                }, 500);
              } catch (error) {
                this.submitLoading = false;
                this.$message.error(error.data.msg);
              }
            },
            (error) => {
              console.log(error);
            }
          );
        },

        close() {
          location.href = "notice_email_template.htm";
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
