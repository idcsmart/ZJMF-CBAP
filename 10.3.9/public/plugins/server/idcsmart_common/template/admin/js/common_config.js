const template = document.getElementsByClassName("common_config")[0];
Vue.prototype.lang = window.lang;
new Vue({
  data() {
    return {
      id: "",
      tabs: "basic", // basic,cost,config,custom
      hover: true,
      tableLayout: false,
      dataLoading: false,
      delVisible: false,
      delId: "",
      delType: "",
      delTit: lang.sureDelete,
      submitLoading: false,
      payType: "", // 计费方式 free , onetime, recurring_prepayment , recurring_postpaid
      currency_prefix:
        JSON.parse(localStorage.getItem("common_set")).currency_prefix || "¥",
      optType: "add", // 新增/编辑
      subOpt: "add", // 子项编辑状态
      comTitle: "",
      // 整个页面
      commonConfig: {
        order_page_description: "",
        allow_qty: 0,
        auto_support: 0,
        server_id: "",
      },
      childInterface: [],
      commonConfigoption: [],
      module_meta: {},
      pricing: {},
      // 默认周期
      defaultCycle: [],
      dataForm: {},
      dataRules: {
        onetime: [
          { required: true, message: lang.input + lang.money, type: "error" },
          {
            pattern: /^\d+(\.\d{0,2})?$/,
            message: lang.verify5,
            type: "warning",
          },
          {
            validator: (val) => val >= 0 && val <= 99999999.99,
            message: lang.verify5 + "，" + lang.money_ver,
            type: "warning",
          },
        ],
        server_id: [
          {
            required: true,
            message: `${lang.select}${lang.child_interface}`,
            type: "error",
          },
        ],
      },
      /* 周期表格 */
      defaultBol: false,
      cycleModel: false,
      defaultName: {
        monthly: lang.month,
        quarterly: lang.quarter,
        semaiannually: lang.half_year_fee,
        annually: lang.year_fee,
        biennially: lang.two_year,
        triennianlly: lang.three_year,
      },
      cycleForm: {
        name: "",
        cycle_time: "",
        cycle_unit: "hour",
        amount: null,
        price_factor: null,
      },
      cycleTime: [
        {
          value: "hour",
          label: lang.hour,
        },
        {
          value: "day",
          label: lang.day,
        },
        {
          value: "month",
          label: lang.natural_month,
        },
      ],
      ratioModel: false,
      ratioData: [],
      ratioColumns: [
        {
          colKey: "name",
          title: lang.cycle_name,
          ellipsis: true,
        },
        {
          colKey: "unit",
          title: lang.cycle_time,
          ellipsis: true,
        },
        {
          colKey: "ratio",
          title: lang.mf_ratio,
          ellipsis: true,
        },
      ],
      loading: false,
      cycleData: [
        {
          id: 1,
          cycle_name: "测试",
          cycle_time: 20,
          price: 100,
          way: "day",
        },
      ],
      // 默认周期
      defaultColumns: [
        {
          colKey: "name",
          title: lang.cycle_name,
          ellipsis: true,
        },
        {
          colKey: "cycle_time",
          title: lang.cycle_time,
          ellipsis: true,
          ellipsis: true,
        },
        {
          colKey: "amount",
          title: lang.price,
          ellipsis: true,
        },
      ],
      cycleColumns: [
        {
          colKey: "name",
          title: lang.cycle_name,
          width: 180,
          ellipsis: true,
        },
        {
          colKey: "cycle_time",
          title: lang.cycle_time,
          ellipsis: true,
          width: 180,
          ellipsis: true,
        },
        // {
        //   colKey: "price_factor",
        //   title: lang.price_factor,
        //   ellipsis: true,
        // },
        {
          colKey: "amount",
          title: lang.price,
          ellipsis: true,
        },
        {
          colKey: "ratio",
          title: lang.cycle_ratio,
          ellipsis: true,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 110,
        },
      ],
      cycleRules: {
        name: [
          {
            required: true,
            message: lang.input + lang.cycle_name,
            type: "error",
          },
          {
            validator: (val) => val?.length <= 20,
            message: lang.verify8 + "1-20",
            type: "warning",
          },
        ],
        cycle_time: [
          {
            required: true,
            message: lang.input + lang.cycle_time,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + lang.verify16,
            type: "warning",
          },
          {
            validator: (val) => val > 0,
            message: lang.input + lang.verify16,
            type: "warning",
          },
        ],
        amount: [
          { required: true, message: lang.input + lang.money, type: "error" },
          {
            pattern: /^\d+(\.\d{0,2})?$/,
            message: lang.verify5,
            type: "warning",
          },
          {
            validator: (val) => val >= 0 && val <= 99999999.99,
            message: lang.verify5 + "，" + lang.money_ver,
            type: "warning",
          },
        ],
      },

      /* 配置选项 */
      configData: [],
      configModel: false,
      configLoading: false,
      configColumns: [
        {
          colKey: "id",
          title: "ID",
          width: 160,
          ellipsis: true,
        },
        {
          colKey: "option_name",
          title: lang.config_name,
          ellipsis: true,
        },
        {
          colKey: "option_type",
          title: lang.type,
          ellipsis: true,
          width: 180,
        },
        {
          colKey: "hidden",
          title: lang.is_show_pro,
          width: 180,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 110,
        },
      ],
      /* 配置详情和子项 */
      configOption: [
        {
          value: "os",
          label: lang.configOption.os,
        },
        {
          value: "select",
          label: lang.configOption.select,
        },
        {
          value: "multi_select",
          label: lang.configOption.multi_select,
        },
        {
          value: "radio",
          label: lang.configOption.radio,
        },
        {
          value: "quantity",
          label: lang.configOption.quantity,
        },
        {
          value: "quantity_range",
          label: lang.configOption.quantity_range,
        },
        {
          value: "yes_no",
          label: lang.configOption.yes_no,
        },
        {
          value: "area",
          label: lang.configOption.area,
        },
      ],
      // freeType
      freeType: [
        {
          value: "stage",
          label: lang.stage,
        },
        {
          value: "qty",
          label: lang.qty_charging,
        },
      ],
      configDetail: {
        // 单个配置详情
        option_name: "",
        option_type: "",
        unit: "",
        option_param: "",
        description: "",
        hidden: 0,
        // 数量输入/拖动才有
        fee_type: "",
        allow_repeat: 0,
        max_repeat: "",
      },
      backupConfig: {}, // 备份配置详情
      // 子配置项数据
      subTit: "",
      configSub: [],
      configSubModel: false,
      configSubForm: {
        option_name: "",
        option_param: "",
        // 默认周期
        onetime: "",
        monthly: "-1",
        quarterly: "-1",
        semaiannually: "-1",
        annually: "-1",
        biennially: "-1",
        triennianlly: "-1",
        custom_cycle: [], // 自定义周期
        // type : quantity,quantity_range
        qty_min: "",
        qty_max: "",
        // type: area
        country: "",
      },
      countryList: [],
      subRules: {
        // 子配置验证
        country: [
          {
            required: true,
            message: lang.select + lang.country,
            type: "error",
          },
        ],
        option_name: [
          {
            required: true,
            message: lang.select + lang.option_value,
            type: "error",
          },
        ],
        qty_min: [
          { required: true, message: lang.input + lang.verify7, type: "error" },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + lang.verify7,
            type: "warning",
          },
          {
            validator: (val) => val >= 0,
            message: lang.input + lang.verify7,
            type: "warning",
          },
        ],
        qty_max: [
          { required: true, message: lang.input + lang.verify7, type: "error" },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + lang.verify7,
            type: "warning",
          },
          {
            validator: (val) => val >= 0,
            message: lang.input + lang.verify7,
            type: "warning",
          },
        ],
        amount: [
          { required: true, message: lang.input + lang.money, type: "error" },
          {
            pattern: /^\d+(\.\d{0,2})?$/,
            message: lang.verify5,
            type: "warning",
          },
          {
            validator: (val) => val >= 0,
            message: lang.verify5,
            type: "warning",
          },
        ],
      },
      configRules: {
        option_name: [
          {
            required: true,
            message: lang.input + lang.option_name,
            type: "error",
          },
          {
            validator: (val) => val?.length <= 20,
            message: lang.verify8 + "1-20",
            type: "warning",
          },
        ],
        option_type: [
          {
            required: true,
            message: lang.select + lang.option_type,
            type: "error",
          },
        ],
        fee_type: [
          {
            required: true,
            message: lang.select + lang.cost_type,
            type: "error",
          },
        ],
        amount: [
          { required: true, message: lang.input + lang.money, type: "error" },
          {
            pattern: /^\d+(\.\d{0,2})?$/,
            message: lang.verify5,
            type: "warning",
          },
          {
            validator: (val) => val >= 0,
            message: lang.verify5,
            type: "warning",
          },
        ],
      },
      subColumns: [
        {
          colKey: "option_name",
          title: "title",
        },
        {
          colKey: "area",
          title: "title-slot-area",
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 100,
        },
      ],

      // 自定义字段
      customModel: false,
      configOptionValue: [],
      customForm: {},
      customColumns: [
        {
          colKey: "id",
          title: "ID",
          width: 160,
          ellipsis: true,
        },
        {
          colKey: "name",
          title: lang.fields_name,
          ellipsis: true,
          width: 180,
        },
        {
          colKey: "type",
          title: lang.type,
          ellipsis: true,
        },
        {
          colKey: "hidden",
          title: lang.hidden,
          width: 180,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 110,
        },
      ],
    };
  },
  watch: {
    "commonConfig.onetime": {
      immediate: true,
      handler(val) {
        if (val) {
          this.commonConfig.onetime = val === "-1.00" ? "" : val;
        }
      },
    },
  },
  created() {
    this.id = location.href.split("?")[1].split("=")[1];
    // 详情
    this.getProDetail();
    // 配置
    this.getConfig();
    this.getCountryList();
    this.getChildInterfaceList();
  },
  methods: {
    // 切换tab
    changeTab() {},
    async getChildInterfaceList() {
      try {
        const res = await getChildInterface();
        this.childInterface = res.data.data.list;
      } catch (error) {}
    },
    serverIdChange(id) {
      if (id) {
        getChildModuleParams({ product_id: this.id, server_id: id }).then(
          (res) => {
            this.commonConfigoption = res.data.data.configoption.map(
              (item, index) => {
                const objK = Object.keys(this.configOptionValue);
                item.default = this.configOptionValue[objK[index]];
                return item;
              }
            );
            this.module_meta = res.data.data.module_meta;
          }
        );
      }
    },

    changeOnetime(val) {
      console.log(val);
    },
    async getCountryList() {
      try {
        const res = await getCountry();
        this.countryList = res.data.data.list;
      } catch (error) {}
    },
    // 获取商品详情
    async getProDetail() {
      try {
        this.dataLoading = true;
        this.defaultCycle = [];
        const res = await getProductInfo(this.id);
        const temp = res.data.data;
        this.commonConfig = temp.common_product; // 基本信息
        this.configOptionValue = temp.config_option;
        this.commonConfig.server_id = this.commonConfig.server_id || "";
        this.serverIdChange(this.commonConfig.server_id);
        this.$set(this.commonConfig, "onetime", temp.pricing.onetime);
        this.pricing = temp.pricing; // 默认周期
        this.payType = temp.pay_type; // 付费类型
        this.cycleData = temp.custom_cycle; // 自定义周期
        const arr = [];
        Object.keys(temp.pricing).forEach((item, index) => {
          if (item !== "onetime") {
            arr.push({
              id: index,
              name: this.defaultName[item],
              cycle_time: this.defaultName[item],
              amount: temp.pricing[item],
              cycle_name: item,
            });
          }
        });
        this.defaultCycle = arr;
        this.dataLoading = false;
      } catch (error) {
        this.dataLoading = false;
      }
    },
    // 提交页面配置
    async submitConfig() {
      try {
        const { order_page_description, allow_qty, auto_support, server_id } =
          this.commonConfig;
        const temp = this.defaultCycle.reduce((all, cur) => {
          all[cur.cycle_name] = cur.amount;
          return all;
        }, {});
        const pricing = {
          onetime: this.commonConfig.onetime,
          ...temp,
        };
        const params = {
          product_id: this.id,
          order_page_description,
          allow_qty,
          auto_support,
          pricing,
          server_id,
          configoption: this.commonConfigoption.map((item) => {
            return item.default;
          }),
        };

        this.submitLoading = true;
        const res = await saveProductInfo(params);
        if (res.data.status === 200) {
          this.$message.success(res.data.msg);
          this.submitLoading = false;
          this.getProDetail();
        }
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    closeData() {
      this.dataModel = false;
      this.lineModel = false;
      this.lineType = "";
    },
    closeSubData() {
      this.configSubModel = false;
    },
    /* 周期相关 */
    async changeRadio() {
      try {
        const res = await getDurationRatio({
          product_id: this.id,
        });
        this.ratioData = res.data.data.list.map((item) => {
          item.ratio = item.ratio ? item.ratio * 1 : null;
          return item;
        });
        this.ratioModel = true;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    async saveRatio() {
      try {
        const isAll = this.ratioData.every((item) => item.ratio);
        if (!isAll) {
          return this.$message.error(`${lang.input}${lang.mf_ratio}`);
        }
        const temp = JSON.parse(JSON.stringify(this.ratioData)).reduce(
          (all, cur) => {
            all[cur.id] = cur.ratio;
            return all;
          },
          {}
        );
        const params = {
          product_id: this.id,
          ratio: temp,
        };
        this.submitLoading = true;
        const res = await saveDurationRatio(params);
        this.submitLoading = false;
        this.ratioModel = false;
        this.$message.success(res.data.msg);
        this.getProDetail();
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    addCycle() {
      this.optType = "add";
      this.comTitle = lang.add_cycle;
      this.cycleForm.name = "";
      this.cycleForm.cycle_time = "";
      this.cycleForm.cycle_unit = "hour";
      this.cycleForm.amount = "";
      this.cycleModel = true;
    },
    editCycle(row) {
      this.optType = "update";
      this.comTitle = lang.update + lang.cycle;
      this.cycleForm = { ...row };
      this.cycleModel = true;
    },
    async submitCycle({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.cycleForm));
          params.product_id = this.id;
          if (this.optType === "add") {
            delete params.id;
          }
          this.submitLoading = true;
          const res = await addAndUpdateProCycle(this.optType, params);
          this.$message.success(res.data.msg);
          this.getProDetail();
          this.cycleModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },
    // 删除周期
    async deleteCycle() {
      try {
        const res = await deleteProCycle({
          product_id: this.id,
          id: this.delId,
        });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getProDetail();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    formatPrice(val) {
      return (val * 1).toFixed(2);
    },
    // 全局确认是否处于编辑默认周期
    checkEdit() {
      if (this.defaultBol) {
      }
    },
    // 修改默认周期
    changeDefault() {
      if (this.defaultBol) {
        this.submitConfig();
      }
      this.defaultBol = !this.defaultBol;
    },
    /* 自定义字段 */
    addCustom() {
      this.optType = "add";
      this.comTitle = lang.add + lang.custom_fields;
      this.customModel = true;
    },
    submitCustom() {},

    /* 配置选项 */
    async getConfig() {
      try {
        this.configLoading = true;
        const res = await getConfigoption({
          product_id: this.id,
        });
        if (res.data.status === 200) {
          this.configData = res.data.data.configoption;
          this.configLoading = false;
        }
      } catch (error) {
        this.configLoading = false;
      }
    },
    // 切换显示隐藏
    async changeHidden(row) {
      try {
        const { product_id, id, hidden } = row;
        const params = { product_id, id, hidden };
        const res = await changeConfigoption(params);
        if (res.data.status === 200) {
          this.$message.success(res.data.msg);
          this.getConfig();
        }
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 删除配置
    async deleteConfig() {
      try {
        const res = await deleteConfigoption({
          product_id: this.id,
          id: this.delId,
        });
        if (res.data.status === 200) {
          this.$message.success(res.data.msg);
          this.getConfig();
          this.delVisible = false;
        }
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 新增/编辑配置项
    addConfig() {
      this.optType = "add";
      this.configModel = true;
      this.comTitle = lang.create + lang.config_option;
      this.configDetail.id = "";
      this.configDetail.option_name = "";
      this.configDetail.option_type = "";
      this.configDetail.unit = "";
      this.configDetail.option_param = "";
      this.configDetail.description = "";
      this.configDetail.hidden = 0;
      this.configDetail.fee_type = "";
      this.configDetail.allow_repeat = 0;
      this.configDetail.max_repeat = "";
    },
    closeConfig() {
      this.configModel = false;
      this.getConfig();
    },
    editConfig(row) {
      this.optType = "update";
      this.comTitle = lang.update + lang.config_option;
      this.configModel = true;
      this.getConfigDetail(row.id);
    },
    // 新增/编辑配置项
    async submitConfigDetail({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.configDetail));
          params.product_id = this.id;
          if (this.optType === "add") {
            delete params.id;
            delete params.qty_min;
            delete params.qty_max;
            delete params.order;
          }
          this.submitLoading = true;
          const res = await addAndUpdateConfigoption(this.optType, params);
          this.$message.success(res.data.msg);
          // 提交过后拉取配置详情
          this.getConfigDetail(res.data.data?.id || this.configDetail.id);
          this.submitLoading = false;
          this.optType = "update";
        } catch (error) {
          console.log(error);
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },
    async getConfigDetail(id) {
      try {
        const res = await getConfigoptionDetail({
          product_id: this.id,
          id,
        });
        this.configDetail = res.data.data.configoption;
        this.backupConfig = JSON.parse(JSON.stringify(this.configDetail));
        this.configSub = res.data.data.configoption_sub;
      } catch (error) {
        console.log(error);
      }
    },
    /* 添加/编辑配置子项 */
    addConfigSub() {
      this.subOpt = "add";
      this.configSubModel = true;
      this.configSubForm.custom_cycle = JSON.parse(
        JSON.stringify(this.cycleData)
      ).map((item) => {
        item.amount = "";
        return item;
      });
      this.subTit = lang.add;
      this.configSubForm.option_name = "";
      this.configSubForm.option_param = "";
      this.configSubForm.onetime = "";
      this.configSubForm.qty_min = "";
      this.configSubForm.qty_max = "";
      this.configSubForm.country = "";
    },
    async editSub(row) {
      this.subOpt = "update";
      this.configSubModel = true;
      this.subTit = lang.update;
      try {
        const res = await getConfigSubDetail({
          product_id: this.configDetail.id,
          id: row.id,
        });
        this.configSubForm = res.data.data.configoption_sub;
      } catch (error) {}
    },
    async submitConfigSub({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.configSubForm));
          params.product_id = this.configDetail.id;
          if (this.subOpt === "add") {
            delete params.id;
          }
          // quantity,quantity_range
          const _type = this.configDetail.option_type;
          if (_type === "quantity" || _type === "quantity_range") {
            delete params.option_name;
          } else {
            delete params.qty_min;
            delete params.qty_max;
          }
          params.custom_cycle = params.custom_cycle.reduce((all, cur) => {
            all[cur.id] = cur.amount;
            return all;
          }, {});
          this.submitLoading = true;
          const res = await addAndUpdateConfigSub(this.subOpt, params);
          this.$message.success(res.data.msg);
          // 提交过后拉取配置详情
          this.getConfigDetail(this.configDetail.id);
          this.configSubModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
        this.$message.warning(firstError);
      }
    },
    async autoFill(name, data) {
      try {
        const price = JSON.parse(JSON.stringify(data)).reduce((all, cur) => {
          if (cur.amount) {
            all[cur.id] = cur.amount;
          }
          return all;
        }, {});
        const params = {
          product_id: this.id,
          price,
        };
        const res = await fillDurationRatio(params);
        const fillPrice = res.data.data.list;
        this[name].custom_cycle = this[name].custom_cycle.map((item) => {
          item.amount = fillPrice[item.id];
          return item;
        });
      } catch (error) {
        console.log('@@@@', error)
        this.$message.error(error.data.msg);
      }
    },
    // 修改首个套餐
    changeMonth1(val, item) {
      if ((typeof val === "string" && isNaN(val * 1)) || val * 1 < 0) {
        return false;
      }
      if (item) {
        // 失去焦点格式化价格
        let index = this.cycleData.findIndex((el) => el.id === item.id);
        this.configSubForm.custom_cycle[index].amount = (val * 1).toFixed(2);
        return false;
      }
      // 自动生成价格  月价格 * （月价格 / 周期价格）
      let temp = JSON.parse(JSON.stringify(this.configSubForm.custom_cycle));
      const curPrice = temp[0].amount;
      temp = temp.map((item) => {
        // 当首个周期为0的时候，全都为0
        if (this.cycleData[0].amount * 1 === 0) {
          if (item.id !== this.cycleData[0].id) {
            item.amount = "0.00";
          }
        } else {
          if (item.id !== this.cycleData[0].id) {
            const curId = this.cycleData.filter((ele) => item.id === ele.id);
            if (curId[0].amount * 1 === 0) {
              item.amount = "0.00";
            } else {
              item.amount = (
                curPrice *
                (((curId[0].amount * 1) / this.cycleData[0].amount) * 1)
              ).toFixed(2);
            }
          }
        }
        return item;
      });
      this.configSubForm.custom_cycle = temp;
    },
    // 删除配置子项
    async deleteSub() {
      try {
        const params = {
          configoption_id: this.configDetail.id,
          id: this.delId,
        };
        const res = await deleteConfigSub(params);
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getConfigDetail(this.configDetail.id);
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    /* 通用删除按钮 */
    comDel(type, row) {
      console.log("first", row);
      this.delId = row.id;
      if (type === "cycle") {
        this.delTit = lang.sure_del_cycle;
      }
      this.delType = type;
      this.delVisible = true;
    },
    // 通用删除
    sureDelete() {
      switch (this.delType) {
        case "cycle":
          return this.deleteCycle();
        case "config":
          return this.deleteConfig();
        case "sub":
          return this.deleteSub();
        default:
          return null;
      }
    },
  },
}).$mount(template);
