// const { json } = require("stream/consumers");

(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const documents = document.getElementsByClassName("promocreate")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      data() {
        return {
          message: "template...",
          params: {
            keywords: "",
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          id: "",
          coderandom: ["alpha", "num"],
          product: [],
          datacenter: [], //数据中心
          subselect: [],
          total: 100,
          pageSizeOptions: [20, 50, 100],
          detialform: {
            product: [],
            apply_scene_time: [],
            value1: 0,
            value2: 0,
            // value: 0.0,
          },
          attachment: [],
          typelist: [],
          size: "medium",
          tableLayout: false,
          stripe: true,
          bordered: true,
          hover: false,
          requiredRules: {
            code: [{ required: true, message: "优惠码代号必填" }],
            name: [{ required: true, message: "优惠码名称必填" }],
            type: [{ required: true, message: "优惠类型必填" }],
            valid_time: [{ required: true, message: "有效时间段必填" }],
            max_times_control: [{ required: true, message: "可使用次数必填" }],
            total_control: [{ required: true, message: "总金额必填" }],
            apply_client: [{ required: true, message: "适用客户必填" }],
            // product_type: [{ required: true, message: "适用产品及规格必填" }],
            apply_scene: [{ required: true, message: "适用付款场景必填" }],
            overlay: [{ required: true, message: "是否允许叠加必填" }],
          },
          rules: {
            productid: [{ required: true, message: "商品必填" }],
          },
          showedit: false,
          formData: {},
          products: [30],
          configoptionold: {},
          configoption: {},
          configoptionarr: [],
          apply_product_list: [], //自定义产品配置
          productlist: [],
          product_option: [], //自定义商品配置所有选项
        };
      },

      methods: {
        //生成优惠码
        getgeneratecode() {
          let parma = {
            alpha: 0,
            num: 0,
          };
          if (this.coderandom.indexOf("alpha") !== -1) {
            parma.alpha = 1;
          }
          if (this.coderandom.indexOf("num") !== -1) {
            parma.num = 1;
          }
          generatecode(parma).then((res) => {
            console.log(res, "resss");
            this.$set(this.detialform, "code", res.data.data.promo_code);
            // this.detialform.code = res.data.data.promo_code;
            console.log(this.detialform, "this.detialform");
          });
        },
        //选择生成码的规则
        onChangecode(e) {
          console.log(e, "onChangecode");
        },
        //选择时长
        applytime(e) {
          console.log(e, "111");
          if (e.includes("all") && e.length > 1) {
            this.detialform.apply_scene_time = ["all"];
            this.$message.warning("不限和其他选项不可同选！");
          }
        },
        //获取数据中心
        getdatacentert() {
          let params = {
            page: 1,
            limit: 10,
            product_id: "",
            orderby: "id",
            sort: "desc",
          };
          datalist().then((res) => {
            console.log(res, "qqq");
            this.datacenter = res.data.data.list;
          });
        },
        chengeproduct(e) {
          console.log(e, "www");
          this.getproductalllist(e);
        },
        //获取所有配置项
        getproductalllist(id) {
          productalllist({ id }).then((res) => {
            this.subselect = res.data.data ? res.data.data : res.data;
            this.subselect.map((item) => {
              this.$set(this.rules, item.field, [
                { required: true, message: item.name + "必填" },
              ]);
              this.rules[item.field] = [
                { required: true, message: item.name + "必填" },
              ];
            });
            console.log(res, "33333");
          });
        },
        //获取商品列表
        getproductlist() {
          productlist().then((res) => {
            console.log(res, "qqq");
            this.product = res.data.data.list;
          });
        },
        handleChangestart(e) {
          // this.$set(this.detialform,'start_time',e)
          // this.detialform.start_time=e
          console.log(e, "1");
        },
        handleChangeend(e) {
          // this.$set(this.detialform,'expiration_time',e)
          // this.detialform.expiration_time=e
          console.log(e, "2");
        },
        // 切换分页
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.params.keywords = "";
        },

        formatResponse(res) {
          console.log(res, "res");
          if (res.status != 200) {
            return { error: "上传失败，请重试" };
          }
          return { save_name: res.data.save_name, url: res.url };
        },

        //提交
        save() {
          this.$refs.myform.validate(this.requiredRules).then((res) => {
            console.log(res, "validate");
            if (res === true) {
              console.log(this.detialform, "this.detialform");
              if (
                this.detialform.type === "percent" &&
                !this.detialform.value1
              ) {
                this.$message.warning("请填写优惠码百分比！");
                return;
              } else if (
                this.detialform.type === "full_reduce" &&
                (!this.detialform.full || !this.detialform.reduce)
              ) {
                this.$message.warning("请填写优惠码满减！");
                return;
              }
              if (
                this.detialform.valid_time === 1 &&
                (!this.detialform.start_time ||
                  !this.detialform.expiration_time)
              ) {
                this.$message.warning("请填写有效时间！");
                return;
              }
              if (
                this.detialform.max_times_control === 1 &&
                !this.detialform.max_times
              ) {
                this.$message.warning("请填写最大使用次数！");
                return;
              }
              if (
                this.detialform.total_control === 1 &&
                !this.detialform.total
              ) {
                this.$message.warning("请填写总金额！");
                return;
              }
              if (
                this.detialform.apply_product === 1 &&
                this.detialform.products === []
              ) {
                this.$message.warning("请填写适用产品及规格！");
                return;
              }
              this.detialform.start_time =
                this.detialform.start_time &&
                this.detialform.start_time.length > 10
                  ? Date.parse(new Date(this.detialform.start_time)) / 1000
                  : this.detialform.start_time;
              this.detialform.expiration_time =
                this.detialform.expiration_time &&
                this.detialform.expiration_time.length > 10
                  ? Date.parse(new Date(this.detialform.expiration_time)) / 1000
                  : this.detialform.expiration_time;
              this.detialform.value =
                this.detialform.value1 + "." + this.detialform.value2;

              if (this.detialform.type === "percent") {
                delete this.detialform.full;
                delete this.detialform.reduce;
              }
              if (this.detialform.type === "full_reduce") {
                delete this.detialform.value;
              }
              this.detialform.value =
                this.detialform.value1 + "." + this.detialform.value2;

              if (this.id) {
                editpromocode({ ...this.detialform, id: this.id })
                  .then((res) => {
                    if (res.data.status === 200) {
                      console.log(res, "res");
                      // this.$message.success(res.data.msg);
                      // setTimeout(() => {
                      location.href = "promo_code.html";
                      // window.history.go(-1);
                      // }, 500);
                    }
                  })
                  .catch((err) => {
                    console.log(err, "err");
                    this.$message.error(err.data.msg);
                  });
              } else {
                addpromocode(this.detialform)
                  .then((res) => {
                    if (res.data.status === 200) {
                      console.log(res, "res");
                      this.$message.success(res.data.msg);
                      // setTimeout(() => {
                      location.href = "promo_code.html";
                      // }, 500);
                    }
                  })
                  .catch((err) => {
                    console.log(err, "err");
                    this.$message.error(err.data.msg);
                  });
              }
            }
          });
        },
        cancle() {
          window.history.go(-1);
        },
        //防抖
        debounce(func, wait) {
          console.log(func, wait);
          let timer = 0;
          // 这里返回的函数是每次用户实际调用的防抖函数
          // 如果已经设定过定时器了就清空上一次的定时器
          // 开始一个新的定时器，延迟执行用户传入的方法
          return function (...args) {
            console.log(func, "1111");
            if (timer) clearTimeout(timer);
            timer = setTimeout(() => {
              func.apply(this, args);
            }, wait);
          };
        },
        formatDateTime(obj) {
          if (obj == null) {
            return null;
          }
          let date = new Date(obj * 1000);
          var Y = date.getFullYear() + "-";
          var M =
            (date.getMonth() + 1 < 10
              ? "0" + (date.getMonth() + 1)
              : date.getMonth() + 1) + "-";
          var D =
            (date.getDate() < 10 ? "0" + date.getDate() : date.getDate()) + " ";
          var h =
            (date.getHours() < 10 ? "0" + date.getHours() : date.getHours()) +
            ":";
          var m =
            (date.getMinutes() < 10
              ? "0" + date.getMinutes()
              : date.getMinutes()) + ":";
          var s =
            date.getSeconds() < 10
              ? "0" + date.getSeconds()
              : date.getSeconds();

          strDate = Y + M + D + h + m + s;
          return strDate;
        },
        changevalue1(e) {
          //   this.detialform.value1 = e;
          console.log(e, "value1");
        },
        changevalue2(e) {
          //   this.detialform.value2 = e;
          console.log(e, "value2");
        },
        edit(id) {
          this.showedit = true;
          this.getproductalllist(id);
          this.formData.productid = Number(id);
          this.productlist.map((item) => {
            if (item.id === id) {
              item.option.map((it) => {
                this.formData[it.optionfield] = it.optionvalue;
              });
            }
          });
          console.log(this.formDat, "this.formDat");
        },
        deletes(id) {
          for (const key in this.configoptionold) {
            if (key == id) {
              delete this.configoptionold[key];
            }
          }
          console.log(this.configoptionold, "this.configoptionold");
          let arr = [];
          this.productlist.map((item) => {
            if (item.id != id) {
              arr.push(item);
            }
          });
          this.productlist = arr;
        },
        onSubmit() {
          this.$refs.form.validate(this.requiredRules).then((res) => {
            // this.apply_product_list.push(this.formData);
            // this.formData = {};
            let obj = {};
            for (const key in this.formData) {
              if (key !== "productid") {
                obj[key] = this.formData[key];
              }
            }
            this.configoptionold[this.formData.productid] = obj;
            this.showedit = false;
            this.formatdata();
            console.log(
              "formData",
              this.rules,
              this.configoptionold,
              this.formData
            );
          });
          // this.formData = {};
        },
        Cancelpz() {
          this.formData = {};
        },
        add() {
          this.showedit = true;
          this.getdatacentert();
        },
        formatdata() {
          this.configoption = JSON.parse(JSON.stringify(this.configoptionold));
          console.log(
            this.configoptionold,
            this.configoption,
            "this.configoptionold"
          );
          let resdata = [];
          for (const key in this.configoption) {
            productalllist({ id: key }).then((res, index) => {
              let obj = {};
              obj.id = key;
              obj.data = res.data.data ? res.data.data : res.data;
              resdata.push(obj);
              this.product_option = resdata;
              this.validPeoduct();
              console.log(resdata, 11111);
            });
          }
        },

        validPeoduct() {
          for (const key in this.configoption) {
            this.product_option.map((item) => {
              if (key === item.id) {
                console.log(item, "item");
                item.data.map((it) => {
                  for (const i in this.configoption[key]) {
                    if (it.field === i) {
                      let arr = [];
                      it.option.map((op) => {
                        this.configoption[key][i].map((reop) => {
                          if (op.value == reop) {
                            arr.push(op);
                            this.configoption[key][i].option = arr;
                          }
                        });
                      });
                      this.configoption[key][i].optionname = it.name;
                      this.configoption[key][i].optionfield = it.field;
                    }
                  }
                });
                console.log(this.configoption[key], item, "key");
                // this.configoption[key]
              }
            });
          }
          let arr = [];
          productlist().then((res) => {
            console.log(this.configoption, "gggg");
            for (const key in this.configoption) {
              res.data.data.list.map((item) => {
                if (key == item.id) {
                  console.log(key, item.id, "item.id");
                  this.configoption[key].productname = item.name;
                }
              });
              let obj = {};
              obj.productname = this.configoption[key].productname;
              obj.id = key;
              obj.option = [];
              for (const i in this.configoption[key]) {
                if (i != "productname") {
                  let oj = {};
                  oj.optionname = this.configoption[key][i].optionname;
                  oj.optionfield = this.configoption[key][i].optionfield;
                  oj.option = [];
                  oj.optionvalue = [];
                  this.configoption[key][i].option.map((op) => {
                    oj.option.push(op.name);
                    oj.optionvalue.push(op.value);
                  });
                  obj.option.push(oj);
                }
              }
              arr.push(obj);
            }
            this.productlist = arr;
            // this.productlist.map((item) => {
            //   this.detialform.products.push(item.id);
            // });
            this.detialform.products = [];
            for (const key in this.configoptionold) {
              this.detialform.products.push(key);
            }

            this.detialform.products = Array.from(
              new Set(this.detialform.products)
            );
            this.detialform.configoption = this.configoptionold;
            console.log(arr, this.productlist, " this.configoption000");
          });
          console.log(this.configoption, "this.configoption");
        },
        max_timeschange(e) {
          if (e.length > 10) {
            this.detialform.max_times = e.slice(0, 10);
            this.$message.warning("可使用次数最多输入10位数！");
          }
          console.log(e.length, "e");
        },
        totalchange(e) {
          if (e.length > 10) {
            this.detialform.total = e.slice(0, 10);
            this.$message.warning("总金额最多输入10位数！");
          }
          console.log(e.length, "e");
        },
      },

      created() {
        this.id = window.location.search.slice(1).split("=")[1];
        console.log(this.id, "this.id");
        if (this.id) {
          filedetial({ id: this.id }).then((res) => {
            if (res.data.status === 200) {
              this.detialform = res.data.data.promo_code;
              console.log(this.detialform, "this.detialform");
              this.detialform.start_time = this.formatDateTime(
                this.detialform.start_time
              );
              this.detialform.expiration_time = this.formatDateTime(
                this.detialform.expiration_time
              );
              console.log(this.detialform, "this.detialform4");
              this.$set(
                this.detialform,
                "value1",
                Math.floor(this.detialform.value)
              );
              this.$set(
                this.detialform,
                "value2",
                this.detialform.value.substring(
                  this.detialform.value.length - 2
                )
              );
              this.detialform.products.map((item) => {
                this.configoptionold[item.product_id] = item.configoption;
              });
              this.formatdata();

              console.log(this.product_option, "this.product_option");
              console.log(this.detialform, "this.detialform1111");
            }
          });
        }
        this.getproductlist();
      },
      mounted() {},
      computed: {},
    }).$mount(documents);
    typeof old_onload == "function" && old_onload();
  };
})(window);
