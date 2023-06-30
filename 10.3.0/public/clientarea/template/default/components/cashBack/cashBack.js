const cashBack = {
  template:
    `
    <el-dialog :visible.sync="showCash" :show-close="false" custom-class="common-cashback-dialog">
      <div class="dialog-title">
        {{lang.apply_cashback}}
      </div>
      <div class="con">
        {{lang.cashback_tip1}}<span class="price">￥{{cashbackPrice | filterMoney}}</span>
        <template v-if="cashbackTime">，{{lang.cashback_tip2}} {{cashbackTime | formateTime}}</template>
      </div>
      <div class="dialog-footer">
        <div class="tip">{{lang.cashback_tip}}</div>
        <div class="opt">
          <el-button class="btn-ok" @click="sureCashback" :loading="cashLoading">{{lang.ticket_btn6}}</el-button>
          <el-button class="btn-no" @click="cancleDialog">{{lang.finance_btn7}}</el-button>
        </div>
      </div>
    </el-dialog>
    `,
  filters: {
    filterMoney (money) {
      if (isNaN(money)) {
        return '0.00'
      } else {
        const temp = `${money}`.split('.')
        return parseInt(temp[0]).toLocaleString() + '.' + (temp[1] || '00')
      }
    },
    formateTime (time) {
      if (time && time !== 0) {
        return formateDate(time * 1000)
      } else {
        return "--"
      }
    }
  },
  data () {
    return {
      hasCash: false, // 是否安装了插件
      isShowBtn: false, // 是否展示申请返现按钮
      cashbackPrice: null,
      cashbackTime: null,
      cashLoading: false
    }
  },
  props: {
    id: {
      type: Number | String,
      required: true,
    },
    showCash: {
      type: Boolean
    }
  },
  mixins: [mixin],
  mounted () {
    this.hasCash = this.addons_js_arr.includes('ProductCashback')
    this.hasCash && this.getCash()
  },
  created () {
  },
  methods: {
    async getCash () {
      try {
        const { data: { data: { cashback_support, is_cashback, expired, price } } } = await getCashbackInfo({ id: this.id })
        if (cashback_support && !is_cashback && price * 1 && ((expired > new Date().getTime() / 1000) || expired === 0)) {
          this.cashbackPrice = price
          this.cashbackTime = expired
          this.isShowBtn = true
        } else {
          this.isShowBtn = false
        }
        this.$emit('showbtn', this.isShowBtn)
      } catch (error) {
        this.$message.error(error.data.msg)
      }
    },
    async sureCashback () {
      try {
        this.cashLoading = true
        const res = await apllyCashback({ id: this.id })
        this.$message.success(res.data.msg)
        this.getCash()
        this.cashLoading = false
        this.$emit('cancledialog', false)
      } catch (error) {
        this.cashLoading = false
        this.$message.error(error.data.msg)
      }
    },
    cancleDialog () {
      this.$emit('cancledialog', false)
    }
  },
}
