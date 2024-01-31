// css 样式依赖common.css
const captchaDialog = {
    template: `
    <div id="captchaHtml" v-show="isShowCaptcha"></div>`,

    // <div class="captcha-dialog">
    // <el-dialog width="7.26rem" :visible.sync="isShowCaptcha" :show-close=false :close-on-click-modal=false >
    //     <div class= "dialog-form">
    //         <div id="captchaHtml"></div>
    //     </div>
    // </el-dialog>
    // </div>
    created() {
        // this.doGetCaptcha()
    },
    data() {
        return {
            captchaData: {
                token: "",
                captcha: "",
                captchaCode: "",
            },
            captchaHtml: "",
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
                    this.captchaHtml = res.data.data.html
                    $("#captchaHtml").html(this.captchaHtml)
                }
            }).catch(error => {

            })
        },
        // closeDialog() {
        //     this.dialogVisible = false
        //     this.errText = ""
        //     this.captchaData.captchaCode = ""
        //     this.$emit('close-dialog')
        // },
        // checkDialog() {
        //     this.loading = true
        //     const params = {
        //         captcha: this.captchaData.captchaCode,
        //         token: this.captchaData.token
        //     }
        //     checkCaptcha(params).then(res => {
        //         if (res.data.status === 200) {
        //             this.$message.success("验证成功");
        //             this.$emit('get-captcha-data', this.captchaData)
        //         }
        //         this.loading = false
        //     }).catch(error => {
        //         this.errText = error.data.msg
        //         this.loading = false
        //     })
        // },
    },
}