const certificationDialog = {
    template: `
    <!-- 未实名认证 dialog -->
    <div class="rz-dialog">
        <el-dialog width="14.48rem" :visible.sync="tip_dialong_show" @close="rzClose">
            <div class="dialag-content">
                <h2 class="tips-title">温馨提示：您还未实名，请尽快前往实名认证</h2>
                <p class="tips-text">据我国2016年11月7日全国人民代表大会常务委员会通过的《中华人民共和国网络安全法》规定,用户不提供真实身份信息的，网络运营者不得为其提供相关服务。 为了符合国家法律法规，以及不影响您参与优惠活动，请您先实名认证。实名认证信息保密工作是统一管理，请放心填写。</p>
                <div class="button-box">
                    <el-button @click="goCertification">立即认证</el-button>
                    <el-link @click ="rzClose()">以后再说</el-link>
                </div>
            </div>
        </el-dialog>
    </div>
    `,
    props: {
        tip_dialong_show: {
            type: Boolean,
            default: false
        },
    },
    data() {
        return {
            isShow: false
        }
    },
    methods: {
        rzClose() {
            this.$emit('close-dialog')
        },
        goCertification() {
            location.href = 'authentication_select.html'
        },

    },
    watch: {}
}