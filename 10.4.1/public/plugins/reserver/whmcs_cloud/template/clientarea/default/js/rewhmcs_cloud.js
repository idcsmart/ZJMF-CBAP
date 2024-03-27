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
    // this.id = location.href.split('?')[1].split('=')[1]?.split('&')[0]
    this.id = this.getQuery("id");

    this.isUpdate = this.getQuery("change");
    this.getCommonData();
    this.getGoodsName();

    // 回显配置
    //const temp = this.getQuery(location.search)

    const temp =
      JSON.parse(sessionStorage.getItem("product_information")) || {};
    if (this.isUpdate && temp.config_options) {
      this.backfill = temp.config_options;
      this.configForm.config_options = temp.config_options;
      this.customfield = temp.customfield;
      this.cycle = temp.config_options.cycle;
      this.orderData.qty = temp.qty;
      this.position = temp.position;
    }
    const self_defined_field = temp.self_defined_field || {};
    this.getCustomFields(self_defined_field);
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
    // 处理自定义下拉选项
    calcOption() {
      return (item) => {
        return item.split(",");
      };
    },
  },
  data() {
    return {
      isUpdate: false,
      id: "",
      tit: "",
      position: "",
      addons_js_arr: [], // 插件数组
      isShowPromo: false, // 是否开启优惠码
      isShowLevel: false, // 是否开启等级优惠
      isShowFull: false,
      isUseDiscountCode: false, // 是否使用优惠码
      backfill: {}, // 回填参数
      customfield: {}, // 自定义字段
      submitLoading: false,
      commonData: {},
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
        const temp = res.data.data;
        this.configoptions = temp.configoption.filter(
          (item) => item.options.length
        );
        const obj = this.configoptions.reduce((all, cur) => {
          all[cur.id] =
            cur.optiontype === "3"
              ? 1
              : cur.optiontype === "4"
              ? cur.qtyminimum * 1
              : cur.options[0].id;
          return all;
        }, {});
        this.configForm = obj;
        /* 处理周期 */
        this.custom_cycles = temp.cycle;
        this.cycle = temp.cycle[0];

        this.changeConfig(this.backfill.cycle ? true : false);
      } catch (error) {}
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
    eventChange(evetObj) {
      if (this.eventData.id !== evetObj.id) {
        this.eventData.id = evetObj.id || "";
        this.customfield.event_promotion = this.eventData.id;
        this.changeConfig();
      }
    },
    // 使用优惠码
    getDiscount(data) {
      this.customfield.promo_code = data[1];
      this.isUseDiscountCode = true;
      this.changeConfig();
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
            (item) => item.id * 1 === this.cycle * 1
          );
        }
        const temp = this.formatData(false);
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
        const result = await calculate(params);
        this.custom_cycles = result.data.data.price;
        this.dataLoading = false;
      } catch (error) {
        this.dataLoading = false;
      }
    },
    removeDiscountCode() {
      this.isUseDiscountCode = false;
      this.customfield.promo_code = "";
      this.code_discount = 0;
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
      this.cycle = item.name;
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
      this.orderData.qty++;
      this.changeConfig();
    },

    formatData() {
      // 之前是把数量类型转换为数组，现在不需要
      const temp = JSON.parse(JSON.stringify(this.configForm));
      // Object.keys(temp).forEach(el => {
      //   const arr = this.configoptions.filter(item => item.id * 1 === el * 1)
      //   if (arr[0].option_type === 'quantity' || arr[0].option_type === 'quantity_range' || arr[0].option_type === 'multi_select') {
      //     if (typeof (temp[el]) !== 'object') {
      //       temp[el] = [temp[el]]
      //     }
      //   }
      // })
      return temp;
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
    // 立即购买
    async buyNow(e) {
      if (e.data && e.data.type !== "iframeBuy") {
        return;
      }
      // 直接传配置到结算页面
      if (!this.verifyCustomFiled()) {
        return;
      }
      const temp = this.formatData();
      const params = {
        product_id: this.id,
        config_options: {
          configoption: temp,
          cycle: this.cycle,
          customfield: this.customObj,
        },
        qty: this.orderData.qty,
        customfield: this.customfield,
        self_defined_field: this.customObj,
      };
      if (e.data && e.data.type === "iframeBuy") {
        const postObj = { type: "iframeBuy", params, price: this.totalPrice };
        window.parent.postMessage(postObj, "*");
        return;
      }
      // location.href = `/cart/settlement.htm?id=${params.product_id}&name=${this.basicInfo.name}&config_options=${enStr}&qty=${params.qty}`
      location.href = `/cart/settlement.htm?id=${params.product_id}`;
      sessionStorage.setItem("product_information", JSON.stringify(params));
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
            customfield: this.customObj,
          },
          qty: this.orderData.qty,
          customfield: this.customfield,
          self_defined_field: this.customObj,
        };

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
        console.log("error", error);
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
            customfield: this.customObj,
          },
          qty: this.orderData.qty,
          customfield: this.customfield,
          self_defined_field: this.customObj,
        };
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
