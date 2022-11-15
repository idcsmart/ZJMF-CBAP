const withdrawDialog = {
    template: `
    <el-dialog width="6.8rem" :visible.sync="withdrawVisible" :show-close=false @close="withdrawCancel" custom-class="withdraw-dialog">
    <div class="dialog-title">
        {{lang.withdraw_title}}
    </div>
    <div class="dialog-main">
        <el-form label-width="80px" :model="withdrawForm" label-position="top">
            <el-form-item :label="lang.withdraw_label1" >
                <el-select class="input-select" v-model="withdrawForm.method_id" @change="methodChange" :placeholder="lang.withdraw_placeholder1">
                    <el-option v-for="item in ruler.method" :key="item.id" :value="item.id" :label="item.name"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item :label="lang.withdraw_label2" v-if="isBank">
                <el-input class="input-select" v-model="withdrawForm.card_number" :placeholder="lang.withdraw_placeholder3"></el-input>
            </el-form-item>
            <el-form-item :label="lang.withdraw_label3" v-else >
                <el-input class="input-select" v-model="withdrawForm.account" :placeholder="lang.withdraw_placeholder2"></el-input>
            </el-form-item>
            <el-form-item :label="lang.withdraw_label4" >
                <el-input class="input-select" v-model="withdrawForm.name" :placeholder="lang.withdraw_placeholder4"></el-input>
            </el-form-item>
            <el-form-item :label="lang.withdraw_label5" >
                <el-input @keyup.native="withdrawForm.amount=oninput(withdrawForm.amount)" class="input-select amount-input" v-model="withdrawForm.amount" :placeholder="lang.withdraw_placeholder5 + currency_prefix + ruler.withdrawable_amount">
                    <el-button class="all-btn" type="text" slot="suffix" @click="withdrawForm.amount=ruler.withdrawable_amount">{{lang.withdraw_btn3}}
                    </el-button>
                </el-input>
            </el-form-item>
            <el-form-item v-if="errText">
                <el-alert :title="errText" type="error" :closable="false" show-icon>
                </el-alert>
            </el-form-item>
        </el-form>
    </div>
    <div class="withdraw-rule">
        <div class="label">{{lang.withdraw_title2}}</div>
        <div class="rules">
            <div class="rule-item" v-if="ruler.withdraw_min || ruler.withdraw_min">
                {{lang.withdraw_text1}}
                <span v-if="ruler.withdraw_min">{{lang.withdraw_text2}}{{currency_prefix}}{{ruler.withdraw_min}}</span>
                <span v-if="ruler.withdraw_min && ruler.withdraw_max">,</span>
                <span v-if="ruler.withdraw_max">{{lang.withdraw_text3}}{{currency_prefix}}{{ruler.withdraw_max}}</span>
            </div>
            <div class="rules-item" v-if="ruler.withdraw_handling_fee || ruler.percent_min">
                {{lang.withdraw_text4}}
                <span v-if="ruler.withdraw_handling_fee">{{ruler.withdraw_handling_fee}}</span>
                <!-- 最低手续费 -->
                <span v-if="ruler.percent_min">{{lang.withdraw_text5}}{{currency_prefix}}{{ruler.percent_min}}</span>
            </div>
        </div>
    </div>
    <span slot="footer" class="dialog-footer">
        <el-button class="btn-ok" type="primary" @click="doApplyWithdraw()" v-loading="withdrawLoading">{{lang.withdraw_btn1}}</el-button>
        <el-button class="btn-no" @click="withdrawCancel">{{lang.withdraw_btn2}}</el-button>
    </span>
</el-dialog>
    `,
    data() {
        return {
            // 提现弹窗开始
            // 是否显示提现弹窗
            currency_prefix: "￥",
            withdrawVisible: false,
            withdrawForm: {
                source: "",
                method_id: "",
                amount: "",
                card_number: "",
                name: "",
                account: "",
                notes: ""
            },
            isBank: false,
            withdrawLoading: false,
            errText: "",
            ruler: {
                // 提现来源
                source: "",
                // 提现方式
                method: [],
                // 第一个提现方式
                method_id: "",
                // 可提现金额
                withdrawable_amount: "",
                // 单次提现最提金额
                withdraw_min: "",
                // 单次提现最高金额
                withdraw_max: "",
                // 提现手续费 百分比的带上“%” 固定金额 保留两位数
                withdraw_handling_fee: "",
                // 最低提现手续费
                percent_min: ""
            },
        }
    },
    created() {
        this.currency_prefix = JSON.parse(localStorage.getItem("common_set_before")).currency_prefix
    },
    methods: {
        // 提现弹窗开始
        // 提现方式变化
        methodChange(e) {
            const method = this.ruler.method
            this.isBank = false
            method.forEach(item => {
                if (item.id == e && item.name == '银行卡') {
                    this.isBank = true
                }
            })
        },
        // 显示提现弹窗
        shwoWithdrawal(ruler) {
            this.withdrawForm = {
                source: ruler.source,
                method_id: ruler.method_id,
                amount: "",
                card_number: "",
                name: "",
                account: "",
                notes: ""
            }
            // 默认选择第一个
            // this.withdrawForm.method_id = ruler.method[0].id
            console.log(ruler);
            this.methodChange(this.withdrawForm.method_id)
            this.ruler = ruler

            this.withdrawVisible = true
            this.errText = ""
        },

        // 申请提现
        doApplyWithdraw() {
            let isPass = true
            this.errText = ""
            const params = {
                ...this.withdrawForm,
            }
            if (this.isBank && !params.card_number) {
                this.errText = lang.withdraw_placeholder3
                isPass = false
                return
            }

            if (!params.method_id) {
                this.errText = lang.withdraw_placeholder1
                isPass = false
                return
            }
            if (!params.amount) {
                this.errText = lang.withdraw_placeholder6
                isPass = false
                return
            } else {
                // 提现金额小于最小金额
                if (this.ruler.withdraw_min && Number(this.ruler.withdraw_min) > Number(params.amount)) {
                    this.errText = lang.withdraw_tips1 + this.currency_prefix + this.ruler.withdraw_min
                    isPass = false
                    return
                }

                if (Number(params.amount) > Number(this.ruler.withdrawable_amount)) {
                    this.errText = lang.withdraw_tips2
                    isPass = false
                    return
                }

                if (this.ruler.withdraw_max && Number(this.ruler.withdraw_max) < Number(params.amount)) {
                    this.errText = lang.withdraw_tips3 + this.currency_prefix + this.ruler.withdraw_max
                    isPass = false
                    return
                }

            }

            if (isPass) {
                this.withdrawLoading = true
                this.errText = ""
                this.$emit('dowithdraw', params)
            }

        },


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
        withdrawCancel() {
            this.withdrawVisible = false
            this.withdrawLoading = false
        }
    }
}