(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("childAccount-box")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      data() {
        const smsNameValidator = (val) => {
          if (!this.formData.email && !this.formData.phone) {
            return {
              result: false,
              message: lang.sub_account_text11,
              type: "error",
            };
          }
          return { result: true };
        };
        return {
          submitLoading: false,
          id: "",
          pId: "",
          country: [],
          formData: {
            visible_product: "host",
            username: "",
            email: "",
            phone: "",
            phone_code: "",
            notice: [],
          },
          module: [], // 模块
          host_id: [], // 产品
          //   notice: [], // 选中的通知权限
          leftTreeActiveList: [1, 4, 7], // 左边树形数据选中的id
          //  通知权限列表
          noticeOptions: [
            { label: lang.sub_account_text12, value: "product" },
            { label: lang.sub_account_text13, value: "marketing" },
            { label: lang.sub_account_text14, value: "ticket" },
            { label: lang.sub_account_text15, value: "cost" },
            { label: lang.sub_account_text16, value: "recommend" },
            { label: lang.sub_account_text17, value: "system" },
          ],
          leftAuth: [], // 左右回显
          rightAuth: [],
          leftTreeData: [],
          rightTreeData: [],
          moduleList: [], // 模块列表
          productList: [], // 产品列表
          valueMode: "onlyLeaf",
          options: [
            { label: lang.sub_account_text18, value: "module" },
            { label: lang.sub_account_text19, value: "host" },
          ],
          rules: {
            username: [{ required: true, message: lang.sub_account_text20 }],
            phone: [{ required: true, message: lang.sub_account_text21 }],
            phone_code: [{ required: true, message: lang.sub_account_text22 }],
            phone: [{ validator: smsNameValidator, trigger: "blur" }],
            email: [{ validator: smsNameValidator, trigger: "blur" }],
          },
          leftRiptData: [], // 左右两边去重的树形数据
          RightRiptData: [],
          leftActiveId: [], // 左右两边选中的id
          rightActiveId: [],
          defaultID: [], // 默认的id
        };
      },
      async created() {
        const query = location.href.split("?")[1].split("&");
        this.id = Number(this.getQuery(query[0]));
        this.pId = Number(this.getQuery(query[1]));
        await this.getTreeList();
        this.getDetail();
        this.getModuleList();
        this.getProductList();
        this.getCountry();
      },
      components: {
        comConfig,
      },
      methods: {
        // 获取国家列表
        async getCountry() {

          try {
            const res = await getCountry();
            this.country = res.data.data.list;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        back() {
          history.go(-1);
        },
        getQuery(val) {
          return val.split("=")[1];
        },
        async getDetail() {
          const res = await getChildAccountDetailAPI(this.id);
          if (res.status == 200) {
            const { data } = res.data;
            let obj = data.account;
            for (const key in this.formData) {
              this.formData[key] = obj[key];
            }
            this.module = obj.module;
            this.host_id = obj.host_id;
            obj.auth.forEach((i) => {
              let node = this.$refs.leftTree.getItem(i);
              // node.isLeaf：判断当前节点是否为子节点
              if (node && node.isLeaf()) {
                //1 如果是子节点，就把状态设置成选中
                this.leftAuth.push(node.value);
                //2 拿到当前设置选中的节点的id 和它的父节点的id  保留起来，如果不修改权限没有触发修改的方法 就把保留的传递过去
                this.leftActiveId.push(node.value);
                this.f1(node, this.leftActiveId);
              } else {
              }
            });

            this.leftActiveId = [...new Set(this.leftActiveId)];
            obj.auth.forEach((i) => {
              let node = this.$refs.rightTree.getItem(i);
              // node.isLeaf：判断当前节点是否为子节点
              if (node && node.isLeaf()) {
                //如果是子节点，就把状态设置成选中
                this.rightAuth.push(node.value);

                this.rightActiveId.push(node.value);
                this.f1(node, this.rightActiveId);
              } else {
              }
            });
          }
        },
        f1(node, arr) {
          let parentNode = node.getParent();
          if (parentNode && parentNode.value) {
            arr.push(parentNode.value);
            this.f1(parentNode, arr);
          }
        },
        // 获取权限树
        async getTreeList() {
          const res = await queryTreeAPI();
          if (res.status == 200) {
            const { data } = res.data;
            let treeArr = data.list;
            this.leftTreeData = treeArr.filter(
              (item) => item.title === lang.sub_account_text23
            );
            this.leftTreeData.map((item) => {
              item.child = item.child.map((obj) => {
                if (obj.child) {
                  obj.child = obj.child.map((K) => {
                    if (K.title == lang.sub_account_text24) {
                      K.disabled = true;
                    }
                    return K;
                  });
                }
                return obj;
              });
              return item;
            });
            this.arrFun(this.leftTreeData);
            let activeObj = this.leftRiptData.find(
              (item) => item.title == lang.sub_account_text24
            );
            // 当为概要的时候 把当前他的id 保留起来，传递到后台，解决不允许修改的逻辑
            // 无论选没选中页面的概要  这个相当于是一个必传的id 保留起来 等到保存传递过去
            this.funActive(activeObj);
            this.rightTreeData = treeArr.filter(
              (item) => item.title === lang.sub_account_text26
            );
          }
        },
        funActive(activeObj) {
          this.defaultID.push(activeObj.id);
          if (activeObj.parent_id) {
            let a = this.leftRiptData.find(
              (item) => item.id == activeObj.parent_id
            );
            if (a) {
              this.funActive(a);
            }
          }
        },
        // 获取所有模块
        async getModuleList() {
          const res = await queryModelAPI();
          if (res.status === 200) {
            this.moduleList = res.data.data.list;
          }
        },
        // 获取所有产品
        async getProductList() {
          const res = await queryProductListAPI(this.pId);
          if (res.status === 200) {
            this.productList = res.data.data.list;

          }
        },
        async saveBtn({ validateResult, firstError }) {
          if (validateResult === true) {
            authArr = [
              ...new Set([
                ...this.leftActiveId,
                // 概要的id节点，是不允许修改的 无论怎样倒要传递过去
                ...this.defaultID,
                ...this.rightActiveId,
              ]),
            ];
            let params = {
              ...this.formData,
              module: this.module,
              auth: authArr,
              host_id: this.host_id,
            };
            try {
              this.submitLoading = true
              const res = await editProductAPI({ id: this.id, ...params });
              if (res.status == 200) {
                this.$message.success(lang.sub_account_text25);
                this.submitLoading = false
                history.back()
              }
            } catch (error) {
              this.submitLoading = false
              this.$message.warning(error.data.msg);
            }
          } else {
            this.$message.warning(firstError);
          }
        },
        arrFun(n) {
          for (var i = 0; i < n.length; i++) {
            //用typeof判断是否是数组
            if (n[i].child && typeof n[i].child == "object") {
              let obj = JSON.parse(JSON.stringify(n[i]));
              delete obj.child;
              this.leftRiptData.push(obj);
              this.arrFun(n[i].child);
            } else {
              this.leftRiptData.push(n[i]);
            }
          }
        },
        deepFun(obj, arr) {
          if (obj.id) {
            this.leftActiveId.push(obj.id);
            if (obj.parent_id) {
              let parent = arr.find((n) => n.id == obj.parent_id);
              this.deepFun(parent, arr);
            }
          }
        },
        onChange(checked, context) {
          let activeID = checked;
          this.leftActiveId = [];
          this.leftRiptData = [];

          this.arrFun(this.leftTreeData);

          activeID.map((item) => {
            let isHave = this.leftRiptData.find((obj) => obj.id == item);
            this.deepFun(isHave, this.leftRiptData);
          });
          this.leftActiveId = [...new Set(this.leftActiveId)];
        },

        onChangeRight(checked, context) {
          let activeID = checked;
          this.rightActiveId = [];
          this.RightRiptData = [];

          this.arrFun2(this.rightTreeData);
          activeID.map((item) => {
            let isHave = this.RightRiptData.find((obj) => obj.id == item);
            this.deepFun2(isHave, this.RightRiptData);
          });
          this.rightActiveId = [...new Set(this.rightActiveId)];
        },

        arrFun2(n) {
          for (var i = 0; i < n.length; i++) {
            //用typeof判断是否是数组
            if (n[i].child && typeof n[i].child == "object") {
              let obj = JSON.parse(JSON.stringify(n[i]));
              delete obj.child;
              this.RightRiptData.push(obj);
              this.arrFun2(n[i].child);
            } else {
              this.RightRiptData.push(n[i]);
            }
          }
        },

        deepFun2(obj, arr) {
          if (obj.id) {
            this.rightActiveId.push(obj.id);
            if (obj.parent_id) {
              let parent = arr.find((n) => n.id == obj.parent_id);
              this.deepFun2(parent, arr);
            }
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
