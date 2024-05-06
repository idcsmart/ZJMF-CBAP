(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("client")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        let checkPwd2 = (val) => {
          if (val !== this.formData.password) {
            return {
              result: false,
              message: window.lang.password_tip,
              type: "error",
            };
          }
          return { result: true };
        };
        return {
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          statusVisble: false,
          hover: true,
          columns: [
            {
              colKey: "id",
              title: "ID",
              width: 125,
              sortType: "all",
              sorter: true,
            },
            {
              colKey: "username",
              title: lang.name,
              width: 120,
              ellipsis: true,
            },
            {
              colKey: "phone",
              title: lang.contact,
              width: 120,
              ellipsis: true,
            },
            {
              colKey: "e-mail",
              title: lang.email,
              width: 120,
              ellipsis: true,
            },
            {
              colKey: "host_active_num",
              title: lang.host_active_product_num,
              width: 140,
            },
            {
              colKey: "status",
              title: lang.isOpen,
              width: 120,
            },
          ],
          hideSortTips: true,
          params: {
            keywords: "",
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
            type: ""
          },
          curLevelId: "",
          total: 0,
          pageSizeOptions: [20, 50, 100],
          formData: {
            username: "",
            email: "",
            phone_code: 86,
            phone: "",
            password: "",
            repassword: "",
          },
          rules: {
            username: [
              {
                required: true,
                message: lang.input + lang.name,
                type: "error",
              },
            ],
            password: [
              {
                required: true,
                message: lang.input + lang.password,
                type: "error",
              },
              {
                pattern: /^[\w@!#$%^&*()+-_]{6,32}$/,
                message: lang.verify8 + "6~32" + "，" + lang.verify14,
                type: "warning",
              },
            ],
            repassword: [
              {
                required: true,
                message: lang.input + lang.surePassword,
                type: "error",
              },
              { validator: checkPwd2, trigger: "blur" },
            ],
          },
          loading: false,
          country: [],
          delId: "",
          curStatus: 1,
          statusTip: "",
          /* 用户等级 */
          levelList: [],
          hasPlugin: false,
          addonArr: [],
          submitLoading: false,
          typeOption: [
            { value: "", label: lang.auth_all },
            { value: "id", label: "ID" },
            { value: "username", label: lang.name },
            { value: "phone", label: lang.phone },
            { value: "email", label: lang.email },
            { value: "company", label: lang.company }
          ],
        };
      },
      computed: {
        filterColor() {
          return (level) => {
            if (level) {
              return this.levelList.filter(
                (item) => item.id * 1 === level[0]?.value * 1
              )[0]?.background_color;
            }
          };
        },
        filterName() {
          return (level) => {
            if (level) {
              return (
                this.levelList.filter(
                  (item) => item.id * 1 === level[0]?.value * 1
                )[0]?.name || ""
              );
            }
          };
        },
        showDetails() {
          const clientAuth = [
            "auth_user_detail_personal_information_view",
            "auth_user_detail_host_info_view",
            "auth_user_detail_order_view",
            "auth_user_detail_transaction_view",
            "auth_user_detail_transaction_view",
            "auth_user_detail_operation_log",
            "auth_user_detail_notification_log_sms_notification",
            "auth_user_detail_notification_log_email_notification",
            "auth_user_detail_ticket_view",
            "auth_user_detail_info_record_view",
          ];
          return clientAuth.some((item) => this.$checkPermission(item));
        },
      },
      created() {
        this.curLevelId = this.getQuery('level_id') * 1 || '';
        this.params.keywords = this.getQuery('keywords') || '';
        this.params.type = this.getQuery('type') || '';
        this.getClientList();
        this.getCountry();
      },
      methods: {
        getQuery(name) {
          const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
          const r = window.location.search.substr(1).match(reg);
          if (r != null) return decodeURI(r[2]);
          return null;
        },
        async getPlugin() {
          try {
            const res = await getAddon();
            this.addonArr = res.data.data.list.map((item) => item.name);
            this.hasPlugin = this.addonArr.includes("IdcsmartClientLevel");
            this.hasPlugin && this.getLevel();
            this.addonArr.includes("IdcsmartSubAccount") && this.getSubUser();
            if (this.addonArr.includes("IdcsmartCertification")) {
              this.columns.splice(2, 0, {
                colKey: "certification",
                title: lang.user_text20,
                width: 150,
              });
            }
          } catch (error) {}
        },
        rowClick(e) {
          location.href = `client_detail.htm?client_id=${e.row.id}`;
        },
        /* 用户等级 */
        async getLevel() {
          try {
            const res = await getAllLevel();
            this.levelList = res.data.data.list;
          } catch (error) {}
        },
        // 输入邮箱的时候取消手机号验证
        cancelPhone(val) {
          if (val) {
            this.$refs.userDialog.clearValidate(["phone"]);
          }
        },
        cancelEmail(val) {
          if (val) {
            this.$refs.userDialog.clearValidate(["email"]);
          }
        },
        // 获取列表
        async getClientList() {
          try {
            this.loading = true;
            const res = await getClientList(this.params, this.curLevelId);
            this.loading = false;
            this.data = res.data.data.list;
            this.total = res.data.data.count;
            this.addonArr.length === 0 && this.getPlugin();
          } catch (error) {
            this.loading = false;
            console.log(error.data && error.data.msg);
          }
        },
        // 子账户
        async getSubUser() {
          try {
            idList = this.data.map((item) => item.id);
            str = idList.join(",");
            const result = await getAdminAccountApi({ id: str });
            let arr = result.data.data.list;
            this.data = this.data.map((item) => {
              let isHave = arr.find((opt) => opt.id === item.id);
              if (isHave) {
                return {
                  ...item,
                  parent_id: isHave.parent_id,
                  parent_name: isHave.username,
                };
              } else {
                return item;
              }
            });
          } catch (error) {}
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          //  this.params.keywords = "";
          this.getClientList();
        },
        // 排序
        sortChange(val) {
          if (!val) {
            this.params.orderby = "id";
            this.params.sort = "desc";
          } else {
            this.params.orderby = val.sortBy;
            this.params.sort = val.descending ? "desc" : "asc";
          }
          this.getClientList();
        },
        clearKey() {
          this.params.keywords = "";
          this.seacrh();
        },
        seacrh() {
          this.params.page = 1;
          this.getClientList();
        },
        // 获取国家列表
        async getCountry() {
          try {
            const res = await getCountry();
            this.country = res.data.data.list;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        close() {
          this.visible = false;
          this.$refs.userDialog.reset();
        },
        // 添加用户
        addUser() {
          this.visible = true;
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const res = await addClient(this.formData);
              this.$message.success(res.data.msg);
              this.getClientList();
              this.visible = false;
              this.$refs.userDialog.reset();
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
        // 查看用户详情
        handleClickDetail(row) {
          location.href = `client_detail.htm?id=${row.id}`;
        },
        // 子账户
        goDetail(id) {
          location.href = `client_detail.htm?client_id=${id}`;
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
