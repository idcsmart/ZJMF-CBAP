(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("feedback")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    const host = location.origin + "/" + location.pathname.split("/")[1];
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          hover: true,
          tableLayout: false,
          delVisible: false,
          loading: false,
          systemGroup: [],
          classModel: false,
          webNavDdlId: null,
          webNavLoading: false,
          webDelVisible: false,
          isCanUpdata: sessionStorage.isCanUpdata === "true",
          classParams: {
            name: "",
            url: "",
            img: "",
            description: "",
            qrcode: [],
          },
          list: [],
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
          navDialog: false,

          editNavId: null,
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
          navRules: {
            name: [
              {
                required: true,
                message: lang.input + lang.info_config_text4,
              },
            ],
            url: [
              {
                required: true,
                message: lang.input + lang.info_config_text6,
              },
            ],
          },
          typeRules: {
            icp_info: [
              {
                required: false,
                message: lang.input + lang.icp_info,
                type: "error",
              },
            ],
            icp_info_link: [
              {
                required: false,
                message: lang.input + lang.jump_link,
                type: "error",
              },
              {
                pattern:
                  /(https?|ftp|file):\/\/[-A-Za-z0-9+&@#/%?=~_|!:,.;]+[-A-Za-z0-9+&@#/%=~_|]/,
                message: lang.feed_tip,
                type: "warning",
              },
            ],
            public_security_network_preparation: [
              {
                required: false,
                message: lang.input + lang.put_on_record,
                type: "error",
              },
            ],
            public_security_network_preparation_link: [
              {
                required: false,
                message: lang.input + lang.jump_link,
                type: "error",
              },
              {
                pattern:
                  /(https?|ftp|file):\/\/[-A-Za-z0-9+&@#/%?=~_|!:,.;]+[-A-Za-z0-9+&@#/%=~_|]/,
                message: lang.feed_tip,
                type: "warning",
              },
            ],
            telecom_appreciation: [
              {
                required: false,
                message: lang.input + lang.telecom_value,
                type: "error",
              },
            ],
            copyright_info: [
              {
                required: false,
                message: lang.input + lang.copyright,
                type: "error",
              },
            ],
            put_on_record: [
              {
                required: false,
                message: lang.input + lang.put_on_record,
                type: "error",
              },
            ],
            enterprise_name: [
              {
                required: false,
                message: lang.input + lang.enterprise_name,
                type: "error",
              },
            ],
            enterprise_telephone: [
              {
                required: false,
                message: lang.input + lang.enterprise_telephone,
                type: "error",
              },
            ],
            enterprise_mailbox: [
              {
                required: false,
                message: lang.input + lang.enterprise_mailbox,
                type: "error",
              },
            ],
            cloud_product_link: [
              {
                required: false,
                message: lang.input + lang.cloud_product_link,
                type: "error",
              },
            ],
            dcim_product_link: [
              {
                required: false,
                message: lang.input + lang.dcim_product_link,
                type: "error",
              },
            ],
            online_customer_service_link: [
              {
                required: false,
                message: lang.input + lang.online_customer_service_link,
                type: "error",
              },
            ],
            qrcode: [
              {
                required: false,
                message: lang.attachment + lang.enterprise_qrcode,
                type: "error",
              },
            ],
            logo: [
              {
                required: false,
                message: lang.attachment + lang.web_logo,
                type: "error",
                trigger: "change",
              },
            ],
          },
          popupProps: {
            overlayClassName: `custom-select`,
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
          submitLoading: false,
          // 反馈详情
          detailModel: false,
          params: {
            keywords: "",
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          total: 0,
          pageSizeOptions: [20, 50, 100],
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
          uploadUrl: host + "/v1/upload",
          // uploadUrl: 'https://kfc.idcsmart.com/admin/v1/upload',
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          // 友情链接
          friendly_link_list: [],
          honor_list: [],
          partner_list: [],
          webNavList: [],
          webNavColumns: [
            {
              colKey: "drag", // 列拖拽排序必要参数
              cell: "drag",
              width: 90,
              title: lang.sort,
            },
            {
              colKey: "name",
              cell: "name",
              ellipsis: true,
              title: lang.info_config_text4,
            },
            {
              colKey: "url",
              cell: "url",
              ellipsis: true,
              minWidth: 360,
              title: lang.info_config_text6,
            },
            {
              colKey: "status",
              cell: "status",
              width: 120,
              title: lang.info_config_text7,
            },
            {
              title: lang.operation,
              colKey: "op",
              width: 120,
            },
          ],
          navParams: {
            name: "",
            web_nav_id: "",
            url: "",
            status: 0,
          },
          friendly_link_loading: false,
          honor_loading: false,
          partner_loading: false,
          infoTit: "",
          calcType: "", // friendly_link honor partner
        };
      },

      created() {
        this.getConfigInfo();
        this.getInfoList("friendly_link");
        this.getInfoList("honor");
        this.getInfoList("partner");
        this.getWebConfig();
        document.title =
          lang.info_config + "-" + localStorage.getItem("back_website_name");
      },
      // 计算属性
      computed: {
        calcSelectList() {
          return this.webNavList.filter((item) => {
            return this.editNavId !== item.id;
          });
        },
      },

      methods: {
        // 获取反馈
        async getConfigInfo() {
          try {
            const res = await getConfigInfo(this.params);
            const temp = res.data.data;
            temp.qrcode = temp.enterprise_qrcode
              ? [
                  {
                    url: temp.enterprise_qrcode,
                  },
                ]
              : [];
            temp.logo = temp.official_website_logo
              ? [
                  {
                    url: temp.official_website_logo,
                  },
                ]
              : [];
            this.infoParams = temp;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        async getInfoList(name) {
          try {
            this[`${name}_loading`] = true;
            const res = await getComInfo(name);
            this[`${name}_list`] = res.data.data.list;
            this[`${name}_loading`] = false;
          } catch (error) {
            this[`${name}_loading`] = false;
            this.$message.error(error.data.msg);
          }
        },
        formatResponse(res) {
          if (res.status != 200) {
            this.$message.error(res.msg);
            this.classParams.qrcode = [];
            return { error: res.msg };
          }
          return { save_name: res.data.image_url, url: res.data.image_url };
        },
        lookDetail(row) {},

        addCalc(type) {
          this.calcType = type;
          this.optType = "add";
          switch (type) {
            case "friendly_link":
              this.infoTit = lang.order_text53 + lang.friendly_link;
              break;
            case "honor":
              this.infoTit = lang.order_text53 + lang.honor;
              break;
            case "partner":
              this.infoTit = lang.order_text53 + lang.partner;
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
        changeStatus(val, row) {
          updateNavShow({ id: row.id, status: val }).then((res) => {
            this.$message.success(res.data.msg);
            this.getWebConfig();
          });
        },
        getWebConfig() {
          this.webNavLoading = true;
          getNavList().then((res) => {
            this.webNavList = res.data.data.list;
            this.$nextTick(() => {
              this.$refs.navTable.expandAll();
              this.webNavLoading = false;
            });
          });
        },
        addWebNav(row) {
          if (row.id) {
            this.editNavId = row.id;
            this.navParams.name = row.name;
            this.navParams.web_nav_id =
              row.web_nav_id === 0 ? "" : row.web_nav_id;
            this.navParams.url = row.url;
            this.navParams.status = row.status;
          } else {
            this.editNavId = null;
            this.navParams.name = "";
            this.navParams.web_nav_id = "";
            this.navParams.url = "";
            this.navParams.status = 0;
          }
          this.navDialog = true;
          setTimeout(() => {
            this.$refs.navForm.clearValidate();
          }, 0);
        },
        closeNavDialog() {
          this.editNavId = null;
          this.navParams.name = "";
          this.navParams.web_nav_id = "";
          this.navParams.url = "";
          this.navParams.status = 0;
          setTimeout(() => {
            this.$refs.navForm.clearValidate();
            this.navDialog = false;
          }, 0);
        },
        async navSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const subApi = this.editNavId ? updateNav : addNav;
              const params = { ...this.navParams };
              params.web_nav_id =
                params.web_nav_id === "" ? 0 : params.web_nav_id;
              params.id = this.editNavId;
              const res = await subApi(params);
              this.$message.success(res.data.msg);
              this.getWebConfig();
              this.submitLoading = false;
              this.navDialog = false;
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        onDragSort({ current, targetIndex, newData }) {
          const prevItem = newData[targetIndex - 1] || {};
          const nextItem = newData[targetIndex + 1] || {};
          sortNav({
            id: current.id,
            prev_id:
              targetIndex === 0 ||
              (prevItem.web_nav_id === 0 &&
                nextItem.web_nav_id &&
                nextItem.web_nav_id !== 0)
                ? 0
                : prevItem.id,
            web_nav_id:
              targetIndex === 0 ||
              (prevItem.web_nav_id === 0 &&
                (nextItem.web_nav_id === 0 || !nextItem.web_nav_id))
                ? 0
                : prevItem.web_nav_id === 0
                ? prevItem.id
                : prevItem.web_nav_id,
          })
            .then((res) => {
              this.$message.success(res.data.msg);
              this.getWebConfig();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        },

        updateItem(type, row) {
          this.calcType = type;
          this.optType = "update";
          Object.assign(this.classParams, row);
          this.classParams.qrcode = [
            {
              url: row.img,
            },
          ];
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
              const res = await addAndUpdateComInfo(
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
        delWebNav(id) {
          this.webNavDdlId = id;
          this.webDelVisible = true;
        },
        async sureDelWebNav() {
          try {
            this.submitLoading = true;
            const res = await delNav(this.webNavDdlId);
            this.$message.success(res.data.msg);
            this.webDelVisible = false;
            this.webNavDdlId = null;
            this.getWebConfig();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },

        async sureDelete() {
          try {
            this.submitLoading = true;
            const res = await delComInfo(this.calcType, this.delId);
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.getInfoList(this.calcType);
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.delVisible = false;
            this.$message.error(error.data.msg);
          }
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getConfigInfo();
        },
        // 分类管理
        classManage() {
          this.classModel = true;
          this.classParams.name = "";
          this.classParams.icon = "";
          this.optType = "add";
        },
        async submitSystemGroup({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const params = JSON.parse(JSON.stringify(this.infoParams));
              params.enterprise_qrcode = params.qrcode[0]?.url
                ? params.qrcode[0].url
                : "";
              params.official_website_logo = params.logo[0]?.url
                ? params.logo[0].url
                : "";
              const res = await saveConfigInfo(params);
              this.$message.success(res.data.msg);
              this.getConfigInfo();
              this.submitLoading = false;
            } catch (error) {
              console.log(error);
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        editGroup(row) {
          this.optType = "update";
          this.classParams = JSON.parse(JSON.stringify(row));
        },
        async deleteGroup() {
          try {
            const res = await delImageGroup({
              id: this.delId,
            });
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.getGroup();
            this.classParams.name = "";
            this.classParams.icon = "";
            this.$refs.classForm.reset();
            this.optType = "add";
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
