const template = document.getElementsByClassName("template")[0];
Vue.prototype.lang = window.lang;
new Vue({
  components: {
    asideMenu,
    topMenu,
  },
  mixins: [mixin],
  mounted() {
    this.getConfig();
    this.hasDiscount = this.addons_js_arr.includes("PromoCode");
    window.addEventListener("message", (event) => this.handelIframeBuy(event));
  },
  destroyed() {},
  data() {
    return {
      id: "",
      tit: "",
      activeName: "fixed", // fixed flex
      hasDiscount: false,
      commonData: {},
      country: "",
      countryName: "",
      city: "",
      curImage: 0,
      imageName: "",
      version: "",
      curImageId: "",
      dataList: [], // 数据中心
      memMarks: {},
      limitList: [], // 限制
      packageId: "", // 套餐ID
      imageList: [], // 镜像
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
      lineDetail: {}, // 线路详情：bill_type, flow, bw, defence , ip
      lineName: "",
      lineType: "",
      bwName: "",
      defenseName: "",
      cpuName: "",
      memoryName: "",
      bwArr: [],
      bwMarks: {},
      bwTip: "",
      bwType: "",
      params: {
        // 配置参数
        data_center_id: "",
        image_id: 0,
        line_id: "",
        bw: "",
        flow: "",
        peak_defence: "",
        ip_num: "",
        duration_id: "",
        notes: "",
        model_config_id: "", // 型号id
        auto_renew: false,
        optional_memory: [], // 提交转换{id: num}
        optional_disk: [],
        package_id: "",
      },
      plan_way: 0,
      root_name: "root",
      hover: false,
      login_way: lang.auto_create, // 登录方式 auto_create
      rules: {
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
      loadingPrice: false,
      totalPrice: 0.0,
      preview: [],
      discount: "",
      duration: "",
      /* 优惠码 */
      promo: {
        scene: "new",
        promo_code: "",
        billing_cycle_time: "",
      },
      cartDialog: false,
      isInit: true,
      // 回调相关
      isUpdate: false,
      position: 0,
      backfill: {},
      // 公网ip
      ipData: [],
      ipName: "",
      modelArr: [],
      originModel: [],
      filterModle: [], // 根据区域过滤过的的型号数组
      cpuSelect: [], // 处理器选择
      memorySelect: [], // 内存选择
      curCpu: "",
      curMemory: "",
      isLogin: localStorage.getItem("jwt"),
      lineChange: false,
      showErr: false,
      showImage: false,
      isHide: true,
      isChangeAreaId: false,
      levelNum: 0,
      /* 套餐 */
      packageList: [],
      curPackageDetails: {
        optional_memory: [],
        optional_disk: [],
      },
      cloudIndex: 0,
      memorySwitch: false,
      diskSwitch: false,
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
  created() {
    this.id = this.getQuery("id");
    this.tit = this.getQuery("name");
    this.isUpdate = this.getQuery("change");
    this.getCommonData();
    this.getIamgeList();
    // 回显配置
    const temp = JSON.parse(sessionStorage.getItem("product_information"));
    if (this.isUpdate && temp.config_options) {
      this.backfill = temp.config_options;
      this.isChangeArea = false;
      const {
        country,
        countryName,
        city,
        curImage,
        version,
        curImageId,
        cloudIndex,
        imageName,
        peak_defence,
        activeName,
        memorySwitch,
        diskSwitch,
      } = this.backfill;
      this.promo = temp.customfield;
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
      this.memorySwitch = memorySwitch;
      this.diskSwitch = diskSwitch;
      this.defenseName =
        peak_defence == 0 ? lang.no_defense : peak_defence + "G";
    }
  },
  watch: {
    "params.image_id"(id) {
      if (id) {
        this.showImage = false;
      }
    },
    "params.data_center_id"(id) {
      this.curCpu = "";
      this.curMemory = "";
      if (this.isUpdate && this.isInit) {
        return;
      }
      this.params.peak_defence = "";
      this.defenseName = "";
      this.params.model_config_id = this.calcModel[0]?.id;
    },
    "params.line_id"(id) {
      // 区域改变，线路必定改变，根据线路改变拉取线路详情
      if (id) {
        this.lineChange = true;
        setTimeout(() => {
          this.lineType = this.lineList.filter(
            (item) => item.id === id
          )[0]?.bill_type;
          this.getLineDetails(id);
        });
      }
    },
    "params.model_config_id"(id) {
      if (this.isUpdate && this.isInit) {
        return;
      }
      // this.isChangeArea = true
      if (!this.isChangeArea) {
        if (this.lineType === "flow") {
          this.params.flow = this.calcFlowList[0].value;
          this.flowName = this.params.flow + "G";
        } else {
          if (this.bwType === "radio") {
            this.params.bw = this.calcBwList[0].value || "";
          } else {
            this.params.bw = this.calcBwRange[0] * 1 || "";
          }
          this.bwName =
            this.params.bw === "NC" ? lang.actual_bw : this.params.bw + "M";
        }
      }
      setTimeout(() => {
        if (!this.lineChange && !this.isInit) {
          this.getCycleList();
        }
      }, 0);
    },
  },
  computed: {
    calcTotalPrice() {
      if (
        this.totalPrice * 1 * this.qty - this.discount * 1 - this.levelNum * 1 >
        0
      ) {
        return (
          this.totalPrice * 1 * this.qty -
          this.discount * 1 -
          this.levelNum * 1
        ).toFixed(2);
      } else {
        return "0.00";
      }
    },
    calcArea() {
      const c = this.dataList.filter((item) => item.id === this.country * 1)[0]
        ?.name;
      return c + this.city;
    },
    calcAreaList() {
      // 计算区域列表
      const temp =
        this.dataList
          .filter((item) => item.id === this.country * 1)[0]
          ?.city.filter((item) => item.name === this.city)[0]?.area || [];
      if (!this.isChangeArea) {
        return temp;
      }
      if (this.isUpdate && this.isInit) {
      } else {
        this.area_name = temp[0]?.name;
        this.params.data_center_id = temp[0]?.id;
      }
      // 根据区域变化，筛选符合条件的机型
      const limitArr = Array.from(
        new Set(
          this.configLimitList
            .filter((item) => item.data_center_id === temp[0]?.id)
            .reduce((all, cur) => {
              all.push(...cur.model_config_id);
              return all;
            }, [])
        )
      );
      this.filterModle = this.modelArr.filter(
        (item) => !limitArr.includes(String(item.id))
      );
      this.cpuSelect = this.modelArr.reduce((all, cur) => {
        const arr = all.filter((item) => item.value === cur.cpu);
        if (arr.length === 0) {
          all.push({
            value: cur.cpu,
            label: cur.cpu,
          });
        }
        return all;
      }, []);
      this.memorySelect = this.modelArr.reduce((all, cur) => {
        const arr = all.filter((item) => item.value === cur.memory);
        if (arr.length === 0) {
          all.push({
            value: cur.memory,
            label: cur.memory,
          });
        }
        return all;
      }, []);
      // 区域改变的时候，也需要计算线路展示
      return temp;
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
    calcCartName() {
      return this.isUpdate ? lang.product_sure_check : lang.product_add_cart;
    },
    calcModel() {
      // 需要处理区域下面值设置了机型的时候，代表该机型可可选
      const arr = this.configLimitList.filter(
        (item) =>
          item.data_center_id === this.params.data_center_id &&
          item.line_id === 0
      );
      if (arr.length > 0) {
        const modelArr = Array.from(
          new Set(
            arr.reduce((all, cur) => {
              all.push(...cur.model_config_id);
              return all;
            }, [])
          )
        );
        this.modelArr = this.originModel.filter(
          (item) => !modelArr.includes(String(item.id))
        );
      } else {
        this.modelArr = this.originModel;
      }
      const temp = this.modelArr.reduce((all, cur) => {
        if (!this.curCpu && !this.curMemory) {
          all.push(cur);
        } else if (this.curCpu && !this.curMemory) {
          if (cur.cpu === this.curCpu) {
            all.push(cur);
          }
        } else if (!this.curCpu && this.curMemory) {
          if (cur.memory === this.curMemory) {
            all.push(cur);
          }
        } else {
          if (cur.cpu === this.curCpu && cur.memory === this.curMemory) {
            all.push(cur);
          }
        }
        return all;
      }, []);
      return temp;
    },
    calcSpecs() {
      return this.modelArr.filter(
        (item) => item.id === this.params.model_config_id
      )[0]?.name;
    },
    calcBwList() {
      // 根据区域，机型以及 线路来判断计算可选带宽  单选
      const temp =
        this.configLimitList.filter(
          (item) =>
            this.params.data_center_id === item.data_center_id &&
            this.params.line_id === item.line_id &&
            item.model_config_id.includes(String(this.params.model_config_id))
        ) || [];
      const bw = temp.reduce((all, cur) => {
        if (cur.min_bw) {
          all.push(...this.createArr([cur.min_bw * 1, cur.max_bw * 1]));
        }
        return all;
      }, []);
      return this.lineDetail.bw.filter((item) => !bw.includes(item.value));
    },
    calcBwRange() {
      // 根据区域，线路来判断计算可选带宽  范围
      const temp =
        this.configLimitList.filter(
          (item) =>
            this.params.data_center_id === item.data_center_id &&
            this.params.line_id === item.line_id &&
            item.model_config_id.includes(String(this.params.model_config_id))
        ) || [];
      if (temp.length === 0) {
        // 没有匹配到限制条件
        this.bwTip = this.createTip(this.bwArr);
        this.bwMarks = this.createMarks(this.bwArr);
        return this.bwArr || [];
      }
      let fArr = [];
      temp.forEach((item) => {
        fArr.push(...this.createArr([item.min_bw * 1, item.max_bw * 1]));
      });
      fArr = Array.from(new Set(fArr));
      const filterArr = this.bwArr.filter((item) => !fArr.includes(item));
      this.bwTip = this.createTip(filterArr);
      this.bwMarks = this.createMarks(filterArr); // data 原数据，目标marks
      return filterArr.filter((item) => !fArr.includes(item));
    },
    calcFlowList() {
      // 根据区域，线路来判断计算可选带宽  单选
      const temp =
        this.configLimitList.filter(
          (item) =>
            this.params.data_center_id === item.data_center_id &&
            this.params.line_id === item.line_id &&
            item.model_config_id.includes(String(this.params.model_config_id))
        ) || [];

      const flow = temp.reduce((all, cur) => {
        if (cur.min_flow) {
          all.push(...this.createArr([cur.min_flow * 1, cur.max_flow * 1]));
        }
        return all;
      }, []);
      return this.lineDetail.flow.filter((item) => !flow.includes(item.value));
    },
    calcLineList() {
      // 区域，机型改变重置线路
      const temp =
        this.dataList
          .filter((item) => item.id === this.country * 1)[0]
          ?.city.filter((item) => item.name === this.city)[0]?.area || [];
      // 如果限制里面对应的线路，流量和带宽均无说明是全限制，则不显示tab
      const areaLimt = this.configLimitList.filter(
        (item) =>
          item.data_center_id === this.params.data_center_id &&
          item.model_config_id.includes(String(this.params.model_config_id)) &&
          item.min_bw === "" &&
          item.min_flow === ""
      );
      let lineId = []; // 限制里面的线路id
      if (areaLimt.length > 0) {
        lineId = Array.from(
          new Set(
            areaLimt.reduce((all, cur) => {
              all.push(cur.line_id);
              return all;
            }, [])
          )
        );
      }
      if (temp.length > 0) {
        this.lineList = temp
          .filter((item) => item.id === this.params.data_center_id)[0]
          ?.line.filter((item) => !lineId.includes(item.id));
        if (!this.isChangeArea) {
          this.lineName = this.lineList.filter(
            (item) => item.id === this.params.line_id
          )[0]?.name;
        } else {
          this.params.line_id = this.lineList[0]?.id;
          this.lineName = this.lineList[0]?.name;
        }
      }
      return this.lineList;
    },
    calcImageList() {
      if (!this.isHide) {
        return this.imageList;
      } else {
        const temp = JSON.parse(JSON.stringify(this.imageList));
        if (this.imageList.length <= 5) {
          return temp.splice(0, 5);
        } else {
          return temp.splice(0, 4);
        }
      }
    },
    calcIpUnit() {
      return (val) => {
        if (val === "NC") {
          return lang.actual_ip;
        }
        let temp = "";
        if (val.includes("_")) {
          temp = val.split(",").reduce((all, cur) => {
            all += cur.split("_")[0] * 1;
            return all;
          }, 0);
        } else {
          temp = val;
        }
        return `${temp}${lang.mf_one}`;
      };
    },
    /* 灵活 */
    calcIpNum() {
      return this.packageList.filter(
        (item) => item.id === this.params.package_id
      )[0]?.ip_num;
    },
    calcBwNum() {
      return this.packageList.filter(
        (item) => item.id === this.params.package_id
      )[0]?.bw;
    },
    calcMemoryNum() {
      if (!(this.params.optional_memory instanceof Array)) {
        return;
      }
      // 处理显示还可以选择多少条内存，计算槽位
      const total =
        this.curPackageDetails.mem_max_num -
        this.curPackageDetails.memory_slot_used;
      if (this.params.optional_memory.length === 0) {
        return total;
      }
      const cur = this.params.optional_memory.reduce((all, cur) => {
        all += cur.other_config.memory_slot * cur.num;
        return all;
      }, 0);
      return total - cur > 0 ? total - cur : 0;
    },
    // 计算配置项是否可选： 槽位 和 数量限制
    calcConfigDisabled() {
      return (item) => {
        if (!(this.params.optional_memory instanceof Array)) {
          return;
        }
        if (
          this.curPackageDetails.mem_max_num === 0 &&
          this.curPackageDetails.mem_max === 0
        ) {
          return false;
        }
        // 只针对未选中项
        if (item.checked) {
          return false;
        }
        // 槽位超出
        const total =
          this.curPackageDetails.mem_max_num -
          this.curPackageDetails.memory_slot_used;
        const cur = this.params.optional_memory.reduce((all, cur) => {
          all += cur.other_config.memory_slot * cur.num;
          return all;
        }, 0);
        if (
          cur + item.other_config.memory_slot > total &&
          this.curPackageDetails.mem_max_num !== 0
        ) {
          return true;
        }
        // 数量超出
        const num =
          this.curPackageDetails.mem_max - this.curPackageDetails.memory_used;
        const usedNum = this.params.optional_memory.reduce((all, cur) => {
          all += cur.other_config.memory * cur.num;
          return all;
        }, 0);
        if (
          usedNum + item.other_config.memory > num &&
          this.curPackageDetails.mem_max !== 0
        ) {
          return true;
        }
      };
    },
    calcMemMax() {
      // 需要兼顾 槽位 和 数量
      return (item) => {
        if (!(this.params.optional_memory instanceof Array)) {
          return
        }
        // 槽位
        const total =
          this.curPackageDetails.mem_max_num -
          this.curPackageDetails.memory_slot_used;
        const cur =
          this.params.optional_memory.reduce((all, cur) => {
            all += cur.other_config.memory_slot * cur.num;
            return all;
          }, 0) -
          item.num * item.other_config.memory_slot;
        // 容量
        const num =
          this.curPackageDetails.mem_max - this.curPackageDetails.memory_used;
        const usedNum =
          this.params.optional_memory.reduce((all, cur) => {
            all += cur.other_config.memory * cur.num;
            return all;
          }, 0) -
          item.other_config.memory * item.num;

        const slotNum = Math.floor(
          (total - cur) / item.other_config.memory_slot
        );
        const allNum = Math.floor((num - usedNum) / item.other_config.memory);
        if (
          this.curPackageDetails.mem_max_num === 0 &&
          this.curPackageDetails.mem_max === 0
        ) {
          return Infinity;
        } else if (this.curPackageDetails.mem_max_num === 0) {
          return allNum;
        } else if (this.curPackageDetails.mem_max === 0) {
          return slotNum;
        } else {
          return slotNum > allNum ? allNum : slotNum;
        }
      };
    },
    /* 计算硬盘 */
    calcDiskNum() {
      if (!(this.params.optional_disk instanceof Array)) {
        return
      }
      const total =
        this.curPackageDetails.disk_max_num - this.curPackageDetails.disk_used;
      if (this.params.optional_disk.length === 0) {
        return total;
      }
      const cur = this.params.optional_disk.reduce((all, cur) => {
        all += cur.num;
        return all;
      }, 0);
      return total - cur > 0 ? total - cur : 0;
    },
    calcDiskDisabled() {
      return (item) => {
        if (!(this.params.optional_disk instanceof Array)) {
          return
        }
        if (this.curPackageDetails.disk_max_num === 0) {
          return false;
        }
        // 只针对未选中项
        if (item.checked) {
          return false;
        }
        // 数量超出
        const num =
          this.curPackageDetails.disk_max_num -
          this.curPackageDetails.disk_used;
        const usedNum = this.params.optional_disk.reduce((all, cur) => {
          all += cur.num;
          return all;
        }, 0);
        if (usedNum >= num) {
          return true;
        }
      };
    },
    calcDiskMax() {
      return (item) => {
        if (!(this.params.optional_disk instanceof Array)) {
          return
        }
        if (this.curPackageDetails.disk_max_num === 0) {
          return Infinity;
        }
        const num =
          this.curPackageDetails.disk_max_num -
          this.curPackageDetails.disk_used;
        const usedNum =
          this.params.optional_disk.reduce((all, cur) => {
            all += cur.num;
            return all;
          }, 0) - item.num;
        return num - usedNum;
      };
    },
  },

  methods: {
    /* 灵活配置 */
    handleClick() {
      // 重置通用数据
      this.getConfig();
    },
    // 切换套餐
    async changeRecommend(item) {
      try {
        if (this.params.package_id === item.id) {
          return;
        }
        this.params.package_id = item.id;
        // 赋值
        const package = await getPackageDetail({
          id: this.id,
          package_id: this.params.package_id,
        });
        this.curPackageDetails = package.data.data;
        this.params.package_id = item.id;
        this.params.optional_memory = [];
        this.params.optional_disk = [];
        setTimeout(() => {
          this.getCycleList();
        }, 10);
      } catch (error) {
        console.log(error);
      }
    },
    checkOption(type, item, bol) {
      if (bol) {
        // 不可选
        return;
      }
      item.checked = !item.checked;
      if (item.checked) {
        this.params[type].push({
          ...item,
          num: 1,
        });
      } else {
        const index = this.params[type].findIndex((el) => el.id === item.id);
        this.params[type].splice(index, 1);
      }
      this.changeConfig();
    },
    changeDiskSwitch(e, type) {
      if (!e) {
        this.params[type] = [];
        this.curPackageDetails[type].forEach((item) => (item.checked = false));
        this.changeConfig();
      }
    },
    /* 灵活配置 end */
    // iframe buy
    async handelIframeBuy(e) {
      this.$refs.orderForm && this.$refs.orderForm.validate(async (res) => {
        if (res) {
          const bol = this.formatData();
          if (bol !== true) {
            return;
          }
          try {
            const params = {
              product_id: this.id,
              config_options: {
                ...this.params,
              },
              qty: this.qty,
              customfield: this.promo,
            };
            if (this.lineDetail.bill_type === "bw") {
              delete params.flow;
            } else {
              delete params.bw;
            }
            if (params.config_options.bw === lang.actual_bw) {
              params.config_options.bw = "NC";
            }
            const postObj = {
              type: "iframeBuy",
              params,
              price: this.calcTotalPrice,
            };
            if (e.data.type === "iframeBuy") {
              window.parent.postMessage(postObj, "*");
            }
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        }
      });
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
        if (this.activeName === "flex") {
          params.scene = "package";
        }
        const res = await getOrderConfig(params);
        const temp = res.data.data;
        // 通用数据处理
        this.dataList = temp.data_center;
        this.originModel = this.modelArr = temp.model_config;
        this.configLimitList = temp.config_limit;
        this.packageList = temp.package;
        // 如果没有推荐配置，跳转到自定义，重新获取数据
        if (this.originModel.length === 0) {
          this.activeName = "flex";
          this.showFast = false;
          this.getConfig();
          return;
        }
        // 初始化数据
        if (!this.isUpdate) {
          // 不是回填
          this.params = {
            data_center_id: "",
            image_id: this.imageList[0]?.image[0]?.id,
            line_id: "",
            bw: "",
            flow: "",
            peak_defence: "",
            ip_num: "",
            duration_id: "",
            notes: "",
            model_config_id: "", // 型号id
            auto_renew: false,
            optional_memory: [], // 提交转换{id: num}
            optional_disk: [],
            package_id: "",
          };
          this.qty = 1;
          this.country = String(this.dataList[0]?.id);
          this.countryName = String(this.dataList[0]?.name);
          this.city = String(this.dataList[0]?.city[0]?.name);
          this.cloudIndex = 0;
          this.params.model_config_id = this.modelArr[0].id;
          if (this.activeName === "flex") {
            this.params.package_id = this.packageList[0]?.id;
          }
        } else {
          // 回填数据
          this.params = this.backfill;
          // 根据区域变化，筛选符合条件的机型
          const filArea =
            this.dataList
              .filter((item) => item.id === this.country * 1)[0]
              ?.city.filter((item) => item.name === this.city)[0]?.area || [];
          const limitArr = Array.from(
            new Set(
              this.configLimitList
                .filter((item) => item.data_center_id === filArea[0]?.id)
                .reduce((all, cur) => {
                  all.push(...cur.model_config_id);
                  return all;
                }, [])
            )
          );
          this.filterModle = this.modelArr.filter(
            (item) => !limitArr.includes(String(item.id))
          );
          this.cpuSelect = this.filterModle.reduce((all, cur) => {
            const arr = all.filter((item) => item.value === cur.cpu);
            if (arr.length === 0) {
              all.push({
                value: cur.cpu,
                label: cur.cpu,
              });
            }
            return all;
          }, []);
          this.memorySelect = this.filterModle.reduce((all, cur) => {
            const arr = all.filter((item) => item.value === cur.memory);
            if (arr.length === 0) {
              all.push({
                value: cur.memory,
                label: cur.memory,
              });
            }
            return all;
          }, []);
        }
        this.totalPrice = 0.0;
        this.isInit = true;
        this.handlerCustom();
      } catch (error) {
        console.log("@@@", error);
      }
    },
    // 切换自定义配置
    handlerCustom() {
      if (!this.isUpdate) {
      } else {
        // 回填
        this.area_name = this.calcAreaList.filter(
          (item) => item.id === this.params.data_center_id
        )[0]?.name;
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
        // 公网IP
        this.ipData = this.lineDetail.ip;
        if (this.isUpdate && this.isInit) {
        } else {
          if (this.ipData.length > 0) {
            this.params.ip_num = this.ipData[0].value;
          } else {
            this.params.ip_num = "";
          }
        }

        this.ipName = this.calcIpUnit(this.params.ip_num);
        if (this.lineDetail.bw) {
          this.bwType = this.lineDetail.bw[0]?.type;
          if (this.isInit && this.isUpdate) {
            // 初次回填
          } else {
            if (this.bwType === "radio") {
              this.params.bw = this.calcBwList[0].value || "";
            } else {
              this.params.bw = this.calcBwRange[0] * 1 || 1;
            }
            // this.params.bw = this.calcBwList[0]?.value || this.calcBwRange[0] * 1 || 1
          }
          this.bwName =
            this.params.bw === "NC" ? lang.actual_bw : this.params.bw + "M";
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
            this.params.flow = this.calcFlowList[0]?.value || "";
          }
          this.flowName =
            this.params.flow > 0 ? this.params.flow + "G" : lang.mf_tip28;
        }
        this.bwMarks = this.createMarks(this.bwArr);
        // 11-22 防御改成默认选中首个
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
        // 根据类型，flex 拉取套餐详情
        if (this.activeName === "flex") {
          const package = await getPackageDetail({
            id: this.id,
            package_id: this.params.package_id,
          });
          const temp = package.data.data;
          temp.optional_memory = temp.optional_memory.map((item) => {
            item.checked = false;
            return item;
          });
          temp.optional_disk = temp.optional_disk.map((item) => {
            item.checked = false;
            return item;
          });
          // 重置灵活套餐数据
          this.curPackageDetails = temp;
          if (this.isUpdate && this.isInit) {
            // 首次回填处理灵活配置的参数
            this.curPackageDetails.optional_memory =
              this.curPackageDetails.optional_memory.map((item) => {
                if (
                  Object.keys(this.backfill.optional_memory).includes(
                    String(item.id)
                  )
                ) {
                  item.checked = true;
                } else {
                  item.checked = false;
                }
                return item;
              });
            this.curPackageDetails.optional_disk =
              this.curPackageDetails.optional_disk.map((item) => {
                if (
                  Object.keys(this.backfill.optional_disk).includes(
                    String(item.id)
                  )
                ) {
                  item.checked = true;
                } else {
                  item.checked = false;
                }
                return item;
              });
            this.params.optional_memory = Array.from(
              Object.keys(this.backfill.optional_memory)
            ).reduce((all, cur) => {
              const _temp = this.curPackageDetails.optional_memory.filter(
                (item) => item.id == cur
              )[0];
              all.push({
                id: cur,
                num: this.backfill.optional_memory[cur],
                value: _temp.value,
                other_config: _temp.other_config,
                checked: true,
              });
              return all;
            }, []);
            this.params.optional_disk = Array.from(
              Object.keys(this.backfill.optional_disk)
            ).reduce((all, cur) => {
              all.push({
                id: cur,
                num: this.backfill.optional_disk[cur],
                value: this.curPackageDetails.optional_disk.filter(
                  (item) => item.id == cur
                )[0]?.value,
              });
              return all;
            }, []);
          } else {
            this.memorySwitch = false;
            this.diskSwitch = false;
            this.params.optional_memory = [];
            this.params.optional_disk = [];
          }
        }
        setTimeout(() => {
          this.getCycleList();
        }, 0);
      } catch (error) {
        console.log("####", error);
      }
    },
    changeBw(e) {
      if (e === lang.actual_bw) {
        this.params.bw = "NC";
      } else {
        this.params.bw = e.replace("M", "");
      }
      // 计算价格
      setTimeout(() => {
        this.getCycleList();
      }, 0);
    },
    changeBwNum(num) {
      if (!this.calcBwRange.includes(num)) {
        this.calcBwRange.forEach((item, index) => {
          if (num > item && num < this.calcBwRange[index + 1]) {
            this.params.bw =
              num - item > this.calcBwRange[index + 1] - num
                ? this.calcBwRange[index + 1]
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
      this.params.peak_defence = c.value;
      this.defenseName = c.value == 0 ? lang.no_defense : c.value + "G";
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
    // 切换IP
    changeIp(e) {
      if (e === lang.actual_ip) {
        this.params.ip_num = "NC";
      } else {
        let temp = e.replace(lang.mf_one, "") * 1;
        this.params.ip_num = this.ipData.filter((item) => {
          if (item.value !== "NC") {
            return (
              (item.value,
              item.value.split(",").reduce((all, cur) => {
                all += cur.split("_")[0] * 1;
                return all;
              }, 0)) === temp
            );
          }
        })[0]?.value;
      }
      setTimeout(() => {
        this.getCycleList();
      }, 0);
    },
    createArr([m, n]) {
      // 生成数组
      let temp = [];
      for (let i = m; i <= n; i++) {
        temp.push(i * 1);
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
    // 选择区域
    changeArea(e) {
      this.isChangeArea = false;
      // 手动切换区域不初始化第一个区域
      this.params.data_center_id = this.calcAreaList.filter(
        (item) => item.name === e
      )[0]?.id;
      this.area_name = this.calcAreaList.filter(
        (item) => item.name === e
      )[0]?.name;
      this.lineList = this.calcAreaList.filter(
        (item) => item.name === e
      )[0]?.line;
      this.params.line_id = this.lineList[0].id;
      this.lineName = this.lineList[0].name;
    },
    // 选择先线路
    chooseLine(item) {
      this.params.data_center_id = item.data_center_id;
      this.params.line_id = item.id;
    },
    // 切换城市
    changeCity(e, city) {
      this.isChangeArea = true;
      this.cloudIndex = 0;
    },
    tableRowClassName({ row, rowIndex }) {
      row.index = rowIndex;
    },

    // 提交前格式化数据
    formatData() {
      // if (!this.params.image_id) {
      //   document.getElementById('image').scrollIntoView({ behavior: "smooth" })
      //   this.showImage = true
      //   return
      // }
      // ssh
      if (this.login_way === lang.security_tab1 && !this.params.ssh_key_id) {
        return this.$message.warning(
          `${lang.placeholder_pre2}${lang.security_tab1}`
        );
      }
      return true;
    },
    // 立即购买
    async submitOrder() {
      this.$refs.orderForm.validate(async (res) => {
        if (res) {
          const bol = this.formatData();
          if (bol !== true) {
            return;
          }
          try {
            const params = {
              product_id: this.id,
              config_options: {
                ...this.params,
              },
              qty: this.qty,
              customfield: this.promo,
            };
            if (this.lineDetail.bill_type === "bw") {
              delete params.flow;
            } else {
              delete params.bw;
            }
            if (params.config_options.bw === lang.actual_bw) {
              params.config_options.bw = "NC";
            }
            // 处理灵活配置参数
            if (this.activeName === "flex") {
              params.config_options.optional_memory = this.formatConfig(
                params.config_options.optional_memory
              );
              params.config_options.optional_disk = this.formatConfig(
                params.config_options.optional_disk
              );
            }
            // 直接传配置到结算页面
            sessionStorage.setItem(
              "product_information",
              JSON.stringify(params)
            );
            location.href = `settlement.htm?id=${params.product_id}`;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        }
      });
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
                memorySwitch: this.memorySwitch,
                diskSwitch: this.diskSwitch,
              },
              qty: this.qty,
              customfield: this.promo,
            };
            if (params.config_options.bw === lang.actual_bw) {
              params.config_options.bw = "NC";
            }
            if (this.lineDetail.bill_type === "bw") {
              delete params.flow;
            } else {
              delete params.bw;
            }
            if (this.activeName === "fixed") {
              delete params.package_id;
            } else {
              delete params.model_config_id;
            }
            // 处理灵活配置参数
            if (this.activeName === "flex") {
              params.config_options.optional_memory = this.formatConfig(
                params.config_options.optional_memory
              );
              params.config_options.optional_disk = this.formatConfig(
                params.config_options.optional_disk
              );
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
                memorySwitch: this.memorySwitch,
                diskSwitch: this.diskSwitch,
              },
              qty: this.qty,
              customfield: this.promo,
            };
            if (params.config_options.bw === lang.actual_bw) {
              params.config_options.bw = "NC";
            }
            if (this.lineDetail.bill_type === "bw") {
              delete params.flow;
            } else {
              delete params.bw;
            }
            if (this.activeName === "fixed") {
              delete params.package_id;
            } else {
              delete params.model_config_id;
            }
            // 处理灵活配置参数
            if (this.activeName === "flex") {
              params.config_options.optional_memory = this.formatConfig(
                params.config_options.optional_memory
              );
              params.config_options.optional_disk = this.formatConfig(
                params.config_options.optional_disk
              );
            }
            this.dataLoading = true;
            const res = await updateCart(params);
            this.$message.success(res.data.msg);
            setTimeout(() => {
              location.href = `shoppingCar.htm`;
            }, 300);
            this.dataLoading = false;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        }
      });
    },
    goToCart() {
      location.href = `shoppingCar.htm`;
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
        this.$message.success(res.data.msg);
        this.discount = res.data.data.discount;
        this.dis_visible = false;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    canclePromo() {
      this.discount = "";
      this.promo.promo_code = "";
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
        }
      } catch (error) {}
    },
    changeDuration() {
      this.loadingPrice = true;
      this.promo.promo_code = "";
      this.discount = "";
      this.changeConfig();
    },
    // 获取周期
    async getCycleList() {
      try {
        this.lineChange = false;
        this.loadingPrice = true;
        const params = JSON.parse(JSON.stringify(this.params));
        params.id = this.id;
        if (this.activeName === "fixed") {
          delete params.package_id;
        } else {
          delete params.model_config_id;
        }
        const hasDuration = params.duration_id;
        if (params.bw === lang.actual_bw) {
          params.bw = "NC";
        }
        if (hasDuration) {
          this.changeConfig();
        }
        const res = await getDuration(params);
        this.cycleList = res.data.data;
        this.params.duration_id =
          this.params.duration_id || this.cycleList[0]?.id;
        if (!hasDuration) {
          this.changeConfig();
        }
      } catch (error) {}
    },
    formatConfig(data) {
      if (data.length === 0) {
        return;
      }
      return data.reduce((all, cur) => {
        all[Number(cur.id)] = cur.num;
        return all;
      }, {});
    },
    // 更改配置计算价格
    async changeConfig() {
      try {
        const params = {
          id: this.id,
          config_options: {
            ...this.params,
          },
          qty: this.qty,
        };
        if (params.config_options.bw === lang.actual_bw) {
          params.config_options.bw = "NC";
        }
        if (this.activeName === "fixed") {
          delete params.config_options.package_id;
        } else {
          delete params.config_options.model_config_id;
        }
        // 处理灵活配置参数
        if (this.activeName === "flex") {
          params.config_options.optional_memory = this.formatConfig(
            params.config_options.optional_memory
          );
          params.config_options.optional_disk = this.formatConfig(
            params.config_options.optional_disk
          );
        }
        this.loadingPrice = true;
        const res = await calcPrice(params);
        this.totalPrice = res.data.data.price;
        this.preview = res.data.data.preview;
        this.duration = res.data.data.duration;
        this.isInit = false;
        // 如果已使用优惠码，计算价格过后重新计算
        if (this.promo.promo_code) {
          this.useDiscount();
        }
        // 计算等级折扣
        if (
          this.isLogin &&
          this.addons_js_arr.includes("IdcsmartClientLevel")
        ) {
          const level = await getLevelDiscount({
            id: this.id,
            amount: this.totalPrice * this.qty,
          });
          this.levelNum = level.data.data.discount;
        }
        this.loadingPrice = false;
      } catch (error) {
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
      this.getCycleList();
    },
  },
}).$mount(template);
