const { showToast } = vant;
window.lang = Object.assign(window.lang, window.plugin_lang);

const app2 = Vue.createApp({
  components: {
    topMenu,
    vanSelect,
    discountCode,
    eventCode,
    customGoods,
  },
  mixins: [mixin],
  data() {
    return {
      lang: window.lang,
      finance_login: false,
      isShowLevel: false,
      isShowPromo: false,
      hasDiscount: false,
      showSystemDisk: false,
      showDiskPick: false,
      diskColumnsType: "system",
      showDiskPicker: false,
      isUseDiscountCode: false, // 是否使用优惠码
      id: "",
      tit: "",
      self_defined_field: {},
      commonData: {},
      autoIp: "172.16.0.0/12",
      showConfigPage: false,
      showNetPage: false,
      showFast: true,
      isShowImage: false,
      isShowRecommend: false,
      showNetPick: false,
      netColumns: [],
      editDiskIndex: 0,
      showVpcPick: false,
      flowName: "",
      showPackgeid: {},
      activeName: "fast", // fast, custom
      country: "",
      countryName: "",
      city: "",
      curImage: 0,
      imageName: "",
      version: "",
      curImageId: "",
      dataList: [], // 数据中心
      resourceList: [], // 资源包
      ressourceName: "",
      baseConfig: {},
      cpuList: [], //cpu
      gpuList: [],
      memoryList: [], // 内存
      memoryArr: [], // 范围时内存数组
      memMarks: {},
      bwMarks: {},
      memoryTip: "",
      limitList: [], // 限制
      packageId: "", // 套餐ID
      imageList: [], // 镜像
      systemDiskList: [], // 系统盘
      dataDiskList: [], // 数据盘
      configLimitList: [], // 限制规则
      cloudIndex: 0,
      clickIndex: 0,
      cycle: "", // 周期
      cycleList: [],
      qty: 1,
      recommendList: [], // 推荐套餐
      // 区域
      area_name: "",
      isChangeArea: true,
      lineList: [], // 线路
      lineType: "",
      lineDetail: {}, // 线路详情：bill_type, flow, bw, defence , ip
      lineName: "",
      bwName: "",
      defenseName: "",
      cpuName: "",
      gpu_name: "",
      show_gpu_name: "",
      memoryName: "",
      imageText: "",
      bwArr: [],
      bwTip: "",
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
        network_type: "",
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
          id: 0, // 选择已有的vc
          ips: "", // 自定义的时候
        },
        notes: "",
        auto_renew: false,
        // 资源包
        resource_package_id: "",
        ip_mac_bind_enable: false, // 嵌套虚拟化
        nat_acl_limit_enable: false, // NAT转发
        nat_web_limit_enable: false, // NAT建站
        ipv6_num_enable: false, // IPv6
      },
      plan_way: 0,
      hover: false,
      login_way: lang.auto_create, // 登录方式 auto_create
      rules: {
        data_center_id: [
          { required: true, message: "请输入活动名称", trigger: "blur" },
        ],
        name: [
          {
            pattern: /^[A-Za-z][a-zA-Z0-9_.-]{5,24}$/,
            message: lang.mf_tip16,
          },
        ],
      },
      sshList: [],
      dis_visible: false,
      // 配置价格
      loadingPrice: true,
      totalPrice: 0.0,
      preview: [],
      discount: "",
      duration: "",
      /* 优惠码 */
      promo: {
        scene: "new",
        promo_code: "",
        billing_cycle_time: "",
        event_promotion: "",
      },
      cartDialog: false,
      isInit: true,
      memoryType: false,
      /* 拖动内存 */
      mStep: 1,
      mMin: "",
      mMax: "",
      /* 存储 */
      storeList: [],
      systemType: [],
      dataType: [],
      systemNum: [],
      areText: "",
      areImg: "",
      dataNumObj: {},
      systemRangArr: {}, // 系统盘不同类型的取值范围数组
      systemRangTip: {}, // 系统盘不同类型的取值范围提示
      dataRangArr: {}, // 数据盘不同类型的取值范围数组
      dataRangTip: {}, // 数据盘不同类型的取值范围提示
      //验证密码
      hasLen: false,
      hasAppoint: true, // 只能输入
      hasLine: false,
      hasMust: false, // 必须包含必须包含小写字母a~z，大写字母A~Z,字母0-9
      /* 安全组 */
      groupName: lang.no_safe_group,
      groupList: [],
      groupSelect: [
        { value: "icmp", name: lang.icmp_name, check: true },
        { value: "ssh", name: lang.ssh_name, check: true },
        { value: "rdp", name: lang.rdp_name, check: true },
        { value: "http", name: lang.http_name, check: true },
        { value: "https", name: lang.https_name, check: true },
        { value: "telnet", name: lang.telnet_name, check: true },
      ],
      /* 网络类型 */
      netName: "",
      showLoginPage: false,
      /* vpc */
      vpcList: [],
      calcTotalPrice: 0,
      vpc_ips: {
        vpc1: {
          tips: lang.range1,
          value: 10,
          select: [
            {
              label: 10,
              value: 10,
            },
            {
              label: 172,
              value: 172,
            },
            {
              label: 192,
              value: 192,
            },
          ],
        },
        vpc2: 0,
        vpc3: 0,
        vpc3Tips: "",
        vpc4: 0,
        vpc4Tips: "",
        vpc6: {
          value: 16,
          select: [
            {
              label: 16,
              value: 16,
            },
            {
              label: 17,
              value: 17,
            },
            {
              label: 18,
              value: 18,
            },
            {
              label: 19,
              value: 19,
            },
            {
              label: 20,
              value: 20,
            },
            {
              label: 21,
              value: 21,
            },
            {
              label: 22,
              value: 22,
            },
            {
              label: 23,
              value: 23,
            },
            {
              label: 24,
              value: 24,
            },
            {
              label: 25,
              value: 25,
            },
            {
              label: 26,
              value: 26,
            },
            {
              label: 27,
              value: 27,
            },
            {
              label: 28,
              value: 28,
            },
          ],
        },
        min: 0,
        max: 255,
      },
      // 回调相关
      isUpdate: false,
      position: 0,
      backfill: {},
      isLogin: localStorage.getItem("jwt"),
      showErr: false,
      sshLoading: false,
      groupLoading: false,
      vpcLoading: false,
      showImage: false,
      showSsh: false,
      showPas: false,
      showRepass: false,
      isHide: true,
      levelNum: 0,
      eventDiscount: 0,
      base_price: "",
      showInfo: [],
      activeNames: [],
      dataLoading: false,
      isShowBtn: false,
      submitLoading: false,
      isShowTop: false,
      hasScroll: false,
      hasTopScroll: false,
      showImgPick: false,
      showPlanPick: false,
      planList: [
        { value: 0, label: lang.auto_plan },
        { value: 1, label: lang.custom },
      ],
      selectDuration: {},
      isShowDur: false,
      showLinePick: false,
      initImage: false, // 是否默认选择第一个镜像
      isCustom: false,
    };
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
    this.getConfig();
    this.hasDiscount = arr.includes("PromoCode");

    window.addEventListener("message", (event) => this.submitOrder(event));
  },
  updated() {
    this.isShowBtn = true;
    if (this.activeName === "fast") {
      return;
    }
    // this.$nextTick(() => {
    //     this.groupSelect.forEach((item, index) => {
    //         const dom = this.$refs[`safe${index}`][0].$el;
    //         item.disabled =
    //             dom.offsetWidth >
    //             dom.getElementsByClassName("safe-item")[0].offsetWidth + 30;
    //     });
    // });
  },
  destroyed() {},

  filters: {
    formateTime(time) {
      if (time && time !== 0) {
        return formateDate(time * 1000);
      } else {
        return "--";
      }
    },
  },
  created() {
    this.id = this.getQuery("id");
    this.isUpdate = this.getQuery("change");
    this.isLogin = localStorage.getItem("jwt");
    this.getCommonData();
    this.getGoodsName();
    this.getIamgeList();
    let temp = "";
    temp = JSON.parse(sessionStorage.getItem("product_information"));
    if (this.isUpdate && temp.config_options) {
      this.backfill = temp.config_options;
      temp.config_options.auto_renew = temp.config_options.auto_renew
        ? true
        : false;
      temp.config_options.ip_mac_bind_enable = temp.config_options
        .ip_mac_bind_enable
        ? true
        : false;
      temp.config_options.nat_acl_limit_enable = temp.config_options
        .nat_acl_limit_enable
        ? true
        : false;
      temp.config_options.nat_web_limit_enable = temp.config_options
        .nat_web_limit_enable
        ? true
        : false;
      temp.config_options.ipv6_num_enable = temp.config_options.ipv6_num_enable
        ? true
        : false;
      this.isChangeArea = false;
      const {
        country,
        countryName,
        city,
        curImage,
        version,
        curImageId,
        cloudIndex,
        activeName,
        imageName,
        network_type,
        peak_defence,
        security_group_id,
        security_group_protocol,
        login_way,
        recommend_config_id,
        groupName,
        data_center_id,
      } = this.backfill;
      this.packageId = recommend_config_id;
      this.promo = temp.customfield;
      this.self_defined_field = temp.self_defined_field || {};
      this.qty = temp.qty;
      this.position = temp.position;
      this.activeName = activeName;
      this.country = country;
      this.countryName = countryName;
      this.curImage = curImage;
      this.city = city;
      this.version = version;
      this.curImageId = curImageId;
      this.cloudIndex = cloudIndex;
      this.imageName = imageName;
      this.imageText = imageName;
      this.groupName = groupName;
      this.netName = network_type === "vpc" ? lang.mf_vpc : lang.mf_normal;
      if (network_type === "vpc") {
        this.getVpcList(data_center_id);
      }
      this.params.vpc.id = temp.config_options.vpc.id;
      const ips = temp.config_options.vpc.ips;
      this.plan_way = ips ? 1 : 0;
      if (ips) {
        const arr = ips.split("/");
        const arr1 = arr[0].split(".");
        this.vpc_ips.vpc1.value = arr1[0] * 1;
        this.vpc_ips.vpc2 = arr1[1] * 1;
        this.vpc_ips.vpc3 = arr1[2] * 1;
        this.vpc_ips.vpc4 = arr1[3] * 1;
        this.vpc_ips.vpc6.value = arr[1] * 1;
      }
      this.defenseName =
        peak_defence == 0 ? lang.no_defense : peak_defence + "G";
      // 安全组
      if (security_group_id) {
        this.getGroup();
      }
      if (security_group_protocol.length > 0) {
        this.groupSelect = this.groupSelect.map((item) => {
          if (security_group_protocol.includes(item.value)) {
            item.check = true;
          }
          return item;
        });
      }
      // 登录方式
      this.login_way = login_way;
      if (login_way === lang.security_tab1) {
        this.getSsh();
      }
    }
  },
  watch: {
    "params.image_id"(id) {
      if (id) {
        this.showImage = false;
      }
    },
    dis_visible(val) {
      if (!val) {
        this.showErr = false;
      }
    },
    "params.network_type"(type) {
      this.netName = type === "normal" ? lang.mf_normal : lang.mf_vpc;
    },
    // 系统盘改变类型，筛选数量可选
    "params.system_disk.disk_type"(val) {
      if (this.activeName === "fast") {
        return;
      }
      if (this.systemDiskList[0].type === "radio") {
        // 单选
        this.systemNum = this.systemDiskList
          .filter((item) => item.other_config.disk_type === val)
          .reduce((all, cur) => {
            all.push({
              value: cur.value,
              label: cur.value,
            });
            return all;
          }, []);
        // 回填初次不初始化
        if (this.isInit && this.isUpdate) {
          return;
        }
        this.params.system_disk.size = this.systemNum[0].value;
      } else {
        // 范围
        this.storeList[0].disk_type = val;
        this.storeList[0].min = this.systemRangArr[val][0];
        this.storeList[0].max =
          this.systemRangArr[val][this.systemRangArr[val].length - 1];
        // 回填初次不初始化
        if (this.isInit && this.isUpdate) {
          return;
        }
        this.params.system_disk.size = this.systemRangArr[val][0];
      }
      if (!this.isInit) {
        this.getCycleList();
      }
    },
    "params.line_id"(id) {
      // 区域改变，线路必定改变，根据线路改变拉取线路详情，以及处理cpu,memory,bw/flow
      if (id && this.activeName === "custom") {
        this.lineType = this.lineList.filter(
          (item) => item.id === this.params.line_id
        )[0]?.bill_type;
        this.getLineDetails(id);
      }
    },
    "params.ssh_key_id"(id) {
      if (id) {
        this.showSsh = false;
      }
    },
    vpcIps: {
      handler(newVal) {
        this.params.vpc.ips = newVal;
      },
      immediate: true,
      deep: true,
    },
  },
  computed: {
    sshKeyText() {
      return this.sshList.filter((item) => {
        return item.id === this.params.ssh_key_id;
      })[0]?.name;
    },
    groupText() {
      return this.groupList.filter((item) => {
        return item.id === this.params.security_group_id;
      })[0]?.name;
    },
    vpcText() {
      return this.vpcList.filter((item) => {
        return item.id === this.params.vpc.id;
      })[0]?.name;
    },
    planText() {
      return this.plan_way === 0 ? lang.auto_plan : lang.custom;
    },
    radioColumns() {
      let arr = [];
      if (this.params.data_disk[this.editDiskIndex]) {
        arr =
          this.dataNumObj[this.params.data_disk[this.editDiskIndex].disk_type];
      }
      return this.diskColumnsType === "system" ? this.systemNum : arr;
    },
    root_name() {
      return this.imageName.toUpperCase().indexOf("WIN") !== -1 ? "administrator" : "root";
    },
    calcMax() {
      // 计算数据盘最大添加的数量，最大16 ，+ 1 是系统盘
      const all = this.baseConfig.disk_limit_num + 1;
      return (index) => {
        const num = this.storeList.reduce((all, cur, ind) => {
          if (index !== ind) {
            all += cur.num * 1;
          }
          return all;
        }, 0);
        return all - num;
      };
    },
    calcAllNum() {
      return this.storeList.reduce((all, cur) => {
        all += cur.num * 1;
        return all;
      }, 0);
    },

    calcArea() {
      const c = this.dataList.filter((item) => item.id === this.country * 1)[0]
        ?.name;
      return c + this.city;
    },
    calcAreaList() {
      // 计算区域列表
      if (this.activeName === "fast" || this.isCustom) {
        return;
      }
      const temp =
        this.dataList
          .filter((item) => item.id === this.country * 1)[0]
          ?.city.filter((item) => item.name === this.city)[0]?.area || [];
      if (!this.isChangeArea) {
        return temp;
      }
      this.area_name = temp[0]?.name;
      this.lineList = temp[0]?.line || [];
      this.params.data_center_id = this.lineList[0]?.data_center_id;
      this.params.line_id = this.lineList[0]?.id;
      this.lineName = this.lineList[0]?.name;
      // 区域变化，重置cpu, 内存
      this.params.cpu = this.cpuList[0]?.value;
      this.cpuName = this.params.cpu + lang.mf_cores;
      if (this.memoryList[0]?.type === "radio") {
        this.params.memory = this.calaMemoryList[0]?.value * 1;
      } else {
        this.params.memory = this.calaMemoryList[0] * 1 || null;
      }
      this.memoryName =
        this.calaMemoryList[0]?.value + this.baseConfig.memory_unit;
      if (!this.baseConfig.support_normal_network) {
        this.getVpcList();
      }
      return temp;
    },
    calcCpu() {
      return this.params.cpu + lang.mf_cores;
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
    calcUsable() {
      return this.dataList
        .filter((item) => item.id === this.country * 1)[0]
        ?.city.filter((item) => item.name === this.city)[0]
        ?.area.filter((item) => item.id === this.params.data_center_id)[0]
        ?.name;
    },
    calcLine() {
      return this.dataList
        .filter((item) => item.id === this.country * 1)[0]
        ?.city.filter((item) => item.name === this.city)[0]
        ?.area.filter((item) => item.id === this.params.data_center_id)[0]
        ?.line.filter((item) => item.id === this.params.line_id)[0]?.name;
    },
    calcCpuList() {
      // 根据区域来判断计算可选cpu数据
      if (this.activeName === "fast") {
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
      if (this.activeName === "fast") {
        return;
      }

      const temp = this.configLimitList.filter((item) => {
        if (item.type === "data_center") {
          return (
            item.data_center_id === this.params.data_center_id &&
            item.cpu.split(",").includes(String(this.params.cpu))
          );
        } else {
          return item.cpu.split(",").includes(String(this.params.cpu));
        }
      });

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
    calcCartName() {
      return this.isUpdate ? lang.product_sure_check : lang.product_add_cart;
    },
    passColumns() {
      const arr = [
        {
          label: lang.set_pas,
          value: lang.set_pas,
        },
        {
          label: lang.auto_create,
          value: lang.auto_create,
        },
      ];
      if (
        this.baseConfig.support_ssh_key &&
        this.imageName.indexOf("Win") === -1
      ) {
        arr.unshift({
          label: lang.security_tab1,
          value: lang.security_tab1,
        });
      }
      return arr;
    },
    calcDataNum() {
      return this.params.data_disk.reduce((all, cur) => {
        all += cur.size;
        return all;
      }, 0);
    },
    calcImageList() {
      const temp =
        this.imageList.filter((item) => item.id === this.curImageId)[0]
          ?.image || [];
      // 回填的时候不能直接选择第一个
      setTimeout(() => {
        if (!this.isUpdate || this.initImage) {
          this.params.image_id = temp[0]?.id;
          this.imageName = temp[0]?.name;
        }
        this.initImage = true;
      }, 0);

      return temp;
    },
    // 镜像icon
    curImageIcon() {
      return this.imageList.filter((item) => item.id === this.curImageId)[0]
        ?.icon;
    },
  },
  methods: {
    handelDiskPick(type) {
      if (type === "system") {
        this.diskColumnsType = "system";
      } else {
        this.diskColumnsType = "dataType";
      }
      this.showDiskPicker = true;
    },
    filterMoney(money) {
      if (isNaN(money)) {
        return "0.00";
      } else {
        const temp = `${money}`.split(".");
        return parseInt(temp[0]).toLocaleString() + "." + (temp[1] || "00");
      }
    },

    formatSwitch(data) {
      data.config_options.auto_renew = data.config_options.auto_renew ? 1 : 0;
      data.config_options.ip_mac_bind_enable = data.config_options
        .ip_mac_bind_enable
        ? 1
        : 0;
      data.config_options.nat_acl_limit_enable = data.config_options
        .nat_acl_limit_enable
        ? 1
        : 0;
      data.config_options.nat_web_limit_enable = data.config_options
        .nat_web_limit_enable
        ? 1
        : 0;
      data.config_options.ipv6_num_enable = data.config_options.ipv6_num_enable
        ? 1
        : 0;
      return data;
    },
    changeNat(e) {
      if (e) {
        this.params.ipv6_num_enable = false;
      }
    },
    changeIpv6(e) {
      if (e) {
        this.params.nat_acl_limit_enable = false;
        this.params.nat_web_limit_enable = false;
      }
    },
    showScroll() {
      setTimeout(() => {
        const el = document.getElementsByClassName("el-main")[0];
        this.hasScroll = el.scrollHeight > el.clientHeight;
      }, 100);
    },
    goTop() {
      const el = document.getElementsByClassName("el-main")[0];
      el.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    },
    // 超出才显示tooltip
    checkWidth() {
      const boxWidth = this.$refs["tooltipBox"].offsetWidth;
      const itemWidth = this.$refs["tooltipItem"].offsetWidth;
      this.showTooltip = boxWidth > itemWidth;
    },
    getQuery(name) {
      const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
      const r = window.location.search.substr(1).match(reg);
      if (r != null) return decodeURI(r[2]);
      return null;
    },
    // 配置数据
    async getConfig() {
      try {
        const params = {
          id: this.id,
        };
        if (this.activeName === "fast") {
          params.scene = "recommend";
        } else {
          this.isCustom = true;
        }
        const res = await getOrderConfig(params);
        const temp = res.data.data;
        // 通用数据处理
        this.dataList = temp.data_center.map((item) => {
          return {
            children: item.city.map((is) => {
              return { id: is.name, ...is };
            }),
            ...item,
          };
        });
        console.log(this.dataList);
        this.resourceList = temp.resource_package;
        this.baseConfig = temp.config;
        this.netColumns = [];
        if (this.baseConfig.support_normal_network) {
          this.netColumns.push({
            label: lang.mf_normal,
            value: lang.mf_normal,
          });
        }
        if (this.baseConfig.support_vpc_network) {
          this.netColumns.push({
            label: lang.mf_vpc,
            value: lang.mf_vpc,
          });
        }

        // 如果没有推荐配置，跳转到自定义，重新获取数据
        if (this.dataList.length === 0) {
          this.activeName = "custom";
          this.showFast = false;
          this.getConfig();
          return;
        }
        // 初始化数据
        if (!this.isUpdate) {
          // 不是回填
          this.params = {
            data_center_id: "",
            cpu: "",
            memory: 1,
            image_id: this.imageList[0]?.image[0]?.id,
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
            network_type:
              this.baseConfig.type !== "host"
                ? "normal"
                : this.baseConfig.support_normal_network
                ? "normal"
                : "vpc",
            name: "",
            ssh_key_id: "",
            security_group_id: "",
            security_group_protocol: [],
            password: "",
            re_password: "",
            vpc: {
              id: "",
              ips: "",
            },
            notes: "",
            auto_renew: false,
            resource_package_id: this.resourceList[0]?.id || "",
            ip_mac_bind_enable: false,
            nat_acl_limit_enable: false,
            nat_web_limit_enable: false,
            ipv6_num_enable: false,
          };
          this.qty = 1;
          this.ressourceName = this.resourceList[0]?.name;
          this.country = String(this.dataList[0]?.id);
          this.countryName = String(this.dataList[0]?.name);
          this.city = String(this.dataList[0]?.city[0]?.name);
          this.areText =
            String(this.dataList[0]?.name) +
            "," +
            String(this.dataList[0]?.city[0]?.name);
          this.areImg = this.dataList[0]?.iso;
          this.cloudIndex = 0;
          this.plan_way = 0;
          this.login_way = lang.auto_create;
          this.createPassword();
          this.isCustom = false;
          /* 根据后台设置的默认 nat 开关 + vpc 等条件判断选中 */
          if (
            this.params.network_type === "vpc" ||
            (this.params.network_type === "normal" &&
              this.baseConfig.type === "lightHost")
          ) {
            if (this.baseConfig.default_nat_acl) {
              this.params.nat_acl_limit_enable = true;
            }
            if (this.baseConfig.default_nat_web) {
              this.params.nat_web_limit_enable = true;
            }
          }
        } else {
          // 回填数据
          this.params = this.backfill;
          console.log(this.params);
          this.ressourceName = this.resourceList.filter(
            (item) => item.id === this.params.resource_package_id
          )[0]?.name;

          const countryItem = this.dataList.filter(
            (item) => item.id * 1 == this.params.country * 1
          );
          this.areImg = countryItem[0].iso;
          this.country = countryItem[0].id + "";
          this.city = this.params.city;
          this.areText = this.params.countryName + "," + this.params.city;
        }

        this.totalPrice = 0.0;
        this.isInit = true;
        // 保存cpu,memory,system_disk,data_disk,config_limit
        this.cpuList = temp.cpu;
        this.memoryList = temp.memory;
        if (temp.memory.length > 0 && temp.memory[0].type !== "radio") {
          // 范围的时候生成默认范围数组
          this.memoryArr = temp.memory.reduce((all, cur) => {
            all.push(...this.createArr([cur.min_value, cur.max_value]));
            return all;
          }, []);
        }
        if (this.memoryList.length > 0) {
          if (this.memoryList[0].type === "radio") {
            this.memoryType = true;
          } else {
            this.memoryType = false;
          }
        }
        this.systemDiskList = temp.system_disk;
        this.dataDiskList = temp.data_disk;
        this.configLimitList = temp.config_limit;
        // 处理存储
        this.handlerType(temp.system_disk, "system");
        this.handlerType(temp.data_disk, "data");
        // fast 推荐配置
        if (this.activeName === "fast") {
          this.handlerFast();
        } else {
          this.isCustom = false;
          this.handlerCustom();
        }
      } catch (error) {
        console.log("@@@", error);
      }
    },
    // 处理套餐配置
    handlerFast() {
      if (this.activeName === "custom") {
        return;
      }
      const temp = this.dataList
        .filter((item) => item.id === this.country * 1)[0]
        ?.city.filter((item) => item.name === this.city)[0]
        ?.area.reduce((all, cur) => {
          all.push(...cur.recommend_config);
          return all;
        }, []);
      this.recommendList = temp;
      // 初始化套餐数据
      if (!this.isUpdate) {
        this.packageId = temp[0].id;
        this.params.data_center_id = temp[0].data_center_id;
        this.params.cpu = temp[0].cpu;
        this.params.memory = temp[0].memory * 1 || 0;
        // this.params.network_type = temp[0].network_type;
        this.params.line_id = temp[0].line_id;
        this.lineType = temp[0].bw ? "bw" : "flow";
        this.params.bw = temp[0].bw;
        this.params.flow = temp[0].flow;
        this.params.peak_defence = temp[0].peak_defence;
        this.params.system_disk.size = temp[0].system_disk_size;
        this.params.system_disk.disk_type = temp[0].system_disk_type;
        this.gpu_name = temp[0].gpu_name;
        this.params.gpu_num = temp[0].gpu_num;
        this.show_gpu_name = temp[0].gpu_num + "*" + temp[0].gpu_name;

        if (temp[0].data_disk_size * 1) {
          this.params.data_disk = [];
          this.params.data_disk.push({
            size: temp[0].data_disk_size,
            disk_type: temp[0].data_disk_type,
          });
        } else {
          this.params.data_disk = [];
        }
        this.showPackgeid = temp[0];
      } else {
        this.showPackgeid = temp.filter(
          (item) => item.id * 1 === this.packageId * 1
        )[0];

        this.gpu_name = temp.filter(
          (item) => item.gpu_num === this.params.gpu_num
        )[0]?.gpu_name;
      }
      this.lineType = this.params.bw ? "bw" : "flow";
      // 计算价格
      setTimeout(() => {
        // console.log('@@fr', this.params)
        this.getCycleList();
      }, 0);
    },
    // 切换自定义配置
    handlerCustom() {
      if (this.baseConfig.only_sale_recommend_config === 1) {
        // 仅购买套餐
        return;
      }
      if (!this.isUpdate) {
        this.createPassword();
        this.storeList = [];
        // 默认第一个系统盘类型
        this.params.system_disk.disk_type = this.systemType[0].value;
        this.params.system_disk.disk_text = this.systemType[0].label;

        this.params.system_disk.size =
          this.systemDiskList[0].value || this.systemDiskList[0].min_value;
        if (this.systemDiskList[0].type === "radio") {
          // 单选
          this.systemNum = this.systemDiskList
            .filter(
              (item) =>
                item.other_config.disk_type ===
                this.params.system_disk.disk_type
            )
            .reduce((all, cur) => {
              all.push({
                value: cur.value,
                label: cur.value,
              });
              return all;
            }, []);
        }
        // 根据类型确定最大最小值
        this.storeList.push({
          type: this.systemDiskList[0].type,
          name: lang.mf_system,
          disk_type: this.systemType[0].value || "",
          disk_text: this.systemType[0].label,
          size:
            this.systemDiskList[0].value || this.systemDiskList[0].min_value,
          min: this.systemDiskList[0].min_value,
          max: this.systemDiskList[this.systemDiskList.length - 1].max_value,
          num: 1,
        });
        // 如果有免费数据盘
        if (this.baseConfig.free_disk_switch) {
          this.storeList.push({
            min: this.baseConfig.free_disk_size,
            max: this.baseConfig.free_disk_size,
            type: "",
            name: lang.mf_tip37,
            disk_type: "",
            size: this.baseConfig.free_disk_size,
            num: 1,
          });
          this.params.data_disk.push({
            // 提交的时候，根据 baseConfig.free_disk_switch 是否删除第一个数据盘
            disk_type: "",
            size: this.baseConfig.free_disk_size,
          });
        }

        // 默认选择cpu 内存
        this.params.cpu = this.cpuList[0]?.value;
        this.cpuName = this.params.cpu + lang.mf_cores;
        if (this.memoryList[0].type === "radio") {
          this.params.memory = this.calaMemoryList[0]?.value * 1;
        } else {
          this.params.memory = this.calaMemoryList[0] * 1;
        }
        this.memoryName =
          this.calaMemoryList[0]?.value + this.baseConfig.memory_unit;
      } else {
        // 回填
        this.area_name = this.calcAreaList.filter(
          (item) => item.id === this.params.data_center_id
        )[0]?.name;
        const temp =
          this.dataList
            .filter((item) => item.id === this.country * 1)[0]
            ?.city.filter((item) => item.name === this.city)[0]?.area || [];
        this.lineList = temp.filter(
          (item) => item.name === this.area_name
        )[0]?.line;
        this.lineName = this.lineList.filter(
          (item) => item.id === this.params.line_id
        )[0]?.name;
        this.cpuName = this.params.cpu + lang.mf_cores;
        this.memoryName = this.params.memory * 1 + this.baseConfig.memory_unit;
        // 处理存储
        // 系统盘
        let arr = [];
        arr.push({
          type: this.systemDiskList[0].type,
          name: lang.mf_system,
          disk_type: this.params.system_disk.disk_type,
          size: this.params.system_disk.size,
        });
        // 数据盘
        if (this.params.data_disk.length > 0) {
          this.params.data_disk.forEach((item) => {
            arr.push({
              min: this.dataDiskList[0].min_value,
              max: this.dataDiskList[this.dataDiskList.length - 1].max_value,
              type: this.dataDiskList[0].type,
              name: lang.common_cloud_text1,
              disk_type: item.disk_type,
              size: item.size,
              num: item.num,
            });
          });
        }
        this.storeList = arr;
      }
    },
    /* 线路 */
    changeLine(e) {
      this.params.line_id = this.lineList.filter(
        (item) => item.name === e
      )[0]?.id;
    },
    async getLineDetails(id) {
      try {
        if (!id) {
          return;
        }

        // 获取线路详情，
        const res = await getLineDetail({ id: this.id, line_id: id });
        this.lineDetail = res.data.data;
        this.defenseName = "";
        this.params.peak_defence = "";

        if (this.lineDetail.bw) {
          if (this.isInit && this.isUpdate) {
            // 初次回填
          } else {
            this.params.bw =
              this.lineDetail.bw[0]?.value || this.lineDetail.bw[0]?.min_value;
          }

          this.bwName = this.params.bw + "M";
          // 循环生成带宽可选数组
          const fArr = [];
          this.lineDetail.bw.forEach((item) => {
            fArr.push(...this.createArr([item.min_value, item.max_value]));
          });
          this.bwArr = fArr;
          this.bwTip = this.createTip(fArr);
        }
        if (this.lineDetail.flow) {
          if (this.isInit && this.isUpdate) {
            // 初次回填
          } else {
            this.params.flow = this.lineDetail.flow[0]?.value;
          }
          this.flowName =
            this.params.flow > 0 ? this.params.flow + "G" : lang.mf_tip28;
        }
        if (this.lineDetail.defence) {
          if (this.isInit && this.isUpdate) {
            // 初次回填
          } else {
            this.params.peak_defence = this.lineDetail.defence[0]?.value;
          }
          this.defenseName =
            this.params.peak_defence == 0
              ? lang.no_defense
              : this.params.peak_defence + "G";
        } else {
          this.defenseName = "";
          this.params.peak_defence = "";
        }
        // gpu
        if (this.lineDetail.gpu) {
          this.gpuList = this.lineDetail.gpu;
          this.gpu_name = this.lineDetail.gpu_name;
          if (this.isInit && this.isUpdate) {
            // 初次回填
          } else {
            this.params.gpu_num = this.lineDetail.gpu[0]?.value;
          }
          this.show_gpu_name =
          this.params.gpu_num + "*" + this.lineDetail.gpu_name;
        } else {
          this.params.gpu_num = "";
          this.gpu_name = "";
          this.show_gpu_name = "";
        }
        this.bwMarks = this.createMarks(this.bwArr);
        setTimeout(() => {
          this.getCycleList();
        }, 0);
      } catch (error) {
        console.log("####", error);
      }
    },
    changeBw(e) {
      this.params.bw = e[0].value;
      this.bwName = this.params.bw + "M";
      // 计算价格
      setTimeout(() => {
        this.getCycleList();
      }, 0);
    },
    handelSelectImg() {
      const e = this.getSelectValue("selectPopRef");
      this.params.image_id = e[0].id;
      this.imageText = e[0].name;
      this.showImgPick = false;
      this.isShowImage = false;
      this.getCycleList();
    },
    getSelectValue(refName) {
      return this.$refs[refName].getSelectedOptions();
    },
    changeBwNum(num) {
      if (window.bwTimer) {
        clearTimeout(window.bwTimer);
        window.bwTimer = null;
      }
      window.bwTimer = setTimeout(() => {
        if (!this.bwArr.includes(num)) {
          this.bwArr.forEach((item, index) => {
            if (num > item && num < this.bwArr[index + 1]) {
              this.params.bw =
                num - item > this.bwArr[index + 1] - num
                  ? this.bwArr[index + 1]
                  : item;
            }
          });
        }
        this.getCycleList();
      }, 300);
    },
    // 选中/取消防御
    chooseDefence(c) {
      if (c && c[0]) {
        this.defenseName = c[0].value == 0 ? lang.no_defense : c[0].value + "G";
        this.params.peak_defence = c[0].value;
      } else {
        this.defenseName = "";
        this.params.peak_defence = "";
      }
      setTimeout(() => {
        this.getCycleList();
      }, 0);
    },
    // 选择ssh
    chooseSshKey(e) {
      this.params.ssh_key_id = e[0].id;
    },
    // 切换流量
    changeFlow(e) {
      console.log(e[0]);
      if (e[0].value > 0) {
        this.params.flow = e[0].value * 1;
        this.flowName = this.params.flow + "G";
      } else {
        this.params.flow = 0;
        this.flowName = lang.mf_tip28;
      }
      console.log(this.flowName);
      setTimeout(() => {
        this.getCycleList();
      }, 0);
    },
    // 切换内存
    changeMemory(e) {
      this.memoryName = e[0].value + this.baseConfig.memory_unit;
      this.params.memory = e[0].value;
      setTimeout(() => {
        this.getCycleList();
      }, 0);
    },
    changeGpu(e) {
      this.params.gpu_num = e[0].value;
      this.show_gpu_name = e[0].value + "*" + this.gpu_name;
      setTimeout(() => {
        this.getCycleList();
      }, 0);
    },
    createArr([m, n]) {
      // 生成数组
      let temp = [];
      for (let i = m; i <= n; i++) {
        temp.push(i);
      }
      return temp;
    },
    createTip(arr) {
      // 生成范围提示
      let tip = "";
      let num = [];
      arr.forEach((item, index) => {
        if (arr[index + 1] - item > 1) {
          num.push(index);
        }
      });
      if (num.length === 0) {
        tip = `${arr[0]}-${arr[arr.length - 1]}`;
      } else {
        tip += `${arr[0]}-${arr[num[0]]},`;
        num.forEach((item, ind) => {
          tip +=
            arr[item + 1] +
            "-" +
            (arr[num[ind + 1]] ? arr[num[ind + 1]] + "," : arr[arr.length - 1]);
        });
      }
      return tip;
    },
    createMarks(data) {
      const obj = {
        0: "",
        25: "",
        50: "",
        75: "",
        100: "",
      };
      const range = data[data.length - 1] - data[0];
      obj[0] = `${data[0]}`;
      obj[25] = `${data[0] + Math.ceil(range * 0.25)}`;
      obj[50] = `${data[0] + Math.ceil(range * 0.5)}`;
      obj[75] = `${data[0] + Math.ceil(range * 0.75)}`;
      obj[100] = `${data[data.length - 1]}`;
      return obj;
    },
    /* 网络类型 */
    changeNet(e) {
      this.params.network_type = e === lang.mf_normal ? "normal" : "vpc";
      if (this.params.network_type === "vpc") {
        if (this.vpcList.length === 0) {
          this.getVpcList();
        } else {
          this.params.vpc.id = this.params.vpc.id || this.vpcList[0]?.id || "";
          this.plan_way = this.plan_way || 0;
        }
      }
      /* 根据后台设置的默认 nat 开关 + vpc 等条件判断选中 */
      if (
        this.params.network_type === "vpc" ||
        (this.params.network_type === "normal" &&
          this.baseConfig.type === "lightHost")
      ) {
        if (this.baseConfig.default_nat_acl) {
          this.params.nat_acl_limit_enable = true;
        }
        if (this.baseConfig.default_nat_web) {
          this.params.nat_web_limit_enable = true;
        }
      }
    },
    // 获取vpc
    async getVpcList() {
      try {
        this.vpcLoading = true;
        const res = await getVpc({
          id: this.id,
          data_center_id: this.params.data_center_id,
          page: 1,
          limit: 1000,
        });
        this.vpcList = res.data.data.list;
        this.vpcList.unshift({ id: "", name: lang.create_network });
        this.params.vpc.id = this.params.vpc.id || this.vpcList[0]?.id || "";
        this.plan_way = this.plan_way || 0;
        this.vpcLoading = false;
      } catch (error) {
        this.vpcLoading = false;
        showToast(error.data.msg);
      }
    },
    changeResource(e) {
      this.ressourceName = e[0].name;
      this.params.resource_package_id = this.resourceList.filter(
        (item) => item.name === e[0].name
      )[0]?.id;
      setTimeout(() => {
        this.getCycleList();
      }, 0);
    },
    changeCpu(e) {
      // 切换cpu，改变内存
      this.isChangeArea = false;
      this.params.cpu = e[0].value;
      this.cpuName = e[0].value + lang.mf_cores;
      // 计算价格
      setTimeout(() => {
        this.params.memory =
          this.memoryList[0].type === "radio"
            ? this.calaMemoryList[0]?.value
            : this.calaMemoryList[0];
        this.memoryName = this.params.memory + this.baseConfig.memory_unit;
        this.getCycleList();
      }, 0);
    },
    changeMem(num) {
      if (window.menTimer) {
        clearTimeout(window.menTimer);
        window.menTimer = null;
      }
      window.menTimer = setTimeout(() => {
        if (!this.calaMemoryList.includes(num)) {
          this.calaMemoryList.forEach((item, index) => {
            if (num > item && num < this.calaMemoryList[index + 1]) {
              this.params.memory =
                num - item > this.calaMemoryList[index + 1] - num
                  ? this.calaMemoryList[index + 1]
                  : item;
            }
          });
        }
        this.getCycleList();
      }, 300);
    },
    clickNetType() {
      this.getVpcList();
      this.showNetPage = true;
    },
    // 切换套餐，自定义
    handleClick() {
      this.activeName === "fast"
        ? (this.activeName = "custom")
        : (this.activeName = "fast");
      this.params.auto_renew = false;
      this.params.ip_mac_bind_enable = false;
      this.params.nat_acl_limit_enable = false;
      this.params.nat_web_limit_enable = false;
      this.params.ipv6_num_enable = false;
      this.params.peak_defence = "";
      this.defenseName = "";
      //  this.params.image_id = 0
      this.curImageId = this.imageList[0]?.id;
      this.showImage = false;
      this.isHide = true;
      this.getConfig();
    },
    // 选择区域
    changeArea(e) {
      this.area_name = e[0].name;
      this.isChangeArea = false;
      this.params.data_center_id = this.calcAreaList.filter(
        (item) => item.name === e[0].name
      )[0]?.id;
      this.lineList = this.calcAreaList.filter(
        (item) => item.name === e[0].name
      )[0]?.line;
      this.params.line_id = this.lineList[0].id;
      this.lineName = this.lineList[0].name;
      // 区域变化，如果有区域限制再重置cpu, 内存 ?

      this.params.cpu = this.cpuList[0]?.value;
      this.cpuName = this.params.cpu + lang.mf_cores;
      if (this.memoryList[0].type === "radio") {
        this.params.memory = this.calaMemoryList[0]?.value * 1;
      } else {
        this.params.memory = this.calaMemoryList[0] * 1;
      }
      this.memoryName =
        this.calaMemoryList[0]?.value + this.baseConfig.memory_unit;
    },
    // 选择先线路
    chooseLine(item) {
      this.params.data_center_id = item.data_center_id;
      this.params.line_id = item.id;
    },
    // 添加数据盘
    addDataDisk(index, item) {
      try {
        // 新增磁盘
        if (index === "add") {
          this.diskColumnsType = "dataType";
          console.log(this.dataType[0]);
          this.storeList.push({
            min: this.dataDiskList[0].min_value,
            max: this.dataDiskList[this.dataDiskList.length - 1].max_value,
            type: this.dataDiskList[0].type,
            name: lang.common_cloud_text1,
            disk_type: this.dataType[0].value,
            disk_text: this.dataType[0].label,
            size: this.dataDiskList[0].value || this.dataDiskList[0].min_value,
            num: 1,
          });
          // 处理params
          this.params.data_disk.push({
            disk_type: this.dataType[0].value,
            disk_text: this.dataType[0].label,
            size: this.dataDiskList[0].value || this.dataDiskList[0].min_value,
          });
          if (this.baseConfig.free_disk_switch) {
            this.editDiskIndex = this.params.data_disk.length - 2;
          } else {
            this.editDiskIndex = this.params.data_disk.length - 1;
          }
          this.getCycleList();
          return;
        }
        // 更改磁盘

        // 更改系统磁盘
        if (index === 0) {
          this.editDiskIndex = 0;
          this.diskColumnsType = "system";
        } else if (this.baseConfig.free_disk_switch && index === 1) {
          // 不能更改免费磁盘
          return;
        } else {
          this.editDiskIndex = index - 1;
          this.diskColumnsType = "dataType";
        }
        this.showSystemDisk = true;
      } catch (err) {
        console.log(err);
      }
    },
    // 切换数据盘类型
    changeDataDisk(e, index) {
      // 分单选和范围
      if (this.dataDiskList[0]?.type === "radio") {
        this.params.data_disk[index - 1].size = this.dataNumObj[e][0]?.value;
      } else {
        this.params.data_disk[index - 1].size = this.dataRangArr[e][0];
        this.storeList[index].min = this.dataRangArr[e][0];
        this.storeList[index].max =
          this.dataRangArr[e][this.dataRangArr[e].length - 1];
      }
      this.getCycleList();
    },
    delDataDisk(index) {
      this.storeList.splice(index, 1);
      this.params.data_disk.splice(index - 1, 1);
      this.getCycleList();
    },
    beforeChange(val, item) {
      item.isAdd = val * 1 > item.size * 1;
      return true;
    },
    // 改变系统盘数量
    changeSysNum(num) {
      // 筛选对应类型下面的所有范围
      const temp = this.systemRangArr[this.params.system_disk.disk_type];
      const isAdd = this.params.system_disk.isAdd;
      if (!temp.includes(num)) {
        let res = num;
        for (let index = 0; index < temp.length; index++) {
          const item = temp[index];
          if (isAdd && item > num) {
            res = item;
            break;
          }
          if (!isAdd && item < num) {
            res = item;
          }
        }
        this.$nextTick(() => {
          this.params.system_disk.size = res;
          this.getCycleList();
        });
      }
    },
    changeDataNum(num, ind) {
      // 数据盘数量改变计算价格
      const temp = this.dataRangArr[this.params.data_disk[ind - 1].disk_type];
      const isAdd = this.params.data_disk[ind - 1].isAdd;
      if (!temp.includes(num)) {
        let res = num;
        for (let index = 0; index < temp.length; index++) {
          const item = temp[index];
          if (isAdd && item > num) {
            res = item;
            break;
          }
          if (!isAdd && item < num) {
            res = item;
          }
        }
        this.$nextTick(() => {
          this.params.data_disk[ind - 1].size = res;
          this.getCycleList();
        });
      }
    },
    // 初始化处理系统盘，数据盘类型
    handlerType(data, type) {
      data.forEach((item) => {
        const temp = item.other_config.disk_type;
        const num = item.value;
        len = this[`${type}Type`].filter((el) => el.value === temp);
        // 处理类型 systemType, dataType
        if (len.length === 0) {
          this[`${type}Type`].push({
            value: temp,
            label:
              item.customfield.multi_language.other_config?.disk_type ||
              item.other_config?.disk_type ||
              lang.mf_no,
          });
        }
        // 处理数量选择 dataNumObj
        if (type === "data") {
          let arr = [];
          const filterArr = data.filter(
            (item) => item.other_config.disk_type === temp
          );
          filterArr.forEach((el) => {
            arr.push({
              value: el.value,
              label: el.value,
            });
          });

          this.dataNumObj[temp] = arr;
        }
      });
      // 根据磁盘类型处理取值范围和提示信息 systemRangArr, dataRangArr
      // 根据磁盘类型处理取值范围和提示信息 systemRangTip, dataRangTip
      this[`${type}Type`].forEach((item) => {
        const temp = this[`${type}DiskList`].filter(
          (lit) => lit.other_config.disk_type === item.value
        );
        const arr = [];
        temp.forEach((i) => {
          arr.push(...this.createArr([i.min_value, i.max_value]));
        });
        this[`${type}RangArr`][item.value] = arr;
        this[`${type}RangTip`][item.value] = this.createTip(arr);
      });
    },
    // 切换安全组
    changeGroup(e) {
      if (e[0].value === lang.exist_group && this.groupList.length === 0) {
        this.getGroup();
      } else if (e[0].value === lang.create_group) {
        const temp = this.groupSelect
          .filter((item) => item.check)
          .reduce((all, cur) => {
            all.push(cur.value);
            return all;
          }, []);
        this.params.security_group_protocol = temp;
      } else {
        this.params.security_group_protocol = [];
      }
      this.groupName = e[0].value;
      this.params.security_group_id = "";
    },
    selectGroup(e) {
      this.params.security_group_id = e[0].id;
    },
    async getGroup() {
      try {
        this.groupLoading = true;
        const res = await getGroup({
          page: 1,
          limit: 1000,
        });
        this.groupList = res.data.data.list;
        this.groupLoading = false;
      } catch (error) {
        this.groupLoading = false;
        showToast(error.data.msg);
      }
    },
    // 切换登录方式
    changeLogin(e) {
      this.params.password = "";
      this.params.ssh_key_id = "";
      this.showSsh = false;
      if (e[0].value === lang.security_tab1 && this.sshList.length === 0) {
        this.getSsh();
      }
      if (e[0].value === lang.auto_create) {
        this.createPassword();
      }
      this.login_way = e[0].value;
    },
    async getSsh() {
      try {
        this.sshLoading = true;
        const res = await getSshList({
          page: 1,
          limit: 1000,
        });
        this.sshList = res.data.data.list;
        this.sshLoading = false;
      } catch (error) {
        this.sshLoading = false;
        showToast(error.data.msg);
      }
    },
    // 生成随机密码
    createPassword() {
      const password = genEnCode(
        Math.floor(Math.random() * 10 + 4),
        1,
        1,
        0,
        1,
        undefined,
        1
      );
      const p1 = [
        String.fromCharCode(Math.floor(Math.random() * 25 + 65)),
        String.fromCharCode(Math.floor(Math.random() * 25 + 97)),
      ];
      const result = p1[0] + p1[1] + password + Math.floor(Math.random() * 10);
      this.params.password = result;
    },
    changeInput(val) {
      this.hasLen = val.length >= 6;
      this.hasAppoint = /[^A-Za-z\d~!@#$&*()_\-+=|{}[\];:<>?,./]/.test(val);
      this.hasMust = /(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])/.test(val);
      this.hasLine = val[0] === "/";
      if (this.hasLen && !this.hasAppoint && this.hasMust && !this.hasLine) {
        this.showPas = false;
      }
    },
    changeRepas(val) {
      if (val && val === this.params.password) {
        this.showRepass = false;
      }
    },
    clickRecommend(index) {
      this.clickIndex = index;
    },
    // 切换套餐
    changeRecommend() {
      this.cloudIndex = this.clickIndex;
      const item = this.recommendList[this.clickIndex];
      if (this.packageId === item.id) {
        return;
      }
      // 赋值
      this.packageId = item.id;
      const temp = JSON.parse(JSON.stringify(item));
      this.showPackgeid = JSON.parse(JSON.stringify(item));
      temp.system_disk = {
        size: temp.system_disk_size,
        disk_type: temp.system_disk_type,
      };
      delete temp.data_disk_size;
      delete temp.data_disk_type;
      delete temp.system_disk_size;
      delete temp.system_disk_type;
      this.gpu_name = item.gpu_name;
      Object.assign(this.params, temp);
      this.lineType = this.params.bw ? "bw" : "flow";
      this.params.data_disk = [];
      if (item.data_disk_size * 1) {
        this.params.data_disk.push({
          size: item.data_disk_size,
          disk_type: item.data_disk_type,
        });
      } else {
        this.params.data_disk = [];
      }
      this.params.name = "";
      this.getCycleList();
      this.isShowRecommend = false;
    },
    handleRecomend() {
      this.clickIndex = this.cloudIndex;
      this.isShowRecommend = true;
    },
    // 切换城市
    changeCity(e, city) {
      console.log(e);
      this.country = e[0].id + "";
      this.city = e[1].name;
      this.areText = e[0].name + "," + e[1].name;
      this.areImg = e[0].iso;
      this.isChangeArea = true;
      this.cloudIndex = 0;
      if (this.activeName === "fast") {
        this.params.duration_id = "";
      }
      this.handlerFast();
    },
    tableRowClassName({ row, rowIndex }) {
      row.index = rowIndex;
    },
    // 更改磁盘类型
    onDiskConfirm() {
      const e = this.$refs.diskPopRef.getSelectedOptions();
      if (this.diskColumnsType === "system") {
        this.params.system_disk.disk_type = e[0].value;
        this.params.system_disk.disk_text = e[0].label;
        if (this.storeList[0].type === "radio") {
          this.params.system_disk.size = this.systemNum[0].value;
        }
      } else {
        this.params.data_disk[this.editDiskIndex].disk_type = e[0].value;
        this.params.data_disk[this.editDiskIndex].disk_text = e[0].label;
        this.storeList[this.editDiskIndex + 1].disk_type = e[0].value;
        this.storeList[this.editDiskIndex + 1].disk_text = e[0].label;
        if (this.storeList[this.editDiskIndex].type === "radio") {
          this.params.data_disk[this.editDiskIndex].size =
            this.dataNumObj[
              this.params.data_disk[this.editDiskIndex].disk_type
            ][0].value;
        } else {
          this.storeList[this.editDiskIndex + 1].min =
            this.dataRangArr[e[0].value][0];
          this.storeList[this.editDiskIndex + 1].max =
            this.dataRangArr[e[0].value][
              this.dataRangArr[e[0].value].length - 1
            ];
        }
        this.$nextTick(() => {
          this.getCycleList();
        });
      }
      this.showDiskPicker = false;
    },
    handelDiskDiao() {
      const e = this.$refs.diskRadioRef.getSelectedOptions();
      if (this.diskColumnsType === "system") {
        this.params.system_disk.size = e[0].value;
      } else {
        this.params.data_disk[this.editDiskIndex].size = e[0].value;
      }
      this.getCycleList();
      this.showDiskPick = false;
    },
    handelNetSelect() {
      const e = this.$refs.netTypeRef.getSelectedOptions();
      this.netName = e[0].value;
      this.changeNet(e[0].value);
      this.showNetPick = false;
    },
    handelVpcSelect() {
      const e = this.$refs.vpcTypeRef.getSelectedOptions();
      this.params.vpc.id = e[0].id;
      this.showVpcPick = false;
    },
    handelPlanSelect() {
      const e = this.$refs.planTypeRef.getSelectedOptions();
      this.plan_way = e[0].value;
      this.showPlanPick = false;
    },
    handelLineSelect() {
      const e = this.$refs.lineTypeRef.getSelectedOptions();
      this.lineName = e[0].name;
      this.showLinePick = false;
      this.changeLine(e[0].name);
    },
    // 提交前格式化数据
    formatData() {
      const temp = this.groupSelect
        .filter((item) => item.check)
        .reduce((all, cur) => {
          all.push(cur.value);
          return all;
        }, []);
      this.params.security_group_protocol = temp;

      if (this.groupName === lang.no_safe_group) {
        this.params.security_group_protocol = [];
        this.params.security_group_id = "";
      }
      if (this.params.security_group_id) {
        this.params.security_group_protocol = [];
      }
      // if (this.params.vpc.id === 0) {
      //   this.params.vpc.id = ''
      // }
      if (this.plan_way === 0) {
        this.params.vpc.ips = "";
      }
      if (!this.params.image_id) {
        if (this.activeName === "fast") {
          document.getElementById("image") &&
            document
              .getElementById("image")
              .scrollIntoView({ behavior: "smooth" });
        } else {
          document.getElementById("image") &&
            document.getElementById("image1").scrollIntoView({
              behavior: "smooth",
              block: "end",
              inline: "nearest",
            });
        }
        this.showImage = true;
        return;
      }
      // 自动创建密码
      if (this.login_way === lang.auto_create && !this.params.password) {
        return showToast(`${lang.placeholder_pre1}${lang.login_password}`);
      }

      // 设置密码
      if (this.login_way === lang.set_pas) {
        // 一个不满足都需要提示
        if (this.hasLen && !this.hasAppoint && this.hasMust && !this.hasLine) {
        } else {
          document.getElementById("ssh") &&
            document
              .getElementById("ssh")
              .scrollIntoView({ behavior: "smooth" });
          this.showPas = true;
          return;
        }
      }
      if (
        this.login_way === lang.set_pas &&
        this.params.password !== this.params.re_password
      ) {
        document.getElementById("ssh") &&
          document.getElementById("ssh").scrollIntoView({ behavior: "smooth" });
        this.showRepass = true;
        return;
      }
      // ssh
      if (this.login_way === lang.security_tab1 && !this.params.ssh_key_id) {
        document.getElementById("ssh") &&
          document.getElementById("ssh").scrollIntoView({ behavior: "smooth" });
        this.showSsh = true;
        return;
      }
      // 自动续费
      this.params.auto_renew = this.params.auto_renew ? 1 : 0;
      // 其他配置
      this.params.ip_mac_bind_enable = this.params.ip_mac_bind_enable ? 1 : 0;
      this.params.nat_acl_limit_enable = this.params.nat_acl_limit_enable
        ? 1
        : 0;
      this.params.nat_web_limit_enable = this.params.nat_web_limit_enable
        ? 1
        : 0;
      this.params.ipv6_num_enable = this.params.ipv6_num_enable ? 1 : 0;
      return true;
    },
    // 立即购买
    async submitOrder(e) {
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
      this.$refs.orderForm.submit();
      const bol = this.formatData();
      if (bol !== true) {
        return;
      }
      const flag = await this.$refs.customGoodRef.getSelfDefinedField();
      if (!flag) return;
      try {
        const params = {
          product_id: this.id,
          config_options: {
            ...JSON.parse(JSON.stringify(this.params)),
          },
          qty: this.qty,
          customfield: this.promo,
          self_defined_field: this.self_defined_field,
        };
        if (this.baseConfig.free_disk_switch && this.activeName === "custom") {
          params.config_options.data_disk.shift();
        }
        if (this.lineDetail.bill_type === "bw") {
          delete params.flow;
        } else {
          delete params.bw;
        }
        if (this.activeName === "fast") {
          params.config_options.recommend_config_id = this.packageId;
        }
        // 处理自动续费，其他配置等
        const _temp = this.formatSwitch(params);

        if (e.data && e.data.type === "iframeBuy") {
          const postObj = {
            type: "iframeBuy",
            params: _temp,
            price: this.calcTotalPrice,
          };
          window.parent.postMessage(postObj, "*");
          return;
        }
        // 直接传配置到结算页面
        sessionStorage.setItem("product_information", JSON.stringify(_temp));
        window.parent.location.href = `/cart/settlement.htm?id=${params.product_id}`;
      } catch (error) {
        this.submitLoading = false;
        showToast(error.data.msg);
      }
    },
    handlerCart() {
      if (this.isUpdate) {
        this.changeCart();
      } else {
        this.addCart();
      }
    },

    // 加入购物车
    addCart() {
      this.$refs.orderForm.validate(async (res) => {
        if (res) {
          const bol = this.formatData();
          if (bol !== true) {
            return;
          }
          const flag = await this.$refs.customGoodRef.getSelfDefinedField();
          if (!flag) return;

          try {
            const params = {
              product_id: this.id,
              config_options: {
                ...this.params,
                // 其他需要回显的页面数据
                activeName: this.activeName,
                country: this.country,
                countryName: this.countryName,
                city: this.city,
                curImage: this.curImage,
                curImageId: this.curImageId,
                imageName: this.imageName,
                version: this.version,
                cloudIndex: this.cloudIndex,
                login_way: this.login_way,
              },
              qty: this.qty,
              customfield: this.promo,
              self_defined_field: this.self_defined_field,
            };
            if (this.lineDetail.bill_type === "bw") {
              delete params.flow;
            } else {
              delete params.bw;
            }
            if (this.activeName === "custom") {
              params.config_options.data_disk = this.formateDataDisk();
            }
            this.submitLoading = true;
            const res = await addToCart(params);
            if (res.data.status === 200) {
              this.cartDialog = true;
              const result = await getCart();
              localStorage.setItem(
                "cartNum",
                "cartNum-" + result.data.data.list.length
              );
            }
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            showToast(error.data.msg);
          }
        }
      });
    },
    // 修改购物车
    async changeCart() {
      this.$refs.orderForm.validate(async (res) => {
        if (res) {
          const bol = this.formatData();
          if (bol !== true) {
            return;
          }
          const flag = await this.$refs.customGoodRef.getSelfDefinedField();
          if (!flag) return;
          try {
            const params = {
              position: this.position,
              product_id: this.id,
              config_options: {
                ...this.params,
                // 其他需要回显的页面数据
                activeName: this.activeName,
                country: this.country,
                countryName: this.countryName,
                city: this.city,
                curImage: this.curImage,
                curImageId: this.curImageId,
                imageName: this.imageName,
                version: this.version,
                cloudIndex: this.cloudIndex,
                login_way: this.login_way,
              },
              qty: this.qty,
              customfield: this.promo,
              self_defined_field: this.self_defined_field,
            };
            if (this.lineDetail.bill_type === "bw") {
              delete params.flow;
            } else {
              delete params.bw;
            }
            if (this.activeName === "custom") {
              params.config_options.data_disk = this.formateDataDisk();
            }
            this.submitLoading = true;
            this.dataLoading = true;
            const res = await updateCart(params);
            showToast(res.data.msg);
            setTimeout(() => {
              this.submitLoading = false;
              window.parent.location.href = `/cart/shoppingCar.htm`;
            }, 300);
            this.dataLoading = false;
          } catch (error) {
            this.submitLoading = false;
            showToast(error.data.msg);
          }
        }
      });
    },
    goToCart() {
      window.parent.location.href = `/cart/shoppingCar.htm`;
      this.cartDialog = false;
    },
    changeCountry() {
      this.countryName = this.dataList.filter(
        (item) => item.id === this.country * 1
      )[0]?.name;
      this.isChangeArea = true;
      this.city = this.dataList.filter(
        (item) => item.id === this.country * 1
      )[0].city[0]?.name;
      this.cloudIndex = 0;
      if (this.activeName === "fast") {
        this.handlerFast();
      }
    },
    eventChange(evetObj) {
      if (this.promo.event_promotion !== evetObj.id) {
        this.promo.event_promotion = evetObj.id;
        this.changeConfig();
      }
    },
    changQty() {
      this.loadingPrice = true;
      this.changeConfig();
    },
    // 使用优惠码
    async useDiscount() {
      try {
        if (this.promo.promo_code.length !== 9) {
          this.showErr = true;
          return;
        }
        const params = JSON.parse(JSON.stringify(this.promo));
        params.product_id = this.id;
        params.qty = this.qty;
        params.amount = this.totalPrice;
        params.billing_cycle_time = this.duration;
        const res = await usePromo(params);
        showToast(res.data.msg);
        this.changeConfig();
        this.dis_visible = false;
      } catch (error) {
        showToast(error.data.msg);
      }
    },
    closeDiscount() {
      this.dis_visible = !this.dis_visible;
    },
    canclePromo() {
      this.discount = 0;
      this.promo.promo_code = "";
      this.changeConfig();
    },
    // 获取镜像
    async getIamgeList() {
      try {
        const res = await getSystemList({ id: this.id });
        const temp = res.data.data.list;
        this.imageList = temp;
        if (!this.isUpdate) {
          this.imageName = this.version = temp[0]?.image[0]?.name;
          this.curImage = 0;
          this.curImageId = temp[0]?.id;
          this.params.image_id = temp[0]?.image[0]?.id;
          this.imageText = temp[0]?.image[0]?.name;
        }
      } catch (error) {}
    },
    // 获取周期
    async getCycleList() {
      try {
        this.loadingPrice = true;
        const params = JSON.parse(JSON.stringify(this.params));
        if (this.activeName === "custom") {
          params.data_disk = this.formateDataDisk();
        }
        if (this.baseConfig.free_disk_switch && this.activeName === "custom") {
          params.data_disk.shift();
        }
        params.id = this.id;
        const hasDuration = params.duration_id;
        if (hasDuration) {
          this.changeConfig();
        }
        if (this.activeName === "fast") {
          params.recommend_config_id = this.packageId;
        }
        const res = await getDuration(params);

        this.selectDuration = res.data.data[0];
        this.cycleList = res.data.data;
        this.params.duration_id =
          this.params.duration_id || this.cycleList[0]?.id;
        if (!hasDuration) {
          this.changeConfig();
        }
      } catch (error) {
        console.log("error", error);
      }
    },
    formateDataDisk() {
      const tempStore = JSON.parse(JSON.stringify(this.storeList));
      tempStore.shift();
      const temp_data = [];
      tempStore.forEach((item, index) => {
        if (item.num > 1) {
          for (let i = 0; i < item.num; i++) {
            temp_data.push({
              disk_type: item.disk_type,
              size: this.params.data_disk[index].size,
            });
          }
        } else {
          temp_data.push({
            disk_type: item.disk_type,
            size: this.params.data_disk[index].size,
          });
        }
      });
      return temp_data;
    },
    getDiscount(...data) {
      this.promo.promo_code = data[1];
      this.useDiscount();
    },
    // 更改配置计算价格
    async changeConfig() {
      try {
        const params = {
          id: this.id,
          config_options: {
            ...JSON.parse(JSON.stringify(this.params)),
            promo_code: this.promo.promo_code,
            event_promotion: this.promo.event_promotion,
          },
          qty: this.qty,
        };
        if (this.activeName === "custom") {
          params.config_options.data_disk = this.formateDataDisk();
        }
        if (this.baseConfig.free_disk_switch && this.activeName === "custom") {
          params.config_options.data_disk.shift();
        }
        if (this.activeName === "fast") {
          params.config_options.recommend_config_id = this.packageId;
        }
        this.loadingPrice = true;
        const res = await calcPrice(params);

        this.discount = res.data.data.price_promo_code_discount * 1 || 0;
        this.levelNum = res.data.data.price_client_level_discount * 1 || 0;
        this.totalPrice = res.data.data.price * 1;
        this.eventDiscount =
          res.data.data.price_event_promotion_discount * 1 || 0;
        this.calcTotalPrice = res.data.data.price_total * 1;

        this.base_price = res.data.data.base_price;
        this.showInfo = res.data.data?.preview;
        this.preview = res.data.data.preview;
        this.duration = res.data.data.duration;
        this.isInit = false;
        this.loadingPrice = false;
      } catch (error) {
        console.log(error);
        this.loadingPrice = false;
        showToast(error.data.msg);
      }
    },
    // 获取通用配置
    getCommonData() {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"));
    },
    getGoodsName() {
      productInfo(this.id).then((res) => {
        this.tit = res.data.data.product.name;
        document.title =
          this.commonData.website_name + "-" + res.data.data.product.name;
      });
    },
    mouseenter(index) {
      // if (index === this.curImage) {
      //   this.hover = true
      // }
      this.curImage = index;
      this.hover = true;
    },
    changeImage(item) {
      this.curImageId = item.id;
      this.showImgPick = true;
      setTimeout(() => {
        this.getCycleList();
      }, 10);
    },
    // 选择周期
    chooseDuration(id, item) {
      if (id === this.params.duration_id) {
        return;
      }
      this.selectDuration = item;
      this.params.duration_id = id;
      this.loadingPrice = true;
      this.promo.promo_code = "";
      this.discount = 0;
      this.changeConfig();
    },
    chooseVersion(ver, id) {
      this.curImageId = id;
      this.version = ver.name;
      this.params.image_id = ver.id;
      this.getCycleList();
    },
    /* vpc校验规则 */
    changeVpc3() {
      switch (this.vpc_ips.vpc6.value) {
        case 16:
          this.vpc_ips.vpc3 = 0;
          break;
        case 17:
          this.vpc_ips.vpc3 = this.near([0, 128], this.vpc_ips.vpc3);
          break;
        case 18:
          this.vpc_ips.vpc3 = this.near([0, 64, 128, 192], this.vpc_ips.vpc3);
          break;
        case 19:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(32, 224)],
            this.vpc_ips.vpc3
          );
          break;
        case 20:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(16, 240)],
            this.vpc_ips.vpc3
          );
          break;
        case 21:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(8, 248)],
            this.vpc_ips.vpc3
          );
          break;
        case 22:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(4, 252)],
            this.vpc_ips.vpc3
          );
          break;
        case 23:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(2, 254)],
            this.vpc_ips.vpc3
          );
          break;
      }
    },
    changeVpc4() {
      switch (this.vpc_ips.vpc6.value) {
        case 25:
          this.vpc_ips.vpc4 = this.near([0, 128], this.vpc_ips.vpc4);
          break;
        case 26:
          this.vpc_ips.vpc4 = this.near([0, 64, 128, 192], this.vpc_ips.vpc4);
          break;
        case 27:
          this.vpc_ips.vpc4 = this.near(
            [0, ...this.productArr(32, 224)],
            this.vpc_ips.vpc4
          );
          break;
        case 28:
          this.vpc_ips.vpc4 = this.near(
            [0, ...this.productArr(16, 240)],
            this.vpc_ips.vpc4
          );
          break;
      }
    },
    productArr(min, max, step) {
      const arr = [];
      for (let i = min; i < max + 1; i = i + min) {
        arr.push(i);
      }
      return arr;
    },
    near(arr, n) {
      arr.sort(function (a, b) {
        return Math.abs(a - n) - Math.abs(b - n);
      });
      return arr[0];
    },
    changeVpcMask(e) {
      this.vpc_ips.vpc6.value = e[0].value;
      switch (e[0].value) {
        case 16:
          this.vpc_ips.vpc3 = 0;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc3Tips = "";
          this.vpc_ips.vpc4Tips = "";
          break;
        case 17:
          this.vpc_ips.vpc3 = this.near([0, 128], this.vpc_ips.vpc3);
          this.vpc_ips.vpc3Tips = lang.range2;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 18:
          this.vpc_ips.vpc3 = this.near([0, 64, 128, 192], this.vpc_ips.vpc3);
          this.vpc_ips.vpc3Tips = lang.range3;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 19:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(32, 224)],
            this.vpc_ips.vpc3
          );
          this.vpc_ips.vpc3Tips = lang.range4;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 20:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(16, 240)],
            this.vpc_ips.vpc3
          );
          this.vpc_ips.vpc3Tips = lang.range5;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 21:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(8, 248)],
            this.vpc_ips.vpc3
          );
          this.vpc_ips.vpc3Tips = lang.range6;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 22:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(4, 252)],
            this.vpc_ips.vpc3
          );
          this.vpc_ips.vpc3Tips = lang.range7;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 23:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(2, 254)],
            this.vpc_ips.vpc3
          );
          this.vpc_ips.vpc3Tips = lang.range8;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 24:
          this.vpc_ips.vpc3Tips = lang.range9;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 25:
          this.vpc_ips.vpc4 = this.near([0, 128], this.vpc_ips.vpc4);
          this.vpc_ips.vpc4Tips = lang.range2;
          this.vpc_ips.vpc3Tips = lang.range1;
          break;
        case 26:
          this.vpc_ips.vpc4 = this.near([0, 64, 128, 192], this.vpc_ips.vpc4);
          this.vpc_ips.vpc4Tips = lang.range3;
          this.vpc_ips.vpc3Tips = lang.range1;
          break;
        case 27:
          this.vpc_ips.vpc4 = this.near(
            [0, ...this.productArr(32, 224)],
            this.vpc_ips.vpc4
          );
          this.vpc_ips.vpc4Tips = lang.range4;
          this.vpc_ips.vpc3Tips = lang.range1;
          break;
        case 28:
          this.vpc_ips.vpc4 = this.near(
            [0, ...this.productArr(16, 240)],
            this.vpc_ips.vpc4
          );
          this.vpc_ips.vpc4Tips = lang.range12;
          this.vpc_ips.vpc3Tips = lang.range1;
          break;
      }
    },
    vpcFormatter(val) {
      if (val * 1 > this.vpc_ips.max) {
        val = this.vpc_ips.max;
      }
      if (val * 1 < this.vpc_ips.min) {
        val = this.vpc_ips.min;
      }
      return val;
    },
    vpc2Formatter(val) {
      if (val * 1 > 255) {
        val = 255;
      }
      if (val * 1 < 0) {
        val = 0;
      }
      return val;
    },
    changeVpcIp(e) {
      this.vpc_ips.vpc1.value = e[0].value;
      switch (this.vpc_ips.vpc1.value) {
        case 10:
          this.vpc_ips.vpc1.tips = lang.range1;
          this.vpc_ips.min = 0;
          this.vpc_ips.max = 255;
          break;
        case 172:
          this.vpc_ips.vpc1.tips = lang.range10;
          if (this.vpc_ips.vpc2 < 16 || this.vpc_ips.vpc2 > 31) {
            this.vpc_ips.vpc2 = 16;
          }
          this.vpc_ips.min = 16;
          this.vpc_ips.max = 31;
          break;
        case 192:
          this.vpc_ips.vpc1.tips = lang.range11;
          this.vpc_ips.vpc2 = 168;
          this.vpc_ips.min = 168;
          this.vpc_ips.max = 168;
          break;
      }
    },
  },
});
window.directiveInfo.forEach((item) => {
  app2.directive(item.name, item.fn);
});
app2.use(vant).mount("#template2");
