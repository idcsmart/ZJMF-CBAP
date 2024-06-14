(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("promo-detail")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    Vue.prototype.moment = window.moment;
    new Vue({
      components: {
        comConfig,
        comTreeSelect,
      },
      data() {
        return {
          id: "",
          formData: {
            code: "",
            type: "percent",
            value: "",
            start_time: "",
            end_time: "",
            max_times: "",
            client_type: "all",
            single_user_once: 0,
            upgrade: 0,
            host_upgrade: 0,
            renew: 0,
            loop: 0,
            cycle_limit: 0,
            cycle: [],
            notes: "",
            products: [],
            need_products: [],
          },
          time_diff: "",
          curTime: "",
          optTit: lang.order_new,
          timeOpt: [
            {
              value: "1-d",
              label: 1 + lang.day,
            },
            {
              value: "3-d",
              label: 3 + lang.day,
            },
            {
              value: "7-d",
              label: 7 + lang.day,
            },
            {
              value: "15-d",
              label: 15 + lang.day,
            },
            {
              value: "1-M",
              label: 1 + lang.month_unit,
            },
            {
              value: "3-M",
              label: 3 + lang.month_unit,
            },
            {
              value: "6-M",
              label: 6 + lang.month_unit,
            },
            {
              value: "1-y",
              label: 1 + lang.year,
            },
            {
              value: "2-y",
              label: 2 + lang.year,
            },
          ],
          rules: {
            code: [
              {
                required: true,
                message: lang.input + lang.promo_tip9,
                type: "error",
              },
              {
                pattern: /^\S*(?=\S{9,})(?=\S*\d)(?=\S*[A-Z])(?=\S*[a-z])\S*$/,
                message: lang.input + lang.promo_tip9,
              },
            ],
            type: [
              {
                required: true,
                message: lang.input + lang.promo_code,
                type: "error",
              },
            ],
            value: [
              {
                required: true,
                message: lang.input + lang.promo_code,
                type: "error",
              },
            ],
            // start_time: [
            //   { required: true, message: lang.input + lang.assert_time, type: 'error' }
            // ],
            max_times: [
              {
                required: true,
                message: lang.input + lang.max_times,
                type: "error",
              },
              {
                pattern: /^[0-9]*$/,
                message: lang.input + lang.package_tip,
                type: "warning",
              },
            ],
            cycle: [
              {
                required: true,
                message: lang.select + lang.cycle,
                type: "error",
              },
            ],
          },
          typeOptions: [
            {
              value: "percent",
              label: lang.percent,
            },
            {
              value: "fixed_amount",
              label: lang.fixed_amount,
            },
            {
              value: "replace_price",
              label: lang.replace_price,
            },
            {
              value: "free",
              label: lang.free,
            },
          ],
          // 用户类型
          useType: [
            {
              value: "all",
              label: lang.unlimited,
            },
            {
              value: "new",
              label: lang.no_product_users,
            },
            {
              value: "old",
              label: lang.has_product_users,
            },
          ],
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
          // 周期
          cycleOpt: [
            {
              value: "monthly",
              label: lang.month,
            },
            {
              value: "quarterly",
              label: lang.promo_quarterly,
            },
            {
              value: "semiannually",
              label: lang.promo_semiannually,
            },
            {
              value: "annually",
              label: lang.promo_annually,
            },
            {
              value: "biennially",
              label: lang.promo_biennially,
            },
            {
              value: "triennially",
              label: lang.promo_triennially,
            },
          ],
          productList: [],
          proList: [],
          treeProps: {
            keys: {
              label: "name",
              value: "key",
              children: "children",
            },
          },
          loading: false,
          optType: "add",
          promoValidator: false,
        };
      },
      watch: {
        id: {
          handler(val) {
            if (val) {
              this.optType = "update";
              this.optTit = lang.edit;
              this.getPromo();
            }
          },
        },
        "formData.start_time"(val) {
          if (val) {
            const res = this.calculateDiffTime(
              parseInt(val / 1000),
              parseInt(this.formData.end_time / 1000)
            );
            if (this.formData.end_time - val) {
              this.time_diff = res;
            }
          }
        },
        "formData.end_time"(val) {
          if (val) {
            const res = this.calculateDiffTime(
              parseInt(this.formData.start_time / 1000),
              parseInt(val / 1000)
            );
            if (val - this.formData.start_time) {
              this.time_diff = res;
            }
          }
        },
      },
      computed: {
        calcLabel() {
          switch (this.formData.type) {
            case "percent":
              return lang.discount_ratio;
            case "fixed_amount":
              return lang.deduction_amount;
            case "replace_price":
              return lang.cover_amount;
          }
        },
        calcPlaceholder() {
          switch (this.formData.type) {
            case "percent":
              return `${lang.discount_ratio}`;
            case "fixed_amount":
              return `${lang.deduction_amount}`;
            case "replace_price":
              return `${lang.cover_amount}`;
          }
        },
      },
      created() {
        this.id = location.href.split("?")[1]?.split("=")[1];
        if (!this.id) {
          this.formData.start_time = new Date().getTime();
        }
        this.getSetting();
        this.init();
      },
      methods: {
        choosePro(val) {
          this.formData.products = val;
        },
        changePromo() {
          setTimeout(() => {
            this.promoValidator =
              this.$refs.promo.errorClasses === "t-is-error";
          }, 0);
        },
        chooseNeedPro(val) {
          this.formData.need_products = val;
        },
        calculateDiffTime(startTime, endTime) {
          var diff = (endTime - startTime) * 1000;
          // 天
          var days = Math.floor(diff / (24 * 3600 * 1000));
          // 小时
          var leave1 = diff % (24 * 3600 * 1000); //计算天数后剩余的毫秒数
          var hours = Math.floor(leave1 / (3600 * 1000));
          // 分钟
          var leave2 = leave1 % (3600 * 1000); //计算小时数后剩余的毫秒数
          var minutes = Math.floor(leave2 / (60 * 1000));
          // 秒
          var leave3 = leave2 % (60 * 1000); //计算分钟数后剩余的毫秒数
          var seconds = Math.round(leave3 / 1000);
          return (
            days +
            lang.day +
            hours +
            lang.promo_hour +
            minutes +
            lang.minutes +
            seconds +
            lang.seconds
          );
        },
        changeStart(e) {
          this.formData.start_time = parseInt(moment(e).valueOf());
          this.curTime = "";
          this.$refs.formValidatorStatus.validate({
            fields: ["start_time", "end_time"],
          });
        },
        changeEnd(e) {
          this.formData.end_time = parseInt(moment(e).valueOf());
          this.time_diff = this.formData.end_time - this.formData.start_time;
          this.$refs.formValidatorStatus.validate({
            fields: ["start_time", "end_time"],
          });
        },
        chooseEnd() {
          if (!this.formData.end_time) {
            this.formData.end_time = new Date().getTime();
          }
        },
        checkTime(val) {
          if (moment(val).unix() > moment(this.formData.end_time).unix()) {
            return { result: false, message: lang.promo_tip10, type: "error" };
          }
          return { result: true };
        },
        checkTime1(val) {
          if (moment(val).unix() < moment(this.formData.start_time).unix()) {
            return { result: false, message: lang.promo_tip10, type: "error" };
          }
          return { result: true };
        },
        changeType(val) {
          this.formData.upgrade = 0;
          this.formData.host_upgrade = 0;
          this.formData.renew = 0;
          this.formData.loop = 0;
          this.$nextTick(() => {
            this.$refs.formValidatorStatus.clearValidate({
              fields: ["value"],
            });
            this.formData.value = "";
          });
        },
        // 快速选择时长
        fastClick(e) {
          if (!e) {
            return false;
          }
          const start = new Date(
            moment(this.formData.start_time).format("YYYY/MM/DD HH:mm:ss")
          );
          const time = e.split("-");
          this.formData.end_time = moment(start)
            .add(time[0], time[1])
            ._d.getTime();
          this.$refs.formValidatorStatus.validate({
            fields: ["start_time", "end_time"],
          });
        },
        // 获取优惠码详情
        async getPromo() {
          try {
            const res = await getPromoDetail({ id: this.id });
            const temp = res.data.data.promo_code;
            temp.start_time = temp.start_time * 1000;
            if (temp.end_time) {
              temp.end_time = temp.end_time * 1000;
            } else {
              temp.end_time = "";
            }
            this.formData = temp;
          } catch (error) {}
        },
        // 随机优惠码
        async randomCode() {
          try {
            const res = await getRandomPromo();
            this.formData.code = res.data.data.code;
            this.changePromo();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        chooseCycle(e) {
          this.formData.cycle = e;
        },
        back() {
          location.href = "index.htm";
        },
        checkMin(val) {
          if (val > this.formData.recharge_max) {
            return {
              result: false,
              message: lang.currency_tip,
              type: "warning",
            };
          }
          return { result: true };
        },
        checkMax(val) {
          if (val < this.formData.recharge_min) {
            return {
              result: false,
              message: lang.currency_tip,
              type: "warning",
            };
          }
          return { result: true };
        },
        changeMoney() {
          this.$refs.formValidatorStatus.validate({
            fields: ["recharge_min", "recharge_max"],
          });
        },
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.loading = true;
              const params = JSON.parse(JSON.stringify(this.formData));
              params.products = params.products.filter((item) => item);
              params.need_products = params.need_products.filter(
                (item) => item
              );
              params.start_time = parseInt(params.start_time / 1000);
              if (params.end_time) {
                params.end_time = parseInt(params.end_time / 1000);
              }
              if (this.optType === "add") {
                delete params.id;
              } else {
                params.id = this.id;
              }
              if (params.cycle_limit === 0) {
                params.cycle = [];
              }
              const res = await addAndUpdatePromo(this.optType, params);
              this.$message.success(res.data.msg);
              setTimeout(() => {
                location.href = "index.htm";
              }, 300);
              this.loading = false;
            } catch (error) {
              console.log(error);
              this.loading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
            this.$message.warning(firstError);
          }
        },
        async getSetting() {
          try {
            const res = await getCurrencyOpt();
            const temp = res.data.data;
            Object.assign(this.formData, temp);
          } catch (error) {}
        },
        async getCommonSetting() {
          try {
            const res = await Axios.get("/common");
            localStorage.setItem("common_set", JSON.stringify(res.data.data));
          } catch (error) {}
        },
        // 商品列表
        async getProList() {
          try {
            const res = await getProduct();
            const temp = res.data.data.list
              .map((item) => {
                item.key = `t-${item.id}`;
                return item;
              })
              .filter((item) => item.product_group_id_second);
            // 过滤没有父级id的商品
            this.proList = temp;
            return this.proList;
          } catch (error) {}
        },
        // 获取一级分组
        async getFirPro() {
          try {
            const res = await getFirstGroup();
            this.firstGroup = res.data.data.list.map((item) => {
              item.key = `f-${item.id}`;
              return item;
            });
            return this.firstGroup;
          } catch (error) {}
        },
        // 获取二级分组
        async getSecPro() {
          try {
            const res = await getSecondGroup();
            this.secondGroup = res.data.data.list.map((item) => {
              item.key = `s-${item.id}`;
              return item;
            });
            return this.secondGroup;
          } catch (error) {}
        },
        init() {
          try {
            // 获取商品，一级，二级分组
            Promise.all([
              this.getProList(),
              this.getFirPro(),
              this.getSecPro(),
            ]).then((res) => {
              const fArr = res[1].map((item) => {
                let secondArr = [];
                res[2].forEach((sItem) => {
                  if (sItem.parent_id === item.id) {
                    secondArr.push(sItem);
                  }
                });
                item.children = secondArr;
                return item;
              });
              setTimeout(() => {
                const temp = fArr.map((item) => {
                  item.children.map((ele) => {
                    let temp = [];
                    res[0].forEach((e) => {
                      if (e.product_group_id_second === ele.id) {
                        temp.push(e);
                      }
                    });
                    ele.children = temp;
                    return ele;
                  });
                  return item;
                });
                // 过滤无子项数据
                this.productList = temp
                  .filter((item) => item.children.length > 0)
                  .map((item) => {
                    item.children = item.children.filter(
                      (el) => el.children.length > 0
                    );
                    return item;
                  });
              }, 0);
            });
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
