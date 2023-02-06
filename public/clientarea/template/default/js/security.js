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
      created() {
        this.getCommonData();
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
          isShowAPI: false,
          isShowAPILog: false,
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
          activeName: "1",
          dataList: [],
          loading: false,
          // 创建api弹窗
          isShowCj: false,
          // 创建成功信息显示弹窗
          isShowCj2: false,
          // 创建api成功返回数据
          apiData: {},
          checked: false,
          apiName: "",
          errText: "",
          delName: "",
          delId: "",
          isShowDel: false,
          isShowWhiteIp: false,
          whiteIpData: {
            id: 0,
            status: 0,
            ip: "",
          },
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
        getRule(arr) {
          let isShow1 = this.showFun(arr, "ApiController::list");
          let isShow2 = this.showFun(arr, "LogController::list");
          if (isShow1) {
            this.isShowAPI = true;
            this.activeName = this.activeName;
            this.getApiList();
          } else {
            this.activeName = "2";
          }
          if (isShow2) {
            this.isShowAPILog = true;
          }
          this.handleClick();
        },
        showFun(arr, str) {
          if (typeof arr == "string") {
            return true;
          } else {
            let isShow = "";
            isShow = arr.find((item) => {
              let isHave = item.includes(str);
              if (isHave) {
                return isHave;
              }
            });
            return isShow;
          }
        },
        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
          document.title = this.commonData.website_name + "-安全";
        },
        // 每页展示数改变
        sizeChange(e) {
          this.params.limit = e;
          this.params.page = 1;
          // 获取列表
          this.getApiList();
        },
        // 当前页改变
        currentChange(e) {
          this.params.page = e;
          this.getApiList();
        },
        inputChange() {
          this.params.page = 1;
          this.getApiList();
        },
        // 获取APi列表
        getApiList() {
          this.loading = true;
          apiList(this.params)
            .then((res) => {
              if (res.data.status === 200) {
                this.dataList = res.data.data.list;
                this.params.total = res.data.data.count;
              }
              this.loading = false;
            })
            .catch((err) => {
              this.loading = false;
            });
        },
        handleClick() {
          console.log(this.activeName);
          if (this.activeName == 2) {
            location.href = "security_ssh.html";
          }
          if (this.activeName == 3) {
            location.href = "security_log.html";
          }
          if (this.activeName == 4) {
            location.href = "security_group.html";
          }
        },
        // 删除
        deleteItem(row) {
          this.delName = row.name;
          this.delId = row.id;
          this.isShowDel = true;
        },
        // 创建api弹窗相关
        cjClose() {
          this.isShowCj = false;
        },
        // 显示创建api弹窗
        showCreateApi() {
          this.isShowCj = true;
          this.apiName = "";
          this.errText = "";
        },
        // 创建API秘钥 提交
        cjSub() {
          let isPass = true;
          if (!this.apiName) {
            this.errText = "请输入api名称";
            isPass = false;
          }

          if (isPass) {
            this.errText = "";
            const params = {
              name: this.apiName,
            };
            createApi(params)
              .then((res) => {
                if (res.data.status === 200) {
                  // 关闭弹窗
                  this.isShowCj = false;
                  // 获取返回信息 并在新弹窗进行展示
                  this.apiData = res.data.data;
                  this.isShowCj2 = true;
                }
              })
              .catch((err) => {
                this.errText = err.data.msg;
              });
          }
        },
        cj2Close() {
          this.isShowCj2 = false;
        },
        cj2Sub() {
          let isPass = true;
          if (!this.checked) {
            this.errText = "请保存信息后勾选";
            isPass = false;
            return false;
          }

          if (isPass) {
            this.errText = "";
            this.isShowCj2 = false;
            this.getApiList();
          }
        },
        // token 复制
        copyToken(token) {
          if (navigator.clipboard && window.isSecureContext) {
            // navigator clipboard 向剪贴板写文本
            this.$message.success("复制成功");
            return navigator.clipboard.writeText(token);
          } else {
            // 创建text area
            const textArea = document.createElement("textarea");
            textArea.value = token;
            // 使text area不在viewport，同时设置不可见
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            this.$message.success("复制成功");
            return new Promise((res, rej) => {
              // 执行复制命令并移除文本框
              document.execCommand("copy") ? res() : rej();
              textArea.remove();
            });
          }
        },
        // 取消删除
        delClose() {
          this.isShowDel = false;
        },
        // 确认删除
        delSub() {
          this.isShowDel = false;
          const params = {
            id: this.delId,
          };
          delApi(params)
            .then((res) => {
              if (res.data.status === 200) {
                this.isShowDel = false;
                this.$message.success(res.data.msg);
                this.getApiList();
              }
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        },
        // ip白名单设置弹窗 展示
        showWhiteIp(row) {
          this.whiteIpData.ip = row.ip;
          this.whiteIpData.id = row.id;
          this.whiteIpData.status = row.status.toString();
          console.log("status", this.whiteIpData.status);
          this.errText = "";
          this.isShowWhiteIp = true;
        },
        whiteIpClose() {
          this.isShowWhiteIp = false;
        },
        whiteIpSub() {
          let isPass = true;
          const data = this.whiteIpData;
          if (data.status == 1) {
            if (!data.ip) {
              this.errText = "请输入ip";
              isPass = false;
            }
          }

          if (isPass) {
            this.errText = "";
            const params = {
              ...data,
            };
            whiteApi(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.isShowWhiteIp = false;
                  this.getApiList();
                  this.$message.success(res.data.msg);
                }
              })
              .catch((error) => {
                this.errText = error.data.msg;
              });
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
