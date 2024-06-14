const template = document.getElementById("product_detail_cloud");
Vue.prototype.lang = Object.assign(window.lang, window.module_lang);
new Vue({
  components: {
    asideMenu,
    topMenu,
    payDialog,
    pagination,
    discountCode,
    cashCoupon,
    safeConfirm,
  },
  created() {
    // 获取产品id
    this.id = location.href.split("?")[1].split("=")[1];

    // this.id = 5315
    // 获取通用信息
    this.getCommonData();
    // 获取产品详情
    this.getHostDetail();
    // 获取实例详情
    this.getCloudDetail();

    this.getCloudStatus();
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
      // 优惠码信息
      this.getPromoCode();
    }
    if (arr.includes("IdcsmartClientLevel")) {
      // 开启了等级优惠
      this.isShowLevel = true;
    }
    if (arr.includes("IdcsmartVoucher")) {
      // 开启了代金券
      this.isShowCash = true;
    }
    // 开启了插件才拉取接口
    // 退款相关
    arr.includes("IdcsmartRefund") && this.getRefundMsg();
    arr.includes("IdcsmartRenew") && this.getRenewStatus();
  },
  updated() {},
  computed: {
    calcPassword() {
      return (pas) => {
        return new Array(pas.length).fill("*").join("");
      };
    },
    calcMin() {
      return (item) => {
        if (item.option_type === 14) {
          return item.qty;
        } else {
          return item.qty_minimum;
        }
      };
    },
    calcDisable() {
      // 处理数据盘升降级不能选择的情况
      return (el, item) => {
        // if (item.option_type === 13) {
        //   return el.firstname * 1 < item.options.filter( o => o.id === this.configForm[item.id])[0]?.firstname * 1
        // } else {
        //   return false
        // }
        return false;
      };
    },
    calcValue() {
      return (item) => {
        const numType = [4, 7, 9, 11, 14, 15, 16, 17, 18, 19];
        if (numType.includes(item.option_type)) {
          return item.qty;
        } else {
          return item.suboption_name;
        }
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
    vpcIps() {
      if (
        this.vpc_ips.vpc2 !== undefined &&
        this.vpc_ips.vpc3 !== undefined &&
        this.vpc_ips.vpc4 !== undefined
      ) {
        const str =
          this.vpc_ips.vpc1.value +
          "." +
          this.vpc_ips.vpc2 +
          "." +
          this.vpc_ips.vpc3 +
          "." +
          this.vpc_ips.vpc4 +
          "/" +
          this.vpc_ips.vpc6.value;
        return str;
      } else {
        return "";
      }
    },
    calcCpu() {
      return this.params.cpu + lang.mf_cores;
    },
    calcCpuList() {
      // 根据区域来判断计算可选cpu数据
      if (this.activeName1 === "fast") {
        return;
      }
      const temp =
        this.configLimitList.filter(
          (item) =>
            item.type === "data_center" &&
            this.params.data_center_id === item.data_center_id
        ) || [];
      const cpu = temp.reduce((all, cur) => {
        all.push(...cur.cpu.split(","));
        return all;
      }, []);
      return this.cpuList.filter((item) => !cpu.includes(String(item.value)));
    },
    calaMemoryList() {
      // 计算可选内存，根据 cpu + 区域
      if (this.activeName1 === "fast") {
        return;
      }
      const temp = this.configLimitList.filter((item) =>
        item.cpu.split(",").includes(String(this.params.cpu))
      );
      if (temp.length === 0) {
        // 没有匹配到限制条件
        if (this.memoryList[0]?.type === "radio") {
          return this.memoryList;
        } else {
          this.memoryTip = this.createTip(this.memoryArr);
          this.memMarks = this.createMarks(this.memoryArr); // data 原数据，目标marks
          return this.memoryArr;
        }
      }
      // 分两种情况，单选和范围，单选：memory 范围，min_memory，max_memory
      if (temp[0].memory) {
        const memory = Array.from(
          new Set(
            temp.reduce((all, cur) => {
              all.push(...cur.memory.split(","));
              return all;
            }, [])
          )
        );
        const filMem = this.memoryList.filter(
          (item) => !memory.includes(String(item.value))
        );
        return filMem;
      } else {
        // 范围
        let fArr = [];
        temp.forEach((item) => {
          fArr.push(...this.createArr([item.min_memory, item.max_memory]));
        });
        fArr = Array.from(new Set(fArr));
        const filterArr = this.memoryArr.filter((item) => !fArr.includes(item));
        this.memoryTip = this.createTip(filterArr);
        this.memMarks = this.createMarks(filterArr); // data 原数据，目标marks
        return filterArr.filter((item) => !fArr.includes(item));
      }
    },
    calcPassword() {
      return (pas) => {
        return new Array(pas.length).fill("*").join("");
      };
    },
  },
  watch: {
    renewParams: {
      handler() {
        let n = 0;
        // l:当前周期的续费价格
        const l = this.hostData.renew_amount;
        if (this.isShowPromo && this.renewParams.customfield.promo_code) {
          // n: 算出来的价格
          n =
            (this.renewParams.base_price * 1000 -
              this.renewParams.clDiscount * 1000 -
              this.renewParams.code_discount * 1000) /
              1000 >
            0
              ? (this.renewParams.base_price * 1000 -
                  this.renewParams.clDiscount * 1000 -
                  this.renewParams.code_discount * 1000) /
                1000
              : 0;
        } else {
          //  n: 算出来的价格
          n =
            (this.renewParams.original_price * 1000 -
              this.renewParams.clDiscount * 1000 -
              this.renewParams.code_discount * 1000) /
              1000 >
            0
              ? (this.renewParams.original_price * 1000 -
                  this.renewParams.clDiscount * 1000 -
                  this.renewParams.code_discount * 1000) /
                1000
              : 0;
        }
        let t = n;
        // 如果当前周期和选择的周期相同，则和当前周期对比价格
        if (
          this.hostData.billing_cycle_time === this.renewParams.duration ||
          this.hostData.billing_cycle_name === this.renewParams.billing_cycle
        ) {
          // 谁大取谁
          t = n;
        }
        this.renewParams.totalPrice =
          t * 1000 - this.renewParams.cash_discount * 1000 > 0
            ? (
                (t * 1000 - this.renewParams.cash_discount * 1000) /
                1000
              ).toFixed(2)
            : 0;
      },
      immediate: true,
      deep: true,
    },
  },
  data() {
    return {
      chartData: [],
      main_ip: "",
      initLoading: true,
      reinstallLoading: false,
      commonData: {
        currency_prefix: "",
        currency_suffix: "",
      },
      self_defined_field: [],
      client_operate_password: "",
      activeName: "2",
      configLimitList: [], // 限制规则
      configObj: {},
      backup_config: [],
      snap_config: [],
      // 实例id
      id: null,
      // 产品id
      product_id: 0,
      // 实例状态
      status: "operating",
      // 实例状态描述
      statusText: "",
      cpu_realData: {},
      // 代金券对象
      cashObj: {},
      // 是否救援系统
      isRescue: false,
      // 是否开启代金券
      isShowCash: false,
      // 产品详情
      hostData: {
        billing_cycle_name: "",
        status: "Active",
        first_payment_amount: "",
        renew_amount: "",
      },
      // 实例详情
      cloudData: {
        data_center: {
          iso: "CN",
        },
        image: {
          icon: "",
        },
        package: {
          cpu: "",
          memory: "",
          out_bw: "",
          system_disk_size: "",
        },
        system_disk: {},
        iconName: "Windows",
      },
      // 是否显示支付信息
      isShowPayMsg: false,
      imgBaseUrl: "",
      // 是否显示添加备注弹窗
      isShowNotesDialog: false,
      // 备份输入框内容
      notesValue: "",
      // 显示重装系统弹窗
      isShowReinstallDialog: false,
      // 重装系统弹窗内容
      reinstallData: {
        image_id: null,
        password: null,
        ssh_key_id: null,
        port: null,
        osGroupId: null,
        osId: null,
        type: "pass",
      },
      client_area: [],
      // 镜像数据
      osData: {},
      // 镜像版本选择框数据
      osSelectData: [],
      // 镜像图片地址
      osIcon: "",
      // Shhkey列表
      sshKeyData: [],
      // 错误提示信息
      errText: "",
      // 镜像是否需要付费
      isPayImg: false,
      payMoney: 0,
      // 镜像优惠价格
      payDiscount: 0,
      // 镜像优惠码价格
      payCodePrice: 0,
      onOffvisible: false,
      rebotVisibel: false,
      codeString: "",
      isShowIp: false,
      renewLoading: false, // 续费计算折扣loading
      // 停用信息
      refundData: {},
      // 停用状态
      refundStatus: {
        Pending: lang.finance_text97,
        Suspending: lang.finance_text136,
        Suspend: lang.finance_text137,
        Suspended: lang.finance_text138,
        Refund: lang.finance_text139,
        Reject: lang.finance_text140,
        Cancelled: lang.finance_text141,
      },

      // 停用相关
      // 是否显示停用弹窗
      isShowRefund: false,
      // 停用页面信息
      refundPageData: {
        host: {
          create_time: 0,
          first_payment_amount: 0,
        },
        configs: [],
      },
      // 停用页面参数
      refundParams: {
        host_id: 0,
        suspend_reason: null,
        type: "Expire",
      },

      addons_js_arr: [], // 插件列表
      isShowPromo: false, // 是否开启优惠码
      isShowLevel: false, // 是否开启等级优惠
      // 续费
      // 显示续费弹窗
      isShowRenew: false, // 续费的总计loading
      renewBtnLoading: false, // 续费按钮的loading
      // 续费页面信息
      renewPageData: [],

      renewActiveId: "",
      renewOrderId: 0,
      isShowRefund: false,
      hostStatus: {
        Unpaid: {
          text: lang.order_text4,
          color: "#F64E60",
          bgColor: "#FFE2E5",
        },
        Pending: {
          text: lang.finance_text88,
          color: "#3699FF",
          bgColor: "#E1F0FF",
        },
        Active: {
          text: lang.finance_text142,
          color: "#1BC5BD",
          bgColor: "#C9F7F5",
        },
        Suspended: {
          text: lang.finance_text143,
          color: "#F99600",
          bgColor: "#FFE2E5",
        },
        Deleted: {
          text: lang.finance_text144,
          color: "#9696A3",
          bgColor: "#F2F2F7",
        },
        Failed: {
          text: lang.finance_text88,
          color: "#FFA800",
          bgColor: "#FFF4DE",
        },
      },
      isRead: false,
      isShowPass: false,
      passHidenCode: "",
      rescueStatusData: {},
      // 日志开始
      logDataList: [],
      logParams: {
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 0,
        orderby: "id",
        sort: "desc",
        keywords: "",
      },
      // 管理开始
      // 开关机状态
      powerStatus: "on",
      powerList: [],
      consoleList: [],
      loading1: false,
      loading2: false,
      loading3: false,
      loading4: false,
      loading5: false,
      loading6: false,
      loading7: false,
      loading8: false,
      ipValueData: [],
      // 重置密码弹窗数据
      rePassData: {
        password: "",
        checked: false,
      },
      // 是否展示重置密码弹窗
      isShowRePass: false,
      // 救援模式弹窗数据
      rescueData: {
        type: "1",
        //  password: ''
      },
      // 是否展示救援模式弹窗
      isShowRescue: false,
      // 是否展示退出救援模式弹窗
      isShowQuit: false,
      ipValue: null,

      // 续费参数
      renewParams: {
        id: 0, //默认选中的续费id
        isUseDiscountCode: false, // 是否使用优惠码
        customfield: {
          promo_code: "", // 优惠码
          voucher_get_id: "", // 代金券码
        },
        duration: "", // 周期
        billing_cycle: "", // 周期时间
        clDiscount: 0, // 用户等级折扣价
        cash_discount: 0, // 代金券折扣价
        code_discount: 0, // 优惠码折扣价
        original_price: 0, // 原价
        base_price: 0,
        totalPrice: 0, // 现价
      },

      logLoading: false,

      // 还原显示数据
      restoreData: {
        restoreId: 0,
        // 实例名称
        cloud_name: "",
        // 创建时间
        time: "",
      },
      // 是否显示删除快照弹窗
      isShowDelBs: false,
      // 删除显示数据
      delData: {
        delId: 0,
        // 实例名称
        cloud_name: "",
        // 创建时间
        time: "",
        // 快照名称
        name: "",
      },
      bsDataLoading: false,
      // 获取快照/备份升降级价格 参数 生成快照/备份数量升降级订单参数
      bsData: {
        id: 0,
        type: "",
        backNum: 0,
        snapNum: 0,
        money: 0,
        moneyDiscount: 0,
        codePrice: 0,
        duration: "月",
      },
      // 是否显示开启备份弹窗
      isShowOpenBs: false,
      // 快照备份订单id
      bsOrderId: 0,
      chartSelectValue: "1",
      // 统计图表开始
      echartLoading: false,
      echartLoading1: false,
      echartLoading2: false,
      echartLoading3: false,
      echartLoading4: false,
      isShowPowerChange: false,
      powerTitle: "",
      diskPriceLoading: false,
      ipPriceLoading: false,
      ipMoney: 0.0,
      ipDiscountkDisPrice: 0.0,
      ipCodePrice: 0.0,
      upgradePriceLoading: false,
      trueDiskLength: 0,
      isShowAutoRenew: false,
      vpcDataList: [],
      vpcLoading: false,
      vpcParams: {
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 200,
        orderby: "id",
        sort: "desc",
        keywords: "",
      },
      isShowengine: false,
      engineID: "",
      curEngineId: "",
      engineSearchLoading: false,
      productOptions: [],
      productParams: {
        page: 1,
        limit: 20,
        keywords: "",
        status: "Active",
        orderby: "id",
        sort: "desc",
        data_center_id: "",
      },
      isShowAddVpc: false,
      plan_way: 0,
      vpc_ips: {
        vpc1: {
          tips: lang.range1,
          value: 10,
          select: [10, 172, 192],
        },
        vpc2: 0,
        vpc3: 0,
        vpc3Tips: "",
        vpc4: 0,
        vpc4Tips: "",
        vpc6: {
          value: 16,
          select: [16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28],
        },
        min: 0,
        max: 255,
      },
      vpcName: "",
      ips: "",
      safeOptions: [],
      safeID: "",

      cpuName: "",
      memoryName: "",
      bwName: "",
      flowName: "",
      defenseName: "",
      calcIPList: [],
      memoryList: [],
      cpuList: [],
      memoryArr: [], // 范围时内存数组
      activeName1: "custom", // fast, custom
      memoryType: false,
      memoryTip: "",
      params: {
        // 配置参数
        data_center_id: "",
        cpu: "",
        memory: 1,
        image_id: 0,
        system_disk: {
          size: "",
          disk_type: "",
        },
        data_disk: [],
        backup_num: "",
        snap_num: "",
        line_id: "",
        bw: "",
        flow: "",
        peak_defence: "",
        ip_num: "",
        duration_id: "",
        network_type: "normal",
        // 提交购买
        name: "", // 主机名
        ssh_key_id: "",
        /* 安全组 */
        security_group_id: "",
        security_group_protocol: [],
        password: "",
        re_password: "",
        vpc: {
          // 新建-系统分配的时候都不传
          id: "", // 选择已有的vc
          ips: "", // 自定义的时候
        },
        notes: "",
      },
      lineDetail: {}, // 线路详情：bill_type, flow, bw, defence , ip
      in_info_tip: {},
      out_info_tip: {},
      consoleData: {},
      chartUnit: "",
      /* 升降级相关*/
      // 升降级套餐列表
      upgradeList: [],
      // 升降级表单
      upgradePackageId: "",
      // 当前切换的升降级套餐
      changeUpgradeData: {},
      // 是否展示升降级弹窗
      isShowUpgrade: false,
      // 升降级参数
      upParams: {
        customfield: {
          promo_code: "", // 优惠码
          voucher_get_id: "", // 代金券码
        },
        duration: "", // 周期
        isUseDiscountCode: false, // 是否使用优惠码
        clDiscount: 0, // 用户等级折扣价
        code_discount: 0, // 优惠码折扣价
        cash_discount: 0, // 代金券折扣价
        original_price: 0, // 原价
        totalPrice: 0, // 现价
      },
      /* 升降级 end */
      isShowPort: false,
      isShowChart: false,
      configoptions: [], // 配置项
      configForm: {}, // 自定义配置项
      configDetails: [], // 实例详情

      /* 升降级 */
      upgradeLoading: false,
      upLicenseDialogShow: false,
      selectUpIndex: 0,
      buy_id: "",
      upPriceLoading: false,
      licenseActive: "1",
      upData: {
        price: 0,
        clDiscount: 0,
        totalPrice: 0,
      },
      isShowUp: true,
      upBtnLoading: false,
      upgradeHost: {},
      upgradeConfig: [],
      upgradeSon_host: [],
      upgradeList: [],
      basicInfo: {},
      configForm: {},
      upSon: [],
      curCycle: 0,
      curCountry: {},
      firstInfo: [],
      filterCountry: {},
      isShowProUpdate: false, // 展示产品升级
      isShowOptionUpdate: false, // 展示配置升级
      updateProId: "", // 升级产品时新的商品id
      backupNum: 0,
      snapNum: 0,
      upLoading: false,
      // filterCountry: [],
      /* 升降级 end */
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
    // 返回剩余到期时间
    formateDueDay(time) {
      return Math.floor((time * 1000 - Date.now()) / (1000 * 60 * 60 * 24));
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
  methods: {
    /* 升降级 */
    handelUpLicense(val) {
      if (!this.isShowProUpdate && !this.isShowOptionUpdate) {
        return;
      }
      if (this.upgradeLoading) return;

      this.upgradeLoading = true;
      if (this.isShowProUpdate) {
        this.licenseActive = "1";
      } else {
        this.licenseActive = "2";
      }
      this.selectUpIndex = 0;
      this.$message({
        showClose: true,
        message: lang.common_cloud_text54,
        type: "warning",
        duration: 10000,
      });
      this.handleTabClick({ name: this.licenseActive });
      this.curCycle = 0;
    },
    handleTabClick(e) {
      this.selectUpIndex = 0;
      const upApi = this.buy_id ? upAppPage : upgradePage;
      const configApi = this.buy_id ? upgradeAppPage : upgradeConfigPage;
      const id = this.buy_id ? this.buy_host_id : this.id;
      this.removeUpDiscountCode(false);
      this.reRemoveCashCode();
      if (e.name === "1") {
        // 产品升降级
        this.isShowUp = true;
        upApi(id)
          .then((res) => {
            this.upgradeList = res.data.data.host;
            this.upgradeHost = res.data.data.host;
            this.upgradeConfig = res.data.data.configoptions;
            this.upgradeSon_host = res.data.data.son_host;
            this.upgradeLoading = false;
            this.getConfig();
            this.upLicenseDialogShow = true;
          })
          .catch((err) => {
            console.log("error", err);
            this.$message.warning(err.data && err.data.msg);
            this.upgradeLoading = false;
          });
      } else {
        // 配置升降级
        configApi(this.id)
          .then((res) => {
            this.upgradeList = res.data.data.host;
            this.upgradeLoading = false;
            this.getConfig();
            this.upLicenseDialogShow = true;
          })
          .catch((err) => {
            this.$message.warning(err.data && err.data.msg);
            this.upgradeLoading = false;
          });
      }
    },
    // 更改授权数量拖动
    changeQuantity(val, i) {
      if (i.son_product_id > 0) {
        return;
      }
      let num1 = val * 1;
      let step = i.subs[0]?.qty_change || 1;
      if (num1 % step !== 0) {
        num1 = parseInt(num1 / step) * step;
      }
      this.configForm[i.id] = [num1];
      const fatherArr = this.configoptions.filter((item) => {
        if (
          item.son_product_id === 0 &&
          (item.option_type === "quantity_range" ||
            item.option_type === "quantity")
        ) {
          return item;
        }
      });
      let num = 0;
      const fatherId = fatherArr.map((item) => {
        return item.id;
      });
      fatherId.forEach((item) => {
        num = this.configForm[item][0]
          ? this.configForm[item][0] * 1 + num
          : this.configForm[item] * 1 + num;
      });
      const arr = this.configoptions.filter((item) => item.son_product_id > 0);
      const arr1 = arr.map((item) => {
        return item.id;
      });

      arr1.forEach((item) => {
        this.configForm[item] = [num];
      });
      this.changeConfig();
    },
    // 升降级使用优惠码
    getUpDiscount(data) {
      this.upParams.customfield.promo_code = data[1];
      this.upParams.isUseDiscountCode = true;
      this.upParams.code_discount = Number(data[0]);
      this.changeConfig();
    },
    // 移除升降级优惠码
    removeUpDiscountCode(bol = true) {
      this.upParams.isUseDiscountCode = false;
      this.upParams.customfield.promo_code = "";
      this.upParams.code_discount = 0;
      if (bol) {
        this.changeConfig();
      }
    },
    // 升降级使用代金券
    upUseCash(val) {
      this.cashObj = val;
      const price = val.price ? Number(val.price) : 0;
      this.upParams.cash_discount = price;
      this.upParams.customfield.voucher_get_id = val.id;
      this.changeConfig();
    },

    // 升降级移除代金券
    upRemoveCashCode() {
      this.$refs.cashRef && this.$refs.cashRef.closePopver();
      this.cashObj = {};
      this.upParams.cash_discount = 0;
      this.upParams.customfield.voucher_get_id = "";
      this.upParams.totalPrice =
        (this.upParams.original_price * 1000 -
          this.upParams.clDiscount * 1000 -
          this.upParams.cash_discount * 1000 -
          this.upParams.code_discount * 1000) /
          1000 >
        0
          ? (
              (this.upParams.original_price * 1000 -
                this.upParams.cash_discount * 1000 -
                this.upParams.clDiscount * 1000 -
                this.upParams.code_discount * 1000) /
              1000
            ).toFixed(2)
          : 0;
    },
    // 升降级点击
    showUpgrade() {
      this.getLineDetails();
      this.isShowUpgrade = true;
      this.$message({
        showClose: true,
        message: lang.common_cloud_text54,
        type: "warning",
        duration: 10000,
      });
    },
    // 关闭升降级弹窗
    upgradeDgClose() {
      this.upLicenseDialogShow = false;
      this.removeUpDiscountCode(false);
      this.reRemoveCashCode();
    },
    // 获取升降级价格
    getCycleList() {
      this.upgradePriceLoading = true;
      const params = {
        id: this.id,
        cpu: this.params.cpu,
        memory: this.params.memory,
        bw: this.params.bw,
        flow: this.params.flow,
        peak_defence: this.params.peak_defence,
      };
      upgradePackagePrice(params)
        .then(async (res) => {
          if (res.data.status == 200) {
            let price = res.data.data.price; // 当前产品的价格
            if (price < 0) {
              this.upParams.original_price = 0;
              this.upParams.totalPrice = 0;
              this.upgradePriceLoading = false;
              return;
            }
            this.upParams.original_price = price;
            this.upParams.totalPrice = price;
            // 开启了等级优惠
            if (this.isShowLevel) {
              await clientLevelAmount({ id: this.product_id, amount: price })
                .then((ress) => {
                  this.upParams.clDiscount = Number(ress.data.data.discount);
                })
                .catch(() => {
                  this.upParams.clDiscount = 0;
                });
            }
            // 开启了优惠码插件
            if (this.isShowPromo) {
              // 更新优惠码
              await applyPromoCode({
                // 开启了优惠券
                scene: "upgrade",
                product_id: this.product_id,
                amount: price,
                billing_cycle_time: this.hostData.billing_cycle_time,
                promo_code: this.upParams.customfield.promo_code,
                host_id: this.id,
              })
                .then((resss) => {
                  this.upParams.isUseDiscountCode = true;
                  this.upParams.code_discount = Number(
                    resss.data.data.discount
                  );
                })
                .catch((err) => {
                  this.upParams.isUseDiscountCode = false;
                  this.upParams.customfield.promo_code = "";
                  this.upParams.code_discount = 0;
                  this.$message.error(err.data.msg);
                });
            }
            this.upParams.totalPrice =
              (price * 1000 -
                this.upParams.clDiscount * 1000 -
                this.upParams.cash_discount * 1000 -
                this.upParams.code_discount * 1000) /
                1000 >
              0
                ? (
                    (price * 1000 -
                      this.upParams.cash_discount * 1000 -
                      this.upParams.clDiscount * 1000 -
                      this.upParams.code_discount * 1000) /
                    1000
                  ).toFixed(2)
                : 0;
            this.upgradePriceLoading = false;
          } else {
            this.upParams.original_price = 0;
            this.upParams.clDiscount = 0;
            this.upParams.isUseDiscountCode = false;
            this.upParams.customfield.promo_code = "";
            this.upParams.code_discount = 0;
            this.upParams.totalPrice = 0;
            this.upgradePriceLoading = false;
          }
        })
        .catch((error) => {
          this.upParams.original_price = 0;
          this.upParams.clDiscount = 0;
          this.upParams.isUseDiscountCode = false;
          this.upParams.customfield.promo_code = "";
          this.upParams.code_discount = 0;
          this.upParams.totalPrice = 0;
          this.upgradePriceLoading = false;
        });
    },
    // 升降级提交
    upgradeSub() {
      const params = {
        id: this.id,
        customfield: {
          promo_code: this.upParams.customfield.promo_code,
          voucher_get_id: this.upParams.customfield.voucher_get_id,
        },
      };
      const type =
        this.licenseActive === "1" ? "upgrade_product" : "upgrade_config";
      this.upLoading = true;
      upgradeOrder(type, params)
        .then((res) => {
          if (res.data.status === 200) {
            this.$message.success(lang.common_cloud_text56);
            this.isShowUpgrade = false;
            const orderId = res.data.data.id;
            this.upLicenseDialogShow = false;
            this.upLoading = false;
            // 调支付弹窗
            this.$refs.topPayDialog.showPayDialog(orderId, 0);
          } else {
            this.upLoading = false;
            this.$message.error(err.data.msg);
          }
        })
        .catch((err) => {
          this.upLoading = false;
          this.$message.error(err.data.msg);
        });
    },
    // 升降级弹窗 套餐选择框变化
    upgradeSelectChange(e) {
      this.upgradeList.map((item) => {
        if (item.id == e) {
          // 获取当前套餐的周期
          let duration = this.cloudData.duration;
          // 该周期新套餐的价格
          let money = item[duration];
          switch (duration) {
            case "month_fee":
              duration = lang.appstore_text54;
              break;
            case "quarter_fee":
              duration = lang.appstore_text55;
              break;
            case "year_fee":
              duration = lang.appstore_text57;
              break;
            case "two_year":
              duration = lang.biennially;
              break;
            case "three_year":
              duration = lang.triennially;
              break;
            case "onetime_fee":
              duration = lang.onetime;
              break;
          }
          this.changeUpgradeData = {
            id: item.id,
            money,
            duration,
            description: item.description,
          };
        }
      });
      this.reRemoveCashCode();
      this.changeConfig();
    },
    qtyChangeNum(val, item) {
      let num1 = val * 1;
      let step = item.subs[0]?.qty_change || 1;
      if (num1 % step !== 0) {
        num1 = parseInt(num1 / step) * step;
      }
      this.configForm[item.id] = [num1];
      const fatherArr = this.configoptions.filter((item) => {
        if (
          item.son_product_id === 0 &&
          (item.option_type === "quantity_range" ||
            item.option_type === "quantity")
        ) {
          return item;
        }
      });
      let num = 0;
      const fatherId = fatherArr.map((item) => {
        return item.id;
      });
      fatherId.forEach((item) => {
        num = this.configForm[item][0]
          ? this.configForm[item][0] * 1 + num
          : this.configForm[item] * 1 + num;
      });
      const arr = this.configoptions.filter((item) => item.son_product_id > 0);
      const arr1 = arr.map((item) => {
        return item.id;
      });
      arr1.forEach((item) => {
        this.configForm[item] = [num];
      });
      setTimeout(() => {
        this.changeConfig();
      }, 300);
    },
    // 切换数量
    changeNum(val, item) {
      let num1 = val.target.value * 1;
      let step = item.subs[0]?.qty_change || 1;
      if (num1 % step !== 0) {
        num1 = parseInt(num1 / step) * step;
      }
      this.configForm[item.id] = [num1];
      const fatherArr = this.configoptions.filter((item) => {
        if (
          item.option_type === "quantity_range" ||
          item.option_type === "quantity"
        ) {
          return item;
        }
      });
      let num = 0;
      const fatherId = fatherArr.map((item) => {
        return item.id;
      });
      fatherId.forEach((item) => {
        num = this.configForm[item][0]
          ? this.configForm[item][0] * 1 + num
          : this.configForm[item] * 1 + num;
      });
      let arr = [];
      this.upSon.forEach((item) => {
        arr = item.configoptions.filter((items) => {
          if (
            item.basicInfo.configoption_id > 0 &&
            (items.option_type === "quantity_range" ||
              items.option_type === "quantity")
          ) {
            return item;
          }
        });
      });
      const arr1 = arr.map((item) => {
        return item.id;
      });
      arr1.forEach((item) => {
        this.sonConfigForm[0][item] = [num];
      });
      setTimeout(() => {
        if (this.upLicenseDialogShow) {
          this.changeConfig();
        } else {
          this.changeSonConfig();
        }
      }, 300);
    },
    // 切换子商品数量
    changeSonNum(val, item) {
      let num = val * 1;
      let step = item.subs[0]?.qty_change || 1;
      if (num % step !== 0) {
        num = parseInt(num / step) * step;
      }
      this.sonConfigForm[item.id] = [num];
      setTimeout(() => {
        if (this.upLicenseDialogShow) {
          this.changeConfig();
        } else {
          this.changeSonConfig();
        }
      }, 300);
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
      if (this.upLicenseDialogShow) {
        this.changeConfig();
      } else {
        this.changeSonConfig();
      }
    },
    // 父商品数据输入
    fatherChange(val, i) {
      let inputNum = val * 1;
      if (i.subs && i.subs[0]) {
        let step = i.subs[0]?.qty_change || 1;
        if (inputNum % step !== 0) {
          inputNum = parseInt(inputNum / step) * step;
        }
        this.configForm[i.id] = [inputNum];
      }
      const fatherArr = this.configoptions.filter((item) => {
        if (
          item.option_type === "quantity_range" ||
          item.option_type === "quantity"
        ) {
          return item;
        }
      });
      let num = 0;
      const fatherId = fatherArr.map((item) => {
        return item.id;
      });
      fatherId.forEach((item) => {
        num = this.configForm[item][0]
          ? this.configForm[item][0] * 1 + num
          : this.configForm[item] * 1 + num;
      });
      let arr = [];
      this.upSon.forEach((item) => {
        arr = item.configoptions.filter((items) => {
          if (
            item.basicInfo.configoption_id > 0 &&
            (items.option_type === "quantity_range" ||
              items.option_type === "quantity")
          ) {
            return item;
          }
        });
      });
      const arr1 = arr.map((item) => {
        return item.id;
      });
      arr1.forEach((item) => {
        this.sonConfigForm[0][item] = [num];
      });
      this.changeConfig();
    },
    // 切换配置选项
    changeItem() {
      if (this.upLicenseDialogShow) {
        this.changeConfig();
      } else {
        this.changeSonConfig();
      }
    },
    async getConfig() {
      this.upSon = [];
      this.buySonData = [];
      this.sonCurCycle = [];
      this.sonCountry = [];
      this.sonConfigForm = [];
      this.sonCycle = [];
      this.sonCurCountry = [];
      try {
        const tabVal = this.licenseActive;
        if (tabVal === "1") {
          this.cycle =
            this.upgradeList[this.selectUpIndex]?.cycle[0]?.billingcycle;
          this.updateProId = this.upgradeList[this.selectUpIndex]?.pid;
        } else {
          const temp = JSON.parse(JSON.stringify(this.upgradeList));
          this.configoptions = temp;
          // 初始化自定义配置参数
          const numType = [4, 7, 9, 11, 14, 15, 16, 17, 18, 19];
          const obj = this.upgradeList.reduce((all, cur) => {
            all[cur.id] = numType.includes(cur.option_type)
              ? cur.qty
              : cur.subid;
            return all;
          }, {});
          this.backups = JSON.parse(JSON.stringify(obj));
          this.configForm = obj;
        }
        this.changeConfig();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 回填处理id
    backfillId(type, id) {
      const temp = this.upgradeConfig.filter((item) => item.id === id);
      if (type === "id") {
        return temp[0]?.configoption_sub_id;
      } else if (type === "quantity_range") {
        return [temp[0]?.qty];
      } else {
        return temp[0]?.qty;
      }
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
    // 切换周期
    changeCycle(item, index) {
      this.cycle = item.billingcycle;
      this.curCycle = index;

      if (
        this.basicInfo.pay_type === "recurring_prepayment" ||
        this.basicInfo.pay_type === "recurring_postpaid"
      ) {
        this.upSon.forEach((el) => {
          this.sonCycle = [];
          this.sonCurCycle = [];
          this.sonCycle.push(el.custom_cycles[index].id);
          this.sonCurCycle.push(index);
        });
      }
      this.changeConfig();
    },
    // 更改配置计算价格
    async changeConfig() {
      const tabVal = this.licenseActive;
      this.upPriceLoading = true;
      try {
        let res = {};
        const temp = this.formatData();
        const sonParams = [];
        // 先提交页面 remf_finance/:id/upgrade_product
        let price = 0;
        this.upgradePriceLoading = true;
        if (tabVal === "1") {
          // 升级产品
          // 提交配置计算价格
          const res = await saveUpgradeHost({
            id: this.id,
            product_id: this.updateProId,
            cycle: this.cycle,
          });
          price = res.data.data.price; // 当前产品的价格
        } else {
          // 升级配置
          const temp1 = this.formatData();
          const params = {
            id: this.id,
            configoption: temp1,
          };
          res = await saveUpgradeCnfig(params);
          price = res.data.data.price;
        }
        if (price < 0) {
          this.upParams.original_price = 0;
          this.upParams.totalPrice = 0;
          this.upgradePriceLoading = false;
          return;
        }
        this.upParams.original_price = price;
        this.upParams.totalPrice = price;
        if (this.isShowLevel) {
          // 计算折扣金额
          await clientLevelAmount({
            id: tabVal === "1" ? this.updateProId : this.product_id,
            amount: price,
          })
            .then((ress) => {
              this.upParams.clDiscount = Number(ress.data.data.discount);
            })
            .catch(() => {
              this.upParams.clDiscount = 0;
            });
        }
        // 开启了优惠码插件
        if (this.isShowPromo) {
          // 更新优惠码
          await applyPromoCode({
            // 开启了优惠券
            scene: "upgrade",
            product_id: this.product_id,
            amount: price,
            billing_cycle_time: this.hostData.billing_cycle_time,
            promo_code: this.upParams.customfield.promo_code,
            host_id: this.id,
          })
            .then((resss) => {
              this.upParams.isUseDiscountCode = true;
              this.upParams.code_discount = Number(resss.data.data.discount);
            })
            .catch((err) => {
              this.upParams.isUseDiscountCode = false;
              this.upParams.customfield.promo_code = "";
              this.upParams.code_discount = 0;
              this.$message.error(err.data.msg);
            });
        }
        this.upParams.totalPrice =
          (price * 1000 -
            this.upParams.clDiscount * 1000 -
            this.upParams.cash_discount * 1000 -
            this.upParams.code_discount * 1000) /
            1000 >
          0
            ? (
                (price * 1000 -
                  this.upParams.cash_discount * 1000 -
                  this.upParams.clDiscount * 1000 -
                  this.upParams.code_discount * 1000) /
                1000
              ).toFixed(2)
            : 0;
        this.upgradePriceLoading = false;
      } catch (error) {
        this.upParams.original_price = 0;
        this.upParams.clDiscount = 0;
        this.upParams.isUseDiscountCode = false;
        this.upParams.customfield.promo_code = "";
        this.upParams.code_discount = 0;
        this.upParams.totalPrice = 0;
        this.upgradePriceLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    formatData() {
      // 处理数量类型的转为数组
      const temp = JSON.parse(JSON.stringify(this.configForm));
      // Object.keys(temp).forEach(el => {
      //   const arr = this.configoptions.filter(item => item.id * 1 === el * 1)
      //   if (arr.length !== 0) {
      //     if (arr[0].option_type === 'quantity'
      //       || arr[0].option_type === 'quantity_range'
      //       || arr[0].option_type === 'multi_select') {
      //       if (typeof (temp[el]) !== 'object') {
      //         temp[el] = [temp[el]]
      //       }
      //     }
      //   }
      // })
      return temp;
    },
    // 点击可升级授权
    selectUpItem(index) {
      this.selectUpIndex = index;
      this.curCycle = 0;
      this.getConfig();
    },
    // 提交升级
    handelUpConfirm() {
      if (this.upBtnLoading) return;
      // this.upBtnLoading = true
      if (this.licenseActive === "1") {
        // 产品升级结算
        upgradeHost({ id: this.id })
          .then((res) => {
            this.$refs.payDialog.showPayDialog(res.data.data.orderid);
          })
          .catch((err) => {
            this.$message.error(err.data.msg);
          })
          .finally(() => {
            this.upBtnLoading = false;
            this.upLicenseDialogShow = false;
          });
      } else {
        // const obj = {}
        // this.upgradeConfig.forEach((item) => { // 原始数量对象
        //   if (item.option_type === 'quantity_range') {
        //     obj[item.id] = [item.qty]
        //   }
        //   if (item.option_type === 'quantity') {
        //     obj[item.id] = item.qty
        //   }
        // })

        // const obj = this.configoptions.reduce((all, cur) => {
        //   if (cur.option_type === 'multi_select') {
        //     const mulArr = this.upgradeConfig.reduce((sum, c) => {
        //       if (c.id === cur.id) {
        //         sum.push(c.configoption_sub_id)
        //       }
        //       return sum
        //     }, [])
        //     all[cur.id] = mulArr
        //   } else {
        //     all[cur.id] = (
        //       cur.option_type === 'quantity' ||
        //       cur.option_type === 'quantity_range'
        //     ) ? this.backfillId('num', cur.id) : this.backfillId('id', cur.id)
        //   }
        //   // 区域的时候保存国家
        //   if (cur.option_type === 'area') {
        //     this.filterCountry[cur.id] = this.toTree(cur.subs)
        //     const curItem = this.upgradeConfig.filter(item => item.id === cur.id)
        //     let index = this.filterCountry[cur.id].findIndex(item => item.reduce((sumC, cc) => {
        //       sumC.push(cc.id)
        //       return sumC
        //     }, []).includes(curItem[0]?.configoption_sub_id * 1)
        //     )
        //     this.$set(this.curCountry, cur.id, index)
        //   }
        //   return all
        // }, {})
        if (this.isEquivalent(this.backups, this.configForm)) {
          this.$message.error(lang.common_cloud_text241);
          this.upBtnLoading = false;
          return;
        }
        const temp1 = this.formatData();
        params = { configoption: temp1, buy: this.isBuyServe };
        const upConfigApi = this.buy_id ? upgradeAppHost : upgradeConfigHost;
        const id = this.buy_id ? this.buy_host_id : this.id;
        upConfigApi(id, params)
          .then((res) => {
            this.$refs.payDialog.showPayDialog(res.data.data.id);
          })
          .catch((err) => {
            this.$message.error(err.data.msg);
          })
          .finally(() => {
            this.upBtnLoading = false;
            this.upLicenseDialogShow = false;
          });
      }
    },
    // 比较对象是否相等
    isEquivalent(a, b) {
      // a:已有配置  b:当前配置
      // 获取a和b对象的属性名数组
      const aProps = Object.getOwnPropertyNames(a);
      // 遍历对象的每个属性并进行比较
      for (let i = 0; i < aProps.length; i++) {
        const propName = aProps[i];
        // 如果属性值为对象，则递归调用该函数进行比较
        if (typeof a[propName] === "object") {
          if (!this.isEquivalent(a[propName], b[propName])) {
            return false;
          }
        } else {
          if (b.hasOwnProperty(propName)) {
            // 否则，直接比较属性值
            if (a[propName] !== b[propName]) {
              return false;
            }
          }
        }
      }
      // 如果遍历完成则说明两个对象内容相同
      return true;
    },
    /* 升降级 end */

    goPay() {
      if (this.hostData.status === "Unpaid") {
        this.$refs.payDialog.showPayDialog(this.hostData.order_id);
      }
    },
    // 日志
    logSizeChange(e) {
      this.logParams.limit = e;
      this.logParams.page = 1;
      // 获取列表
      this.getLogList();
    },
    logCurrentChange(e) {
      this.logParams.page = e;
      this.getLogList();
    },
    getLogList() {
      this.logLoading = true;
      const params = {
        ...this.logParams,
        id: this.id,
      };
      getLog(params)
        .then((res) => {
          if (res.data.status === 200) {
            this.logParams.total = res.data.data.count;
            this.logDataList = res.data.data.list;
          }
          this.logLoading = false;
        })
        .catch((error) => {
          this.logLoading = false;
        });
    },
    hadelSafeConfirm(val) {
      this[val]();
    },
    formatData() {
      const temp = JSON.parse(JSON.stringify(this.configForm));
      return temp;
    },

    // 跳转对应页面
    handleClick() {
      this.getCloudDetail();
      switch (this.activeName) {
        case "1":
          this.chartSelectValue = "1";
          this.getstarttime(1);
          this.getChartList();
          break;
        case "2":
          break;
        case "log":
          this.getLogList();
          break;
        default:
          const key = this.client_area[this.activeName * 1 - 3].key;
          configArea({ id: this.id, key }).then((res) => {
            this.$nextTick(() => {
              $(`#arae-${this.activeName}`).html(res.data.data.content);
            });
          });
          break;
      }
    },
    // 获取通用配置
    getCommonData() {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"));
      document.title =
        this.commonData.website_name + "-" + lang.common_cloud_text43;
    },
    // 获取自动续费状态
    getRenewStatus() {
      const params = {
        id: this.id,
      };
      renewStatus(params).then((res) => {
        if (res.data.status === 200) {
          const status = res.data.data.status;
          this.isShowPayMsg = status == 1 ? true : false;
        }
      });
    },
    autoRenewChange() {
      this.isShowAutoRenew = true;
    },
    autoRenewDgClose() {
      this.isShowPayMsg = !this.isShowPayMsg;
      this.isShowAutoRenew = false;
    },
    doAutoRenew() {
      const params = {
        id: this.id,
        status: this.isShowPayMsg ? 1 : 0,
      };
      rennewAuto(params)
        .then((res) => {
          if (res.data.status === 200) {
            this.$message.success(lang.common_cloud_text44);
            this.isShowAutoRenew = false;
            this.getRenewStatus();
          }
        })
        .catch((error) => {
          this.$message.error(error.data.msg);
        });
    },
    // 获取产品详情
    getHostDetail() {
      const params = {
        id: this.id,
      };
      hostDetail(params).then((res) => {
        if (res.data.status === 200) {
          this.hostData = res.data.data.host;
          this.self_defined_field = res.data.data.self_defined_field.map(
            (item) => {
              item.hidenPass = false;
              return item;
            }
          );
          this.hostData.status_name =
            this.hostStatus[res.data.data.host.status].text;

          // 判断下次缴费时间是否在十天内
          if (
            (this.hostData.due_time * 1000 - new Date().getTime()) /
              (24 * 60 * 60 * 1000) <=
            10
          ) {
            this.isRead = true;
          }
          this.product_id = this.hostData.product_id;
        }
      });
    },
    copyPass(text) {
      if (navigator.clipboard && window.isSecureContext) {
        // navigator clipboard 向剪贴板写文本
        this.$message.success(lang.index_text32);
        return navigator.clipboard.writeText(text);
      } else {
        // 创建text area
        const textArea = document.createElement("textarea");
        textArea.value = text;
        // 使text area不在viewport，同时设置不可见
        document.body.appendChild(textArea);
        // textArea.focus()
        textArea.select();
        this.$message.success(lang.index_text32);
        return new Promise((res, rej) => {
          // 执行复制命令并移除文本框
          document.execCommand("copy") ? res() : rej();
          textArea.remove();
        });
      }
    },
    textRange(el) {
      const targetw = el.getBoundingClientRect().width;
      const range = document.createRange();
      range.setStart(el, 0);
      range.setEnd(el, el.childNodes.length);
      const rangeWidth = range.getBoundingClientRect().width;
      return rangeWidth > targetw;
    },
    checkWidth(e, index) {
      const bol = this.textRange(e.target);
      this.configDetails[index].show = bol;
    },
    hideTip(index) {
      this.configDetails[index].show = false;
    },
    // 获取实例详情
    async getCloudDetail() {
      try {
        const res = await cloudDetail({
          id: this.id,
        });
        this.cloudData = res.data.data;
        this.chartData = res.data.data.module_chart.map((item) => {
          this.$set(item, "selectValue", item.select[0]?.value || "");
          this.$set(item, "loading", false);
          return item;
        });
        this.client_area = res.data.data.module_client_area;
        const btnList = res.data.data.module_button.control.concat(
          res.data.data.module_button.console
        );
        this.consoleList = [];
        this.powerList = [];
        btnList.forEach((item) => {
          if (
            item.func === "crack_pass" ||
            item.func === "reinstall" ||
            item.func === "vnc"
          ) {
            this.consoleList.push(item);
          } else {
            this.powerList.push(item);
          }
        });
        this.powerStatus = this.powerList[0]?.func || "";
        this.configDetails = [];
        if (this.cloudData.system_button.upgrade_option) {
          this.isShowOptionUpdate =
            !this.cloudData.system_button.upgrade_option.disabled;
        }
        const temp = []
          .filter((item) => item.showdetail)
          .reduce((all, cur) => {
            all.push({
              name: cur.fieldname,
              sub_name: cur.value,
            });
            return all;
          }, []);
        this.configDetails = this.cloudData.config_options
          .concat(temp)
          .map((item) => {
            this.$set(item, "show", false);
            return item;
          });

        // 主IP
        this.main_ip = this.cloudData.host_data.dedicatedip;
        // 网络里面的IP列表
        const ipList = this.cloudData.host_data.assignedips || [];
        if (this.main_ip) {
          ipList.unshift(this.main_ip);
          const _tempIp = Array.from(new Set(ipList));
          this.netDataList = _tempIp.reduce((all, cur) => {
            if (cur) {
              all.push({
                ip: cur,
                gateway: "--",
                subnet_mask: "--",
              });
            }
            return all;
          }, []);
        }

        this.osData = res.data.data.os || {};
        if (this.osData.subs && this.osData.subs[0]) {
          this.osData.subs.forEach((item) => {
            item.version = item.version.map((items) => {
              items.os = item.os;
              return items;
            });
          });
          this.osSelectData = this.osData.subs[0]?.version || [];
          this.osIcon =
            "/plugins/reserver/mf_finance_common/template/clientarea/pc/default/img/remf_finance_common/" +
              this.osData.subs[0]?.os +
              ".svg" || "";
          this.reinstallData.osGroupId = this.osData.subs[0]?.os || "";
          this.reinstallData.osId = this.osData.subs[0].version[0]?.id || "";
          this.selectOsObj = this.osData.subs[0]?.version[0] || {};
          this.initLoading = false;
        }
      } catch (error) {
        console.log(error);
      }
    },
    // 关闭备注弹窗
    notesDgClose() {
      this.isShowNotesDialog = false;
    },
    // 显示 修改备注 弹窗
    doEditNotes() {
      this.isShowNotesDialog = true;
      this.notesValue = this.hostData.notes;
    },
    // 修改备注提交
    subNotes() {
      const params = {
        id: this.id,
        notes: this.notesValue,
      };
      editNotes(params)
        .then((res) => {
          if (res.data.status === 200) {
            // 重新拉取产品详情
            this.getHostDetail();
            this.$message({
              message: lang.appstore_text359,
              type: "success",
            });
            this.isShowNotesDialog = false;
          }
        })
        .catch((err) => {
          this.$message.error(err.data.msg);
        });
    },
    // 返回产品列表页
    goBack() {
      window.history.back();
    },
    // 关闭重装系统弹窗
    reinstallDgClose() {
      this.isShowReinstallDialog = false;
    },
    // 展示重装系统弹窗
    showReinstall() {
      this.errText = "";
      this.reinstallData.password = null;
      this.reinstallData.key = null;
      this.reinstallData.port = null;
      this.isShowReinstallDialog = true;
    },
    // 提交重装系统
    doReinstall() {
      if (this.reinstallLoading) {
        return;
      }
      let isPass = true;
      const data = { ...this.reinstallData };
      if (!data.osId) {
        isPass = false;
        this.errText = lang.common_cloud_text45;
        return false;
      }
      if (!this.client_operate_password) {
        this.$refs.safeRef.openDialog("doReinstall");
        return;
      }
      const client_operate_password = this.client_operate_password;
      this.client_operate_password = "";
      this.reinstallLoading = true;
      if (isPass) {
        this.errText = "";
        provision({
          id: this.id,
          func: "reinstall",
          os: this.selectOsObj.id,
          os_group: this.selectOsObj.os,
          client_operate_password,
        })
          .then((res) => {
            this.$message.success(res.data.msg);
            this.getCloudDetail();
            this.isShowReinstallDialog = false;
            this.reinstallLoading = false;
          })
          .catch((err) => {
            this.reinstallLoading = false;
            this.errText = err.data.msg;
          });
      }
    },

    // 镜像分组改变时
    osSelectGroupChange(e) {
      this.osData.subs.forEach((item) => {
        if (item.os == e) {
          this.osSelectData = item.version;
          this.selectOsObj = this.osSelectData[0];
          this.osIcon =
            "/plugins/reserver/mf_finance_common/template/clientarea/pc/default/img/remf_finance_common/" +
            item.os +
            ".svg";
          this.reinstallData.osId = item.version[0].id;
        }
      });
    },
    // 镜像版本改变时
    osSelectChange(e) {
      this.selectOsObj = this.osSelectData.filter(
        (item) => item.id === this.reinstallData.osId
      )[0];
    },
    // 随机生成密码
    autoPass() {
      let pass = randomCoding(1) + 0 + genEnCode(9, 1, 1, 0, 1, 0);
      this.reinstallData.password = pass;
      // 重置密码
      this.rePassData.password = pass;
      // 救援系统密码
      // this.rescueData.password = pass
    },
    // 随机生成port
    autoPort() {
      const temp = genEnCode(3, 1, 0, 0, 0, 0);
      if (temp[0] * 1 === 0) {
        this.reinstallData.port = Math.ceil(Math.random() * 9) + temp.substr(1);
      } else {
        this.reinstallData.port = temp;
      }
    },

    // 获取实例状态
    getCloudStatus() {
      const params = {
        id: this.id,
      };
      cloudStatus(params)
        .then((res) => {
          if (res.status === 200) {
            this.status = res.data.data.status;
            this.statusText = res.data.data.desc;
          }
        })
        .catch((err) => {
          this.$message.error(err.data.msg);
        });
    },

    // 控制台点击
    doGetVncUrl() {
      if (!this.client_operate_password) {
        this.$refs.safeRef.openDialog("doGetVncUrl");
        return;
      }
      const client_operate_password = this.client_operate_password;
      this.client_operate_password = "";
      const params = {
        id: this.id,
        func: "vnc",
        client_operate_password,
      };
      provision(params)
        .then((res) => {
          if (res.data.status === 200) {
            window.open(res.data.data.url);
          }
          this.loading2 = false;
        })
        .catch((err) => {
          this.$message.error(err.data.msg);
          this.loading2 = false;
        });
    },
    getVncUrl() {
      this.loading4 = true;
      this.doGetVncUrl();
    },

    // 获取产品停用信息
    getRefundMsg() {
      const params = {
        id: this.id,
      };
      refundMsg(params)
        .then((res) => {
          if (res.data.status === 200) {
            this.refundData = res.data.data.refund;
          }
        })
        .catch((err) => {
          this.refundData = null;
        });
    },
    // 获取cup/内存使用信息
    getRealData() {
      realData(this.id).then((res) => {
        this.cpu_realData = res.data.data;
      });
    },
    // 支付成功回调
    paySuccess(e) {
      this.getHostDetail();
      this.getCloudDetail();
    },
    // 取消支付回调
    payCancel(e) {
      console.log(e);
    },
    // 获取优惠码信息
    getPromoCode() {
      const params = {
        id: this.id,
      };
      promoCode(params).then((res) => {
        if (res.data.status === 200) {
          let codes = res.data.data.promo_code;
          let code = "";
          codes.map((item) => {
            code += item + ",";
          });
          code = code.slice(0, -1);
          this.codeString = code;
        }
      });
    },

    // 续费使用代金券
    reUseCash(val) {
      this.cashObj = val;
      const price = val.price ? Number(val.price) : 0;
      this.renewParams.cash_discount = price;
      this.renewParams.customfield.voucher_get_id = val.id;
    },
    // 续费移除代金券
    reRemoveCashCode() {
      this.$refs.cashRef && this.$refs.cashRef.closePopver();
      this.cashObj = {};
      this.renewParams.cash_discount = 0;
      this.renewParams.customfield.voucher_get_id = "";
    },
    // 续费使用优惠码
    async getRenewDiscount(data) {
      this.renewParams.customfield.promo_code = data[1];
      this.renewParams.isUseDiscountCode = true;
      this.renewParams.code_discount = Number(data[0]);
      const price = this.renewParams.base_price;
      const discountParams = { id: this.product_id, amount: price };
      // 开启了等级折扣插件
      if (this.isShowLevel) {
        // 获取等级抵扣价格
        await clientLevelAmount(discountParams)
          .then((res2) => {
            if (res2.data.status === 200) {
              this.renewParams.clDiscount = Number(res2.data.data.discount); // 客户等级优惠金额
            }
          })
          .catch((error) => {
            this.renewParams.clDiscount = 0;
          });
      }
    },
    // 移除续费的优惠码
    removeRenewDiscountCode() {
      this.renewParams.isUseDiscountCode = false;
      this.renewParams.customfield.promo_code = "";
      this.renewParams.code_discount = 0;
      const price = this.renewParams.original_price;
    },

    // 显示续费弹窗
    showRenew() {
      if (this.renewBtnLoading) return;
      this.renewBtnLoading = true;
      // 获取续费页面信息
      const params = {
        id: this.id,
      };
      this.isShowRenew = true;
      this.renewLoading = true;
      renewPage(params)
        .then(async (res) => {
          if (res.data.status === 200) {
            this.renewPageData = res.data.data.host;
            this.renewActiveId = 0;
            this.renewParams.billing_cycle =
              this.renewPageData[0].billing_cycle;
            this.renewParams.duration = this.renewPageData[0].duration;
            this.renewParams.original_price = this.renewPageData[0].price;
            this.renewParams.base_price = this.renewPageData[0].base_price;
            this.renewParams.totalPrice = this.renewPageData[0].price;
            this.renewLoading = false;
          }
          this.renewLoading = false;
        })
        .catch((err) => {
          this.renewBtnLoading = false;
          this.renewLoading = false;
          this.$message.error(err.data.msg);
        });
    },
    // 续费弹窗关闭
    renewDgClose() {
      this.renewBtnLoading = false;
      this.isShowRenew = false;
      this.removeRenewDiscountCode();
      this.reRemoveCashCode();
    },
    // 续费提交
    subRenew() {
      const params = {
        id: this.id,
        billing_cycle: this.renewParams.billing_cycle,
        customfield: this.renewParams.customfield,
      };
      renew(params)
        .then((res) => {
          if (res.data.status === 200) {
            if (res.data.code == "Paid") {
              this.$message.success(res.data.msg);
              this.getHostDetail();
            } else {
              this.isShowRenew = false;
              this.renewOrderId = res.data.data.id;
              const orderId = res.data.data.id;
              const amount = this.renewParams.totalPrice;
              this.$refs.topPayDialog.showPayDialog(orderId, amount);
            }
            this.renewBtnLoading = false;
          }
        })
        .catch((err) => {
          this.renewBtnLoading = false;
          this.$message.error(err.data.msg);
        });
    },
    // 续费周期点击
    async renewItemChange(item, index) {
      this.reRemoveCashCode();
      this.renewLoading = true;
      this.renewActiveId = index;
      this.renewParams.duration = item.duration;
      this.renewParams.billing_cycle = item.billing_cycle;
      let price = item.price;
      this.renewParams.original_price = item.price;
      this.renewParams.base_price = item.base_price;
      // 开启了优惠码插件
      if (this.isShowPromo && this.renewParams.isUseDiscountCode) {
        const discountParams = { id: this.product_id, amount: item.base_price };
        // 开启了等级折扣插件
        if (this.isShowLevel) {
          // 获取等级抵扣价格
          await clientLevelAmount(discountParams)
            .then((res2) => {
              if (res2.data.status === 200) {
                this.renewParams.clDiscount = Number(res2.data.data.discount); // 客户等级优惠金额
              }
            })
            .catch((error) => {
              this.renewParams.clDiscount = 0;
            });
        }
        // 更新优惠码
        await applyPromoCode({
          // 开启了优惠券
          scene: "renew",
          product_id: this.product_id,
          amount: item.base_price,
          billing_cycle_time: this.renewParams.duration,
          promo_code: this.renewParams.customfield.promo_code,
        })
          .then((resss) => {
            price = item.base_price;
            this.renewParams.isUseDiscountCode = true;
            this.renewParams.code_discount = Number(resss.data.data.discount);
          })
          .catch((err) => {
            this.$message.error(err.data.msg);
            this.removeRenewDiscountCode();
          });
      }

      this.renewLoading = false;
    },

    // 取消停用
    quitRefund() {
      if (!this.client_operate_password) {
        this.$refs.safeRef.openDialog("quitRefund");
        return;
      }
      const client_operate_password = this.client_operate_password;
      this.client_operate_password = "";

      const params = {
        id: this.refundData.id,
        client_operate_password,
      };
      cancel(params)
        .then((res) => {
          if (res.data.status == 200) {
            this.$message.success(lang.common_cloud_text57);
            this.getRefundMsg();
          }
        })
        .catch((err) => {
          this.$message.error(err.data.msg);
        });
    },
    // 关闭停用
    refundDgClose() {},
    // 删除实例点击
    showRefund() {
      const params = {
        host_id: this.id,
      };
      // 获取停用页面信息
      refundPage(params).then((res) => {
        if (res.data.status == 200) {
          this.refundPageData = res.data.data;
          this.refundPageData.configs =
            this.refundPageData.config_option.data.reduce((all, cur) => {
              all.push({
                name: cur.name,
                value: this.configDetails.filter(
                  (el) =>
                    el.id ===
                    cur.field.replace("configoption[", "").replace("]", "") * 1
                )[0]?.sub_name,
              });
              return all;
            }, []);
          this.isShowRefund = true;
        }
      });
    },
    // 关闭停用弹窗
    refundDgClose() {
      this.isShowRefund = false;
    },
    // 停用弹窗提交
    subRefund() {
      const params = {
        host_id: this.id,
        suspend_reason: this.refundParams.suspend_reason,
        type: this.refundParams.type,
      };
      if (!params.suspend_reason) {
        this.$message.error(lang.common_cloud_text58);
        return false;
      }
      if (!params.type) {
        this.$message.error(lang.common_cloud_text59);
        return false;
      }
      if (!this.client_operate_password) {
        this.$refs.safeRef.openDialog("subRefund");
        return;
      }
      params.client_operate_password = this.client_operate_password;
      this.client_operate_password = "";
      refund(params)
        .then((res) => {
          if (res.data.status == 200) {
            this.$message.success(lang.common_cloud_text60);
            this.isShowRefund = false;
            this.getRefundMsg();
          }
        })
        .catch((err) => {
          this.$message.error(err.data.msg);
        });
    },
    // 管理开始
    // 进行开关机
    toChangePower() {
      if (!this.client_operate_password) {
        this.$refs.safeRef.openDialog("toChangePower");
        return;
      }
      const client_operate_password = this.client_operate_password;
      this.client_operate_password = "";

      this.loading1 = true;
      provision({
        id: this.id,
        func: this.powerStatus,
        client_operate_password,
      })
        .then((res) => {
          this.$message.success(res.data.msg);
          this.loading1 = false;
          this.isShowPowerChange = false;
          this.getDetail();
        })
        .catch((err) => {
          this.$message.error(err.data.msg);
          this.loading1 = false;
        });
    },
    // 重置密码点击
    showRePass() {
      this.errText = "";
      this.rePassData = {
        password: "",
        checked: false,
      };
      this.isShowRePass = true;
    },
    // 关闭重置密码弹窗
    rePassDgClose() {
      this.isShowRePass = false;
    },
    // 重置密码提交
    rePassSub() {
      const data = this.rePassData;
      let isPass = true;
      if (!data.password) {
        isPass = false;
        this.errText = lang.common_cloud_text61;
        return false;
      }

      if (!this.client_operate_password) {
        this.$refs.safeRef.openDialog("rePassSub");
        return;
      }
      const client_operate_password = this.client_operate_password;
      this.client_operate_password = "";
      if (isPass) {
        this.loading5 = true;
        this.errText = "";
        const params = {
          id: this.id,
          func: "crack_pass",
          password: data.password,
          force: "on",
          client_operate_password,
        };
        provision(params)
          .then((res) => {
            if (res.data.status === 200) {
              this.$message.success(lang.common_cloud_text63);
              this.isShowRePass = false;
              this.getDetail();
            }
            this.loading5 = false;
          })
          .catch((error) => {
            this.errText = error.data.msg;
            this.loading5 = false;
          });
      }
    },

    // 显示电源操作确认弹窗
    showPowerDialog() {
      this.powerTitle = this.powerList.filter(
        (item) => item.func === this.powerStatus
      )[0].name;
      this.powerType = this.powerStatus;
      this.isShowPowerChange = true;
    },

    // 变化监听
    sliderChange(val, item) {
      const arr = [];
      item.selectList.forEach((i) => {
        arr.push([i.min_value, i.max_value]);
      });
      item.size = this.mapToRange(val, arr, item.min_value);
    },
    changeDataNum(val, item) {
      // 数据盘数量改变计算价格
      item.size = this.mapToRange(
        val,
        item.selectList[0][item.type].config,
        item.selectList[0][item.type].config[0]
      );
    },

    handelConsole(item) {
      this.consoleData = item;
      if (item.func === "crack_pass") {
        this.showRePass();
      }
      if (item.func === "reinstall") {
        this.showReinstall();
      }
      if (item.func === "vnc") {
        this.doGetVncUrl();
      }
    },
    getstarttime(type) {
      // 1: 过去24小时 2：过去三天 3：过去七天
      let nowtime = parseInt(new Date().getTime() / 1000);
      if (type == 1) {
        this.startTime = nowtime - 24 * 60 * 60;
      } else if (type == 2) {
        this.startTime = nowtime - 24 * 60 * 60 * 3;
      } else if (type == 3) {
        this.startTime = nowtime - 24 * 60 * 60 * 7;
      }
    },
    // 时间选择框
    chartSelectChange(e) {
      // 计算开始时间
      this.getstarttime(e);
      // 重新拉取图表数据
      this.getChartList();
    },
    getChartList() {
      this.chartData.forEach((items, i) => {
        items.loading = true;
        const params = {
          id: this.id,
          start: this.startTime,
          type: items.type,
          select: items.selectValue,
        };
        chartList(params)
          .then((res) => {
            if (res.data.status === 200) {
              const list = res.data.data.list;
              const options = {
                title: {
                  text: items.title,
                },
                tooltip: {
                  show: true,
                  trigger: "axis",
                },
                legend: {
                  data: res.data.data.label,
                },
                grid: {
                  left: "5%",
                  right: "4%",
                  bottom: "5%",
                  containLabel: true,
                },
                xAxis: {
                  type: "category",
                  boundaryGap: false,
                  data: list[0].map((item) => item.time),
                },
                yAxis: {
                  type: "value",
                },
                series: res.data.data.label.map((item, index) => {
                  return {
                    name: item,
                    data: list[index].map((item) => item.value),
                    type: "line",
                    areaStyle: {},
                  };
                }),
              };
              echarts
                .init(document.getElementById(`${i}-echart`))
                .setOption(options);
            }
            items.loading = false;
          })
          .catch((err) => {
            console.log(err);
            items.loading = false;
          });
      });
    },
    powerDgClose() {
      this.isShowPowerChange = false;
    },
  },
}).$mount(template);
