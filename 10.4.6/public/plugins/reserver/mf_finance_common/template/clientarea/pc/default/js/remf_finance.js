const template = document.getElementsByClassName("common-config")[0];
Vue.prototype.lang = Object.assign(window.lang, window.module_lang);
new Vue({
  components: {
    asideMenu,
    topMenu,
    payDialog,
    eventCode,
    discountCode,
  },
  created() {
    if (window.performance.navigation.type === 2) {
      sessionStorage.removeItem("product_information");
    }
    let temp = {};
    const params = getUrlParams();
    this.id = params.id;
    if (params.config || sessionStorage.getItem("product_information")) {
      try {
        temp = JSON.parse(params.config);
        this.isUpdate = true;
        this.isConfig = true;
      } catch (e) {
        temp = JSON.parse(sessionStorage.getItem("product_information")) || {};
        this.isUpdate = params.change;
      }
    }

    // 回显配置

    if (this.isUpdate && temp.config_options) {
      this.backfill = temp.config_options;
      this.configForm.config_options = temp.config_options;
      this.customfield = temp.customfield;
      this.cycle = temp.config_options.cycle;
      this.orderData.qty = temp.qty;
      this.position = temp.position;
      this.customObj = temp.config_options.customfield;
    }
    const self_defined_field = temp.self_defined_field || {};
    this.getCustomFields(self_defined_field);
    this.getCommonData();
    this.getGoodsName();
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
    this.getConfig();
    window.addEventListener("message", (event) => this.buyNow(event));
  },
  updated() {
    // 关闭loading
    document.getElementById("mainLoading").style.display = "none";
    document.getElementsByClassName("template")[0].style.display = "block";
    this.isShowBtn = true;
  },
  destroyed() {},
  computed: {
    calcDes() {
      return (val) => {
        const temp = val
          .replace(/&lt;/g, "<")
          .replace(/&gt;/g, ">")
          .replace(/&quot;/g, '"')
          .replace(/&/g, "&")
          .replace(/"/g, '"')
          .replace(/'/g, "'");
        return temp;
      };
    },
    calcSwitch() {
      return (item, type) => {
        if (type) {
          const arr = item.subs.filter(
            (item) => item.option_name === lang.com_config.yes
          );
          return arr[0]?.id;
        } else {
          const arr = item.subs.filter(
            (item) => item.option_name === lang.com_config.no
          );
          return arr[0]?.id;
        }
      };
    },
    calcCountry() {
      return (val) => {
        return this.countryList.filter((item) => val === item.iso)[0]?.name_zh;
      };
    },
    calcCity() {
      return (id) => {
        return this.filterCountry[id].filter(
          (item) => item[0]?.country === this.curCountry[id]
        )[0];
      };
    },
    calcUnit() {
      return (item) => {
        switch (item.option_type) {
          case 11:
          case 18:
            return "Mbps";
          case 4:
          case 15:
            return lang.mf_one;
          case 7:
          case 16:
            return lang.mf_cores;
          case 9:
          case 14:
          case 17:
          case 19:
            return "GB";
        }
      };
    },
    calcSystem() {
      return (item) => {
        const temp = item.sub[this.curSystem].child;
        return temp;
      };
    },
    // 处理自定义下拉选项
    calcOption() {
      return (item) => {
        return item.split(",");
      };
    },
  },
  data() {
    return {
      id: "",
      position: "",
      isUpdate: false,
      isConfig: false,
      addons_js_arr: [], // 插件数组
      isShowPromo: false, // 是否开启优惠码
      isShowLevel: false, // 是否开启等级优惠
      isShowFull: false,
      isUseDiscountCode: false, // 是否使用优惠码
      backfill: {}, // 回填参数
      customfield: {}, // 自定义字段
      submitLoading: false,
      commonData: {},
      tit: "",
      eventData: {
        id: "",
        discount: 0,
      },
      // 订单数据
      orderData: {
        qty: 1,
        // 是否勾选阅读
        isRead: false,
        // 付款周期
        duration: "",
      },
      // 右侧展示区域
      showInfo: [],
      base_price: "",
      // 商品原单价
      onePrice: 0,
      // 商品原总价
      original_price: 0,

      timerId: null, // 订单id
      basicInfo: {
        pay_type: "",
        name: "",
      }, // 基础信息
      configoptions: [], // 配置项
      custom_cycles: [], // 自定义周期
      curCycle: 0,
      cycle: "",
      onetime: "",
      pay_type: "",
      // 提交数据
      configForm: {
        // 自定义配置项
      },
      isShowBtn: false,
      // 国家列表
      countryList: [],
      // 处理过后的国家列表
      filterCountry: {},
      curCountry: {}, // 当前国家，根据配置id存入对应的初始索引
      cartDialog: false,
      dataLoading: false,
      // 客户等级折扣金额
      clDiscount: 0,
      // 优惠码折扣金额
      code_discount: 0,
      totalPrice: 0,
      curSystem: "",
      systemArr: [],
      passwordRules: {},
      detailProduct: {}, // 商品基础配置
      shouHost: false,
      shouPassword: false,
      /* custom_fields */
      custom_fields: [],
      customObj: {},
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
  methods: {
    // 解析url
    getQuery(name) {
      const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
      const r = window.location.search.substr(1).match(reg);
      if (r != null) return decodeURI(r[2]);
      return null;
    },
    async getConfig() {
      try {
        // 商品详情
        const des = await getReDetails(this.id);
        this.basicInfo.name = des.data.data.product.name;
        this.basicInfo.pay_type = des.data.data.product.pay_type;
        // 初始化自定义配置参数
        const res = await getCommonDetail(this.id);
        const temp = res.data;
        this.shouHost = temp.product.host.show === "1";
        this.shouPassword = temp.product.password.show === "1";
        this.basicInfo.allow_qty = temp.allow_qty;
        this.detailProduct = temp.product;
        this.configoptions = temp.option.filter((item) => item.sub);
        const obj = this.configoptions.reduce((all, cur) => {
          if (cur.option_type === 3) {
            // switch
            all[cur.id] = 0;
          } else if (
            cur.option_type === 4 ||
            cur.option_type === 7 ||
            cur.option_type === 9 ||
            cur.option_type === 11 ||
            cur.option_type === 14 ||
            cur.option_type === 15 ||
            cur.option_type === 16 ||
            cur.option_type === 17 ||
            cur.option_type === 18 ||
            cur.option_type === 19
          ) {
            // 数量
            all[cur.id] = cur.qty_minimum * 1;
          } else if (cur.option_type === 5) {
            // 操作系统
            this.curSystem = Object.keys(cur.sub)[0];
            this.systemArr = Object.keys(cur.sub).reduce((all, cur) => {
              all.push({
                value: cur,
                label: cur,
              });
              return all;
            }, []);
            all[cur.id] = cur.sub[this.curSystem].child[0].id;
          } else if (cur.option_type === 12) {
            // 区域
            all[cur.id] = cur.sub[0].area[0]?.id;
          } else {
            all[cur.id] = cur.sub[0].id;
          }
          return all;
        }, {});
        obj.host = temp.product.host.host;
        obj.password = temp.product.password.password;
        this.passwordRules = temp.product.password.rule;
        this.configForm = obj;
        /* 处理周期 */
        // if (this.basicInfo.pay_type === 'onetime') {
        //   this.cycle = 'onetime'
        //   this.onetime = temp.pricing.onetime
        // } else if (this.pay_type === 'free') {
        //   this.cycle = 'free'
        // } else {
        //   this.custom_cycles = temp.product.cycle
        //   this.cycle = this.custom_cycles[0].billingcycle
        // }
        this.custom_cycles = temp.product.cycle;
        this.cycle = this.custom_cycles[0].billingcycle;

        this.changeConfig(this.backfill.cycle ? true : false);
      } catch (error) {
        console.log("@error", error);
      }
    },
    getCustomFields(data) {
      const obj = {};
      customFieldsProduct(this.id).then((res) => {
        this.custom_fields = res.data.data.data.map((item) => {
          obj[item.id] = "";
          if (
            Object.keys(data).length > 0 &&
            (data[item.id] !== undefined || data[item.id] !== null)
          ) {
            obj[item.id] = data[item.id];
          } else {
            if (item.field_type === "tickbox") {
              obj[item.id] = item.is_required === 1 ? "1" : "0";
            }
          }
          return item;
        });
        this.$set(this, "customObj", obj);
      });
    },
    changeSystem(item) {
      this.configForm[item.id] = item.sub[this.curSystem].child[0]?.id;
      this.changeConfig();
    },
    refreshPassword() {
      this.configForm.password = genEnCode(
        this.passwordRules.len_num * 1,
        this.passwordRules.num * 1,
        this.passwordRules.upper * 1,
        this.passwordRules.special * 1
      );
    },
    // 数组转树
    toTree(data) {
      var temp = Object.values(
        data.reduce((res, item) => {
          res[item.country]
            ? res[item.country].push(item)
            : (res[item.country] = [item]);
          return res;
        }, {})
      );
      return temp;
    },
    // 切换配置选项
    changeItem() {
      this.changeConfig();
    },
    // 使用优惠码
    getDiscount(data) {
      this.customfield.promo_code = data[1];
      this.isUseDiscountCode = true;
      this.changeConfig();
    },
    eventChange(evetObj) {
      if (this.eventData.id !== evetObj.id) {
        this.eventData.id = evetObj.id || "";
        this.customfield.event_promotion = this.eventData.id;
        this.changeConfig();
      }
    },
    // 更改配置计算价格
    async changeConfig(bol = false) {
      try {
        if (bol) {
          /* 处理 quantity quantity_range  */
          const _temp = this.backfill.configoption;
          Object.keys(_temp).forEach((item) => {
            const type = this.configoptions.filter((el) => el.id === item)[0]
              ?.option_type;
            if (type === "quantity" || type === "quantity_range") {
              _temp[item] = _temp[item][0];
            }
          });
          this.configForm = _temp;
          this.cycle = this.backfill.cycle;
          this.curCycle = this.custom_cycles.findIndex(
            (item) => item.billingcycle === this.cycle
          );
          this.curSystem = this.customfield.curSystem;
        }
        const temp = this.formatData();
        const params = {
          id: this.id,
          config_options: {
            configoption: temp,
            cycle: this.cycle,
            promo_code: this.customfield.promo_code,
            event_promotion: this.customfield.event_promotion,
          },
          qty: this.orderData.qty,
        };
        this.dataLoading = true;
        const res = await calcPrice(params);

        this.original_price = res.data.data.price * 1;
        this.totalPrice = res.data.data.price_total * 1;
        this.clDiscount = res.data.data.price_client_level_discount * 1 || 0;
        this.code_discount = res.data.data.price_promo_code_discount * 1 || 0;
        this.eventData.discount =
          res.data.data.price_event_promotion_discount * 1 || 0;

        this.base_price = res.data.data.base_price;
        this.showInfo = res.data.data.preview;
        this.onePrice = res.data.data.price; // 原单价
        this.orderData.duration = res.data.data.duration;

        // 重新计算周期显示
        const result = await calculate(this.id);
        this.custom_cycles = result.data.data.duration;
        this.dataLoading = false;
      } catch (error) {
        console.log("@@@@", error);
        this.dataLoading = false;
      }
    },
    removeDiscountCode() {
      this.isUseDiscountCode = false;
      this.customfield.promo_code = "";
      this.code_discount = 0;
      this.changeConfig();
    },
    // 切换数据中心
    changeArea(id, el) {
      this.configForm[id] = el.area[0].id;
      this.changeConfig();
    },
    // 切换国家
    changeCountry(id, index) {
      this.$set(this.curCountry, id, index);
      this.configForm[id] = this.filterCountry[id][index][0]?.id;
      this.changeConfig();
    },
    // 切换城市
    changeCity(el, id) {
      this.configForm[id] = el.id;
      this.changeConfig();
    },
    // 切换单击选择
    changeClick(id, el) {
      if (this.configForm[id] === el.id) {
        return;
      }
      this.configForm[id] = el.id;
      this.changeConfig();
    },
    // 切换数量
    changeNum(val, item) {
      console.log("@@@", item);
      let temp = 0;
      if (val * 1 < item.qtyminimum * 1) {
        temp = item.qtyminimum * 1;
      } else if (val * 1 > item.qtymaximum * 1) {
        temp = item.qtymaximum * 1;
      } else {
        temp = val * 1;
      }
      if (isNaN(temp)) {
        temp = item.qtyminimum;
      }
      setTimeout(() => {
        this.configForm[item.id] = val * 1;
        console.log(23233223, this.configForm);
        this.changeConfig();
      });
    },
    // 切换周期
    changeCycle(item, index) {
      this.cycle = item.billingcycle;
      this.curCycle = index;
      this.changeConfig();
    },
    // 商品购买数量减少
    delQty() {
      if (this.basicInfo.allow_qty === 0) {
        return false;
      }
      if (this.orderData.qty > 1) {
        this.orderData.qty--;
        this.changeConfig();
      }
    },
    // 商品购买数量增加
    addQty() {
      if (this.basicInfo.allow_qty === 0) {
        return false;
      }
      if (
        this.detailProduct.stock_control &&
        this.orderData.qty >= this.detailProduct.qty
      ) {
        return false;
      }
      this.orderData.qty++;
      this.changeConfig();
    },

    formatData() {
      // 之前是把数量类型转换为数组，现在不需要
      const temp = JSON.parse(JSON.stringify(this.configForm));
      Object.keys(temp).forEach((item) => {
        if (typeof temp[item] === "object") {
          temp[item] = temp[item][temp[item].length - 1];
        }
      });
      return temp;
    },
    // 立即购买
    async buyNow(e) {
      if (e.data && e.data.type !== "iframeBuy") {
        return;
      }
      if (
        Boolean(
          (JSON.parse(localStorage.getItem("common_set_before")) || {})
            .custom_fields?.before_settle === 1
        )
      ) {
        window.open("/account.htm");
        return;
      }

      if (!this.verifyCustomFiled()) {
        return;
      }
      const temp = this.formatData();
      const params = {
        product_id: this.id,
        config_options: {
          configoption: temp,
          cycle: this.cycle,
          host: temp.host,
          password: temp.password,
          customfield: this.customObj,
        },
        qty: this.orderData.qty,
        customfield: {
          ...this.customfield,
          curSystem: this.curSystem,
        },
        self_defined_field: this.customObj,
      };
      if (!this.shouHost) {
        delete params.config_options.host;
      }
      if (!this.shouPassword) {
        delete params.config_options.password;
      }

      if (e.data && e.data.type === "iframeBuy") {
        const postObj = { type: "iframeBuy", params, price: this.totalPrice };
        window.parent.postMessage(postObj, "*");
        return;
      }
      const enStr = encodeURI(JSON.stringify(params.config_options));
      // 直接传配置到结算页面

      // location.href = `/cart/settlement.htm?id=${params.product_id}&name=${this.basicInfo.name}&config_options=${enStr}&qty=${params.qty}`
      location.href = `/cart/settlement.htm?id=${params.product_id}`;
      sessionStorage.setItem("product_information", JSON.stringify(params));
    },
    /* 验证自定义字段必填和正则 */
    verifyCustomFiled() {
      try {
        const requireArr = this.custom_fields.filter(
          (item) =>
            item.is_required === 1 ||
            (item.is_required === 0 && this.customObj[item.id] !== "")
        );
        if (requireArr.length === 0) {
          return true;
        }
        const temp = requireArr.find((item) => this.customObj[item.id] === "");
        if (temp) {
          this.$message.error(`${temp.field_name}${lang.common_cloud_text295}`);
          return false;
        }
        const valItem = requireArr
          .filter((item) => item.regexpr || item.field_type === "link")
          .map((item) => {
            if (item.field_type === "link") {
              item.regexpr =
                "/^(((ht|f)tps?)://)?([^!@#$%^&*?.s-]([^!@#$%^&*?.s]{0,63}[^!@#$%^&*?.s])?.)+[a-z]{2,6}/?/";
            }
            return item;
          })
          .find(
            (item) =>
              !new RegExp(item.regexpr.replace(/^\/|\/$/g, "")).test(
                this.customObj[item.id]
              )
          );
        if (valItem) {
          this.$message.error(
            `${valItem.field_name}${lang.common_cloud_text296}`
          );
          return false;
        }
        return true;
      } catch (error) {
        console.log("error", error);
        return false;
      }
    },
    // 加入购物车
    async addCart() {
      try {
        if (!this.verifyCustomFiled()) {
          return;
        }
        const temp = this.formatData();
        const params = {
          product_id: this.id,
          config_options: {
            configoption: temp,
            cycle: this.cycle,
            host: temp.host,
            password: temp.password,
            customfield: this.customObj,
          },
          qty: this.orderData.qty,
          customfield: {
            ...this.customfield,
            curSystem: this.curSystem,
          },
          self_defined_field: this.customObj,
        };
        if (!this.shouHost) {
          delete params.config_options.host;
        }
        if (!this.shouPassword) {
          delete params.config_options.password;
        }

        const res = await addToCart(params);
        if (res.data.status === 200) {
          this.cartDialog = true;
          const result = await getCart();
          localStorage.setItem(
            "cartNum",
            "cartNum-" + result.data.data.list.length
          );
        }
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 修改购物车
    async changeCart() {
      try {
        if (!this.verifyCustomFiled()) {
          return;
        }
        const temp = this.formatData();
        const params = {
          position: this.position,
          product_id: this.id,
          config_options: {
            configoption: temp,
            cycle: this.cycle,
            host: temp.host,
            password: temp.password,
            customfield: this.customObj,
          },
          qty: this.orderData.qty,
          customfield: {
            ...this.customfield,
            curSystem: this.curSystem,
          },
        };
        if (!this.shouHost) {
          delete params.config_options.host;
        }
        if (!this.shouPassword) {
          delete params.config_options.password;
        }

        this.dataLoading = true;
        const res = await updateCart(params);
        this.$message.success(res.data.msg);
        setTimeout(() => {
          location.href = "/cart/shoppingCar.htm";
        }, 300);
        this.dataLoading = false;
      } catch (error) {
        console.log("errore", error);
        this.$message.error(error.data.msg);
      }
    },
    goToCart() {
      location.href = "/cart/shoppingCar.htm";
      this.cartDialog = false;
    },
    // 支付成功回调
    paySuccess(e) {
      this.submitLoading = false;
      location.href = "common_product_list.htm";
    },
    // 取消支付回调
    payCancel(e) {
      this.submitLoading = false;
      location.href = "finance.htm";
    },
    getGoodsName() {
      productInfo(this.id).then((res) => {
        this.tit = res.data.data.product.name;
        document.title =
          this.commonData.website_name + "-" + res.data.data.product.name;
      });
    },
    // 获取通用配置
    getCommonData() {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"));
    },
  },
}).$mount(template);
