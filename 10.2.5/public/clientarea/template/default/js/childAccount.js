(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = window.lang;
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
          this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
          document.title = this.commonData.website_name + "-子账户列表";

        },
        addChildAccountBtn() {
          location.href = "/addChildAccount.html";
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
            str = "此操作将停用该子账户, 是否继续";
            status = 0;
          } else {
            str = "此操作将启用该子账户, 是否继续";
            status = 1;
          }
          let params = {
            id: obj.id,
            status: status,
          };
          this.$confirm(str, "提示", {
            confirmButtonText: "确定",
            cancelButtonText: "取消",
            type: "warning",
          })
            .then(async () => {
              try {
                const res = await changeChildAccountDteailAPI(params);
                if (res.status === 200) {
                  await this.getCildAccountList();
                  this.$message.success("修改成功");
                }
              } catch (error) {
                this.$message.warning(error.data.msg);
              }
            })
            .catch((err) => {
              console.log(err);
            });
        },
        handleEdit(obj) {
          location.href = `/addChildAccount.html?id=${obj.id}&type=edit`;
        },
        handleDel(obj) {
          this.$confirm("此操作将永久删除该子账户, 是否继续?", "提示", {
            confirmButtonText: "确定",
            cancelButtonText: "取消",
            type: "warning",
          })
            .then(async () => {
              const res = await delChildAccountAPI(obj);
              if (res.status === 200) {
                this.getCildAccountList();
                this.$message.success("删除成功");
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
