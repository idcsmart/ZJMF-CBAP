(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const helpIndex = document.getElementsByClassName("helpIndex")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      data() {
        return {
          message: "template...",
          params: {
            keywords: "",
            page: 1,
            limit: 10,
            orderby: "id",
            sort: "desc",
          },
          showdialog: false,
          options: [],
          first: "",
          value: "1",
          list: [],
          listold: [],
          isactive: false,
          checkid: "",
          dialog: {},
          dialog_name: "",
          searchlist: [],
          searchlistall: [],
          choselist: [],
          // keywords: "",
          total: 0,
          // pageSize: 10,
          // page: 1,
          checkgroup: [],
          typelist: [],
        };
      },
      methods: {
        //选标题
        changetitle(e) {
          this.typelist.map((item) => {
            if (item.id === e) {
              this.dialog_name = item.name;
            }
          });
          console.log(e, "title");
        },
        getindexlist() {
          helpindex().then((res) => {
            res.data.data.index.map((item) => {
              if (!item.id) {
                item.id = 0;
                item.name = "暂不选";
                item.index_hot_show = 0;
                item.helps = [];
                return item;
              }
            });
            console.log(res.data.data.index, "list");
            this.list = res.data.data.index;
            this.listold = JSON.parse(JSON.stringify(this.list));
          });
          console.log(this.list, "list222");
        },
        //获取分类列表
        gettypelist() {
          gethelptype().then((res) => {
            for (let i = 0; i < res.data.data.list.length; i++) {
              res.data.data.list[i].isedit = false;
              res.data.data.list[i].index = i + 1;
            }
            this.typelist = res.data.data.list;
            this.typelist.push({
              id: 0,
              name: "暂不选",
              index_hot_show: 0,
              helps: [],
            });
          });
        },
        titlecheck(e) {
          // this.isactive = e;
          // this.searchlist.map((item, index) => {
          //   if (item.id === id) {
          //     this.choselist.push(item);
          //   }
          // });
          // this.choselist = [];
          console.log(this.choselist, this.checkgroup, "this.checkgroup");
          if (this.checkgroup.length > 3) {
            this.$message.warning("最多选择三个文档！");
            this.checkgroup.pop();
            console.log(this.checkgroup, "this.checkgroup1111");
            return;
          }
          this.checkgroup = e;
          this.choselist = [];
          for (let i = 0; i < this.searchlistall.length; i++) {
            for (let j = 0; j < e.length; j++) {
              if (this.searchlistall[i].id === e[j]) {
                console.log(this.searchlistall[i].id, e[j], "id");
                this.choselist.push(this.searchlistall[i]);
              }
            }
          }
          // let hash = {};
          // newArr = this.choselist.reduce((preVal, curVal) => {
          //   hash[curVal.id]
          //     ? ""
          //     : (hash[curVal.id] = true && preVal.push(curVal));
          //   return preVal;
          // }, []);

          // this.choselist = newArr;
          console.log(e, this.choselist, "eeee");
        },

        redeleteClickOp(e) {
          this.checkgroup.map((item, index) => {
            if (item === e) {
              return this.checkgroup.splice(index, 1);
            }
          });
          this.choselist.map((it, ind) => {
            if (it.id === e) {
              return this.choselist.splice(ind, 1);
            }
          });
          console.log(this.checkgroup, "this.checkgroup");
        },
        keywordssearch(page) {
          this.helplistIndexs(page);
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.params.keywords = "";
          this.helplistIndexs();
        },
        mobile_file(id) {
          console.log(id, "1111");
          if (id === 0) {
            return;
          }
          this.showdialog = true;
          this.listold.map((item) => {
            if (item.id === id) {
              this.dialog = item;
              this.choselist = this.dialog.helps;
              this.checkgroup = [];
              this.dialog.helps.map((item) => {
                this.checkgroup.push(item.id);
              });
            }
          });
          console.log(this.dialog, "this.dialog");
          this.helplistIndexs(this.dialog);
          this.helplistIndexsall(this.dialog);
        },
        helplistIndexs(page) {
          let params = {
            addon_idcsmart_help_type_id: this.dialog.id,
            // keywords: this.params.keywords,
            ...this.params,
            page: page === 1 ? 1 : this.params.page,
            // limit: this.params.limit,
            // orderby: "",
            // sort: "",
          };
          helplistIndex(params).then((res) => {
            this.searchlist = res.data.data.list;
            this.total = res.data.data.count;
            console.log(this.searchlist, "this.searchlist");
          });
        },
        helplistIndexsall() {
          let params = {
            addon_idcsmart_help_type_id: this.dialog.id,
            // keywords: this.params.keywords,
            ...this.params,
            // page: this.params.page,
            limit: 10000,
            // orderby: "",
            // sort: "",
          };
          helplistIndex(params).then((res) => {
            this.searchlistall = res.data.data.list;
          });
        },
        //热度切换
        hotchange(e, id) {
          console.log(e, id);
          this.list.map((item, index) => {
            if (item.id === id) {
              return (item.index_hot_show = e);
            }
          });
          this.savehelp();
        },
        Confirmindex() {
          this.list.map((item, index) => {
            if (item.id === this.dialog.id) {
              return (item.helps = this.choselist);
            }
          });
          console.log(this.list, "this.list");
          this.savehelp();
        },
        savehelp() {
          let newArr = [];
          this.list.map((item) => {
            if (!item.id) {
              return (item.id = 0);
            }
          });
          this.list.forEach((item) => {
            if (item.id !== 0) {
              newArr.push(item.id);
            } else {
              item.helps = [];
            }
          });
          if (new Set(newArr).size != newArr.length) {
            this.$message.warning("有重复标题，请重选！");
            return;
          }
          savehelpindex({ index: [...this.list] }).then((res) => {
            if (res.data.status === 200) {
              this.$message.success("保存成功！");
              this.choselist = [];
              this.dialog = {};
              this.params = {
                keywords: "",
                page: 1,
                limit: 10,
                orderby: "id",
                sort: "desc",
              };
              this.getindexlist();
              setTimeout(() => {
                this.showdialog = false;
              }, 1000);
            }
          });
          console.log(this.choselist, this.list, "11111");
        },
        close(context) {
          console.log(
            "关闭弹窗，点击关闭按钮、按下ESC、点击蒙层等触发",
            context
          );
        },
        onCancel(context) {
          this.params = {
            keywords: "",
            page: 1,
            limit: 10,
            orderby: "id",
            sort: "desc",
          };
          this.choselist = [];
          this.dialog = {};
          console.log("点击了取消按钮", this.params);
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
      },
      created() {
        this.getindexlist();
        this.gettypelist();
      },
    }).$mount(helpIndex);
    typeof old_onload == "function" && old_onload();
  };
})(window);
