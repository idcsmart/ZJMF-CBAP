(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const help = document.getElementsByClassName("help")[0];
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
          columns: [
            {
              width: "100",
              colKey: "id",
              title: "序号",
              // 对齐方式
              align: "center",
              // 设置列类名
              className: "custom-column-class-name",
              // 设置列属性
              attrs: {
                "data-id": "first-column",
              },
            },
            {
              width: "400",
              colKey: "title",
              title: "文档名称",
              ellipsis: true,
            },
            {
              width: "150",
              colKey: "type",
              title: "分类",
            },
            {
              width: "150",
              colKey: "admin",
              title: "发布人",
            },
            {
              width: "250",
              colKey: "create_time",
              title: "发布时间",
              cell: "createtime",
            },
            {
              width: 110,
              colKey: "hidden",
              title: "显示/隐藏",
              cell: "pushorback",
            },
            {
              colKey: "op",
              width: 100,
              title: "操作",
              cell: "op",
            },
          ],

          columns2: [
            {
              width: "100",
              colKey: "index",
              title: "序号",
              // 对齐方式
              align: "center",
              // 设置列类名
              className: "custom-column-class-name",
              // 设置列属性
              attrs: {
                "data-id": "first-column",
              },
            },
            {
              width: "300",
              colKey: "name",
              title: "分类名称",
              cell: "name",
            },
            {
              colKey: "admin",
              title: "修改人",
            },
            {
              colKey: "update_time",
              title: "修改时间",
              cell: "time",
            },
            {
              colKey: "op",
              width: 200,
              title: "操作",
              cell: "op",
            },
          ],
          typelist: [],
          pagination: {
            current: 1,
            pageSize: 20,
            pageSizeOptions: [20, 50, 100],
            total: 0,
          },
          list: [],
          key: "",
          delVisible: false,
          delId: '',
          maxHeight: '',
          loading: false,
          isSubmit: false, // 限制重复提交保存
          isUpdate: false // 正在编辑状态
        };
      },
      created() {
        this.getlist();
      },
      mounted() {
        this.maxHeight = document.getElementById('content').clientHeight - 170
        let timer = null
        window.onresize = () => {
          if (timer) {
            return
          }
          timer = setTimeout(() => {
            this.maxHeight = document.getElementById('content').clientHeight - 170
            clearTimeout(timer)
            timer = null
          }, 300)
        }
      },
      methods: {
        canceledit() {
          this.gettypelist();
        },
        //分页改变
        changepages(res) {
          console.log(res, "pagesize");
          this.pagination.current = res.pagination.current;
          this.pagination.pageSize = res.pagination.pageSize;
          this.getlist();
        },
        async onswitch(e, el) {
          try {
            await helphidden({ id: e.id, hidden: el ? 0 : 1 });
            this.$message.success(el ? '显示成功' : '隐藏成功');
            this.getlist();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        //编辑
        edit(id) {
          location.href = "help_create.htm?id=" + id;
        },
        //删除
        deletes(id) {
          this.delVisible = true
          this.delId = id
        },
        async sureDelUser() {
          try {
            const res = await helpdelete({ id: this.delId })
            this.$message.success(res.data.msg);
            this.getlist();
            this.delVisible = false
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        //获取文档列表
        async getlist(page) {
          // console.log(111);
          this.loading = true
          let param = {
            page: page ? page : this.pagination.current,
            limit: this.pagination.pageSize,
            ...this.params,
          };
          let resdata = await helplist(param);
          this.pagination.total = resdata.data.data.count;
          this.list = resdata.data.data.list;
          this.loading = false
        },
        //搜索
        onEnter(e) {
          this.params.keywords = e;
          this.pagination.current = 1;
          this.getlist();
        },

        changetabs(data) {
          this.activetabs = data;
          if (this.activetabs === 1) {
            location.href = "help_create.htm";
          }
          if (this.activetabs === 2) {
            location.href = "help_index.htm";
          }
          if (this.activetabs === 3) {
            this.visible = true;
            this.isUpdate = false;
            this.gettypelist();
          }
        },
        //获取分类列表
        gettypelist() {
          this.isUpdate = false;
          gethelptype().then((res) => {
            for (let i = 0; i < res.data.data.list.length; i++) {
              res.data.data.list[i].isedit = false;
              res.data.data.list[i].index = i + 1;
            }
            this.typelist = res.data.data.list;
          });
        },
        // 修改时候的保存
        edithelptypeform(e, id) {
          if (!id) {
            return;
          }
          if (this.isSubmit) {
            return;
          }
          this.isSubmit = true
          edithelptype({ id, name: e })
            .then((res) => {
              this.$message.success(res.data.msg);
              this.gettypelist();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            }).finally(() => {
              this.isUpdate = false
              setTimeout(() => {
                this.isSubmit = false
              }, 1000)
            })
        },
        // 修改单个
        edithandleClickOp(id) {
          if (this.isUpdate) {
            return this.$message.error('请先保存正在编辑的分类');
          }
          this.isUpdate = true
          for (let i = 0; i < this.typelist.length; i++) {
            if (id === this.typelist[i].id) {
              this.$set(this.typelist[i], "isedit", true);
              this.key = Math.random();
            }
          }
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
          if (this.isUpdate) {
            return this.$message.error('请先保存正在编辑的分类');
          }
          let flag = true;
          // this.typelist.map((item) => {
          //   if (!item.name) {
          //     this.$message.warning("请输入新增类型！");
          //     flag = false;
          //     return;
          //   }
          //   if (item.isedit) {
          //     this.$message.warning("请先保存新增类型！");
          //     flag = false;
          //     return;
          //   }
          // });
          if (flag) {
            this.typelist.push({
              name: "",
              isedit: true,
            });
          }
        },
        // 批量新增
        savehandleClickadd() {
          let temp = JSON.parse(JSON.stringify(this.typelist))
          temp = temp.filter(item => !item.id).reduce((all, cur) => {
            all.push({
              name: cur.name
            })
            return all
          }, [])
          if (temp.length === 0) {
            return this.$message.warning("请先新增分类！");
          }
          const hasName = temp.filter(item => !item.name)
          if (hasName.length > 0) {
            return this.$message.warning("请输入分类名称！");
          }
          if (this.isSubmit) {
            return;
          }
          this.isSubmit = true
          addhelptype({ list: temp })
            .then((res) => {
              this.$message.success(res.data.msg);
              this.gettypelist();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            }).finally(() => {
              setTimeout(() => {
                this.isSubmit = false
              }, 1000)
            })
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
          return Y + M + D + h + m + s;
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
        onConfirm(context) {
          console.log(
            "@confirm与onConfirm任选一种方式即可，其他几个事件类似",
            context
          );
          this.visible = false;
        },
        onConfirmAnother(context) {
          console.log("点击了确认按钮", context);
        },
        close(context) {
          console.log(
            "关闭弹窗，点击关闭按钮、按下ESC、点击蒙层等触发",
            context
          );
        },
        onCancel(context) {
          console.log("点击了取消按钮", context);
        },
        onKeydownEsc(context) {
          console.log("按下了ESC", context);
        },
        onClickCloseBtn(context) {
          console.log("点击了关闭按钮", context);
        },
        onClickOverlay(context) {
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
