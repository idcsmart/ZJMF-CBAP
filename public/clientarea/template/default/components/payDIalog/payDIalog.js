const payDialog = {
    template: `
    <!-- 支付 dialog -->
    <div class="zf-dialog">
        <el-dialog width="6.8rem" :visible.sync="isShowZf" :show-close=false @close="zfClose">
            <div class="dialog-title">
                支付
            </div>
            <div class="dialog-form">
               
            </div>
        </el-dialog>
    </div>
    `,
    methods: {
        // 支付 dialog guanbi
        isShow:false,
        zfClose() {
            console.log("支付dialog关闭");
        },
    },
    props:{
        isShowZf:{
            type:Boolean,
        },
        orderId:{
            type:Number
        },
        amount:{
            type:Number
        }
    }
}