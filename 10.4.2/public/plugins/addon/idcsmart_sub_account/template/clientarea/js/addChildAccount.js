(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    new Vue({
      components: {
        asideMenu,
        topMenu,
        pagination,
      },
      async created() {
        let obj = this.getUrlParams();
        if (Object.values(obj).length > 0) {
          this.accountId = obj.id;
          if (obj.id) {
            this.isDetali = true;
          }
          this.accountType = obj.type;
          this.getDetail();
        }
        this.getCountry();
        this.getCommonData();
        this.getProjectList();
        this.getPermissionsList();
        this.getAllProduct();
        this.getAllModel();
      },
      mounted() {},
      updated() {
        // // 关闭loading
        // document.getElementById('mainLoading').style.display = 'none';
        // document.getElementsByClassName('template')[0].style.display = 'block'
      },
      destroyed() {},
      data() {
        return {
          countryList: [],
          commonData: {},
          accountId: "",
          accountType: "",
          isDetali: false, // 判断是否展示详情，详情就禁用所有不允许输入
          projectList: [], // 项目列表
          addAccountForm: {
            username: "",
            email: "",
            phone_code: "",
            phone: "",
            password: "",
            project_id: [], // 项目id
            visible_product: "module", //可见产品类型
            notice: [], // 通知权限
            host_id: [], // 具体产品id
            module: [],
          },
          productList: [], // 产品列表 --- 模块
          host_idList: [], // 产品列表 --- 具体产品

          permissionsLeftList: [], // 左边的权限
          permissionsRightList: [], // 左边的权限
          defaultProps: {
            children: "child",
            label: "title",
            disabled: this.disabledFn,
          },
          leftPermissionsID: [], // 左右两边选中的权限id
          rightPermissionsID: [], // 右边选中的权限id
          validateEmail: (rule, value, callback) => {
            const mailReg =
              /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/;
            if (!value) {
              return callback(new Error(lang.invoice_text125));
            }
            setTimeout(() => {
              if (mailReg.test(value.trim())) {
                callback();
              } else {
                callback(new Error(lang.invoice_text126));
              }
            }, 100);
          },
          rules: {
            username: [
              {
                required: true,
                message: lang.withdraw_placeholder4,
                trigger: "blur",
              },
              {
                min: 3,
                message: lang.withdraw_tips4,
                trigger: "blur",
              },
            ],
            password: [
              {
                required: true,
                message: lang.subaccount_text15,
                trigger: "blur",
              },
              {
                min: 6,
                message: lang.subaccount_text42,
                trigger: "blur",
              },
            ],
          },
          leftRepitTree: [], // 左边去重的树形数据
          rightRepitTree: [], // 右边去重的树形数据
          aa: [2, 3],
          isCheck: false,
          isCheckRight: false,
          defaultID: [],
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
        getUrlParams() {
          var urlObj = {};
          if (!window.location.search) {
            return false;
          }
          var urlParams = window.location.search.substring(1);
          var urlArr = urlParams.split("&");
          for (var i = 0; i < urlArr.length; i++) {
            var urlArrItem = urlArr[i].split("=");
            urlObj[urlArrItem[0]] = urlArrItem[1];
          } // 判断是否有参数
          //   if (arguments.length >= 1) {
          //     return urlObj[params];
          //   }
          return urlObj;
        },
        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          document.title =
            this.commonData.website_name + "-" + lang.subaccount_text43;
        },
        getCountry() {
          queryCountryAPI().then((res) => {
            if (res.data.status === 200) {
              this.countryList = res.data.data.list;
              let china = this.countryList.find(
                (item) => item.name_zh == "中国"
              );
              this.addAccountForm.phone_code = china.phone_code;
            }
          });
        },
        goBack() {
          location.href = "childAccount.htm";
        },
        // 获取项目列表
        async getProjectList() {
          const res = await queryProjectListAPI();
          if (res.status == 200) {
            const { data } = res.data;
            this.projectList = data.list;
          }
        },
        // 获取权限树形数据
        async getPermissionsList() {
          const res = await queryPermissionsListAPI();
          if (res.status === 200) {
            const { data } = res.data;
            this.permissionsLeftList = data.list.filter(
              (item) => item.title == "基础权限"
            );
            this.arrFun(this.permissionsLeftList);
            let activeObj = this.leftRepitTree.find(
              (item) => item.title == "概要"
            );
            // 当前节点是概要的时候 取到它当前的节点和父节点，存起来，无论前台选美选中概要这个节点，
            // 都要传递到后台， 因为解决概要是必须选中这个逻辑
            this.funActive(activeObj);

            this.$nextTick(() => {
              const nodes = [];
              this.defaultID.forEach((item) => {
                const node = this.$refs.leftTree.getNode(item);
                if (node.isLeaf) {
                  //关键，过滤掉不是叶子节点的
                  nodes.push(item);
                }
              });
              this.$refs.leftTree.setCheckedKeys(nodes, true);
            });

            this.permissionsRightList = data.list.filter(
              (item) => item.title == "产品权限"
            );
          }
        },
        disabledFn(data, node) {
          if (data.title == "概要") {
            node.checked = true;
            return true;
          } else {
            return false;
          }
        },
        // 递归去取 概要这个节点和它的父节点 id 存起来
        funActive(activeObj) {
          this.defaultID.push(activeObj.id);
          if (activeObj.parent_id) {
            let a = this.leftRepitTree.find(
              (item) => item.id == activeObj.parent_id
            );
            if (a) {
              this.funActive(a);
            }
          }
        },
        // 获取所有产品
        async getAllProduct() {
          const res = await queryAllProductAPI();
          if (res.status == 200) {
            const { data } = res.data;
            this.host_idList = data.list;
          }
        },
        // 获取所有模块
        async getAllModel() {
          const res = await queryAllModelAPI();
          if (res.status == 200) {
            const { data } = res.data;
            this.productList = data.list;
          }
        },
        // 获取账户详情
        async getDetail() {
          const res = await queryChildAccountDteailAPI({ id: this.accountId });
          if (res.status === 200) {
            const { data } = res.data;
            let obj = data.account;
            for (let key in this.addAccountForm) {
              if (key != "phone_code") {
                this.addAccountForm[key] = obj[key];
              }
            }
            if (obj.phone_code) {
              this.addAccountForm.phone_code = obj.phone_code;
            }
            setTimeout(() => {
              this.$nextTick(() => {
                // 回显左侧树结构
                const leftNodes = [];
                obj.auth.forEach((item) => {
                  const node = this.$refs.leftTree.getNode(item);
                  if (node && node.isLeaf) {
                    //关键，过滤掉不是叶子节点的
                    leftNodes.push(item);
                  }
                });
                this.$refs.leftTree.setCheckedKeys(leftNodes, true);
                // 回显右侧树结构
                const rightNodes = [];
                obj.auth.forEach((item) => {
                  const node = this.$refs.rightTree.getNode(item);
                  if (node && node.isLeaf) {
                    //关键，过滤掉不是叶子节点的
                    rightNodes.push(item);
                  }
                });
                this.$refs.rightTree.setCheckedKeys(rightNodes, true);
                // 左右赋值 避免两端不选返回为空 获取当前选中的节点和半选中的节点，保存起来，如果没有修改就直接返回改节点
                this.leftPermissionsID = this.$refs.leftTree
                  .getCheckedKeys()
                  .concat(this.$refs.leftTree.getHalfCheckedKeys());
                this.rightPermissionsID = this.$refs.rightTree
                  .getCheckedKeys()
                  .concat(this.$refs.rightTree.getHalfCheckedKeys());
              });
            }, 1000);
          }
        },
        arrFun(n) {
          for (var i = 0; i < n.length; i++) {
            //用typeof判断是否是数组
            if (n[i].child && typeof n[i].child == "object") {
              let obj = JSON.parse(JSON.stringify(n[i]));
              delete obj.child;
              this.leftRepitTree.push(obj);
              this.arrFun(n[i].child);
            } else {
              this.leftRepitTree.push(n[i]);
            }
          }
        },
        deepFun(obj, arr) {
          if (obj.id) {
            this.leftPermissionsID.push(obj.id);
            if (obj.parent_id) {
              let parent = arr.find((n) => n.id == obj.parent_id);
              this.deepFun(parent, arr);
            }
          }
        },
        checkLeftFun(a, { checkedKeys }) {
          if (checkedKeys.length < 1) {
            this.leftPermissionsID = checkedKeys;
          } else {
            // 由于下面 去重的数组和选中的id 都是push 进去的所以每次重新选择后都需要清空一次
            this.leftPermissionsID = [];
            this.leftRepitTree = [];
            this.arrFun(this.permissionsLeftList);
            checkedKeys.map((item) => {
              let isHave = this.leftRepitTree.find((obj) => obj.id == item);
              this.deepFun(isHave, this.leftRepitTree);
            });
            this.leftPermissionsID = [...new Set(this.leftPermissionsID)];
          }
        },

        // 右边

        checkRightFun(a, { checkedKeys }) {
          this.rightRepitTree = [];
          if (checkedKeys.length < 1) {
            this.rightPermissionsID = checkedKeys;
          } else {
            this.rightPermissionsID = [];
            this.arrFun2(this.permissionsRightList);
            checkedKeys.map((item) => {
              let isHave = this.rightRepitTree.find((obj) => obj.id == item);
              this.deepFun2(isHave, this.rightRepitTree);
            });
            this.rightPermissionsID = [...new Set(this.rightPermissionsID)];
          }
        },
        arrFun2(n) {
          for (var i = 0; i < n.length; i++) {
            //用typeof判断是否是数组
            if (n[i].child && typeof n[i].child == "object") {
              let obj = JSON.parse(JSON.stringify(n[i]));
              delete obj.child;
              this.rightRepitTree.push(obj);
              this.arrFun2(n[i].child);
            } else {
              this.rightRepitTree.push(n[i]);
            }
          }
        },
        deepFun2(obj, arr) {
          if (obj.id) {
            this.rightPermissionsID.push(obj.id);
            if (obj.parent_id) {
              let parent = arr.find((n) => n.id == obj.parent_id);
              this.deepFun2(parent, arr);
            }
          }
        },
        saveBtn() {
          this.$refs.ruleForm.validate(async (valid) => {
            if (valid) {
              const auth = new Set([
                ...this.rightPermissionsID,
                ...this.leftPermissionsID,
                ...this.defaultID,
              ]);
              let params = {
                ...this.addAccountForm,
                auth: [...auth],
              };

              try {
                if (this.accountId && this.accountType == "edit") {
                  await editChildAccountDteailAPI({
                    ...params,
                    id: this.accountId,
                  });
                  this.$message.success(lang.subaccount_text44);
                } else {
                  await createChilAccountAPI(params);
                  this.$message.success(lang.subaccount_text45);
                }
                this.goBack();
              } catch (error) {
                this.$message.warning(error.data.msg);
              }
            } else {
              return false;
            }
          });
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);
