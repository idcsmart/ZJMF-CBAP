(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        asideMenu,
        topMenu,
        discountCode,
        eventCode,
      },
      created() {
        localStorage.frontMenusActiveId = "";
        this.getCommonData();
      },
      mounted() {
        this.addons_js_arr = JSON.parse(
          document.querySelector("#addons_js").getAttribute("addons_js")
        ); // 插件列表
        const arr = this.addons_js_arr.map((item) => {
          return item.name;
        });
        if (arr.includes("PromoCode")) {
          // 开启了优惠码插件
          this.isShowPromo = true;
        }
        if (arr.includes("IdcsmartClientLevel")) {
          // 开启了等级优惠
          this.isShowLevel = true;
        }
        if (arr.includes("EventPromotion")) {
          // 开启活动满减
          this.isShowFull = true;
        }
        this.getCartList();
      },
      updated() {
        // 关闭loading
        document.getElementById("mainLoading").style.display = "none";
        document.getElementsByClassName("template")[0].style.display = "block";
      },
      destroyed() {},
      data() {
        return {
          timer1: null,
          listLoding: false,
          commonData: {},
          searchVal: "",
          checkedCities: [],
          checkAll: false, // 是否全选
          visible: false,
          showList: [],
          addons_js_arr: [], // 插件列表
          shoppingList: [],
          isShowPromo: false, // 是否开启优惠码
          isShowLevel: false, // 是否开启等级优惠
          isShowFull: false, // 是否开启满减优惠
          settleLoading: false,
        };
      },

      filters: {
        formateTime(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000);
          } else {
            return "--";
          }
        },
        filterMoney(money) {
          if (isNaN(money) || money * 1 < 0) {
            return "0.00";
          } else {
            return formatNuberFiexd(money);
          }
        },
      },
      computed: {
        totalPrice() {
          const arr = [];
          this.checkedCities.forEach((position) => {
            this.showList.forEach((item) => {
              if (position === item.position) {
                arr.push(item);
              }
            });
          });
          return arr.reduce((pre, cur) => {
            return pre + cur.calcItemPrice * 1;
          }, 0);
        },
        nowList() {
          if (this.searchVal !== "") {
            return this.showList.filter((item) =>
              item.name.includes(this.searchVal)
            );
          } else {
            return this.showList;
          }
        },
      },
      methods: {
        // 获取购物车列表
        getCartList() {
          this.listLoding = true;
          cartList()
            .then((res) => {
              this.shoppingList = res.data.data.list.map((item, index) => {
                item.price = 0; // 商品单价
                item.calcItemPrice = 0;
                item.code_discount = 0; // 商品优惠码抵扣金额
                item.level_discount = 0; // 商品等级优惠折扣金额
                item.eventDiscount = 0; // 商品活动优惠金额
                item.isUseDiscountCode = false; // 商品是否使用优惠码
                item.position = index; // 商品所在购物车位置
                item.isShowTips = false; // 是否提示商品库存不足
                item.priceLoading = true; // 商品价格loading
                if (item.stock_control === 1 && item.qty > item.stock_qty) {
                  item.isShowTips = true;
                  item.qty = item.stock_qty;
                }
                item.isLoading = true; // 商品loading
                return item;
              });
              const arr = this.shoppingList.filter((arritem) => {
                return arritem.customfield?.is_domain !== 1;
              });
              this.listLoding = false;
              this.showList = [...arr];
              this.showList.forEach((item) => {
                this.getConfigOption(item);
              });
            })
            .catch((err) => {
              console.log(err);
              this.listLoding = false;
            });
        },
        // 获取商品配置项价格
        getConfigOption(item) {
          const params = {
            config_options: {
              ...item.config_options,
              promo_code: item.customfield.promo_code,
              event_promotion: item.customfield.event_promotion,
            },
            qty: item.qty,
          };
          item.priceLoading = true;
          configOption(item.product_id, params)
            .then(async (res) => {
              item.info = res.data.data;
              const son_previews = [];
              if (res.data.data.other && res.data.data.other.son_previews) {
                res.data.data.other.son_previews.forEach((i) => {
                  i.forEach((items) => {
                    son_previews.push(items);
                  });
                });
              }
              item.preview = res.data.data.preview.concat(son_previews);
              item.price = res.data.data.price * 1;
              item.calcItemPrice = res.data.data.price_total * 1;
              item.level_discount =
                res.data.data.price_client_level_discount * 1 || 0;
              item.code_discount =
                res.data.data.price_promo_code_discount * 1 || 0;
              item.eventDiscount =
                res.data.data.price_event_promotion_discount * 1 || 0;
            })
            .catch((err) => {
              item.preview = [];
            })
            .finally(() => {
              item.priceLoading = false;
              item.isLoading = false;
              this.$forceUpdate();
            });
        },
        // 更改活动
        changeEventCode(priceObj, item) {
          if (item.customfield.event_promotion == priceObj.id) {
            return;
          }
          item.customfield.event_promotion = priceObj.id;
          const params = {
            position: item.position,
            product_id: item.product_id,
            config_options: item.config_options, // 配置信息
            qty: item.qty, // 商品数量
            customfield: item.customfield,
            self_defined_field: item.self_defined_field,
          };
          updateCart(params).then((res) => {
            this.getConfigOption(item);
          });
        },
        // 使用优惠码
        getDiscount(data) {
          this.showList.forEach((item) => {
            if (item.position === data[2]) {
              item.customfield.promo_code = data[1];
              item.isUseDiscountCode = true;
              const params = {
                position: data[2],
                product_id: item.product_id,
                config_options: item.config_options, // 配置信息
                qty: item.qty, // 商品数量
                customfield: item.customfield,
                self_defined_field: item.self_defined_field,
              };
              updateCart(params).then((res) => {});
              this.getConfigOption(item);
            }
          });
        },
        // 删除优惠码
        removeDiscountCode(item) {
          item.customfield.promo_code = "";
          item.isUseDiscountCode = false;
          let i;
          this.shoppingList.forEach((items, index) => {
            if (items.position === item.position) {
              i = index;
            }
          });
          const params = {
            position: i,
            product_id: item.product_id,
            config_options: item.config_options, // 配置信息
            qty: item.qty, // 商品数量
            customfield: item.customfield,
            self_defined_field: item.self_defined_field,
          };
          updateCart(params).then((res) => {
            this.getConfigOption(item);
          });
        },
        // 搜索
        searchValChange(value) {
          this.checkedCities = [];
          this.checkAll = false;
        },
        // 点击全选按钮
        handleCheckAllChange(val) {
          const arr = this.showList.filter((item) => {
            return item.info;
          });
          const arrr = arr.map((item) => {
            return item.position;
          });
          this.checkedCities = val ? arrr : [];
        },
        // 编辑商品数量
        handelEditGoodsNum(index, num) {
          return editGoodsNum(index, num);
        },
        // 编辑商品
        goGoods(item) {
          if (item.info) {
            const obj = {
              config_options: item.config_options, // 配置信息
              position: item.position, // 修改接口要用的位置信息
              qty: item.qty, // 商品数量
              customfield: item.customfield,
              self_defined_field: item.self_defined_field,
            };
            sessionStorage.setItem("product_information", JSON.stringify(obj));
          }
          location.href = `goods.htm?id=${item.product_id}&change=true&name=${item.name}`;
        },
        // 监听购物车选择数量变化
        handleCheckedCitiesChange(value) {
          this.checkAll = value.length === this.showList.length;
        },
        // 删除商品函数
        deleteGoodsList(arr, isRefsh) {
          deleteGoods(arr)
            .then((res) => {
              if (res.data.status === 200) {
                this.$message.success(res.data.msg);
                isRefsh && this.getCartList();
                this.$refs.topMenu.getCartList();
              }
            })
            .catch((err) => {
              err.data.msg && this.$message.error(err.data.msg);
            })
            .finally(() => {});
        },
        // 点击删除按钮
        handelDeleteGoods(item, index) {
          // 调用删除接口
          const p = item.position;
          let shoppingList_index = 0;
          let checkedCities_index = 0;
          // 删除列表中对应的商品
          this.showList.splice(index, 1);
          this.shoppingList.forEach((item, index) => {
            if (item.position === p) {
              shoppingList_index = index;
            }
          });
          this.checkedCities.forEach((item, index) => {
            if (item.position === p) {
              checkedCities_index = index;
            }
          });
          this.shoppingList.splice(shoppingList_index, 1);
          this.checkedCities.splice(checkedCities_index, 1);
          this.deleteGoodsList([shoppingList_index]);
        },
        // 删除选中的商品
        deleteCheckGoods() {
          if (this.checkedCities.length === 0) {
            this.$message.warning(lang.referral_status9);
            return;
          } else {
            this.deleteGoodsList(this.checkedCities, true);
            this.checkedCities = [];
          }
        },
        // 商品数量增加减少
        handleChange(n, o, item, index) {
          if (item.stock_control === 1 && n >= item.stock_qty) {
            this.$message.error(lang.referral_status10);
          }
          // 节个流
          if (this.timer1) {
            clearTimeout(this.timer1);
            this.timer1 = null;
          }
          this.timer1 = setTimeout(() => {
            this.handelEditGoodsNum(index, n)
              .then(async () => {
                this.getConfigOption(item);
              })
              .catch((err) => {
                err.data.msg && this.$message.error(err.data.msg);
              })
              .finally(() => {
                clearTimeout(this.timer1);
                this.timer1 = null;
              });
          }, 500);
        },
        // 结算
        goSettle() {
          this.settleLoading = true;
          if (this.checkedCities.length === 0) {
            this.$message.warning(lang.referral_status11);
            return;
          }
          const arr = []; // 装的是被选中的商品在购物位置的索引
          this.shoppingList.forEach((item, index) => {
            this.checkedCities.forEach((items) => {
              if (items == item.position) {
                arr.push(index);
              }
            });
          });
          sessionStorage.shoppingCartList = JSON.stringify(arr);
          location.href = `/cart/settlement.htm?cart=1`;
          this.settleLoading = false;
        },
        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          document.title =
            this.commonData.website_name + "-" + lang.shoppingCar_title;
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
