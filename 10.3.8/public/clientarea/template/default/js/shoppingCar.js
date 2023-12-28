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
        eventCode
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
          isShowFull: false // 是否开启满减优惠
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
          if (isNaN(money)) {
            return "0.00";
          } else {
            const temp = `${money}`.split(".");
            return parseInt(temp[0]).toLocaleString() + "." + (temp[1] || "00");
          }
        }
      },
      computed: {
        calcItemPrice() {
          return function (item) {
            const price =
              item.price * item.qty -
              item.code_discount -
              item.level_discount * (item.hasCalc ? 1 : item.qty) -
              item.eventDiscount;
            return price > 0 ? formatNuberFiexd(price) : 0;
          };
        },
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
            return (
              pre +
              (cur.price * cur.qty * 1000 -
                cur.level_discount * (cur.hasCalc ? 1 : cur.qty) * 1000 -
                cur.code_discount * 1000 -
                cur.eventDiscount * 1000) /
                1000
            );
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
        }
      },
      methods: {
        // 获取购物车列表
        getCartList() {
          this.listLoding = true;
          cartList()
            .then((res) => {
              this.shoppingList = res.data.data.list.map((item, index) => {
                item.price = 0; // 商品单价
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
              const arr = this.shoppingList.filter((item) => {
                return item.customfield?.is_domain !== 1;
              });
              this.showList = [...arr];
              this.listLoding = false;
              arr.forEach((item) => {
                configOption(item.product_id, item.config_options)
                  .then(async (res) => {
                    item.info = res.data.data;
                    const son_previews = [];
                    if (
                      res.data.data.other &&
                      res.data.data.other.son_previews
                    ) {
                      res.data.data.other.son_previews.forEach((item) => {
                        item.forEach((items) => {
                          son_previews.push(items);
                        });
                      });
                    }
                    item.preview = res.data.data.preview.concat(son_previews);
                    if (res.data.data.discount) {
                      item.price = (
                        Number(res.data.data.price) +
                        Number(res.data.data.discount)
                      ).toFixed(2); // 商品价格
                      item.level_discount = Number(res.data.data.discount);
                      item.hasCalc = false; // 只计算了单个价格的折扣，页面需要乘以数量
                    } else {
                      item.price = Number(res.data.data.price); // 商品价格
                      item.hasCalc = true;
                    }
                    // 开启了等级优惠
                    if (this.isShowLevel && !res.data.data.discount) {
                      item.level_discount = await this.getLeveAmout(item);
                    }
                    // 开启了优惠券
                    if (this.isShowPromo && item.customfield.promo_code) {
                      item.isUseDiscountCode = true;
                      this.isUseDiscountCode = true;
                      item.code_discount = await this.getCodeAmount(item);
                    }
                    // 开启了活动满减
                    if (this.isShowFull && item.customfield.event_promotion) {
                      item.eventDiscount = await this.getEventAmout(item);
                    }
                  })
                  .catch((err) => {
                    console.log(err);
                    item.preview = [];
                  })
                  .finally(() => {
                    item.priceLoading = false;
                    this.showList = [...arr];
                    item.isLoading = false;
                  });
              });
            })
            .catch((err) => {
              console.log(err);
              this.listLoding = false;
            });
        },
        changeEventCode(priceObj, item) {
          item.eventDiscount = priceObj.discount;
          item.customfield.event_promotion = priceObj.id;
          const params = {
            position: item.position,
            product_id: item.product_id,
            config_options: item.config_options, // 配置信息
            qty: item.qty, // 商品数量
            customfield: item.customfield
          };
          updateCart(params).then((res) => {
            this.$forceUpdate();
          });
        },
        // 获取等级优惠
        async getLeveAmout(item) {
          let discount = 0;
          await clientLevelAmount({
            id: item.product_id,
            amount: item.price * item.qty
          })
            .then((res) => {
              if (res.data.status === 200) {
                discount = Number(res.data.data.discount);
              }
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
          return discount;
        },
        // 获取优惠码金额
        async getCodeAmount(item) {
          let discount = 0;
          await applyPromoCode({
            scene: "new",
            product_id: item.product_id,
            amount: item.price,
            billing_cycle_time: item.info.duration,
            promo_code: item.customfield.promo_code,
            qty: item.qty
          })
            .then((res) => {
              discount = Number(res.data.data.discount);
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
          return discount;
        },
        // 获取活动优惠金额
        async getEventAmout(item) {
          let discount = 0;
          await applyEventPromotion({
            event_promotion: item.customfield.event_promotion,
            product_id: item.product_id,
            qty: item.qty,
            amount: item.price,
            billing_cycle_time: item.info.duration
          })
            .then((res) => {
              discount = Number(res.data.data.discount);
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
          return discount;
        },
        // 使用优惠码
        getDiscount(data) {
          this.showList.forEach((item) => {
            if (item.position === data[2]) {
              item.code_discount = data[0];
              item.customfield.promo_code = data[1];
              item.isUseDiscountCode = true;
              const params = {
                position: data[2],
                product_id: item.product_id,
                config_options: item.config_options, // 配置信息
                qty: item.qty, // 商品数量
                customfield: item.customfield
              };
              updateCart(params).then((res) => {});
              this.$forceUpdate();
            }
          });
        },
        // 删除优惠码
        removeDiscountCode(item) {
          item.code_discount = 0;
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
            customfield: item.customfield
          };
          updateCart(params).then((res) => {
            console.log(res.data.data);
          });
          this.$forceUpdate();
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
              customfield: item.customfield
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
          item.priceLoading = true;
          this.timer1 = setTimeout(() => {
            this.handelEditGoodsNum(index, n)
              .then(async () => {
                // 更新等级优惠金额
                if (this.isShowLevel && item.hasCalc) {
                  item.level_discount = await this.getLeveAmout(item);
                }
                // 开启了优惠券
                if (this.isShowPromo && item.customfield.promo_code) {
                  item.code_discount = await this.getCodeAmount(item);
                }
                // 开启了活动满减
                if (this.isShowFull && item.customfield.event_promotion) {
                  item.eventDiscount = await this.getEventAmout(item);
                }
              })
              .catch((err) => {
                err.data.msg && this.$message.error(err.data.msg);
              })
              .finally(() => {
                item.priceLoading = false;
                clearTimeout(this.timer1);
                this.timer1 = null;
              });
          }, 500);
        },
        // 结算
        goSettle() {
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
          location.href = `./settlement.htm?cart=1`;
        },
        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          document.title =
            this.commonData.website_name + "-" + lang.common_cloud_text301;
        }
      }
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
