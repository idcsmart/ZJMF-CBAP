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
            return (
              pre +
              (cur.price * cur.qty * 1000 -
                ((cur.level_discount * (cur.hasCalc ? 1 : cur.qty)) * 1000) -
                cur.code_discount * 1000) /
                1000
            );
          }, 0);
        },
      },
      methods: {
        // 获取购物车列表
        getCartList() {
          this.listLoding = true;
          cartList()
            .then((res) => {
              this.shoppingList = res.data.data.list;
              this.listLoding = false;
              this.showList = [];
              this.shoppingList.forEach((item, index) => {
                item.price = 0; // 商品单价
                item.code_discount = 0; // 商品优惠码抵扣金额
                item.level_discount = 0; // 商品等级优惠折扣金额
                item.isUseDiscountCode = false; // 商品是否使用优惠码
                item.position = index; // 商品所在购物车位置
                item.isShowTips = false; // 是否提示商品库存不足
                item.priceLoading = true; // 商品价格loading
                if (item.stock_control === 1 && item.qty > item.stock_qty) {
                  item.isShowTips = true;
                  item.qty = item.stock_qty;
                }
                item.isLoading = true; // 商品loading
                this.showList = [...this.shoppingList].filter((item) => {
                  return item.customfield?.is_domain !== 1;
                });
                configOption(item.product_id, item.config_options)
                  .then((ress) => {
                    item.info = ress.data.data;
                    const son_previews = [];
                    if (
                      ress.data.data.other &&
                      ress.data.data.other.son_previews
                    ) {
                      ress.data.data.other.son_previews.forEach((item) => {
                        item.forEach((items) => {
                          son_previews.push(items);
                        });
                      });
                    }
                    item.preview = ress.data.data.preview.concat(son_previews); // 配置信息
                    if (ress.data.data.discount) {
                      item.price = (Number(ress.data.data.price) + Number(ress.data.data.discount)).toFixed(2); // 商品价格
                      item.level_discount = Number(ress.data.data.discount)
                      item.hasCalc = false // 只计算了单个价格的折扣，页面需要乘以数量
                    } else {
                      item.price = Number(ress.data.data.price); // 商品价格
                      item.hasCalc = true
                    }
                    item.priceLoading = false;
                    if (this.isShowLevel && !ress.data.data.discount) { // 特殊处理 价格里面没有 discount 才计算等级折扣
                      clientLevelAmount({
                        id: item.product_id,
                        amount: item.price * item.qty,
                      })
                        .then((resss) => {
                          if (resss.data.status === 200) {
                            item.level_discount = Number(
                              resss.data.data.discount
                            ); // 获取商品等级折扣金额
                            item.isLoading = false;
                            this.showList = [...this.shoppingList].filter(
                              (item) => {
                                return item.customfield?.is_domain !== 1;
                              }
                            );
                            if (this.shoppingList.length === 1) {
                              this.checkedCities = [
                                this.shoppingList[0].position,
                              ];
                              this.checkAll = true;
                            }
                          }
                        })
                        .catch((error) => {
                          item.isLoading = false;
                          this.showList = [...this.shoppingList].filter(
                            (item) => {
                              return item.customfield?.is_domain !== 1;
                            }
                          );
                        });
                    } else {
                      item.isLoading = false;
                      this.showList = [...this.shoppingList].filter((item) => {
                        return item.customfield?.is_domain !== 1;
                      });
                    }
                    if (this.isShowPromo && item.customfield.promo_code) {
                      item.isUseDiscountCode = true;
                      // 更新优惠码
                      applyPromoCode({
                        scene: "new",
                        product_id: item.product_id,
                        amount: item.price,
                        billing_cycle_time: item.info.duration,
                        promo_code: item.customfield.promo_code,
                        qty: item.qty,
                      })
                        .then((res) => {
                          item.priceLoading = false;
                          item.code_discount = Number(res.data.data.discount);
                          this.showList = [...this.shoppingList].filter(
                            (item) => {
                              return item.customfield?.is_domain !== 1;
                            }
                          );
                        })
                        .catch((err) => {
                          this.$message.error(err.data.msg);
                          item.priceLoading = false;
                          this.showList = [...this.shoppingList].filter(
                            (item) => {
                              return item.customfield?.is_domain !== 1;
                            }
                          );
                        });
                    }
                  })
                  .catch(() => {
                    item.preview = [];
                    item.invalid = true;
                    item.isLoading = false;
                    item.priceLoading = false;
                    this.showList = [...this.shoppingList].filter((item) => {
                      return item.customfield?.is_domain !== 1;
                    });
                  });
              });
            })
            .catch(() => {
              this.listLoding = false;
            });
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
                customfield: item.customfield,
              };
              updateCart(params).then((res) => {
                console.log(res.data.data);
              });
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
            customfield: item.customfield,
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
          if (value !== "") {
            const arr = [];
            this.shoppingList.forEach((item, index) => {
              if (this.shoppingList[index].name.includes(value)) {
                arr.push(item);
              }
            });
            this.showList = arr;
          } else {
            this.showList = [...this.shoppingList].filter((item) => {
              return item.customfield?.is_domain !== 1;
            });
          }
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
              i = index;
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
          this.$set(this.showList, index, {
            ...this.showList[index],
            priceLoading: true,
          });
          this.timer1 = setTimeout(() => {
            this.handelEditGoodsNum(index, n)
              .then(() => {
                // 更新等级优惠金额
                if (this.isShowLevel && item.hasCalc) {
                  this.$set(this.showList, index, {
                    ...this.showList[index],
                    priceLoading: true,
                  });
                  clientLevelAmount({
                    id: item.product_id,
                    amount: item.price * item.qty,
                  })
                    .then((resss) => {
                      if (resss.data.status === 200) {
                        item.level_discount = Number(resss.data.data.discount); // 获取商品等级折扣金额
                        this.$set(this.showList, index, {
                          ...this.showList[index],
                          priceLoading: false,
                        });
                        this.$set(this.showList, index, {
                          ...this.showList[index],
                          level_discount: Number(resss.data.data.discount),
                        });
                      }
                    })
                    .catch((error) => {
                      this.$set(this.showList, index, {
                        ...this.showList[index],
                        priceLoading: false,
                      });
                    });
                }
                // 更新优惠码优惠金额
                if (this.isShowPromo && item.isUseDiscountCode) {
                  this.$set(this.showList, index, {
                    ...this.showList[index],
                    priceLoading: true,
                  });
                  // 更新优惠码
                  applyPromoCode({
                    scene: "new",
                    product_id: item.product_id,
                    amount: item.price,
                    billing_cycle_time: item.info.duration,
                    promo_code: item.customfield.promo_code,
                    qty: item.qty,
                  })
                    .then((res) => {
                      this.$set(this.showList, index, {
                        ...this.showList[index],
                        priceLoading: false,
                      });
                      this.$set(this.showList, index, {
                        ...this.showList[index],
                        code_discount: Number(res.data.data.discount),
                      });
                    })
                    .catch((err) => {
                      this.$message.error(err.data.msg);
                      this.$set(this.showList, index, {
                        ...this.showList[index],
                        priceLoading: false,
                      });
                    });
                }
              })
              .catch((err) => {
                err.data.msg && this.$message.error(err.data.msg);
              })
              .finally(() => {
                clearTimeout(this.timer1);
                this.timer1 = null;
                this.$set(this.showList, index, {
                  ...this.showList[index],
                  priceLoading: false,
                });
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
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
