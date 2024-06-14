(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("configuration-system")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          columns: [
            {
              colKey: "id",
              title: "ID",
              width: 90,
            },
            {
              colKey: "title",
              title: lang.oss_text1,
              cell: "title",
              ellipsis: true,
            },
            {
              colKey: "author",
              title: lang.oss_text2,
              cell: "author",
              ellipsis: true,
            },
            {
              colKey: "version",
              title: lang.oss_text3,
              cell: "version",
              width: 150,
              ellipsis: true,
            },
            {
              colKey: "status",
              title: lang.oss_text4,
              cell: "status",
              width: 200,
            },
            {
              colKey: "have",
              title: lang.oss_text5,
              cell: "have",
              width: 200,
            },
            {
              colKey: "op",
              title: lang.oss_text6,
              cell: "op",
              width: 200,
            },
          ],
          loading: false,
          isGetsms: false,
          statusVisble: false,
          data: [],
          submitLoading: false,
          delId: null,
          configVisble: false,
          formLoading: false,
          curStatus: 1,
          configTip: "",
          installVisible: false,
          installTip: "",
          type: "",
          configData: [],
          rules: {},
          hasController: true,
          statusTip: "",
          isCanUpdata: sessionStorage.isCanUpdata === "true",
          ossPageData: {
            oss_method: "LocalOss", // 对象存储方式
            oss_sms_plugin: "", // 短信接口
            oss_sms_plugin_template: "", // 短信模板
            oss_sms_plugin_admin: [], // 短信通知人员
            oss_mail_plugin: "", // 邮件接口
            oss_mail_plugin_template: "", // 邮件模板
            oss_mail_plugin_admin: [], // 邮件模板
            password: "",
          },
          saveLoading: false,
          localVisible: false,
          originMethod: "",
          smsList: [],
          emailList: [],
          smsTemplateList: [],
          emailTemplateList: [],
          adminList: [],
          treeProps: {
            keys: {
              label: "nickname",
              value: "id",
              children: "children",
            },
          },
        };
      },
      created() {
        document.title =
          lang.oss_setting + "-" + localStorage.getItem("back_website_name");
        this.getActivePlugin();
        this.getOssList();
        this.getAdmin();
        this.getEmailList();
        this.emailTemplate();
      },
      computed: {
        calcOssMethod() {
          return this.data
            .filter((item) => item.status == 1)
            .map((item) => {
              return {
                value: item.name,
                label: item.title,
              };
            });
        },
        // 格式化配置里面的options
        computedOptions() {
          return (options) => {
            const arr = [];
            Object.keys(options).map((item) => {
              arr.push({ value: item, label: options[item] });
            });
            return arr;
          };
        },
        calcNewName() {
          return (
            this.data.filter(
              (item) => item.name === this.ossPageData.oss_method
            )[0]?.title || ""
          );
        },
      },
      methods: {
        async getActivePlugin() {
          const res = await getActiveAddon();
          this.hasController = (res.data.data.list || [])
            .map((item) => item.name)
            .includes("TemplateController");
        },
        async getAdmin() {
          try {
            const res = await getAdminList({
              page: 1,
              limit: 10000,
              status: 1,
            });
            const temp = res.data.data.list;
            this.adminList = temp
              .reduce((all, cur) => {
                cur.id = String(cur.id);
                if (!all.includes(cur.roles)) {
                  all.push(cur.roles);
                }
                return all;
              }, [])
              .reduce((all, cur, index) => {
                all.push({
                  id: cur + index,
                  nickname: cur,
                  children: [...temp.filter((item) => item.roles === cur)],
                });
                return all;
              }, []);
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        getTemList() {
          this.ossPageData.oss_sms_plugin_template = "";
          this.smsTemplate();
        },

        // 短信模板
        async smsTemplate() {
          if (!this.isGetsms) {
            await getSmsInterface()
              .then((res) => {
                this.smsList = res.data.data.list;
                this.isGetsms = true;
              })
              .catch((err) => {
                this.$message.error(err.data.msg);
                this.isGetsms = true;
              });
          }
          const name =
            this.smsList.filter(
              (item) => item.id == this.ossPageData.oss_sms_plugin
            )[0]?.name || "";
          getSmsTemplate(name)
            .then((res) => {
              this.smsTemplateList = res.data.data.list;
            })
            .catch((err) => {
              this.smsTemplateList = [];
              this.$message.error(err.data.msg);
            });
        },
        // 邮件接口
        getEmailList() {
          getEmailInterface()
            .then((res) => {
              this.emailList = res.data.data.list;
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        },
        // 邮件模板
        emailTemplate() {
          getEmailTemplate()
            .then((res) => {
              this.emailTemplateList = res.data.data.list;
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        },
        getOssPageConfig() {
          apiOssPage().then((res) => {
            this.originMethod = res.data.data.oss_method;
            this.ossPageData = Object.assign(this.ossPageData, res.data.data);
            this.ossPageData.oss_sms_plugin_template =
              Number(this.ossPageData.oss_sms_plugin_template) || "";
            this.ossPageData.oss_mail_plugin =
              Number(this.ossPageData.oss_mail_plugin) || "";
            this.ossPageData.oss_mail_plugin_template =
              Number(this.ossPageData.oss_mail_plugin_template) || "";
            this.ossPageData.oss_sms_plugin =
              Number(this.ossPageData.oss_sms_plugin) || "";
            this.smsTemplate();
          });
        },
        cancelPageDia() {
          this.localVisible = false;
          this.ossPageData.password = "";
        },
        // 获取是否有数据
        getOssData(item) {
          apiOssData(item.name)
            .then((res) => {
              item.have = res.data.data.has_data;
            })
            .catch((err) => {
              item.have = false;
            });
        },
        // 获取状态
        getOssStatus(item) {
          apiOssLink(item.name)
            .then((res) => {
              item.link = res.data.data.link;
            })
            .catch((err) => {
              item.link = false;
            });
        },
        handelSavePage() {
          if (this.originMethod !== this.ossPageData.oss_method) {
            this.ossPageData.password = "";
            this.localVisible = true;
            return;
          }
          this.savePageConfig();
        },
        savePageConfig() {
          if (
            this.originMethod !== this.ossPageData.oss_method &&
            !this.ossPageData.password
          ) {
            this.$message.error(lang.oss_text18);
            return;
          }
          this.saveLoading = true;
          this.submitLoading = true;
          apiOssConfigPut(this.ossPageData)
            .then((res) => {
              this.submitLoading = false;
              this.$message.success(res.data.msg);
              this.getOssList();
              this.localVisible = false;
              this.ossPageData.password = "";
              this.saveLoading = false;
            })
            .catch((err) => {
              this.submitLoading = false;
              this.saveLoading = false;
              this.$message.error(err.data.msg);
            });
        },
        // 配置
        handleConfig(row) {
          this.delId = row.name;
          this.getConfig(row.id);
          this.configVisble = true;
        },
        async getConfig(id) {
          try {
            this.formLoading = true;
            const params = {
              name: this.delId,
              id,
            };
            const res = await apiOssDetail(params);
            this.configData = res.data.data.plugin.config;
            this.configTip = res.data.data.plugin.title;
            this.formLoading = false;
          } catch (error) {
            this.formLoading = false;
          }
        },
        // 保存配置
        async onSubmit() {
          try {
            const params = {
              name: this.delId,
              config: {},
            };
            for (const i in this.configData) {
              params.config[this.configData[i].field] =
                this.configData[i].value;
            }
            this.submitLoading = true;
            const res = await saveOssConfig(params);
            this.$message.success(res.data.msg);
            this.configVisble = false;
            this.getOssList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 停用/启用
        changeStatus(row) {
          this.delId = row.name;
          this.curStatus = row.status;
          this.statusTip =
            this.curStatus == 1
              ? window.lang.oss_text27
              : window.lang.oss_text29;
          this.statusVisble = true;
        },
        async sureChange() {
          try {
            let tempStatus = this.curStatus === 1 ? 0 : 1;
            const params = {
              name: this.delId,
              status: tempStatus,
            };
            this.submitLoading = true;
            const res = await changeStatus(params);
            this.$message.success(res.data.msg);
            this.statusVisble = false;
            this.getOssList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(res.data.msg);
            this.statusVisble = false;
          }
        },
        // 删除
        handelInstall(row) {
          this.installVisible = true;
          this.delId = row.name;
          this.type = row.status === 3 ? "install" : "uninstall";
          this.installTip =
            row.status == 3
              ? window.lang.sureInstall
              : window.lang.sureUninstall;
        },
        async sureInstall() {
          try {
            const params = {
              name: this.delId,
            };
            this.submitLoading = true;
            const res = await deleteOss(this.type, params);
            this.$message.success(res.data.msg);
            this.installVisible = false;
            this.getOssList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.installVisible = false;
            this.$message.error(error.data.msg);
          }
        },
        async getOssList() {
          this.getOssPageConfig();
          this.loading = true;
          apiOssList()
            .then((res) => {
              this.loading = false;
              this.data = res.data.data.list.map((item) => {
                item.have = false;
                item.link = false;
                this.getOssStatus(item);
                this.getOssData(item);
                return item;
              });
            })
            .catch((err) => {
              this.loading = false;
              this.$message.error(err.data.msg);
            });
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
