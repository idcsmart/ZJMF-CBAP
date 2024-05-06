const { showToast } = vant;
window.lang = Object.assign(window.lang, window.plugin_lang);

const app2 = Vue.createApp({
  components: {
    topMenu,
    vanSelect,
  },
  created() {
    this.analysisUrl();
    this.inputChange();
    this.getCommon();
  },
  data() {
    return {
      lang: window.lang,
      imgUrl: `${url}`,
      id: 0,
      menuActiveId: 1,
      hostData: {},
      commonData: {},
      self_defined_field: [],
      menuList: [
        {
          id: 1,
          text: lang.cloud_menu_1,
        },
        {
          id: 2,
          text: lang.cloud_menu_2,
        },
        {
          id: 3,
          text: lang.cloud_menu_3,
        },
        {
          id: 4,
          text: lang.cloud_menu_4,
        },
        {
          id: 5,
          text: lang.cloud_menu_5,
        },
      ],
      powerStatus: {
        on: {
          text: lang.common_cloud_text10,
          icon: `/plugins/reserver/mf_cloud/template/clientarea/mobile/overseas/img/cloud/on.svg`,
        },
        off: {
          text: lang.common_cloud_text11,
          icon: `/plugins/reserver/mf_cloud/template/clientarea/mobile/overseas/img/cloud/off.svg`,
        },
        operating: {
          text: lang.common_cloud_text12,
          icon: `/plugins/reserver/mf_cloud/template/clientarea/mobile/overseas/img/cloud/operating.svg`,
        },
        fault: {
          text: lang.common_cloud_text86,
          icon: `/plugins/reserver/mf_cloud/template/clientarea/mobile/overseas/img/cloud/fault.svg`,
        },
        suspend: {
          text: lang.common_cloud_text87,
          icon: `/plugins/reserver/mf_cloud/template/clientarea/mobile/overseas/img/cloud/suspended.svg`,
        },
      },
      finished: false,
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
          color: "#F99600",
          bgColor: "#FFF4DE",
        },
        Deleted: {
          text: lang.common_cloud_text92,
          color: "#9696A3",
          bgColor: "#F2F2F7",
        },
        Failed: {
          text: lang.common_cloud_text93,
          color: "#3699FF",
          bgColor: "#E1F0FF",
        },
      },
      statusSelect: [
        {
          id: 1,
          status: "Unpaid",
          label: lang.common_cloud_text88,
        },
        {
          id: 2,
          status: "Pending",
          label: lang.common_cloud_text89,
        },
        {
          id: 3,
          status: "Active",
          label: lang.common_cloud_text90,
        },
        {
          id: 4,
          status: "Suspended",
          label: lang.common_cloud_text91,
        },
        {
          id: 5,
          status: "Deleted",
          label: lang.common_cloud_text92,
        },
      ],
      // 数据中心
      center: [],
      // 产品列表
      cloudData: [],
      loading: false,
      params: {
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 200,
        orderby: "id",
        sort: "desc",
        keywords: "",
        data_center_id: "",
        status: "",
        m: null,
      },
      timerId: null,
      showFillter: false,
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
  },
  methods: {
    handelSelectCenter(item) {
      if (this.params.data_center_id !== item.id) {
        this.params.data_center_id = item.id;
      } else {
        this.params.data_center_id = "";
      }
    },
    handelSelectStatue(item) {
      if (this.params.status !== item.status) {
        this.params.status = item.status;
      } else {
        this.params.status = "";
      }
    },
    analysisUrl() {
      let url = window.location.href;
      let getqyinfo = url.split("?")[1];
      let getqys = new URLSearchParams("?" + getqyinfo);
      let m = getqys.get("m");
      this.params.m = m;
    },
    getCommon() {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"));
      document.title =
        this.commonData.website_name + "-" + lang.common_cloud_text94;
    },
    // 切换分页
    sizeChange(e) {
      this.params.limit = e;
      this.params.page = 1;
      this.getCloudList();
    },
    currentChange(e) {
      this.params.page = e;
      this.getCloudList();
    },
    // 数据中心选择框变化时
    selectChange() {
      this.params.page = 1;
      this.getCloudList();
    },
    inputChange() {
      this.params.page = 1;
      this.cloudData = [];
      this.loading = true;
      this.getCloudList();
    },
    centerSelectChange() {
      this.params.page = 1;
      this.getCloudList();
    },
    statusSelectChange() {
      this.params.page = 1;
      this.getCloudList();
    },
    // 获取产品列表
    getCloudList() {
      cloudList(this.params).then((res) => {
        if (res.data.status === 200) {
          const list = res.data.data.list.map((item) => {
            item.isInit = true;
            return item;
          });
          // 循环获取产品详情
          this.cloudData = this.cloudData.concat(list);
          this.cloudData.forEach((item) => {
            item.isInit && this.getHostInfo(item);
          });
          this.self_defined_field = res.data.data.self_defined_field;
          this.params.total = res.data.data.count;
          this.params.page++;
          this.loading = false;
          if (this.cloudData.length >= res.data.data.count) {
            this.finished = true;
          } else {
            this.finished = false;
          }
          const area = res.data.data.data_center;
          area &&
            area.map((item) => {
              item.label =
                item.country_name + "-" + item.city + "-" + item.area;
              return item;
            });
          this.center = area;
        }
      });
    },
    async getHostInfo(item) {
      try {
        item.loading = true;
        const data = await getHostDetail({ id: item.id });
        const res = data.data.data;
        item.country = res.data_center.country;
        item.city = res.data_center.city;
        item.area = res.data_center.area;
        item.country_code = res.data_center.iso;
        item.power_status = res.power_status;
        item.ip = res.ip;
        item.image_group_name = res.image.image_group_name;
        item.image_name = res.image.name;
        item.loading = false;
      } catch (error) {}
    },
    // 跳转产品详情
    toDetail(row) {
      location.href = `productdetail.htm?id=${row.id}`;
    },
  },
});
window.directiveInfo.forEach((item) => {
  app2.directive(item.name, item.fn);
});
app2.use(vant).mount("#product-template");
