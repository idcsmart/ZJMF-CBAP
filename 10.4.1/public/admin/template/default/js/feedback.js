(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("feedback")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
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
          classParams: {
            id: "",
            name: "",
            description: "",
          },
          list: [],
          typeColumns: [
            {
              colKey: "title",
              title: lang.title,
              ellipsis: true,
            },
            {
              colKey: "type",
              title: lang.type,
              ellipsis: true,
            },
            {
              colKey: "description",
              title: lang.description,
              ellipsis: true,
            },
            {
              colKey: "username",
              title: lang.order_client,
              ellipsis: true,
            },
            {
              colKey: "contact",
              title: lang.contact,
              ellipsis: true,
            },
            {
              colKey: "create_time",
              title: lang.feedback_time,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 120,
            },
          ],
          groupColumns: [
            // 套餐表格
            {
              colKey: "image_group_name",
              title: lang.type_manage,
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: 120,
            },
          ],
          typeRules: {
            name: [
              {
                required: true,
                message: lang.input + lang.feedback_type,
                type: "error",
                trigger: "blur",
              },
              // {
              //   validator: val => val?.length <= 10, message: lang.verify8 + '1-10', type: 'warning'
              // }
            ],
            description: [
              {
                required: true,
                message: lang.input + lang.type + lang.description,
                type: "error",
                trigger: "blur",
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
          typeList: [],
          typeLoading: false,
          detailObj: {
            description: "",
            attachment: [],
          },
          preImg: "",
        };
      },
      computed: {},
      methods: {
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
        // 获取反馈
        async getFeedbackList() {
          try {
            this.loading = true;
            const res = await getFeedback(this.params);
            this.list = res.data.data.list;
            this.total = res.data.data.count;
            this.loading = false;
          } catch (error) {
            this.loading = false;
            this.$message.error(error.data.msg);
          }
        },
        async getFeedbackTypeList() {
          try {
            this.typeLoading = true;
            const res = await getFeedbackType();
            this.systemGroup = res.data.data.list;
            this.typeLoading = false;
          } catch (error) {
            this.typeLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        lookDetail(row) {
          this.detailModel = true;
          this.detailObj = Object.assign(row);
        },
        // 附件下载
        downloadFile(url) {
          const name = url;
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
            this.preImg = url;
            this.viewer.show();
          } else {
            const downloadElement = document.createElement("a");
            downloadElement.href = url;
            downloadElement.download = url.split("^")[1]; // 下载后文件名
            document.body.appendChild(downloadElement);
            downloadElement.click(); // 点击下载
          }
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getFeedbackList();
        },
        // 分类管理
        classManage() {
          this.classModel = true;
          this.classParams.name = "";
          this.classParams.description = "";
          this.optType = "add";
          console.log(11111);
        },
        async submitSystemGroup({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const params = JSON.parse(JSON.stringify(this.classParams));
              if (this.optType === "add") {
                delete params.id;
                params.product_id = this.id;
              }
              this.submitLoading = true;
              const res = await addAndUpdateFeedbackType(this.optType, params);
              this.$message.success(res.data.msg);
              this.getFeedbackTypeList();
              this.submitLoading = false;
              this.classParams.name = "";
              this.classParams.description = "";
              this.$refs.classForm.clearValidate();
              this.optType = "add";
            } catch (error) {
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
        deleteGroup(row) {
          this.delId = row.id;
          this.delVisible = true;
        },
        async sureDelete() {
          try {
            this.submitLoading = true;
            const res = await delFeedbackType({
              id: this.delId,
            });
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.getFeedbackTypeList();
            this.classParams.name = "";
            this.classParams.icon = "";
            this.$refs.classForm.reset();
            this.optType = "add";
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
      },
      created() {
        this.getFeedbackList();
        this.getFeedbackTypeList();
        document.title =
          lang.feedback + "-" + localStorage.getItem("back_website_name");
      },
      mounted() {
        this.initViewer();
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
