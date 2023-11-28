(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = window.lang;
    const host = location.host;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    new Vue({
      components: {
        asideMenu,
        topMenu,
        payDialog,
        cashCoupon,
      },
      created() {
        localStorage.frontMenusActiveId = "";
        const temp = this.getQuery(location.search);
        if (temp.cart) {
          // 购物车过来
          this.selectGoodsList = JSON.parse(sessionStorage.shoppingCartList);
          this.isFormShoppingCart = true;
        } else {
          const obj = sessionStorage.product_information
            ? JSON.parse(sessionStorage.product_information)
            : sessionStorage.settleItem
            ? JSON.parse(sessionStorage.settleItem)
            : {};
          sessionStorage.settleItem = sessionStorage.product_information;
          sessionStorage.removeItem("product_information");
          this.isFormShoppingCart = false;
          this.productObj = {
            product_id: temp.id ? temp.id : obj.id ? obj.id : "",
            config_options: obj.config_options,
            qty: Number(obj.qty),
            customfield: obj.customfield,
          };
          productDetail(this.productObj.product_id).then((res) => {
            this.productObj.name = res.data.data.product.name;
            this.showGoodsList.push(this.productObj);
          });
        }
        this.getCommonData();
        this.getPayLisy();
      },
      mounted() {
        this.addons_js_arr = JSON.parse(
          document.querySelector("#addons_js").getAttribute("addons_js")
        ); // 插件列表
        this.getCartList();
        // 关闭loading
        // document.getElementById('mainLoading').style.display = 'none';
        // document.getElementsByClassName('template')[0].style.display = 'block'
      },
      updated() {
        // 关闭loading
        document.getElementById("mainLoading").style.display = "none";
        document.getElementsByClassName("template")[0].style.display = "block";
      },
      data() {
        return {
          commonData: {}, // 公告接口数据
          addons_js_arr: [],
          showPayBtn: false,
          // 代金券对象
          cashObj: {},
          totalPriceLoading: false,
          cashPrice: 0,
          goodIdList: [],
          isUseDiscountCode: false,
          productObj: {
            customfield: {}
          }, // 单独结算的商品对象
          shoppingList: [], // 所有购物车列表
          listLoading: false,
          isShowCash: false,
          selectGoodsList: [],
          isFormShoppingCart: true, // 是否从购物车页面结算
          showGoodsList: [], // 展示的列表
          payTypeList: [], // 支付渠道数组
          payType: "", // 选择的支付渠道
          checked: false, // 勾选隐私协议
          isShowPromo: false, // 是否开启优惠码
          isShowLevel: false, // 是否开启等级优惠
          subBtnLoading: false, // 提交按钮loading
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
          return this.showGoodsList.reduce((pre, cur) => {
            return (
              pre +
              (cur.price * cur.qty * 1000 -
                ((cur.level_discount * (cur.hasCalc ? 1 : cur.qty)) * 1000) -
                cur.code_discount * 1000) /
                1000
            );
          }, 0);
        },
        finallyPrice() {
          return this.totalPrice - this.cashPrice > 0
            ? this.totalPrice - this.cashPrice
            : 0;
        },
        orginPrice() {
          return this.showGoodsList.reduce((pre, cur) => {
            return pre + cur.price * cur.qty;
          }, 0);
        },
        totalLevelDiscount() {
          return this.showGoodsList.reduce((pre, cur) => {
            return (pre * 1000 + cur.level_discount * (cur.hasCalc ? 1 : cur.qty) * 1000) / 1000;
          }, 0);
        },
        totalCodelDiscount() {
          return this.showGoodsList.reduce((pre, cur) => {
            return (pre * 1000 + cur.code_discount * 1000) / 1000;
          }, 0);
        },
      },
      methods: {
        getRule(arr) {
          let isHave = this.showFun(arr, "PayController::pay");
          if (isHave) {
            this.showPayBtn = true;
          }
        },
        showFun(arr, str) {
          if (typeof arr == "string") {
            return true;
          } else {
            let isShow = "";
            isShow = arr.find((item) => {
              let isHave = item.includes(str);
              if (isHave) {
                return isHave;
              }
            });
            return isShow;
          }
        },
        // 获取购物车列表
        getCartList() {
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
          if (arr.includes("IdcsmartVoucher")) {
            // 开启了代金券
            this.isShowCash = true;
          }
          this.listLoading = true;
          cartList().then((res) => {
            const arr = [];
            this.shoppingList = res.data.data.list;
            if (this.isFormShoppingCart) {
              this.selectGoodsList.forEach((item) => {
                const obj = this.shoppingList[item];
                arr.push(obj);
              });
            } else {
              arr.push(this.productObj);
            }
            this.showGoodsList = [...arr];
            this.listLoading = false;

            arr.forEach((item) => {
              item.isLoading = true;
              console.log(item.product_id);
              this.goodIdList.push(item.product_id);
              item.price = 0; // 商品单价
              item.code_discount = 0; // 商品优惠码抵扣金额
              item.isUseDiscountCode = false;
              item.level_discount = 0; // 商品等级优惠折扣金额
              configOption(item.product_id, item.config_options)
                .then((res) => {
                  item.info = res.data.data;
                  const son_previews = [];
                  if (res.data.data.other && res.data.data.other.son_previews) {
                    res.data.data.other.son_previews.forEach((item) => {
                      item.forEach((items) => {
                        son_previews.push(items);
                      });
                    });
                  }
                  item.preview = res.data.data.preview.concat(son_previews);
                  if (res.data.data.discount) {
                    item.price = (Number(res.data.data.price) + Number(res.data.data.discount)).toFixed(2); // 商品价格
                    item.level_discount = Number(res.data.data.discount)
                    item.hasCalc = false // 只计算了单个价格的折扣，页面需要乘以数量
                  } else {
                    item.price = Number(res.data.data.price); // 商品价格
                    item.hasCalc = true
                  }
                  if (this.isShowLevel && !res.data.data.discount) {
                    // 开启了等级优惠
                    clientLevelAmount({
                      id: item.product_id,
                      amount: item.price * item.qty,
                    })
                      .then((ress) => {
                        if (ress.data.status === 200) {
                          item.level_discount = Number(ress.data.data.discount);
                          this.showGoodsList = [...arr];
                        }
                      })
                      .catch(() => {
                        this.showGoodsList = [...arr];
                      });
                  }
                  if (this.isShowPromo && item.customfield.promo_code) {
                    // 更新优惠码
                    applyPromoCode({
                      // 开启了优惠券
                      scene: "new",
                      product_id: item.product_id,
                      amount: item.price,
                      billing_cycle_time: item.info.duration,
                      promo_code: item.customfield.promo_code,
                      qty: item.qty,
                    })
                      .then((resss) => {
                        item.isUseDiscountCode = true;
                        this.isUseDiscountCode = true;
                        item.code_discount = Number(resss.data.data.discount);
                        this.showGoodsList = [...arr];
                      })
                      .catch((err) => {
                        this.$message.error(err.data.msg);
                        this.showGoodsList = [...arr];
                      });
                  }
                  item.isLoading = false;
                })
                .catch(() => {
                  item.preview = [];
                  this.showGoodsList = [...arr];
                })
                .finally(() => {
                  item.isLoading = false;
                  this.showGoodsList = [...arr];
                });
            });
          });
        },
        goPay() {
          if (!this.checked) {
            this.$message.warning(lang.shoppingCar_tip_text6);
            return;
          }
          this.subBtnLoading = true;
          if (this.isFormShoppingCart) {
            cart_settle({
              positions: this.selectGoodsList,
              customfield: { voucher_get_id: this.cashObj.id },
            })
              .then((res) => {
                this.$refs.payDialog.showPayDialog(
                  res.data.data.order_id,
                  res.data.data.amount,
                  this.payType
                );
              })
              .catch((err) => {
                this.$message.error(err.data.msg);
              })
              .finally(() => {
                this.subBtnLoading = false;
              });
          } else {
            product_settle({
              product_id: this.productObj.product_id,
              config_options: this.productObj.config_options,
              customfield: this.productObj.customfield,
              qty: this.productObj.qty,
            })
              .then((res) => {
                this.$refs.payDialog.showPayDialog(
                  res.data.data.order_id,
                  0,
                  this.payType
                );
              })
              .catch((err) => {
                this.$message.error(err.data.msg);
              })
              .finally(() => {
                this.subBtnLoading = false;
              });
          }
        },
        useCash(val) {
          this.cashObj = val;
          this.cashPrice = Number(val.price);
          this.productObj.customfield.voucher_get_id = val.id;
        },
        // 支付成功回调
        paySuccess(e) {
          location.href = "./finance.htm";
        },
        // 取消支付回调
        payCancel(e) {
          location.href = "./finance.htm";
        },
        // 续费移除代金券
        reRemoveCashCode() {
          this.$refs.cashRef.closePopver();
          this.cashObj = {};
          this.cashPrice = 0;
          this.productObj.customfield.voucher_get_id = "";
        },
        getPayLisy() {
          payLisy().then((res) => {
            this.payTypeList = res.data.data.list;
            this.payType = res.data.data.list[0].name;
          });
        },
        // 解析url
        getQuery(url) {
          const str = url.substr(url.indexOf("?") + 1);
          const arr = str.split("&");
          const res = {};
          for (let i = 0; i < arr.length; i++) {
            const item = arr[i].split("=");
            res[item[0]] = item[1];
          }
          return res;
        },
        goTermsServiceUrl() {
          window.open(this.commonData.terms_service_url);
        },
        goTermsPrivacUrl() {
          window.open(this.commonData.terms_privacy_url);
        },
        goHelpUrl(url) {
          window.open(this.commonData[url]);
        },

        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          document.title =
            this.commonData.website_name + "-" + lang.shoppingCar_tip_text7;
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
