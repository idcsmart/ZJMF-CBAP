(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("client_records")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}`;
    new Vue({
      components: {
        comConfig,
        comChooseUser,
      },
      data() {
        return {
          baseUrl: str,
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          hover: true,
          params: {
            keywords: "",
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          id: "",
          total: 0,
          pageSizeOptions: [20, 50, 100],
          loading: false,
          title: "",
          delId: "",
          maxHeight: "",
          clinetParams: {
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          clientList: [], // 用户列表
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
          hasTicket: false,
          hasNewTicket: false,
          authList: JSON.parse(
            JSON.stringify(localStorage.getItem("backAuth"))
          ),
          clientDetail: {},
          searchLoading: false,
          scrollDisabled: false,
          recordsParams: {
            id: "",
            page: 1,
            limit: 20,
          },
          recordsTotal: 0,
          recordsList: [],
          totalUpdate: false,
          recordLoading: false,
          loadingText: "",
          delVisible: false,
          delId: "",
          rules: {
            content: [
              {
                required: true,
                message: lang.input + lang.client_info,
                type: "error",
                trigger: "blur",
              },
            ],
          },
          recordFrom: {
            id: "",
            content: "",
            attachment: [],
          },
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          uploadTip: "",
          uploadUrl: str + "/v1/upload",
          optType: "",
          preImg: "",
          tempData: {},
          isLoading: false,
          submitLoading: false,
        };
      },
      computed: {
        calcShow() {
          return (data) => {
            return (
              `#${data.id}-` +
              (data.username
                ? data.username
                : data.phone
                ? data.phone
                : data.email) +
              (data.company ? `(${data.company})` : "")
            );
          };
        },
        isExist() {
          return !this.clientList.find(
            (item) => item.id === this.clientDetail.id
          );
        },
      },
      mounted() {
        document.title =
          lang.info_records + "-" + localStorage.getItem("back_website_name");
        window.addEventListener("scroll", this.scrollBottom, true);
        this.initViewer();
      },
      methods: {
        // 远程搜素
        remoteMethod(key) {
          this.clinetParams.keywords = key;
          this.getClintList();
        },
        filterMethod(search, option) {
          return option;
        },
        // 获取用户详情
        async getUserDetail() {
          try {
            const res = await getClientDetail(this.id);
            this.clientDetail = res.data.data.client;
          } catch (error) {}
        },
        async getPlugin() {
          try {
            const res = await getAddon();
            const temp = res.data.data.list.reduce((all, cur) => {
              all.push(cur.name);
              return all;
            }, []);
            this.hasTicket = temp.includes("IdcsmartTicket");
            this.hasNewTicket = temp.includes("TicketPremium");
          } catch (error) {}
        },
        changeUser(id) {
          this.id = id;
          location.href = `client_log.htm?client_id=${this.id}`;
        },
        async getClintList() {
          try {
            this.searchLoading = true;
            const res = await getClientList(this.clinetParams);
            this.clientList = res.data.data.list;
            this.clientTotal = res.data.data.count;
            this.searchLoading = false;
          } catch (error) {
            this.searchLoading = false;
            console.log(error.data.msg);
          }
        },
        initViewer() {
          this.viewer = new Viewer(document.getElementById("viewer"), {
            button: true,
            inline: false,
            zoomable: true,
            title: true,
            tooltip: true,
            minZoomRatio: 0.5,
            maxZoomRatio: 100,
            movable: true,
            interval: 2000,
            navbar: true,
            loading: true,
          });
        },
        // 附件下载
        downloadfile(url) {
          if (this.totalUpdate) {
            return;
          }
          const name = url;
          console.log("@@@@", url);
          const type = name.substring(name.lastIndexOf(".") + 1);
          if (
            [
              "png",
              "jpg",
              "jepg",
              "bmp",
              "webp",
              "PNG",
              "JPG",
              "JEPG",
              "BMP",
              "WEBP",
            ].includes(type)
          ) {
            this.preImg = host + "/upload/common/default/" + url;
            this.viewer.show();
          } else {
            const downloadElement = document.createElement("a");
            downloadElement.href = host + "/upload/common/default/" + url;
            downloadElement.download = url.split("^")[1]; // 下载后文件名
            document.body.appendChild(downloadElement);
            downloadElement.click(); // 点击下载
          }
        },
        //上传文件之前
        beforeUploadfile(e) {
          let isrepeat = false;
          this.recordFrom.attachment.map((item) => {
            if (item.name === e.name) {
              this.$message.warning(lang.upload_tip);
              isrepeat = true;
            }
          });
          return !isrepeat;
        },
        formatResponse(res) {
          if (res.status !== 200) {
            this.$nextTick(() => {
              this.files = [];
            });
            return this.$message.error(res.msg);
          }
          return { save_name: res.data.save_name, url: res.data.save_name };
        },
        // 上传附件-进度
        uploadProgress(val) {
          if (val.percent) {
            this.uploadTip = "uploaded" + val.percent + "%";
            if (val.percent === 100) {
              this.uploadTip = "";
            }
          }
        },
        uploadSuccess(val) {
          const file = val.fileList.reduce((all, cur) => {
            all.push(typeof cur === "string" ? cur : cur.response.save_name);
            return all;
          }, []);
          if (this.optType === "add") {
            this.recordsList[0].attachment = file;
          } else {
            const index = this.recordsList.findIndex(
              (item) => item.id === this.delId
            );
            this.recordsList[index].attachment = file;
          }
        },
        //删除上传文件
        delfiles(index, ind) {
          this.recordFrom.attachment.splice(ind, 1);
          this.recordsList[index].attachment.splice(ind, 1);
        },
        //上传失败
        handleFail({ file }) {},
        cancelItem(item) {
          if (this.optType === "add") {
            this.recordsList.splice(0, 1);
            this.totalUpdate = false;
            return;
          }
          item.edit = false;
          this.totalUpdate = false;
          this.recordFrom = this.tempData;
          this.recordsList = this.recordsList.map((el) => {
            if (el.id === item.id) {
              el.attachment = this.tempData.attachment;
            }
            return el;
          });
        },
        editItem(item) {
          if (this.totalUpdate) {
            return this.$message.warning(lang.order_type_verify3);
          }
          this.optType = "update";
          this.totalUpdate = true;
          item.edit = true;
          this.delId = item.id;
          this.recordFrom = JSON.parse(JSON.stringify(item));
          this.tempData = JSON.parse(JSON.stringify(item));
        },
        // 删除记录
        delItem(row) {
          this.delId = row.id;
          this.delVisible = true;
        },
        async sureDelUser() {
          try {
            this.submitLoading = true;
            const res = await deleteRecord({
              id: this.delId,
            });
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.getRecordsList();
            this.totalUpdate = false;
            this.recordsList.splice(
              this.recordsList.findIndex((item) => item.id === this.delId),
              1
            );
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.delVisible = false;
            this.$message.error(error.data.msg);
          }
        },

        // 提交信息记录
        async confirmRecord({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = JSON.parse(JSON.stringify(this.recordFrom));
              params.attachment = params.attachment.reduce((all, cur) => {
                all.push(typeof cur === "string" ? cur : cur.response.url);
                return all;
              }, []);
              if (this.optType === "add") {
                params.id = this.id;
              } else {
                params.id = this.delId;
              }
              this.submitLoading = true;
              const res = await addAndUpdateRecord(this.optType, params);
              this.$message.success(res.data.msg);
              this.recordsParams.page = 1;
              this.getRecordsList();
              this.totalUpdate = false;
              this.submitLoading = false;
            } catch (error) {
              this.submitLoading = false;
              this.totalUpdate = false;
              this.$message.error(error.data.msg);
            }
          } else {
            this.$message.warning(firstError);
          }
        },
        // 滚动计算
        scrollBottom() {
          const clientHeight =
            document.documentElement.clientHeight || document.body.clientHeight;
          if (
            document.querySelector(".loading").getBoundingClientRect().top <
            clientHeight
          ) {
            if (this.scrollDisabled) {
              this.loadingText = lang.no_more_data;
              return;
            } else {
              this.loadingText = lang.loading;
              if (
                this.recordsList.length === this.recordsTotal &&
                this.recordsTotal !== 0
              ) {
                return;
              }
              if (this.isLoading) {
                return;
              }
              this.recordsParams.page++;
              this.getRecordsList();
            }
          }
        },
        addRecord() {
          this.optType = "add";
          this.totalUpdate = true;
          this.recordsList.unshift({
            id: this.id,
            admin_name: localStorage.getItem("name") || "",
            content: "",
            attachment: [],
            edit: true,
            create_time: new Date().getTime() / 1000,
          });
          this.recordFrom.content = "";
          this.recordFrom.attachment = [];
          this.recordsParams.page = 1;
        },
        async getRecordsList() {
          try {
            this.isLoading = true;
            this.recordLoading = true;
            const res = await getRecordList(this.recordsParams);
            this.recordsTotal = res.data.data.count;
            if (this.recordsParams.page > 1) {
              this.recordsList = Array.from(
                new Set(this.recordsList.concat(res.data.data.list))
              );
            } else {
              this.recordsList = res.data.data.list;
            }
            this.recordLoading = false;
            if (
              this.recordsTotal !== 0 &&
              this.recordsList.length < this.recordsTotal
            ) {
              this.scrollDisabled = false;
            } else {
              this.scrollDisabled = true;
            }
            this.isLoading = false;
            this.recordsList = this.recordsList
              .map((item) => {
                item.edit = false;
                return item;
              })
              .sort((a, b) => b.id * 1 - a.id * 1);
          } catch (error) {
            this.recordLoading = false;
            this.$message.error(error.data.msg);
          }
        },
      },
      created() {
        this.id = this.recordsParams.id =
          location.href.split("?")[1].split("=")[1] * 1;
        // this.getClintList();
        this.getPlugin();
        this.getUserDetail();
        this.getRecordsList();
      },
      destroyed() {
        window.removeEventListener("scroll", this.scrollBottom);
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
