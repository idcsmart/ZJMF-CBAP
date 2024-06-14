(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
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
          theme: "",
          tabList: [],
          tab: "template_web_config.htm",
          id: "",
          backUrl: `${str}configuration_theme.htm?name=web_switch`,
          name: "web",
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          delLoading: false,
          hover: true,
          loading: false,
          /* 参数 */
          infoParams: {
            icp_info: "",
            icp_info_link: "",
            public_security_network_preparation: "",
            public_security_network_preparation_link: "",
            telecom_appreciation: "",
            copyright_info: "",
            enterprise_name: "",
            enterprise_telephone: "",
            enterprise_mailbox: "",
            dcim_product_link: "",
            cloud_product_link: "",
            online_customer_service_link: "",
            qrcode: [],
            logo: [],
          },
          // 图片上传相关
          uploadUrl: str + "/v1/upload",
          // uploadUrl: 'https://kfc.idcsmart.com/admin/v1/upload',
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          typeRules: {
            name: [
              {
                required: true,
                message: `${lang.tem_input}${lang.tem_name}`,
                type: "error",
              },
            ],
            url: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_feed_link}`,
                type: "error",
              },
            ],
            description: [
              {
                required: true,
                message: `${lang.tem_input}${lang.description}`,
                type: "error",
              },
            ],
            icp_info: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_icp_info}`,
                type: "error",
              },
            ],
            icp_info_link: [
              {
                required: true,
                message: `${lang.tem_input}${lang.tem_jump_link}`,
                type: "error",
              },
              {
                pattern:
                  /(https?|ftp|file):\/\/[-A-Za-z0-9+&@#/%?=~_|!:,.;]+[-A-Za-z0-9+&@#/%=~_|]/,
                message: lang.tem_tip1,
                type: "warning",
              },
            ],
            public_security_network_preparation: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_put_on_record}`,
                type: "error",
              },
            ],
            public_security_network_preparation_link: [
              {
                required: true,
                message: `${lang.tem_input}${lang.tem_jump_link}`,
                type: "error",
              },
              {
                pattern:
                  /(https?|ftp|file):\/\/[-A-Za-z0-9+&@#/%?=~_|!:,.;]+[-A-Za-z0-9+&@#/%=~_|]/,
                message: lang.tem_tip1,
                type: "warning",
              },
            ],
            telecom_appreciation: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_telecom_value}`,
                type: "error",
              },
            ],
            copyright_info: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_copyright}`,
                type: "error",
              },
            ],
            enterprise_name: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_enterprise_name}`,
                type: "error",
              },
            ],
            enterprise_telephone: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_enterprise_telephone}`,
                type: "error",
              },
            ],
            enterprise_mailbox: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_enterprise_mailbox}`,
                type: "error",
              },
            ],
            online_customer_service_link: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_online_link}`,
                type: "error",
              },
            ],
            qrcode: [
              {
                required: true,
                message: `${lang.tem_attachment}${lang.temp_enterprise_qrcode}`,
                type: "error",
              },
            ],
            logo: [
              {
                required: true,
                message: `${lang.tem_attachment}${lang.temp_web_logo}`,
                type: "error",
                trigger: "change",
              },
            ],
          },
          submitLoading: false,
          optTitle: "",
          /* friendly_link | honor | partner */
          friendly_link_list: [],
          honor_list: [],
          partner_list: [],
          webNavList: [],
          linkColumns: [
            {
              colKey: "name",
              title: lang.nickname,
              ellipsis: true,
            },
            {
              colKey: "url",
              title: lang.feed_link,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 120,
            },
          ],
          honorColumns: [
            {
              colKey: "img",
              title: lang.picture,
              ellipsis: true,
            },
            {
              colKey: "name",
              title: lang.nickname,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 120,
            },
          ],
          partnerColumns: [
            {
              colKey: "img",
              title: lang.picture,
              ellipsis: true,
            },
            {
              colKey: "name",
              title: lang.nickname,
              ellipsis: true,
            },
            {
              colKey: "description",
              title: lang.description,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 120,
            },
          ],
          friendly_link_loading: false,
          honor_loading: false,
          partner_loading: false,
          infoTit: "",
          classModel: false,
          classParams: {
            name: "",
            url: "",
            img: "",
            description: "",
            qrcode: [],
          },
          calcType: "",
          delId: "",
          delDialog: false,
          upgradeDialog: false,
          themeInfo: {},
        };
      },
      created() {
        this.theme = getQuery().theme || "";
        this.getConfigInfo();
        this.getThemeInfo();
        this.getTabList();
        this.getInfoList("friendly_link");
        this.getInfoList("honor");
        this.getInfoList("partner");
      },
      methods: {
        sureUpgrade() {
          this.submitLoading = true;
          upgradeTheme({ theme: this.theme })
            .then((res) => {
              this.submitLoading = false;
              this.upgradeDialog = false;
              this.$message.success(res.data.msg);
              window.location.reload();
            })
            .catch((err) => {
              this.submitLoading = false;
              this.$message.error(err.data.msg);
            });
        },
        sureDel() {
          this.delLoading = true;
          uninstallTheme(this.theme)
            .then((res) => {
              this.$message.success(res.data.msg);
              this.delLoading = false;
              this.delDialog = false;
              location.href = this.backUrl;
            })
            .catch((err) => {
              this.delLoading = false;
              this.$message.error(err.data.msg);
            });
        },
        handleUpgrade() {
          this.upgradeDialog = true;
        },
        getThemeInfo() {
          getThemeLatestVersion(this.theme).then((res) => {
            this.themeInfo = res.data.data;
          });
        },
        handleDelete() {
          this.delDialog = true;
        },
        getTabList() {
          getTemplateControllerTab({ theme: this.theme }).then((res) => {
            this.tabList = res.data.data.list;
          });
        },
        changeTab(val) {
          const curPath = location.pathname
            .split("/")
            .find((item) => item.indexOf("htm") !== -1);
          if (val === curPath) {
            return;
          }
          location.href = val + `?theme=${this.theme}`;
        },
        /* friendly_link | honor | partner */
        addCalc(type) {
          this.calcType = type;
          this.optType = "add";
          switch (type) {
            case "friendly_link":
              this.infoTit = `${lang.tem_add}${lang.temp_friendly_link}`;
              break;
            case "honor":
              this.infoTit = `${lang.tem_add}${lang.temp_honor}`;
              break;
            case "partner":
              this.infoTit = `${lang.tem_add}${lang.temp_partner}`;
              break;
          }
          this.classParams = {
            name: "",
            url: "",
            img: "",
            description: "",
            qrcode: [],
          };
          this.classModel = true;
        },
        updateItem(type, row) {
          this.calcType = type;
          this.optType = "update";
          Object.assign(this.classParams, row);
          this.classParams.qrcode = [{ url: row.img }];
          this.classModel = true;
          switch (type) {
            case "friendly_link":
              this.infoTit = lang.edit + lang.friendly_link;
              break;
            case "honor":
              this.infoTit = lang.edit + lang.honor;
              break;
            case "partner":
              this.infoTit = lang.edit + lang.partner;
              break;
          }
        },
        async submitInfo({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const params = JSON.parse(JSON.stringify(this.classParams));
              if (this.calcType !== "friendly_link") {
                params.img =
                  params.qrcode[0]?.response?.save_name || params.qrcode[0].url;
              }
              const res = await addAndUpdateController(
                this.calcType,
                this.optType,
                params
              );
              this.$message.success(res.data.msg);
              this.getInfoList(this.calcType);
              this.submitLoading = false;
              this.classModel = false;
            } catch (error) {
              console.log("error", error);
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        deleteItem(type, row) {
          this.calcType = type;
          this.delId = row.id;
          this.delVisible = true;
        },
        async sureDelete() {
          try {
            this.delLoading = true;
            const res = await delController(this.calcType, {
              id: this.delId,
            });
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.getInfoList(this.calcType);
            this.delLoading = false;
          } catch (error) {
            this.delLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        async getInfoList(name) {
          try {
            this[`${name}_loading`] = true;
            const res = await getControllerList(name);
            this[`${name}_list`] = res.data.data.list;
            this[`${name}_loading`] = false;
          } catch (error) {
            this[`${name}_loading`] = false;
            this.$message.error(error.data.msg);
          }
        },
        /* friendly_link | honor | partner end */

        /* 参数配置 */
        formatResponse(res) {
          if (res.status != 200) {
            this.$message.error(res.msg);
            this.classParams.qrcode = [];
            return { error: res.msg };
          }
          return { save_name: res.data.image_url, url: res.data.image_url };
        },
        async getConfigInfo() {
          try {
            const res = await getControllerConfig(this.name);
            const temp = res.data.data;
            temp.qrcode = temp.enterprise_qrcode
              ? [{ url: temp.enterprise_qrcode }]
              : [];
            temp.logo = temp.official_website_logo
              ? [{ url: temp.official_website_logo }]
              : [];
            this.infoParams = temp;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        async submitConfig({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.loading = true;
              const params = JSON.parse(JSON.stringify(this.infoParams));
              params.enterprise_qrcode = params.qrcode[0]?.url
                ? params.qrcode[0].url
                : "";
              params.official_website_logo = params.logo[0]?.url
                ? params.logo[0].url
                : "";
              const res = await saveControllerConfig(this.name, params);
              this.$message.success(res.data.msg);
              this.getConfigInfo();
              this.loading = false;
            } catch (error) {
              console.log(error);
              this.loading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
