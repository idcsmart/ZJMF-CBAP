const payDialog = {
    template:
        `
        <el-dialog width="6.8rem" custom-class='zf-dialog' :visible.sync="isShowZf" :show-close=false @close="zfClose">
            <div class="dialog-title">
                支付
            </div>
            <div class="dialog-form">
                <el-row>
                    <el-col :span="5">支付方式</el-col>
                    <el-col :span="19">
                        <el-select @change="zfSelectChange" v-model="zfData.gateway">
                            <el-option v-for="item in gatewayList" :key="item.id" :label="item.title" :value="item.name"></el-option>
                        </el-select>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="5"><span>&nbsp;</span></el-col>
                    <el-col :span="19">
                        <el-checkbox v-model="zfData.checked" @change="useBalance">使用余额</el-checkbox>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="5">订单金额</el-col>
                    <el-col :span="19">
                        <div>{{ commonData.currency_prefix }}
                            {{ Number(zfData.amount).toFixed(2)}} {{ commonData.currency_suffix }}
                        </div>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="5">支付金额</el-col>
                    <el-col :span="19">
                        <div class="true-money">{{ commonData.currency_prefix}}
                            {{
                                zfData.checked?(zfData.amount-balance <=0 ? 0:
                                    zfData.amount-balance).toFixed(2):Number(zfData.amount).toFixed(2)}} {{
                                    commonData.currency_suffix }}
                        </div>
                    </el-col>
                </el-row>
                <el-row v-show="!isShowPay">
                    <el-col :span="5"><span>&nbsp;</span></el-col>
                    <el-col :span="19">
                        <div class="true-money">
                            -{{ commonData.currency_prefix }}
                            {{zfData.checked?zfData.amount>=balance?Number(balance).toFixed(2):Number(zfData.amount).toFixed(2):null}}
                            {{commonData.currency_suffix }}
                        </div>
                    </el-col>
                </el-row>
                <el-row v-show="errText">
                    <el-col :span="24">
                        <el-alert :title="errText" type="error" :closable="false" show-icon>
                        </el-alert>
                    </el-col>
                </el-row>

                <el-row v-show="isShowPay" v-loading="payLoading">
                    <el-col :span="24">
                        <div class="pay-html" v-show="isShowimg" v-html="payHtml"></div>
                    </el-col>
                </el-row>
                <el-row v-show="!isShowPay">
                    <el-col :span="24">
                        <div class="form-footer">
                            <el-button class="btn-ok" @click="handleOk">确认支付</el-button>
                            <el-button class="btn-no" @click="zfClose">取消</el-button>
                        </div>
                    </el-col>
                </el-row>
            </div>
        </el-dialog>
    `,
    created() {
        // 获取支付方式列表
        this.getGateway()
        this.commonData = JSON.parse(localStorage.getItem("common_set_before"))
    },
    destroyed() {
        clearInterval(this.timer)
        clearTimeout(this.balanceTimer)
    },
    data() {
        return {
            // 显示弹窗
            isShowZf: false,
            // 显示底部支付按钮
            isShowPay: true,
            timer: null,
            time: 300000,
            zfData: {
                // 订单id
                orderId: 0,
                // 订单金额
                amount: 0,
                checked: false,
                // 支付方式
                gateway: gatewayList.length > 0 ? gatewayList[0].name : ''
            },
            // 支付方式
            gatewayList: [],
            payLoading: false,
            isShowimg: true,
            // 用户余额
            balance: 0,
            errText: '',
            payHtml: '',
            balanceTimer: null,
            commonData: {
                currency_prefix: '￥'
            },
            isPaySuccess: false
        }
    },
    methods: {
        // 获取账户详情
        getAccount() {
            account().then(res => {
                if (res.data.status === 200) {
                    this.balance = res.data.data.account.credit
                }
            })
        },
        // 支付关闭
        zfClose() {
            if (!this.isPaySuccess) {
                this.$emit('paycancel', this.zfData.orderId)
            }

            this.isShowZf = false
            this.isShowPay = true
            clearInterval(this.timer)
            this.time = 300000
            if (this.zfData.checked) {  // 如果勾选了使用余额
                this.zfData.checked = false
                // 取消使用余额
                const params = {
                    id: this.zfData.orderId,
                    use: 0
                }
                creditPay(params).then(res => {

                }).catch(error => { })

            }
        },
        // 获取支付方式列表
        getGateway() {
            gatewayList().then(res => {
                if (res.data.status === 200) {
                    this.gatewayList = res.data.data.list
                }
            })
        },
        // 支付方式切换
        zfSelectChange() {
            this.payLoading = true
            this.isShowimg = true
            const balance = Number(this.balance)
            const money = Number(this.zfData.amount)
            // 余额大于等于支付金额 且 勾选了使用余额
            if ((balance >= money) && this.zfData.checked) {
                return false
            }
            // 获取第三方支付
            const params = {
                gateway: this.zfData.gateway,
                id: this.zfData.orderId
            }
            pay(params).then(res => {
                this.errText = ""
                this.payLoading = false
                this.payHtml = res.data.data.html
            }).catch(error => {
                this.isShowimg = false
                this.payLoading = false
                this.errText = error.data.msg
            })
        },
        // 使用余额
        useBalance() {
            this.getAccount()
            if (this.balanceTimer) {
                clearTimeout(this.balanceTimer)
            }
            this.balanceTimer = setTimeout(() => {
                creditPay({
                    id: this.zfData.orderId,
                    use: this.zfData.checked ? 1 : 0
                }).then(res => {
                    // 新的订单id
                    const tempId = res.data.data.id;
                    this.zfData.orderId = tempId
                    // 获取新订单的详情
                    orderDetails(tempId).then(result => {
                        const orderRes = result.data.data.order;
                        if (this.zfData.checked) {    //使用余额
                            if (Number(this.balance) >= Number(orderRes.amount)) {
                                this.errText = ""
                                this.isShowPay = false
                            } else {
                                // 账户余额小于 订单金额 重新拉取第三方支付并显示
                                this.isShowPay = true
                                this.zfSelectChange();
                            }
                        } else { // 取消使用余额
                            if (Number(this.balance) >= Number(orderRes.amount)) {
                                this.errText = ""
                                this.isShowPay = true
                            } else {
                                // 账户余额小于 订单金额 重新拉取第三方支付并显示
                                this.isShowPay = true
                                this.zfSelectChange();
                            }
                        }
                    })
                }).catch(error => {
                    this.errText = error.data.msg
                })
            }, 500)



        },
        // 确认使用余额支付
        handleOk() {
            const params = {
                gateway: this.zfData.gateway,
                id: this.zfData.orderId
            }
            pay(params).then(res => { }).catch(error => { })
        },
        // 轮循支付状态
        pollingStatus(id) {
            if (this.timer) {
                clearInterval(this.timer)
            }
            this.timer = setInterval(async () => {
                const res = await getPayStatus(id);
                this.time = this.time - 2000
                if (res.data.code === "Paid") {
                    this.$message.success(res.data.msg)
                    clearInterval(this.timer);
                    this.time = 300000
                    this.isShowCz = false
                    this.isShowZf = false
                    this.getAccount()
                    this.isPaySuccess = true
                    this.$emit('payok', this.zfData.orderId)
                    return false
                }
                if (this.time === 0) {
                    clearInterval(this.timer);
                    // 关闭充值 dialog
                    this.isShowCz = false
                    this.isShowZf = false
                    this.$message.error("支付超时")
                }
            }, 2000)
        },
        // 点击去支付
        showPayDialog(orderId, amount) {
            this.isPaySuccess = false
            if (this.timer) {  // 清除定时器
                clearInterval(this.timer)
            }
            const params = {
                id: orderId,
                use: 0
            }
            creditPay(params).then(res => {
                this.errText = ""
                // 默认不使用余额
                this.zfData.checked = false
                // 重置支付倒计时5分钟
                this.time = 300000
                // 获取余额
                this.getAccount()
                // 获取订单金额 和 订单id
                this.zfData.orderId = Number(orderId)
                this.zfData.amount = Number(amount)
                // 展示支付 dialog
                this.isShowZf = true
                // 默认拉取第一种支付方式
                this.zfData.gateway = this.gatewayList[0].name
                this.zfSelectChange()
                // 轮询支付
                this.pollingStatus(this.zfData.orderId)

            }).catch(error => { })



        },
    },

}