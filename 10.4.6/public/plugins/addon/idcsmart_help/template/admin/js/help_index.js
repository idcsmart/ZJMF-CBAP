(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const helpIndex = document.getElementsByClassName("helpIndex")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    new Vue({
      components: {
        comConfig
      },
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
          submitLoading: false
        };
      },
      computed: {
        filterInterface() {
          let arr = []
          arr = this.list.reduce((all, cur) => {
            all.push(cur.id)
            return all
          }, [])
          let temp = []
          temp = this.typelist.map(item => {
            item.disabled = false
            if (arr.includes(item.id) && item.name !== lang.help_text24) {
              item.disabled = true
            }
            return item
          })
          return temp
        },
        calcItem (it) {
         return it => {
          if (this.checkgroup.length > 0) {
            console.log('#####',it, arr)
            if (Array.from(this.checkgroup).includes(it)) {
              return false
            } else {
              return true
            }

          }
         }
        },
        calcSearchList () {
          return this.searchlist.filter(item => item.hidden === 0)
        }
      },
      methods: {
        backList() {
          location.href = 'index.htm'
        },
        //选标题
        changetitle(e, index) {
          this.list[index].helps = []
          this.typelist.map((item) => {
            if (item.id === e) {
              item.disabled = true
              this.dialog_name = item.name
            }
          });
        },
        getindexlist() {
          helpindex().then((res) => {
            res.data.data.index.map((item) => {
              if (!item.id) {
                item.id = 0;
                item.name = lang.help_text24;
                item.index_hot_show = 0;
                item.helps = [];
                return item;
              }
            });
            this.list = res.data.data.index;
            this.listold = JSON.parse(JSON.stringify(this.list));
          });
        },
        //获取分类列表
        gettypelist() {
          gethelptype().then((res) => {
            for (let i = 0; i < res.data.data.list.length; i++) {
              res.data.data.list[i].isedit = false;
              res.data.data.list[i].disabled = false;
              res.data.data.list[i].index = i + 1;
            }
            this.typelist = res.data.data.list;
            this.typelist.push({
              id: 0,
              name: lang.help_text24,
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
          if (e.length > 3) {
            this.$message.warning(lang.help_text25);
            e.pop();
            this.checkgroup = e;
            return;
          }
          this.checkgroup = e;
          this.choselist = [];
          for (let i = 0; i < this.searchlistall.length; i++) {
            for (let j = 0; j < e.length; j++) {
              if (this.searchlistall[i].id === e[j]) {
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
        mobile_file(id, index) {
          if (id === 0) {
            return;
          }
          this.checkgroup = [];
          this.showdialog = true;
          this.typelist.map((item) => {
            if (item.id === id) {
              this.dialog = item;
              this.dialog_name = item.name
            }
          });
          this.listold.map((item) => {
            if (item.id === id) {
              // this.dialog = item;
              this.choselist = this.dialog.helps;
              this.checkgroup = [];
              this.dialog.helps?.map((item) => {
                this.checkgroup.push(item.id);
              });
            }
          });
          this.choselist = this.listold.filter(item => item.id === id)[0]?.helps || []
          this.checkgroup = this.choselist.reduce((all, cur) => {
            all.push(cur.id)
            return all
          }, [])
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
            this.$message.warning(lang.help_text26);
            return;
          }
          this.submitLoading = true
          savehelpindex({ index: [...this.list] }).then((res) => {
            if (res.data.status === 200) {
              this.$message.success(lang.help_text27);
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
          }).finally(() => {
            this.submitLoading = false;
          });
        },
        close(context) {

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
        },
        onKeydownEsc(context) {
        },
        onClickCloseBtn(context) {
        },
        onClickOverlay(context) {
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
