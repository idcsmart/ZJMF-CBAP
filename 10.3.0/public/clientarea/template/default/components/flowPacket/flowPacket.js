const flowPacket = {
  template:
    `
    <el-dialog :visible.sync="showPackage && packageList.length > 0" custom-class="common-package-dialog" :loading="packageLoading">
      <i class="el-icon-close" @click="cancleDialog"></i>
      <div class="dialog-title">
        {{lang.buy_package}}
      </div>
      <div class="con">
        <div class="p-item" v-for="item in packageList" :key="item.id"
          :class="{active: item.id === curPackageId}" @click="choosePackage(item)">
          <p class="tit">{{item.name}}</p>
          <p class="qty">{{item.capacity}}G</p>
          <p class="price">{{currencyPrefix}}{{item.price | filterMoney}}</p>
          <i class="el-icon-check"></i>
        </div>
      </div>
      <div class="dialog-footer">
        <el-button class="btn-ok" @click="handlerPackage"
          :loading="packageLoading">{{lang.ticket_btn6}}</el-button>
        <el-button class="btn-no" @click="cancleDialog">{{lang.finance_btn7}}</el-button>
      </div>
    </el-dialog>
    `,
  mixins: [mixin],
  filters: {
    filterMoney (money) {
      if (isNaN(money)) {
        return '0.00'
      } else {
        const temp = `${money}`.split('.')
        return parseInt(temp[0]).toLocaleString() + '.' + (temp[1] || '00')
      }
    }
  },
  data () {
    return {
      hasFlow: true,
      packageLoading: false,
      submitLoading: false,
      packageList: [],
      curPackageId: '',
      currencyPrefix: JSON.parse(localStorage.getItem("common_set_before")).currency_prefix
    }
  },
  props: {
    id: {
      type: Number | String,
      required: true,
    },
    showPackage: {
      type: Boolean
    },
  },
  mounted () {
    this.hasFlow = this.addons_js_arr.includes('FlowPacket')
    this.hasFlow && this.getPackageList()
  },
  methods: {
    async getPackageList () {
      try {
        this.packageLoading = true
        const res = await getFlowPacket({
          id: this.id
        })
        this.packageList = res.data.data.list
        if (this.packageList.length === 0) {
          this.$emit('cancledialog', false)
          return this.$message.warning(lang.package_tip)
        }
        this.curPackageId = this.packageList[0]?.id
        this.packageLoading = false
      } catch (error) {
        this.packageLoading = false
        this.$message.error(error.data.msg)
      }
    },
    choosePackage (item) {
      this.curPackageId = item.id
    },
    async handlerPackage () {
      try {
        this.submitLoading = true
        const res = await buyFlowPacket({
          id: this.id,
          flow_packet_id: this.curPackageId,
        })
        this.$emit('sendpackid', res.data.data.id)
        this.submitLoading = false
      } catch (error) {
        this.submitLoading = false
        this.$message.error(error.data.msg)
      }
    },
    cancleDialog () {
      this.$emit('cancledialog', false)
    }
  },
}