(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const help = document.getElementsByClassName("promocode")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      data() {
        return {
          message: "help...",
          params: {
            keywords: "",
            orderby: "id",
            sort: "desc",
          },
          activetabs: 1,
          visible: false,
          visible3: false,
          visible4: false,
          moveData: {
            addon_idcsmart_file_folder_id: "",
          },
          columns: [
            {
              colKey: "id",
              type: "multiple",
              className: "demo-multiple-select-cell",
              // reserveelection:"true",
              width: 64,
            },
            {
              colKey: "code",
              title: "优惠码代号",
            },
            {
              maxwidth: "200",
              colKey: "name",
              title: "优惠码名称",
              // ellipsis: true,
              align: "center",
              cell: "name",
            },
            {
              colKey: "type",
              title: "优惠类型",
              cell: "type",
            },
            {
              colKey: "apply_client",
              title: "适用客户",
            },
            {
              width: "300",
              colKey: "",
              title: "有效时间段",
              cell: "validtime",
            },
            {
              colKey: "",
              title: "已/总使用次数",
              cell: "times",
            },

            {
              colKey: "status",
              title: "当前状态",
              cell: "status",
            },
            {
              colKey: "op",
              width: 200,
              title: "操作",
              cell: "op",
            },
          ],

          columns2: [
            {
              width: "200",
              colKey: "name",
              className: "columns2name",
              title: "文件名称",
            },
            {
              colKey: "addon_idcsmart_file_folder_id",
              title: "文件夹",
              cell: "folder",
            },
            {
              colKey: "visible_range",
              title: "可见范围",
              cell: "range",
            },
            {
              colKey: "product_id",
              title: "指定产品",
              cell: "product",
            },

            {
              colKey: "hidden",
              width: 200,
              title: "显/隐",
              cell: "op",
            },
          ],
          uploadfilelist: [],
          pagination: {
            current: 1,
            pageSize: 20,
            pageSizeOptions: [20, 50, 100],
            total: 0,
          },
          list: [],
          selectedRowKeys: [],
          files: [],
          showinfo: false,
          menudata: [],
          nodevalue: "",
          key: "",
          index: 2,
          isdelete: "",
          appendfolder: false,
          newfolder: "新建文件夹",
          folder_id: "",
          visible_range: [
            { label: "所有用户", value: "all" },
            { label: "有产品的用户", value: "host" },
            { label: "有指定产品的用户", value: "product" },
          ],
          product: [],
          rulesmove: {
            addon_idcsmart_file_folder_id: [
              { required: true, message: "文件夹必填" },
            ],
          },
          rules: {
            name: [{ required: true, message: "名称必填" }],
            addon_idcsmart_file_folder_id: [
              { required: true, message: "文件夹必填" },
            ],
            visible_range: [{ required: true, message: "可见范围必填" }],
          },
          formData: [],
        };
      },
      created() {
        this.getpromocodelist();
      },
      mounted() {
        this.getpromocodelist();
      },

      methods: {
        //获取优惠码列表
        getpromocodelist(page) {
          let param = {
            page: page ? page : this.pagination.current,
            limit: this.pagination.pageSize,
            ...this.params,
          };
          promocodelist(param).then((res) => {
            // this.menudata
            this.pagination.total = res.data.data.count;
            this.list = res.data.data.list;
            for (let i = 0; i < this.list.length; i++) {
              if (this.list[i].apply_client === "All") {
                this.list[i].apply_client = "所有";
              } else if (this.list[i].apply_client === "New") {
                this.list[i].apply_client = "新客户";
              } else if (this.list[i].apply_client === "Old") {
                this.list[i].apply_client = "老客户";
              }
            }

            console.log(this.list, "9999");
          });
        },
        //暂停/启用优惠码
        stop(id, status) {
          promocodehidden({ id, status }).then((res) => {
            console.log(res, "res");
            if (res.data.status === 200) {
              this.$message.success(res.data.msg);
              this.getpromocodelist();
            }
          });
        },

        // changeinput(e) {
        //   this.params.keywords = e;
        //   console.log(this.params.keywords, e);
        // },
        //多选框改变
        rehandleSelectChange(e) {
          console.log(e, "eee");
          this.selectedRowKeys = e;
        },
        todetialfiles(e) {
          this.folder_id = e.node.data.id;
          this.getfilelist();
          console.log(e, "ddd");
          //   filedetial({ id: e.node.data.id }).then((res) => {
          //     console.log(res, "888");
          //   });
        },
        changepages(res) {
          console.log(res, "pagesize");
          this.pagination.current = res.pagination.current;
          this.pagination.pageSize = res.pagination.pageSize;
          this.getpromocodelist();
        },
        async onswitch(e, el) {
          console.log(el, "111");
          await filehidden({ id: e.id, hidden: el ? 0 : 1 });
          this.getfilelist();
          console.log(e, "wwww");
        },
        //设置
        edit(id) {
          location.href = "promocode_create.html?id=" + id;
        },
        //提交修改文件
        onSubmit() {
          console.log("edit", this.formData);

          editfile(this.formData).then((res) => {
            if (res.data.status === 200) {
              this.$message.success(res.data.msg);
              this.showinfo = false;
            }
          });
        },
        //搜索
        onEnter(e) {
          this.params.keywords = e;
          this.pagination.current = 1;
          this.getpromocodelist();
        },

        //清空文件夹名字
        deletenode(node) {
          this.menudata.map((item) => {
            if (item.id === node.data.id) {
              item.label = "";
            }
          });
        },
        nodeblur(e, id) {
          console.log(e, id);
          this.nodevalue = "";
          if (!e) {
            return;
          }
          editfolder({ id, name: e }).then((res) => {
            console.log("editfolder", res);
            this.getfolderlist();
          });
        },
        nonewdeblur(e) {
          if (!e) {
            this.$message.error("文件名不能为空!");
            this.appendfolder = false;
            return;
          }
          addfolder({ name: e })
            .then((res) => {
              if (res.data.status === 200) {
                this.appendfolder = false;
                this.$message.success("文件夹添加成功!");
                this.getfolderlist();
              }
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        },
        //修改文件夹分类
        editfolder(node) {
          this.nodevalue = node.value;
          console.log(node, "111");
        },
        //删除文件夹分类
        deletefolder(node, data) {
          this.isdelete = node.data.id;
          if (data === "confirm") {
            this.isdelete = "";
            folderdelete({ id: node.data.id })
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success("文件夹删除成功");
                  this.getfolderlist();
                }
              })
              .catch((err) => {
                this.$message.error(err.data.msg);
              });
          }
        },
        //新增分类
        append(node) {
          this.appendfolder = true;
          //   this.getfolderlist();
        },
        changetabs(data) {
          this.activetabs = data;
          if (this.activetabs === 1) {
            location.href = "promocode_create.html";
          }
          if (this.activetabs === 2) {
            if (this.selectedRowKeys.length > 0) {
              this.visible3 = true;
            } else {
              this.$message.warning("请选择要删除的文件！");
            }
          }
        },
        //获取分类列表
        gettypelist() {
          gethelptype().then((res) => {
            for (let i = 0; i < res.data.data.list.length; i++) {
              res.data.data.list[i].isedit = false;
              res.data.data.list[i].index = i + 1;
            }
            this.typelist = res.data.data.list;
          });
        },
        //上传文件
        formatResponse(res) {
          console.log(res, "res");
          if (res.status != 200) {
            return { error: "上传失败，请重试" };
          }
          return { save_name: res.data.save_name, url: res.url };
        },
        changeupload(res) {
          this.uploadfilelis = [];
          console.log(this.uploadfilelis, res, "this.uploadfilelis");
          res.map((item) => {
            console.log(item.response.save_name, "item.response.save_name");
            let abj = {
              name: item.name,
              filename: item.response.save_name,
              addon_idcsmart_file_folder_id: "",
              visible_range: "",
              product_id: [],
              hidden: false,
            };
            this.uploadfilelist.push(abj);
          });

          console.log(this.uploadfilelist, this.files, res, "files");
        },

        edithelptypeform(e, id) {
          edithelptype({ id, name: e })
            .then((res) => {
              this.$message.success(res.data.msg);
              this.gettypelist();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        },
        edithandleClickOp(id) {
          for (let i = 0; i < this.typelist.length; i++) {
            if (id === this.typelist[i].id) {
              this.$set(this.typelist[i], "isedit", true);
              this.key = Math.random();
            }
          }
          console.log(this.typelist, "this.typelist");
        },
        deleteClickOp(id) {
          deletehelptype({ id })
            .then((res) => {
              this.$message.success(res.data.msg);
              this.gettypelist();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        },
        //新增
        addtype() {
          this.typelist.push({
            name: "",
            isedit: true,
          });
        },
        savehandleClickadd(name) {
          addhelptype({ name })
            .then((res) => {
              this.$message.success(res.data.msg);
              this.gettypelist();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        },
        deleteClickadd(name) {
          // this.typelist;
          this.typelist.forEach((value, index, array) => {
            if (name === value.name && !value.id) {
              console.log(name, value, "value.name");
              array.splice(index, 1);
            }
          });
          console.log(this.typelist, " this.typelist");
        },
        onConfirm() {
          this.visible = false;
        },
        onSubmitmove() {
          if (!this.moveData.addon_idcsmart_file_folder_id) {
            this.$message.warning("请选择文件夹！");
            return;
          }
          movefile({ id: this.selectedRowKeys, ...this.moveData }).then(
            (res) => {
              console.log(res, "onSubmitmove");
              if (res.data.status === 200) {
                this.$message.success("移动成功！");
                this.visible4 = false;
              }
            }
          );
        },
        uploadConfirm() {
          console.log(1111, this.uploadfilelist);
          this.uploadfilelist.map((item) => {
            if (
              !item.name ||
              !item.addon_idcsmart_file_folder_id ||
              !item.visible_range
            ) {
              this.$message.warning("请填写完文件信息！");
              return;
            }
            if (
              item.visible_range === "product" &&
              (!item.product_id || item.product_id.length === 0)
            ) {
              this.$message.warning("请选择指定产品！");
              return;
            }
            return (item.hidden = item.hidden ? 0 : 1);
          });
          unloadfile(this.uploadfilelist).then((res) => {
            console.log(res, "unloadfile");
            if (res.data.status === 200) {
              this.$message.success("上传成功！");
              this.visible = false;
            }
          });
        },
        close3() {
          this.visible3 = false;
        },
        onConfirm3() {
          if (this.selectedRowKeys.length > 1) {
            this.selectedRowKeys.map((item) => {
              this.deleteitem(item);
            });
          } else {
            this.deleteitem(this.selectedRowKeys);
          }
          console.log(this.selectedRowKeys, "2222xs");
        },
        deleteitem(id) {
          promocodedelete({ id }).then((res) => {
            if (res.data.status === 200) {
              this.$message.success("删除成功！");
              this.selectedRowKeys = [];
              this.getpromocodelist();
              this.visible3 = false;
            }
          });
        },
        close(context) {
          this.uploadfilelist = [];
          console.log(
            "关闭弹窗，点击关闭按钮、按下ESC、点击蒙层等触发",
            context
          );
        },
        onCancel(context) {
          this.uploadfilelist = [];
          console.log("点击了取消按钮", context);
        },
        onKeydownEsc(context) {
          this.uploadfilelist = [];
          console.log("按下了ESC", context);
        },
        onClickCloseBtn(context) {
          this.uploadfilelist = [];
          console.log("点击了关闭按钮", context);
        },
        onClickOverlay(context) {
          this.uploadfilelist = [];
          console.log("点击了蒙层", context);
        },
        getLocalTime(nS) {
          return new Date(parseInt(nS) * 1000)
            .toLocaleString()
            .replace(/:\d{1,2}$/, " ");
        },
        getLocalTime2(nS) {
          var date = new Date(nS * 1000); //
          var Y = date.getFullYear() + "/";
          var M =
            date.getMonth() + 1 < 10
              ? "0" + (date.getMonth() + 1) + "/"
              : date.getMonth() + 1 + "-";
          var D =
            date.getDate() < 10
              ? "0" + date.getDate() + " "
              : date.getDate() + " ";
          var h =
            date.getHours() < 10
              ? "0" + date.getHours() + ":"
              : date.getHours() + ":";
          var m =
            date.getMinutes() < 10
              ? "0" + date.getMinutes() + ":"
              : date.getMinutes() + ":";
          var s =
            date.getSeconds() < 10
              ? "0" + date.getSeconds()
              : date.getSeconds();
          return Y + M + D;
        },
      },
    }).$mount(help);
    typeof old_onload == "function" && old_onload();
  };
})(window);
