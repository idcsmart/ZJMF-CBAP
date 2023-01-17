(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const finance = document.getElementById("finance");
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        asideMenu,
        topMenu,
        pagination,
        payDialog,
        withdrawDialog,
      },
      mounted() {
        window.addEventListener("scroll", this.computeScroll);
        // 关闭loading
        // document.getElementById('mainLoading').style.display = 'none';
        const addons_js_arr = JSON.parse(
          document.querySelector("#addons_js").getAttribute("addons_js")
        ); // 插件列表
        const arr = addons_js_arr.map((item) => {
          return item.name;
        });
        if (arr.includes("IdcsmartVoucher")) {
          // 开启了代金券
          this.isShowCash = true;
        }
      },
      updated() {
        // 关闭loading
        // document.getElementById('mainLoading').style.display = 'none';
        // document.getElementsByClassName('template')[0].style.display = 'block'
      },
      destroyed() {
        window.removeEventListener("scroll", this.computeScroll);
      },
      data() {
        return {
          isShowOrderController: false, // 是否展示订单记录
          isShowTransactionController: false, // 是否展示交易订单
          isShowBalance: false, // 是否展示余额订单
          // 交易记录 是否展示订单详情
          isDetail: false,
          // 后台返回的支付html
          payHtml: "",
          // 轮询相关
          timer: null,
          balanceTimer: null,
          isShowCash: false,
          time: 300000,
          // 支付方式
          gatewayList: [],
          // 错误提示信息
          errText: "",
          deleteObj: {},
          // 待退款金额
          unAmount: 0,
          commonData: {},
          // 货币前缀
          currency_prefix: "",
          // 货币后缀
          currency_code: "",
          // 用户余额
          balance: "",
          // 支付弹窗相关 开始
          // 支付弹窗控制
          isShowZf: false,
          isShowDeOrder: false,
          // 是否展示第三方支付
          isShowPay: true,
          zfData: {
            // 订单id
            orderId: 0,
            // 订单金额
            amount: 0,
            checked: false,
            // 支付方式
            gateway: gatewayList.length > 0 ? gatewayList[0].name : "",
          },

          // 支付弹窗相关结束
          // 是否显示提现弹窗
          isShowTx: false,
          // 是否显示充值弹窗
          isShowCz: false,
          // 充值弹窗表单数据
          czData: {
            amount: "",
            gateway: "",
          },
          czDataOld: {
            amount: "",
            gateway: "",
          },
          // 提现弹窗表单数据
          txData: {
            method_id: "",
            account: "",
            card_number: "",
            name: "",
            amount: "",
            source: "credit",
          },
          // 余额记录列表
          balanceType: {
            Recharge: { text: lang.finance_text8 },
            Applied: { text: lang.finance_text9 },
            Refund: { text: lang.finance_text10 },
            Withdraw: { text: lang.finance_text11 },
            Artificial: { text: "人工订单" }
          },
          loading1: false,
          loading2: false,
          loading3: false,
          loading4: false,
          dataList1: [],
          dataList2: [],
          dataList3: [],
          dataList4: [],
          timerId1: null,
          timerId2: null,
          timerId3: null,
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
          params3: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
            type: "",
            start_time: "",
            end_time: "",
          },
          date: [],
          activeIndex: "1",
          // 订单类型
          tipslist1: [
            {
              color: "#0058FF",
              name: lang.finance_text12,
              value: "new",
            },
            {
              color: "#3DD598",
              name: lang.finance_text13,
              value: "renew",
            },
            {
              color: "#F0142F ",
              name: lang.finance_text14,
              value: "upgrade",
            },
            {
              color: "#F99600 ",
              name: lang.finance_text15,
              value: "artificial",
            },
          ],
          // 订单详情 产品状态
          status: {
            Unpaid: "未付款",
            Pending: "开通中",
            Active: "使用中",
            Suspended: "暂停",
            Deleted: "删除",
            Failed: "开通失败",
          },
          // 提现规则
          ruleData: {
            method: [],
          },
          // 提现方式
          txway: {},
          payLoading: false,
          isShowimg: true,
          payLoading1: false,
          isShowimg1: true,
          isShowBackTop: false,
          scrollY: 0,
          isEnd: false,
          isShowMore: false,
          showCard: false, // 是否显示银行卡提现规则
          statusList: [
            {
              label: "待审核",
              id: 0,
            },
            {
              label: "待打款",
              id: 1,
            },
            {
              label: "审核驳回",
              id: 2,
            },
            {
              label: "已打款",
              id: 3,
            },
          ],
          isdot: false, // 当提现成功后 在提现记录加一个提示的效果

          // 代金卷
          /* 新增代金券 */
          vParams: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 0,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },
          availableParams: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 0,
          },
          voucherList: [],
          voucherAvailableList: [],
          dialogVisible: false,
          voucherLoading: false,
          diaLoading: false,
        };
      },
      mixins: [mixin],
      created() {
        // 订单记录列表
        // this.getorderList();
        this.getCommon();
        this.getAccount();
        this.getGateway();
        this.getUnAmount();

      },
      watch: {
        txData: {
          handler(newV, oldV) {
            let obj = this.ruleData.method.find(
              (item) => item.id == newV.method_id
            );
            if (obj && obj.name == "银行卡") {
              this.showCard = true;
            } else {
              this.showCard = false;
            }
          },
          deep: true,
        },
      },
      filters: {
        formateTime(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000);
          } else {
            return "--";
          }
        },
        formateTime1(time) {
          if (time && time !== 0) {
            return formateDate1(time * 1000);
          } else {
            return lang.voucher_effective;
          }
        },
      },
      methods: {
        getRule(arr) {
          let isShow1 = this.showFun(arr, "OrderController::index");
          let isShow2 = this.showFun(arr, "TransactionController::list");
          let isShow3 = this.showFun(arr, "AccountController::creditList");
          // 订单记录
          if (isShow1) {
            this.isShowOrderController = true;
          } else {
            this.activeIndex = "2";
            // this.handleClick();
          }
          // 交易记录
          if (isShow2) {
            this.isShowTransactionController = true;
          } else {
            this.activeIndex = "3";
            // this.handleClick();
          }
          // 余额记录
          if (isShow3) {
            this.isShowBalance = true;
          } else {
            this.activeIndex = "4";
          }
          this.handleClick();
        },
        showFun(arr, str) {
          if (typeof arr == "string") {
            return true;
          } else {
            let isShow = "";
            isShow = arr.find((item) => {
              let isHave = item.includes(str);
              if (isHave) {
                return isHave;
              }
            });
            return isShow;
          }
        },
        // 去往提现列表
        goWithdrawal() {
          location.href = "/withdrawal.html";
        },
        //获取订单列表
        getorderList() {
          this.loading1 = true;
          orderList(this.params1).then((res) => {
            if (res.data.status === 200) {
              this.params1.total = res.data.data.count;
              let list = res.data.data.list;
              list.map((item) => {
                let product_name = "";
                // 商品名称 含两个以上的 只取前两个拼接然后拼接上商品名称的个数
                if (item.product_names.length > 2) {
                  product_name =
                    item.product_names[0] +
                    "、" +
                    item.product_names[1] +
                    " " +
                    "等" +
                    item.product_names.length +
                    "个商品";
                } else {
                  item.product_names.map((n) => {
                    product_name += n + "、";
                  });
                  product_name = product_name.slice(0, -1);
                }
                item.product_name = product_name;

                // 判断有无子数据
                if (item.order_item_count > 1) {
                  // item.children = []
                  item.hasChildren = true;
                }
                item.data = [];
              });

              this.dataList1 = list;
            }
            this.loading1 = false;
          });
        },
        // 获取交易记录列表
        getTransactionList() {
          this.loading2 = true;
          transactionList(this.params2).then((res) => {
            if (res.data.status === 200) {
              let list = res.data.data.list;
              if (list) {
                list.map((item) => {
                  if (item.order_id == 0) {
                    item.order_id = "--";
                  }
                });
              }
              this.dataList2 = list;
              this.params2.total = res.data.data.count;
            }
            this.loading2 = false;
          });
        },
        // 获取余额记录列表
        getCreditList() {
          this.loading3 = true;
          if (this.date && this.date[0]) {
            this.params3.start_time = this.date[0] / 1000;
            this.params3.end_time = this.date[1] / 1000;
          } else {
            this.params3.start_time = "";
            this.params3.end_time = "";
          }
          creditList(this.params3).then((res) => {
            if (res.data.status === 200) {
              let list = res.data.data.list;
              // 过滤人工订单 不显示
              // list = list.filter((item) => {
              //   return item.type !== "Artificial";
              // });
              this.dataList3 = list;
              this.params3.total = res.data.data.count;
            }
            this.loading3 = false;
          });
        },
        // 获取订单详情
        getOrderDetailsList(id) {
          this.loading4 = true;
          orderDetails(id).then((res) => {
            if (res.data.status === 200) {
              let data = res.data.data.order;
              let item = data.items;
              let product_name = "";
              if (item) {
                item.map((n) => {
                  if (n.product_name) {
                    product_name += n.product_name + "、";
                  }
                });
              }
              data.product_name = product_name.slice(0, -1);
              let order = [];
              order.push(data);
              this.dataList4 = order;
            }
            this.loading4 = false;
          });
        },
        // 获取支付方式列表
        getGateway() {
          gatewayList().then((res) => {
            if (res.data.status === 200) {
              this.gatewayList = res.data.data.list;
            }
          });
        },
        // tab点击事件
        handleClick() {
          if (this.activeIndex == 1) {
            // 订单记录
            this.getorderList();
          }
          if (this.activeIndex == 2) {
            // 交易记录
            this.getTransactionList();
          }
          if (this.activeIndex == 3) {
            // 余额记录
            this.getCreditList(3);
          }
          if (this.activeIndex == 4) {
            this.getVoucher();
          }
        },
        sizeChange1(e) {
          this.params1.limit = e;
          this.getorderList();
        },
        sizeChange2(e) {
          this.params2.limit = e;
          this.getTransactionList();
        },
        sizeChange3(e) {
          this.params3.limit = e;
          this.getCreditList();
        },

        currentChange1(e) {
          this.params1.page = e;
          this.getorderList();
        },
        currentChange2(e) {
          this.params2.page = e;
          this.getTransactionList();
        },
        currentChange3(e) {
          this.params3.page = e;
          this.getCreditList();
        },
        // 订单记录 搜索框事件
        inputChange1() {
          this.params1.page = 1;
          this.getorderList();
        },
        // 交易记录 搜索框事件
        inputChange2() {
          this.params2.page = 1;
          this.getTransactionList();
        },
        // 余额记录 搜索框事件
        inputChange3() {
          this.params3.page = 1;
          this.getCreditList();
        },
        changeDate() { },
        // 获取通用配置
        getCommon() {
          this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
          document.title = this.commonData.website_name + "-财务信息";
          this.currency_prefix = this.commonData.currency_prefix;
          this.currency_code = this.commonData.currency_code;
        },
        // 获取账户详情
        getAccount() {
          account().then((res) => {
            if (res.data.status === 200) {
              this.balance = res.data.data.account.credit;
            }
          });
        },
        // 获取待审核金额
        getUnAmount() {
          this.addons_js_arr.includes('IdcsmartRefund') && unAmount().then((res) => {
            if (res.data.status === 200) {
              this.unAmount = res.data.data.amount;
            }
          });
        },
        // 显示充值 dialog
        showCz() {
          // 初始化弹窗数据
          this.czData = {
            amount: "",
            gateway: this.gatewayList[0] ? this.gatewayList[0].name : "",
          };
          this.czDataOld = {
            amount: "",
            gateway: "",
          };
          this.errText = "";
          this.isShowCz = true;
          this.payLoading1 = false;
          this.payHtml = "";
        },
        keepTwoDecimalFull(num) {
          var result = parseFloat(num);
          if (isNaN(result)) {
            return num;
          }
          result = Math.round(num * 100) / 100;
          var s_x = result.toString(); //将数字转换为字符串

          var pos_decimal = s_x.indexOf("."); //小数点的索引值

          // 当整数时，pos_decimal=-1 自动补0
          if (pos_decimal < 0) {
            pos_decimal = s_x.length;
            s_x += ".";
          }

          // 当数字的长度< 小数点索引+2时，补0
          while (s_x.length <= pos_decimal + 2) {
            s_x += "0";
          }
          return s_x;
        },
        // 显示提现 dialog
        showTx() {
          this.getWithdrawRule()
        },
        dowithdraw(params) {
          // 推介计划提现
          withdraw(params)
            .then((res) => {
              if (res.data.status == 200) {
                this.$message.success("申请提现成功");
                // 关闭提现弹窗
                this.getAccount();
                this.$refs.withdrawDialog.withdrawCancel();
                this.isdot = true;
              }
            })
            .catch((error) => {
              this.$message.error(error.data.msg);
              // 关闭提现提交按钮的加载状态
              this.$refs.withdrawDialog.withdrawLoading = false;
            });
        },
        // 申请提现 提交
        doCredit() {
          let isPass = true;
          const data = this.txData;
          if (data.method === "alipay") {
            // 支付宝 提现
            if (!data.account) {
              isPass = false;
              this.errText = "请输入支付宝账号";
            }
          }

          if (data.method === "bank") {
            // 银行卡 提现
            if (!data.card_number) {
              isPass = false;
              this.errText = "请输入银行卡号";
            }
            if (!data.name) {
              isPass = false;
              this.errText = "请输入银行卡持有人姓名";
            }
          }

          if (!data.amount) {
            isPass = false;
            this.errText = "请输入提现金额";
          }

          if (isPass) {
            // 清空错误信息
            this.errText = "";
            const params = {
              source: "credit",
              method_id: data.method_id,
              amount: Number(data.amount),
              card_number: data.card_number,
              name: data.name,
              account: data.account,
            };
            withdraw(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success("提现申请成功");
                  this.isShowTx = false;
                  // 重新拉取余额记录
                  this.getCreditList();
                  // 重新拉取当前余额
                  this.getAccount();
                  // 重新拉取待退款金额
                  this.getUnAmount();
                }
              })
              .catch((error) => {
                this.errText = error.data.msg;
              });
          }
        },
        // 充值金额变化时触发
        czInputChange() {
          let data = this.czData;
          let isPass = true;
          if (!data.gateway) {
            this.errText = "请选择充值方式";
            isPass = false;
          }
          if (!data.amount) {
            this.errText = "请输入充值金额";
            isPass = false;
          }

          if (
            this.czData.amount == this.czDataOld.amount &&
            this.czData.gateway == this.czDataOld.gateway
          ) {
            isPass = false;
          }

          if (isPass) {
            this.errText = "";
            // 调用充值接口
            const params = {
              amount: Number(data.amount),
              gateway: data.gateway,
            };
            this.doRecharge(params);
          }
        },
        // 充值方式变化时触发
        czSelectChange() {
          let data = this.czData;
          let isPass = true;
          if (!data.gateway) {
            this.errText = "请选择充值方式";
            isPass = false;
          }
          if (!data.amount) {
            isPass = false;
          }
          if (isPass) {
            this.errText = "";
            // 调用充值接口
            const params = {
              amount: Number(data.amount),
              gateway: data.gateway,
            };
            this.doRecharge(params);
          }
        },
        // 充值dialog 关闭
        czClose() {
          this.isShowCz = false;
          clearInterval(this.timer);
          this.time = 300000;
        },
        // 充值
        doRecharge(params) {
          this.payLoading1 = true;
          this.isShowimg1 = true;
          this.czDataOld = { ...this.czData };
          recharge(params)
            .then((res) => {
              if (res.data.status === 200) {
                const orderId = res.data.data.id;
                const gateway = params.gateway;
                // 调用支付接口
                pay({ id: orderId, gateway })
                  .then((res) => {
                    this.payLoading1 = false;
                    this.isShowimg1 = true;
                    this.errText = "";
                    if (res.data.status === 200) {
                      this.payHtml = res.data.data.html;
                      // 轮询支付状态
                      this.pollingStatus(orderId);
                    }
                  })
                  .catch((error) => {
                    this.payLoading1 = false;
                    this.isShowimg1 = false;
                    this.errText = error.data.msg;
                  });
              }
            })
            .catch((error) => {
              // 显示错误信息
              this.errText = error.data.msg;
              // 关闭loading
              this.payLoading1 = false;
              // 第三方支付
              this.payHtml = "";
            });
        },
        // 轮循支付状态
        pollingStatus(id) {
          if (this.timer) {
            clearInterval(this.timer);
          }
          this.timer = setInterval(async () => {
            const res = await getPayStatus(id);
            this.time = this.time - 2000;
            if (res.data.code === "Paid") {
              this.$message.success(res.data.msg);
              clearInterval(this.timer);
              this.time = 300000;
              this.isShowCz = false;
              this.isShowZf = false;
              this.getCreditList();
              this.getorderList();
              this.getAccount();
              this.getUnAmount();
              return false;
            }
            if (this.time === 0) {
              clearInterval(this.timer);
              // 关闭充值 dialog
              this.isShowCz = false;
              this.isShowZf = false;
              this.$message.error("支付超时");
            }
          }, 2000);
        },
        getRowKey(row) {
          return row.id + "-" + row.host_id;
        },
        // 订单记录订单详情
        load(tree, treeNode, resolve) {
          // 获取订单详情
          const id = tree.id;
          orderDetails(id).then((res) => {
            if (res.data.status === 200) {
              let resData = res.data.data.order.items;
              resolve(resData);
            }
          });
          // this.getOrderDetailsList(id)
        },
        // 打开删除弹窗
        openDeletaDialog(row, index) {
          this.isShowDeOrder = true;
          this.deleteObj = {
            id: row.id,
            index: index,
          };
        },
        // 删除订单
        handelDeleteOrder() {
          delete_order(this.deleteObj.id)
            .then((res) => {
              this.dataList1.splice(this.deleteObj.index, 1);
              this.params1.total = Number(this.params1.total) - 1;
              this.$message.success("删除成功");
              this.isShowDeOrder = false;
            })
            .catch(() => {
              this.isShowDeOrder = false;
            });
        },
        // 交易记录订单详情
        rowClick(orderId) {
          this.isDetail = true;
          // const id = row.order_id
          this.getOrderDetailsList(orderId);
        },
        // 支付弹窗相关
        // 点击去支付
        showPayDialog(row) {
          const orderId = row.id;
          const amount = row.amount;
          this.$refs.payDialog.showPayDialog(orderId, amount);
        },
        // 支付成功回调
        paySuccess(e) {
          this.getCreditList();
          this.getorderList();
          this.getAccount();
          this.getUnAmount();
        },
        // 取消支付回调
        payCancel(e) {
        },
        // 支付方式切换
        zfSelectChange() {
          this.payLoading = true;
          this.isShowimg = true;
          const balance = Number(this.balance);
          const money = Number(this.zfData.amount);
          // 余额大于等于支付金额 且 勾选了使用余额
          if (balance >= money && this.zfData.checked) {
            return false;
          }
          // 获取第三方支付
          const params = {
            gateway: this.zfData.gateway,
            id: this.zfData.orderId,
          };
          pay(params)
            .then((res) => {
              this.errText = "";
              this.payLoading = false;
              this.payHtml = res.data.data.html;
            })
            .catch((error) => {
              this.isShowimg = false;
              this.payLoading = false;
              this.errText = error.data.msg;
            });
        },
        // 使用余额
        useBalance() {
          this.getAccount();
          if (this.balanceTimer) {
            clearTimeout(this.balanceTimer);
          }
          this.balanceTimer = setTimeout(() => {
            creditPay({
              id: this.zfData.orderId,
              use: this.zfData.checked ? 1 : 0,
            })
              .then((res) => {
                // 新的订单id
                const tempId = res.data.data.id;
                this.zfData.orderId = tempId;
                // 获取新订单的详情
                orderDetails(tempId).then((result) => {
                  const orderRes = result.data.data.order;
                  if (this.zfData.checked) {
                    //使用余额
                    if (Number(this.balance) >= Number(orderRes.amount)) {
                      this.errText = "";
                      this.isShowPay = false;
                    } else {
                      // 账户余额小于 订单金额 重新拉取第三方支付并显示
                      this.isShowPay = true;
                      this.zfSelectChange();
                    }
                  } else {
                    // 取消使用余额
                    if (Number(this.balance) >= Number(orderRes.amount)) {
                      this.errText = "";
                      this.isShowPay = true;
                    } else {
                      // 账户余额小于 订单金额 重新拉取第三方支付并显示
                      this.isShowPay = true;
                      this.zfSelectChange();
                    }
                  }
                });
              })
              .catch((error) => {
                this.errText = error.data.msg;
              });
          }, 500);
        },
        // 获取提现规则
        getWithdrawRule() {
          withdrawRule().then((res) => {
            if (res.data.status === 200) {
              this.ruleData = res.data.data;
              const ruler = {
                // 提现来源
                source: "credit",
                // 提现方式
                method: [],
                // 第一个提现方式
                method_id: "",
                // 可提现金额
                withdrawable_amount: this.balance,
                // 单次提现最提金额
                withdraw_min: "",
                // 单次提现最高金额
                withdraw_max: "",
                // 提现手续费 百分比的带上“%” 固定金额 保留两位数
                withdraw_handling_fee: "",
                // 最低提现手续费
                percent_min: "",
              };
              ruler.method = this.ruleData.method;
              ruler.method_id = this.ruleData.method[0]
                ? this.ruleData.method[0].id
                : "";
              ruler.withdraw_max = this.keepTwoDecimalFull(this.ruleData.max);
              ruler.withdraw_min = this.keepTwoDecimalFull(this.ruleData.min);
              // 如果是固定的费用就取 withdraw_fee
              if (this.ruleData.withdraw_fee_type == "fixed") {
                ruler.withdraw_handling_fee =
                  this.currency_prefix + this.ruleData.withdraw_fee;
              } else {
                // 如果是百分比收费就取 percent 不过加上 符号
                ruler.withdraw_handling_fee = this.ruleData.percent ? this.ruleData.percent + "%" : '';
              }
              ruler.percent_min = this.keepTwoDecimalFull(
                this.ruleData.percent_min
              );
              this.$refs.withdrawDialog.shwoWithdrawal(ruler);
            }
          });
        },
        // 充值关闭
        zfClose() {
          this.isShowZf = false;
          this.isShowPay = true;
          clearInterval(this.timer);
          this.time = 300000;
          if (this.zfData.checked) {
            // 如果勾选了使用余额
            this.zfData.checked = false;
            // 取消使用余额
            const params = {
              id: this.zfData.orderId,
              use: 0,
            };
            creditPay(params)
              .then((res) => { })
              .catch((error) => { });
          }
        },
        // 确认使用余额支付
        handleOk() {
          const params = {
            gateway: this.zfData.gateway,
            id: this.zfData.orderId,
          };
          pay(params)
            .then((res) => { })
            .catch((error) => { });
        },
        // 返回两位小数
        oninput(value) {
          let str = value;
          let len1 = str.substr(0, 1);
          let len2 = str.substr(1, 1);
          //如果第一位是0，第二位不是点，就用数字把点替换掉
          if (str.length > 1 && len1 == 0 && len2 != ".") {
            str = str.substr(1, 1);
          }
          //第一位不能是.
          if (len1 == ".") {
            str = "";
          }
          if (len1 == "+") {
            str = "";
          }
          if (len1 == "-") {
            str = "";
          }
          //限制只能输入一个小数点
          if (str.indexOf(".") != -1) {
            let str_ = str.substr(str.indexOf(".") + 1);
            if (str_.indexOf(".") != -1) {
              str = str.substr(0, str.indexOf(".") + str_.indexOf(".") + 1);
            }
          }
          //正则替换
          str = str.replace(/[^\d^\.]+/g, ""); // 保留数字和小数点
          str = str.replace(/^\D*([0-9]\d*\.?\d{0,2})?.*$/, "$1"); // 小数点后只能输 2 位
          return str;
        },
        // 监测滚动
        computeScroll() {
          let sizeWidth = document.documentElement.clientWidth; // 初始宽宽度
          if (sizeWidth > 750) {
            return false;
          }

          const body = document.getElementById("finance");
          // 获取距离顶部的距离
          let scrollTop =
            window.pageYOffset ||
            document.documentElement.scrollTop ||
            document.body.scrollTop;
          // 获取窗口的高度
          let browserHeight = window.outerHeight;
          // 滚动条高度
          const scrollHeight = body.scrollHeight;
          let scroll = scrollTop - this.scrollY;
          this.scrollY = scrollTop;
          // 判断返回顶部按钮是否显示
          if (scrollTop > browserHeight) {
            if (scroll < 0) {
              this.isShowBackTop = true;
            } else {
              this.isShowBackTop = false;
            }
          } else {
            this.isShowBackTop = false;
          }

          // 判断是否到达底部
          if (browserHeight + scrollTop >= scrollHeight) {
            // 判断是否加载数据
            if (this.activeIndex == 1) {
              // 订单记录
              // 判断是否最后一页
              // 是：显示到底了
              // 不是：则加载下一页 显示加载中
              const params = this.params1;
              // 计算总页数
              let allPage =
                params.total % params.limit == 0
                  ? params.total / params.limit
                  : Math.floor(params.total / params.limit) + 1;

              if (params.page == allPage) {
                // 已经是最后一页了
                this.isEnd = true;
              } else {
                // 显示加载中
                this.isShowMore = true;
                // 页数加一
                this.params1.page = this.params1.page + 1;
                // 获取订单记录 push到列表中
                // 关闭加载中
                orderList(this.params1).then((res) => {
                  if (res.data.status === 200) {
                    this.params1.total = res.data.data.count;
                    let list = res.data.data.list;

                    list.map((item) => {
                      let product_name = "";
                      // 商品名称 含两个以上的 只取前两个拼接然后拼接上商品名称的个数
                      if (item.product_names.length > 2) {
                        product_name =
                          item.product_names[0] +
                          "、" +
                          item.product_names[1] +
                          " " +
                          "等" +
                          item.product_names.length +
                          "个商品";
                      } else {
                        item.product_names.map((n) => {
                          product_name += n + "、";
                        });
                        product_name = product_name.slice(0, -1);
                      }
                      item.product_name = product_name;
                      item.data = [];
                      // 判断有无子数据
                      if (item.order_item_count > 1) {
                        // item.children = []
                        item.hasChildren = true;
                      }
                      this.dataList1.push(item);
                    });
                  }
                  this.isShowMore = false;
                });
              }
            }
            if (this.activeIndex == 2) {
              // 交易记录
              // 判断是否最后一页
              // 是：显示到底了
              // 不是：则加载下一页 显示加载中
              const params = this.params2;
              // 计算总页数
              let allPage =
                params.total % params.limit == 0
                  ? params.total / params.limit
                  : Math.floor(params.total / params.limit) + 1;

              if (params.page == allPage) {
                // 已经是最后一页了
                this.isEnd = true;
              } else {
                // 显示加载中
                this.isShowMore = true;
                // 页数加一
                this.params2.page = this.params2.page + 1;
                // 获取交易记录 push到列表中
                // 关闭加载中

                transactionList(this.params2).then((res) => {
                  if (res.data.status === 200) {
                    let list = res.data.data.list;
                    if (list) {
                      list.map((item) => {
                        if (item.order_id == 0) {
                          item.order_id = "--";
                        }
                        this.dataList2.push(item);
                      });
                    }
                    this.params2.total = res.data.data.count;
                  }
                  this.isShowMore = false;
                });
              }
            }
            if (this.activeIndex == 3) {
              // 余额记录
              // 判断是否最后一页
              // 是：显示到底了
              // 不是：则加载下一页 显示加载中
              const params = this.params3;
              // 计算总页数
              let allPage =
                params.total % params.limit == 0
                  ? params.total / params.limit
                  : Math.floor(params.total / params.limit) + 1;

              if (params.page == allPage) {
                // 已经是最后一页了
                this.isEnd = true;
              } else {
                // 显示加载中
                this.isShowMore = true;
                // 页数加一
                this.params3.page = this.params3.page + 1;
                // 获取交易记录 push到列表中
                // 关闭加载中

                creditList(this.params3).then((res) => {
                  if (res.data.status === 200) {
                    let list = res.data.data.list;
                    // 过滤人工订单 不显示
                    // list = list.filter((item) => {
                    //   return item.type !== "Artificial";
                    // });

                    list.map((item) => {
                      this.dataList3.push(item);
                    });

                    this.params3.total = res.data.data.count;
                  }
                  this.isShowMore = false;
                });
              }
            }
          } else {
            this.isEnd = false;
            this.isShowMore = false;
          }
        },
        // 返回顶部
        goBackTop() {
          document.documentElement.scrollTop = document.body.scrollTop = 0;
        },
        // 移动端item展开
        showItem(item) {
          if (item.hasChildren) {
            const id = item.id;
            orderDetails(id).then((res) => {
              if (res.data.status === 200) {
                let resData = res.data.data.order.items;
                this.dataList1.map((n) => {
                  if (n.id === item.id) {
                    // n.id = 1
                    n.data = resData;
                  }
                });
              }
            });
          }
        },

        // 代金卷
        showVoucherDialog() {
          this.dialogVisible = true;
          this.getVoucherAvailable();
        },
        toggleVoucher(row) {
          this.voucherList
            .filter((item) => item.id !== row.id)
            .map((item) => {
              item.isShow = false;
              return item;
            });
          this.voucherAvailableList
            .filter((item) => item.id !== row.id)
            .map((item) => {
              item.isShow = false;
              return item;
            });
          row.isShow = !row.isShow;
        },
        // 可领代金券
        async getVoucherAvailable() {
          try {
            this.diaLoading = true;
            const res = await voucherAvailable(this.availableParams);
            let temp = res.data.data.list;
            temp = temp.map((item) => {
              item.isShow = false;
              return item;
            });
            this.voucherAvailableList = temp;
            this.availableParams.total = res.data.data.count;
            this.diaLoading = false;
          } catch (error) {
            this.diaLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 点击领取
        async sureGet(item) {
          try {
            if (item.is_get) {
              return;
            }
            const res = await voucherGet({ id: item.id });
            this.$message.success(res.data.msg);
            this.getVoucherAvailable();
            this.getVoucher();
          } catch (error) {
            this.$message.error(error.data.msg);
            this.getVoucherAvailable();
          }
        },
        // 获取我的代金券
        async getVoucher() {
          try {
            this.voucherLoading = true;
            const res = await voucherMine(this.vParams);
            let temp = res.data.data.list;
            temp = temp.map((item) => {
              item.isShow = false;
              return item;
            });
            this.voucherList = temp;
            this.vParams.total = res.data.data.count;
            this.voucherLoading = false;
          } catch (error) {
            this.voucherLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 每页展示数改变
        sizeChange(e) {
          this.vParams.limit = e;
          this.vParams.page = 1;
          // 获取列表
          this.getVoucher();
        },
        // 当前页改变
        currentChange(e) {
          this.vParams.page = e;
          this.getVoucher();
        },
      },
    }).$mount(finance);
    typeof old_onload == "function" && old_onload();
  };
})(window);
