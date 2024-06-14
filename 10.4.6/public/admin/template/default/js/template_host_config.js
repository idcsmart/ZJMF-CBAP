(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          theme: "",

          tab: "template_host_config.htm",
          backUrl: `${str}configuration_theme.htm?name=web_switch`,
          id: "",
          name: "",
          data: [],
          tabList: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          submitLoading: false,
          hover: true,
          loading: false,
          // 图片上传相关
          uploadUrl: str + "v1/upload",
          // uploadUrl: 'https://kfc.idcsmart.com/admin/v1/upload',
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          // banner
          bannerName: "",
          bannerColumns: [
            {
              colKey: "drag",
              width: 30,
              className: "drag-icon",
            },
            { colKey: "img", title: lang.tem_banner, width: "300" },
            {
              colKey: "url",
              title: lang.tem_jump_link,
              width: "200",
              ellipsis: true,
            },
            {
              colKey: "time",
              title: lang.tem_time_range,
              width: "230",
              ellipsis: true,
            },
            {
              colKey: "show",
              title: lang.tem_show,
              width: "100",
              ellipsis: true,
            },
            {
              colKey: "notes",
              title: lang.tem_notes,
              width: "150",
              ellipsis: true,
            },
            { colKey: "op", title: lang.tem_opt, width: "120" },
          ],
          isDelBanner: false,
          tempBanner: [],
          editFile: [],
          editItem: {
            id: "",
            url: "",
            img: [],
            show: false,
            notes: "",
            edit: false,
            timeRange: [],
          },
          delVisible: false,
          delLoading: false,
          curId: "",
          optType: "",
          optTitle: "",
          currency_prefix:
            JSON.parse(localStorage.getItem("common_set")).currency_prefix ||
            "¥",
          /* 侧边栏 */
          curValue: "",
          sliderArr: [
            {
              value: "cloud",
              label: lang.temp_host_cloud,
            },
            {
              value: "dcim",
              label: lang.temp_host_dcim,
            },
            {
              value: "ssl",
              label: lang.temp_host_ssl,
            },
            {
              value: "sms",
              label: lang.temp_host_sms,
            },
            {
              value: "brand",
              label: lang.temp_host_brand,
            },
            {
              value: "server",
              label: lang.temp_host_server,
            },
            {
              value: "cabinet",
              label: lang.temp_host_cabinet,
            },
            {
              value: "icp",
              label: lang.temp_host_icp,
            },
          ],
          // 基础表格
          baseColumns: [
            { colKey: "title", title: lang.temp_title, ellipsis: true },
            { colKey: "description", title: lang.description, ellipsis: true },
            {
              colKey: "price",
              title: lang.temp_price,
              width: "200",
              ellipsis: true,
            },
            { colKey: "op", title: lang.tem_opt, width: "120" },
          ],
          baseFormData: {
            id: "",
            title: "",
            price: null,
            description: "",
            product_id: "",
            price_unit: "month",
            url: "",
          },
          baseRules: {
            title: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_title}`,
                type: "error",
              },
            ],
            price: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_price}`,
                type: "error",
              },
            ],
            description: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_description}`,
                type: "error",
              },
            ],
            product_id: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_product}`,
                type: "error",
              },
            ],
            first_area: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_first_area}`,
                type: "error",
              },
            ],
            second_area: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_second_area}`,
                type: "error",
              },
            ],
            firId: [
              {
                required: true,
                message: `${lang.tem_select}${lang.temp_first_area}`,
                type: "error",
              },
            ],
            area_id: [
              {
                required: true,
                message: `${lang.tem_select}${lang.temp_belong_area}`,
                type: "error",
              },
            ],
            region: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_region}`,
                type: "error",
              },
            ],
            ip_num: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_ip_num}`,
                type: "error",
              },
            ],
            bandwidth: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_bw}`,
                type: "error",
              },
            ],
            defense: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_defense}`,
                type: "error",
              },
            ],
            system_disk: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_system_disk}`,
                type: "error",
              },
            ],
            duration: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_duration}`,
                type: "error",
              },
            ],
            tag: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_tag}`,
                type: "error",
              },
            ],
            cpu: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_cpu}`,
                type: "error",
              },
            ],
            memory: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_memory}`,
                type: "error",
              },
            ],
            disk: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_disk}`,
                type: "error",
              },
            ],
            original_price: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_original_price}`,
                type: "error",
              },
            ],
            bandwidth_price: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_bw_price}`,
                type: "error",
              },
            ],
            selling_price: [
              {
                required: true,
                message: `${lang.tem_input}${lang.temp_sell_price}`,
                type: "error",
              },
            ],
            url: [
              {
                required: true,
                message: `${lang.tem_input}${lang.tem_jump_link}`,
                type: "error",
              },
              {
                pattern:
                  /(https?|ftp|file):\/\/[-A-Za-z0-9+&@#/%?=~_|!:,.;]+[-A-Za-z0-9+&@#/%=~_|]/,
                message: lang.tem_tip1,
                type: "warning",
              },
            ],
          },
          baseVisible: false,
          baseList: [],
          icp_product_id: "",
          deleteType: "",
          // 更多优惠 | 商标延申服务
          moreName: "",
          moreList: [],
          moreColumns: [],
          moreLoadng: false,
          unitSelect: [
            { value: "month", label: lang.temp_month },
            { value: "year", label: lang.temp_year },
          ],
          bwUnitSelect: [
            { value: "month", label: `M/${lang.temp_month}` },
            { value: "year", label: `M/${lang.temp_year}` },
          ],
          isDelMore: false,
          // 区域
          areaName: "",
          areaForm: {
            first_area: "",
            second_area: "",
          },
          areaVisble: false,
          areaList: [],
          hostAreaSelect: [], // cloud dicm
          areaLoading: false,
          areaColumns: [
            { colKey: "id", title: "ID", ellipsis: true },
            {
              colKey: "first_area",
              title: lang.temp_first_area,
              ellipsis: true,
            },
            {
              colKey: "second_area",
              title: lang.temp_second_area,
              ellipsis: true,
            },
            { colKey: "op", title: lang.tem_opt, width: "120" },
          ],
          isDelArea: false,
          // 服务器托管
          serverList: [],
          serverLoading: false,
          serverColumns: [
            { colKey: "first_area", title: lang.temp_area, ellipsis: true },
            { colKey: "title", title: lang.temp_title, ellipsis: true },
            { colKey: "product_id", title: lang.temp_product, ellipsis: true },
            { colKey: "op", title: lang.tem_opt, width: "120" },
          ],
          // 产品弹窗
          hostVisible: false,
          hostFormData: {
            firId: "",
            area_id: "",
            title: "",
            region: "",
            ip_num: "",
            bandwidth: "",
            defense: "",
            bandwidth_price: null,
            bandwidth_price_unit: "month",
            selling_price: null,
            selling_price_unit: "month",
            product_id: "",
            // cloud dcim 专属
            description: "",
            duration: "",
            tag: "",
            original_price: "",
            original_price_unit: "month",
            // cloud 专属
            system_disk: "",
            // dcim 专属
            cpu: "",
            memory: "",
            disk: "",
          },
          hostColumns: [
            {
              colKey: "first_area",
              title: lang.temp_first_area,
              ellipsis: true,
            },
            {
              colKey: "second_area",
              title: lang.temp_second_area,
              ellipsis: true,
            },
            { colKey: "title", title: lang.temp_product_name, ellipsis: true },
            {
              colKey: "description",
              title: lang.temp_product_descript,
              ellipsis: true,
            },
            {
              colKey: "product_id",
              title: lang.temp_product,
              width: "150",
              ellipsis: true,
            },
            { colKey: "op", title: lang.tem_opt, width: "120" },
          ],
          hostName: "",
          hostConfig: {
            cloud_server_more_offers: 0,
            physical_server_more_offers: 0,
          },
          calcHostArea: [],
          delDialog: false,
          upgradeDialog: false,
          themeInfo: {},
        };
      },
      created() {
        this.theme = getQuery().theme || "";
        this.getTabList();
        this.getThemeInfo();
        this.curValue = "cloud";
        this.init("cloud");
        this.moreColumns = JSON.parse(JSON.stringify(this.baseColumns));
        this.moreColumns.splice(2, 1, {
          colKey: "url",
          title: lang.tem_jump_link,
          ellipsis: true,
        });
      },
      filters: {
        filterMoney(money) {
          if (isNaN(money)) {
            return "0.00";
          } else {
            const temp = `${money}`.split(".");
            return parseInt(temp[0]).toLocaleString() + "." + (temp[1] || "00");
          }
        },
      },
      computed: {
        showMore() {
          if (
            this.curValue === "brand" ||
            (this.curValue === "cloud" &&
              this.hostConfig.cloud_server_more_offers) ||
            (this.curValue === "dcim" &&
              this.hostConfig.physical_server_more_offers)
          ) {
            return true;
          }
        },
        calcTit() {
          switch (this.curValue) {
            case "brand":
              return lang.temp_host_brand;
            case "ssl":
              return lang.temp_ssl_specs;
            case "sms":
              return lang.temp_host_sms;
            case "server":
              return lang.temp_host_server;
            case "cabinet":
              return lang.temp_host_cabinet;
            case "icp":
              return lang.temp_icp_tit;
          }
        },
        calcTit1() {
          switch (this.curValue) {
            case "cloud":
            case "dcim":
              return lang.temp_discount;
            case "brand":
              return lang.temp_brand_server;
          }
        },
        calcColumns() {
          switch (this.curValue) {
            case "cloud":
            case "dcim":
              return this.hostColumns;
            case "server":
              return this.serverColumns;
            default:
              return this.baseColumns;
          }
        },
        calcAreaColumns() {
          if (this.curValue === "server") {
            let temp = JSON.parse(JSON.stringify(this.areaColumns));
            temp.splice(2, 1);
            return temp;
          } else {
            return this.areaColumns;
          }
        },
        showEditArea() {
          const arr = ["cloud", "dcim", "server"];
          return arr.includes(this.curValue);
        },
      },
      methods: {
        sureUpgrade() {
          this.submitLoading = true;
          upgradeTheme({ theme: this.theme })
            .then((res) => {
              this.submitLoading = false;
              this.upgradeDialog = false;
              this.$message.success(res.data.msg);
              window.location.reload();
            })
            .catch((err) => {
              this.submitLoading = false;
              this.$message.error(err.data.msg);
            });
        },
        sureDel() {
          this.delLoading = true;
          uninstallTheme(this.theme)
            .then((res) => {
              this.$message.success(res.data.msg);
              this.delLoading = false;
              this.delDialog = false;
              location.href = this.backUrl;
            })
            .catch((err) => {
              this.delLoading = false;
              this.$message.error(err.data.msg);
            });
        },
        handleUpgrade() {
          this.upgradeDialog = true;
        },
        getThemeInfo() {
          getThemeLatestVersion(this.theme).then((res) => {
            this.themeInfo = res.data.data;
          });
        },
        handleDelete() {
          this.delDialog = true;
        },
        changeFir(val) {
          this.hostFormData.area_id = "";
          this.calcHostArea = this.hostAreaSelect[val]?.children;
        },
        changeTab(val) {
          const curPath = location.pathname
            .split("/")
            .find((item) => item.indexOf("htm") !== -1);
          if (val === curPath) {
            return;
          }
          location.href = val + `?theme=${this.theme}`;
        },
        init(prefix) {
          this.name = `${prefix}_server_product`; // 对应第一个table
          this.bannerName = `${prefix}_server_banner`; // banner
          this.areaName = `${prefix}_server_area`; // 区域
          this.moreName = `${prefix}_server_discount`; // 更多优惠 / 商标延申服务
          this.hostName = `${prefix}_server`; // 配置
          this.getBannerList();
          this.getBaseList();
          this.getAreaList();
          this.getMoreList();
          this.getHostConfig();
        },
        changeSlider(val) {
          if (this.curValue === val) {
            return;
          }
          this.baseList = [];
          this.curValue = val;
          switch (val) {
            case "cloud":
              this.init("cloud");
              break;
            case "dcim":
              this.init("physical");
              break;
            case "ssl":
              this.name = "ssl_certificate_product";
              this.getBaseList();
              break;
            case "sms":
              this.name = "sms_service_product";
              this.getBaseList();
              break;
            case "brand":
              this.name = "trademark_register_product";
              this.moreName = "trademark_service_product";
              this.getBaseList();
              this.getMoreList();
              break;
            case "server":
              this.name = "server_hosting_product";
              this.areaName = "server_hosting_area";
              this.getBaseList();
              this.getAreaList();
              break;
            case "cabinet":
              this.name = "cabinet_rental_product";
              this.getBaseList();
              break;
            case "icp":
              this.name = "icp_service_product";
              this.getBaseList();
              this.getIcpConfig();
              break;
          }
          this.$refs.baseDialog && this.$refs.baseDialog.reset();
          this.$refs.hostDialog && this.$refs.hostDialog.reset();
          this.$refs.comDialog && this.$refs.comDialog.reset();
        },
        async hostSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const params = JSON.parse(JSON.stringify(this.hostFormData));
              if (this.optType === "add") {
                delete params.id;
              }
              if (this.curValue === "server") {
                delete params.description;
                delete params.duration;
                delete params.system_disk;
                delete params.tag;
                delete params.original_price;
                delete params.original_price_unit;
                delete params.cpu;
                delete params.memory;
                delete params.disk;
              }
              if (this.curValue === "cloud" || this.curValue === "dcim") {
                delete params.region;
                delete params.defense;
                delete params.bandwidth_price;
                delete params.bandwidth_price_unit;
                delete params.first_area;
              }
              if (this.curValue === "cloud") {
                delete params.ip_num;
                delete params.disk;
              }
              if (this.curValue === "dcim") {
                delete params.system_disk;
              }
              delete params.firId;
              const res = await addAndUpdateController(
                this.name,
                this.optType,
                params
              );
              this.$message.success(res.data.msg);
              this.getBaseList();
              this.hostVisible = false;
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
        /* host config */
        async getHostConfig() {
          try {
            const res = await getControllerConfig(this.hostName);
            this.hostConfig = Object.assign(this.hostConfig, res.data.data);
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        async changeHostConfig(val) {
          try {
            let params = {};
            if (this.hostName === "cloud_server") {
              params.cloud_server_more_offers = val;
            }
            if (this.hostName === "physical_server") {
              params.physical_server_more_offers = val;
            }
            const res = await saveControllerConfig(this.hostName, params);
            this.$message.success(res.data.msg);
            this.getHostConfig();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        /* host config end */
        /* area */
        manageArea() {
          this.areaVisble = true;
          this.optType = "add";
        },
        getTabList() {
          getTemplateControllerTab({ theme: this.theme }).then((res) => {
            this.tabList = res.data.data.list;
          });
        },
        async getAreaList() {
          try {
            this.areaLoading = true;
            const res = await getControllerList(this.areaName);
            this.areaList = res.data.data.list;
            this.hostAreaSelect = res.data.data.area.map((item, ind) => {
              item.key = ind;
              return item;
            });
            this.areaLoading = false;
          } catch (error) {
            this.areaLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        editArea(row) {
          this.optType = "update";
          this.areaForm = JSON.parse(JSON.stringify(row));
        },
        delArea(row) {
          this.curId = row.id;
          this.delVisible = true;
          this.isDelArea = true;
        },
        async deleteArea() {
          try {
            this.delLoading = true;
            const res = await delController(this.areaName, {
              id: this.curId,
            });
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.delLoading = false;
            this.optType = "add";
            this.areaForm.first_area = "";
            this.areaForm.second_area = "";
            this.getAreaList();
          } catch (error) {
            this.delVisible = false;
            this.delLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        async submitArea({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const params = JSON.parse(JSON.stringify(this.areaForm));
              if (this.optType === "add") {
                delete params.id;
              }
              if (this.curValue === "server") {
                delete params.second_area;
              }
              const res = await addAndUpdateController(
                this.areaName,
                this.optType,
                params
              );
              this.$message.success(res.data.msg);
              this.getAreaList();
              if (this.curValue === "cloud" || this.curValue === "dcim") {
                this.getBaseList();
              }
              this.optType = "add";
              this.areaForm.first_area = "";
              this.areaForm.second_area = "";
              this.baseVisible = false;
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
        /* area end */
        /* more */
        async getMoreList() {
          try {
            this.moreLoadng = true;
            const res = await getControllerList(this.moreName);
            this.moreList = res.data.data.list;
            this.moreLoadng = false;
          } catch (error) {
            this.moreLoadng = false;
            this.$message.error(error.data.msg);
          }
        },
        editMore(row) {
          this.handleMoreName();
          this.optType = "update";
          this.optTitle = lang.tem_edit;
          this.baseVisible = true;
          this.baseFormData = JSON.parse(JSON.stringify(row));
        },
        handleMoreAdd() {
          this.optTitle = lang.tem_add;
          this.baseVisible = true;
          this.baseFormData.price_unit = "month";
          this.optType = "add";
          this.handleMoreName();
          this.$refs.baseDialog && this.$refs.baseDialog.reset();
        },
        /* more end */
        // 处理多个表格name
        handleBaseName() {
          switch (
            this.curValue // 存在多个表格： cloud dcim brand
          ) {
            case "cloud":
              this.name = "cloud_server_product";
              break;
            case "dcim":
              this.name = "physical_server_product";
              break;
            case "brand":
              this.name = "trademark_register_product";
              break;
          }
        },
        handleMoreName() {
          switch (this.curValue) {
            case "cloud":
              this.name = "cloud_server_discount";
              break;
            case "dcim":
              this.name = "physical_server_discount";
              break;
            case "brand":
              this.name = "trademark_service_product";
              break;
          }
        },
        /* base */
        handleBaseAdd() {
          this.optTitle = lang.tem_add;
          this.handleBaseName();
          const arr = ["cloud", "dcim", "server"];
          if (arr.includes(this.curValue)) {
            this.hostVisible = true;
            this.optTitle = `${lang.tem_add}${lang.temp_host}`;
            this.hostFormData.bandwidth_price_unit = "month";
            this.hostFormData.selling_price_unit = "month";
            this.hostFormData.original_price_unit = "month";
          } else {
            this.baseVisible = true;
            this.baseFormData.price_unit = "month";
          }
          this.hostFormData.firId = "";
          this.optType = "add";
          this.$refs.baseDialog && this.$refs.baseDialog.reset();
          this.$refs.hostDialog && this.$refs.hostDialog.reset();
        },
        editBase(row) {
          this.handleBaseName();
          this.optType = "update";
          const arr = ["cloud", "dcim", "server"];
          this.optTitle = lang.tem_edit;
          if (arr.includes(this.curValue)) {
            this.hostVisible = true;
            this.optTitle = `${lang.tem_edit}${lang.temp_host}`;
            this.hostFormData = JSON.parse(JSON.stringify(row));
            this.hostFormData.firId = this.hostAreaSelect.findIndex(
              (item) => item.name === row.first_area
            );
            this.calcHostArea =
              this.hostAreaSelect[this.hostFormData.firId]?.children;
          } else {
            this.baseVisible = true;
            this.baseFormData = JSON.parse(JSON.stringify(row));
          }
        },
        changePrice(val) {
          if (val < 0) {
            this.baseFormData.price = 0;
          }
        },
        async getBaseList() {
          try {
            this.loading = true;
            const res = await getControllerList(this.name);
            this.baseList = res.data.data.list;
            this.loading = false;
          } catch (error) {
            this.loading = false;
            this.$message.error(error.data.msg);
          }
        },
        async baseSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const params = JSON.parse(JSON.stringify(this.baseFormData));
              if (this.optType === "add") {
                delete params.id;
              }
              if (this.curValue !== "ssl" && this.curValue !== "sms") {
                delete params.price_unit;
              }
              if (this.curValue !== "cloud" && this.curValue !== "dcim") {
                delete params.url;
              } else {
                delete params.price;
                delete params.product_id;
              }
              const res = await addAndUpdateController(
                this.name,
                this.optType,
                params
              );
              this.$message.success(res.data.msg);
              const moreArr = [
                "cloud_server_discount",
                "physical_server_discount",
                "trademark_service_product",
              ];
              if (moreArr.includes(this.name)) {
                this.getMoreList();
              } else {
                this.getBaseList(this.name);
              }
              this.baseVisible = false;
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
        /* base end */
        /* icp */
        async getIcpConfig() {
          try {
            const res = await getControllerConfig("icp");
            this.icp_product_id = res.data.data.icp_product_id;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        async deleteBase() {
          try {
            this.delLoading = true;
            const res = await delController(this.name, {
              id: this.curId,
            });
            this.$message.success(res.data.msg);
            // 删除更多
            if (this.isDelMore) {
              this.isDelMore = false;
              this.getMoreList(this.name);
            } else {
              this.getBaseList(this.name);
            }
            this.delVisible = false;
            this.delLoading = false;
          } catch (error) {
            this.delVisible = false;
            this.delLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        async saveIcp() {
          try {
            if (!this.icp_product_id) {
              return this.$message.error(`${lang.tem_input}ID`);
            }
            this.submitLoading = true;
            const res = await saveControllerConfig("icp", {
              icp_product_id: this.icp_product_id,
            });
            this.submitLoading = false;
            this.$message.success(res.data.msg);
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        /* icp end */
        comDel(type, row) {
          this.deleteType = type;
          this.curId = row.id;
          this.delVisible = true;
          this.isDelMore = false;
          this.handleBaseName();
        },
        comMoreDel(type, row) {
          this.deleteType = type;
          this.curId = row.id;
          this.delVisible = true;
          this.isDelMore = true;
          this.handleMoreName();
        },
        sureDelete() {
          if (this.isDelArea) {
            this.isDelArea = false;
            this.deleteArea();
          } else if (this.isDelBanner) {
            this.isDelBanner = false;
            this.deleteBanner();
          } else {
            this.deleteBase();
          }
        },
        /* banner */
        addBanner() {
          this.tempBanner = this.tempBanner
            .filter((item) => item.id)
            .map((item) => {
              item.edit = false;
              return item;
            });
          this.tempBanner.push({
            url: "",
            img: "",
            start_time: "",
            end_time: "",
            show: 0,
            notes: "",
            edit: true,
            timeRange: [],
          });
          this.editItem = {
            id: "",
            url: "",
            img: [],
            show: 0,
            notes: "",
            edit: false,
            timeRange: [],
          };
          this.optType = "add";
        },
        handlerEdit(row) {
          this.tempBanner = this.tempBanner
            .filter((item) => item.id)
            .map((item) => {
              item.edit = false;
              return item;
            });
          row.edit = true;
          this.optType = "update";
          this.editItem = JSON.parse(JSON.stringify(row));
          this.editItem.img = [{ url: row.img }];
          this.editItem.timeRange = [
            moment.unix(row.start_time).format("YYYY/MM/DD"),
            moment.unix(row.end_time).format("YYYY/MM/DD"),
          ];
        },
        delBanner(row) {
          this.delVisible = true;
          this.isDelBanner = true;
          this.curId = row.id;
        },
        async changeShow(e, row) {
          try {
            if (row.edit) {
              return false;
            }
            const res = await changeBaseStatus(this.bannerName, {
              id: row.id,
              show: e,
            });
            this.$message.success(res.data.msg);
            this.getBannerList();
          } catch (error) {
            this.$message.error(error.data.msg);
            this.getBannerList();
          }
        },
        async deleteBanner() {
          try {
            this.delLoading = true;
            const res = await delController(this.bannerName, {
              id: this.curId,
            });
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.getBannerList();
            this.delLoading = false;
          } catch (error) {
            this.delLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        cancelItem(row, index) {
          if (!row.id) {
            this.tempBanner.splice(index, 1);
          }
          row.edit = false;
        },
        changeFile(file) {
          this.editItem.img = [
            {
              url: file.response.data.image_url,
            },
          ];
        },
        async saveBannerItem(row, index) {
          try {
            const temp = JSON.parse(JSON.stringify(this.editItem));
            if (temp.img.length === 0) {
              return this.$message.error(`${lang.upload}${lang.picture}`);
            }
            if (temp.timeRange.length === 0) {
              return this.$message.error(`${lang.select}${lang.time}`);
            }
            if (!temp.url) {
              return this.$message.error(`${lang.input}${lang.feed_link}`);
            }
            const reg =
              /^(((ht|f)tps?):\/\/)?([^!@#$%^&*?.\s-]([^!@#$%^&*?.\s]{0,63}[^!@#$%^&*?.\s])?\.)+[a-z]{2,6}\/?/;
            if (temp.url && !reg.test(temp.url)) {
              return this.$message.error(`${lang.input}${lang.feed_tip}`);
            }
            temp.start_time = parseInt(
              new Date(temp.timeRange[0].replaceAll("-", "/")).getTime() / 1000
            );
            temp.end_time = parseInt(
              new Date(temp.timeRange[1].replaceAll("-", "/")).getTime() / 1000
            );
            if (temp.lastModified) {
              temp.img = temp.img[0]?.response.data.image_url;
            } else {
              temp.img = temp.img[0].url;
            }
            temp.edit = false;
            if (this.optType === "add") {
              delete temp.id;
            }
            temp.show = row.show;
            const res = await addAndUpdateController(
              this.bannerName,
              this.optType,
              temp
            );
            this.$message.success(res.data.msg);
            this.getBannerList();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        async onDragSort(params) {
          try {
            this.tempBanner = params.currentData;
            const arr = this.tempBanner.reduce((all, cur) => {
              all.push(cur.id);
              return all;
            }, []);
            const res = await changeBaseOrder(this.bannerName, {
              id: arr,
              theme: this.theme,
            });
            this.$message.success(res.data.msg);
            this.getBannerList();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        formatImgResponse(res) {
          if (res.status === 200) {
            return { url: res.data.image_url };
          } else {
            return this.$message.error(res.msg);
          }
        },
        // 获取banner数据
        async getBannerList() {
          try {
            this.loading = true;
            const res = await getControllerList(this.bannerName);
            this.tempBanner = res.data.data.list.map((item) => {
              item.edit = false;
              return item;
            });
            this.loading = false;
          } catch (error) {
            this.loading = false;
            this.$message.error(error.data.msg);
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
