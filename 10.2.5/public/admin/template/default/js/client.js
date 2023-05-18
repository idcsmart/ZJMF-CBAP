(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("client")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
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
              width: 200,
              ellipsis: true,
            },
            {
              colKey: "phone",
              title: lang.contact,
              width: 400,
              ellipsis: true,
            },
            {
              colKey: "email",
              title: lang.email,
              width: 400,
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
          },
          curLevelId: "",
          total: 0,
          pageSizeOptions: [20, 50, 100],
          formData: {
            // 添加用户
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
          maxHeight: "",
          authList: JSON.parse(
            JSON.stringify(localStorage.getItem("backAuth"))
          ),
          /* 用户等级 */
          levelList: [],
          hasPlugin: false,
          addonArr: []
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
      },
      mounted() {
        this.maxHeight = document.getElementById("content").clientHeight - 170;
        let timer = null;
        window.onresize = () => {
          if (timer) {
            return;
          }
          timer = setTimeout(() => {
            this.maxHeight =
              document.getElementById("content").clientHeight - 170;
            clearTimeout(timer);
            timer = null;
          }, 300);
        };
      },
      created() {
        const href = location.href.split("?")[1];
        if (href) {
          this.curLevelId = location.href.split("?")[1]?.split("=")[1] * 1;
        }
        // 权限相关
        if (!this.authList.includes("ClientController::clientList")) {
          return this.$message.error(lang.tip17 + "," + lang.tip18);
        }
        this.getClientList();
        this.getCountry();
      },
      methods: {
        async getPlugin() {
          try {
            const res = await getAddon();
            this.addonArr = res.data.data.list.map(item => item.name)
            this.hasPlugin = this.addonArr.includes("IdcsmartClientLevel");
            this.hasPlugin && this.getLevel();
            this.addonArr.includes('IdcsmartSubAccount') && this.getSubUser()
            if (this.addonArr.includes('IdcsmartCertification')) {
              this.columns.splice(2, 0, { colKey: "certification", title: '实名状态', width: 150 })
            }
          } catch (error) { }
        },
        rowClick(e) {
          location.href = `client_detail.html?client_id=${e.row.id}`;
        },
        /* 用户等级 */
        async getLevel() {
          try {
            const res = await getAllLevel();
            this.levelList = res.data.data.list;
          } catch (error) { }
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
            this.addonArr.length === 0 && this.getPlugin()
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
          } catch (error) {

          }
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
              const res = await addClient(this.formData);
              this.$message.success(res.data.msg);
              this.getClientList();
              this.visible = false;
              this.$refs.userDialog.reset();
            } catch (error) {
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
            // this.$message.warning(firstError);
          }
        },
        // 查看用户详情
        handleClickDetail(row) {
          location.href = `client_detail.html?id=${row.id}`;
        },
        // 停用/启用
        changeStatus(row) {
          event.stopPropagation();
          this.delId = row.id;
          this.curStatus = row.status;
          this.statusTip = this.curStatus ? lang.sure_Close : lang.sure_Open;
          this.statusVisble = true;
        },
        async sureChange() {
          try {
            let tempStatus = this.curStatus === 1 ? 0 : 1;
            const res = await changeOpen(this.delId, { status: tempStatus });
            this.$message.success(res.data.msg);
            this.statusVisble = false;
            this.getClientList();
          } catch (error) {
            console.log(error);
          }
        },
        closeDialog() {
          this.statusVisble = false;
        },

        // 删除用户
        deleteUser(row) {
          event.stopPropagation();
          this.delVisible = true;
          this.delId = row.id;
        },
        async sureDel() {
          try {
            await deleteClient(this.delId);
            this.$message.success(window.lang.del_success);
            this.params.page =
              this.data.length > 1 ? this.params.page : this.params.page - 1;
            this.delVisible = false;
            this.getClientList();
          } catch (error) {
            this.delVisible = false;
            this.$message.error(error.data.msg);
          }
        },
        cancelDel() {
          this.delVisible = false;
        },
        // 子账户
        goDetail(id) {
          console.log(111);
          location.href = `client_detail.html?client_id=${id}`;
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
