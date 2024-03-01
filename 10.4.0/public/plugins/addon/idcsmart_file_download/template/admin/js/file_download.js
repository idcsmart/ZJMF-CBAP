(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const help = document.getElementsByClassName("download")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
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

          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          uploadUrl: str + "v1/upload",
          columns: [
            {
              colKey: "drag", // 列拖拽排序必要参数
              title: lang.sort,
              cell: "drag",
              width: 40,
            },
            {
              colKey: "id",
              type: "multiple",
              className: "demo-multiple-select-cell",
            },
            {
              width: "450",
              colKey: "name",
              title: lang.file_download_text20,
              ellipsis: true,
              cell: "name",
              // align: "center",
            },
            {
              width: "200",
              colKey: "description",
              title: lang.file_download_text50,
              ellipsis: true,
              cell: "description",
            },
            {
              width: "120",
              colKey: "admin",
              title: lang.file_download_text21,
            },
            {
              width: "150",
              colKey: "create_time",
              title: lang.file_download_text22,
              cell: "createtime",
            },
            // {
            //   width: "150",
            //   colKey: "filetype",
            //   title: "文件类型",
            // },
            // {
            //   width: "150",
            //   colKey: "filesize",
            //   title: "大小",
            //   cell: "filesize",
            // },
            {
              width: "90",
              colKey: "hidden",
              title: lang.file_download_text23,
              cell: "pushorback",
              ellipsis: true,
            },
            {
              colKey: "op",
              width: 100,
              align: "center",
              title: lang.file_download_text24,
              cell: "op",
            },
          ],

          columns2: [
            {
              width: "200",
              colKey: "name",
              className: "columns2name",
              title: lang.file_download_text25,
              cell: "name",
            },
            {
              colKey: "addon_idcsmart_file_folder_id",
              title: lang.file_download_text19,
              cell: "folder",
            },
            {
              colKey: "visible_range",
              title: lang.file_download_text14,
              cell: "range",
            },
            {
              colKey: "product_id",
              title: lang.file_download_text15,
              cell: "product",
            },

            {
              colKey: "hidden",
              width: 200,
              title: lang.file_download_text26,
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
            showJumper: true,
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
          newfolder: lang.file_download_text27,
          folder_id: "",
          visible_range: [
            { label: lang.file_download_text28, value: "all" },
            { label: lang.file_download_text29, value: "host" },
            { label: lang.file_download_text30, value: "product" },
          ],
          product: [],
          rulesmove: {
            addon_idcsmart_file_folder_id: [
              { required: true, message: lang.file_download_text31 },
            ],
          },
          rules: {
            name: [{ required: true, message: lang.file_download_text32 }],
            addon_idcsmart_file_folder_id: [
              { required: true, message: lang.file_download_text31 },
            ],
            visible_range: [
              { required: true, message: lang.file_download_text33 },
            ],
            product_id: [
              { required: true, message: lang.file_download_text34 },
            ],
          },
          formData: {
            addon_idcsmart_file_folder_id: "",
            product_id: [],
            id: "",
            name: "",
            visible_range: "",
            description: "",
          },
          folderNum: 0,
          baseUrl: url,
          defaultFileId: "",
          curIndex: "",
          maxHeight: "",
          loading: false,
          fileLoading: false,
          search: "",
          filterOptions: [],
          submitLoading: false,
          uploadLoading: false
        };
      },
      watch: {
        search: {
          deep: true,
          immediate: true,
          handler(val) {
            if (!val) {
              this.filterOptions = JSON.parse(JSON.stringify(this.product));
            }
          },
        },
      },
      created() {
        this.getfolderlist();
        this.getfilelist();
        this.getproductlist();
      },
      computed: {
        filterData() {
          const temp = this.menudata.filter(
            (item) => item.id !== this.folder_id
          );
          return this.menudata.filter((item) => item.id !== this.folder_id);
        },
        calcSize() {
          return (row) => {
            return `(${
              row.filesize / 1024 / 1024 >= 1
                ? (row.filesize / 1024 / 1024).toFixed(2) + "M"
                : (row.filesize / 1024).toFixed(2) + "KB"
            })`;
          };
        },
      },
      methods: {
        onDragSort({ newData }) {
          this.list = newData;
          this.changefileOrder();
        },
        // 修改排序
        changefileOrder() {
          const idList = this.list.map((item) => item.id);
          fileOrder({
            addon_idcsmart_file_folder_id: this.folder_id,
            id: idList,
          }).then((res) => {
          });
        },
        blurHandler(e) {
          if (!e) {
            this.search = "";
          }
        },
        onSearch() {
          const temp = JSON.parse(JSON.stringify(this.product));
          this.filterOptions = temp.filter(
            (item) => item.name.indexOf(this.search) !== -1
          );
        },
        //获取文件夹列表
        getfolderlist() {
          folderlist().then((res) => {
            // this.menudata
            res.data.data.list.map((item) => {
              item.label = item.name;
              item.edit = false;
            });
            this.menudata = res.data.data.list;
            this.folderNum = res.data.data.count;
            // this.defaultFileId =
            //   this.menudata.filter((item) => item.default === 1)[0]?.id || "";
          });
        },
        changeAll() {
          this.folder_id = "";
          this.curIndex = -1;
          this.getfilelist();
        },
        changeFile(item, index) {
          this.folder_id = item.id;
          this.curIndex = index;
          this.pagination.current = 1;
          this.getfilelist();
        },
        //获取文件列表
        getfilelist(page) {
          let param = {
            addon_idcsmart_file_folder_id: this.folder_id,
            page: page ? page : this.pagination.current,
            limit: this.pagination.pageSize,
            ...this.params,
          };
          this.loading = true;
          filelist(param).then((res) => {
            this.pagination.total = res.data.data.count;
            this.list = res.data.data.list;
            this.loading = false;
          });
        },
        //下载文件
        downloadFile(item) {
          downloadfile({ id: item.id }).then((res) => {
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
            this.filterOptions = this.product = res.data.data.list;
          });
        },
        changeinput(e) {
          this.params.keywords = e;
        },
        //多选框改变
        rehandleSelectChange(e) {
          this.selectedRowKeys = e;
        },
        todetialfiles(e) {
          this.folder_id = e.node.id;
          this.getfilelist();
          //   filedetial({ id: e.node.data.id }).then((res) => {
          //     console.log(res, "888");
          //   });
        },
        changepages(res) {
          this.pagination.current = res.pagination.current;
          this.pagination.pageSize = res.pagination.pageSize;
          this.getfilelist();
        },
        async onswitch(e, el) {
          await filehidden({ id: e.id, hidden: el ? 0 : 1 });
          this.$message.success(
            el ? lang.file_download_text35 : lang.file_download_text36
          );
          this.getfilelist();
        },
        //编辑
        async edit(id) {
          try {
            const res = await filedetial({ id });
            this.formData = { ...res.data.data.file };
            this.showinfo = true;
          } catch (error) {}
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
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true
              const res = await editfile(this.formData);
              this.$message.success(res.data.msg);
              this.showinfo = false;
              this.getfilelist();
              this.getfolderlist();
              this.selectedRowKeys = [];
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
        //搜索
        onEnter(e) {
          this.params.keywords = e;
          this.pagination.current = 1;
          this.getfilelist();
        },

        //清空文件夹名字
        deletenode(node) {
          this.menudata.map((item) => {
            if (item.id === node.id) {
              item.label = "";
            }
          });
        },
        nodeblur() {
          this.nodevalue = "";
        },
        savefolder(name, id) {
          if (!name) {
            return;
          }
          this.fileLoading = true;
          editfolder({ id, name }).then((res) => {
            this.nodevalue = "";
            this.getfolderlist();
            this.fileLoading = false;
          });
        },
        //新增文件夹
        addnewfolder() {
          if (!this.newfolder) {
            this.$message.error(lang.file_download_text37);
            this.appendfolder = false;
            return;
          }
          this.fileLoading = true;
          addfolder({ name: this.newfolder })
            .then((res) => {
              if (res.data.status === 200) {
                this.appendfolder = false;
                this.$message.success(lang.file_download_text38);
                this.getfolderlist();
                this.fileLoading = false;
              }
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        },
        //修改文件夹分类
        editfolder(node) {
          this.nodevalue = node.value;
          node.edit = true;
        },
        // 切换默认文件夹
        async changeDef(item) {
          try {
            const res = await checkDef({ id: item.id });
            this.$message.success(lang.file_download_text39);
            this.getfolderlist();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        //删除文件夹分类
        deletefolder(node, data) {
          this.isdelete = node.id;
          if (data === "confirm") {
            this.isdelete = "";
            this.fileLoading = true;
            folderdelete({ id: node.id })
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(lang.file_download_text40);
                  this.getfolderlist();
                  this.fileLoading = false;
                }
              })
              .catch((err) => {
                this.fileLoading = false;
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
          if (this.activetabs === 3) {
            if (this.selectedRowKeys.length > 0) {
              this.visible3 = true;
            } else {
              this.$message.warning(lang.file_download_text41);
              return;
            }
          }
          if (this.menudata.length <= 0) {
            this.$message.warning(lang.file_download_text42);
            return;
          }
          if (this.activetabs === 1) {
            this.visible = true;
          }
          /* 移动文件 */
          if (this.activetabs === 2) {
            if (this.selectedRowKeys.length > 0) {
              this.visible4 = true;
            } else {
              this.$message.warning(lang.file_download_text43);
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
        beforeUpload () {
          this.uploadLoading = true
        },
        //上传文件
        formatResponse(res) {
          if (res.status != 200) {
            this.$message.error(res.msg)
            this.files = []
            this.uploadLoading = false
            return { error: res.msg, url: res.url };
          }
          this.uploadLoading = false
          return { save_name: res.data.save_name, url: res.url };
        },
        onRemovefile(res) {},
        // changeupload (res) {
        //   this.files.forEach((item, index) => {
        //     console.log(item.response.save_name, "item.response.save_name");
        //     let abj = {
        //       id: new Date().getTime(),
        //       name: item.name,
        //       filename: item.response.save_name,
        //       addon_idcsmart_file_folder_id: "",
        //       visible_range: "",
        //       product_id: [],
        //       hidden: false,
        //     };
        //     this.uploadfilelist.push(abj);
        //   });
        //   var newArr = [];
        //   for (var i = 0; i < this.uploadfilelist.length; i++) {
        //     // if (newArr.indexOf(this.uploadfilelist[i].name) === -1) {
        //     //   newArr.push(this.uploadfilelist[i]);
        //     // }
        //     newArr.forEach(item => {
        //       if (item.name && item.name !== this.uploadfilelist[i].name) {
        //         newArr.push(this.uploadfilelist[i]);
        //       }
        //     })
        //   }
        //   console.log('newArr', newArr)
        //   this.uploadfilelist = newArr;
        // },

        changeupload(res) {
          // let temp = JSON.parse(JSON.stringify(this.files))
          // temp = temp.map(item => {
          //   item.id = new Date().getTime()
          //   item.name = item.name
          //   item.filename = item.response.save_name
          //   item.addon_idcsmart_file_folder_id = ''
          //   item.visible_range = ''
          //   item.product_id = []
          //   item.hidden = false
          //   return item
          // })
          // console.log('@#@#@#@#', temp)
          this.files.forEach((item, index) => {
            let abj = {
              id: new Date().getTime(),
              name: item.name,
              filename: item.response.save_name,
              addon_idcsmart_file_folder_id: this.folder_id,
              visible_range: "all",
              product_id: [],
              hidden: true,
            };

            if (this.uploadfilelist.length === 0) {
              this.uploadfilelist.push(abj);
            } else {
              if (this.uploadfilelist[index]?.name !== item.name) {
                this.uploadfilelist.push(abj);
              }
            }
            // this.uploadfilelist.push(abj);
          });
          // var newArr = [];
          // for (var i = 0; i < this.uploadfilelist.length; i++) {
          //   // if (newArr.indexOf(this.uploadfilelist[i].name) === -1) {
          //   //   newArr.push(this.uploadfilelist[i]);
          //   // }
          //   newArr.forEach(item => {
          //     if (item.name && item.name !== this.uploadfilelist[i].name) {
          //       newArr.push(this.uploadfilelist[i]);
          //     }
          //   })
          // }
          // console.log('newArr', newArr)
          // this.uploadfilelist = newArr;
        },

        //删除上传文件
        deleteupfile(filename) {
          let arr = [];
          this.uploadfilelist.forEach((item) => {
            // console.log(id, item.id);
            if (item.filename !== filename) {
              arr.push(item);
            }
          });
          let arr1 = [];
          this.files.forEach((item) => {
            // console.log(id, item.id);
            if (item.response.save_name !== filename) {
              arr1.push(item);
            }
          });
          this.uploadfilelist = arr;
          this.files = arr1;
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
          // console.log(this.typelist, "this.typelist");
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
              //  console.log(name, value, "value.name");
              array.splice(index, 1);
            }
          });
          // console.log(this.typelist, " this.typelist");
        },
        onConfirm() {
          this.visible = false;
        },
        onSubmitmove() {
          if (!this.moveData.addon_idcsmart_file_folder_id) {
            this.$message.warning(lang.file_download_text44);
            return;
          }
          this.submitLoading = true
          this.selectedRowKeys.map((item) => {
            movefile({ id: item, ...this.moveData })
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(lang.file_download_text45);
                  this.getfilelist();
                  this.getfolderlist();
                  this.selectedRowKeys = [];
                  this.visible4 = false;
                  this.submitLoading = false
                }
              })
              .catch((err) => {
                this.submitLoading = false
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
          // console.log(1111, this.uploadfilelist);
          this.uploadfilelist.map((item) => {
            if (
              !item.name ||
              !item.addon_idcsmart_file_folder_id ||
              !item.visible_range
            ) {
              this.$message.warning(lang.file_download_text46);
              return;
            }
            if (
              item.visible_range === "product" &&
              (!item.product_id || item.product_id.length === 0)
            ) {
              this.$message.warning(lang.file_download_text47);
              return;
            }
            return (item.hidden = item.hidden ? 0 : 1);
          });
          this.submitLoading = true
          unloadfile({ file: this.uploadfilelist }).then((res) => {
            if (res.data.status === 200) {
              this.$message.success(lang.file_download_text48);
              this.uploadfilelist = [];
              this.files = [];
              this.getfilelist();
              this.getfolderlist();
              this.visible = false;
            }
          }).finally(() => {
            this.submitLoading = false;
          });
        },
        close3() {
          this.visible3 = false;
        },
        async onConfirm3() {
          try {
            const arr = [];
            this.submitLoading = true
            for (let i = 0; i < this.selectedRowKeys.length; i++) {
              const p = await deletefile({
                id: this.selectedRowKeys[i],
              });
              arr.push(p);
            }
            Promise.all(arr)
              .then((res) => {
                this.$message.success(lang.file_download_text49);
                this.selectedRowKeys = [];
                this.getfilelist();
                this.getfolderlist();
                this.visible3 = false;
              })
              .catch((err) => {
                this.visible3 = false;
                this.$message.error(err.data.msg);
              }).finally(() => {
                this.submitLoading = false;
              });
          } catch (error) {
            this.visible3 = false;
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        close(context) {
          this.uploadfilelist = [];
          this.files = [];
        },
        onCancel(context) {
          this.uploadfilelist = [];
          this.files = [];
        },
        onKeydownEsc(context) {
          this.uploadfilelist = [];
          this.files = [];
        },
        onClickCloseBtn(context) {
          this.uploadfilelist = [];
          this.files = [];
        },
        onClickOverlay(context) {
          this.files = [];
          this.uploadfilelist = [];
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
