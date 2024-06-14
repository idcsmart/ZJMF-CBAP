(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    new Vue({
      components: {
        asideMenu,
        topMenu,
        pagination,
      },
      async created() {
        // 获取当前是主账号还是子账户
        this.is_sub_account = localStorage.getItem("is_sub_account");
        this.getCommonData();
        this.getCildAccountList();
      },
      mounted() {},
      updated() {
        // // 关闭loading
        // document.getElementById('mainLoading').style.display = 'none';
        // document.getElementsByClassName('template')[0].style.display = 'block'
      },
      destroyed() {},
      data() {
        return {
          params: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },
          commonData: {},
          dialogVisible: false,
          cildAccountList: [],
          is_sub_account: 1, // 默认是子账户
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
        // 每页展示数改变
        sizeChange(e) {
          this.params.limit = e;
          this.params.page = 1;
          // 获取列表
        },
        // 当前页改变
        currentChange(e) {
          this.params.page = e;
        },

        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          document.title =
            this.commonData.website_name + "-" + lang.subaccount_text46;
        },
        addChildAccountBtn() {
          location.href = "addChildAccount.htm";
        },
        // 获取子账户列表
        async getCildAccountList() {
          const res = await queryChildAccountListAPI(this.params);
          if (res.status == 200) {
            const { data } = res.data;
            this.cildAccountList = data.list;
            this.params.total = data.count;
          }
        },
        changeState(obj) {
          let str = "";
          let status = 0;
          if (obj.status) {
            str = lang.subaccount_text47;
            status = 0;
          } else {
            str = lang.subaccount_text48;
            status = 1;
          }
          let params = {
            id: obj.id,
            status: status,
          };
          this.$confirm(str, lang.subaccount_text49, {
            confirmButtonText: lang.subaccount_text50,
            cancelButtonText: lang.subaccount_text51,
            type: "warning",
          })
            .then(async () => {
              try {
                const res = await changeChildAccountDteailAPI(params);
                if (res.status === 200) {
                  await this.getCildAccountList();
                  this.$message.success(lang.subaccount_text44);
                }
              } catch (error) {
                this.$message.warning(error.data.msg);
              }
            })
            .catch((err) => {});
        },
        handleEdit(obj) {
          location.href = `addChildAccount.htm?id=${obj.id}&type=edit`;
        },
        handleDel(obj) {
          this.$confirm(lang.subaccount_text52, lang.subaccount_text49, {
            confirmButtonText: lang.subaccount_text50,
            cancelButtonText: lang.subaccount_text51,
            type: "warning",
          })
            .then(async () => {
              const res = await delChildAccountAPI(obj);
              if (res.status === 200) {
                this.getCildAccountList();
                this.$message.success(lang.subaccount_text53);
              }
            })
            .catch((err) => {
              console.log(err);
            });
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
