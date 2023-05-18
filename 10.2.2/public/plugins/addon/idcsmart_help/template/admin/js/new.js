(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const help = document.getElementsByClassName("news")[0];
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
          visibledel: false,
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
              width: 400,
              colKey: "title",
              title: "文档名称",
              ellipsis: true,
            },
            {
              width: 150,
              colKey: "type",
              title: "所属分类",
            },
            {
              width: 150,
              colKey: "admin",
              title: "发布人",
            },
            {
              width: 250,
              colKey: "create_time",
              title: "发布时间",
              cell: "create_time",
            },
            {
              width: 110,
              colKey: "hidden",
              title: "发布/撤回",
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
        };
      },
      created() {
        console.log(111);
        this.getlist();
      },
      methods: {
        changepages(res) {
          console.log(res, "pagesize");
          this.pagination.current = res.pagination.current;
          this.pagination.pageSize = res.pagination.pageSize;
          this.getlist();
        },
        async onswitch(e, el) {
          console.log(el, "111");
          await helphidden({ id: e.id, hidden: el ? 0 : 1 });
          this.getlist();
          console.log(e, "wwww");
        },
        //编辑
        edit(id) {
          location.href = "news_create.html?id=" + id;
        },
        //删除
        deletes(id) {
          console.log(id, "help");
          helpdelete({ id }).then((res) => {
            this.$message.success(res.data.msg);
            this.getlist();
          });
        },
        //获取文档列表
        async getlist(page) {
          console.log(this.params, "this.params");
          let param = {
            page: page ? page : this.pagination.current,
            limit: this.pagination.pageSize,
            ...this.params,
          };
          let resdata = await helplist(param);
          this.pagination.total = resdata.data.data.count;
          this.list = resdata.data.data.list;
          console.log(this.list, "list");
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
            location.href = "news_create.html";
          }
          if (this.activetabs === 3) {
            this.visible = true;
            this.gettypelist();
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
        edithelptypeform(e, id) {
          console.log(e, id, "id");
          if (!id) {
            return;
          }
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
        //取消修改
        canceledit() {
          this.gettypelist();
        },
        //新增
        addtype() {
          console.log(this.typelist, "this.typelist");
          let flag = true;
          this.typelist.map((item) => {
            if (!item.name) {
              this.$message.warning("请输入新增类型！");
              flag = false;
              return;
            }
            if (item.isedit) {
              this.$message.warning("请先保存新增类型！");
              flag = false;
              return;
            }
          });
          if (flag) {
            this.typelist.push({
              name: "",
              isedit: true,
            });
          }
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
      },
    }).$mount(help);
    typeof old_onload == "function" && old_onload();
  };
})(window);
