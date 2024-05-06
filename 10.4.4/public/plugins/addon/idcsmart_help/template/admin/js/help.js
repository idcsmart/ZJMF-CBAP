(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const help = document.getElementsByClassName("help")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    new Vue({
      components: {
        comConfig
      },
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
              title: lang.help_text28,
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
              title: lang.help_text29,
              ellipsis: true,
            },
            {
              width: "150",
              colKey: "type",
              title: lang.help_text30,
            },
            {
              width: "150",
              colKey: "admin",
              title: lang.help_text31,
            },
            {
              width: "250",
              colKey: "create_time",
              title: lang.help_text32,
              cell: "createtime",
            },
            {
              width: 110,
              colKey: "hidden",
              title: lang.help_text33,
              cell: "pushorback",
            },
            {
              colKey: "op",
              width: 100,
              title: lang.help_text34,
              cell: "op",
            },
          ],

          columns2: [
            {
              width: "100",
              colKey: "index",
              title: lang.help_text28,
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
              title: lang.help_text35,
              cell: "name",
            },
            {
              colKey: "admin",
              title: lang.help_text36,
            },
            {
              colKey: "update_time",
              title: lang.help_text37,
              cell: "time",
            },
            {
              colKey: "op",
              width: 200,
              title: lang.help_text34,
              cell: "op",
            },
          ],
          typelist: [],
          pagination: {
            current: 1,
            pageSize: 20,
            pageSizeOptions: [20, 50, 100],
            total: 0,
            showJumper: true
          },
          list: [],
          key: "",
          delVisible: false,
          delId: '',
          maxHeight: '',
          loading: false,
          isSubmit: false, // 限制重复提交保存
          isUpdate: false, // 正在编辑状态
          submitLoading: false
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
          this.pagination.current = res.pagination.current;
          this.pagination.pageSize = res.pagination.pageSize;
          this.getlist();
        },
        async onswitch(e, el) {
          try {
            await helphidden({ id: e.id, hidden: el ? 0 : 1 });
            this.$message.success(el ? lang.help_text38 : lang.help_text39);
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
            this.submitLoading = true
            const res = await helpdelete({ id: this.delId })
            this.$message.success(res.data.msg);
            this.getlist();
            this.delVisible = false
            this.submitLoading = false
          } catch (error) {
            this.submitLoading = false
            this.$message.error(error.data.msg);
          }
        },
        //获取文档列表
        async getlist(page) {
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
            return this.$message.error(lang.help_text40);
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
            return this.$message.error(lang.help_text40);
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
            return this.$message.warning(lang.help_text41);
          }
          const hasName = temp.filter(item => !item.name)
          if (hasName.length > 0) {
            return this.$message.warning(lang.help_text42);
          }
          if (this.isSubmit) {
            return;
          }
          this.isSubmit = true
          this.submitLoading = true
          addhelptype({ list: temp })
            .then((res) => {
              this.$message.success(res.data.msg);
              this.gettypelist();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            }).finally(() => {
              setTimeout(() => {
                this.submitLoading = false
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
              : date.getMonth() + 1 + "/";
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
              array.splice(index, 1);
            }
          });
        },
        onConfirm(context) {

          this.visible = false;
        },
        onConfirmAnother(context) {
        },
        close(context) {

        },
        onCancel(context) {
        },
        onKeydownEsc(context) {
        },
        onClickCloseBtn(context) {
        },
        onClickOverlay(context) {
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
