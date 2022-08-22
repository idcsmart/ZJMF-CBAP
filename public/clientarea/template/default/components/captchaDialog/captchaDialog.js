// css 样式依赖common.css
const captchaDialog = {
    template: `
    <div class="captcha-dialog">
    <el-dialog width="7.26rem" :visible.sync="isShowCaptcha" :show-close=false :close-on-click-modal=false >
        <div class="dialog-title">
            图形验证
        </div>
        <div class="dialog-form">
            <el-input v-model="captchaData.captchaCode"  size="large" placeholder="请输入验证码"></el-input>
            <img :src="captchaData.captcha" @click="doGetCaptcha">
        </div>
        <div class="err-alert">
        <el-alert v-show="errText" show-icon :title="errText" type="error" :closable="false"></el-alert>
        </div>
        <div class="dialog-footer">
            <el-button class="btn-ok" @click="checkDialog" v-loading="loading">验证</el-button>
            <el-button class="btn-no" @click="closeDialog">取消</el-button>
        </div>
    </el-dialog>
    </div>`,
    created() {
        this.doGetCaptcha()
    },
    data() {
        return {
            captchaData: {
                token: "",
                captcha: "",
                captchaCode: ""
            },
            errText: "",
            loading: false
        }
    },
    props: {
        isShowCaptcha: {
            type: Boolean,
        }
    },
    methods: {
        // 获取图形验证码
        doGetCaptcha() {
            this.errText = ""

            getCaptcha().then(res => {
                if (res.data.status === 200) {
                    this.captchaData.token = res.data.data.token
                    this.captchaData.captcha = res.data.data.captcha
                }
                
            })
        },
        closeDialog() {
            this.dialogVisible = false
            this.errText = ""
            this.captchaData.captchaCode = ""
            this.$emit('close-dialog')
        },
        checkDialog() {
            this.loading = true
            const params = {
                captcha: this.captchaData.captchaCode,
                token: this.captchaData.token
            }
            checkCaptcha(params).then(res => {
                if (res.data.status === 200) {
                    this.$message.success("验证成功");
                    this.$emit('get-captcha-data', this.captchaData)
                }
                this.loading = false
            }).catch(error => {
                this.errText = error.data.msg
                this.loading = false
            })
        }
    },
}