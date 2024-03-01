(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("create-order")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
        comTreeSelect,
        comChooseUser
      },
      data() {
        return {
          userList: [],
          userParams: {
            keywords: "",
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          renewsParms: {
            type: "renew",
            client_id: "",
            id: "",
            customfield: {
              billing_cycle: "",
              custom_amount: 0,
            },
          },
          renewHostList: [],
          selectRenewIndex: 0,
          currency_prefix: JSON.parse(localStorage.getItem("common_set"))
            .currency_prefix,
          currency_code: JSON.parse(localStorage.getItem("common_set"))
            .currency_code,
          formData: {
            client_id: "", // 用户id
            type: "new", // new新订单 renew 续费订单 upgrade 升降级订单 artificial 人工订单
            host_id: "",
            host_name: "",
            product: {
              product_id: "",
              config_options: {},
              price: "",
              product_name: "",
            },
            // 人工订单
            amount: "",
            description: "",
            // 新订单
            newProductId: "",
            newProductName: "",
          },
          showProduct: [], // 右侧渲染
          productList: [], // 所有商品下拉选择
          curClientProduct: [], // 当前用户已有的商品
          totalPrice: 0,
          rules: {
            client_id: [
              {
                required: true,
                message: lang.input + lang.user,
                type: "error",
              },
            ],
            product_name: [
              {
                required: true,
                message: lang.select + lang.product,
                type: "error",
              },
            ],
            newProductId: [
              {
                required: true,
                message: lang.select + lang.product,
                type: "error",
              },
            ],
            type: [
              {
                required: true,
                message: lang.input + lang.order_type,
                type: "error",
              },
            ],
            description: [
              {
                required: true,
                message: lang.input + lang.description,
                type: "error",
              },
            ],
            amount: [
              {
                required: true,
                message: lang.input + lang.price,
                type: "error",
              },
              {
                pattern: /^\d+(\.\d{0,2})?$/,
                message: lang.verify5,
                type: "warning",
              },
              {
                validator: (val) => val > 0,
                message: lang.verify5,
                type: "warning",
              },
            ],
            lang_admin: [{ required: true }],
          },
          orderType: [
            { type: "new", name: lang.new },
            // { type: 'upgrade', name: lang.upgrade },
            { type: "artificial", name: lang.artificial },
          ],
          newOrderIframeUrl: "",
          curNum: 0,
          clientParams: {
            client_id: "",
          },
          isInitClientId: false,
          isShowTree: false,
          clientShopList: [],
          shopId: "",
          upgradeList: [], // 升降级产品列表
          userTotal: 0,
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
              "max-height": "362px",
            }),
          },
          treeProps: {
            keys: {
              label: "name",
              value: "key",
              children: "children",
            },
            valueMode: "parentFirst",
          },
          renewIds: "",
          visibleTreeObj: [{ visibleTree: false }],
          newBuyData: {
            price: null,
            isEditPrice: false,
            custom_renew_amount_switch: false,
            custom_renew_amount: 0,
            loading: false,
          },
          submitLoading: false
        };
      },
      mounted() {
        document.addEventListener("click", () => {
          this.visibleTreeObj.forEach((item) => {
            item.visibleTree = false;
          });
        });
        // 监听子页面想父页面的传参
        window.addEventListener("message", (event) =>
          this.handelIframeMsg(event)
        );
      },
      watch: {
        "formData.client_id"(val) {
          this.formData.host_id = "";
          this.shopId = "";
          this.formData.host_name = "";
          this.formData.product.product_id = "";
          this.formData.product.config_options = {};
          this.formData.product.price = "";
          this.getActiveShop(val);
        },
        // 检测vue版下插件数据的改变
        "formData.products": {
          deep: true,
          handler() {},
        },
      },
      created() {
        // 获取用户列表
        const temp = this.getQuery(location.search);
        if (temp.id) {
          this.getUserList(temp.id);
        } else {
          this.getUserList();
        }
        this.getPlugin();

        // 获取分组
        // 获取产品列表
       // this.getProductList();
        document.title = lang.create_order + "-" + localStorage.getItem("back_website_name");
      },
      methods: {
        handelIframeMsg(e) {
          const { type, params, price } = e.data;
          const { client_id } = this.formData;
          if (type === "iframeBuy") {
            if (this.newBuyData.isEditPrice && this.newBuyData.price === null) {
              this.$message.error(lang.order_text84);
              return;
            }
            this.newBuyData.loading = true;
            const subPrice = this.newBuyData.isEditPrice
              ? this.newBuyData.price
              : price * 1;
            const renewPrice = this.newBuyData.custom_renew_amount_switch
              ? this.newBuyData.custom_renew_amount
              : null;
            const renewSwitch = this.newBuyData.custom_renew_amount_switch
              ? 1
              : 0;
            const subObj = {
              ...params,
              custom_order_amount: subPrice,
              client_id,
              custom_renew_amount_switch: renewSwitch,
            };
            if (renewPrice !== null) {
              subObj.custom_renew_amount = renewPrice;
            }
            settleOrder(subObj)
              .then((res) => {
                this.newBuyData.loading = false;
                location.href = `order_details.htm?id=${res.data.data.order_id}`;
              })
              .catch((err) => {
                this.$message.error(err.data.msg);
                this.newBuyData.loading = false;
              });
          }
        },
        clearId() {
          this.formData.newProductId = "";
        },
        focusHandler(index) {
          this.visibleTreeObj.forEach((item) => {
            item.visibleTree = false;
          });
          this.curNum = index;
          const width = document.getElementById(`myPopup`).offsetWidth;
          this.$set(
            this.visibleTreeObj[index],
            "visibleTree",
            !this.visibleTreeObj[index].visibleTree
          );
          // 阻止冒泡关闭弹窗
          this.$nextTick(() => {
            document.getElementById(`myPopup`).onclick = () => {
              event.stopPropagation();
            };
            document.getElementsByClassName(
              "t-popup__content"
            )[0].style.width = `${width}px`;
          });
        },
        // 解析url
        getQuery(url) {
          const str = url.substr(url.indexOf("?") + 1);
          const arr = str.split("&");
          const res = {};
          for (let i = 0; i < arr.length; i++) {
            const item = arr[i].split("=");
            res[item[0]] = item[1];
          }
          return res;
        },
        async getPlugin() {
          try {
            const res = await getAddon();
            const addonArr = res.data.data.list.map((item) => item.name);
            const hasRenew = addonArr.includes("IdcsmartRenew");
            if (hasRenew) {
              this.orderType.push({ type: "renew", name: lang.renew });
            }
          } catch (error) {}
        },
        proChange(val) {
          console.log(val);
        },
        // 选择用户
        choosePro (val) {
          this.formData.newProductId = val;
          this.newOrderIframeUrl = `${window.location.origin}/goods_iframe.htm?id=${val}`;
        },
        // 选择用户
        async chooseUser(e) {
          window.client_id = e;
          this.formData.client_id = e;
          try {
            if (this.formData.type === "new") {
              const res = await loginByUserId(e);
              localStorage.setItem("jwt", res.data.data.jwt);
            }
          } catch (error) {
            console.log(error);
          }
        },
        // 改变类型
        changeType(type) {
          if (type === "new") {
          }
        },

        selectRenew(index) {
          this.selectRenewIndex = index;
          this.renewsParms.customfield.billing_cycle =
            this.renewHostList[index].billing_cycle;
          this.renewsParms.customfield.custom_amount =
            this.renewHostList[index].price * 1;
        },
        // 获取用户已开通的产品
        async getActiveShop(val) {
          try {
            const params = {
              client_id: val,
              status: "Active",
              page: 1,
              limit: 99999,
            };
            const res = await getShopList(params);
            this.clientShopList = res.data.data.list;
          } catch (error) {}
        },
        // 商品选择
        onClick(e) {
          if (!e.node.data.children) {
            const pId = e.node.data.id;
            const pName = e.node.data.name;
            this.formData.newProductName = pName;
            this.formData.newProductId = pId;
            this.$set(this.visibleTreeObj[this.curNum], "visibleTree", false);
            // 设置iframe的地址
            this.newOrderIframeUrl = `${window.location.origin}/goods_iframe.htm?id=${pId}`;
          }
        },
        // 设置产品下拉框名称
        getLabel(createElement, node) {
          const label = node.data.name;
          const { data } = node;
          data.label = label;
          return label;
        },
        // 续费选择
        chooseRenew() {
          this.renewHostList = [];
          this.selectRenewIndex = 0;
          // 拉取页面
          this.getRenewPage();
        },
        async getRenewPage() {
          try {
            const res = await getSingleRenew(this.renewIds);
            if (res.data.status === 200) {
              this.renewHostList = res.data.data.host;
              if (this.renewHostList.length > 0) {
                this.selectRenew(0);
              }
            } else {
              this.$message.error(res.data.msg);
            }
          } catch (error) {
            console.log(error);
            this.$message.error(error.data.msg);
          }
        },
        /*** 提交订单 ***/
        async onSubmit({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              const {
                client_id,
                type,
                amount,
                description,
                products,
                host_id,
                product,
              } = this.formData;
              switch (this.formData.type) {
                // 人工订单
                case "artificial":
                  const params = {
                    client_id,
                    type,
                    amount,
                    description,
                  };
                  this.submitLoading = true;
                  const res = await createOrder(params);
                  this.$message.success(res.data.msg);
                  setTimeout(() => {
                    this.submitLoading = false;
                    location.href = `order_details.htm?id=${res.data.data.id}`;
                  }, 300);
                  break;
                // 新订单
                case "new":
                  const postData = {
                    type: "iframeBuy",
                  };
                  this.$refs.Iframe.contentWindow.postMessage(postData, "*");
                  break;
                // 续费订单
                case "renew":
                  this.renewsParms.client_id = this.formData.client_id;
                  this.renewsParms.id = this.renewIds;
                  this.submitLoading = true;
                  createOrder(this.renewsParms)
                    .then((res) => {
                      this.$message.success(res.data.msg);
                      location.href = `order_details.htm?id=${res.data.data.id}`;
                    })
                    .catch((err) => {
                      this.$message.error(err.data.msg);
                    }).finally(() => {
                      this.submitLoading = false;
                    });
                  break;
                // 升降级
                case "upgrade":
                  break;
              }
            } catch (error) {
              this.$message.error(error.data.msg);
            }
          } else {
            this.$message.warning(firstError);
          }
        },
        // 获取用户列表
        async getUserList(id) {
          try {
            this.userList = [];
            this.userTotal = 0;
            this.searchLoading = true;
            if (id) {
              this.userParams.client_id = id;
              this.formData.client_id = id * 1;
              this.chooseUser(id);
            } else {
              this.userParams.client_id = "";
            }
            const res = await getClientList(this.userParams);
            this.userList = res.data.data.list;
            this.userTotal = res.data.data.count;
            this.searchLoading = false;
          } catch (error) {
            this.searchLoading = false;
          }
        },
        filterMethod(search, option) {
          return option;
        },
        // 远程搜素
        remoteMethod(key) {
          this.userParams.keywords = key;
          setTimeout(() => {
            this.getUserList();
          }, 300);
        },
        clearKey() {
          this.userParams.keywords = "";
          this.getUserList();
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
