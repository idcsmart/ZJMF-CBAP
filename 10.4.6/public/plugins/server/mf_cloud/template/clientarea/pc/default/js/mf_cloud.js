const template = document.getElementsByClassName("template")[0];
Vue.prototype.lang = Object.assign(window.lang, window.module_lang);
new Vue({
  components: {
    asideMenu,
    topMenu,
    eventCode,
    customGoods,
    discountCode,
  },
  mixins: [mixin],
  created() {
    this.isLogin = localStorage.getItem("jwt");
    this.getCommonData();
    // 回显配置
    let temp = {};
    const params = getUrlParams();
    this.id = params.id;
    this.getGoodsName();
    this.getIamgeList();
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
        peak_defence === 0 ? lang.no_defense : peak_defence + "G";
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
  mounted() {
    this.getConfig();
    this.hasDiscount = this.addons_js_arr.includes("PromoCode");
    this.isShowLevel = this.addons_js_arr.includes("IdcsmartClientLevel");
    // 开启活动满减
    this.isShowFull = this.addons_js_arr.includes("EventPromotion");
    // 监听子页面想父页面的传参
    window.addEventListener("message", (event) => this.submitOrder(event));
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
      if (this.isInit) {
        return;
      }
      if (this.activeName === "fast") {
        return;
      }
      if (type === "vpc") {
        this.params.ipv6_num = "";
      } else {
        this.params.ipv6_num = this.lineDetail.ipv6[0]?.value;
      }
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
    "params.cpu"(val) {
      if (val) {
        this.params.cpu = val * 1;
        this.limitNum++;
      }
    },
    "params.memory"(val) {
      if (val) {
        this.params.memory = val * 1;
        this.limitNum++;
      }
    },
  },
  computed: {
    /* 最新限制 */
    // 根据限制处理周期
    calcDuration() {
      if (this.activeName === "fast") {
      }
    },
    /* 最新限制 end */
    showFlowBw() {
      if (!this.lineDetail.flow && this.lineDetail.bill_type !== "bw") {
        return;
      }
      return this.lineDetail.flow.filter(
        (item) => item.value === this.params.flow
      )[0]?.other_config?.out_bw;
    },
    showPort() {
      return this.baseConfig.rand_ssh_port === 1 && this.params.port === "";
    },
    root_name() {
      return this.imageName.indexOf("Win") !== -1 ? "administrator" : "root";
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
      this.params.cpu = this.calcCpuList[0]?.value;
      if (this.memoryList[0]?.type === "radio") {
        this.params.memory = this.calaMemoryList[0]?.value * 1;
      } else {
        this.params.memory = this.calaMemoryList[0] * 1;
      }
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
      if (this.activeName === "fast") {
        return;
      }
      if (this.configLimitList.length === 0) {
        if (this.isInit && this.isUpdate) {
        } else {
          this.params.cpu = this.cpuList[0]?.value;
        }
        return this.cpuList;
      }

      // 1.找到结果有关于cpu的限制
      const temp = this.configLimitList
        .reduce((all, cur) => {
          if (cur.result.cpu) {
            all.push(cur);
          }
          return all;
        }, [])
        .filter(
          (
            item // 2.筛选当前配置全部符合条件的限制
          ) =>
            (!item.rule.data_center ||
              (item.rule.data_center.opt === "eq"
                ? item.rule.data_center.id.includes(this.params.data_center_id)
                : !item.rule.data_center.id.includes(
                    this.params.data_center_id
                  ))) &&
            (!item.rule.memory ||
              (item.rule.memory.opt === "eq"
                ? this.handleRange(item.rule, "memory")
                : !this.handleRange(item.rule, "memory"))) &&
            (!item.rule.image ||
              (item.rule.image.opt === "eq"
                ? item.rule.image.id.includes(this.params.image_id)
                : !item.rule.image.id.includes(this.params.image_id)))
        );
      let temCpu = [];
      if (temp.length > 0) {
        // 结果求交集
        const cpuArr = temp.reduce((all, cur) => {
          if (cur.result.cpu.opt === "eq") {
            all.push(cur.result.cpu.value);
          } else {
            const tempCpu = this.cpuList.reduce((all, cur) => {
              all.push(cur.value);
              return all;
            }, []);
            const result = tempCpu.filter(
              (item) => !cur.result.cpu.value.includes(item)
            );
            all.push(result);
          }
          return all;
        }, []);
        cpuOpt = this.handleMixed(...cpuArr);
        if (cpuOpt.length === 0) {
          // 没有交集的时候取全部
          temCpu = this.cpuList;
        } else {
          temCpu = this.cpuList.filter((item) => {
            return Array.from(new Set(cpuOpt)).includes(item.value);
          });
        }
      } else {
        temCpu = this.cpuList;
      }
      const cpuId = temCpu.map((item) => item.value * 1);
      if (!cpuId.includes(this.params.cpu)) {
        this.params.cpu = cpuId[0];
      }
      return temCpu;
    },
    calaMemoryList() {
      // 计算可选内存，根据 cpu + 区域
      if (this.activeName === "fast") {
        return;
      }
      if (this.configLimitList.length === 0) {
        if (this.memoryList[0]?.type === "radio") {
          return this.memoryList;
        } else {
          this.memoryTip = this.createTip(this.memory_arr);
          this.memMarks = this.createMarks(this.memory_arr); // data 原数据，目标marks
          return this.memory_arr;
        }
      }
      let temp = this.configLimitList
        .reduce((all, cur) => {
          if (cur.result.memory) {
            all.push(cur);
          }
          return all;
        }, [])
        .filter((item) => {
          return (
            (!item.rule.data_center ||
              (item.rule.data_center.opt === "eq"
                ? item.rule.data_center.id.includes(this.params.data_center_id)
                : !item.rule.data_center.id.includes(
                    this.params.data_center_id
                  ))) &&
            (!item.rule.cpu ||
              (item.rule.cpu.opt === "eq"
                ? item.rule.cpu.value.includes(this.params.cpu)
                : !item.rule.cpu.value.includes(this.params.cpu))) &&
            (!item.rule.image ||
              (item.rule.image.opt === "eq"
                ? item.rule.image.id.includes(this.params.image_id)
                : !item.rule.image.id.includes(this.params.image_id)))
          );
        });

      let ruleResult = [];
      if (temp.length === 0) {
        if (this.memoryList[0]?.type === "radio") {
          return this.memoryList;
        } else {
          this.memoryTip = this.createTip(this.memory_arr);
          this.memMarks = this.createMarks(this.memory_arr); // data 原数据，目标marks
          return this.memory_arr;
        }
      } else {
        ruleResult = temp;
      }
      // 内存原始范围
      let originmemory_arr = [];
      if (this.memoryList[0]?.type === "radio") {
        originmemory_arr = this.memoryList.map((item) => item.value);
      } else {
        this.memoryList.forEach((item) => {
          originmemory_arr.push(
            ...this.createArr([item.min_value, item.max_value])
          );
        });
      }
      // 最小，最大值求交集
      const memoryMax = this.memory_arr[this.memory_arr.length - 1];
      let memory_arr = ruleResult.reduce((all, cur) => {
        // 根据 eq,neq判断是否取反
        if (cur.result.memory.opt === "eq") {
          all.push(
            this.createArr([
              cur.result.memory.min * 1,
              cur.result.memory.max === ""
                ? memoryMax
                : cur.result.memory.max * 1,
            ])
          );
        } else {
          let result = this.createArr([
            cur.result.memory.min * 1,
            cur.result.memory.max === ""
              ? memoryMax
              : cur.result.memory.max * 1,
          ]);
          result = this.memory_arr.filter((item) => !result.includes(item));
          all.push(result);
        }
        return all;
      }, []);
      let filterMemory = [];
      let memoryOpt = this.handleMixed(...memory_arr);
      if (memoryOpt.length === 0) {
        // 取交集数据为空的时候
        memoryOpt = this.memory_arr;
      }
      if (this.memoryList[0]?.type === "radio") {
        originmemory_arr = originmemory_arr.filter((item) =>
          memoryOpt.includes(item)
        );
        filterMemory = this.memoryList.filter((item) =>
          originmemory_arr.includes(item.value)
        );
      } else {
        filterMemory = memoryOpt.filter((item) =>
          originmemory_arr.includes(item)
        );
        this.memoryTip = this.createTip(filterMemory);
      }
      // 当前无之前选项重置
      if (this.memoryList[0]?.type === "radio") {
        const memoryId = filterMemory.map((item) => item.value * 1);
        if (!memoryId.includes(this.params.memory)) {
          this.params.memory = memoryId[0];
        }
      } else {
        if (!filterMemory.includes(this.params.memory)) {
          this.params.memory = filterMemory[0];
        }
      }
      return filterMemory;
    },
    calcCartName() {
      return this.isUpdate ? lang.product_sure_check : lang.product_add_cart;
    },
    calcDataNum() {
      return this.params.data_disk.reduce((all, cur) => {
        all += cur.size;
        return all;
      }, 0);
    },
    calcImageList() {
      let temp = JSON.parse(JSON.stringify(this.imageList));
      if (temp.length === 0) {
        return [];
      }
      /* 限制只针对自定义，不支持套餐 */
      if (
        this.activeName === "custom" &&
        this.limitNum &&
        this.configLimitList.length > 0
      ) {
        let tempLimit = this.configLimitList
          .reduce((all, cur) => {
            if (cur.result.image) {
              all.push(cur);
            }
            return all;
          }, [])
          .filter(
            (item) =>
              (!item.rule.data_center ||
                (item.rule.data_center.opt === "eq"
                  ? item.rule.data_center.id.includes(
                      this.params.data_center_id
                    )
                  : !item.rule.data_center.id.includes(
                      this.params.data_center_id
                    ))) &&
              (!item.rule.cpu ||
                (item.rule.cpu.opt === "eq"
                  ? item.rule.cpu.value.includes(this.params.cpu)
                  : !item.rule.cpu.value.includes(this.params.cpu))) &&
              (!item.rule.memory ||
                (item.rule.memory.opt === "eq"
                  ? this.handleRange(item.rule, "memory")
                  : !this.handleRange(item.rule, "memory")))
          );
        const allImageId = this.imageList.reduce((all, cur) => {
          all.push(...cur.image.map((item) => item.id));
          return all;
        }, []);
        const imageId = tempLimit.reduce((all, cur) => {
          if (cur.result.image.opt === "eq") {
            all.push(cur.result.image.id);
          } else {
            let result = allImageId.filter(
              (item) => !cur.result.image.id.includes(item)
            );
            all.push(result);
          }
          return all;
        }, []);
        // 求交集
        let resultImage = this.handleMixed(...imageId);
        if (resultImage.length === 0) {
          resultImage = allImageId;
        }
        if (tempLimit.length > 0) {
          temp = temp
            .map((item) => {
              item.image = item.image.filter((el) =>
                resultImage.includes(el.id)
              );
              return item;
            })
            .filter((item) => item.image.length > 0);
          // image_id 不在可选配置内
          const imageId = temp.reduce((all, cur) => {
            all.push(...cur.image.map((item) => item.id));
            return all;
          }, []);
          if (
            (!this.params.image_id ||
              !imageId.includes(this.params.image_id)) &&
            temp.length > 0
          ) {
            this.curImageId = temp[0]?.id;
            this.imageName = temp[0]?.name;
            this.curImage = "";
            this.version = temp[0].image[0]?.name;
            this.params.image_id = temp[0].image[0]?.id;
            this.isManual = false;
          }
        }
      }
      this.filterIamge = temp;
      if (!this.isHide) {
        return temp;
      } else {
        const _temp = JSON.parse(JSON.stringify(temp));
        if (temp.length <= 5) {
          return _temp.splice(0, 5);
        } else {
          return _temp.splice(0, 4);
        }
      }
    },
  },
  data() {
    return {
      finance_login: false,
      hasDiscount: false,
      isShowFull: false,
      isShowLevel: false,
      id: "",
      tit: "",
      commonData: {},
      product_related_list: [],
      eventData: {
        id: "",
        discount: 0,
      },
      calcTotalPrice: 0,
      showFast: true,
      self_defined_field: {},
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
      gpu_name: "",
      memoryList: [], // 内存
      memory_arr: [], // 范围时内存数组
      memMarks: {},
      bwMarks: {},
      memoryTip: "",
      limitList: [], // 限制
      packageId: "", // 套餐ID
      imageList: [], // 镜像
      filterIamge: [],
      systemDiskList: [], // 系统盘
      dataDiskList: [], // 数据盘
      configLimitList: [], // 限制规则
      cloudIndex: 0,
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
      memoryName: "",
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
        ipv6_num: "",
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
          id: "", // 选择已有的vc
          ips: "", // 自定义的时候
        },
        port: null,
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
      /* vpc */
      vpcList: [],
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
      limitNum: 0,
      // 回调相关
      isUpdate: false,
      isConfig: false,
      position: 0,
      backfill: {},
      isLogin: false,
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
      isCustom: false,
      isManual: false, // 手动切换
    };
  },
  methods: {
    deepCopy(obj, hash = new WeakMap()) {
      if (typeof obj !== "object" || obj === null) {
        return obj;
      }
      if (hash.has(obj)) {
        return hash.get(obj);
      }

      let copy = Array.isArray(obj) ? [] : {};
      hash.set(obj, copy);

      for (let key in obj) {
        if (obj.hasOwnProperty(key)) {
          copy[key] = this.deepCopy(obj[key], hash);
        }
      }

      return copy;
    },
    // 处理结果的交集
    handleMixed(...arr) {
      if (arr.length === 0) {
        return [];
      }
      let resultArr = new Set(arr[0]);
      for (let i = 1; i < arr.length; i++) {
        const curArr = arr[i];
        if (!curArr || !curArr.length) {
          return [];
        }
        const newArr = new Set();
        for (const element of resultArr) {
          if (curArr.includes(element)) {
            newArr.add(element);
          }
        }
        resultArr = newArr;
        if (resultArr.size === 0) {
          return [];
        }
      }
      return Array.from(resultArr);
    },
    handleRange(item, type) {
      // 处理范围内的是否包含当前参数: memory,system_disk,data_disk,bw,flow,ipv4_num,ipv6_num
      // 初始化的时候，需要处理各参数的最大范围
      let target = "";
      if (type === "system_disk") {
        target = this.params.system_disk.size;
      } else if (type === "data_disk") {
        // 当没有选择数据盘的时候， 值为 "", 会过滤掉设置了数据盘的规则
        target = this.params.data_disk[0]?.size;
        if (!target) {
          return true;
        }
      } else {
        target = this.params[type];
      }
      let rangeMax = this[`${type}_arr`][this[`${type}_arr`].length - 1];
      return this.createArr([
        item[type].min * 1,
        item[type].max === ""
          ? rangeMax
          : item[type].max * 1 >= rangeMax
          ? rangeMax
          : item[type].max * 1,
      ]).includes(target);
    },
    changeIpv4() {
      // 看有没有关联ipv4的限制
      this.getCycleList();
    },
    changeIpv6() {
      // 看有没有关联ipv6的限制
      this.getCycleList();
    },
    changeNat(e) {
      if (e) {
        this.params.ipv6_num_enable = false;
      }
    },
    getQuery(name) {
      const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
      const r = window.location.search.substr(1).match(reg);
      if (r != null) return decodeURI(r[2]);
      return null;
    },
    // 随机端口
    randomNum() {
      const min = this.baseConfig.rand_ssh_port_start * 1;
      const max = this.baseConfig.rand_ssh_port_end * 1;
      const range = max - min + 1;
      const num = Math.floor(Math.random() * range) + min;
      return num;
    },
    refreshPort() {
      this.params.port = this.randomNum();
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
        this.dataList = temp.data_center;
        this.resourceList = temp.resource_package || [];
        this.baseConfig = temp.config;
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
            ipv6_num: "",
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
            port: null,
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
          if (this.baseConfig.rand_ssh_port === 1) {
            this.refreshPort();
          }
        } else {
          // 回填数据
          this.params = this.backfill;
          if (this.baseConfig.free_disk_switch) {
            // 免费数据盘
            this.params.data_disk.splice(0, 0, {
              disk_type: "",
              size: this.baseConfig.free_disk_size,
            });
          }
          this.ressourceName = this.resourceList.filter(
            (item) => item.id === this.params.resource_package_id
          )[0]?.name;
        }

        this.totalPrice = 0.0;
        this.isInit = true;
        // 保存cpu,memory,system_disk,data_disk,config_limit
        this.cpuList = temp.cpu;
        this.memoryList = temp.memory;
        if (temp.memory.length > 0) {
          if (temp.memory[0].type !== "radio") {
            this.memoryType = false;
            // 范围的时候生成默认范围数组
            this.memory_arr = temp.memory.reduce((all, cur) => {
              all.push(...this.createArr([cur.min_value, cur.max_value]));
              return all;
            }, []);
          } else {
            this.memoryType = true;
            this.memory_arr = temp.memory.map((item) => item.value);
          }
        }
        this.memory_arr = this.memory_arr.sort((a, b) => a - b);
        this.systemDiskList = temp.system_disk;
        this.dataDiskList = temp.data_disk;
        this.configLimitList = temp.limit_rule;
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
        this.params.gpu_num = temp[0].gpu_num;
        this.gpu_name = temp[0].gpu_name;
        this.params.memory = temp[0].memory * 1 || 0;
        this.params.line_id = temp[0].line_id;
        this.lineType = temp[0].bw ? "bw" : "flow";
        this.params.bw = temp[0].bw;
        this.params.flow = temp[0].flow;
        this.params.ipv6_num = temp[0].ipv6_num;
        this.params.peak_defence = temp[0].peak_defence;
        this.params.system_disk.size = temp[0].system_disk_size;
        this.params.system_disk.disk_type = temp[0].system_disk_type;
        if (temp[0].data_disk_size * 1) {
          this.params.data_disk = [];
          this.params.data_disk.push({
            size: temp[0].data_disk_size,
            disk_type: temp[0].data_disk_type,
          });
        } else {
          this.params.data_disk = [];
        }
      } else {
        this.gpu_name = temp.filter(
          (item) => item.gpu_num === this.params.gpu_num
        )[0]?.gpu_name;
      }
      this.lineType = this.params.bw ? "bw" : "flow";
      // 计算价格
      setTimeout(() => {
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
          size:
            this.systemDiskList[0].value || this.systemDiskList[0].min_value,
          min: this.systemDiskList[0].min_value,
          max: this.systemDiskList[this.systemDiskList.length - 1].max_value,
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
          });
          this.params.data_disk.push({
            // 提交的时候，根据 baseConfig.free_disk_switch 是否删除第一个数据盘
            disk_type: "",
            size: this.baseConfig.free_disk_size,
          });
        }
        // 默认选择cpu 内存
        // console.log('@#@#@##@#@initcpu', this.calcCpuList[0])
        // this.params.cpu = this.calcCpuList[0]?.value;
        // if (this.memoryList[0].type === "radio") {
        //   this.params.memory = this.calaMemoryList[0]?.value * 1;
        // } else {
        //   this.params.memory = this.calaMemoryList[0] * 1;
        // }
        // this.memoryName = this.calaMemoryList[0]?.value + this.baseConfig.memory_unit;
      } else {
        // 回填
        this.area_name = this.calcAreaList.filter(
          (item) => item.id === this.params.data_center_id
        )[0]?.name;
        const temp =
          this.dataList
            .filter((item) => item.id === this.country * 1)[0]
            ?.city.filter((item) => item.name === this.city)[0]?.area || [];

        this.lineList =
          temp.filter((item) => item.name === this.area_name)[0]?.line || [];
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
          this.params.data_disk.forEach((item, index) => {
            arr.push({
              min: this.dataDiskList[0].min_value,
              max: this.dataDiskList[this.dataDiskList.length - 1].max_value,
              type: this.dataDiskList[0].type,
              name:
                this.baseConfig.free_disk_switch && index === 0
                  ? lang.mf_tip37
                  : lang.common_cloud_text1,
              disk_type: item.disk_type,
              size: item.size,
            });
          });
        }
        this.storeList = arr;
      }
    },
    goBuy(id) {
      window.open(`goods.htm?id=${id}`);
    },
    getGoodsName() {
      productInfo(this.id).then((res) => {
        this.tit = res.data.data.product.name;
        document.title =
          this.commonData.website_name + "-" + res.data.data.product.name;
        if (
          res.data.data.product.customfield.product_related_limit &&
          res.data.data.product.customfield.product_related_limit.related
        ) {
          this.product_related_list =
            res.data.data.product.customfield.product_related_limit.related;
        }
      });
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
        this.bwMarks = this.createMarks(this.bwArr);

        // gpu
        if (this.lineDetail.gpu) {
          this.gpuList = this.lineDetail.gpu;
          this.gpu_name = this.lineDetail.gpu_name;
          if (this.isInit && this.isUpdate) {
            // 初次回填
          } else {
            this.params.gpu_num = this.lineDetail.gpu[0]?.value;
          }
        } else {
          this.params.gpu_num = "";
          this.gpu_name = "";
        }
        // 11-22 防御改成默认选中首个
        if (this.lineDetail.defence) {
          if (this.isInit && this.isUpdate) {
            // 初次回填
          } else {
            this.params.peak_defence = this.lineDetail.defence[0]?.value;
          }
          this.defenseName =
            this.params.peak_defence === 0
              ? lang.no_defense
              : this.params.peak_defence + "G";
        } else {
          this.defenseName = "";
          this.params.peak_defence = "";
        }
        // 处理IPV4
        if (this.lineDetail.ip) {
          if (this.isInit && this.isUpdate) {
            // 初次回填
          } else {
            this.params.ip_num = this.lineDetail.ip[0]?.value;
          }
        } else {
          this.params.ip_num = "";
        }
        // 处理IPV6
        if (this.lineDetail.ipv6) {
          if (this.isInit && this.isUpdate) {
            // 初次回填
          } else {
            this.params.ipv6_num = this.lineDetail.ipv6[0]?.value;
          }
        } else {
          this.params.ipv6_num = "";
        }

        setTimeout(() => {
          this.getCycleList();
        }, 0);
      } catch (error) {
        console.log("####", error);
      }
    },
    changeBw(e) {
      this.params.bw = e.replace("M", "");
      // 计算价格
      setTimeout(() => {
        this.getCycleList();
      }, 0);
    },
    changeBwNum(num) {
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
    },
    // 选中/取消防御
    chooseDefence(e, c) {
      // if (this.defenseName === c.value + "G") {
      //   this.defenseName = "";
      //   this.params.peak_defence = "";
      // } else {
      //   this.defenseName = c.value + "G";
      //   this.params.peak_defence = c.value;
      // }
      if (c.value === 0) {
        this.defenseName = lang.no_defense;
      } else {
        this.defenseName = c.value + "G";
      }
      this.params.peak_defence = c.value;
      setTimeout(() => {
        this.getCycleList();
      }, 0);
      e.preventDefault();
    },
    // 切换流量
    changeFlow(e) {
      if (e === lang.mf_tip28) {
        this.params.flow = 0;
      } else {
        this.params.flow = e.replace("G", "") * 1;
      }

      setTimeout(() => {
        this.getCycleList();
      }, 0);
    },
    // 切换内存
    changeMemory(e) {
      this.params.memory = e;
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
      setTimeout(() => {
        this.getCycleList();
      }, 0);
    },
    // 获取vpc
    async getVpcList(data_id) {
      try {
        this.vpcLoading = true;
        const res = await getVpc({
          id: this.id,
          data_center_id: data_id || this.params.data_center_id,
          page: 1,
          limit: 1000,
        });
        this.vpcList = res.data.data.list;
        this.params.vpc.id = this.params.vpc.id || this.vpcList[0]?.id || "";
        this.plan_way = this.plan_way || 0;
        this.vpcLoading = false;
      } catch (error) {
        this.vpcLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    changeResource(e) {
      this.ressourceName = e;
      this.params.resource_package_id = this.resourceList.filter(
        (item) => item.name === e
      )[0]?.id;
      setTimeout(() => {
        this.getCycleList();
      }, 0);
    },
    changeCpu(e) {
      // 切换cpu，改变内存
      this.isChangeArea = false;
      // 计算价格
      setTimeout(() => {
        this.getCycleList();
      }, 0);
    },
    changeMem(num) {
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
    },
    // 切换套餐，自定义
    handleClick() {
      this.params.nat_acl_limit_enable = false;
      this.params.nat_web_limit_enable = false;
      this.params.auto_renew = false;
      this.params.ip_mac_bind_enable = false;
      this.params.peak_defence = "";
      this.params.ip_num = "";
      this.params.ipv6_num = "";
      this.showImage = false;
      this.isHide = true;
      this.curImage = 0;
      this.imageName = this.version = this.imageList[0]?.image[0]?.name;
      this.curImageId = this.imageList[0]?.id;
      this.getConfig();
    },
    // 选择区域
    changeArea(e) {
      this.isChangeArea = false;
      this.params.data_center_id = this.calcAreaList.filter(
        (item) => item.name === e
      )[0]?.id;
      this.lineList = this.calcAreaList.filter(
        (item) => item.name === e
      )[0]?.line;
      this.params.line_id = this.lineList[0].id;
      this.lineName = this.lineList[0].name;
      // 区域变化，如果有区域限制再重置cpu, 内存 ?

      // this.params.cpu = this.cpuList[0]?.value;
      // this.cpuName = this.params.cpu + lang.mf_cores;
      // if (this.memoryList[0].type === "radio") {
      //   this.params.memory = this.calaMemoryList[0]?.value * 1;
      // } else {
      //   this.params.memory = this.calaMemoryList[0] * 1;
      // }
      // this.memoryName =
      //   this.calaMemoryList[0]?.value + this.baseConfig.memory_unit;
    },
    // 选择先线路
    chooseLine(item) {
      this.params.data_center_id = item.data_center_id;
      this.params.line_id = item.id;
    },
    // 添加数据盘
    addDataDisk() {
      this.storeList.push({
        min: this.dataDiskList[0].min_value,
        max: this.dataDiskList[this.dataDiskList.length - 1].max_value,
        type: this.dataDiskList[0].type,
        name: lang.common_cloud_text1,
        disk_type: this.dataType[0].value,
        size: this.dataDiskList[0].value || this.dataDiskList[0].min_value,
      });
      // 处理params
      this.params.data_disk.push({
        disk_type: this.dataType[0].value,
        size: this.dataDiskList[0].value || this.dataDiskList[0].min_value,
      });
      this.getCycleList();
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
    // 改变系统盘数量
    changeSysNum(num) {
      // 筛选对应类型下面的所有范围
      const temp = this.systemRangArr[this.params.system_disk.disk_type];
      if (!temp.includes(num)) {
        temp.forEach((item, index) => {
          if (num > item && num < temp[index + 1]) {
            let res =
              num - item > temp[index + 1] - num ? temp[index + 1] : item;
            this.$nextTick(() => {
              this.params.system_disk.size = res;
            });
          }
        });
      }
      setTimeout(() => {
        this.getCycleList();
      });
    },
    changeDataNum(num, ind) {
      // 数据盘数量改变计算价格
      const temp = this.dataRangArr[this.params.data_disk[ind - 1].disk_type];
      if (!temp.includes(num)) {
        temp.forEach((item, index) => {
          if (num > item && num < temp[index + 1]) {
            let res =
              num - item > temp[index + 1] - num ? temp[index + 1] : item;
            this.$nextTick(() => {
              this.params.data_disk[ind - 1].size = res;
            });
          }
        });
      }
      setTimeout(() => {
        this.getCycleList();
      });
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
            label: temp || lang.mf_no,
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
      if (e === lang.exist_group && this.groupList.length === 0) {
        this.getGroup();
      }
      if (e === lang.create_group) {
        // 新建安全组
        this.groupSelect.forEach((item, index) => {
          const dom = this.$refs[`safe${index}`][0].$el;
          item.disabled =
            dom.offsetWidth >
            dom.getElementsByClassName("safe-item")[0].offsetWidth + 30;
        });
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
      this.params.security_group_id = "";
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
        this.$message.error(error.data.msg);
      }
    },
    // 切换登录方式
    changeLogin(e) {
      this.params.password = "";
      this.params.ssh_key_id = "";
      this.showSsh = false;
      if (e === lang.security_tab1 && this.sshList.length === 0) {
        this.getSsh();
      }
      if (e === lang.auto_create) {
        this.createPassword();
      }
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
        this.$message.error(error.data.msg);
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
    // 切换套餐
    changeRecommend(item, index) {
      this.cloudIndex = index;
      if (this.packageId === item.id) {
        return;
      }
      // 赋值
      this.packageId = item.id;
      const temp = JSON.parse(JSON.stringify(item));
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
      if (item.ipv6_num > 0) {
        this.params.network_type = "normal";
      }
      this.params.ipv6_num = item.ipv6_num;
      this.getCycleList();
    },
    // 切换城市
    changeCity(e, city) {
      this.isChangeArea = true;
      this.cloudIndex = 0;
      this.handlerFast();
    },
    tableRowClassName({ row, rowIndex }) {
      row.index = rowIndex;
    },

    // 提交前格式化数据
    formatData(bol = true) {
      if (this.groupName === lang.no_safe_group) {
        this.security_group_id = "";
        this.params.security_group_protocol = [];
      } else if (this.groupName === lang.create_group) {
        const temp = this.groupSelect
          .filter((item) => item.check)
          .reduce((all, cur) => {
            all.push(cur.value);
            return all;
          }, []);
        this.security_group_id = "";
        this.params.security_group_protocol = temp;
      } else if (this.groupName === lang.exist_group) {
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
          document.getElementById("image1") &&
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
      if (this.login_way === lang.auto_create && !this.params.password && bol) {
        this.createPassword();
        // return this.$message.warning(
        //   `${lang.placeholder_pre1}${lang.login_password}`
        // );
      }
      // 设置密码
      if (this.login_way === lang.set_pas) {
        // 一个不满足都需要提示
        if (this.isUpdate) {
          this.changeInput(this.params.password);
        }

        if (this.hasLen && !this.hasAppoint && this.hasMust && !this.hasLine) {
        } else {
          document.getElementById("ssh").scrollIntoView({ behavior: "smooth" });
          this.showPas = true;
          return;
        }
      }
      if (
        this.login_way === lang.set_pas &&
        this.params.password !== this.params.re_password
      ) {
        document.getElementById("ssh").scrollIntoView({ behavior: "smooth" });
        this.showRepass = true;
        return;
      }
      // ssh
      if (this.login_way === lang.security_tab1 && !this.params.ssh_key_id) {
        document.getElementById("ssh").scrollIntoView({ behavior: "smooth" });
        this.showSsh = true;
        return;
      }
      return true;
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

      this.$refs.orderForm.validate(async (res) => {
        if (res) {
          const bol = this.formatData();
          if (bol !== true) {
            return;
          }
          const flag = this.$refs.customGoodRef.getSelfDefinedField();
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
            if (
              this.baseConfig.free_disk_switch &&
              this.activeName === "custom"
            ) {
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
            sessionStorage.setItem(
              "product_information",
              JSON.stringify(_temp)
            );
            location.href = `/cart/settlement.htm?id=${params.product_id}`;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        }
      });
    },
    handlerCart() {
      if (this.isUpdate && !this.isConfig) {
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
          const flag = this.$refs.customGoodRef.getSelfDefinedField();
          if (!flag) return;
          try {
            const params = {
              product_id: this.id,
              config_options: {
                ...JSON.parse(JSON.stringify(this.params)),
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
                groupName: this.groupName,
              },
              qty: this.qty,
              customfield: this.promo,
              self_defined_field: this.self_defined_field,
            };
            if (
              this.baseConfig.free_disk_switch &&
              this.activeName === "custom"
            ) {
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
            const _temp = this.formatSwitch(params);
            const res = await addToCart(_temp);
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
          const flag = this.$refs.customGoodRef.getSelfDefinedField();
          if (!flag) return;
          try {
            const params = {
              position: this.position,
              product_id: this.id,
              config_options: {
                ...JSON.parse(JSON.stringify(this.params)),
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
                groupName: this.groupName,
              },
              qty: this.qty,
              customfield: this.promo,
              self_defined_field: this.self_defined_field,
            };
            if (
              this.baseConfig.free_disk_switch &&
              this.activeName === "custom"
            ) {
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
            this.dataLoading = true;
            const _temp = this.formatSwitch(params);
            const res = await updateCart(_temp);
            this.$message.success(res.data.msg);
            setTimeout(() => {
              location.href = "/cart/shoppingCar.htm";
            }, 300);
            this.dataLoading = false;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        }
      });
    },
    goToCart() {
      location.href = "/cart/shoppingCar.htm";
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
    changQty() {
      this.loadingPrice = true;
      this.changeConfig();
    },
    eventChange(evetObj) {
      if (this.eventData.id !== evetObj.id) {
        this.eventData.id = evetObj.id || "";
        this.promo.event_promotion = this.eventData.id;
        if (this.params.data_center_id) {
          this.changeConfig();
        }
      }
    },
    // 使用优惠码
    getDiscount(data) {
      this.promo.promo_code = data[1];
      this.changeConfig();
    },
    removeDiscountCode() {
      this.promo.promo_code = "";
      this.discount = 0;
      this.changeConfig();
    },
    // 获取镜像
    async getIamgeList() {
      try {
        const res = await getSystemList({ id: this.id });
        const temp = res.data.data.list;
        this.imageList = temp;
        if (!this.isUpdate && temp.length > 0) {
          this.imageName = this.version = temp[0].image[0]?.name;
          this.curImage = 0;
          this.curImageId = temp[0].id;
          this.params.image_id = temp[0].image[0]?.id;
        }
      } catch (error) {}
    },
    changeDuration() {
      this.loadingPrice = true;
      this.promo.promo_code = "";
      this.discount = 0;
      this.changeConfig();
    },
    // 获取周期
    async getCycleList() {
      try {
        this.loadingPrice = true;
        const params = JSON.parse(JSON.stringify(this.params));
        params.id = this.id;
        // 免费盘不传参数
        if (this.baseConfig.free_disk_switch && this.activeName === "custom") {
          params.data_disk.shift();
        }
        const hasDuration = params.duration_id;
        if (hasDuration && this.configLimitList.length === 0) {
          return this.changeConfig();
        }
        if (this.activeName === "fast") {
          params.recommend_config_id = this.packageId;
        }
        const res = await getDuration(params);
        let temp = res.data.data;
        // 根据限制处理周期
        if (this.configLimitList.length > 0) {
          if (this.activeName === "fast") {
            // 套餐
            const tempDur = this.configLimitList
              .reduce((all, cur) => {
                if (cur.rule.recommend_config) {
                  all.push(cur.rule);
                }
                return all;
              }, [])
              .filter((item) =>
                item.recommend_config.id.includes(params.recommend_config_id)
              );
            let packageArr = [],
              durationArr = [];
            tempDur.length > 0 &&
              tempDur.forEach((item) => {
                packageArr.push(...item.recommend_config.id);
                durationArr.push(...item.duration.id);
              });
            if (
              Array.from(new Set(packageArr)).includes(
                params.recommend_config_id
              )
            ) {
              temp = temp.filter((item) =>
                Array.from(new Set(durationArr)).includes(item.id)
              );
            }
            if (this.isInit && this.isUpdate) {
            } else {
              this.params.duration_id = "";
            }
          } else {
            // 自定义
          }
        }
        this.cycleList = temp;
        if (this.isInit && this.isUpdate) {
        } else {
          this.params.duration_id =
            this.params.duration_id || this.cycleList[0]?.id;
        }
        this.changeConfig();
      } catch (error) {
        console.log("error", error);
      }
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
        if (this.baseConfig.free_disk_switch && this.activeName === "custom") {
          params.config_options.data_disk.shift();
        }
        if (this.activeName === "fast") {
          params.config_options.recommend_config_id = this.packageId;
        }
        this.loadingPrice = true;
        const res = await calcPrice(params);

        this.totalPrice = res.data.data.price * 1;
        this.calcTotalPrice = res.data.data.price_total * 1;
        this.eventData.discount =
          res.data.data.price_event_promotion_discount * 1 || 0;
        this.discount = res.data.data.price_promo_code_discount * 1 || 0;
        this.levelNum = res.data.data.price_client_level_discount * 1 || 0;
        this.preview = res.data.data.preview;
        this.duration = res.data.data.duration;
        this.isInit = false;
        this.loadingPrice = false;
      } catch (error) {
        console.log("error", error);
        this.loadingPrice = false;
        this.$message.error(error.data.msg);
      }
    },
    // 获取通用配置
    getCommonData() {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"));
      document.title = this.commonData.website_name + "-" + this.tit;
    },
    mouseenter(index) {
      // if (index === this.curImage) {
      //   this.hover = true
      // }
      this.curImage = index;
      this.hover = true;
    },
    changeImage(item, index) {
      this.imageName = item.name;
      this.curImage = index;
      this.hover = true;
    },
    chooseVersion(ver, id) {
      this.curImageId = id;
      this.version = ver.name;
      this.params.image_id = ver.id;
      this.isManual = true;
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
    changeVpcMask(value) {
      switch (value) {
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
    changeVpcIp() {
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
}).$mount(template);
