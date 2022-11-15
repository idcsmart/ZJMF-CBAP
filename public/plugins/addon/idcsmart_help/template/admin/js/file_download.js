(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const help = document.getElementsByClassName("download")[0];
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
              // 禁用行选中方式一：使用 disabled 禁用行（示例代码有效，勿删）。disabled 参数：{row: RowData; rowIndex: number })
              // 这种方式禁用行选中，当前行会添加行类名 t-table__row--disabled，禁用行文字变灰
              // disabled: ({ rowIndex }) => rowIndex === 1 || rowIndex === 3,

              // 禁用行选中方式二：使用 checkProps 禁用行（示例代码有效，勿删）
              // 这种方式禁用行选中，行文本不会变灰
              // checkProps: ({ rowIndex }) => ({ disabled: rowIndex % 2 !== 0 }),
              width: 64,
            },
            {
              width: "400",
              colKey: "name",
              title: "文件名",
              ellipsis: true,
              cell: "name",
              // align: "center",
            },
            {
              colKey: "admin",
              title: "上传人",
            },
            {
              width: "160",
              colKey: "create_time",
              title: "上传日期",
              cell: "createtime",
            },
            {
              colKey: "filetype",
              title: "文件类型",
            },
            {
              colKey: "filesize",
              title: "大小",
              cell: "filesize",
            },
            {
              colKey: "hidden",
              title: "显/隐",
              cell: "pushorback",
            },
            {
              colKey: "op",
              width: 100,
              align: "center",
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
              cell: "name",
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
          uploadTip: "",
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
        console.log(111);
        this.getfolderlist();
        this.getfilelist();
        this.getproductlist();
      },
      methods: {
        //获取文件夹列表
        getfolderlist() {
          folderlist().then((res) => {
            // this.menudata
            res.data.data.list.map((item) => {
              item.label = item.name;
            });
            this.menudata = res.data.data.list;
            console.log(this.menudata, "9999");
          });
        },
        //获取文件列表
        getfilelist(page) {
          let param = {
            addon_idcsmart_file_folder_id: this.folder_id,
            page: page ? page : this.pagination.current,
            limit: this.pagination.pageSize,
            ...this.params,
          };
          filelist(param).then((res) => {
            this.pagination.total = res.data.data.count;
            this.list = res.data.data.list;
          });
        },
        //下载文件
        downloadFile(item) {
          console.log("id", item.id);
          downloadfile({ id: item.id }).then((res) => {
            console.log(res, "downloadFile");
            this.getdownloadfile(item, res);
          });
        },
        getdownloadfile(item, res) {
          const fileName = item.name + "." + item.filetype;
          const _res = res.data;
          const blob = new Blob([_res]);
          const downloadElement = document.createElement("a");
          const href = window.URL.createObjectURL(blob); // 创建下载的链接
          downloadElement.href = href;
          downloadElement.download = decodeURI(fileName); // 下载后文件名
          document.body.appendChild(downloadElement);
          downloadElement.click(); // 点击下载
          document.body.removeChild(downloadElement); // 下载完成移除元素
          window.URL.revokeObjectURL(href); // 释放掉blob对象
        },
        //获取商品列表
        getproductlist() {
          productlist().then((res) => {
            console.log(res, "qqq");
            this.product = res.data.data.list;
          });
        },
        changeinput(e) {
          this.params.keywords = e;
          console.log(this.params.keywords, e);
        },
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
          this.getfilelist();
        },
        async onswitch(e, el) {
          console.log(el, "111");
          await filehidden({ id: e.id, hidden: el ? 0 : 1 });
          this.getfilelist();
          console.log(e, "wwww");
        },
        //编辑
        edit(id) {
          this.showinfo = true;
          filedetial({ id }).then((res) => {
            this.formData = res.data.data.file;
            console.log("edit", res);
          });
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
        //提交修改文件
        onSubmit() {
          console.log("edit", this.formData);

          editfile(this.formData).then((res) => {
            if (res.data.status === 200) {
              this.$message.success(res.data.msg);
              this.showinfo = false;
              this.getfilelist();
              this.getfolderlist();
              this.selectedRowKeys = [];
            }
          });
        },
        //搜索
        onEnter(e) {
          this.params.keywords = e;
          this.pagination.current = 1;
          this.getfilelist();
        },

        //清空文件夹名字
        deletenode(node) {
          console.log("1111");
          this.menudata.map((item) => {
            if (item.id === node.data.id) {
              item.label = "";
            }
          });
        },
        nodeblur() {
          this.nodevalue = "";
        },
        savefolder(name, id) {
          console.log(name, id);
          if (!name) {
            return;
          }
          editfolder({ id, name }).then((res) => {
            console.log("editfolder", res);
            this.nodevalue = "";
            this.getfolderlist();
          });
        },
        //新增文件夹
        addnewfolder() {
          console.log(this.newfolder, "333");
          if (!this.newfolder) {
            this.$message.error("文件名不能为空!");
            this.appendfolder = false;
            return;
          }
          addfolder({ name: this.newfolder })
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
        //取消编辑
        canceledit() {
          this.getfolderlist();
        },
        changetabs(data) {
          this.activetabs = data;
          if (this.activetabs === 1) {
            this.visible = true;
          }
          console.log(this.uploadfilelist, "uploadfilelist");
          if (this.activetabs === 2) {
            if (this.selectedRowKeys.length > 0) {
              this.visible4 = true;
            } else {
              this.$message.warning("请选择要移动的文件！");
            }
          }
          if (this.activetabs === 3) {
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
            return { error: res.msg };
          }
          return { save_name: res.data.save_name, url: res.url };
        },
        onRemovefile(res) {
          console.log(res, "res");
        },
        changeupload(res) {
          // this.uploadfilelist = [];
          console.log(this.uploadfilelist, res, "this.uploadfilelis");
          res.map((item, index) => {
            console.log(item.response.save_name, "item.response.save_name");
            let abj = {
              id: new Date().getTime(),
              name: item.name,
              filename: item.response.save_name,
              addon_idcsmart_file_folder_id: "",
              visible_range: "",
              product_id: [],
              hidden: false,
            };
            this.uploadfilelist.push(abj);
          });
          var newArr = [];
          for (var i = 0; i < this.uploadfilelist.length; i++) {
            if (newArr.indexOf(this.uploadfilelist[i].name) === -1) {
              newArr.push(this.uploadfilelist[i]);
            }
          }
          this.uploadfilelist = newArr;
          console.log(newArr, this.uploadfilelist, this.files, res, "files");
        },

        //删除上传文件
        deleteupfile(id) {
          let arr = [];
          this.uploadfilelist.map((item) => {
            console.log(id, item.id);
            if (item.id !== id) {
              arr.push(item);
            }
          });
          this.uploadfilelist = arr;
          console.log(this.uploadfilelist, arr, "this.uploadfilelist");
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
          this.selectedRowKeys.map((item) => {
            movefile({ id: item, ...this.moveData })
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success("移动成功！");
                  this.getfilelist();
                  this.getfolderlist();
                  this.selectedRowKeys = [];
                  this.visible4 = false;
                }
              })
              .catch((err) => {
                this.$message.error(err.data.msg);
              });
          });
        },
        //移动文件
        moveChange(e) {
          // console.log(e, "moveChange");
          // let param = {
          //   addon_idcsmart_file_folder_id: e,
          //   page: "",
          //   limit: "",
          //   ...this.params,
          // };
          // filelist(param).then((res) => {
          // });
        },
        //文件弹窗确认
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
          unloadfile({ file: this.uploadfilelist }).then((res) => {
            console.log(res, "unloadfile");
            if (res.data.status === 200) {
              this.$message.success("上传成功！");
              this.uploadfilelist = [];
              this.getfilelist();
              this.getfolderlist();
              this.visible = false;
            }
          });
        },
        close3() {
          this.visible3 = false;
        },
        onConfirm3() {
          console.log(this.selectedRowKeys, "this.selectedRowKeys");
          this.selectedRowKeys.map((item) => {
            deletefile({ id: item }).then((res) => {
              if (res.data.status === 200) {
                setTimeout(() => {
                  this.$message.success("删除成功！");
                  this.selectedRowKeys = [];
                  this.getfilelist();
                  this.getfolderlist();
                  this.visible3 = false;
                }, 1000);
              }
            });
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
      },
    }).$mount(help);
    typeof old_onload == "function" && old_onload();
  };
})(window);
