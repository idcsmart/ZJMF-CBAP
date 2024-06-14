const template = document.getElementsByClassName("common_product_detail")[0];
Vue.prototype.lang = Object.assign(window.lang, window.module_lang);

new Vue({
  components: {
    asideMenu,
    topMenu,
    pagination,
    payDialog,
    cashCoupon,
    discountCode,
    cashBack,
    safeConfirm,
  },
  created() {
    this.id = location.href.split("?")[1].split("=")[1];
    this.getCommonData();
    this.getDetail();
    this.getComDetail();
    // 获取退款信息
    // this.getRefundInfo()
    this.getCountryList();
    // this.getRenewStatus()
    this.getRenewPrice();
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
      this.getPromo();
    }
    if (arr.includes("IdcsmartClientLevel")) {
      // 开启了等级优惠
      this.isShowLevel = true;
    }
    if (arr.includes("IdcsmartVoucher")) {
      // 开启了代金券
      this.isShowCash = true;
    }
    if (arr.includes("IdcsmartRefund")) {
      this.hasRefundPlugin = true;
      this.getRefundInfo();
    }
    if (arr.includes("IdcsmartRenew")) {
      this.hasRenewPlugin = true;
      this.getRenewStatus();
    }
    window.reshHtml = this.handleClick;
  },
  updated() {
    // 关闭loading
    // document.getElementById('mainLoading').style.display = 'none';
    // document.getElementsByClassName('common_product_detail')[0].style.display = 'block'
  },
  destroyed() {},
  data() {
    return {
      initLoading: true,
      hasRefundPlugin: false,
      hasRenewPlugin: false,
      baseUrl: url,
      id: "",
      isShowCash: false,
      product_id: "",
      client_operate_password: "",
      renewLoading: false,
      pro_base_price: 0,
      params: {
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 200,
        orderby: "id",
        sort: "desc",
        keywords: "",
      },
      commonData: {},
      // 代金券对象
      cashObj: {},
      payWay: {
        free: lang.free,
        onetime: lang.onetime,
        recurring_prepayment: lang.recurring_prepayment,
        recurring_postpaid: lang.recurring_postpaid,
      },
      countryList: [],
      host: {}, // 基础信息
      configoptions: [], // 配置
      status: {
        Unpaid: {
          text: lang.common_cloud_text88,
          color: "#F64E60",
          bgColor: "#FFE2E5",
        },
        Pending: {
          text: lang.common_cloud_text89,
          color: "#3699FF",
          bgColor: "#E1F0FF",
        },
        Active: {
          text: lang.common_cloud_text90,
          color: "#1BC5BD",
          bgColor: "#C9F7F5",
        },
        Suspended: {
          text: lang.common_cloud_text91,
          color: "#F0142F",
          bgColor: "#FFE2E5",
        },
        Deleted: {
          text: lang.common_cloud_text92,
          color: "#9696A3",
          bgColor: "#F2F2F7",
        },
        Failed: {
          text: lang.common_cloud_text93,
          color: "#FFA800",
          bgColor: "#FFF4DE",
        },
      },
      // 停用状态
      refundStatus: {
        Pending: lang.common_cloud_text234,
        Suspending: lang.common_cloud_text235,
        Suspend: lang.common_cloud_text236,
        Suspended: lang.common_cloud_text237,
        Refund: lang.common_cloud_text238,
        Reject: lang.common_cloud_text239,
        Cancelled: lang.common_cloud_text240,
      },
      isShowPass: false,
      /* 停用相关 */
      isStop: false,
      noRefundVisible: false,
      refundVisible: false,
      refundInfo: {}, //商品停用信息
      refundForm: {
        str: "",
        arr: [],
        type: "Expire", // Expire, Immediate
      },
      refundMoney: "0.00",
      refundDialog: {},
      // 续费
      renewActiveId: "0",
      // 显示续费弹窗
      isShowRenew: false,
      customfield: {},
      // 续费页面信息
      renewPageData: [],
      addons_js_arr: [], // 插件列表
      isShowPromo: false, // 是否开启优惠码
      isShowLevel: false, // 是否开启等级优惠
      isUseDiscountCode: false, // 是否使用优惠码
      // 续费参数
      renewParams: {
        id: 0,
        duration: "", // 周期
        billing_cycle: "", // 周期时间
        clDiscount: 0, // 用户等级折扣价
        code_discount: 0, // 优惠码折扣价
        cash_discount: 0, // 代金券折扣价格
        original_price: 0, // 售卖价格
        base_price: 0, // 原价
        totalPrice: 0, // 应支付价格
      },
      /* 备注 */
      isShowNotesDialog: false,
      host: {},
      hostData: {},
      hidenPass: false,
      self_defined_field: [],
      notesValue: "",
      promo_code: [],
      loading: false,
      // 自动续费
      isShowPayMsg: 0,
      autoTitle: "",
      dialogVisible: false,
      /* 升降级 */
      upgradeLoading: false,
      upLicenseDialogShow: false,
      selectUpIndex: 0,
      buy_id: "",
      buy_host_id: "",
      upPriceLoading: false,
      licenseActive: "1",
      upData: {
        price: 0,
        clDiscount: 0,
        totalPrice: 0,
        code_discount: 0,
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
      renewPriceList: [],
      filterCountry: {},
      // filterCountry: [],
      /* 升降级 end */

      /* 2023/11/22新增  */
      activeName: "0",
      chartSelectValue: "1",
      startTime: 0,
      addonsArr: [],
      configLimitList: [], // 限制规则
      configObj: {},
      backup_config: [],
      snap_config: [],
      cpu_realData: {},
      // 是否救援系统
      isRescue: false,
      cloudConfig: {},
      // 实例详情
      cloudData: {
        data_center: {
          iso: "CN",
        },
        image: {
          icon: "",
        },
        config: {
          reinstall_sms_verify: 0,
          reset_password_sms_verify: 0,
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
      // 显示重装系统弹窗
      isShowReinstallDialog: false,
      // 重装系统弹窗内容
      reinstallData: {
        osGroupId: "",
        osId: "",
      },
      selectOsObj: {},
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
      // 管理开始
      // 开关机状态
      powerStatus: "",
      consoleList: [],
      powerList: [],
      // 重置密码弹窗数据
      rePassData: {
        password: "",
      },
      codeTimer: null,
      sendTime: 60,
      isSendCodeing: false,
      sendFlag: false,
      // 是否展示重置密码弹窗
      isShowRePass: false,
      // 救援模式弹窗数据
      rescueData: {
        type: "1",
        password: "",
      },
      // 是否展示救援模式弹窗
      isShowRescue: false,
      // 是否展示退出救援模式弹窗
      isShowQuit: false,
      ipValue: null,

      netLoading: false,
      netDataList: [],
      netParams: {
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 200,
        orderby: "id",
        sort: "desc",
        keywords: "",
      },
      elasticLoading: false,
      elasticParams: {
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 0,
      },
      elasticList: [],
      // 网络流量
      flowData: {},
      // 日志开始
      logDataList: [],
      logParams: {
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 200,
        orderby: "id",
        sort: "desc",
        keywords: "",
      },
      logLoading: false,

      // 备份与快照开始
      dataList1: [],
      // 备份列表数据
      dataList1: [],
      // 快照列表数据
      dataList2: [],
      backLoading: false,
      snapLoading: false,
      params1: {
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 200,
        orderby: "id",
        sort: "desc",
        keywords: "",
      },
      params2: {
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 200,
        orderby: "id",
        sort: "desc",
        keywords: "",
      },
      // true 标记为备份  false 标记为快照
      isBs: true,
      // 弹窗表单数据
      createBsData: {
        id: 0,
        name: "",
        disk_id: 0,
      },
      // 实例磁盘列表
      // 是否显示弹窗
      isShwoCreateBs: false,
      cgbsLoading: false,
      isShowhyBs: false,
      safeDialogShow: false,
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
        duration: lang.common_cloud_text110,
      },
      // 是否显示开启备份弹窗
      isShowOpenBs: false,
      // 快照备份订单id
      bsOrderId: 0,
      chartSelectValue: "1",
      // 统计图表开始
      echartLoading1: false,
      echartLoading2: false,
      echartLoading3: false,
      echartLoading4: false,
      isShowPowerChange: false,
      loading1: false,
      loading2: false,
      loading3: false,
      loading4: false,
      loading5: false,
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
      consoleData: {},
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
      memory_unit: "",
      // 流量包
      showPackage: false,
      packageLoading: false,
      packageList: [],
      curPackageId: "",
      /* 转发建站 */
      aclLoading: false,
      webLoading: false,
      aclList: [],
      webList: [],
      protocolArr: [
        { value: 1, label: "TCP" },
        { value: 2, label: "UDP" },
        { value: 3, label: "TCP+UDP" },
      ],
      natDialog: false,
      natType: "", // acl, web
      natForm: {
        name: "",
        int_port: undefined,
        protocol: "",
        domain: "",
      },
      submitLoaing: false,
      natRules: {
        name: [
          {
            required: true,
            message: `${lang.placeholder_pre1}${lang.security_label1}`,
            trigger: "blur",
          },
        ],
        domain: [
          {
            required: true,
            message: `${lang.placeholder_pre1}${lang.domain}`,
            trigger: "blur",
          },
        ],
        int_port: [
          {
            required: true,
            message: `${lang.placeholder_pre1}${lang.int_port}`,
            trigger: "blur",
          },
        ],
        protocol: [
          {
            required: true,
            message: `${lang.placeholder_pre2}${lang.protocol}`,
            trigger: "change",
          },
        ],
      },
      chartData: [],
      client_area: [],
      client_button: {},
      osData: {},
      /* 2023/11/22新增结束  */
      statusText: "",
      postPowerStatus: "",
    };
  },
  mixins: [mixin],
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
    // filterCountry () {
    //   return (country) => {
    //     const name = this.countryList.filter(item => item.iso === country)
    //     return name[0]?.name_zh
    //   }
    // },
    calcSwitch() {
      return (item, type) => {
        if (type) {
          const arr = item.subs.filter((item) => item.option_name === lang.yes);
          return arr[0]?.id;
        } else {
          const arr = item.subs.filter((item) => item.option_name === lang.no);
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
    showRenewPrice() {
      let p = this.hostData.renew_amount;
      this.renewPriceList.forEach((item) => {
        if (
          item.billing_cycle === this.hostData.billing_cycle_name &&
          this.hostData.renew_amount * 1 < item.price * 1
        ) {
          p = item.price * 1;
        }
      });
      return p;
    },
  },
  watch: {
    renewParams: {
      handler() {
        let n = 0;
        // l:当前周期的续费价格
        const l = this.hostData.renew_amount;
        if (this.isShowPromo && this.customfield.promo_code) {
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
          console.log(n > l);
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
  methods: {
    hadelSafeConfirm(val) {
      this[val]();
    },
    // 获取实例状态
    getCloudStatus() {
      const params = {
        id: this.id,
        func: "status",
      };
      provision(params)
        .then((res) => {
          if (res.status === 200) {
            this.postPowerStatus = res.data.data.status;
            this.statusText = res.data.data.des;
            if (this.status == "operating") {
              this.getCloudStatus();
            } else {
              this.$emit("getstatus", res.data.data.status);
            }
          }
        })
        .catch((err) => {
          this.getCloudStatus();
        });
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
    /* 2023/11/22新增  */
    // 跳转对应页面
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
    // 统计图表开始
    // 获取内存用量
    getChartList() {
      this.chartData.forEach((items, i) => {
        items.loading = true;
        const params = {
          id: this.id,
          chart: {
            start: this.startTime,
            type: items.type,
            select: items.selectValue,
          },
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
                  left: "8%",
                  right: "8%",
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
            items.loading = false;
          });
      });
    },
    // 时间选择框
    chartSelectChange(e) {
      // 计算开始时间
      this.getstarttime(e);
      // 重新拉取图表数据
      this.getChartList();
    },
    // 显示电源操作确认弹窗
    showPowerDialog() {
      this.powerTitle = this.powerList.filter(
        (item) => item.func === this.powerStatus
      )[0].name;
      this.powerType = this.powerStatus;
      this.isShowPowerChange = true;
    },
    // 随机生成密码
    autoPass() {
      // 重置密码
      this.rePassData.password =
        randomCoding(1) + 0 + genEnCode(9, 1, 1, 0, 1, 0);
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
    // 展示重装系统弹窗
    showReinstall() {
      this.errText = "";
      this.isShowReinstallDialog = true;
    },
    // 关闭重装系统弹窗
    reinstallDgClose() {
      this.isShowReinstallDialog = false;
    },
    // 镜像分组改变时
    osSelectGroupChange(e) {
      this.osData.subs.forEach((item) => {
        if (item.os == e) {
          this.osSelectData = item.version;
          this.selectOsObj = this.osSelectData[0];
          this.osIcon =
            "/plugins/reserver/idcsmart_common/template/clientarea/pc/default/img/idcsmart_common/" +
            item.os +
            ".svg";
          this.reinstallData.osId = item.version[0].id;
        }
      });
    },
    // 随机生成port
    autoPort() {
      this.reinstallData.port = genEnCode(3, 1, 0, 0, 0, 0);
    },
    // 镜像版本改变时
    osSelectChange() {
      this.selectOsObj = this.osSelectData.filter(
        (item) => item.id === this.reinstallData.osId
      )[0];
    },
    // 提交重装系统
    doReinstall() {
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
      if (isPass) {
        this.errText = "";
        provision({
          id: this.id,
          func: "reinstall",
          option_id: this.osData.id,
          sub_id: this.selectOsObj.id,
          os: this.selectOsObj.option_param,
          os_name: this.selectOsObj.option_name,
          client_operate_password,
        })
          .then((res) => {
            this.$message.success(res.data.msg);
            this.getDetail();
            this.isShowReinstallDialog = false;
          })
          .catch((err) => {
            this.errText = err.data.msg;
          });
      }
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
    // 重置密码点击
    showRePass() {
      this.errText = "";
      this.rePassData = {
        password: "",
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
      // if (!data.code && this.cloudConfig.reset_password_sms_verify === 1) {
      //   isPass = false;
      //   this.errText = lang.account_tips33;
      //   return false;
      // }
      // if (!data.checked && this.powerStatus == "off") {
      //   isPass = false;
      //   this.errText = lang.common_cloud_text62;
      //   return false;
      // }

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
    // 控制台点击
    doGetVncUrl() {
      const params = {
        id: this.id,
        func: "vnc",
      };
      provision(params)
        .then((res) => {
          if (res.data.status === 200) {
            window.open(res.data.url);
          }
          this.loading2 = false;
        })
        .catch((err) => {
          this.$message.error(err.data.msg);
          this.loading2 = false;
        });
    },
    // 电源相关
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
    powerDgClose() {
      this.isShowPowerChange = false;
    },

    handleClick() {
      this.getDetail();
      switch (this.activeName) {
        case "0":
          break;
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
              $(`#arae-${this.activeName}`).html(res.data.data.html);
            });
          });
          break;
      }
    },
    /* 2023/11/22新增结束  */
    /* 升降级 */
    handelUpLicense(val) {
      if (this.upgradeLoading) return;
      if (val !== "isUpApp") {
        this.buy_id = "";
        this.buy_host_id = "";
      }
      this.upgradeLoading = true;
      this.licenseActive = "2";
      this.selectUpIndex = 0;
      this.$message({
        showClose: true,
        message: lang.common_cloud_text54,
        type: "warning",
        duration: 10000,
      });
      this.handleTabClick({ name: "2" });
      this.curCycle = 0;
    },
    handleTabClick(e) {
      this.selectUpIndex = 0;
      const upApi = this.buy_id ? upAppPage : upgradePage;
      const configApi = this.buy_id ? upgradeAppPage : upgradeConfigPage;
      const id = this.buy_id ? this.buy_host_id : this.id;
      if (e.name === "1") {
        // 产品升降级
        this.isShowUp = true;
        upApi(id)
          .then((res) => {
            this.upgradeList = res.data.data.upgrade;
            if (res.data.data.upgrade.length === 0) {
              this.isShowUp = false;
              this.licenseActive = "2";
              this.handleTabClick({ name: "2" });
              return;
            }
            this.upgradeHost = res.data.data.host;
            this.upgradeConfig = res.data.data.configoptions;
            this.upgradeSon_host = res.data.data.son_host;
            this.upgradeLoading = false;
            this.getConfig();
            this.upLicenseDialogShow = true;
          })
          .catch((err) => {
            this.$message.warning(err.data && err.data.msg);
            this.upgradeLoading = false;
          });
      } else {
        // 配置升降级
        configApi(id)
          .then((res) => {
            this.upgradeList = res.data.data.upgrade_configoptions;
            this.upgradeHost = res.data.data.host;
            this.upgradeConfig = res.data.data.configoptions;
            this.upgradeSon_host = res.data.data.son_host;
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
          const temp = this.upgradeList[this.selectUpIndex * 1];
          this.basicInfo = temp.common_product;
          this.configoptions = temp.configoptions.filter(
            (item) => item.subs.length
          );
          this.custom_cycles = temp.custom_cycles;
          this.pay_type = temp.common_product?.pay_type;
          this.onetime =
            temp.cycles?.onetime === "-1.00" ? "0.00" : temp.cycles.onetime;
          // 初始化自定义配置参数
          const obj = this.configoptions.reduce((all, cur) => {
            all[cur.id] =
              cur.option_type === "multi_select" ||
              cur.option_type === "quantity" ||
              cur.option_type === "quantity_range"
                ? [
                    cur.option_type === "multi_select"
                      ? cur.subs[0].id
                      : cur.subs[0].qty_min,
                  ]
                : cur.subs[0].id;
            // 区域的时候保存国家
            if (cur.option_type === "area") {
              this.filterCountry[cur.id] = this.toTree(cur.subs);
              this.$set(this.curCountry, cur.id, 0);
            }
            return all;
          }, {});
          this.configForm = obj;
          // 处理费用周期
          if (this.pay_type === "onetime") {
            this.cycle = "onetime";
          } else if (this.pay_type === "free") {
            this.cycle = "free";
          } else {
            this.cycle = temp.custom_cycles[0].id;
          }
          /* 处理子商品 */
          this.originSon = temp.son;
          this.originSon &&
            temp.son.forEach((item, index) => {
              // 左侧展示的数据
              // 默认选中的周期
              this.sonCurCycle.push(0);
              this.upSon.push({
                open: true,
                basicInfo: item.common_product,
                configoptions: item.configoptions.filter(
                  (el) => el.subs.length
                ),
                custom_cycles: item.custom_cycles,
                pay_type: item.common_product.pay_type,
                onetime:
                  item.cycles.onetime === "-1.00"
                    ? "0.00"
                    : item.cycles.onetime,
              });
              // 初始化自定义配置参数
              const obj = item.configoptions
                .filter((el) => el.subs.length)
                .reduce((all, cur) => {
                  all[cur.id] =
                    cur.option_type === "multi_select" ||
                    cur.option_type === "quantity" ||
                    cur.option_type === "quantity_range"
                      ? [
                          cur.option_type === "multi_select"
                            ? cur.subs[0].id
                            : cur.subs[0].qty_min,
                        ]
                      : cur.subs[0].id;
                  // 区域的时候保存国家
                  if (cur.option_type === "area") {
                    this.sonCountry.push({ [cur.id]: this.toTree(cur.subs) });
                    this.sonCurCountry.push({ [cur.id]: 0 });
                  }
                  return all;
                }, {});
              this.sonConfigForm.push(obj);
              // 处理费用周期
              let sonC = "";
              if (item.common_product.pay_type === "onetime") {
                sonC = "onetime";
              } else if (item.common_product.pay_type === "free") {
                sonC = "free";
              } else {
                sonC = item.custom_cycles[0].id;
              }
              this.sonCycle.push(sonC);
            });
        } else {
          const temp = JSON.parse(JSON.stringify(this.upgradeList));
          this.configoptions = temp;
          // 初始化自定义配置参数
          const obj = this.configoptions.reduce((all, cur) => {
            if (cur.option_type === "multi_select") {
              const mulArr = this.upgradeConfig.reduce((sum, c) => {
                if (c.id === cur.id) {
                  sum.push(c.configoption_sub_id);
                }
                return sum;
              }, []);
              all[cur.id] = mulArr;
            } else if (cur.option_type === "quantity") {
              all[cur.id] = this.backfillId("quantity", cur.id);
            } else {
              all[cur.id] =
                cur.option_type === "quantity_range"
                  ? this.backfillId("quantity_range", cur.id)
                  : this.backfillId("id", cur.id);
            }
            // 区域的时候保存国家
            if (cur.option_type === "area") {
              this.filterCountry[cur.id] = this.toTree(cur.subs);
              const curItem = this.upgradeConfig.filter(
                (item) => item.id === cur.id
              );
              let index = this.filterCountry[cur.id].findIndex((item) =>
                item
                  .reduce((sumC, cc) => {
                    sumC.push(cc.id);
                    return sumC;
                  }, [])
                  .includes(curItem[0]?.configoption_sub_id * 1)
              );
              this.$set(this.curCountry, cur.id, index);
            }
            return all;
          }, {});
          this.backups = JSON.parse(JSON.stringify(obj));
          this.configForm = obj;
        }
        this.changeConfig();
      } catch (error) {
        console.log("error", error);
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
    goPay() {
      if (this.hostData.status === "Unpaid") {
        this.$refs.payDialog.showPayDialog(this.hostData.order_id);
      }
    },
    // 切换周期
    changeCycle(item, index) {
      this.cycle = item.id;
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
        if (tabVal === "1") {
          // 配置子商品的参数
          this.upSon.forEach((item, index) => {
            sonParams.push({
              config_options: {
                configoption: this.upFormatSubData(
                  this.sonConfigForm[index],
                  index
                ),
                cycle: this.sonCycle[index],
              },
              id: this.originSon[index].configoptions[0].product_id,
              qty: 1,
              buy: item.open,
            });
          });
          const params = {
            configoption: temp,
            cycle: this.cycle,
            son: sonParams,
            product_id:
              this.upgradeList[this.selectUpIndex * 1]?.configoptions[0]
                .product_id,
          };
          res = this.buy_id
            ? await upAppPrice(this.buy_host_id, params)
            : await upgradePrice(this.id, params);
          this.upData.price = res.data.data.upgrade_price; // 原单价
          this.pro_base_price = res.data.data.base_price; // 原单价  用于优惠码和用户等级
          // 重新计算周期显示
          const calculateParams = {
            config_options: {
              configoption: { ...temp },
              son: sonParams,
              cycle: this.cycle,
              host_id: this.buy_id ? this.buy_host_id : this.id,
            },
            qty: 1,
            id: this.upgradeList[this.selectUpIndex * 1]?.configoptions[0]
              .product_id,
          };
          const result = this.buy_id
            ? await buyCalculate(calculateParams)
            : await calculate(calculateParams);
          this.custom_cycles = result.data.data.custom_cycles;
          this.onetime = result.data.data.cycles.onetime;
          // 重新计算周期价格显示
          result.data.data.son ||
            [].forEach((el, ind) => {
              this.$set(this.upSon[ind], "custom_cycles", el.custom_cycles);
              this.$set(this.upSon[ind], "onetime", el.cycles.onetime);
            });
        } else {
          const temp1 = this.formatData();
          const params = { configoption: temp1, buy: this.isBuyServe };
          res = this.buy_id
            ? await syncAppPrice(this.buy_host_id, params)
            : await syncUpgradePrice(this.id, params);
          this.upData.price = res.data.data.price; // 原单价
          this.pro_base_price = res.data.data.price; // 原单价  用于优惠码和用户等级     // 原单价
        }
        if (this.isShowLevel) {
          // 计算折扣金额
          const discount = await clientLevelAmount({
            id:
              tabVal === "1"
                ? this.upgradeList[this.selectUpIndex * 1]?.configoptions[0]
                    .product_id
                : this.product_id,
            amount: this.pro_base_price,
          });
          this.upData.clDiscount = Number(discount.data.data.discount);
        }
        // 开启了优惠码插件
        if (this.isShowPromo) {
          // 更新优惠码
          await applyPromoCode({
            // 开启了优惠券
            scene: "upgrade",
            product_id:
              tabVal === "1"
                ? this.upgradeList[this.selectUpIndex * 1]?.configoptions[0]
                    .product_id
                : this.product_id,
            amount: this.pro_base_price,
            billing_cycle_time: this.host.billing_cycle_time,
            promo_code: "",
            host_id: this.id,
          })
            .then((resss) => {
              this.upData.code_discount = Number(resss.data.data.discount);
            })
            .catch((err) => {
              this.upData.code_discount = 0;
            });
        }
        console.log(this.upData);
        this.upData.totalPrice =
          (this.upData.price * 1000 -
            this.upData.clDiscount * 1000 -
            this.upData.code_discount * 1000) /
          1000;
        this.upPriceLoading = false;
      } catch (error) {
        console.log("error11111", error);
        this.upPriceLoading = false;
        this.dataLoading = false;
      }
    },
    formatData() {
      // 处理数量类型的转为数组
      const temp = JSON.parse(JSON.stringify(this.configForm));
      Object.keys(temp).forEach((el) => {
        const arr = this.configoptions.filter((item) => item.id * 1 === el * 1);
        if (arr.length !== 0) {
          if (
            arr[0].option_type === "quantity" ||
            arr[0].option_type === "quantity_range" ||
            arr[0].option_type === "multi_select"
          ) {
            if (typeof temp[el] !== "object") {
              temp[el] = [temp[el]];
            }
          }
        }
      });
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
        const temp = this.formatData();
        // 配置子商品的参数
        const sonParams = [];
        this.upSon.forEach((item, index) => {
          sonParams.push({
            config_options: {
              configoption: this.upFormatSubData(
                this.sonConfigForm[index],
                index
              ),
              cycle: this.sonCycle[index],
            },
            id: this.originSon[index].configoptions[0].product_id,
            qty: 1,
            buy: item.open,
          });
        });
        // 配置子商品的参数
        const params = {
          id: this.id,
          product_id:
            this.upgradeList[this.selectUpIndex * 1]?.configoptions[0]
              .product_id,
          config_options: {
            configoption: temp,
            cycle: this.cycle,
            son: sonParams,
          },
          qty: 1,
          customfield: {},
        };
        const upHostApi = this.buy_id ? upAppHost : upgradeHost;
        const id = this.buy_id ? this.buy_host_id : this.id;
        upHostApi(id, params)
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
    changeAutoStatus(e) {
      this.dialogVisible = true;
      this.autoTitle = this.isShowPayMsg
        ? lang.common_cloud_text242
        : lang.common_cloud_text243;
    },
    async changeAuto() {
      try {
        const params = {
          id: this.id,
          status: this.isShowPayMsg ? 0 : 1,
        };
        const res = await rennewAuto(params);
        this.$message.success(res.data.msg);
        this.dialogVisible = false;
        this.getRenewStatus();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    async getRenewStatus() {
      try {
        const res = await renewStatus({
          id: this.id,
        });
        this.isShowPayMsg = res.data.data.status;
      } catch (error) {}
    },
    async getPromo() {
      try {
        const res = await getPromoCode(this.id);
        this.promo_code = res.data.data.promo_code;
      } catch (error) {}
    },
    /* 备注 */
    async getComDetail() {
      try {
        const res = await getCommonDetail(this.id);
        this.hostData = res.data.data.host;
        this.self_defined_field = res.data.data.self_defined_field.map(
          (item) => {
            item.hidenPass = false;
            return item;
          }
        );
        this.product_id = res.data.data.host.product_id;
      } catch (error) {}
    },
    // 显示 修改备注 弹窗
    doEditNotes() {
      this.isShowNotesDialog = true;
      this.notesValue = this.hostData.notes;
    },
    // 修改备注提交
    async subNotes() {
      const params = {
        id: this.id,
        notes: this.notesValue,
      };
      try {
        const res = await changeNotes(params);
        this.$message.success(res.data.msg);
        this.isShowNotesDialog = false;
        this.getComDetail();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    notesDgClose() {
      this.isShowNotesDialog = false;
    },
    // 获取退款信息
    async getRefundInfo() {
      try {
        const res = await getRefundInfo(this.id);
        this.refundInfo = res.data.data.refund;
      } catch (error) {}
    },
    /* 停用 */
    async stop_use() {
      this.refundForm.str = "";
      this.refundForm.arr = [];
      this.refundForm.type = "Expire";
      this.refundMoney = "0.00";
      try {
        const res = await getRefund(this.id);
        this.refundDialog = res.data.data;
        // if (!this.refundDialog.allow_refund) {
        //   this.noRefundVisible = true
        //   return false
        // }
        this.refundVisible = true;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    changeReson(e) {
      this.refundMoney =
        e === "Immediate" ? this.refundDialog.host.amount : "0.00";
    },
    async submitRefund() {
      try {
        if (this.refundDialog.reason_custom) {
          // 自定义
          if (!this.refundForm.str) {
            return this.$message.error(lang.common_cloud_label44);
          }
        } else {
          if (this.refundForm.arr.length === 0) {
            return this.$message.error(lang.common_cloud_text58);
          }
        }
        if (!this.client_operate_password) {
          this.$refs.safeRef.openDialog("submitRefund");
          return;
        }
        const client_operate_password = this.client_operate_password;
        this.client_operate_password = "";

        const params = {
          host_id: this.id,
          type: this.refundForm.type,
          suspend_reason: this.refundDialog.reason_custom
            ? this.refundForm.str
            : this.refundForm.arr,
          client_operate_password,
        };
        this.loading = true;
        const res = await submitRefund(params);
        this.loading = false;
        this.$message.success(lang.common_cloud_text60);
        this.refundVisible = false;
        this.getRefundInfo();
      } catch (error) {
        this.loading = false;
        this.$message.error(error.data.msg);
      }
    },

    // 取消停用
    async cancelRefund() {
      if (!this.client_operate_password) {
        this.$refs.safeRef.openDialog("cancelRefund");
        return;
      }
      const client_operate_password = this.client_operate_password;
      this.client_operate_password = "";
      try {
        const res = await cancelRefund({
          id: this.refundInfo.id,
          client_operate_password,
        });
        this.$message.success(lang.common_cloud_text220);
        this.getRefundInfo();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },

    async getCountryList() {
      try {
        const res = await getCountry();
        this.countryList = res.data.data.list;
      } catch (error) {}
    },
    async getDetail() {
      try {
        const res = await getCommonListDetail(this.id);
        this.host = res.data.data.host;
        const temp = res.data.data.configoptions.map((item) => {
          item.show = false;
          return item;
        });
        this.firstInfo = temp;
        this.chartData = res.data.data.chart.map((item) => {
          item.selectValue = item.select[0]?.value || "";
          item.loading = false;
          return item;
        });
        this.client_area = res.data.data.client_area;
        this.client_button = res.data.data.client_button;
        this.powerList = res.data.data.client_button?.control || [];
        this.powerStatus = this.powerList[0]?.func || "";
        this.consoleList = res.data.data.client_button?.console || [];
        this.osData = res.data.data.os || {};
        if (this.osData.subs && this.osData.subs[0]) {
          this.osSelectData = this.osData.subs[0]?.version || [];
          this.osIcon =
            "/plugins/reserver/idcsmart_common/template/clientarea/pc/default/img/idcsmart_common/" +
              this.osData.subs[0]?.os +
              ".svg" || "";
          this.reinstallData.osGroupId = this.osData.subs[0]?.os || "";
          this.reinstallData.osId = this.osData.subs[0].version[0]?.id || "";
          this.selectOsObj = this.osData.subs[0]?.version[0] || {};
        }

        this.initLoading = false;
      } catch (error) {
        console.log(error);
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
      this.firstInfo[index].show = bol;
    },
    hideTip(index) {
      this.firstInfo[index].show = false;
    },
    back() {
      window.history.back();
    },
    // 每页展示数改变
    sizeChange(e) {
      this.params.limit = e;
      this.params.page = 1;
      // 获取列表
    },
    // 当前页改变
    currentChange(e) {
      this.params.page = e;
    },

    // 获取通用配置
    getCommonData() {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"));
      document.title =
        this.commonData.website_name + "-" + lang.common_cloud_text221;
    },
    // 使用优惠码
    async getDiscount(data) {
      this.customfield.promo_code = data[1];
      this.isUseDiscountCode = true;
      this.renewParams.code_discount = Number(data[0]);
      const price = this.renewParams.base_price;
      const discountParams = { id: this.product_id, amount: price };
      // // 开启了等级折扣插件
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
    removeDiscountCode() {
      this.isUseDiscountCode = false;
      this.customfield.promo_code = "";
      this.renewParams.code_discount = 0;
      this.renewParams.clDiscount = 0;
    },
    // 显示续费弹窗
    showRenew() {
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
        })
        .catch((err) => {
          this.renewLoading = false;
          this.$message.error(err.data.msg);
        });
    },
    // 续费使用代金券
    reUseCash(val) {
      this.cashObj = val;
      const price = val.price ? Number(val.price) : 0;
      this.renewParams.cash_discount = price;
      this.customfield.voucher_get_id = val.id;
    },
    // 续费移除代金券
    reRemoveCashCode() {
      this.$refs.cashRef.closePopver();
      this.cashObj = {};
      this.renewParams.cash_discount = 0;
      this.customfield.voucher_get_id = "";
    },
    // 续费弹窗关闭
    renewDgClose() {
      this.isShowRenew = false;
      this.removeDiscountCode();
      this.reRemoveCashCode();
    },
    getRenewPrice() {
      renewPage({ id: this.id })
        .then(async (res) => {
          if (res.data.status === 200) {
            this.renewPriceList = res.data.data.host;
          }
        })
        .catch((err) => {
          this.renewPriceList = [];
        });
    },
    // 续费提交
    subRenew() {
      this.client_operate_password = "";
      const params = {
        id: this.id,
        billing_cycle: this.renewParams.billing_cycle,
        customfield: this.customfield,
      };
      this.loading = true;
      renew(params)
        .then((res) => {
          if (res.data.status === 200) {
            if (res.data.code == "Paid") {
              this.$message.success(res.data.msg);
              this.getDetail();
              this.loading = false;
            }

            this.isShowRenew = false;
            this.renewOrderId = res.data.data.id;
            const orderId = res.data.data.id;
            const amount = this.renewParams.price;
            this.loading = false;
            this.$refs.payDialog.showPayDialog(orderId, amount);
          }
        })
        .catch((err) => {
          this.$message.error(err.data.msg);
          this.loading = false;
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
      if (this.isShowPromo && this.customfield.promo_code) {
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
          promo_code: this.customfield.promo_code,
        })
          .then((resss) => {
            this.isUseDiscountCode = true;
            this.renewParams.code_discount = Number(resss.data.data.discount);
          })
          .catch((err) => {
            this.$message.error(err.data.msg);
            this.removeDiscountCode();
          });
      }
      this.renewLoading = false;
    },

    // 支付成功回调
    paySuccess(e) {
      this.getDetail();
      this.getComDetail();
      console.log(e);
    },
    // 取消支付回调
    payCancel(e) {
      console.log(e);
    },
  },
}).$mount(template);
