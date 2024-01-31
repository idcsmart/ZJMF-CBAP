(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = window.lang;
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    new Vue({
      components: {
        opinionButton,
      },
      data() {
        return {
          message: "template...",
          // 工单类型数据
          orderTypeData: [],
          prereplyContent: "",
          // 工单类型下拉框
          orderTypeOptions: [],
          popupVisible: false,
          // 工单状态数据
          orderStatusData: [],
          // 工单部门
          departmentList: [],
          // 预设回复列表
          prereplyList: [],
          editReplayItem: {},
          // 完结状态下拉框
          statusOpitons: [
            {
              statusText: lang.order_text63,
              status: 1,
            },
            {
              statusText: lang.order_text64,
              status: 0,
            },
          ],
          // 指定部门下拉框数据（管理员分组列表数据）
          departmentOptions: [],
          // 指定人员下拉框数据（分组下管理员）
          adminsOptions: [],
          // 所有人员数据
          adminList: [],
          isSubmit: false,
          isSubStatus: false,
          isUpdateType: false,
          isUpdateStatus: false,
          isAddType: false,
          isAddStatus: false,
          isEditReply: false,
          deleteVisible: false,
          isAddReply: false,
          saveLoading: false,
          columns: [
            {
              colKey: "name",
              title: lang.order_text65,
              cell: "name",
              width: "360",
            },
            {
              colKey: "admin",
              title: lang.order_text66,
              cell: "department",
              width: "360",
            },
            {
              colKey: "op",
              title: lang.order_text67,
              cell: "op",
              width: "360",
              width: "128",
            },
          ],
          columns2: [
            {
              colKey: "index",
              title: lang.order_text68,
              cell: "index",
              width: "193",
            },
            {
              colKey: "name",
              title: lang.order_text69,
              cell: "name",
              width: "302",
            },
            {
              colKey: "color",
              title: lang.order_text70,
              cell: "color",
              width: "302",
            },
            {
              colKey: "statusText",
              title: lang.order_text71,
              cell: "status",
              width: "302",
            },
            {
              colKey: "op",
              title: lang.order_text67,
              cell: "op",
              width: "128",
            },
          ],
          columns3: [
            {
              colKey: "content",
              title: lang.order_text72,
              cell: "content",
            },
            {
              colKey: "op",
              title: lang.order_text67,
              cell: "op",
              width: "128",
            },
          ],
          configObj: {
            refresh_time: "",
            ticket_notice_open: 0,
            ticket_notice_description: ""
          },
          submitLoading: false
        };
      },
      computed: {},
      computed: {
        calcName() {
          return (arr) => {
            if (arr.length > 0) {
              const temp = arr.reduce((all, cur) => {
                all.push(cur.name);
                return all;
              }, []);
              return temp.join("，");
            }
            return "";
          };
        },
        calcId() {
          return (arr) => {
            const temp = arr.reduce((all, cur) => {
              all.push(cur.id);
              return all;
            }, []);
            return temp;
          };
        },
      },
      methods: {
        async getConfig () {
          try {
            const res = await getOrderConfig()
            this.configObj = res.data.data
          } catch (error) {

          }
        },
        async saveConfig () {
          try {
            this.submitLoading = true
            const res = await saveOrderConfig(this.configObj)
            this.$message.success(res.data.msg)
            this.submitLoading = false
            this.getConfig()
          } catch (error) {
            this.$message.error(error.data.msg)
          }
        },
        // 初始化富文本
        initTemplate() {
          tinymce.init({
            selector: "#tiny",
            language_url: "/tinymce/langs/zh_CN.js",
            language: "zh_CN",
            min_height: 400,
            width: "100%",
            plugins:
              "link lists image code table colorpicker textcolor wordcount contextmenu fullpage paste",
            toolbar:
              "bold italic underline strikethrough | fontsizeselect | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote | undo redo | link unlink image fullpage code | removeformat",
            images_upload_url: str + "v1/upload",
            paste_data_images: true,
            convert_urls: false,
            //粘贴图片后，自动上传
            urlconverter_callback: function (url, node, on_save, name) {
              return url;
            },
            images_upload_handler: this.handlerAddImg,
          });
        },
        // 富文本上传图片
        handlerAddImg(blobInfo, success, failure) {
          return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append("file", blobInfo.blob());
            axios
              .post(`${str}v1/upload`, formData, {
                headers: {
                  Authorization:
                    "Bearer" + " " + localStorage.getItem("backJwt"),
                },
              })
              .then((res) => {
                const json = {};
                if (res.status !== 200) {
                  failure("HTTP Error: " + res.data.msg);
                  return;
                }
                // json = JSON.parse(res)
                json.location = res.data.data?.image_url;
                if (!json || typeof json.location !== "string") {
                  failure("Error:" + res.data.msg);
                  return;
                }
                success(json.location);
              });
          });
        },
        // 添加工单类型
        appendToRoot() {
          if (this.isUpdateType || this.isAddType) {
            return this.$message.error(lang.please_save);
          }
          this.orderTypeData.push({
            isedit: false,
            name: "",
            isAdd: true,
            arr: [],
            arr1: [],
          });
          this.isAddType = true;
        },
        // 获取工单类型数据
        getOrderTypeOptions() {
          getUserOrderType()
            .then((result) => {
              this.orderTypeOptions = result.data.data.list.map((item) => {
                return { name: item.name };
              });
              this.getDepartmentOptions();
              this.orderTypeData = result.data.data.list.map((item) => {
                item.isedit = false;
                item.status = "edit";
                item.admin_role_id = 0;
                // // 设置选中的管理员id
                const temp = item.admin.reduce((all, cur) => {
                  all.push(cur.id);
                  return all;
                }, []);
                item.arr = temp;
                item.arr1 = temp;
                return item;
              });
            })
            .catch();
        },
        onPopupVisibleChange(val) {
          this.popupVisible = val;
        },
        onClear(row) {
          row.name = "";
        },
        onOptionClick(item, row) {
          row.name = item;
          this.popupVisible = false;
        },
        onInputChange(val, row) {
          row.name = val;
        },
        // 编辑
        edithandleClickOp(id) {
          if (this.isUpdateType) {
            return this.$message.error(lang.please_save);
          }
          this.isUpdateType = true;
          for (let i = 0; i < this.orderTypeData.length; i++) {
            if (id === this.orderTypeData[i].id) {
              this.$set(this.orderTypeData[i], "isedit", true);
            }
          }
        },
        // 工单-工单类型管理-删除
        async orderTypeMgtDelete(row) {
          const result = await orderTypeDelete(row.id);
          if (result.status === 200) {
            this.$message.success({
              content: result.data.msg,
              placement: "top-right",
            });
            this.getOrderTypeOptions();
          } else {
            this.$message.warning({
              content: result.data.msg,
              placement: "top-right",
            });
          }
        },
        // 取消新增类型
        deleteClickadd() {
          this.orderTypeData.pop();
          this.isAddType = false;
        },
        //取消修改
        canceledit(item) {
          this.isUpdateType = false;
          item.arr1 = item.arr;
          this.getOrderTypeOptions();
        },
        // 工单-工单类型管理-保存
        async orderTypeMgtSave(row) {
          if (row.arr1.length === 0 || !row.name) {
            this.$message.warning({
              content: lang.order_type_verify1,
              placement: "top-right",
            });
            return;
          }
          const params = {
            admin_id: row.arr1,
            name: row.name,
          };
          if (this.isSubmit) {
            return;
          }
          this.isSubmit = true;
          if (row.status === "edit") {
            params.id = row.id;
            await orderTypeEdit(row.id, params)
              .then((result) => {
                this.$message.success({
                  content: result.data.msg,
                  placement: "top-right",
                });
                this.getOrderTypeOptions();
              })
              .catch((result) => {
                this.$message.warning({
                  content: result.data.msg,
                  placement: "top-right",
                });
              });
          } else {
            await orderTypeAdd(params)
              .then((result) => {
                this.$message.success({
                  content: result.data.msg,
                  placement: "top-right",
                });
                this.getOrderTypeOptions();
              })
              .catch((result) => {
                this.$message.warning({
                  content: result.data.msg,
                  placement: "top-right",
                });
              });
          }
          this.isSubmit = false;
          this.isUpdateType = false;
          this.isAddType = false;
        },
        // 删除
        deleteClickOp(id) {
          deletehelptype({ id })
            .then((res) => {
              this.$message.success(res.data.msg);
              this.getOrderTypeOptions();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        },
        // 工单-转内部-选择部门变化
        departmentChange(val, item) {
          // 获取部门id对应部门名称
          // const department = this.departmentOptions.filter(item => item.id === val)[0];
          // const name = department ? department.name : null;
          // const optionList = [];
          // // 清除已选人员数据
          // this.adminList.forEach(item => {
          //   if (name && item.roles === name) {
          //     optionList.push(item);
          //   }
          // });
          // this.adminsOptions = optionList;
          item.arr1 = val;
        },
        // 获取工单状态列表
        getTicketStatus() {
          ticketStatus().then((res) => {
            res.data.data.list.forEach((item, index) => {
              if (item["default"] === 1) {
                item.noEdit = true;
              }
              if (item["status"] === 1) {
                item.statusText = lang.order_text63;
              } else if (item["status"] === 0) {
                item.statusText = lang.order_text64;
              } else {
                item.statusText = "--";
              }
              delete item["default"];
              item.index = index + 1;
              item.isedit = false;
            });
            this.orderStatusData = res.data.data.list;
          });
        },
        // 编辑状态
        editStatus(row) {
          if (this.isUpdateStatus) {
            return this.$message.error(lang.please_save);
          }
          if (row.noEdit) {
            return this.$message.error(lang.order_text73);
          }
          this.isUpdateStatus = true;
          for (let i = 0; i < this.orderStatusData.length; i++) {
            if (row.id === this.orderStatusData[i].id) {
              this.$set(this.orderStatusData[i], "isedit", true);
            }
          }
        },
        // 保存状态
        async orderStatustSave(row) {
          console.log(row);
          if (!row.name || !row.color || row.status === "") {
            this.$message.warning({
              content: lang.order_text74,
              placement: "top-right",
            });
            return;
          }
          const params = {
            name: row.name,
            color: row.color,
            status: row.status,
          };
          if (this.isSubStatus) {
            return;
          }
          this.isSubStatus = true;
          if (row.isedit) {
            params.id = row.id;
            await editTicketStatus(params)
              .then((result) => {
                this.$message.success({
                  content: result.data.msg,
                  placement: "top-right",
                });
                this.getTicketStatus();
              })
              .catch((result) => {
                this.$message.warning({
                  content: result.data.msg,
                  placement: "top-right",
                });
              });
          } else {
            await addTicketStatus(params)
              .then((result) => {
                this.$message.success({
                  content: result.data.msg,
                  placement: "top-right",
                });
                this.getTicketStatus();
              })
              .catch((result) => {
                this.$message.warning({
                  content: result.data.msg,
                  placement: "top-right",
                });
              });
          }
          this.isSubStatus = false;
          this.isUpdateStatus = false;
          this.isAddStatus = false;
        },
        // 添加工单状态
        appendStatus() {
          if (this.isUpdateStatus || this.isAddStatus) {
            return this.$message.error(lang.please_save);
          }
          const index = this.orderStatusData.length + 2;
          this.orderStatusData.push({
            isedit: false,
            name: "",
            isAdd: true,
            index: index,
          });
          this.isAddStatus = true;
        },
        // 工单状态删除
        async orderStatusMgtDelete(row) {
          if (row.noEdit) {
            return this.$message.error(lang.order_text75);
          }
          const result = await deleteTicketStatus(row.id).catch((result) => {
            this.$message.warning({
              content: result.data.msg,
              placement: "top-right",
            });
          });
          if (result.status === 200) {
            this.$message.success({
              content: result.data.msg,
              placement: "top-right",
            });
            this.getTicketStatus();
          } else {
            this.$message.warning({
              content: result.data.msg,
              placement: "top-right",
            });
          }
        },
        // 取消新增状态
        deleteStatusadd() {
          this.orderStatusData.pop();
          this.isAddStatus = false;
        },

        // 取消修改状态
        cancelStatusEdit() {
          this.isUpdateStatus = false;
          this.getTicketStatus();
        },
        // 工单-转发-选择人员变化
        adminChange(val) {
          this.$forceUpdate();
        },
        // 获取部门数据
        getDepartmentOptions() {
          getAdminList({ page: 1, limit: 10000 })
            .then((result) => {
              this.departmentOptions = result.data.data.list;
              this.orderTypeData.forEach((item, index) => {
                this.departmentOptions.forEach((items) => {
                  if (item.role_name === items.name) {
                    item.admin_role_id = items.id;
                  }
                });
              });
            })
            .catch();
        },
        // 获取人员数据
        getAdminList() {
          getAdminList({ page: 1, limit: 10000 })
            .then((result) => {
              this.adminList = result.data.data.list;
            })
            .catch();
        },
        // 时间格式转换
        formatDate(dateStr) {
          const date = new Date(dateStr * 1000);
          const str1 = [
            date.getFullYear(),
            date.getMonth() + 1,
            date.getDate(),
          ].join("-");
          const str2 = [
            this.formatDateAdd0(date.getHours()),
            this.formatDateAdd0(date.getMinutes()),
            this.formatDateAdd0(date.getSeconds()),
          ].join(":");
          return str1 + " " + str2;
        },
        formatDateAdd0(m) {
          return m < 10 ? "0" + m : m;
        },

        // 获取工单预设回复列表
        getTicketPrereply() {
          ticketPrereply().then((res) => {
            this.prereplyList = res.data.data.list;
          });
        },
        // 编辑预设回复
        editPrereply(row) {
          if (this.isEditReply) {
            return this.$message.error(lang.order_text76);
          }
          this.isEditReply = true;
          tinyMCE.editors[0].setContent(row.content);
          this.editReplayItem = row;
        },
        // 删除预设回复
        async deletePrereply(row) {
          if (this.isEditReply) {
            return this.$message.error(lang.order_text76);
          }
          await deleteTicketPrereply(row.id);
          this.getTicketPrereply();
        },

        handelDelete() {},
        // 保存预设回复
        async savePreReplay() {
          this.saveLoading = true;
          const content = tinyMCE.editors[0].getContent();
          const params = {
            content: content,
          };
          // 编辑
          if (this.isEditReply) {
            params.id = this.editReplayItem.id;
            await editTicketPrereply(params);
            this.isEditReply = false;
            tinyMCE.editors[0].setContent("");
            this.getTicketPrereply();
          } else {
            await addTicketPrereply(params);
            tinyMCE.editors[0].setContent("");
            this.getTicketPrereply();
          }
          this.saveLoading = false;
        },
      },
      created() {
        this.getOrderTypeOptions();
        this.getTicketStatus();
        this.getTicketPrereply();
        this.getConfig()
      },
      mounted() {
        this.initTemplate();
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
