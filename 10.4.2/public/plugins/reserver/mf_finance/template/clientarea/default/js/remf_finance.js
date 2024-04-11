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
    this.id = this.getQuery("id");

    this.isUpdate = this.getQuery("change");
    this.getCommonData();
    this.getGoodsName();
    // 回显配置

    const temp =
      JSON.parse(sessionStorage.getItem("product_information")) || {};
    if (this.isUpdate && temp.config_options) {
      this.backfill = temp.config_options;
      this.configForm.config_options = temp.config_options;
      this.customfield = temp.customfield;
      this.cycle = temp.config_options.cycle;
      this.orderData.qty = temp.qty;
      this.position = temp.position;
      this.cascaderParams = temp.customfield.cascaderParams;
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
      isUpdate: "",
      addons_js_arr: [], // 插件数组
      isShowPromo: false, // 是否开启优惠码
      isShowLevel: false, // 是否开启等级优惠
      isShowFull: false,
      isUseDiscountCode: false, // 是否使用优惠码
      backfill: {}, // 回填参数
      customfield: {}, // 自定义字段
      submitLoading: false,
      eventData: {
        id: "",
        discount: 0,
      },
      commonData: {},
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
      totalPrice: 0,
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
      tit: "",
      curSystem: "",
      systemArr: [],
      passwordRules: {},
      detailProduct: {}, // 商品基础配置
      shouHost: false,
      shouPassword: false,
      /* custom_fields */
      custom_fields: [],
      customObj: {},
      /* 级联 */
      cascaderObj: {},
      init: true,
      curCasId: "",
      curIndex: "",
      cascaderSon: {}, // 级联拉取的相关子项的数据
      hasCascader: false,
      cascaderNum: 0, // 级联配置项的个数
      pagaeLoading: false,
      cascaderParams: {},
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
        this.pagaeLoading = true;
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
        this.custom_cycles = temp.product.cycle;
        this.cycle = this.custom_cycles[0].billingcycle;
        this.passwordRules = temp.product.password.rule;
        this.cascaderNum = temp.option.filter(
          (item) => item.option_type === 20
        ).length;
        // 过滤掉没有子项 + 数量类型最大值是0的数据 + 去除 option_type = 20 的，需要拉取接口保存最后一级的配置
        const numArr = [4, 7, 9, 11, 14, 15, 16, 17, 18, 19];
        this.configoptions = temp.option
          .filter((item) => item.sub)
          .filter((item) => {
            if (numArr.includes(item.option_type)) {
              return item.sub[0].qty_maximum > 0;
            } else {
              return true;
            }
          })
          .map((item) => {
            if (item.option_type === 20) {
              this.hasCascader = true;
              // 有层级联动，需要等待拉取数据过后才计算价格，虽然配置不影响价格，但是右侧的预览项需要
              this.$set(this.cascaderObj, item.id, { id: item.sub[0]?.id });
              const cid = item.id;
              const sub_id = this.isUpdate
                ? this.cascaderParams[cid]
                : item.sub[0]?.id;
              this.getCascaderList(cid, sub_id).then((res) => {
                item.sonData = res;
                this.cascaderSon[item.id] = res || [];
                if (
                  this.hasCascader &&
                  this.cascaderNum === Object.keys(this.cascaderSon).length
                ) {
                  this.changeConfig(this.backfill.cycle ? true : false);
                }
              });
            }
            return item;
          });
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
        this.configForm = obj;
        if (!this.hasCascader) {
          this.changeConfig(this.backfill.cycle ? true : false);
        }
        /* custom_fields  不参与计算价格 */
      } catch (error) {
        console.log("@error", error);
      }
    },
    changeObj(target) {
      for (let i = 1, j = arguments.length; i < j; i++) {
        let source = arguments[i] || {}; // 拿到对象
        for (let prop in source) {
          // 遍历对象，拿到对象自己的 key value，重新组装到target对象中
          if (source.hasOwnProperty(prop)) {
            let value = source[prop];
            if (value !== undefined) {
              target[prop] = value;
            }
          }
        }
      }

      return target;
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
    async getCascaderList(cid, sub_id) {
      // 拉取级联数据
      try {
        const res = await getCascader({
          id: this.id,
          cid,
          sub_id,
        });
        // 存储 cid ， sub_id 方便购物车回填
        this.cascaderParams[cid] = sub_id;
        const temp = res.data.data[0]?.son?.reduce((all, cur) => {
          all[cur.id] = cur.checkSubId * 1;
          return all;
        }, {});
        this.$set(this.cascaderObj[cid], "son", temp);
        return res.data.data[0]?.son;
      } catch (error) {
        console.log("error", error);
        this.$message.error(error.data.msg);
      }
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
        const temp = this.formatData(false);
        const params = {
          id: this.id,
          config_options: {
            configoption: temp.config_options.configoption,
            cycle: this.cycle,
            promo_code: this.customfield.promo_code,
            event_promotion: this.customfield.event_promotion,
          },
          qty: this.orderData.qty,
        };
        this.pagaeLoading = false;
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
        this.init = false;
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
    cascaderClick(cid, sub_id, el) {
      this.configForm[el.id] = sub_id;
      this.cascaderObj[cid].id = sub_id;
      this.configoptions = JSON.parse(JSON.stringify(this.configoptions)).map(
        (item) => {
          if (item.id === cid) {
            this.getCascaderList(cid, sub_id).then((res) => {
              item.sonData = res;
              this.cascaderSon[el.id] = res;
              this.changeConfig();
            });
          }
          return item;
        }
      );
    },
    cascaderSonClick(cid, sub_id, el, ind) {
      if (this.cascaderObj[cid] === el.id) {
        return;
      }
      this.configoptions = JSON.parse(JSON.stringify(this.configoptions)).map(
        (item) => {
          if (item.id === cid) {
            this.getCascaderList(cid, el.id).then((res) => {
              item.sonData = res;
              this.cascaderSon[cid] = res;
              this.changeConfig();
            });
          }
          return item;
        }
      );
    },
    // 切换数量
    changeNum(val, item) {
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
    formatData(bol = true) {
      const temp = JSON.parse(JSON.stringify(this.configForm));
      // 有级联的时候需要拼接子项参数
      if (Object.keys(this.cascaderSon).length) {
        Object.keys(this.cascaderSon).forEach((item) => {
          this.cascaderSon[item].forEach((el) => {
            temp[el.id] = el.checkSubId * 1;
          });
        });
      }
      const params = {
        position: this.position,
        product_id: this.id,
        config_options: {
          configoption: temp,
          cycle: this.cycle,
          host: temp.host,
          password: temp.password,
        },
        qty: this.orderData.qty,
        customfield: {
          ...this.customfield,
          curSystem: this.curSystem,
          cascaderParams: this.cascaderParams,
        },
        self_defined_field: this.customObj,
      };
      if (!this.isUpdate) {
        delete params.position;
      }
      if (!this.shouHost) {
        delete params.config_options.host;
      }
      if (!this.shouPassword) {
        delete params.config_options.password;
      }
      if (bol) {
        if (!this.verifyCustomFiled()) {
          return false;
        }
      }
      return params;
    },
    // 立即购买
    async buyNow(e) {
      if (e.data && e.data.type !== "iframeBuy") {
        return;
      }
      if (!this.verifyCustomFiled()) {
        return;
      }
      const params = this.formatData();
      if (!params) {
        return;
      }
      if (e.data && e.data.type === "iframeBuy") {
        const postObj = { type: "iframeBuy", params, price: this.totalPrice };
        window.parent.postMessage(postObj, "*");
        return;
      }
      location.href = `/cart/settlement.htm?id=${params.product_id}`;
      sessionStorage.setItem("product_information", JSON.stringify(params));
    },

    // 加入购物车
    async addCart() {
      try {
        const params = this.formatData();
        if (!params) {
          return;
        }
        if (!this.verifyCustomFiled()) {
          return;
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
        console.log("error", error);
        this.$message.error(error.data.msg);
      }
    },
    // 修改购物车
    async changeCart() {
      try {
        const params = this.formatData();
        if (!params) {
          return;
        }
        if (!this.verifyCustomFiled()) {
          return;
        }
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
